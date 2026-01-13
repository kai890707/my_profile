# UI/UX Specification: Dashboard Profile Edit Display Fix

**Feature**: 修復 Dashboard 編輯模式資料顯示問題
**Type**: Bug Fix
**Priority**: Medium
**Created**: 2026-01-12
**Owner**: Product Designer

---

## 1. User Flow Overview

### 1.1 Current User Flow (Problematic)

```
1. User lands on /dashboard (View Mode)
   ✅ Avatar shows: Photo OR Name abbreviation OR 'U'

2. User clicks "編輯資料" button
   → editMode = true
   ❌ Avatar shows: 'U' (BUG - should show current avatar/name)

3. User makes changes and clicks "取消"
   → editMode = false
   ✅ Avatar returns to correct display
```

### 1.2 Fixed User Flow (Expected)

```
1. User lands on /dashboard (View Mode)
   ✅ Avatar shows: Photo OR Name abbreviation OR 'U'
   ✅ "編輯資料" button disabled while profileLoading = true

2. Data finishes loading (profileLoading = false)
   ✅ "編輯資料" button becomes enabled

3. User clicks "編輯資料" button
   → editMode = true
   ✅ Avatar shows: Photo OR Name abbreviation OR 'U' (CORRECT)
   ✅ All form fields pre-filled with current values

4. User makes changes and clicks "取消"
   → editMode = false
   ✅ All fields reset to original values immediately
   ✅ Avatar returns to correct display
```

---

## 2. Visual Design Requirements

### 2.1 Avatar Display States

#### State 1: View Mode (Unchanged)
- **Avatar Priority**: Photo → Name abbreviation → 'U'
- **Size**: 2xl (large)
- **Position**: Top of profile card
- **Styling**: Standard Avatar component styles

#### State 2: Edit Mode - Loading
- **Display**: Skeleton loader (if entering edit mode while loading)
- **Duration**: Until profileLoading = false
- **Size**: 2xl
- **Note**: Should not occur after fix (edit button disabled during loading)

#### State 3: Edit Mode - Data Loaded
- **Avatar Priority**:
  1. New uploaded photo preview (if user just uploaded)
  2. Current photo (if exists)
  3. Name abbreviation (from full_name)
  4. Default 'U'
- **Size**: 2xl
- **Behavior**: Immediately reflects any new upload
- **Position**: Same as view mode

### 2.2 Edit Button States

#### State 1: Loading (NEW)
```
Button Appearance:
- Label: "編輯資料"
- State: Disabled
- Cursor: not-allowed
- Opacity: 0.5 (visual indication of disabled state)
- Tooltip: "資料載入中..."
```

#### State 2: Ready (Existing)
```
Button Appearance:
- Label: "編輯資料"
- State: Enabled
- Cursor: pointer
- Opacity: 1.0
- Hover: Background color change
```

### 2.3 Form Field States

#### View Mode
- All fields read-only
- Display current values
- No input borders

#### Edit Mode - Initial Display
- All fields editable
- **CRITICAL**: All fields pre-filled with current values
- Input borders visible
- Placeholder text hidden (since fields have values)

#### Edit Mode - After Changes
- Modified fields highlighted (optional)
- Validation errors shown inline
- Character counters active

---

## 3. Interaction Requirements

### 3.1 Page Load Interaction

```
Timeline:
0ms     → Page renders with profileLoading = true
        → Show skeleton loaders
        → "編輯資料" button disabled

100ms   → useProfile() hook fetches data

500ms   → Data received, profileLoading = false
        → Hide skeleton loaders
        → Display actual data
        → "編輯資料" button enabled
```

### 3.2 Enter Edit Mode Interaction

```
User Action: Click "編輯資料" button

Pre-condition Check:
✅ profileLoading = false (button only enabled if true)
✅ profileData exists and is complete

Action Sequence:
1. setEditMode(true)
2. Form fields populate with current values (via useEffect)
3. Avatar displays with correct fallback
4. Focus first editable field (optional UX enhancement)
5. Show "取消" and "儲存變更" buttons
```

### 3.3 Avatar Upload Interaction (Edit Mode)

