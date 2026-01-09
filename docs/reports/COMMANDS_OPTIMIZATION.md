# Commands 工作流程優化分析

**版本**: v1.0
**建立日期**: 2026-01-08
**目的**: 整合 Commands 工作流程與 OpenSpec SDD，移除冗餘步驟

---

## 📊 現況分析

### 當前 Commands 工作流程

```
/implement [功能描述]
    ↓
1. /clarify (PM)          → 需求規格書.md
    ↓
2. /explore (Architect)   → 技術分析報告.md
    ↓
3. /plan (Tech Lead)      → 實作計畫書.md
    ↓
4. /execute (Developer)   → 程式碼
    ↓
5. /verify (QA)          → 測試報告.md
```

### OpenSpec SDD 工作流程

```
新功能開發
    ↓
1. Create Proposal        → openspec/changes/<feature>/proposal.md
    ↓
2. Write Specs           → openspec/changes/<feature>/specs/
    ↓                        - api.md
                             - data-model.md
                             - business-rules.md
3. Break Down Tasks      → openspec/changes/<feature>/tasks.md
    ↓
4. Implement             → 實際程式碼
    ↓
5. Archive               → 合併到 openspec/specs/
```

---

## 🔍 重疊分析

### 重疊部分

| Commands 階段 | OpenSpec 階段 | 重疊度 | 產出 |
|--------------|--------------|--------|------|
| **/clarify** (PM) | **Proposal** | 80% | 需求說明、驗收標準 |
| **/explore** (Architect) | - | N/A | 技術分析（OpenSpec 無此步驟）|
| **/plan** (Tech Lead) | **Specs + Tasks** | 70% | 技術方案、任務拆解 |
| **/execute** (Developer) | **Implement** | 100% | 程式碼 |
| **/verify** (QA) | - | N/A | 測試報告（OpenSpec 無明確步驟）|

### 冗餘問題

#### 問題 1: 重複的文件產出
- **需求規格書** (clarify) vs **proposal.md** (OpenSpec)
- **實作計畫書** (plan) vs **specs/ + tasks.md** (OpenSpec)
- 兩套文件系統並存，維護成本高

#### 問題 2: Explore 階段的必要性
- **何時需要**: 新專案、不熟悉的架構、重大重構
- **何時不需要**: 小功能、維護性開發、架構熟悉
- **現況**: 每次都執行，浪費時間

#### 問題 3: Verify 階段的定位
- **現況**: 作為獨立的 QA 角色階段
- **問題**: 小功能的測試驗收可能過於正式
- **建議**: 整合到 Execute 或改為可選

---

## ✅ 優化建議

### 方案 A: 完全整合至 OpenSpec（激進）

**工作流程**:
```
/implement [功能] --openspec
    ↓
1. Create OpenSpec Change
   - 建立 openspec/changes/<feature>/
   - 產出 proposal.md (整合 clarify)
   - 產出 specs/*.md (整合 plan)
   - 產出 tasks.md
    ↓
2. Execute Implementation
   - 按照 tasks.md 執行
   - 使用 TodoWrite 追蹤進度
    ↓
3. Test & Verify (可選)
   - 執行測試腳本
   - 產出簡化測試報告
    ↓
4. Archive to openspec/specs/
```

**優點**:
- ✅ 單一文件系統，避免重複
- ✅ 完全遵循 OpenSpec 規範
- ✅ 文件結構清晰，易於維護

**缺點**:
- ❌ 需要改寫所有 commands
- ❌ 學習曲線（團隊需適應 OpenSpec）

---

### 方案 B: 混合模式（漸進）✅ **推薦**

**保留 Commands，產出改為 OpenSpec 格式**

#### 新的工作流程

