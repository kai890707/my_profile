# AI 自動化開發流程系統

## 系統概述

完整的 AI 自動化開發流程，透過角色扮演和指令編排實現從需求到交付的全流程自動化。

**核心理念**：一個指令完成所有開發流程

```bash
/implement [功能描述]
```

## 系統架構

```
用戶需求
   ↓
PM (需求確認)
   ↓
Architect (技術分析)
   ↓
Tech Lead (計畫制定) → 等待確認
   ↓
Developer (程式碼實作)
   ↓
QA (品質驗收)
   ↓
交付成果
```

## 檔案結構

```
.claude/
├── README.md
└── commands/
    ├── implement.md      # 主指令
    ├── clarify.md       # PM
    ├── explore.md       # Architect
    ├── plan.md          # Tech Lead
    ├── execute.md       # Developer
    └── verify.md        # QA
```

## 角色說明

### PM - /clarify
- 需求訪談與確認
- 功能範圍界定
- 驗收標準制定
- 輸出：需求規格書

### Architect - /explore
- 系統架構分析
- 程式碼掃描（Glob/Grep/Read）
- 整合點識別
- 輸出：技術分析報告

### Tech Lead - /plan
- 技術方案設計
- 任務拆解與排序
- 風險評估
- 輸出：實作計畫書

### Developer - /execute
- 程式碼實作
- TodoWrite 進度追蹤
- 品質控制
- 輸出：可運行程式碼

**工作準則**：
- 一次一個 in_progress 任務
- 遵循程式碼規範
- 避免過度工程
- 禁止跳過步驟
- 禁止添加計畫外功能

### QA - /verify
- 驗收標準檢查
- 功能測試（正常/邊界/異常）
- 安全檢查（OWASP Top 10）
- 效能與相容性測試
- 輸出：驗收測試報告

## 使用方式

### 完整流程

```bash
/implement 建立用戶登入系統
```

執行 5 個階段：
1. PM 訪談需求
2. Architect 分析技術
3. Tech Lead 制定計畫（等待確認）
4. Developer 編寫程式碼
5. QA 測試驗收

### 單獨執行

```bash
/clarify [描述]      # 僅需求確認
/explore [描述]      # 僅技術分析
/plan [描述]         # 僅計畫制定
/execute             # 僅程式碼實作
/verify              # 僅品質驗收
```

## 使用範例

### 新增功能

```bash
/implement 新增商品評論功能
```

流程：
1. PM：了解評論功能需求（評分、內容、圖片）
2. Architect：找出商品、用戶資料表位置
3. Tech Lead：設計資料表、API、前端頁面
4. Developer：建立 Migration、Model、Controller、View
5. QA：測試評論新增/編輯/刪除/顯示

### 修復 Bug

```bash
/implement 修復購物車總價計算錯誤
```

流程：
1. PM：確認錯誤重現步驟
2. Architect：定位購物車程式碼
3. Tech Lead：設計修復方案
4. Developer：修改計算邏輯
5. QA：測試各種購物車情境

### 效能優化

```bash
/implement 優化首頁載入速度
```

流程：
1. PM：定義效能目標（3秒內）
2. Architect：識別效能瓶頸
3. Tech Lead：設計優化策略
4. Developer：實施優化
5. QA：測量載入時間、壓力測試

## 配置選項

### 執行模式

```bash
# 完全自動（僅計畫後暫停）
/implement [描述] --auto

# 每階段暫停（預設）
/implement [描述] --step

# 跳過 QA
/implement [描述] --skip-verify
```

### 角色範圍

```bash
# 僅需求確認
/implement [描述] --pm-only

# 技術開發（跳過 PM）
/implement [描述] --tech-only

# 完整流程（預設）
/implement [描述] --full
```

## 品質保證

### Developer 檢查清單

**安全性**：
- 無 SQL Injection 風險
- 無 XSS 漏洞
- 無 CSRF 風險
- 輸入驗證完整

**效能**：
- 無 N+1 查詢
- 適當使用快取
- 記憶體使用合理

**程式碼**：
- 遵循專案規範
- 變數命名有意義
- 可讀性佳

### QA 測試清單

**功能**：
- 正常流程
- 邊界情況
- 錯誤處理

**品質**：
- 安全性
- 效能
- 相容性

## 最佳實踐

### 清楚描述需求

不好：
```
/implement 做一個功能
```

良好：
```
/implement 建立用戶登入系統，支援 Email/密碼和 Google OAuth
```

### 信任流程

- 讓每個角色專注職責
- 不跳過 Plan 階段確認
- 讓 QA 完整測試

### 及時反饋

- PM 訪談時提供清楚需求
- Plan 階段仔細審查計畫
- QA 階段確認測試結果

## 疑難排解

### Q: 如何查看進度？

Developer 使用 TodoWrite 即時更新任務狀態。

### Q: 計畫不符期望？

在 Plan 階段暫停時可：
1. 要求 Tech Lead 調整
2. 回到 PM 重新確認需求
3. 要求 Architect 提供其他方案

### Q: 發現 Bug？

QA 會記錄 Bug，可選擇：
1. 要求 Developer 修復
2. 降低優先級稍後處理
3. 調整需求規避問題

### Q: 想添加計畫外功能？

不建議執行中途添加，應：
1. 完成當前開發
2. 啟動新 /implement 流程
3. 讓 PM 確認新需求

## 系統優勢

| 優勢 | 說明 |
|------|------|
| 專業分工 | 每個角色專注擅長領域 |
| 品質保證 | 多層次檢查確保品質 |
| 流程透明 | 每階段產出清楚可見 |
| 可追蹤 | TodoWrite 即時追蹤 |
| 可擴展 | 易於添加角色或階段 |
| 可重用 | 子指令可獨立使用 |

---

版本：1.0.0
最後更新：2026-01-07
