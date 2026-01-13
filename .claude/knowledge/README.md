# YAMU 專案知識庫

**目的**: 為 AI 輔助開發提供結構化、可精準定位的專案知識
**最後更新**: 2026-01-13
**版本**: 1.0

---

## 快速導航

### 依開發階段

| 階段 | 必讀文件 | 說明 |
|------|---------|------|
| **開始新功能** | [workflow/sdd-process.md](./workflow/sdd-process.md) | 了解 SDD 開發流程 |
| **Backend 開發** | [backend/architecture.md](./backend/architecture.md) | Laravel 架構模式 |
| **Frontend 開發** | [frontend/architecture.md](./frontend/architecture.md) | Next.js 架構模式 |
| **API 設計** | [backend/api-design.md](./backend/api-design.md) | RESTful API 規範 |
| **資料庫設計** | [backend/database.md](./backend/database.md) | DB Schema 設計原則 |
| **撰寫測試** | [backend/testing.md](./backend/testing.md), [frontend/testing.md](./frontend/testing.md) | 測試策略 |
| **部署** | [workflow/deployment.md](./workflow/deployment.md) | 部署流程 |

### 依技術領域

**工作流程**:
- [SDD 流程](./workflow/sdd-process.md) - Specification-Driven Development 完整流程
- [Git 工作流程](./workflow/git-workflow.md) - 分支策略與協作流程
- [部署流程](./workflow/deployment.md) - 開發到生產的完整部署

**Backend (Laravel 11)**:
- [架構模式](./backend/architecture.md) - MVC, Action Pattern, Service Layer
- [API 設計](./backend/api-design.md) - RESTful API 設計原則與規範
- [資料庫設計](./backend/database.md) - Schema 設計、索引優化、關聯管理
- [驗證規範](./backend/validation.md) - FormRequest, 驗證規則, 錯誤處理
- [錯誤處理](./backend/error-handling.md) - 異常處理、API 錯誤回應
- [測試策略](./backend/testing.md) - Feature Tests, Unit Tests, PHPStan

**Frontend (Next.js 15 + React 19)**:
- [架構模式](./frontend/architecture.md) - App Router, 目錄結構, 程式碼組織
- [組件模式](./frontend/component-patterns.md) - React 組件設計模式與最佳實踐
- [狀態管理](./frontend/state-management.md) - React Query, Zustand, Context
- [API 整合](./frontend/api-integration.md) - API Client, 錯誤處理, 認證
- [測試策略](./frontend/testing.md) - Component Tests, E2E Tests, Playwright

---

## 依優先級分類

### 高優先級 (必讀)

開發任何功能前必須了解:
1. [SDD 流程](./workflow/sdd-process.md) - 開發流程規範
2. [Backend 架構](./backend/architecture.md) - 後端架構模式
3. [Frontend 架構](./frontend/architecture.md) - 前端架構模式
4. [API 設計](./backend/api-design.md) - API 設計規範

### 中優先級 (常用)

開發過程中經常參考:
1. [資料庫設計](./backend/database.md) - DB Schema 設計
2. [組件模式](./frontend/component-patterns.md) - React 組件設計
3. [驗證規範](./backend/validation.md) - 資料驗證
4. [狀態管理](./frontend/state-management.md) - 前端狀態管理

### 低優先級 (需要時查閱)

特定場景才需要:
1. [錯誤處理](./backend/error-handling.md) - 異常處理細節
2. [部署流程](./workflow/deployment.md) - 部署相關
3. [測試策略](./backend/testing.md) - 深入測試技巧

---

## 依標籤快速查找

### Backend 標籤
- `[laravel]` - Laravel 框架相關
- `[api]` - API 設計與實作
- `[database]` - 資料庫相關
- `[validation]` - 資料驗證
- `[testing]` - 測試相關
- `[security]` - 安全性
- `[performance]` - 效能優化

### Frontend 標籤
- `[nextjs]` - Next.js 框架
- `[react]` - React 相關
- `[typescript]` - TypeScript 類型
- `[ui]` - UI 組件
- `[state]` - 狀態管理
- `[api-integration]` - API 整合
- `[testing]` - 測試相關

### Workflow 標籤
- `[sdd]` - Specification-Driven Development
- `[git]` - Git 工作流程
- `[deployment]` - 部署
- `[ci-cd]` - 持續整合/部署

---

## 知識庫使用指南

### 對 AI 開發助手

**快速定位知識**:
1. 根據任務類型，先查看「快速導航」區
2. 讀取相關文件的 Quick Reference
3. 深入閱讀「實例代碼」區獲取具體範例
4. 查看「常見錯誤」避免踩坑

