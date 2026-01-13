---
category: workflow
tags: [deployment, docker, ci-cd, production]
priority: medium
last_updated: 2026-01-13
applies_to: All YAMU deployment
related_docs: [git-workflow.md, sdd-process.md]
---

# 部署流程

## Quick Reference

- 環境: Development → Staging → Production
- Backend 端口: 8080 (Docker)
- Frontend 端口: 3001 (Next.js dev server)
- 部署方式: Docker Compose
- 自動化指令: `/deploy <environment>`
- 部署前檢查: 測試通過、品質檢查通過、規格歸檔完成
- 回滾策略: 使用 Git tag 快速回滾

## 使用場景

**適用於**:
- Feature 開發完成後部署到 Staging
- Staging 驗證通過後部署到 Production
- Hotfix 緊急部署
- 版本更新部署

**不適用於**:
- 本地開發測試（使用 `docker-compose up`）
- 實驗性功能（先在 Development 環境測試）

## 核心概念

YAMU 專案採用多環境部署策略，確保每個變更都經過充分測試才進入生產環境。

**三個環境**:
1. **Development**: 本地開發環境，開發者日常工作
2. **Staging**: 預生產環境，與生產環境配置相同，用於最終驗證
3. **Production**: 生產環境，服務真實用戶

部署流程遵循「Build Once, Deploy Everywhere」原則：
- 在 CI/CD 中構建一次 Docker Image
- 使用相同的 Image 部署到不同環境
- 通過環境變數區分環境配置

## 部署流程

### 階段 1: 開發環境 (Development)

**目的**: 本地開發和快速測試

**啟動 Backend**:
```bash
cd my_profile_laravel

# 啟動 Docker 容器
docker-compose up -d

# 查看日誌
docker-compose logs -f app

# 執行 Migration
docker exec -it my_profile_laravel_app php artisan migrate

# 執行 Seeder
docker exec -it my_profile_laravel_app php artisan db:seed
```

**啟動 Frontend**:
```bash
cd frontend

# 安裝依賴
npm install

# 啟動開發伺服器
npm run dev

# 開啟瀏覽器
open http://localhost:3001
```

**驗證**:
```bash
# 測試 Backend API
curl http://localhost:8080/api/health

# 測試 Frontend
curl http://localhost:3001
```

### 階段 2: 預生產環境 (Staging)

**目的**: 最終驗證，確保可以部署到生產

**手動部署**:
```bash
# 切換到 Staging 配置
export ENVIRONMENT=staging

# Backend 部署
cd my_profile_laravel
docker-compose -f docker-compose.staging.yml up -d

# Frontend 構建
cd frontend
npm run build
npm start

# 執行 Migration（Staging DB）
docker exec -it staging_app php artisan migrate --force
```

**自動化部署** (推薦):
```bash
# 使用 /deploy 指令
/deploy staging

# 自動執行:
# 1. 品質檢查（PHPStan, ESLint）
# 2. 執行所有測試
# 3. 構建 Docker Image
# 4. 部署到 Staging
# 5. 執行 Migration
# 6. 健康檢查
# 7. 通知部署結果
```

**驗證步驟**:
- [ ] API 健康檢查通過
- [ ] Frontend 頁面可正常載入
- [ ] 資料庫 Migration 成功
- [ ] 關鍵功能測試通過
- [ ] E2E 測試通過

### 階段 3: 生產環境 (Production)

**前置檢查**:
- [ ] Staging 環境驗證通過
- [ ] 所有測試通過
- [ ] PR 已合併到 main
- [ ] 程式碼已標記版本 tag
- [ ] 備份當前生產資料庫

**部署流程**:
```bash
# 1. 備份生產資料庫
/backup production

# 2. 部署到生產
/deploy production

# 自動執行:
# - 最終品質檢查
# - 構建生產 Docker Image
# - 滾動更新（零停機）
# - 執行 Migration
# - 健康檢查
# - 記錄部署日誌

# 3. 驗證部署結果
curl https://api.yamu.com/health
curl https://yamu.com

# 4. 監控關鍵指標
/monitor production
```

**回滾策略** (如果出現問題):
```bash
# 快速回滾到上一個版本
/rollback production

# 或手動回滾
git checkout <previous-tag>
/deploy production
```

## 實例代碼

### 完整部署流程範例