```
/implement [功能] --mode=openspec
    ↓
1. /clarify (PM)
   產出: openspec/changes/<feature>/proposal.md
         └─ 包含：功能概述、使用情境、驗收標準
    ↓
2. /explore (Architect) [可選]
   執行條件: --with-explore 或首次開發
   產出: openspec/changes/<feature>/specs/architecture-analysis.md
    ↓
3. /plan (Tech Lead)
   產出: openspec/changes/<feature>/tasks.md
         openspec/changes/<feature>/specs/api.md
         openspec/changes/<feature>/specs/data-model.md
         openspec/changes/<feature>/specs/business-rules.md
    ↓
4. /execute (Developer)
   輸入: 讀取 tasks.md
   產出: 實際程式碼
   追蹤: TodoWrite 對應 tasks.md
    ↓
5. /verify (QA) [可選]
   執行條件: --with-verify 或重要功能
   產出: openspec/changes/<feature>/test-report.md
    ↓
6. /archive
   動作: 將 specs/ 合併到 openspec/specs/
```

#### 指令調整

**1. /clarify 修改**
```markdown
# 需求確認

產出檔案: `openspec/changes/{feature-name}/proposal.md`

格式:
# Proposal: {功能名稱}

## Why (問題陳述)
## What (解決方案)
## Scope (範圍)
## Success Criteria (驗收標準)
```

**2. /explore 修改**（改為可選）
```markdown
# 探索程式碼 [可選]

執行條件:
- 新專案或不熟悉架構時使用 --with-explore
- 預設跳過此階段

產出檔案: `openspec/changes/{feature-name}/specs/architecture-analysis.md`
```

**3. /plan 修改**
```markdown
# 制定實作計畫

產出檔案:
1. `openspec/changes/{feature-name}/tasks.md` - 任務清單
2. `openspec/changes/{feature-name}/specs/api.md` - API 規格
3. `openspec/changes/{feature-name}/specs/data-model.md` - 資料模型
4. `openspec/changes/{feature-name}/specs/business-rules.md` - 業務規則
```

**4. /execute 不變**（讀取 OpenSpec tasks.md）

**5. /verify 修改**（改為可選）
```markdown
# 驗收確認 [可選]

執行條件:
- 重要功能使用 --with-verify
- 小功能可跳過（由 Developer 自測）

產出檔案: `openspec/changes/{feature-name}/test-report.md`
```

**6. 新增 /archive**
```markdown
# 歸檔已完成功能

執行:
1. 合併 specs/ 到 openspec/specs/
2. 移動變更目錄到 openspec/changes/archived/
3. 更新 openspec/specs/ 相關文件
```

---

### 方案 C: 保持現況（保守）

**不整合 OpenSpec，維持獨立的 Commands 流程**

**優點**:
- ✅ 無需修改現有流程
- ✅ 團隊熟悉度高

**缺點**:
- ❌ 文件重複維護
- ❌ 未遵循 OpenSpec 規範
- ❌ 與專案主流程脫節

---

## 🎯 推薦方案：方案 B（混合模式）

### 理由

1. **漸進式改進** - 保留 Commands 結構，只改變產出格式
2. **降低學習成本** - 團隊仍使用熟悉的指令
3. **完全相容 OpenSpec** - 產出符合規範，可長期維護
4. **彈性調整** - 可選擇性跳過 explore/verify
5. **避免文件重複** - 統一到 OpenSpec 目錄

---

## 📋 實施計畫

### Phase 1: Commands 文件修改（1-2 天）

#### 需要修改的檔案

```
.claude/commands/
├── clarify.md       ← 修改：產出 proposal.md
├── explore.md       ← 修改：改為可選，產出 architecture-analysis.md
├── plan.md          ← 修改：產出 tasks.md + specs/*.md
├── execute.md       ← 輕微修改：讀取 tasks.md
├── verify.md        ← 修改：改為可選，產出 test-report.md
└── implement.md     ← 修改：加入 --mode, --with-explore, --with-verify 選項
```

新增檔案：
```
.claude/commands/
└── archive.md       ← 新增：歸檔功能
```

#### 修改重點

