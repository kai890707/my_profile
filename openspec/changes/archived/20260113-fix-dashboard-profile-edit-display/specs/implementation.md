# Implementation Guide: Dashboard Profile Edit Fix

**Feature**: 修復 Dashboard 編輯模式資料顯示問題
**Type**: Bug Fix
**Priority**: Medium
**Created**: 2026-01-12
**Owner**: Frontend Developer

---

## 1. Implementation Overview

### 1.1 Summary

This fix resolves two related issues in the Dashboard profile edit page:
1. **Edit button enabled during loading** - allows premature entry to edit mode
2. **Avatar shows 'U' in edit mode** - instead of user's name abbreviation

### 1.2 Changes Required

**Total Changes**: 2 modifications in 1 file

| File | Lines Changed | Type |
|------|---------------|------|
| `frontend/app/(dashboard)/dashboard/page.tsx` | 2 lines | Modification |

**Estimated Time**: 5 minutes (code) + 15 minutes (testing) = 20 minutes total

---

## 2. Step-by-Step Implementation

### Step 1: Open the Target File

```bash
cd /Users/kai/KAA/my_profile/frontend
code app/(dashboard)/dashboard/page.tsx
```

Or use Read tool:
```bash
# Read the file to locate exact lines
cat app/(dashboard)/dashboard/page.tsx | head -n 280 | tail -n 90
```

---

### Step 2: Locate Problem Areas

**Problem Area 1**: Edit Button (Line 197-199)
**Problem Area 2**: Avatar Fallback (Line 272)

Use your editor's search function:
- Search for: `編輯資料`
- Search for: `fallback={getAvatarFallback`

---

### Step 3: Apply Fix 1 - Edit Button Disable Logic

**Location**: Line 197-199

**Before (Current Code)**:
```tsx
{!editMode && (
  <Button onClick={() => setEditMode(true)}>編輯資料</Button>
)}
```

**After (Fixed Code)**:
```tsx
{!editMode && (
  <Button
    onClick={() => setEditMode(true)}
    disabled={profileLoading}
  >
    編輯資料
  </Button>
)}
```

**Changes Made**:
1. Multi-line Button component for readability
2. Added `disabled={profileLoading}` prop

**Using Edit Tool**:
```typescript
Edit(
  file_path: "frontend/app/(dashboard)/dashboard/page.tsx",
  old_string: "{!editMode && (\n          <Button onClick={() => setEditMode(true)}>編輯資料</Button>\n        )}",
  new_string: "{!editMode && (\n          <Button\n            onClick={() => setEditMode(true)}\n            disabled={profileLoading}\n          >\n            編輯資料\n          </Button>\n        )}"
)
```

---

### Step 4: Apply Fix 2 - Avatar Fallback Logic

**Location**: Line 272

**Before (Current Code)**:
```tsx
<Avatar
  src={avatarPreview || profileData?.avatar}
  fallback={getAvatarFallback(profileData || {})}
  size="2xl"
/>
```

**After (Fixed Code)**:
```tsx
<Avatar
  src={avatarPreview || profileData?.avatar}
  fallback={profileData ? getAvatarFallback(profileData) : 'U'}
  size="2xl"
/>
```

**Changes Made**:
1. Replaced `profileData || {}` with ternary check
2. Only call `getAvatarFallback()` if `profileData` exists
3. Otherwise return `'U'` directly

**Using Edit Tool**:
```typescript
Edit(
  file_path: "frontend/app/(dashboard)/dashboard/page.tsx",
  old_string: "                <Avatar\n                  src={avatarPreview || profileData?.avatar}\n                  fallback={getAvatarFallback(profileData || {})}\n                  size=\"2xl\"\n                />",
  new_string: "                <Avatar\n                  src={avatarPreview || profileData?.avatar}\n                  fallback={profileData ? getAvatarFallback(profileData) : 'U'}\n                  size=\"2xl\"\n                />"
)
```

---

### Step 5: Save and Verify TypeScript Compilation

```bash
cd /Users/kai/KAA/my_profile/frontend

# Run TypeScript typecheck
npm run typecheck
```

**Expected Output**:
```
✓ TypeScript check passed
No errors found
```

**If Errors Occur**: Review syntax, ensure proper indentation and closing brackets

---

### Step 6: Start Dev Server (If Not Running)

```bash
cd /Users/kai/KAA/my_profile/frontend

# Start development server
npm run dev
```

**Expected Output**:
```
▲ Next.js 16.1.1 (Turbopack)
- Local:         http://localhost:3001
✓ Ready in 2.7s
```

---

### Step 7: Manual Testing

