# Tasks: Dashboard Profile Edit Display Fix

**Feature**: 修復 Dashboard 編輯模式資料顯示問題
**Type**: Bug Fix
**Priority**: Medium
**Created**: 2026-01-12
**Owner**: Frontend Developer

---

## Task Overview

**Total Tasks**: 7
**Estimated Time**: 30 minutes
**Files Modified**: 1

---

## Task Breakdown

### Task 1: Apply Edit Button Disable Logic ⚡ Priority

**Description**: Add `disabled` prop to edit button based on `profileLoading` state

**File**: `frontend/app/(dashboard)/dashboard/page.tsx`
**Location**: Line 197-199

**Changes**:
```tsx
// Before
{!editMode && (
  <Button onClick={() => setEditMode(true)}>編輯資料</Button>
)}

// After
{!editMode && (
  <Button
    onClick={() => setEditMode(true)}
    disabled={profileLoading}
  >
    編輯資料
  </Button>
)}
```

**Acceptance Criteria**:
- [ ] Edit button has `disabled` prop
- [ ] `disabled={profileLoading}` correctly passed
- [ ] TypeScript compiles without errors

**Estimated Time**: 2 minutes

---

### Task 2: Apply Avatar Fallback Fix ⚡ Priority

**Description**: Fix Avatar fallback logic to check `profileData` existence before calling `getAvatarFallback()`

**File**: `frontend/app/(dashboard)/dashboard/page.tsx`
**Location**: Line 272

**Changes**:
```tsx
// Before
<Avatar
  src={avatarPreview || profileData?.avatar}
  fallback={getAvatarFallback(profileData || {})}
  size="2xl"
/>

// After
<Avatar
  src={avatarPreview || profileData?.avatar}
  fallback={profileData ? getAvatarFallback(profileData) : 'U'}
  size="2xl"
/>
```

**Acceptance Criteria**:
- [ ] Fallback uses ternary check: `profileData ? getAvatarFallback(profileData) : 'U'`
- [ ] No longer passes empty object `{}`
- [ ] TypeScript compiles without errors

**Estimated Time**: 2 minutes

---

### Task 3: TypeScript Type Check

**Description**: Verify TypeScript compilation passes after code changes

**Command**:
```bash
cd /Users/kai/KAA/my_profile/frontend
npm run typecheck
```

**Acceptance Criteria**:
- [ ] No TypeScript errors
- [ ] No TypeScript warnings
- [ ] All types correctly inferred

**Expected Output**:
```
✓ TypeScript check passed
No errors found
```

**Estimated Time**: 1 minute

---

### Task 4: Manual Testing - Loading State

**Description**: Test that edit button is disabled during data loading

**Steps**:
1. Open browser: `http://localhost:3001/dashboard`
2. Hard refresh (Cmd+Shift+R / Ctrl+Shift+F5)
3. Observe loading skeleton
4. Verify "編輯資料" button not visible or disabled
5. After data loads, verify button becomes enabled

**Acceptance Criteria**:
- [ ] Button disabled or hidden during loading
- [ ] Button enabled after `profileLoading = false`
- [ ] Visual indication of disabled state (gray, low opacity)

**Estimated Time**: 3 minutes

---

### Task 5: Manual Testing - Avatar Display

**Description**: Test that Avatar shows correct name abbreviation in edit mode

**Steps**:
1. Navigate to `/dashboard` with data loaded
2. Observe avatar in view mode (should show name abbreviation or photo)
3. Click "編輯資料" button
4. Observe avatar in edit mode
5. Verify avatar matches view mode (NOT 'U')

**Test Cases**:
- **TC1**: User with `full_name = '測試業務員'`
  - Expected: Avatar shows '測' in both view and edit mode ✅
- **TC2**: User with no `full_name` but has `email = 'test@example.com'`
  - Expected: Avatar shows 'T' (from email) ✅
- **TC3**: First-time user with no data
  - Expected: Avatar shows 'U' (default) ✅

**Acceptance Criteria**:
- [ ] Avatar in edit mode shows name abbreviation (not default 'U')
- [ ] Avatar in edit mode matches view mode
- [ ] No console errors

**Estimated Time**: 5 minutes

---

### Task 6: Manual Testing - Cancel Behavior

