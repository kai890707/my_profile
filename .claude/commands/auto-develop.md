# 自動化功能開發 (完全自動化)

**功能描述**: $ARGUMENTS

**核心理念**: 只需要與用戶完成需求分析，其餘所有步驟完全自動化執行

---

## 🚀 AUTO-RUN 精神

```
用戶只需要：
1. 下達指令：/auto-develop [功能描述]
2. 參與需求分析（5-10 分鐘對話）
3. 確認需求無誤

系統自動完成：
4. 智能判斷開發範圍（Backend/Frontend/全棧）
5. 自動規格化（技術設計、UI 設計）
6. 自動建立 Git branch
7. 自動開發（前端 + 後端，可並行）
8. 自動測試（API + E2E + 整合）
9. 自動歸檔規格
10. 自動 Git 操作（commit, push, PR）
11. 自動部署（staging → production）

預估時間：2-4 小時完全自動執行
```

---

## 執行流程

### 🎯 Phase 1: 需求分析 (唯一需要用戶參與的階段)

**使用 Agent**: `requirements-analyst`

```
Task tool:
  subagent_type: requirements-analyst
  prompt: 進行需求訪談，分析「[功能描述]」的完整需求，並判斷開發範圍
```

**Agent 工作內容**:
1. 系統化需求訪談
   - 功能目的
   - 目標用戶
   - 使用情境
   - 技術限制
   - 優先級

2. **智能判斷開發範圍**
   - 需要 Backend API？(Y/N)
   - 需要 Frontend UI？(Y/N)
   - 需要 UI 設計變更？(Y/N)
   - 需要架構調整？(Y/N)
   - 需要資料庫變更？(Y/N)

3. 制定完整 Proposal
   - Why / What / Scope
   - 前端需求清單
   - 後端需求清單
   - Success Criteria

**產出**:
- `openspec/changes/YYYYMMDD-action-description/proposal.md`

**資料夾命名規範** (重要):
```
格式: YYYYMMDD-action-description

說明:
- YYYYMMDD: 8 位數日期（當天日期）
- action: add, fix, update, refactor, remove, optimize, enhance
- description: kebab-case 功能描述

範例:
- 20260113-add-rating-feature
- 20260113-fix-cart-calculation
- 20260113-optimize-search-performance

歸檔位置: openspec/changes/archived/YYYYMMDD-action-description/
```

- **開發範圍判斷結果**:
  ```json
  {
    "backend": true,
    "frontend": true,
    "ui_design": true,
    "architecture": false,
    "database": true,
    "priority": "high"
  }
  ```

**用戶確認點** ✋:
```
📋 需求分析完成！

功能：新增業務員評分與評論功能

開發範圍：
  ✅ Backend API (5 個端點)
  ✅ Frontend UI (2 個頁面，3 個組件)
  ✅ UI 設計 (評分星星、評論卡片)
  ✅ 資料庫 (1 個新資料表)
  ❌ 架構調整

預估工作量：
  Backend: 1.5-2 小時
  Frontend: 1.5-2 小時
  測試: 30 分鐘
  總計: 3.5-4.5 小時

接下來將進入完全自動化模式：
🤖 自動規格化
🤖 自動建立 Git branch
🤖 自動開發（前端 + 後端並行）
🤖 自動測試
🤖 自動 Git 操作
🤖 自動部署

是否開始執行？
  ✅ 是，開始 AUTO-RUN（推薦）
  ❌ 否，讓我再確認一次需求
```

---

### 🤖 Phase 2: 自動化執行（完全不需要用戶參與）

從這一步開始，系統完全自動化執行，只在完成時通知用戶。

---

#### Step 2.1: 自動建立 Git Branch

**執行操作**:
```bash
# 自動切換到 develop
git checkout develop
git pull origin develop

# 自動創建 feature branch (基於日期 + 功能描述)
# 格式: feature/YYYYMMDD-action-description
git checkout -b feature/20260113-add-rating-feature

# 自動推送到遠端
git push -u origin feature/20260113-add-rating-feature
```

**輸出訊息**:
```
🤖 AUTO-RUN: Git branch created
   Branch: feature/20260113-add-rating-feature
   Base: develop
   Status: Pushed to remote
```

---

#### Step 2.2: 自動規格化（並行執行）

根據 Phase 1 的判斷結果，**並行執行**多個規格化任務：

##### 🔹 如果需要 Backend：啟動 Backend 規格化

