# Implementation Tasks

**功能**: 改善業務員頁面工作經驗與專業證照呈現
**日期**: 2026-01-20
**預估時間**: 2-3 小時

---

## 📋 任務總覽

### Frontend Tasks (7 個主要任務)

所有任務可並行開發，但建議依序執行以確保品質。

---

## 🔨 開發任務

### Task 1: 建立 EmptyState 共用組件
**優先級**: High (其他組件依賴)
**預估時間**: 15 分鐘
**檔案**: `frontend/components/ui/empty-state.tsx`

**描述**:
- 建立可複用的空狀態組件
- 支援自定義圖標、標題、描述

**Acceptance Criteria**:
- [ ] 組件接受 icon, title, description props
- [ ] 支援自定義樣式
- [ ] 響應式設計
- [ ] TypeScript 類型完整

---

### Task 2: 建立 ExperienceItem 組件
**優先級**: High
**預估時間**: 30 分鐘
**檔案**: `frontend/components/features/salesperson/experience-item.tsx`

**描述**:
- 單個工作經驗項目組件
- 支援展開/收合詳細描述
- 顯示驗證標記
- 計算年資

**Acceptance Criteria**:
- [ ] Props: experience (Experience 類型)
- [ ] 展開/收合邏輯 (useState)
- [ ] 驗證標記顯示 (approval_status === 'approved')
- [ ] 日期格式化 (formatDate)
- [ ] 年資計算
- [ ] 過渡動畫 (transition-all duration-200)
- [ ] 響應式設計
- [ ] 無障礙屬性 (ARIA)

---

### Task 3: 建立 ExperienceTimeline 組件
**優先級**: High
**預估時間**: 45 分鐘
**檔案**: `frontend/components/features/salesperson/experience-timeline.tsx`

**描述**:
- 工作經驗時間軸容器組件
- 垂直時間軸設計
- 依時間排序
- 空狀態處理
- Loading 骨架屏

**Acceptance Criteria**:
- [ ] Props: experiences (Experience[]), isLoading (boolean)
- [ ] 依 start_date 倒序排列 (最新在上)
- [ ] 時間軸線視覺設計
- [ ] 時間節點標記 (圓點)
- [ ] 空狀態顯示 EmptyState
- [ ] Loading 骨架屏 (animate-pulse)
- [ ] 響應式時間軸設計
- [ ] 使用 ExperienceItem 渲染項目

---

### Task 4: 建立 CertificationCard 組件
**優先級**: High
**預估時間**: 30 分鐘
**檔案**: `frontend/components/features/salesperson/certification-card.tsx`

**描述**:
- 單張專業證照卡片組件
- 支援展開/收合描述
- 徽章式設計
- 驗證標記
- 證書查看按鈕

**Acceptance Criteria**:
- [ ] Props: certification (Certification 類型)
- [ ] 展開/收合邏輯
- [ ] 徽章圖標顯示
- [ ] 驗證標記 (CheckCircle2)
- [ ] 過期狀態檢查
- [ ] 證書查看按鈕 (如有 file_url)
- [ ] Hover 效果 (border-primary-300)
- [ ] 響應式設計

---

### Task 5: 建立 CertificationCards 組件
**優先級**: High
**預估時間**: 45 分鐘
**檔案**: `frontend/components/features/salesperson/certification-cards.tsx`

**描述**:
- 專業證照容器組件
- 2欄網格佈局
- 篩選功能 (已驗證/全部)
- 排序功能 (發證日期)
- 空狀態處理
- Loading 骨架屏

**Acceptance Criteria**:
- [ ] Props: certifications (Certification[]), isLoading (boolean)
- [ ] 篩選 UI (Tabs: 全部/已驗證)
- [ ] 篩選邏輯 (filter by approval_status)
- [ ] 依 issue_date 倒序排列
- [ ] 網格佈局 (grid md:grid-cols-2 gap-4)
- [ ] 空狀態顯示 EmptyState
- [ ] Loading 骨架屏
- [ ] 響應式佈局 (Mobile 單欄)
- [ ] 使用 CertificationCard 渲染項目

---

### Task 6: 更新業務員詳情頁面
**優先級**: High (整合所有組件)
**預估時間**: 30 分鐘
**檔案**: `frontend/app/salesperson/[id]/page.tsx`

**描述**:
- 整合新的時間軸和卡片組件
- 替換現有的工作經驗和證照區塊
- 保持其他功能不變

**Acceptance Criteria**:
- [ ] Import 新組件
- [ ] 替換工作經驗區塊 (使用 ExperienceTimeline)
- [ ] 替換專業證照區塊 (使用 CertificationCards)
- [ ] 傳遞 isLoading 狀態
- [ ] 保留其他現有功能
- [ ] 確認 useSalespersonDetail hook 正常運作
- [ ] 響應式佈局正確