```bash
# Scenario: Feature 開發完成，準備部署

# Step 1: 確認 Feature 分支已合併
git checkout main
git pull origin main

# Step 2: 標記版本
git tag -a v1.2.0 -m "Release v1.2.0: Add rating system"
git push origin v1.2.0

# Step 3: 部署到 Staging
/deploy staging

# 輸出:
# ✓ Running quality checks...
#   - PHPStan: PASSED
#   - ESLint: PASSED
# ✓ Running tests...
#   - Backend Tests: 201/201 PASSED
#   - Frontend Tests: 45/45 PASSED
# ✓ Building Docker images...
#   - Backend: yamu-backend:v1.2.0
#   - Frontend: yamu-frontend:v1.2.0
# ✓ Deploying to Staging...
# ✓ Running migrations...
# ✓ Health check: OK
#
# Staging deployment completed successfully!
# URL: https://staging.yamu.com

# Step 4: 在 Staging 驗證功能
# - 手動測試關鍵功能
# - 執行 E2E 測試
# - 確認無問題

# Step 5: 部署到 Production
/deploy production

# 輸出:
# ⚠ Warning: You are deploying to PRODUCTION
# ✓ Backup completed: backup-20260113-100000.sql
# ✓ Building production images...
# ✓ Rolling update (zero downtime)...
#   - Scaling up new version...
#   - Waiting for new version to be healthy...
#   - Scaling down old version...
# ✓ Running migrations...
# ✓ Health check: OK
#
# Production deployment completed successfully!
# URL: https://yamu.com
#
# Rollback command (if needed):
# /rollback production v1.1.0

# Step 6: 監控生產環境
/monitor production

# Step 7: 如果發現問題，快速回滾
# /rollback production v1.1.0
```

### Docker Compose 配置

**開發環境** (`docker-compose.yml`):
```yaml
version: '3.8'

services:
  app:
    build:
      context: .
      dockerfile: docker/Dockerfile
    ports:
      - "8080:80"
    volumes:
      - .:/var/www/html
    environment:
      - APP_ENV=local
      - APP_DEBUG=true
    depends_on:
      - mysql

  mysql:
    image: mysql:8.0
    ports:
      - "3307:3306"
    environment:
      - MYSQL_DATABASE=yamu
      - MYSQL_USER=sail
      - MYSQL_PASSWORD=password
    volumes:
      - mysql_data:/var/lib/mysql

volumes:
  mysql_data:
```

**生產環境** (`docker-compose.prod.yml`):
```yaml
version: '3.8'

services:
  app:
    image: yamu-backend:${VERSION}
    ports:
      - "8080:80"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
      - APP_KEY=${APP_KEY}
      - DB_HOST=${DB_HOST}
      - DB_DATABASE=${DB_DATABASE}
      - DB_USERNAME=${DB_USERNAME}
      - DB_PASSWORD=${DB_PASSWORD}
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost/api/health"]
      interval: 30s
      timeout: 10s
      retries: 3
```

### CI/CD Pipeline (GitHub Actions)

```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  push:
    tags:
      - 'v*'

jobs:
  deploy:
    runs-on: ubuntu-latest
    steps:
      - name: Checkout code
        uses: actions/checkout@v3

      - name: Run tests
        run: |
          cd my_profile_laravel
          composer install
          composer test

      - name: Build Docker images
        run: |
          docker build -t yamu-backend:${{ github.ref_name }} .

      - name: Push to Registry
        run: |
          docker push yamu-backend:${{ github.ref_name }}

      - name: Deploy to Production
        run: |
          ssh user@production-server "
            docker pull yamu-backend:${{ github.ref_name }}
            docker-compose up -d
          "

      - name: Health Check
        run: |
          curl -f https://api.yamu.com/health || exit 1
```

## 常見錯誤

### 錯誤 1: 未執行 Migration 就部署

**錯誤示範**:
```bash
# 直接部署新版本，沒有執行 Migration
docker-compose up -d

# 應用啟動失敗：資料庫結構不匹配
```

**問題**: 程式碼需要新的資料庫結構，但 Migration 未執行

**正確做法**:
```bash
# 部署時自動執行 Migration
/deploy staging

# 或手動執行
docker exec -it app php artisan migrate --force
```

### 錯誤 2: 在生產環境使用開發配置

**錯誤示範**:
```bash
# 在生產環境設定 APP_DEBUG=true
APP_DEBUG=true
APP_ENV=local

# 洩漏敏感資訊，效能低下
```

**正確做法**:
```bash
# 生產環境必須使用生產配置
APP_DEBUG=false
APP_ENV=production
APP_KEY=<secure-key>
```

### 錯誤 3: 沒有備份就直接部署

**錯誤示範**:
```bash
# 直接部署到生產，沒有備份
/deploy production

# 部署失敗後發現資料無法恢復
```