**使用 Agent**: `software-architect`

```
Task tool:
  subagent_type: software-architect
  prompt: 基於 proposal，設計完整的 Backend 技術架構
  run_in_background: true  # 背景執行
```

**Agent 工作內容**:
1. 設計 API 端點（Request/Response/驗證）
2. 設計資料模型（Migration/Model/關聯）
3. 定義業務規則（驗證邏輯/約束）
4. 架構設計（Service Layer/Repository Pattern）

**產出**:
- `specs/backend/api.md`
- `specs/backend/data-model.md`
- `specs/backend/business-rules.md`
- `specs/backend/architecture.md`

---

##### 🔹 如果需要 Frontend：啟動 Frontend 規格化

**使用 Agent**: `product-designer`

```
Task tool:
  subagent_type: product-designer
  prompt: 設計完整的 UI/UX，包括設計系統、組件和頁面
  run_in_background: true  # 背景執行，與 Backend 並行
```

**Agent 工作內容**:
1. UI/UX 設計（設計系統、色彩、字體）
2. 組件規格（Props/變體/使用範例）
3. 頁面規格（佈局/互動/狀態）
4. API 整合方式（React Query/錯誤處理）
5. 狀態管理（Zustand/路由）

**產出**:
- `specs/frontend/ui-ux.md`
- `specs/frontend/components.md`
- `specs/frontend/pages.md`
- `specs/frontend/api-integration.md`
- `specs/frontend/state-routing.md`

---

**等待兩個 Agent 完成** (並行執行，總時間為最長的那個)

**輸出訊息**:
```
🤖 AUTO-RUN: Specifications completed

Backend Specs:
  ✅ API Design (5 endpoints)
  ✅ Data Model (1 table: ratings)
  ✅ Business Rules (8 rules)
  ✅ Architecture (Service Layer pattern)

Frontend Specs:
  ✅ UI/UX Design (Design system defined)
  ✅ Components (3 components)
  ✅ Pages (2 pages)
  ✅ API Integration (React Query setup)
  ✅ State Management (Zustand stores)

Time elapsed: 5 minutes
```

---

#### Step 2.3: 自動任務拆解

**執行操作**:
- 讀取所有 specs
- 自動拆解為原子任務
- 識別依賴關係
- 判斷可並行的任務

**產出**:
- `tasks.md`

**任務範例**:
```markdown
# Implementation Tasks

## Backend Tasks (可與 Frontend 並行)
- [ ] Backend-1: Create ratings migration
- [ ] Backend-2: Create Rating model
- [ ] Backend-3: Create RatingController
- [ ] Backend-4: Implement POST /api/ratings
- [ ] Backend-5: Implement GET /api/ratings
- [ ] Backend-6: Write Rating tests (15 tests)

## Frontend Tasks (可與 Backend 並行)
- [ ] Frontend-1: Create RatingStars component
- [ ] Frontend-2: Create ReviewCard component
- [ ] Frontend-3: Create RatingModal component
- [ ] Frontend-4: Implement rating page
- [ ] Frontend-5: Integrate API with React Query
- [ ] Frontend-6: Write E2E tests (10 tests)

## Integration Tasks (依賴前兩者完成)
- [ ] Integration-1: Test API + Frontend integration
- [ ] Integration-2: Test authentication flow
```

**輸出訊息**:
```
🤖 AUTO-RUN: Tasks breakdown completed
   Total tasks: 14
   Backend: 6 tasks (parallel with Frontend)
   Frontend: 6 tasks (parallel with Backend)
   Integration: 2 tasks (depends on both)
```

---

#### Step 2.4: 自動開發（並行執行）

**策略**: Backend 和 Frontend **同時並行開發**

##### 🔹 Backend 開發（背景執行）

**使用 Agent**: `laravel-specialist`

```
Task tool:
  subagent_type: laravel-specialist
  prompt: 實作所有 Backend 任務，遵循 Laravel 最佳實踐
  run_in_background: true  # 背景執行
```

**Agent 工作內容**:
1. 使用 TodoWrite 追蹤進度
2. 逐步實作 Backend 任務
   - Migrations
   - Models
   - Controllers
   - Form Requests
   - Tests
3. 自動驗證每個任務
4. 自動修復錯誤

**產出**:
- 完整的 Backend 程式碼
- 所有測試通過

---

##### 🔹 Frontend 開發（背景執行，與 Backend 並行）

