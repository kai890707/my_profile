# Frontend 文檔索引

**專案**: YAMU Frontend SPA
**框架**: Next.js 16.1.1 + TypeScript + React 19
**最後更新**: 2026-01-09

---

## 📚 文檔結構

本目錄包含 YAMU Frontend 專案的核心技術文檔,所有文檔都經過組織整理,可供開發團隊、Commands 和 Skills 直接參考。

---

## 📖 核心文檔

### 1. [設計系統](./design-system.md)
**用途**: UI/UX 設計規範和組件樣式指南

**包含內容**:
- 🎨 色彩系統 (Primary, Secondary, Functional Colors)
- 📝 字體系統 (Font Family, Sizes, Weights)
- 📐 間距系統 (4px Grid System)
- 🔘 圓角系統 (Border Radius)
- 🌑 陰影系統 (Shadow Levels)
- ⚡ 動畫系統 (Transitions, Easing)
- 📱 響應式斷點 (Mobile, Tablet, Desktop)
- 🧱 組件設計原則 (Button, Input, Card, etc.)

**何時參考**:
- 開發新 UI 組件時
- 調整樣式和視覺設計時
- 確保設計一致性時
- Skills 需要了解設計規範時

**關鍵特色**:
- 活潑親和的設計風格
- 基於 Tailwind CSS
- 完整的色彩和間距規範

---

### 2. [測試指南](./testing.md)
**用途**: 完整的測試策略、工具和執行指南

**包含內容**:
- 🧪 單元測試 (Vitest + React Testing Library)
- 📱 響應式測試 (Mobile, Tablet, Desktop)
- 🌐 瀏覽器兼容性測試 (Chrome, Firefox, Safari, Edge)
- ✅ 測試清單和檢查點
- 📝 測試報告範本
- 🔧 自動化測試建議

**何時參考**:
- 執行測試任務時
- 驗證新功能時
- 準備部署前檢查時
- Skills 需要執行測試時

**測試覆蓋**:
- 52 個自動化測試 (Button, Input, SearchFilters, SearchPage)
- 100+ 個手動測試檢查點
- 4 大瀏覽器兼容性測試

---

### 3. [性能優化](./performance.md)
**用途**: 性能優化策略和最佳實踐

**包含內容**:
- ⚡ 已實施的優化 (React Query, Image Lazy Loading)
- 📊 性能監控 (Web Vitals)
- 🚀 進一步優化建議 (Dynamic Import, Code Splitting)
- 📈 性能測試方法 (Lighthouse, Bundle Analysis)
- 🐛 常見性能問題和解決方案

**何時參考**:
- 優化應用性能時
- 分析性能瓶頸時
- 準備生產部署時
- Skills 需要性能優化建議時

**性能目標**:
- FCP < 2s
- LCP < 2.5s
- CLS < 0.1
- FID < 100ms
- Lighthouse Score > 80

---

## 📊 相關報告

歷史開發報告和狀態文檔請參考: [`../reports/README.md`](../reports/README.md)

**報告類型**:
- 專案完成報告
- 階段總結報告 (Phase 7, Phase 8)
- 測試狀態報告

---

## 🔗 快速參考

### 設計相關
```
色彩: frontend/docs/design-system.md#色彩系統
字體: frontend/docs/design-system.md#字體系統
組件: frontend/docs/design-system.md#組件設計原則
```

### 測試相關
```
單元測試: frontend/docs/testing.md#單元測試
響應式測試: frontend/docs/testing.md#響應式設計測試
瀏覽器測試: frontend/docs/testing.md#瀏覽器兼容性測試
```

### 性能相關
```
優化清單: frontend/docs/performance.md#已實施的優化
監控指標: frontend/docs/performance.md#監控指標
性能測試: frontend/docs/performance.md#性能測試
```

---

## 🎯 使用建議

### 對於開發者
1. **開始新功能前**: 閱讀 `design-system.md` 確保符合設計規範
2. **完成功能後**: 參考 `testing.md` 執行測試
3. **部署前**: 檢查 `performance.md` 優化性能

### 對於 Commands/Skills
1. **`/implement-frontend`**: 參考 `design-system.md` 了解 UI 規範
2. **測試相關 Skills**: 參考 `testing.md` 獲取測試清單
3. **性能優化 Skills**: 參考 `performance.md` 獲取優化建議

### 對於團隊協作
1. **新成員 Onboarding**: 按順序閱讀所有文檔
2. **Code Review**: 參考設計系統確保一致性
3. **技術決策**: 參考性能指南做出明智選擇

---

## 📁 文檔維護

### 更新原則
- 新增主要功能時更新對應文檔
- 設計規範變更時同步更新
- 測試策略調整時更新測試指南

### 版本控制
- 所有文檔都包含 "最後更新" 日期
- 重大變更應記錄版本歷史

### 文檔質量
- 保持簡潔清晰
- 包含實例和代碼片段
- 提供快速參考鏈接

---

## 🤝 貢獻指南

如需添加新文檔或更新現有文檔:

1. 確保文檔格式一致
2. 包含清晰的標題和章節
3. 提供實際使用範例
4. 更新本索引文件

---

**文檔狀態**: ✅ 完整且最新
**維護者**: Development Team
**版本**: 1.0
**最後更新**: 2026-01-09