**clarify.md**:
```markdown
## 輸出成果

完成後，產出檔案：`openspec/changes/<feature-name>/proposal.md`

格式：
# Proposal: <功能名稱>

## Why (問題陳述)
[說明為什麼需要這個功能，解決什麼問題]

## What (解決方案)
[說明功能的核心內容和價值]

## Scope (範圍)
### In Scope
- 功能 1
- 功能 2

### Out of Scope (Future Enhancements)
- 功能 3

## Success Criteria (驗收標準)
- [ ] 標準 1
- [ ] 標準 2
```

**plan.md**:
```markdown
## 輸出成果

完成後，產出以下檔案：

1. **tasks.md** - 任務清單
   ```markdown
   # Implementation Tasks: <功能名稱>

   ## Phase 1: <階段名稱>
   - [ ] Task 1.1: <任務描述>
   - [ ] Task 1.2: <任務描述>

   ## Phase 2: <階段名稱>
   - [ ] Task 2.1: <任務描述>
   ```

2. **specs/api.md** - API 規格
   ```markdown
   # API Specification: <功能名稱>

   ## New Endpoints
   ### POST /api/<endpoint>
   ...
   ```

3. **specs/data-model.md** - 資料模型
4. **specs/business-rules.md** - 業務規則
```

**implement.md** 加入選項:
```markdown
## 執行模式

### 完整模式（預設）
```bash
/implement <功能描述>
```
執行：Clarify → Plan → Execute

### 含架構分析
```bash
/implement <功能描述> --with-explore
```
執行：Clarify → Explore → Plan → Execute

### 含 QA 驗收
```bash
/implement <功能描述> --with-verify
```
執行：Clarify → Plan → Execute → Verify

### 全流程
```bash
/implement <功能描述> --full
```
執行：Clarify → Explore → Plan → Execute → Verify
```

---

### Phase 2: 建立自動化腳本（3-5 天）

#### 腳本 1: create-change.sh
```bash
#!/bin/bash
# 使用: ./scripts/create-change.sh feature-name "Feature Description"

FEATURE_NAME=$1
DESCRIPTION=$2

mkdir -p openspec/changes/$FEATURE_NAME/specs

# 建立 proposal.md 模板
cat > openspec/changes/$FEATURE_NAME/proposal.md <<EOF
# Proposal: $DESCRIPTION

## Why (問題陳述)

## What (解決方案)

## Scope

### In Scope
-

### Out of Scope

## Success Criteria
- [ ]

---

**Status**: Draft
**Created**: $(date +%Y-%m-%d)
EOF

# 建立其他模板檔案...
echo "✅ Change proposal created: openspec/changes/$FEATURE_NAME/"
```

#### 腳本 2: archive-change.sh
```bash
#!/bin/bash
# 使用: ./scripts/archive-change.sh feature-name

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
    cat $CHANGE_DIR/specs/api.md >> openspec/specs/api/endpoints.md
    echo "✅ API specs merged"
fi

# 合併資料模型
if [ -f "$CHANGE_DIR/specs/data-model.md" ]; then
    echo "" >> openspec/specs/models/data-models.md
    echo "---" >> openspec/specs/models/data-models.md
    echo "" >> openspec/specs/models/data-models.md
    cat $CHANGE_DIR/specs/data-model.md >> openspec/specs/models/data-models.md
    echo "✅ Data model specs merged"
fi

# 移動到歸檔
mkdir -p openspec/changes/archived
mv $CHANGE_DIR openspec/changes/archived/
echo "✅ Change archived: $FEATURE_NAME"
```

---

### Phase 3: 文件更新（1 天）

更新以下文件以反映新流程：

1. **DEVELOPMENT.md** - 更新開發流程說明
2. **CLAUDE.md** - 更新 Commands 使用方式
3. **AGENTS.md** - 整合 Commands 與 OpenSpec 指引

---

## 🗑️ 移除的冗餘步驟

### 1. 預設不執行 /explore

**移除原因**:
- 多數小功能開發不需要完整架構分析
- 架構師的技術分析應該是「按需執行」
- 熟悉的專案不需要每次都探索

**改為**:
- 使用 `--with-explore` 旗標時才執行
- 新專案或重大功能才需要

**節省時間**: 每次開發省約 15-30 分鐘