**使用 Agent**: `react-specialist`

```
Task tool:
  subagent_type: react-specialist
  prompt: 實作所有 Frontend 任務，確保 Type-Safe 和 High-Performance
  run_in_background: true  # 背景執行，與 Backend 並行
```

**Agent 工作內容**:
1. 使用 TodoWrite 追蹤進度
2. 逐步實作 Frontend 任務
   - Components
   - Pages
   - API Integration
   - Custom Hooks
   - E2E Tests
3. 自動驗證每個任務
4. 自動修復錯誤

**產出**:
- 完整的 Frontend 程式碼
- 所有測試通過

---

**等待兩個 Agent 完成** (並行執行)

**實時進度輸出**:
```
🤖 AUTO-RUN: Development in progress

Backend Progress:  ████████░░░░░░░░ 6/6 tasks (100%)
  ✅ Backend-1: Migration created
  ✅ Backend-2: Model created
  ✅ Backend-3: Controller created
  ✅ Backend-4: POST /api/ratings implemented
  ✅ Backend-5: GET /api/ratings implemented
  ✅ Backend-6: Tests written (15/15 passing)

Frontend Progress: ██████████░░░░░░ 6/6 tasks (100%)
  ✅ Frontend-1: RatingStars component
  ✅ Frontend-2: ReviewCard component
  ✅ Frontend-3: RatingModal component
  ✅ Frontend-4: Rating page implemented
  ✅ Frontend-5: API integrated with React Query
  ✅ Frontend-6: E2E tests written (10/10 passing)

Time elapsed: 1 hour 45 minutes
```

---

#### Step 2.5: 自動整合測試

**使用 Agent**: `qa-engineer`

```
Task tool:
  subagent_type: qa-engineer
  prompt: 執行完整的整合測試，包括 API、Frontend、整合流程
```

**Agent 工作內容**:
1. 環境檢查（Backend、Frontend 是否運行）
2. 執行 Backend 測試
   - PHPUnit tests (100% pass required)
   - Coverage check (≥80% required)
   - PHPStan Level 9
3. 執行 Frontend 測試
   - E2E tests (Playwright)
   - TypeScript compilation
   - ESLint check
4. 執行整合測試
   - Frontend → Backend 資料流
   - 認證流程
   - Error handling
5. 生成測試報告

**產出**:
- `tests/reports/test-report-<timestamp>.md`
- 測試通過/失敗清單

**自動錯誤處理**:
```
如果測試失敗：
  → 分析失敗原因
  → 自動調用對應 agent 修復
     - Backend 問題 → laravel-specialist
     - Frontend 問題 → react-specialist
  → 重新執行測試
  → 最多重試 3 次

如果仍失敗：
  → 暫停 AUTO-RUN
  → 通知用戶並提供詳細錯誤報告
```

**輸出訊息**:
```
🤖 AUTO-RUN: Testing completed

Test Results:
  ✅ Backend Tests: 201/201 passing (100%)
  ✅ Coverage: 82% (target: ≥80%)
  ✅ PHPStan: Level 9 passed (0 errors)
  ✅ Frontend Tests: 10/10 passing (100%)
  ✅ TypeScript: 0 errors
  ✅ Integration Tests: 5/5 passing (100%)

Total tests: 216
Pass rate: 100%

Time elapsed: 20 minutes
```

---

#### Step 2.6: 自動歸檔規格

**執行操作**:
1. 合併 `specs/backend/api.md` 到 `openspec/specs/api/endpoints.md`
2. 合併 `specs/backend/data-model.md` 到 `openspec/specs/models/data-models.md`
3. 合併 `specs/backend/business-rules.md` 到 `openspec/specs/business-rules.md`
4. 合併 `specs/frontend/*` 到 `openspec/specs/frontend/`
5. 移動整個變更目錄到 archived
   - 從: `openspec/changes/20260113-add-rating-feature/`
   - 到: `openspec/changes/archived/20260113-add-rating-feature/`

**輸出訊息**:
```
🤖 AUTO-RUN: Specifications archived

Merged to:
  ✅ openspec/specs/api/endpoints.md (5 new endpoints)
  ✅ openspec/specs/models/data-models.md (1 new table)
  ✅ openspec/specs/business-rules.md (8 new rules)
  ✅ openspec/specs/frontend/ui-components.md (3 new components)
  ✅ openspec/specs/frontend/pages.md (2 new pages)

Archived to:
  ✅ openspec/changes/archived/20260113-add-rating-feature/
```