**變更範圍**:
- Line 186-230: 工作經驗區塊
- Line 232-280: 專業證照區塊

---

### Task 7: 撰寫組件測試
**優先級**: Medium
**預估時間**: 30 分鐘
**檔案**:
- `frontend/components/features/salesperson/__tests__/experience-timeline.test.tsx`
- `frontend/components/features/salesperson/__tests__/certification-cards.test.tsx`

**描述**:
- Component tests for ExperienceTimeline
- Component tests for CertificationCards
- 測試覆蓋率 >= 80%

**Acceptance Criteria**:
- [ ] 測試渲染 (有/無資料)
- [ ] 測試展開/收合互動
- [ ] 測試排序功能
- [ ] 測試篩選功能 (證照)
- [ ] 測試空狀態
- [ ] 測試 Loading 狀態
- [ ] 測試響應式行為 (可選)

---

## ✅ 完成檢查清單

### 功能完整性
- [ ] 所有 7 個任務完成
- [ ] 工作經驗時間軸正確顯示
- [ ] 專業證照卡片正確顯示
- [ ] 展開/收合功能運作正常
- [ ] 篩選功能運作正常
- [ ] 排序功能正確
- [ ] Loading 狀態正確顯示
- [ ] 空狀態正確顯示

### 視覺設計
- [ ] 符合 Design System 規範
- [ ] 時間軸視覺正確
- [ ] 卡片樣式精緻
- [ ] 過渡動畫流暢
- [ ] 響應式設計正確 (Mobile/Tablet/Desktop)

### 程式碼品質
- [ ] TypeScript: 0 errors
- [ ] ESLint: 0 errors
- [ ] 所有 Props 有類型定義
- [ ] 無 console.log
- [ ] 無 any 類型
- [ ] 遵循 react-best-practices

### 測試
- [ ] Component tests 通過
- [ ] 測試覆蓋率 >= 80%
- [ ] 手動測試通過

### 可訪問性
- [ ] WCAG AA 色彩對比 (>= 4.5:1)
- [ ] 鍵盤導航可操作
- [ ] ARIA 標籤完整
- [ ] Screen Reader 友善

### 效能
- [ ] LCP < 2.5s
- [ ] 無不必要的 re-render
- [ ] 使用 React.memo (如需要)
- [ ] Bundle size 增加 < 50KB

---

## 📊 進度追蹤

| Task | 狀態 | 負責人 | 完成時間 |
|------|------|--------|----------|
| Task 1: EmptyState | ⏸️ Pending | React Specialist | - |
| Task 2: ExperienceItem | ⏸️ Pending | React Specialist | - |
| Task 3: ExperienceTimeline | ⏸️ Pending | React Specialist | - |
| Task 4: CertificationCard | ⏸️ Pending | React Specialist | - |
| Task 5: CertificationCards | ⏸️ Pending | React Specialist | - |
| Task 6: 更新頁面 | ⏸️ Pending | React Specialist | - |
| Task 7: 測試 | ⏸️ Pending | React Specialist | - |

---

## 🔄 執行順序建議

### 依序執行 (推薦)
```
Task 1 → Task 2 → Task 3 → Task 4 → Task 5 → Task 6 → Task 7
```

**原因**:
- Task 1 (EmptyState) 被 Task 3 和 Task 5 依賴
- Task 2 被 Task 3 依賴
- Task 4 被 Task 5 依賴
- Task 6 依賴 Task 3 和 Task 5
- Task 7 在所有組件完成後執行

### 並行執行 (進階)
```
Group A: Task 1
Group B: Task 2 + Task 4 (並行，依賴 Task 1)
Group C: Task 3 + Task 5 (並行，依賴 Group B)
Group D: Task 6 (依賴 Group C)
Group E: Task 7 (依賴 Group D)
```

---

## 🐛 已知風險

1. **效能風險**: 如果經驗/證照數量過多 (>50 項)
   - 緩解: 考慮虛擬滾動或分頁
   - 緩解: 預設只顯示前 10 項

2. **瀏覽器相容性**: 某些 CSS 特性可能不支援舊版瀏覽器
   - 緩解: 使用 PostCSS autoprefixer
   - 緩解: 提供 fallback 樣式

3. **測試覆蓋率**: 互動邏輯較複雜，可能影響測試覆蓋率
   - 緩解: 重點測試核心功能
   - 緩解: 手動測試補充

---

## 📝 備註

- 所有組件使用 Tailwind CSS
- 遵循現有的 Design System
- 不需要新增 dependencies
- 保持與現有頁面的一致性

---

**建立日期**: 2026-01-20
**最後更新**: 2026-01-20
**版本**: 1.0