```
User Action: Click avatar to upload new photo

Action Sequence:
1. File picker opens
2. User selects image
3. avatarPreview updates with new image data URL
4. Avatar component immediately shows preview
5. File stored in form state (not saved yet)
```

### 3.4 Cancel Edit Interaction

```
User Action: Click "取消" button

Action Sequence:
1. setEditMode(false)
2. All form fields reset to original values (via reset())
3. avatarPreview cleared (set to null)
4. Avatar returns to original display
5. No confirmation dialog (per user requirement)
6. Form validation errors cleared
```

### 3.5 Save Changes Interaction (Out of Scope)

_Note: Existing save functionality unchanged in this fix_

---

## 4. Error States and Edge Cases

### 4.1 Error State: No Profile Data

**Scenario**: profileData is null/undefined after loading completes

**Display**:
- Avatar: Show 'U' (default fallback)
- Fields: Show empty with placeholders
- Edit button: Enabled (allow user to add data)

### 4.2 Error State: Partial Profile Data

**Scenario**: Some fields missing (e.g., no full_name but has email)

**Display**:
- Avatar: Use next available fallback (email → 'U')
- Populated fields: Show current values
- Missing fields: Show placeholders
- Edit button: Enabled

### 4.3 Error State: Loading Timeout

**Scenario**: Data fails to load after 10 seconds

**Display**:
- Show error message: "資料載入失敗，請重新整理頁面"
- Edit button: Remains disabled
- Avatar: Show 'U' (default)

### 4.4 Edge Case: Rapid Cancel/Edit

**Scenario**: User clicks "編輯資料" → "取消" → "編輯資料" rapidly

**Expected Behavior**:
- Each cancel fully resets form state
- Each edit mode entry re-populates with current data
- No state corruption or stale data

---

## 5. Accessibility Requirements

### 5.1 Keyboard Navigation

- ✅ Tab order: "編輯資料" button → Form fields → "取消"/"儲存" buttons
- ✅ Enter key: Submit form (when in edit mode)
- ✅ Escape key: Cancel edit mode (optional enhancement)

### 5.2 Screen Reader Announcements

```
When edit button becomes enabled:
"編輯資料按鈕已啟用"

When entering edit mode:
"進入編輯模式，表單已預填您的個人資料"

When canceling edit:
"已取消編輯，返回檢視模式"
```

### 5.3 Focus Management

- After entering edit mode: Focus first editable field
- After canceling edit: Focus returns to "編輯資料" button
- After saving: Focus appropriate element based on result

---

## 6. Responsive Design

### 6.1 Desktop (≥1024px)
- Avatar: 2xl size
- Form: 2-column layout (existing)
- Edit button: Full width in header
- All features fully functional

### 6.2 Tablet (768px - 1023px)
- Avatar: 2xl size
- Form: 2-column layout (may stack on smaller tablets)
- Edit button: Full width
- All features fully functional

### 6.3 Mobile (<768px)
- Avatar: xl size (smaller for mobile)
- Form: Single column layout
- Edit button: Full width
- All features fully functional

---

## 7. Performance Requirements

### 7.1 Loading Performance

- Skeleton display: <50ms after page load
- Data fetch: Complete within 2 seconds (normal conditions)
- Edit mode transition: <100ms (instant feel)

### 7.2 Interaction Performance

- Button state change: Immediate (<16ms)
- Avatar preview update: <100ms after file selection
- Form reset: Instant (<50ms)

---

## 8. Visual Design Mockups

### Mockup 1: View Mode (Loading)

```
┌─────────────────────────────────────┐
│ Dashboard                           │
├─────────────────────────────────────┤
│                                     │
│   ┌───────────────────┐            │
│   │                   │            │
│   │   [Skeleton]      │            │
│   │   Loading...      │            │
│   │                   │            │
│   └───────────────────┘            │
│                                     │
│   [Skeleton Name]                  │
│   [Skeleton Email]                 │
│                                     │
│   [編輯資料] (Disabled, Gray)       │
│                                     │
└─────────────────────────────────────┘
```

### Mockup 2: View Mode (Data Loaded)