---

#### Step 2.7: 自動 Git 操作

**執行操作**:

```bash
# 1. 添加所有變更
git add .

# 2. 自動生成 commit message
git commit -m "$(cat <<'EOF'
feat: add rating and review feature

## Backend Changes
- Add ratings table migration
- Create Rating model with Eloquent relationships
- Implement RatingController (5 endpoints)
- Add Form Requests for validation
- Write 15 unit and feature tests

## Frontend Changes
- Create RatingStars component
- Create ReviewCard component
- Create RatingModal component
- Implement rating page with API integration
- Add React Query hooks for rating API
- Write 10 E2E tests with Playwright

## Tests
- Backend: 201/201 tests passing (100%)
- Coverage: 82%
- Frontend: 10/10 E2E tests passing
- Integration: 5/5 tests passing

## Specifications
- API specs archived to openspec/specs/api/
- Data model archived to openspec/specs/models/
- UI specs archived to openspec/specs/frontend/

Co-Authored-By: Claude Sonnet 4.5 <noreply@anthropic.com>
EOF
)"

# 3. 推送到遠端
git push origin feature/20260113-add-rating-feature

# 4. 自動創建 Pull Request
gh pr create --base develop \
  --title "feat: add rating and review feature" \
  --body "$(cat <<'EOF'
## 📝 變更摘要
新增業務員評分與評論功能，包含完整的 Backend API 和 Frontend UI。

## 🎯 功能特性
- ✅ 使用者可對業務員進行 1-5 星評分
- ✅ 可選文字評論
- ✅ 顯示業務員平均評分
- ✅ 評分需要登入認證
- ✅ 一人一評（可修改已有評分）

## 🔄 變更類型
- [x] 新功能 (feat)
- [ ] Bug 修復 (fix)
- [ ] 重構 (refactor)

## 📋 變更內容

### Backend (Laravel)
- 新增 `ratings` 資料表
- 新增 `Rating` Model
- 新增 `RatingController` (5 個端點)
- 新增 `StoreRatingRequest` 驗證
- 新增 `UpdateRatingRequest` 驗證
- 新增 15 個測試

### Frontend (Next.js)
- 新增 `RatingStars` 組件
- 新增 `ReviewCard` 組件
- 新增 `RatingModal` 組件
- 新增評分頁面
- 整合 React Query
- 新增 10 個 E2E 測試

## 🧪 測試與品質檢查

### Backend (Laravel)
- [x] Tests: 201/201 passing (100%)
- [x] Coverage: 82% (≥80%)
- [x] PHPStan: Level 9 passed (0 errors)
- [x] Code Style: PSR-12 compliant

### Frontend (Next.js)
- [x] TypeScript: 0 errors
- [x] ESLint: 0 errors
- [x] E2E Tests: 10/10 passing
- [x] Build: Success

### 整合測試
- [x] API Integration: 5/5 tests passing
- [x] Authentication flow: Working
- [x] Error handling: Tested

## 🔗 相關連結
- OpenSpec 規格: `openspec/changes/archived/20260113-add-rating-feature/`
- 測試報告: `tests/reports/test-report-20260113.md`

## ✅ PR Merge 要求
- [x] 至少 1 人 Code Review 通過
- [x] 所有測試通過 (216/216)
- [x] 測試覆蓋率達標 (82%)
- [x] PHPStan Level 9 無錯誤
- [x] TypeScript 編譯無錯誤
- [x] OpenSpec 規格已歸檔

---
🤖 此 PR 由 /auto-develop 完全自動化生成
EOF
)"
```

**輸出訊息**:
```
🤖 AUTO-RUN: Git operations completed

Commit:
  ✅ Message: feat: add rating and review feature
  ✅ Files changed: 42 files
  ✅ Insertions: +2,847 lines
  ✅ Deletions: -0 lines

Pull Request:
  ✅ Created: #123
  ✅ Title: feat: add rating and review feature
  ✅ URL: https://github.com/user/repo/pull/123
  ✅ Base: develop
  ✅ Status: Open, awaiting review
```

---

#### Step 2.8: 自動部署到 Staging

**當 PR 被合併到 develop 時，自動觸發**

**執行**: GitHub Actions 自動部署

