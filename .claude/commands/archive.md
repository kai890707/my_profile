# 歸檔功能到規範庫

**變更名稱**: $ARGUMENTS

**任務**: 將完成的變更歸檔到 OpenSpec 規範庫

---

## 前置條件

- ✅ Proposal 已完成
- ✅ Specs 已完成
- ✅ Tasks 已全部完成
- ✅ 功能已測試通過
- ✅ 變更目錄存在: `openspec/changes/<feature-name>/`

---

## 目標

將變更內容合併到主規範庫，成為系統的「真實來源」(Source of Truth)

---

## 工作流程

### Step 1: 合併 API 規格

**目標檔案**: `openspec/specs/api/endpoints.md`

**操作**:
1. 讀取 `openspec/changes/<feature-name>/specs/api.md`
2. 在 `openspec/specs/api/endpoints.md` 末尾加入分隔線
3. 附加新的 API 規格
4. 加入歸檔標記

**範例**:
```markdown
<!-- 在 openspec/specs/api/endpoints.md 末尾 -->

---

## Feature: <功能名稱>

**Added**: YYYY-MM-DD
**Change**: <feature-name>

### POST /api/endpoint

[完整的 API 規格內容]

### GET /api/endpoint/:id

[完整的 API 規格內容]

---
```

---

### Step 2: 合併資料模型規格

**目標檔案**: `openspec/specs/models/data-models.md`

**操作**:
1. 讀取 `openspec/changes/<feature-name>/specs/data-model.md`
2. 在 `openspec/specs/models/data-models.md` 末尾加入分隔線
3. 附加新的資料模型規格
4. 加入歸檔標記

**範例**:
```markdown
<!-- 在 openspec/specs/models/data-models.md 末尾 -->

---

## Feature: <功能名稱>

**Added**: YYYY-MM-DD
**Change**: <feature-name>

### Table: <table_name>

[完整的資料模型規格]

---
```

---

### Step 3: 合併業務規則（如有）

**目標檔案**: `openspec/specs/business-rules.md` (如不存在則建立)

**操作**:
1. 讀取 `openspec/changes/<feature-name>/specs/business-rules.md`
2. 合併業務規則到主文件
3. 維持規則編號的連續性

---

### Step 4: 移動變更目錄到歸檔

**操作**:
```bash
mkdir -p openspec/changes/archived
mv openspec/changes/<feature-name> openspec/changes/archived/
```

**保留內容**:
- proposal.md
- specs/
- tasks.md
- (任何其他相關文件)

**目的**: 保留完整的開發歷史，供未來參考

---

### Step 5: 更新變更日誌（可選）

**檔案**: `openspec/CHANGELOG.md`

**格式**:
```markdown
## [YYYY-MM-DD]

### Added
- **<功能名稱>** (<feature-name>)
  - 新增 X 個 API 端點
  - 新增 Y 個資料表
  - 實現 Z 條業務規則
```

---

## 自動化腳本（建議）

建立 `scripts/archive-change.sh`:

```bash
#!/bin/bash

FEATURE_NAME=$1
CHANGE_DIR="openspec/changes/$FEATURE_NAME"

# 檢查目錄是否存在
if [ ! -d "$CHANGE_DIR" ]; then
    echo "❌ Error: $CHANGE_DIR not found"
    exit 1
fi

# 合併 API 規格
if [ -f "$CHANGE_DIR/specs/api.md" ]; then
    echo "" >> openspec/specs/api/endpoints.md
    echo "---" >> openspec/specs/api/endpoints.md
    echo "" >> openspec/specs/api/endpoints.md
    echo "## Feature: $FEATURE_NAME" >> openspec/specs/api/endpoints.md
    echo "**Added**: $(date +%Y-%m-%d)" >> openspec/specs/api/endpoints.md
    echo "" >> openspec/specs/api/endpoints.md
    cat $CHANGE_DIR/specs/api.md >> openspec/specs/api/endpoints.md
    echo "✅ API specs merged"
fi

# 合併資料模型
if [ -f "$CHANGE_DIR/specs/data-model.md" ]; then
    echo "" >> openspec/specs/models/data-models.md
    echo "---" >> openspec/specs/models/data-models.md
    echo "" >> openspec/specs/models/data-models.md
    echo "## Feature: $FEATURE_NAME" >> openspec/specs/models/data-models.md
    echo "**Added**: $(date +%Y-%m-%d)" >> openspec/specs/models/data-models.md
    echo "" >> openspec/specs/models/data-models.md
    cat $CHANGE_DIR/specs/data-model.md >> openspec/specs/models/data-models.md
    echo "✅ Data model specs merged"
fi

# 移動到歸檔
mkdir -p openspec/changes/archived
mv $CHANGE_DIR openspec/changes/archived/
echo "✅ Change archived: $FEATURE_NAME"
echo "📁 Location: openspec/changes/archived/$FEATURE_NAME"
```