**Description**: Test that cancel button correctly resets form and clears avatar preview

**Steps**:
1. Enter edit mode
2. (Optional) Upload a test avatar image
3. Modify form fields (e.g., change phone number)
4. Click "取消" button
5. Verify:
   - Returns to view mode
   - Avatar preview cleared (shows original)
   - Form fields reset to original values

**Acceptance Criteria**:
- [ ] Cancel button returns to view mode
- [ ] Avatar preview cleared (`avatarPreview = null`)
- [ ] All form fields reset to original values
- [ ] No confirmation dialog (per user requirement)

**Estimated Time**: 3 minutes

---

### Task 7: Console Error Check

**Description**: Verify no TypeErrors or other errors in browser console

**Steps**:
1. Open Browser DevTools (F12)
2. Go to Console tab
3. Clear console
4. Perform all actions:
   - Load dashboard
   - Enter edit mode
   - Upload avatar (optional)
   - Cancel edit
   - Re-enter edit mode
5. Review console for errors

**Acceptance Criteria**:
- [ ] No TypeErrors
- [ ] No "Cannot read properties of undefined" errors
- [ ] No "substring is not a function" errors
- [ ] No React warnings

**Estimated Time**: 3 minutes

---

## Additional Tasks (Optional)

### Task 8: Browser Compatibility Testing (Optional)

**Description**: Test on multiple browsers

**Browsers**:
- [ ] Chrome/Edge (latest)
- [ ] Firefox (latest)
- [ ] Safari (latest, if on macOS)

**Estimated Time**: 5 minutes

---

### Task 9: Responsive Design Testing (Optional)

**Description**: Test on multiple viewport sizes

**Viewports**:
- [ ] Desktop (1920x1080)
- [ ] Tablet (768x1024)
- [ ] Mobile (375x667)

**Estimated Time**: 5 minutes

---

### Task 10: E2E Test Creation (Optional)

**Description**: Create automated E2E test for this fix

**File**: `frontend/e2e/dashboard-profile-fix.spec.ts`

**Test Cases**:
```typescript
test('should show correct avatar in edit mode', async ({ page }) => {
  // Login
  // Navigate to dashboard
  // Click edit button
  // Assert avatar shows name abbreviation
});

test('should disable edit button during loading', async ({ page }) => {
  // Simulate slow API
  // Reload dashboard
  // Assert edit button disabled
});

test('should clear avatar preview on cancel', async ({ page }) => {
  // Enter edit mode
  // Upload image
  // Click cancel
  // Assert preview cleared
});
```

**Acceptance Criteria**:
- [ ] All tests pass
- [ ] Tests cover key scenarios

**Estimated Time**: 15 minutes

---

## Task Dependencies

```
Task 1 (Edit Button) ────┐
                          ├──> Task 3 (TypeScript Check)
Task 2 (Avatar Fallback) ─┘                │
                                            ↓
                          ┌──> Task 4 (Loading Test)
                          │
                          ├──> Task 5 (Avatar Test)
Task 3 (TypeScript) ──────┤
                          ├──> Task 6 (Cancel Test)
                          │
                          └──> Task 7 (Console Check)
                                            │
                                            ↓
                          ┌─────────────────┴──────────────────┐
                          │                                     │
                          ↓                                     ↓
            Task 8 (Browser Testing)           Task 10 (E2E Tests)
                          │                                     │
                          └─────────────────┬──────────────────┘
                                            ↓
                          Task 9 (Responsive Testing)
```

---

## Task Execution Order

### Phase 1: Code Changes (Required)
1. Task 1: Apply Edit Button Disable Logic
2. Task 2: Apply Avatar Fallback Fix
3. Task 3: TypeScript Type Check

**Time**: 5 minutes

---

### Phase 2: Manual Testing (Required)
4. Task 4: Manual Testing - Loading State
5. Task 5: Manual Testing - Avatar Display
6. Task 6: Manual Testing - Cancel Behavior
7. Task 7: Console Error Check

**Time**: 14 minutes

---

### Phase 3: Additional Testing (Optional)
8. Task 8: Browser Compatibility Testing
9. Task 9: Responsive Design Testing
10. Task 10: E2E Test Creation

