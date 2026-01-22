# Avatar E2E Tests Summary

**Date**: 2026-01-22
**Phase**: 5 - E2E Testing
**Status**: ✅ Tests Written (Execution pending frontend implementation)

---

## Test File Created

**Location**: `/Users/kai/KAA/my_profile/frontend/tests/e2e/avatar-upload.spec.ts`

**Total Test Cases**: 15
**Test Suites**: 7

---

## Test Coverage

### 1. Avatar Upload Flow (3 tests)

| Test Case | Description | Expected Outcome |
|-----------|-------------|------------------|
| `salesperson can upload avatar successfully` | Upload valid PNG/JPEG avatar | Avatar displayed, data URL verified |
| `avatar persists after page reload` | Reload page after upload | Avatar remains visible |
| `avatar persists after logout and login` | Logout and login again | Avatar still displayed |

**Purpose**: Verify core upload functionality and data persistence.

---

### 2. Avatar Delete Flow (1 test)

| Test Case | Description | Expected Outcome |
|-----------|-------------|------------------|
| `salesperson can delete avatar` | Delete uploaded avatar | Avatar removed, default shown |

**Purpose**: Verify deletion functionality works correctly.

---

### 3. Avatar Validation (3 tests)

| Test Case | Description | Expected Outcome |
|-----------|-------------|------------------|
| `should show error for oversized file (>2MB)` | Upload file >2MB | 400 error, error message shown |
| `should reject SVG files (XSS protection)` | Upload SVG with script | 422 error, validation failure |
| `should show error for invalid data URL format` | Upload invalid data URL | 422 error, error message |

**Purpose**: Verify all backend validation rules are enforced in UI.

---

### 4. Avatar Display (3 tests)

| Test Case | Description | Expected Outcome |
|-----------|-------------|------------------|
| `avatar should be displayed in header` | Check header on homepage | Avatar visible in header |
| `avatar should be displayed on all pages` | Check 4 different pages | Avatar visible on all pages |
| `avatar should be displayed in salesperson profile` | Check dashboard profile | Profile avatar visible |

**Purpose**: Verify avatar is displayed consistently across the application.

---

### 5. Responsive Design (2 tests)

| Test Case | Description | Expected Outcome |
|-----------|-------------|------------------|
| `avatar upload should work on mobile viewport` | Upload on 375x667 viewport | Upload works, avatar visible |
| `avatar should be displayed correctly on tablet viewport` | Check on 768x1024 viewport | Avatar displayed properly |

**Purpose**: Verify responsive design works on mobile and tablet.

---

### 6. Performance (2 tests)

| Test Case | Description | Expected Outcome |
|-----------|-------------|------------------|
| `avatar upload should complete within 3 seconds` | Measure upload time | <3 seconds |
| `avatar image should load quickly` | Measure initial load time | <2 seconds |

**Purpose**: Verify upload and display performance meets targets.

---

### 7. User Roles (1 test)

| Test Case | Description | Expected Outcome |
|-----------|-------------|------------------|
| `regular user should not have avatar upload functionality` | Try to access as regular user | No upload UI, no dashboard access |

**Purpose**: Verify role-based access control.

---

## Test Infrastructure

### Helper Functions

```typescript
// Login helper
async function loginAsSalesperson(page: Page)

// Screenshot helper
async function saveScreenshot(page: Page, name: string)

// Image generation helpers
function createTestImageDataUrl(width: number, height: number, format: 'png' | 'jpeg'): string
function createOversizedDataUrl(): string

// API wait helper
async function waitForApiResponse(page: Page, endpoint: string)
```

### Configuration

- **BASE_URL**: `http://localhost:3001` (Frontend)
- **API_URL**: `http://localhost:8080` (Backend)
- **Test User**: `salesperson@example.com` / `password123`

### Screenshot Storage

Screenshots saved to: `/tmp/avatar-upload-{test-name}-{timestamp}.png`

---

## Test Approach

### File Upload Simulation

The tests use custom events to simulate file uploads:

```typescript
await page.evaluate((dataUrl) => {
  window.dispatchEvent(new CustomEvent('avatar-upload', {
    detail: { avatar: dataUrl }
  }));
}, testImageDataUrl);
```

**Rationale**:
- Playwright can handle real file uploads via `setInputFiles()`
- Custom events allow testing before full UI implementation
- Tests can be updated to use real file uploads once UI is ready

### Alternative Approach (For Real Files)

```typescript
// Future enhancement: Use Playwright file upload API
await page.locator('input[type="file"]').setInputFiles({
  name: 'avatar.png',
  mimeType: 'image/png',
  buffer: Buffer.from(imageDataUrl.split(',')[1], 'base64')
});
```

---

## Integration with Existing Tests

### Existing E2E Tests

- **header-auth.spec.ts**: 28 tests for authentication and header
- **avatar-upload.spec.ts**: 15 tests for avatar functionality

**Total E2E Coverage**: 43 tests

### Test Execution

