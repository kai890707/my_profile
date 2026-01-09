# 開發流程優化建議

**版本**: v1.0
**建立日期**: 2026-01-08
**狀態**: 待討論

---

## 📊 當前流程分析

### 已完成的優化 ✅

1. **OpenSpec SDD 整合** - 規範驅動開發流程建立
2. **文件標準化** - 統一文件結構（specs/, changes/, archive/)
3. **開發指南完善** - DEVELOPMENT.md 提供完整指引
4. **歷史文件歸檔** - 清理專案根目錄，保留歷史記錄

### 當前工作流程

```
新功能開發流程：
1. 手動建立變更提案目錄
2. 手動撰寫 proposal.md, tasks.md, specs/*.md
3. 手動建立 Migration/Model/Controller
4. 手動執行測試
5. 手動歸檔到 openspec/specs/
```

**問題點**:
- 重複性手動操作多
- 容易遺漏步驟
- 測試流程不一致
- 歸檔流程需要手動整合

---

## 🚀 優化建議分類

### A. 高優先級（建議立即實施）

#### A1. 開發腳本工具集
**目的**: 簡化常用開發操作

**建議實施**:
```bash
my_profile_ci4/scripts/
├── dev.sh              # 啟動開發環境
├── migrate.sh          # 執行 Migration
├── test-api.sh         # API 測試腳本
├── create-feature.sh   # 建立新功能骨架
└── archive-feature.sh  # 歸檔已完成功能
```

**範例: dev.sh**
```bash
#!/bin/bash
# 一鍵啟動開發環境
docker-compose up -d
docker exec -it my_profile_ci4-app-1 php spark migrate
echo "✅ 開發環境已啟動"
echo "API: http://localhost:8080"
echo "phpMyAdmin: http://localhost:8081"
```

**範例: create-feature.sh**
```bash
#!/bin/bash
# 使用方式: ./scripts/create-feature.sh rating-system
FEATURE_NAME=$1
mkdir -p openspec/changes/$FEATURE_NAME/specs
touch openspec/changes/$FEATURE_NAME/proposal.md
touch openspec/changes/$FEATURE_NAME/tasks.md
touch openspec/changes/$FEATURE_NAME/specs/api.md
touch openspec/changes/$FEATURE_NAME/specs/data-model.md
touch openspec/changes/$FEATURE_NAME/specs/business-rules.md
echo "✅ 功能提案骨架已建立: openspec/changes/$FEATURE_NAME/"
```

**優點**:
- 減少重複操作
- 標準化流程
- 降低人為錯誤

**需要討論的問題**:
1. 是否需要這些腳本？
2. 還有哪些常用操作需要腳本化？

---

#### A2. API 測試自動化
**目的**: 快速驗證 API 功能

**建議實施**:
```bash
my_profile_ci4/tests/api/
├── auth.test.sh        # 認證 API 測試
├── salesperson.test.sh # 業務員 API 測試
├── search.test.sh      # 搜尋 API 測試
├── admin.test.sh       # 管理員 API 測試
└── run-all.sh          # 執行所有測試
```

**範例: run-all.sh**
```bash
#!/bin/bash
echo "=== API 自動化測試 ==="
./tests/api/auth.test.sh
./tests/api/search.test.sh
./tests/api/salesperson.test.sh
./tests/api/admin.test.sh
echo "=== 測試完成 ==="
```

**優點**:
- 快速驗證功能完整性
- 回歸測試自動化
- 減少手動測試時間

**需要討論的問題**:
1. 測試範圍是否涵蓋所有端點？
2. 是否需要整合 PHPUnit 單元測試？

---

#### A3. OpenSpec 工作流程增強
**目的**: 簡化 OpenSpec 操作

**建議實施**:
```bash
# 快速建立功能提案（使用模板）
./scripts/create-feature.sh <feature-name>

# 自動歸檔功能（合併到主文件）
./scripts/archive-feature.sh <feature-name>
```

