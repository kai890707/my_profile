# 變更提案：修復 Dashboard 個人資料編輯表單預填問題

**提案日期**: 2026-01-15
**提案者**: Development Team
**狀態**: ✅ 已完成

---

## 1. 變更概述

### 1.1 問題描述

業務員在 Dashboard 頁面點擊「編輯資料」按鈕後，表單欄位顯示為空白，沒有預先填入當前的個人資料。

### 1.2 影響範圍

- **頁面**: `/dashboard` (業務員 Dashboard)
- **功能**: 個人資料編輯
- **使用者**: 所有業務員

### 1.3 優先級

**高** - 影響核心使用者體驗

---

## 2. 根本原因分析

### 2.1 問題 1：API 回應結構不匹配

**Backend API 回應格式**:
```json
{
  "success": true,
  "data": {
    "profile": {
      "id": 1,
      "full_name": "張三",
      "phone": "0912345678",
      ...
    }
  }
}
```

**Frontend useProfile hook 處理**:
```typescript
// ❌ 錯誤：直接返回 response.data
return response.data;  // 返回 { profile: {...} }
```

**結果**: `profile` 變成 `{ profile: {...} }` 而非直接的 profile 物件，導致 `profile.full_name` 為 `undefined`。

### 2.2 問題 2：useEffect 觸發時機不正確

**原始程式碼**:
```typescript
useEffect(() => {
  if (profile) {
    resetProfile({...});
  }
}, [profile?.id]);  // ← 只監聽 profile?.id
```

**問題**: 當 `profile?.id` 在頁面載入時就已存在，後續進入編輯模式時 `useEffect` 不會重新執行，導致 `resetProfile()` 沒有被呼叫。

---

## 3. 解決方案

### 3.1 修復 useProfile hook

**檔案**: `frontend/hooks/useSalesperson.ts`

```typescript
// ✅ 修復後：解包 profile 物件
export function useProfile() {
  return useQuery({
    queryKey: salespersonKeys.profile,
    queryFn: async () => {
      const response = await salespersonApi.getProfile();
      // Backend 返回 { success: true, data: { profile: {...} } }
      // 需要解包取出 profile
      const data = response.data as { profile?: any } | any;
      return data?.profile ?? data;
    },
  });
}
```

### 3.2 修復 useEffect 依賴

**檔案**: `frontend/app/(dashboard)/dashboard/page.tsx`

```typescript
// ✅ 修復後：加入 editMode 依賴
useEffect(() => {
  if (profile && editMode) {  // ← 加入 editMode 條件
    resetProfile({
      full_name: profile.full_name || '',
      phone: profile.phone || '',
      bio: profile.bio || '',
      specialties: profile.specialties || '',
      service_regions: serviceRegions,
    });
  }
}, [profile?.id, editMode]);  // ← 加入 editMode 依賴
```

---

## 4. 驗收標準

- [x] AC-1: 點擊「編輯資料」後，姓名欄位顯示當前姓名
- [x] AC-2: 點擊「編輯資料」後，電話欄位顯示當前電話
- [x] AC-3: 點擊「編輯資料」後，簡介欄位顯示當前簡介
- [x] AC-4: 點擊「編輯資料」後，專長欄位顯示當前專長
- [x] AC-5: 點擊「編輯資料」後，服務地區正確選中
- [x] AC-6: 點擊「取消」後，表單重置為原始值
- [x] AC-7: TypeScript 編譯無錯誤

---

## 5. 變更記錄

| 檔案 | 變更類型 | 說明 |
|------|----------|------|
| `frontend/hooks/useSalesperson.ts` | 修改 | 解包 API 回應中的 profile 物件 |
| `frontend/app/(dashboard)/dashboard/page.tsx` | 修改 | 修正 useEffect 依賴和類型標註 |

---

## 6. 測試結果

- [x] TypeScript 編譯通過
- [x] 檢視模式正確顯示資料
- [x] 編輯模式正確預填資料
- [x] 取消按鈕正確重置表單

---

## 7. Git 記錄

**Commit**: `31067fc5`
**Message**: `fix: Resolve profile edit form not pre-filling current data`
**Branch**: `main`
**Status**: ✅ 已推送至 origin/main