**正確做法**:
```bash
# 部署前先備份
/backup production

# 確認備份成功後再部署
/deploy production

# 如果出問題，使用備份恢復
/restore production backup-20260113-100000.sql
```

### 錯誤 4: 忘記測試 Migration

**錯誤示範**:
```bash
# 在 Staging 跳過 Migration 測試
# 直接在 Production 執行 Migration
# Migration 失敗，導致服務中斷
```

**正確做法**:
```bash
# 在 Staging 先測試 Migration
/deploy staging

# 確認 Migration 成功
docker exec -it staging_app php artisan migrate:status

# 確認無問題後再部署 Production
/deploy production
```

## 最佳實踐

### 實作檢查清單

部署前檢查:
- [ ] 所有測試通過（Backend + Frontend）
- [ ] 品質檢查通過（PHPStan Level 9 + ESLint）
- [ ] PR 已合併到 main
- [ ] 已標記版本 tag
- [ ] 規格已歸檔到 openspec/specs/
- [ ] 已在 Staging 驗證功能

部署過程中:
- [ ] 備份生產資料庫
- [ ] 使用滾動更新（零停機）
- [ ] 執行 Migration
- [ ] 健康檢查通過
- [ ] 記錄部署日誌

部署後檢查:
- [ ] API 健康檢查通過
- [ ] Frontend 可正常訪問
- [ ] 關鍵功能正常運作
- [ ] 監控指標正常
- [ ] 錯誤日誌無異常
- [ ] 準備好回滾方案

### 注意事項

**零停機部署**:
- 使用滾動更新策略
- 先啟動新版本，確認健康後才關閉舊版本
- 資料庫 Migration 需要向下兼容

**資料庫 Migration**:
- 先新增欄位，後續版本再刪除舊欄位
- 避免在 Migration 中執行大量資料操作
- 測試 Migration rollback 功能

**環境變數管理**:
- 生產環境的環境變數存放在安全位置
- 不要在程式碼中硬編碼配置
- 使用 `.env` 文件，但不提交到 Git

**版本標記**:
- 使用語義化版本 (Semantic Versioning)
- 格式: `v<major>.<minor>.<patch>`
- 範例: `v1.2.3`

## 相關知識

### 前置知識

在進行部署前，建議先了解:
- [Git 工作流程](./git-workflow.md) - PR 合併與版本標記
- [SDD 流程](./sdd-process.md) - 確保規格完整
- Docker 基礎知識
- 環境變數管理

### 延伸閱讀

深入了解部署相關主題:
- [Backend 測試](../backend/testing.md) - 部署前測試要求
- [Frontend 測試](../frontend/testing.md) - 前端測試標準
- DevOps 最佳實踐

### 實作流程

完整的發布流程:
1. [SDD 流程](./sdd-process.md) - 開發功能
2. [Git 工作流程](./git-workflow.md) - 合併 PR
3. [本文件] - 部署到 Staging
4. [本文件] - 驗證並部署到 Production

## 決策記錄

### 當前決策 (2026-01-13)

**採用 Docker Compose 的原因**:
- 原因 1: 簡化部署流程，環境一致性
- 原因 2: 易於本地開發與生產環境匹配
- 原因 3: 適合中小型專案，無需 Kubernetes 複雜度
- 原因 4: 支援滾動更新，實現零停機部署

**考慮的替代方案**:
- 方案 A (Kubernetes): 過於複雜，專案規模不需要
- 方案 B (直接部署): 環境不一致，難以管理

**為什麼使用三環境策略**:
- Development: 快速開發迭代
- Staging: 最終驗證，避免生產問題
- Production: 穩定服務用戶

### 歷史演進

**2026-01-13**: 完善自動化部署
- 新增 `/deploy` 自動化指令
- 實現滾動更新零停機
- 加強健康檢查機制

**2026-01-10**: 初始版本
- 建立 Docker Compose 配置
- 定義三環境部署策略
- 設置 CI/CD Pipeline

## 參考資源

### 官方文檔
- [Docker Documentation](https://docs.docker.com/) - Docker 官方文檔
- [Docker Compose](https://docs.docker.com/compose/) - Docker Compose 文檔
- [Laravel Deployment](https://laravel.com/docs/11.x/deployment) - Laravel 部署指南
- [Next.js Deployment](https://nextjs.org/docs/deployment) - Next.js 部署指南

### 專案內部文檔
- `.claude/commands/deploy.md` - 部署指令文檔
- `.claude/commands/setup-cicd.md` - CI/CD 設置指令
- `docker-compose.yml` - 開發環境配置
- `docker-compose.prod.yml` - 生產環境配置

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