**Test Scenario 1: Loading State**

1. Open browser: `http://localhost:3001`
2. Navigate to `/dashboard` (or login if not authenticated)
3. **Hard refresh** (Cmd+Shift+R / Ctrl+Shift+F5) to see loading state
4. Observe:
   - ✅ Skeleton loader appears
   - ✅ "編輯資料" button not visible yet
5. After data loads (~1-2 seconds):
   - ✅ Profile data appears
   - ✅ "編輯資料" button becomes visible and enabled

**Expected**: Button only appears after loading completes ✅

---

**Test Scenario 2: Avatar Display in Edit Mode**

1. Ensure you're on `/dashboard` with data loaded
2. Observe avatar in **View Mode**:
   - ✅ Shows photo OR name abbreviation (e.g., "測" for "測試業務員")
3. Click **"編輯資料"** button
4. Observe avatar in **Edit Mode**:
   - ✅ Shows same photo OR name abbreviation (NOT "U")
   - ✅ Matches what was shown in view mode

**Before Fix**: Would show "U" ❌
**After Fix**: Shows correct name abbreviation ✅

---

**Test Scenario 3: Avatar Upload and Cancel**

1. In Edit Mode, click avatar to upload new image
2. Select a test image
3. Observe:
   - ✅ Avatar immediately shows preview of uploaded image
4. Click **"取消"** button
5. Observe:
   - ✅ Returns to View Mode
   - ✅ Avatar shows original (uploaded image preview cleared)
   - ✅ All form fields reset to original values

---

**Test Scenario 4: Console Error Check**

1. Open Browser DevTools (F12)
2. Go to **Console** tab
3. Clear console
4. Perform all actions:
   - Navigate to Dashboard
   - Enter edit mode
   - Upload avatar (optional)
   - Cancel edit
   - Enter edit mode again
5. Check console:
   - ✅ **No TypeErrors**
   - ✅ **No "Cannot read properties of undefined" errors**
   - ✅ **No "substring is not a function" errors**

---

### Step 8: Browser Testing

**Test on Multiple Browsers**:
- ✅ Chrome/Edge (Chromium)
- ✅ Firefox
- ✅ Safari (if on macOS)

**Test Responsive Design**:
- ✅ Desktop (1920x1080)
- ✅ Tablet (768x1024)
- ✅ Mobile (375x667)

**Test User Flows**:
- ✅ New user (no profile data yet)
- ✅ Existing user with complete profile
- ✅ Existing user with partial profile (e.g., no phone number)

---

### Step 9: Automated Testing (Optional but Recommended)

**Create E2E Test**:

**File**: `frontend/e2e/dashboard-profile-fix.spec.ts`

```typescript
import { test, expect } from '@playwright/test';

test.describe('Dashboard Profile Edit Fix', () => {
  test.beforeEach(async ({ page }) => {
    // Login with test credentials
    await page.goto('http://localhost:3001/login');
    await page.fill('input[name="email"]', 'salesperson@example.com');
    await page.fill('input[name="password"]', 'test123');
    await page.click('button[type="submit"]');
    await page.waitForURL('**/dashboard');
  });

  test('should show correct avatar in edit mode', async ({ page }) => {
    // Wait for data to load
    await page.waitForLoadState('networkidle');

    // Get avatar text in view mode
    const viewModeAvatar = await page.locator('[class*="avatar"]').first().textContent();
    console.log('View Mode Avatar:', viewModeAvatar);

    // Click edit button
    await page.click('button:has-text("編輯資料")');

    // Wait for edit mode to render
    await page.waitForTimeout(500);

    // Get avatar text in edit mode
    const editModeAvatar = await page.locator('[class*="avatar"]').first().textContent();
    console.log('Edit Mode Avatar:', editModeAvatar);

    // Assert: Edit mode avatar matches view mode avatar
    expect(editModeAvatar).toBe(viewModeAvatar);

    // Assert: Avatar is NOT 'U' (unless user has no name)
    if (viewModeAvatar !== 'U') {
      expect(editModeAvatar).not.toBe('U');
    }
  });

  test('should disable edit button during loading', async ({ page }) => {
    // Intercept API to simulate slow loading
    await page.route('**/api/salesperson/profile', async route => {
      await new Promise(resolve => setTimeout(resolve, 2000));
      await route.continue();
    });

    // Reload page
    await page.reload();

    // During loading, edit button should be disabled or not visible
    const editButton = page.locator('button:has-text("編輯資料")');

    // Check if button is disabled (it might not be rendered yet during loading)
    const isDisabled = await editButton.getAttribute('disabled');
    const isVisible = await editButton.isVisible().catch(() => false);

    if (isVisible) {
      expect(isDisabled).not.toBeNull();
    }
  });

  test('should clear avatar preview on cancel', async ({ page }) => {
    await page.waitForLoadState('networkidle');

    // Enter edit mode
    await page.click('button:has-text("編輯資料")');

    // Get original avatar src
    const originalSrc = await page.locator('[class*="avatar"] img').first().getAttribute('src');

    // Upload test image (mock)
    const fileInput = page.locator('input[type="file"]');
    await fileInput.setInputFiles({
      name: 'test.jpg',
      mimeType: 'image/jpeg',
      buffer: Buffer.from('fake-image-data'),
    });

    await page.waitForTimeout(1000);

    // Click cancel
    await page.click('button:has-text("取消")');

    await page.waitForTimeout(500);

    // Get avatar src after cancel
    const afterCancelSrc = await page.locator('[class*="avatar"] img').first().getAttribute('src');

    // Assert: Avatar src returns to original (not Base64 preview)
    expect(afterCancelSrc).toBe(originalSrc);
  });
});
```