---

### 2. 預設不執行 /verify

**移除原因**:
- 小功能的正式 QA 測試過於繁重
- Developer 應該自行測試基本功能
- 完整的 QA 報告對小改動來說是 overhead

**改為**:
- 使用 `--with-verify` 旗標時才執行
- 重要功能或發布前才進行正式驗收

**節省時間**: 每次開發省約 20-40 分鐘

---

### 3. 合併文件產出

**移除**:
- 獨立的「需求規格書.md」
- 獨立的「實作計畫書.md」
- 獨立的「技術分析報告.md」

**改為**:
- 統一產出到 `openspec/changes/<feature>/`
- 遵循標準化的 OpenSpec 格式

**效益**:
- 避免文件散落各處
- 易於歸檔和維護
- 符合業界標準（OpenSpec）

---

## 📊 效益評估

### 時間節省

| 流程 | 原本耗時 | 優化後耗時 | 節省 |
|------|---------|-----------|------|
| Clarify | 10-15 分鐘 | 8-12 分鐘 | ~20% |
| Explore | 15-30 分鐘 | 0 分鐘（可選）| ~100%（預設跳過）|
| Plan | 20-30 分鐘 | 15-25 分鐘 | ~20% |
| Execute | 不變 | 不變 | 0% |
| Verify | 20-40 分鐘 | 0 分鐘（可選）| ~100%（預設跳過）|
| **總計** | **65-115 分鐘** | **23-37 分鐘** | **~60%** |

**小功能開發**（無需 explore/verify）:
- 原本: 65-115 分鐘
- 優化後: 23-37 分鐘
- **節省約 60% 時間**

---

### 文件品質提升

- ✅ 統一格式（OpenSpec 標準）
- ✅ 易於搜尋和參考
- ✅ 版本控制友善
- ✅ 符合業界最佳實踐

---

## 🤔 需要討論的問題

### 問題 1: 採用哪個方案？

- [ ] **方案 A**: 完全整合至 OpenSpec（激進）
- [ ] **方案 B**: 混合模式（漸進）✅ **建議**
- [ ] **方案 C**: 保持現況（保守）

### 問題 2: /explore 和 /verify 預設是否執行？

**選項 A**: 預設不執行（推薦）
- 優點: 節省時間，快速迭代
- 缺點: 需要手動判斷何時需要

**選項 B**: 預設執行
- 優點: 完整流程，品質保證
- 缺點: 耗時，小功能過於正式

**我的建議**: 選項 A（預設不執行）

### 問題 3: 是否保留舊的文件格式作為備選？

**選項 A**: 完全移除舊格式
- 優點: 強制統一標準
- 缺點: 團隊需適應

**選項 B**: 保留作為 legacy 選項
- 優點: 漸進式過渡
- 缺點: 維護兩套系統

**我的建議**: 選項 A（完全移除）

---

## 🚀 實施時間表

### Week 1: 準備階段
- [ ] 討論並確認優化方案
- [ ] 設計新的 Commands 文件結構
- [ ] 建立範例檔案模板

### Week 2: 實施階段
- [ ] 修改 .claude/commands/ 中的 6 個檔案
- [ ] 建立自動化腳本（create-change.sh, archive-change.sh）
- [ ] 測試新流程（使用小功能測試）

### Week 3: 文件更新
- [ ] 更新 DEVELOPMENT.md
- [ ] 更新 CLAUDE.md
- [ ] 更新 AGENTS.md
- [ ] 撰寫使用範例

### Week 4: 驗證與調整
- [ ] 使用新流程開發實際功能
- [ ] 收集問題和改進建議
- [ ] 微調流程和文件

---

## 📝 後續行動

請確認以下事項：

1. **採用方案 B（混合模式）是否同意？**
2. **explore 和 verify 預設不執行是否同意？**
3. **是否需要我立即開始修改 Commands 文件？**
4. **自動化腳本是否需要？優先級如何？**

---

**文件狀態**: 待討論並決策
**最後更新**: 2026-01-08
