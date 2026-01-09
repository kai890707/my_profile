# 歷史文件歸檔

**目的**: 此目錄保存專案早期使用的文件，這些文件的內容已整合至 OpenSpec 規範系統。

**歸檔日期**: 2026-01-08

---

## 歸檔原因

本專案已全面採用 **OpenSpec Specification-Driven Development (SDD)** 開發方法。以下文件為轉換至 OpenSpec 前的舊版文件，僅作為歷史參考保留。

**目前規範系統位置**: `openspec/specs/`

---

## 歸檔文件清單

### 1. 需求規格書.md
- **用途**: 原始系統需求規格文件（PM 階段產出）
- **已整合至**:
  - `openspec/specs/architecture/overview.md` - 系統架構
  - `openspec/specs/api/endpoints.md` - API 端點規範
  - `openspec/specs/models/data-models.md` - 資料模型

### 2. 實作計劃書.md
- **用途**: 技術分析與實作計畫（Architect 階段產出）
- **已整合至**:
  - `openspec/specs/architecture/overview.md` - 技術決策
  - `my_profile_ci4/DEVELOPMENT.md` - 開發流程指南

### 3. 專案完成報告.md
- **用途**: 專案完成狀態報告
- **已整合至**:
  - `README.md` - 專案狀態摘要
  - `my_profile_ci4/DEVELOPMENT.md` - 完整開發文件

---

## ⚠️ 重要提醒

**請勿繼續使用此目錄中的文件作為開發參考。**

所有最新的規範和文件請參考：

1. **專案總覽**: `/README.md`
2. **開發流程**: `/my_profile_ci4/DEVELOPMENT.md`
3. **系統規範**: `/openspec/specs/`
4. **AI 助理指南**: `/AGENTS.md`
5. **Claude Code 指南**: `/CLAUDE.md`

---

## 未來開發流程

新功能開發請遵循 OpenSpec SDD 工作流程：

```
1. 建立變更提案 (openspec/changes/<feature-name>/proposal.md)
2. 撰寫詳細規格 (API、資料模型、業務規則)
3. 拆解實作任務 (tasks.md)
4. 實作與測試
5. 歸檔至 openspec/specs/ (整合至主文件)
```

詳細流程請參考: `my_profile_ci4/DEVELOPMENT.md`

---

**歸檔政策**: 這些文件保留作為歷史記錄，如需要可隨時參考，但不應作為當前開發的依據。