**使用方式**:
```bash
./scripts/archive-change.sh rating-feature
```

---

## 檢查清單

歸檔前必須確認:
- [ ] 所有功能已實作完成
- [ ] 所有測試已通過
- [ ] 無已知 Bug
- [ ] 規格文件完整（API, Data Model, Business Rules）

歸檔後必須驗證:
- [ ] `openspec/specs/api/endpoints.md` 已更新
- [ ] `openspec/specs/models/data-models.md` 已更新
- [ ] 變更目錄已移動到 `openspec/changes/archived/`
- [ ] 主規範庫內容正確、格式一致

---

## 歸檔後的維護

### 規範庫成為真實來源

歸檔後：
- ✅ `openspec/specs/` 是最新、最權威的規範
- ✅ 新功能開發應參考主規範庫
- ✅ 文件更新應同步到主規範庫

### 歷史變更可追溯

歸檔的變更提案：
- ✅ 保留完整的開發歷史
- ✅ 可查詢當初的設計決策
- ✅ 可參考實作細節

---

## 使用範例

### 範例 1: 歸檔評分功能

```bash
/archive rating-feature
```

**執行流程**:
1. 讀取 `openspec/changes/rating-feature/specs/api.md`
2. 合併 7 個 API 端點到 `openspec/specs/api/endpoints.md`
3. 讀取 `openspec/changes/rating-feature/specs/data-model.md`
4. 合併 ratings 資料表到 `openspec/specs/models/data-models.md`
5. 讀取 `openspec/changes/rating-feature/specs/business-rules.md`
6. 合併 11 條業務規則到 `openspec/specs/business-rules.md`
7. 移動 `rating-feature/` 到 `openspec/changes/archived/`
8. 更新 `openspec/CHANGELOG.md`

**結果**:
```
✅ API specs merged (7 endpoints)
✅ Data model specs merged (1 table)
✅ Business rules merged (11 rules)
✅ Change archived: rating-feature
📁 Location: openspec/changes/archived/rating-feature
```

---

### 範例 2: 歸檔 Bug 修復

```bash
/archive fix-cart-calculation
```

**執行流程**:
1. 讀取 `openspec/changes/fix-cart-calculation/specs/business-rules.md`
2. 更新 `openspec/specs/business-rules.md` 中的相關規則
3. 移動變更目錄到歸檔
4. 記錄變更

**結果**:
```
✅ Business rules updated
✅ Change archived: fix-cart-calculation
📁 Location: openspec/changes/archived/fix-cart-calculation
```

---

## 常見問題

### Q: 是否應該刪除歸檔的變更目錄？

A: **不應該**。保留在 `openspec/changes/archived/` 中，作為歷史記錄。

**原因**:
- 可追溯開發歷史
- 可查詢設計決策
- 可參考任務拆解方式

---

### Q: 如果規格有衝突怎麼辦？

A: 手動解決衝突。

**步驟**:
1. 識別衝突點（如相同的 API 端點）
2. 決定如何合併（保留哪個版本）
3. 更新主規範庫
4. 記錄決策原因

---

### Q: 是否需要更新文件版本號？

A: 建議在 `openspec/specs/` 加入變更日期標記。

**範例**:
```markdown
## Feature: Rating System
**Added**: 2026-01-08
**Change**: rating-feature
```

---

## 完成標準

歸檔完成時：
- ✅ 主規範庫已更新（API, Data Model, Business Rules）
- ✅ 變更目錄已移動到 archived/
- ✅ 格式一致、無衝突
- ✅ 可正常閱讀和參考

---

**歸檔完成**: 功能規範已成為系統真實來源，可供未來開發參考。