**Time**: 25 minutes (if all optional tasks completed)

---

## Completion Criteria

### Minimum Requirements (Tasks 1-7)
- ✅ Edit button disabled during loading
- ✅ Avatar shows correct fallback in edit mode
- ✅ TypeScript compiles without errors
- ✅ Manual testing passes for all key scenarios
- ✅ No console errors

**Status**: Ready for deployment after completing Tasks 1-7

---

### Full Completion (Tasks 1-10)
- ✅ All minimum requirements met
- ✅ Browser compatibility verified
- ✅ Responsive design tested
- ✅ E2E tests written and passing

**Status**: Production-ready with comprehensive test coverage

---

## Time Estimates

| Phase | Tasks | Estimated Time |
|-------|-------|----------------|
| **Phase 1: Code Changes** | 1-3 | 5 minutes |
| **Phase 2: Manual Testing** | 4-7 | 14 minutes |
| **Phase 3: Optional Testing** | 8-10 | 25 minutes |
| **Total (Required)** | 1-7 | **19 minutes** |
| **Total (Full)** | 1-10 | **44 minutes** |

---

## Task Status Tracking

### In Progress
- [ ] Task 1: Apply Edit Button Disable Logic
- [ ] Task 2: Apply Avatar Fallback Fix
- [ ] Task 3: TypeScript Type Check
- [ ] Task 4: Manual Testing - Loading State
- [ ] Task 5: Manual Testing - Avatar Display
- [ ] Task 6: Manual Testing - Cancel Behavior
- [ ] Task 7: Console Error Check

### Optional (Not Started)
- [ ] Task 8: Browser Compatibility Testing
- [ ] Task 9: Responsive Design Testing
- [ ] Task 10: E2E Test Creation

### Completed
(None yet - to be updated during implementation)

---

## Blockers and Risks

### Potential Blockers
- ❌ **Dev server not running** → Start with `npm run dev`
- ❌ **TypeScript errors** → Review syntax and types
- ❌ **API not responding** → Check Backend server running on port 8080
- ❌ **Authentication issues** → Use test credentials: salesperson@example.com / test123

### Risk Mitigation
- **Low Risk**: Only 2 lines of code changed
- **Easy Rollback**: Can revert commit quickly if issues occur
- **Well-Tested**: Manual testing covers all scenarios

---

## Post-Implementation Tasks

After all tasks complete:

1. **Git Commit**:
   ```bash
   git add frontend/app/(dashboard)/dashboard/page.tsx
   git commit -m "fix: Disable edit button during loading and fix Avatar fallback in edit mode"
   ```

2. **Update Documentation**:
   - Update `openspec/specs/frontend/ui-components.md` with Avatar fallback pattern

3. **Archive Specifications**:
   ```bash
   /archive fix-dashboard-profile-edit-display
   ```

4. **Create PR** (if using feature branch):
   ```bash
   gh pr create --title "Fix: Dashboard profile edit display issues"
   ```

5. **Deploy to Staging**:
   - Build: `npm run build`
   - Deploy to staging environment
   - Verify on staging

6. **Deploy to Production**:
   - After staging approval
   - Deploy build to production
   - Monitor for errors

---

## Success Metrics

### Functional Metrics
- ✅ 0 TypeErrors in console
- ✅ Edit button disabled rate: 100% during loading
- ✅ Avatar correct display rate: 100% in edit mode
- ✅ Form reset success rate: 100% on cancel

### Performance Metrics
- ✅ Page load time: <2 seconds (unchanged)
- ✅ Edit mode transition: <100ms (unchanged)
- ✅ Cancel action: <50ms (unchanged)

### Quality Metrics
- ✅ TypeScript strict mode: Passes
- ✅ ESLint: No errors
- ✅ Code review: Approved (if applicable)
- ✅ User feedback: Positive

---

## Notes

- **Priority**: Medium (improves UX but not critical)
- **Impact**: Medium (affects all salesperson users)
- **Complexity**: Low (only 2 lines of code)
- **Risk**: Low (easy to understand and test)
- **Rollback**: Easy (can revert quickly if needed)

---

**Tasks Ready for Execution** ✅

**Next Action**: Begin Task 1 - Apply Edit Button Disable Logic