**archive-feature.sh 範例**:
```bash
#!/bin/bash
FEATURE_NAME=$1
CHANGE_DIR="openspec/changes/$FEATURE_NAME"

# 合併 API 規格
cat $CHANGE_DIR/specs/api.md >> openspec/specs/api/endpoints.md
echo "---" >> openspec/specs/api/endpoints.md

# 合併資料模型
cat $CHANGE_DIR/specs/data-model.md >> openspec/specs/models/data-models.md
echo "---" >> openspec/specs/models/data-models.md

# 移動到歸檔
mv $CHANGE_DIR openspec/changes/archived/

echo "✅ 功能已歸檔: $FEATURE_NAME"
```

**優點**:
- 保持規格文件最新
- 避免遺漏歸檔步驟
- 統一歸檔格式

**需要討論的問題**:
1. 歸檔時是否需要保留原始變更提案？
2. 如何處理規格衝突？

---

### B. 中優先級（可逐步實施）

#### B1. Git Hooks 整合
**目的**: 提交前自動檢查

**建議實施**:
```bash
.git/hooks/pre-commit
```

**檢查項目**:
- PHP 語法檢查 (`php -l`)
- CodeIgniter 4 coding standards (PHP_CodeSniffer)
- 禁止提交包含 `dd()`, `var_dump()` 的程式碼
- 檢查是否有未歸檔的 openspec/changes/

**優點**:
- 確保程式碼質量
- 防止低級錯誤
- 維護規範一致性

**需要討論的問題**:
1. 是否需要強制執行 Hooks？
2. 檢查標準是否過於嚴格？

---

#### B2. 代碼生成器
**目的**: 從 OpenSpec 規格生成程式碼骨架

**建議實施**:
```bash
./scripts/generate-code.sh openspec/changes/rating-system/
```

**功能**:
1. 解析 `specs/data-model.md` 生成 Migration
2. 解析 `specs/data-model.md` 生成 Model
3. 解析 `specs/api.md` 生成 Controller 骨架
4. 自動更新 Routes.php

**範例輸出**:
```
✅ Migration 已生成: app/Database/Migrations/CreateRatingsTable.php
✅ Model 已生成: app/Models/RatingModel.php
✅ Controller 已生成: app/Controllers/Api/RatingController.php
⚠️  請手動更新: app/Config/Routes.php (7 條路由)
```

**優點**:
- 大幅減少重複性程式碼
- 確保規格與實作一致
- 加速開發進度

**需要討論的問題**:
1. 自動生成的程度（完整實作 vs 骨架）？
2. 如何處理自定義邏輯？
3. 是否需要支援更新既有程式碼？

---

#### B3. CI/CD Pipeline
**目的**: 自動化測試與部署

**建議實施**: GitHub Actions

```yaml
.github/workflows/ci.yml
```

**流程**:
1. 每次 Push/PR 自動執行
2. 啟動 Docker 容器
3. 執行 Migration
4. 執行 PHPUnit 測試
5. 執行 API 測試腳本
6. 產生測試報告

**優點**:
- 自動化測試
- 快速發現問題
- 確保主分支穩定

**需要討論的問題**:
1. 是否需要 CI/CD？（目前是個人專案）
2. 部署環境設定？
3. 測試失敗時的處理流程？

---

### C. 低優先級（未來考慮）

#### C1. OpenAPI / Swagger 整合
**目的**: 自動生成 API 文件

**建議**: 從 `openspec/specs/api/endpoints.md` 生成 OpenAPI 3.0 spec

**優點**:
- 互動式 API 文件
- 前端開發者友善
- 支援 API 測試工具

---

#### C2. Docker 開發環境優化
**目的**: 更快的開發體驗

**建議**:
- Volume 優化（開發與生產分離）
- Hot Reload（程式碼修改即時生效）
- 多階段構建（減小映像體積）

---

#### C3. 日誌與監控
**目的**: 生產環境問題追蹤