```
┌─────────────────────────────────────┐
│ Dashboard                           │
├─────────────────────────────────────┤
│                                     │
│   ┌───────────────────┐            │
│   │                   │            │
│   │       張三        │   ← Avatar shows name abbreviation
│   │    (or Photo)     │            │
│   │                   │            │
│   └───────────────────┘            │
│                                     │
│   姓名: 張三                        │
│   Email: zhang@example.com         │
│   電話: 0912-345-678               │
│                                     │
│   [編輯資料] (Enabled, Primary)    │
│                                     │
└─────────────────────────────────────┘
```

### Mockup 3: Edit Mode (Correct Display)

```
┌─────────────────────────────────────┐
│ Dashboard                           │
├─────────────────────────────────────┤
│                                     │
│   ┌───────────────────┐            │
│   │                   │            │
│   │       張三        │   ← Avatar CORRECTLY shows name
│   │    (or Photo)     │            │
│   │   [Click Upload]  │            │
│   └───────────────────┘            │
│                                     │
│   姓名: [張三          ]  ← Pre-filled
│   Email: [zhang@example.com]       │
│   電話: [0912-345-678 ]            │
│                                     │
│   [取消]  [儲存變更]                │
│                                     │
└─────────────────────────────────────┘
```

### Mockup 4: Edit Mode (Bug - OLD Behavior)

```
┌─────────────────────────────────────┐
│ Dashboard                           │
├─────────────────────────────────────┤
│                                     │
│   ┌───────────────────┐            │
│   │                   │            │
│   │        U          │   ← BUG: Shows 'U' instead of name
│   │                   │            │
│   │   [Click Upload]  │            │
│   └───────────────────┘            │
│                                     │
│   姓名: [張三          ]            │
│   Email: [zhang@example.com]       │
│   電話: [0912-345-678 ]            │
│                                     │
│   [取消]  [儲存變更]                │
│                                     │
└─────────────────────────────────────┘
```

---

## 9. Animation Requirements

### 9.1 Edit Mode Transition
- **Duration**: 200ms
- **Easing**: ease-in-out
- **Properties**: Opacity fade-in for edit controls

### 9.2 Avatar Change
- **Duration**: 150ms
- **Easing**: ease-out
- **Properties**: Opacity fade when switching between view/edit mode

### 9.3 Button State Change
- **Duration**: 100ms
- **Easing**: ease-in-out
- **Properties**: Opacity, background color for disabled state

---

## 10. Success Criteria

### Visual Verification Checklist

- [ ] View mode avatar displays correctly (Photo → Name → 'U')
- [ ] Edit button disabled during loading
- [ ] Edit button enabled after loading completes
- [ ] Edit mode avatar displays correctly (same as view mode)
- [ ] Edit mode form fields pre-filled with current values
- [ ] Cancel button resets all fields immediately
- [ ] No visual glitches during transitions
- [ ] Responsive design works on all screen sizes
- [ ] Loading skeleton displays during data fetch

### Interaction Verification Checklist

- [ ] Cannot enter edit mode during loading
- [ ] Edit mode shows correct avatar fallback
- [ ] Avatar upload preview works in edit mode
- [ ] Cancel resets avatar preview
- [ ] Keyboard navigation works correctly
- [ ] Screen reader announcements accurate
- [ ] No console errors or warnings

---

## 11. Design Tokens (Reference)

### Colors
- Primary Button: `bg-primary-600 hover:bg-primary-700`
- Disabled Button: `bg-gray-300 cursor-not-allowed opacity-50`
- Avatar Fallback Background: `bg-primary-100`
- Avatar Fallback Text: `text-primary-700`

### Spacing
- Avatar size 2xl: `h-24 w-24` (96px)
- Avatar size xl: `h-20 w-20` (80px)
- Button padding: `px-4 py-2`
- Form field spacing: `mb-4`

### Typography
- Avatar fallback text: `text-3xl font-semibold`
- Field labels: `text-sm font-medium text-gray-700`
- Field values: `text-base text-gray-900`

---

## 12. Out of Scope (Not Changed)

- ❌ View mode display logic (already working)
- ❌ Save changes functionality (already working)
- ❌ Form validation rules (already working)
- ❌ API integration (already working)
- ❌ Avatar upload mechanism (already working)

---

**Status**: ✅ Specification Complete
**Next Step**: Component Specification (components.md)
**Owner**: Product Designer
**Reviewers**: Frontend Developer, Tech Lead