**輸出訊息**:
```
🤖 AUTO-RUN: PR merged to develop

CI/CD Pipeline triggered:
  ✅ Code quality checks: Passed
  ✅ Tests: 216/216 passing
  ✅ Security scan: No issues
  ✅ Build Docker images: Success
  ✅ Deploy to Staging: Success
  ✅ Health check: Passed
  ✅ Smoke tests: Passed

Staging URL: https://staging.example.com

Next step: Monitor staging for 24-48 hours, then deploy to production
```

---

#### Step 2.9: 自動部署到 Production（可選）

**如果用戶在初始階段選擇「自動部署到 Production」**

**使用 Agent**: `devops-engineer`

```
Task tool:
  subagent_type: devops-engineer
  prompt: 部署到 production，執行 Zero-downtime 部署
```

**Agent 工作內容**:
1. 部署前檢查
2. 資料庫備份
3. Blue-Green 部署
4. 健康檢查
5. 監控指標
6. 通知團隊

**輸出訊息**:
```
🤖 AUTO-RUN: Production deployment completed

Deployment:
  ✅ Strategy: Blue-Green
  ✅ Downtime: 0 seconds
  ✅ Database backup: Completed
  ✅ Health check: Passed
  ✅ Smoke tests: Passed
  ✅ Monitoring: Active

Production URL: https://example.com

Metrics:
  - Response time: 145ms (P95)
  - Error rate: 0.01%
  - CPU usage: 45%
  - Memory usage: 62%
```

---

### 🎉 Phase 3: 完成通知

**總結報告**:

```
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🎉 AUTO-RUN 完成！
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

功能：新增業務員評分與評論功能

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📊 執行摘要
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Total Time: 3 hours 15 minutes
Start: 2026-01-13 14:00:00
End: 2026-01-13 17:15:00

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
✅ 完成項目
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Phase 1: Requirements Analysis (10 minutes)
  ✅ Proposal created
  ✅ Scope defined (Backend + Frontend)
  ✅ User confirmed

Phase 2: Specifications (15 minutes, parallel)
  ✅ Backend specs (API, Data Model, Business Rules)
  ✅ Frontend specs (UI/UX, Components, Pages)
  ✅ Tasks breakdown (14 tasks)

Phase 3: Development (1 hour 45 minutes, parallel)
  Backend (1h 30m):
    ✅ 1 migration created
    ✅ 1 model created
    ✅ 1 controller created (5 methods)
    ✅ 2 form requests created
    ✅ 15 tests written (100% pass)

  Frontend (1h 45m):
    ✅ 3 components created
    ✅ 2 pages created
    ✅ API integration (React Query)
    ✅ 10 E2E tests written (100% pass)

Phase 4: Testing (20 minutes)
  ✅ Backend tests: 201/201 passing (100%)
  ✅ Coverage: 82%
  ✅ PHPStan: Level 9 passed
  ✅ Frontend tests: 10/10 passing (100%)
  ✅ Integration tests: 5/5 passing (100%)

Phase 5: Archive (5 minutes)
  ✅ Specs merged to openspec/specs/
  ✅ Change archived

Phase 6: Git Operations (5 minutes)
  ✅ All changes committed
  ✅ Pushed to remote
  ✅ PR created (#123)

Phase 7: Deployment (30 minutes)
  ✅ CI/CD triggered
  ✅ Deployed to Staging
  ✅ Health check passed

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
📋 產出清單
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Git:
  Branch: feature/20260113-add-rating-feature
  Commit: feat: add rating and review feature
  PR: #123 (Open)
  URL: https://github.com/user/repo/pull/123

Backend Files (7 files):
  ✅ app/Models/Rating.php
  ✅ app/Http/Controllers/Api/RatingController.php
  ✅ app/Http/Requests/StoreRatingRequest.php
  ✅ app/Http/Requests/UpdateRatingRequest.php
  ✅ database/migrations/2026_01_13_create_ratings_table.php
  ✅ tests/Feature/RatingTest.php
  ✅ tests/Unit/RatingModelTest.php

Frontend Files (8 files):
  ✅ components/ui/rating-stars.tsx
  ✅ components/features/rating/review-card.tsx
  ✅ components/features/rating/rating-modal.tsx
  ✅ app/(public)/salesperson/[id]/ratings/page.tsx
  ✅ lib/api/ratings.ts
  ✅ hooks/useRatings.ts
  ✅ tests/e2e/rating.spec.ts
  ✅ types/rating.ts

Specifications (Archived):
  ✅ openspec/changes/archived/20260113-add-rating-feature/proposal.md
  ✅ openspec/changes/archived/20260113-add-rating-feature/specs/backend/api.md
  ✅ openspec/changes/archived/20260113-add-rating-feature/specs/backend/data-model.md
  ✅ openspec/changes/archived/20260113-add-rating-feature/specs/backend/business-rules.md
  ✅ openspec/changes/archived/20260113-add-rating-feature/specs/frontend/ui-ux.md
  ✅ openspec/changes/archived/20260113-add-rating-feature/specs/frontend/components.md
  ✅ openspec/changes/archived/20260113-add-rating-feature/specs/frontend/pages.md
  ✅ openspec/changes/archived/20260113-add-rating-feature/tasks.md

Tests Report:
  ✅ tests/reports/test-report-20260113-171500.md

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
🔗 相關連結
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

Pull Request:
  🔗 https://github.com/user/repo/pull/123

Staging Environment:
  🔗 https://staging.example.com

Specifications:
  📁 openspec/changes/archived/20260113-add-rating-feature/

Test Report:
  📄 tests/reports/test-report-20260113-171500.md

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
⏭️ 下一步
━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━

1. 等待 Code Review
   - PR: #123
   - Reviewers: (自動指定)
   - 預估: 1-2 小時

2. PR 通過後自動合併到 develop

3. 監控 Staging 環境
   - 測試所有功能正常
   - 觀察錯誤率、效能指標
   - 建議觀察期: 24-48 小時

4. 部署到 Production
   - 手動執行: /deploy production
   - 或等待自動排程部署

━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━
```

