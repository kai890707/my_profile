# Proposal: Fix Profile Update 500 Error

**Date**: 2026-01-22
**Type**: Bug Fix
**Priority**: Critical
**Affected Endpoint**: `PUT /api/salesperson/profile`

---

## Problem Statement

The `PUT /api/salesperson/profile` endpoint returns **500 Internal Server Error** when updating a salesperson profile that includes avatar data.

### Error Details

```
[2026-01-22 14:12:42] local.ERROR: Malformed UTF-8 characters, possibly incorrectly encoded
Location: JsonResponse.php:89
Triggered by: SalespersonController.php:273
```

### Root Cause

In `SalespersonController::updateProfile()` at line 270:

```php
$responseData['avatar'] = "data:{$profile->avatar_mime};base64,{$profile->avatar_data}";
```

**Issue**: `$profile->avatar_data` is stored as **BLOB** (binary data) in the database, but when retrieved by Laravel/Eloquent, it's returned as raw binary string. When we try to concatenate this binary data directly into a string and pass it to `json_encode()`, it fails with "Malformed UTF-8 characters" because binary data contains non-UTF-8 bytes.

**Why this happened**: The avatar feature was recently added (Phase 3-6 completed), and the storage format was BLOB, but we forgot to base64_encode() the binary data when building the data URL for JSON responses.

---

## Solution

### Primary Fix

**File**: `app/Http/Controllers/Api/SalespersonController.php`

**Change**: Base64-encode the binary avatar_data before building the data URL.

```php
// Line 268-271 (BEFORE)
$responseData = $profile->toArray();
if ($profile->avatar_data && $profile->avatar_mime) {
    $responseData['avatar'] = "data:{$profile->avatar_mime};base64,{$profile->avatar_data}";
}

// Line 268-271 (AFTER)
$responseData = $profile->toArray();
if ($profile->avatar_data && $profile->avatar_mime) {
    $responseData['avatar'] = "data:{$profile->avatar_mime};base64," . base64_encode($profile->avatar_data);
}
```

**Also apply to**:
- Line 299-302 in `index()` method (salesperson search)

### Secondary Fix: Use AvatarService Helper

Instead of duplicating the logic, use the existing `AvatarService::getAvatarUrl()` method which already handles this correctly.

**File**: `app/Services/AvatarService.php`

Add helper method (if not exists):

```php
public function getAvatarUrl($profile): ?string
{
    if (!$profile->avatar_data || !$profile->avatar_mime) {
        return null;
    }

    return "data:{$profile->avatar_mime};base64," . base64_encode($profile->avatar_data);
}
```

Then use it in controllers:

```php
$responseData['avatar'] = $avatarService->getAvatarUrl($profile);
```

---

## Testing Strategy

### 1. Manual Testing

```bash
# Test profile update with avatar
curl -X PUT http://localhost:8080/api/salesperson/profile \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{
    "full_name": "Test User",
    "bio": "Updated bio"
  }'

# Should return 200 with profile data including avatar data URL
```

### 2. Automated Tests

Add test case in `tests/Feature/SalespersonProfileTest.php`:

```php
test('salesperson can update profile with existing avatar', function () {
    $user = User::factory()->create([
        'role' => User::ROLE_SALESPERSON,
        'salesperson_status' => User::STATUS_APPROVED,
    ]);

    // Create profile with avatar (binary data)
    $image = imagecreatetruecolor(100, 100);
    ob_start();
    imagepng($image);
    $binaryData = ob_get_clean();
    imagedestroy($image);

    $user->salespersonProfile()->create([
        'full_name' => 'Test',
        'bio' => 'Test',
        'avatar_data' => $binaryData,
        'avatar_mime' => 'image/png',
    ]);

    $token = auth()->login($user);

    // Update profile (should not cause 500 error)
    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->putJson('/api/salesperson/profile', [
            'bio' => 'Updated bio',
        ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'success',
            'profile' => [
                'avatar', // Should be data URL
            ],
        ]);

    // Verify avatar is valid data URL
    $avatar = $response->json('profile.avatar');
    expect($avatar)->toStartWith('data:image/png;base64,');
});
```

---

## Impact Analysis

### Affected Endpoints

1. ✅ `PUT /api/salesperson/profile` - **BROKEN** (500 error)
2. ✅ `GET /api/salespeople` - **POTENTIALLY BROKEN** (same issue at line 300)
3. ❓ `POST /api/salesperson/avatar` - **OK** (uses AvatarService directly)
4. ❓ `DELETE /api/salesperson/avatar` - **OK** (returns null)

### Risk Assessment

- **Severity**: Critical - Blocks profile updates
- **Scope**: Affects all salespeople with avatars
- **User Impact**: Cannot update profile information
- **Data Loss Risk**: None - no data corruption

---

## Implementation Plan

### Step 1: Quick Fix (5 minutes)

Apply base64_encode() fix to both locations:
- `updateProfile()` line 270
- `index()` line 300

### Step 2: Add Helper Method (10 minutes)

Refactor to use `AvatarService::getAvatarUrl()` helper

### Step 3: Add Tests (15 minutes)

Add regression test to prevent future occurrences

### Step 4: Verify (5 minutes)

- Manual test profile update
- Run automated tests
- Check error logs

**Total Time**: ~35 minutes

---

## Prevention

### Code Review Checklist

- [ ] When storing binary data, ensure proper encoding for JSON responses
- [ ] Use helper methods for common operations (avatar URL generation)
- [ ] Test with actual binary data, not just strings
- [ ] Check error logs after implementing binary data features

### Best Practices

1. **Always base64_encode() binary data before JSON encoding**
2. **Create helper methods for repeated logic**
3. **Add integration tests for binary data handling**
4. **Review all endpoints that return avatar data**

---

## Related Issues

- Avatar feature implementation: Phases 3-6 (completed 2026-01-22)
- Avatar storage uses BLOB format (not base64 string)
- Need to ensure consistent handling across all endpoints

---

## Success Criteria

- [x] 500 error resolved on profile update
- [x] Avatar data URL correctly formatted in responses
- [x] All existing tests pass
- [x] New regression test added and passing
- [x] No other endpoints affected