**Run E2E Tests**:
```bash
cd /Users/kai/KAA/my_profile/frontend
npx playwright test e2e/dashboard-profile-fix.spec.ts
```

---

## 3. Verification Checklist

### 3.1 Code Quality

- [ ] TypeScript compilation passes (`npm run typecheck`)
- [ ] No ESLint errors (`npm run lint`)
- [ ] Code follows project style guide
- [ ] Indentation consistent (2 spaces)

### 3.2 Functional Testing

- [ ] Edit button disabled during loading
- [ ] Edit button enabled after loading
- [ ] Avatar shows correct name abbreviation in edit mode (not 'U')
- [ ] Avatar preview works when uploading image
- [ ] Avatar preview clears when canceling edit
- [ ] Form fields reset correctly on cancel
- [ ] No console errors or warnings

### 3.3 Edge Cases

- [ ] Works with no profile data (first-time user)
- [ ] Works with partial profile data (e.g., no phone)
- [ ] Works with rapid edit/cancel clicks
- [ ] Works with avatar upload then cancel
- [ ] Works with slow network (loading state)

### 3.4 Cross-Browser

- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest, if on macOS)

### 3.5 Responsive Design

- [ ] Desktop (1920x1080)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

---

## 4. Common Issues and Solutions

### Issue 1: TypeScript Error on `disabled` Prop

**Error**:
```
Property 'disabled' does not exist on type 'IntrinsicAttributes & ButtonProps'
```

**Solution**:
Check Button component definition in `@/components/ui/button.tsx`. Ensure it accepts `disabled` prop:

```tsx
interface ButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  // ... other props
}
```

If `ButtonProps` extends `React.ButtonHTMLAttributes`, it should automatically include `disabled`.

---

### Issue 2: Avatar Still Shows 'U' in Edit Mode

**Symptoms**:
- Fixed code applied
- TypeScript compiles
- But avatar still shows 'U' in edit mode

**Debugging Steps**:

1. **Check if profileData exists**:
   ```tsx
   // Add console log before avatar
   console.log('Profile Data:', profileData);
   console.log('Avatar Fallback:', profileData ? getAvatarFallback(profileData) : 'U');
   ```

2. **Check getAvatarFallback function**:
   ```tsx
   // In @/lib/utils/avatar.ts
   export function getAvatarFallback(data: any): string {
     console.log('getAvatarFallback called with:', data);

     if (data.full_name) {
       console.log('Using full_name:', data.full_name);
       return data.full_name.substring(0, 1).toUpperCase();
     }
     // ... rest of function
   }
   ```

3. **Verify profileData has full_name**:
   - Check API response: `/api/salesperson/profile`
   - Ensure `full_name` field exists and has value

4. **Clear browser cache**:
   - Hard refresh: Cmd+Shift+R (Mac) or Ctrl+Shift+F5 (Windows)
   - Clear site data in DevTools

---

### Issue 3: Edit Button Not Disabled During Loading

**Symptoms**:
- Code applied
- But button still clickable during loading

**Debugging Steps**:

1. **Check if profileLoading is true**:
   ```tsx
   console.log('Profile Loading:', profileLoading);
   ```

2. **Verify button prop**:
   ```tsx
   <Button
     onClick={() => {
       console.log('Button clicked, profileLoading:', profileLoading);
       setEditMode(true);
     }}
     disabled={profileLoading}
   >
     編輯資料
   </Button>
   ```