**標準開發流程**:
```
任務開始
  ↓
讀取 workflow/sdd-process.md (了解流程)
  ↓
讀取對應的 backend/ 或 frontend/ 知識
  ↓
參考「實例代碼」開始實作
  ↓
檢查「最佳實踐」清單
  ↓
完成開發
```

**搜尋知識點**:
- 使用 grep 搜尋標籤: `grep -r "tags: .*api" .`
- 搜尋關鍵字: `grep -r "FormRequest" ./backend/`
- 搜尋檔案: 直接查看目錄結構

### 對人類開發者

**新手指南**:
1. 從 [SDD 流程](./workflow/sdd-process.md) 開始
2. 閱讀對應技術棧的架構文件
3. 實作時參考實例代碼
4. 遇到問題查看「常見錯誤」

**進階使用**:
- 參考「決策記錄」了解設計演進
- 查看「相關知識」建立知識網絡
- 使用「最佳實踐」清單自我檢查

---

## 知識庫維護

### 新增知識文件

1. 複製 `TEMPLATE.md` 作為起點
2. 填寫完整的元數據
3. 按照模板結構撰寫內容
4. 更新本 README.md 的索引

### 更新現有文件

1. 更新 `last_updated` 日期
2. 在「決策記錄」區新增變更說明
3. 保持向下兼容，標註過時內容

### 文件規範

**必須包含**:
- 完整的 YAML 元數據
- Quick Reference 快速參考
- 至少一個實例代碼
- 至少一個常見錯誤
- 相關知識連結

**建議包含**:
- 使用場景說明
- 最佳實踐清單
- 決策記錄
- 參考資源

---

## 知識庫結構

```
.claude/knowledge/
├── README.md                        # 本文件 - 知識庫索引
├── TEMPLATE.md                      # 文件模板
│
├── workflow/                        # 工作流程知識
│   ├── sdd-process.md              # SDD 流程深度解析
│   ├── git-workflow.md             # Git 分支策略
│   └── deployment.md               # 部署流程
│
├── backend/                         # Backend 專屬
│   ├── architecture.md             # Laravel 架構模式
│   ├── api-design.md               # API 設計原則
│   ├── database.md                 # 資料庫設計
│   ├── validation.md               # 驗證規範
│   ├── error-handling.md           # 錯誤處理
│   └── testing.md                  # 測試策略
│
└── frontend/                        # Frontend 專屬
    ├── architecture.md             # Next.js 架構模式
    ├── component-patterns.md       # React 組件模式
    ├── state-management.md         # 狀態管理
    ├── api-integration.md          # API 整合
    └── testing.md                  # 測試策略
```

---

## 與其他文檔的關係

| 文檔類型 | 位置 | 目的 | 使用時機 |
|---------|------|------|---------|
| **CLAUDE.md** | 根目錄/子專案 | 快速參考、環境設定 | 專案設置、快速查找 |
| **knowledge/** | `.claude/knowledge/` | 深度知識、設計模式 | 開發實作、學習理解 |
| **commands/** | `.claude/commands/` | 開發指令、工作流程 | 執行開發流程 |
| **openspec/** | `openspec/specs/` | 功能規格、API 文檔 | 實作具體功能 |

**使用順序**:
1. 新任務 → 先讀 `commands/` 了解流程
2. 開始實作 → 讀 `knowledge/` 學習模式
3. 撰寫規格 → 參考 `openspec/` 規範
4. 環境問題 → 查 `CLAUDE.md` 快速參考

---

## 常見問題

### Q: 知識庫 vs CLAUDE.md 有什麼不同？

**CLAUDE.md**: 快速參考手冊
- 環境設定
- 常用命令
- 專案結構
- 快速開始

**knowledge/**: 深度知識庫
- 設計模式
- 架構決策
- 最佳實踐
- 實例代碼

### Q: 我該先讀哪些文件？

**Backend 開發**:
1. workflow/sdd-process.md
2. backend/architecture.md
3. backend/api-design.md

**Frontend 開發**:
1. workflow/sdd-process.md
2. frontend/architecture.md
3. frontend/component-patterns.md

**全棧開發**:
1. workflow/sdd-process.md
2. backend/architecture.md
3. frontend/architecture.md

### Q: 如何快速找到我需要的知識？

1. 查看本文件的「快速導航」區
2. 使用標籤搜尋: `grep -r "tags: .*你的關鍵字"`
3. 閱讀文件的 Quick Reference 區
4. 查看「相關知識」找到關聯文件

---

## 反饋與改進

如果你發現:
- 知識點缺失
- 實例代碼過時
- 說明不清楚
- 需要新增主題

請提出 Issue 或直接更新相關文件。

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