---

## 錯誤處理與回滾

### 自動錯誤處理

在 AUTO-RUN 過程中，如果遇到錯誤：

```
┌─────────────────────────────────────────────┐
│         錯誤類型與處理策略                   │
└─────────────────────────────────────────────┘

1. 語法錯誤 (Syntax Error)
   → 自動修復（檢查語法、修正拼寫）
   → 重新執行
   → 不暫停 AUTO-RUN

2. 測試失敗 (Test Failure)
   → 分析失敗原因
   → 自動調用對應 agent 修復
   → 重新執行測試
   → 最多重試 3 次
   → 如仍失敗，暫停並通知用戶

3. Git 衝突 (Merge Conflict)
   → 暫停 AUTO-RUN
   → 通知用戶
   → 提供衝突解決指引
   → 用戶解決後繼續

4. 部署失敗 (Deployment Failure)
   → 自動回滾到上一個穩定版本
   → 通知用戶
   → 提供失敗日誌
   → 建議修復措施

5. 規格不清 (Specification Ambiguity)
   → 暫停 AUTO-RUN
   → 使用 AskUserQuestion 詢問用戶
   → 用戶回答後繼續
```

---

### 手動中止

用戶可以在任何時候中止 AUTO-RUN：

```bash
# 中止當前 AUTO-RUN
/stop-auto-run

# 會提示：
⚠️  AUTO-RUN 已中止

當前進度：
  ✅ Phase 1: Requirements Analysis
  ✅ Phase 2: Specifications
  🔄 Phase 3: Development (Backend 80%, Frontend 60%)
  ⏸️  Phase 4: Testing (未開始)

已完成的工作已保存：
  - Git branch: feature/20260113-add-rating-feature
  - Specifications: openspec/changes/20260113-add-rating-feature/
  - Backend code: 部分完成 (80%)
  - Frontend code: 部分完成 (60%)

如需繼續：
  /resume-auto-run 20260113-add-rating-feature

如需放棄：
  git checkout develop
  git branch -D feature/20260113-add-rating-feature
```

---

## 使用場景

### 場景 1: 新增完整功能（全棧）

```bash
/auto-develop 新增業務員評分與評論功能

# 用戶只需要：
# 1. 參與 10 分鐘需求分析
# 2. 確認開始執行

# 系統自動完成：
# 3-4 小時後，功能完全開發完成
# PR 已創建，等待 Review
```

---

### 場景 2: 純 Backend API

```bash
/auto-develop 新增匯出業務員資料 API

# 需求分析判斷：只需要 Backend
# 系統自動：
#   - 設計 API
#   - 實作 Controller
#   - 撰寫測試
#   - 創建 PR

# 時間：1.5-2 小時
```

---

### 場景 3: 純 Frontend UI