**建議**:
- 結構化日誌（JSON 格式）
- 錯誤追蹤（Sentry 整合）
- 效能監控（APM 工具）

---

## 📝 實施優先級總結

### 立即實施（本週）
1. **開發腳本工具集** (A1) - 簡化日常操作
2. **API 測試自動化** (A2) - 確保功能穩定性
3. **OpenSpec 工作流程增強** (A3) - 簡化規格管理

### 短期實施（1-2 週）
4. **Git Hooks 整合** (B1) - 提升程式碼質量
5. **代碼生成器** (B2) - 加速開發

### 中期考慮（1-2 月）
6. **CI/CD Pipeline** (B3) - 自動化測試流程

### 長期規劃（未來）
7. OpenAPI 整合 (C1)
8. Docker 優化 (C2)
9. 監控系統 (C3)

---

## 🎯 需要討論的關鍵問題

### 問題 1: 開發腳本範圍
**選項**:
- A. 只實施基本腳本（dev.sh, migrate.sh, test-api.sh）
- B. 完整實施（包含 create-feature.sh, archive-feature.sh）
- C. 全面自動化（包含代碼生成器）

**我的建議**: 選項 B（完整實施基本腳本）

---

### 問題 2: 代碼生成器的程度
**選項**:
- A. 只生成骨架（空方法，需手動填寫邏輯）
- B. 生成基礎 CRUD（包含簡單的增刪改查邏輯）
- C. 完整生成（包含驗證、錯誤處理等）

**我的建議**: 選項 A（骨架生成）- 保留開發彈性

---

### 問題 3: CI/CD 需求
**選項**:
- A. 暫時不需要（手動測試即可）
- B. 簡單 CI（只執行測試，不部署）
- C. 完整 CI/CD（測試 + 自動部署）

**我的建議**: 選項 A（個人專案暫不需要）

---

### 問題 4: 測試策略
**選項**:
- A. 只維持目前的 Shell 腳本測試
- B. 加入 PHPUnit 單元測試（測試 Model/Controller）
- C. 完整測試覆蓋（單元測試 + 整合測試 + E2E 測試）

**我的建議**: 選項 B（加入基本單元測試）

---

## 📋 實施計畫（如果批准）

### 第 1 週
- [ ] 建立 `scripts/` 目錄
- [ ] 實施 dev.sh, migrate.sh, test-api.sh
- [ ] 實施 create-feature.sh, archive-feature.sh
- [ ] 建立 API 測試腳本（auth, search, salesperson, admin）
- [ ] 更新 DEVELOPMENT.md 說明新腳本用法

### 第 2 週
- [ ] 實施 Git pre-commit Hook
- [ ] 設計代碼生成器架構
- [ ] 開發 Migration 生成器
- [ ] 開發 Model 生成器
- [ ] 測試與優化

### 第 3-4 週
- [ ] 開發 Controller 生成器
- [ ] 整合所有生成器到 generate-code.sh
- [ ] 撰寫使用文件
- [ ] 完整測試與優化

---

## 💡 其他優化建議

### 1. 環境變數管理
建議建立 `.env.example` 模板，說明所有必要的環境變數。

### 2. 資料庫備份腳本
```bash
./scripts/backup-db.sh  # 定期備份資料庫
```

### 3. 日誌查看工具
```bash
./scripts/tail-logs.sh  # 即時查看 API 日誌
```

### 4. 效能分析工具
```bash
./scripts/profile-api.sh <endpoint>  # 分析特定 API 效能
```

---

## 🤔 下一步行動

請討論並決定：

1. **哪些優化建議應該實施？**
2. **實施的優先順序是否合適？**
3. **是否有其他需要優化的流程？**
4. **對於「需要討論的關鍵問題」，您的選擇是？**

完成討論後，我將根據決策開始實施相應的優化措施。

---

**文件狀態**: 待討論並決策
**最後更新**: 2026-01-08