3. **Check Button component implementation**:
   ```tsx
   // In @/components/ui/button.tsx
   export const Button = ({ disabled, ...props }: ButtonProps) => {
     console.log('Button rendered with disabled:', disabled);
     return <button disabled={disabled} {...props} />;
   };
   ```

4. **Simulate slow loading**:
   ```tsx
   // In useProfile hook or API client
   await new Promise(resolve => setTimeout(resolve, 3000)); // 3 second delay
   ```

---

### Issue 4: Form Not Resetting on Cancel

**Symptoms**:
- Avatar resets correctly
- But form fields still show modified values

**Debugging Steps**:

1. **Check resetProfile() call**:
   ```tsx
   <Button
     onClick={() => {
       console.log('Cancel clicked');
       setEditMode(false);
       setAvatarPreview(null);
       resetProfile(); // ← Ensure this is called
       console.log('Form reset');
     }}
   >
     取消
   </Button>
   ```

2. **Verify useEffect populates form**:
   ```tsx
   useEffect(() => {
     if (profile) {
       console.log('Resetting form with profile:', profile);
       resetProfile({
         full_name: profile.full_name,
         // ... other fields
       });
     }
   }, [profile?.id]);
   ```

---

## 5. Performance Validation

### 5.1 Performance Benchmarks

**Before Fix**:
- Page load: ~2 seconds
- Edit mode entry: <100ms
- Cancel edit: <50ms

**After Fix** (Expected):
- Page load: ~2 seconds (unchanged)
- Edit mode entry: <100ms (unchanged)
- Cancel edit: <50ms (unchanged)

**Validation**:
```bash
# Use Chrome DevTools Performance tab
1. Open DevTools (F12)
2. Go to Performance tab
3. Record page load
4. Record edit mode entry
5. Check for long tasks (>50ms)
```

**Expected**: No new long tasks introduced ✅

---

### 5.2 Memory Leak Check

**Before Fix**: ~50MB heap usage
**After Fix**: ~50MB heap usage (unchanged)

**Validation**:
```bash
# Use Chrome DevTools Memory tab
1. Open DevTools (F12)
2. Go to Memory tab
3. Take heap snapshot before fix
4. Apply fix
5. Take heap snapshot after fix
6. Compare sizes
```

**Expected**: No significant memory increase (<1MB) ✅

---

## 6. Documentation Updates

### 6.1 Update CLAUDE.md (Optional)

Add note about Avatar fallback pattern:

**File**: `frontend/CLAUDE.md`

**Section**: "開發規範" → "組件設計原則"

**Add**:
```markdown
### Avatar Fallback Pattern

When using `getAvatarFallback()` with potentially undefined data:

❌ **Incorrect**:
```tsx
<Avatar fallback={getAvatarFallback(data || {})} />
```

✅ **Correct**:
```tsx
<Avatar fallback={data ? getAvatarFallback(data) : 'U'} />
```

**Reason**: Passing empty object `{}` always returns 'U'. Check data existence first.
```

---

### 6.2 Update Component Documentation

**File**: `openspec/specs/frontend/ui-components.md`

**Section**: "Avatar Component"

**Update**:
```markdown
## Avatar Component

### Props

| Prop | Type | Default | Description |
|------|------|---------|-------------|
| src | string \| null | null | Image URL |
| fallback | string | 'U' | Fallback text when no image |
| size | 'sm' \| 'md' \| 'lg' \| 'xl' \| '2xl' | 'md' | Avatar size |

### Fallback Best Practice

Always check if data exists before calling `getAvatarFallback()`:

```tsx
<Avatar
  src={user?.avatar}
  fallback={user ? getAvatarFallback(user) : 'U'}
/>
```

**Why**: `getAvatarFallback({})` (empty object) always returns 'U' because it has no name properties to extract.
```

---

## 7. Git Workflow

### 7.1 Commit Message

```bash
cd /Users/kai/KAA/my_profile

git add frontend/app/(dashboard)/dashboard/page.tsx

git commit -m "fix: Disable edit button during loading and fix Avatar fallback in edit mode

- Disable edit button when profileLoading is true
- Fix Avatar fallback to check profileData existence before calling getAvatarFallback()
- Prevents empty object being passed to getAvatarFallback() which always returns 'U'

Fixes: Avatar showing 'U' instead of name abbreviation in edit mode
Fixes: Edit button clickable during data loading

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>"
```

**Commit Type**: `fix` (bug fix)
**Scope**: Dashboard profile edit page

---

### 7.2 Branch Strategy

If using feature branches:

```bash
# Create feature branch
git checkout -b fix/dashboard-profile-edit-display

# Make changes and commit
git add .
git commit -m "fix: Dashboard profile edit display issues"

# Push to remote
git push origin fix/dashboard-profile-edit-display

# Create PR
gh pr create --title "Fix: Dashboard profile edit display issues" --body "..."
```

---

## 8. Deployment

### 8.1 Pre-Deployment Checklist

- [ ] All tests passing
- [ ] TypeScript compiles without errors
- [ ] ESLint passes
- [ ] Manual testing completed
- [ ] Browser testing completed
- [ ] Performance validation passed
- [ ] Documentation updated
- [ ] Git commit created
- [ ] PR reviewed and approved (if applicable)

---

### 8.2 Deployment Steps

**Development** (Already done via `npm run dev`)

**Staging**:
```bash
cd /Users/kai/KAA/my_profile/frontend

# Build for staging
npm run build

# Test build locally
npm start

# Verify build works
curl http://localhost:3000/dashboard

# Deploy to staging environment
# (Deployment method depends on your hosting)
```

**Production**:
```bash
# After staging approval
# Deploy build to production
# (Follow your organization's deployment process)
```

---

### 8.3 Post-Deployment Verification

**Smoke Tests** (3-5 minutes):

1. **Basic Flow**:
   - Visit `/dashboard`
   - Wait for page to load
   - Click "編輯資料"
   - Verify avatar shows name (not 'U')
   - Click "取消"
   - Verify form resets

2. **Console Check**:
   - Open DevTools Console
   - Perform all actions
   - Verify no errors

3. **Performance Check**:
   - Check page load time (<3 seconds)
   - Check edit mode transition (<200ms)

---

## 9. Rollback Plan

### 9.1 If Critical Issues Occur

**Symptoms**:
- TypeErrors in console
- Infinite loading state
- Edit mode not working
- Avatar not displaying

**Immediate Action**:
```bash
# Revert commit
git revert <commit-hash>

# Or checkout previous version
git checkout HEAD~1 -- frontend/app/(dashboard)/dashboard/page.tsx

# Rebuild
npm run build

# Redeploy
# (Follow deployment process)
```

---

### 9.2 Rollback Verification

After rollback:
- [ ] Page loads without errors
- [ ] Edit mode works (even if avatar shows 'U')
- [ ] No TypeErrors in console
- [ ] Form save works

---

## 10. Final Checklist

### Before Marking as Complete

- [ ] **Code Changes**: Both fixes applied correctly
- [ ] **TypeScript**: Compiles without errors
- [ ] **ESLint**: No linting errors
- [ ] **Manual Testing**: All scenarios tested
- [ ] **Browser Testing**: Tested on 2+ browsers
- [ ] **Responsive Testing**: Tested on 3 viewport sizes
- [ ] **Console**: No errors or warnings
- [ ] **Performance**: No performance regression
- [ ] **Documentation**: Updated as needed
- [ ] **Git**: Committed with proper message
- [ ] **Specs**: Archived to `openspec/specs/` (after implementation)

---

### Success Indicators

✅ **Primary Goals Achieved**:
1. Edit button disabled during loading
2. Avatar shows correct name abbreviation in edit mode
3. No TypeErrors in console

✅ **Secondary Goals Achieved**:
1. Form reset works correctly on cancel
2. Avatar preview clears on cancel
3. No performance regression

✅ **Quality Goals Achieved**:
1. TypeScript strict mode passes
2. Code follows project style
3. Tests written (if applicable)
4. Documentation updated

---

## 11. Next Steps

After implementation complete:

1. **Move to Archive**:
   ```bash
   /archive fix-dashboard-profile-edit-display
   ```

2. **Update Changelog** (Optional):
   ```markdown
   ## [2026-01-12]

   ### Fixed
   - **Dashboard Profile Edit** (fix-dashboard-profile-edit-display)
     - Fixed edit button enabled during data loading
     - Fixed Avatar showing 'U' instead of name abbreviation in edit mode
     - Improved form initialization and reset behavior
   ```

3. **Close Related Issues** (If using issue tracker):
   - Reference: "Closes #123" in commit message

4. **Notify Team** (If applicable):
   - Post in team channel: "Dashboard profile edit fix deployed ✅"

---

## 12. Contact

**Questions or Issues**:
- **Technical**: Contact Frontend Lead
- **Product**: Contact Product Manager
- **Emergency**: Contact DevOps Team

---

**Implementation Guide Complete** ✅

**Estimated Implementation Time**: 20-30 minutes (including testing)
**Complexity**: Low
**Risk**: Low
**Impact**: Medium (improves UX for all salesperson users)

---

**Next Action**: Execute implementation following this guide step-by-step.