```bash
/auto-develop 新增 Dashboard 統計圖表

# 需求分析判斷：只需要 Frontend
# 系統自動：
#   - 設計 UI/UX
#   - 實作組件
#   - 整合 API
#   - E2E 測試
#   - 創建 PR

# 時間：2-2.5 小時
```

---

### 場景 4: Bug 修復

```bash
/auto-develop 修復購物車折扣計算錯誤

# 需求分析：
#   - 描述 Bug 現象
#   - 重現步驟
#   - 根本原因

# 系統自動：
#   - 設計修復方案
#   - 實作修復
#   - 回歸測試
#   - 創建 PR

# 時間：30 分鐘 - 1 小時
```

---

## 與其他 Commands 的對比

### `/implement` vs `/auto-develop`

| 特性 | `/implement` | `/auto-develop` |
|------|-------------|----------------|
| **範圍** | 只有 Backend | 智能判斷（Backend/Frontend/全棧） |
| **並行** | 不支援 | 前後端並行開發 |
| **Git 操作** | 需要手動 | 完全自動（branch, commit, PR） |
| **部署** | 需要手動 | 可選自動部署 |
| **用戶參與** | 3 個確認點 | 1 個確認點（需求分析後） |
| **總時間** | 1-2 小時 (只 Backend) | 2-4 小時 (全棧，但並行執行) |

---

### `/implement-frontend` vs `/auto-develop`

| 特性 | `/implement-frontend` | `/auto-develop` |
|------|---------------------|----------------|
| **範圍** | 只有 Frontend | 智能判斷（包含 Frontend） |
| **Backend 依賴** | 假設 API 已存在 | 自動開發 Backend API |
| **整合測試** | 不包含 | 自動執行 |
| **Git 操作** | 需要手動 | 完全自動 |

---

## 最佳實踐

### ✅ 適合使用 `/auto-develop` 的情況

1. **新功能開發** - 從零開始的完整功能
2. **全棧功能** - 需要前後端配合的功能
3. **時間充裕** - 可以讓系統運行 2-4 小時
4. **需求明確** - 能在 10 分鐘內完成需求分析
5. **希望省力** - 不想手動執行多個 commands

---

### ❌ 不適合使用 `/auto-develop` 的情況

1. **需要精細控制** - 想要掌控每個步驟
2. **實驗性開發** - 不確定方向，需要邊做邊調整
3. **緊急修復** - 需要立即部署，沒時間等待完整流程
4. **只改一個小地方** - 修改幾行代碼，不需要完整流程

這些情況建議使用：
- `/implement` - Backend 單獨開發
- `/implement-frontend` - Frontend 單獨開發
- 直接修改代碼 - 小改動

---

## 配置選項

在需求分析階段，可以配置 AUTO-RUN 的行為：

```
🎛️ AUTO-RUN 配置選項

1. 開發範圍
   [ ] Backend API
   [ ] Frontend UI
   [ ] 架構調整
   [ ] 資料庫變更

2. 執行模式
   ( ) 並行模式 - 前後端同時開發（推薦，更快）
   ( ) 依序模式 - Backend 完成後再開發 Frontend

3. 部署策略
   [ ] 自動部署到 Staging（PR 合併後）
   [ ] 自動部署到 Production（謹慎使用）

4. 通知設定
   [ ] 每個階段完成時通知
   [ ] 只在完成或錯誤時通知

5. 錯誤處理
   ( ) 自動修復（推薦）- 最多重試 3 次
   ( ) 暫停詢問 - 遇到錯誤立即暫停
```

---

## 總結

**`/auto-develop` 的核心價值**:

1. ✅ **極致省力** - 只需參與需求分析，其餘完全自動
2. ✅ **智能判斷** - 自動判斷需要開發什麼（Backend/Frontend/全棧）
3. ✅ **並行執行** - 前後端同時開發，節省時間
4. ✅ **完整流程** - 從需求到部署，一氣呵成
5. ✅ **高品質** - 自動測試、自動修復、自動驗證
6. ✅ **可追溯** - 所有規格自動歸檔，可隨時查看

**使用 `/auto-develop` 的理想場景**:
- 你知道要做什麼功能
- 你能在 10 分鐘內說清楚需求
- 你不想手動執行多個 commands
- 你有 2-4 小時讓系統自動運行
- 你希望獲得高品質的程式碼和完整的規格文件

---

**開始使用**: `/auto-develop [功能描述]`