```bash
# Run all E2E tests
cd frontend
npx playwright test

# Run only avatar tests
npx playwright test avatar-upload

# Run with UI
npx playwright test --ui

# Run in headed mode (see browser)
npx playwright test --headed
```

---

## Dependencies

### Frontend Implementation Required

The E2E tests assume the following frontend components exist:

1. **Avatar Upload Component**
   - Location: Dashboard profile page
   - Upload button or file input
   - Preview of current avatar

2. **Avatar Display Component**
   - Header avatar (all pages)
   - Profile avatar (dashboard)

3. **Avatar Delete Button**
   - Delete or remove avatar action
   - Confirmation dialog (optional)

4. **Error Handling**
   - Error messages for validation failures
   - Toast notifications or inline errors

### Backend API Endpoints (Already Implemented)

- ✅ `POST /api/salesperson/avatar` - Upload avatar
- ✅ `DELETE /api/salesperson/avatar` - Delete avatar
- ✅ Rate limiting (10 req/min)
- ✅ Validation (VR-001 to VR-005)

---

## Execution Status

### Current State

- ✅ Tests written and structured
- ✅ Helper functions created
- ✅ 15 comprehensive test cases
- ⏳ **Execution pending**: Frontend avatar UI implementation

### Next Steps

1. **Frontend Implementation**:
   - Create Avatar Upload Component
   - Add Avatar Display to Header and Profile
   - Implement error handling and loading states

2. **Update Tests** (if needed):
   - Replace custom events with real file upload API
   - Update selectors to match actual DOM structure
   - Add data-testid attributes for reliable selection

3. **Execute Tests**:
   - Run against development environment
   - Fix any failures
   - Capture test results

4. **CI/CD Integration**:
   - Add to GitHub Actions workflow
   - Run on every PR
   - Screenshot artifacts for debugging

---

## Test Maintenance

### Updating Tests

When frontend implementation changes:

1. **Update Selectors**:
   ```typescript
   // Update to match actual DOM
   const avatarUpload = page.locator('[data-testid="avatar-upload"]');
   ```

2. **Update Event Handling**:
   ```typescript
   // Use real file upload if implemented
   await fileInput.setInputFiles('./test-images/avatar.png');
   ```

3. **Update Assertions**:
   ```typescript
   // Adjust based on actual error messages
   await expect(errorMessage).toContain('檔案過大');
   ```

### Adding New Tests

Follow the existing pattern:

```typescript
test.describe('New Test Suite', () => {
  test('new test case', async ({ page }) => {
    await loginAsSalesperson(page);
    await page.goto(`${BASE_URL}/path`);

    // Test logic

    await saveScreenshot(page, 'test-name');
  });
});
```

---

## Performance Targets

### Test Execution Time

- **Target**: <2 minutes for all 15 tests
- **Timeout**: 10 seconds per test (configurable)

### CI/CD Performance

- **Parallel Execution**: Run tests in parallel (3 workers)
- **Retry**: Retry failed tests up to 2 times
- **Artifacts**: Save screenshots and videos on failure

---

## Accessibility Testing

**Future Enhancement**: Add accessibility checks

```typescript
test('avatar upload should be accessible', async ({ page }) => {
  // Run axe-core accessibility tests
  await page.goto(`${BASE_URL}/dashboard`);
  const accessibilityScanResults = await new AxeBuilder({ page }).analyze();
  expect(accessibilityScanResults.violations).toEqual([]);
});
```

---

## Known Limitations

1. **File Upload Simulation**:
   - Currently uses custom events instead of real file uploads
   - Will be updated once frontend UI is implemented

2. **API Mocking**:
   - Tests hit real backend API
   - Could add Playwright request interception for isolated tests

3. **Screenshot Storage**:
   - Screenshots saved to `/tmp`
   - Consider using Playwright's built-in artifact storage

---

## Recommendations

### For Production

1. **Add Visual Regression Testing**:
   - Use Playwright's screenshot comparison
   - Detect unintended UI changes

2. **Add Load Testing**:
   - Test with multiple concurrent uploads
   - Verify rate limiting works

3. **Add Cross-Browser Testing**:
   - Test in Chrome, Firefox, Safari, Edge
   - Verify consistent behavior

### For Development

1. **Add Interactive Mode**:
   - Use Playwright Test UI for debugging
   - `npx playwright test --ui`

2. **Add Trace Viewer**:
   - Record traces for failed tests
   - `npx playwright test --trace on`

3. **Add Screenshot Comparison**:
   - Compare before/after upload
   - Verify visual changes

---

## Conclusion

✅ **Phase 5 E2E Testing: COMPLETE (Tests Written)**

- **15 comprehensive E2E test cases** covering all avatar functionality
- **7 test suites** organized by feature area
- **Ready to execute** once frontend avatar UI is implemented
- **Follows project patterns** (matches existing header-auth tests)
- **Well documented** with helpers and configuration

**Next Phase**: Phase 6 - Integration Testing & Final Report

---

## References

- Existing E2E Tests: `/frontend/tests/e2e/header-auth.spec.ts`
- Playwright Docs: https://playwright.dev
- Test Best Practices: `/frontend/docs/testing.md`
