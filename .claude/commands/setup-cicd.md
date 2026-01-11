# 設置 CI/CD Pipeline

**任務**: 使用 DevOps Engineer Agent 設置完整的 CI/CD 自動化流程

---

## 🔴 重要：使用 DevOps Engineer Agent

**所有 CI/CD 設置必須使用 `devops-engineer` agent**：

```
當需要設置 CI/CD pipeline 時，必須使用 Task tool 啟動 devops-engineer agent
```

**devops-engineer 負責**：
- ✅ 分析專案結構和需求
- ✅ 生成 GitHub Actions workflow 配置
- ✅ 配置程式碼品質檢查（PHPStan, Pint, ESLint, TypeScript）
- ✅ 配置自動化測試（Unit, Integration, E2E）
- ✅ 配置安全掃描（Snyk, OWASP, Trivy）
- ✅ 配置 Docker 構建和推送
- ✅ 配置自動部署（Staging 自動、Production 手動）
- ✅ 配置通知（Slack, Email）
- ✅ 設置環境變數和 Secrets

**範例**：
```
Task tool:
- subagent_type: devops-engineer
- prompt: 設置完整的 CI/CD pipeline，包括程式碼品質檢查、自動化測試、安全掃描、Docker 構建和自動部署
```

詳見：`.claude/agents/devops-engineer.md`

---

## CI/CD Pipeline 架構

```
┌─────────────────────────────────────────────────────────┐
│                    Pull Request Event                    │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│              Job 1: Code Quality Checks                  │
│  • PHPStan (Level 9)                                    │
│  • Laravel Pint (PSR-12)                                │
│  • TypeScript Compiler                                  │
│  • ESLint                                               │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│              Job 2: Automated Tests                      │
│  • Backend Unit Tests (PHPUnit)                         │
│  • Backend Integration Tests                            │
│  • Frontend Unit Tests (Vitest)                         │
│  • Coverage Reports (Codecov)                           │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│              Job 3: E2E Tests                           │
│  • Playwright Tests (Multi-browser)                     │
│  • Visual Regression Tests                              │
│  • Performance Tests                                    │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│              Job 4: Security Scanning                    │
│  • Snyk (Dependency vulnerabilities)                    │
│  • OWASP Dependency Check                               │
│  • Trivy (Docker image scan)                            │
└─────────────────────────────────────────────────────────┘
                           ↓
┌─────────────────────────────────────────────────────────┐
│              Job 5: Build Docker Images                  │
│  • Multi-stage build                                    │
│  • Push to Registry (GHCR)                              │
│  • Tag with commit SHA                                  │
└─────────────────────────────────────────────────────────┘
                           ↓
        ┌──────────────────┴──────────────────┐
        │                                     │
┌───────▼────────┐                  ┌────────▼─────────┐
│ Deploy Staging │                  │ Deploy Production│
│  (Auto on      │                  │  (Manual on      │
│   develop)     │                  │   main)          │
└────────────────┘                  └──────────────────┘
```

---

## 執行流程

### Step 1: 環境檢查

devops-engineer agent 會檢查：

```bash
# 檢查專案結構
✓ Backend: Laravel 專案存在
✓ Frontend: Next.js 專案存在
✓ Docker: Dockerfile 存在
✓ 測試: 測試套件配置完成

# 檢查必要文件
✓ composer.json
✓ package.json
✓ phpunit.xml
✓ playwright.config.ts
✓ .env.example
```

### Step 2: 生成 GitHub Actions Workflows

devops-engineer agent 會生成以下檔案：

#### 1. 主要 CI/CD Pipeline
**檔案**: `.github/workflows/ci-cd.yml`

包含：
- Code Quality Checks
- Automated Tests (Backend + Frontend)
- E2E Tests
- Security Scanning
- Docker Build & Push
- Deploy to Staging (develop 分支)
- Deploy to Production (main 分支，需手動批准)

#### 2. PR 檢查 Workflow
**檔案**: `.github/workflows/pr-check.yml`

包含：
- 快速程式碼品質檢查
- 單元測試
- 檢查結果評論到 PR

#### 3. 排程任務 Workflow
**檔案**: `.github/workflows/scheduled-tasks.yml`

包含：
- 每日安全掃描
- 每週依賴更新檢查
- 效能基準測試

### Step 3: 配置 Secrets

devops-engineer agent 會提示需要在 GitHub 設置的 Secrets：

```bash
# 必須設置的 Secrets
GITHUB_TOKEN                # GitHub 自動提供
CODECOV_TOKEN              # Codecov 上傳 token

# Deployment Secrets
STAGING_SSH_KEY            # Staging 伺服器 SSH key
STAGING_HOST               # Staging 伺服器 IP
STAGING_USER               # Staging 伺服器使用者

PRODUCTION_SSH_KEY         # Production 伺服器 SSH key
PRODUCTION_HOST            # Production 伺服器 IP
PRODUCTION_USER            # Production 伺服器使用者

# Database
DB_PASSWORD                # 資料庫密碼
DB_ROOT_PASSWORD          # 資料庫 root 密碼
REDIS_PASSWORD            # Redis 密碼

# Security Scanning
SNYK_TOKEN                # Snyk API token

# Notifications
SLACK_WEBHOOK             # Slack webhook URL

# Optional
AWS_ACCESS_KEY_ID         # AWS S3 (備份用)
AWS_SECRET_ACCESS_KEY     # AWS S3
AWS_S3_BUCKET            # S3 bucket name
```

### Step 4: 配置環境變數

**檔案**: `.github/workflows/env-vars.yml`

```yaml
env:
  # PHP Version
  PHP_VERSION: '8.3'

  # Node Version
  NODE_VERSION: '18'

  # MySQL Version
  MYSQL_VERSION: '8.0'

  # Container Registry
  REGISTRY: ghcr.io
  IMAGE_NAME: ${{ github.repository }}

  # Deployment
  DEPLOY_TIMEOUT: 600
  HEALTH_CHECK_RETRIES: 5
```

### Step 5: 設置分支保護規則

devops-engineer agent 會建議設置以下分支保護：

#### develop 分支
```yaml
Required:
  ✓ Require pull request reviews (1 approval)
  ✓ Require status checks to pass:
    - code-quality
    - test
    - security-scan
  ✓ Require branches to be up to date
  ✓ Require linear history

Optional:
  ⚪ Require deployments to succeed (staging)
```

#### main 分支
```yaml
Required:
  ✓ Require pull request reviews (2 approvals)
  ✓ Require status checks to pass:
    - code-quality
    - test
    - e2e-test
    - security-scan
    - build-docker
  ✓ Require branches to be up to date
  ✓ Require linear history
  ✓ Include administrators

Optional:
  ⚪ Require deployments to succeed (production)
```

### Step 6: 測試 CI/CD Pipeline

```bash
# 1. 建立測試分支
git checkout -b test/ci-cd-setup

# 2. 提交 workflow 檔案
git add .github/workflows/
git commit -m "ci: setup CI/CD pipeline"

# 3. 推送並建立 PR
git push origin test/ci-cd-setup
gh pr create --title "Setup CI/CD Pipeline" --body "Test CI/CD workflow"

# 4. 觀察 GitHub Actions 執行
gh run list
gh run watch

# 5. 檢查執行結果
# ✓ 所有 jobs 都應該通過
# ✓ 檢查執行時間是否合理
# ✓ 檢查 artifacts 是否正確上傳
```

### Step 7: 優化與調整

根據測試結果，devops-engineer agent 會建議優化：

#### 快取策略優化
```yaml
# Composer Cache
- name: Cache Composer dependencies
  uses: actions/cache@v3
  with:
    path: vendor
    key: composer-${{ hashFiles('composer.lock') }}

# NPM Cache
- name: Cache NPM dependencies
  uses: actions/cache@v3
  with:
    path: ~/.npm
    key: npm-${{ hashFiles('package-lock.json') }}

# Docker Layer Cache
- name: Cache Docker layers
  uses: actions/cache@v3
  with:
    path: /tmp/.buildx-cache
    key: buildx-${{ github.sha }}
    restore-keys: buildx-
```

#### 並行執行優化
```yaml
strategy:
  matrix:
    # 並行測試不同環境
    php: [8.2, 8.3]
    node: [18, 20]
  fail-fast: false  # 不要因為一個失敗就全部停止
```

#### 條件執行優化
```yaml
# 只在特定檔案改變時執行
jobs:
  backend-tests:
    if: |
      contains(github.event.head_commit.modified, 'my_profile_laravel/') ||
      contains(github.event.head_commit.modified, 'composer.json')
```

---

## 配置檔案範例

### 基本配置

```yaml
# .github/workflows/ci.yml
name: CI

on:
  pull_request:
    branches: [main, develop]
  push:
    branches: [main, develop]

jobs:
  quality:
    name: Code Quality
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'

      - name: Install Dependencies
        run: |
          cd my_profile_laravel
          composer install

      - name: Run PHPStan
        run: |
          cd my_profile_laravel
          vendor/bin/phpstan analyse

      - name: Run Pint
        run: |
          cd my_profile_laravel
          vendor/bin/pint --test

  test:
    name: Tests
    runs-on: ubuntu-latest
    needs: quality

    services:
      mysql:
        image: mysql:8.0
        env:
          MYSQL_ROOT_PASSWORD: password
          MYSQL_DATABASE: testing
        ports:
          - 3306:3306

    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.3'
          coverage: xdebug

      - name: Install Dependencies
        run: |
          cd my_profile_laravel
          composer install

      - name: Run Tests
        run: |
          cd my_profile_laravel
          php artisan test --coverage --min=80
```

### 部署配置

```yaml
# .github/workflows/deploy.yml
name: Deploy

on:
  push:
    branches: [develop, main]
  workflow_dispatch:  # 手動觸發

jobs:
  deploy-staging:
    if: github.ref == 'refs/heads/develop'
    runs-on: ubuntu-latest
    environment:
      name: staging
      url: https://staging.example.com

    steps:
      - uses: actions/checkout@v4

      - name: Deploy to Staging
        run: |
          echo "${{ secrets.STAGING_SSH_KEY }}" > key
          chmod 600 key

          ssh -i key ${{ secrets.STAGING_USER }}@${{ secrets.STAGING_HOST }} << 'EOF'
            cd /var/www/my-profile
            git pull origin develop
            docker-compose pull
            docker-compose up -d
            docker-compose exec -T backend php artisan migrate --force
          EOF

      - name: Health Check
        run: curl -f https://staging.example.com/api/health

  deploy-production:
    if: github.ref == 'refs/heads/main'
    runs-on: ubuntu-latest
    environment:
      name: production
      url: https://example.com

    steps:
      - uses: actions/checkout@v4

      # 類似 staging 但多了批准步驟
```

---

## 監控 CI/CD 執行

### GitHub Actions 介面

```bash
# CLI 查看
gh run list                    # 列出最近的執行
gh run view <run-id>          # 查看特定執行
gh run watch                  # 即時監控執行
gh run rerun <run-id>         # 重新執行

# 下載 artifacts
gh run download <run-id>

# 查看日誌
gh run view <run-id> --log
```

### 執行統計

devops-engineer agent 會生成統計報告：

```markdown
## CI/CD 執行統計

### 成功率 (Last 30 days)
- Total Runs: 156
- Successful: 142 (91%)
- Failed: 14 (9%)

### 平均執行時間
- Code Quality: 2m 15s
- Tests: 5m 30s
- E2E Tests: 8m 45s
- Security Scan: 3m 20s
- Docker Build: 4m 10s
- Deploy: 3m 0s
- **Total**: ~27 minutes

### 最常見失敗原因
1. Flaky E2E tests (6 次)
2. Test timeout (4 次)
3. Network issues (2 次)
4. Dependency conflicts (2 次)

### 優化建議
- 修復 flaky tests
- 增加 test timeout
- 使用 cache 加速構建
```

---

## 成本優化

### GitHub Actions 免費額度

```yaml
Free Tier:
  - Public repositories: 無限制
  - Private repositories: 2000 minutes/month

優化策略:
  1. 使用 cache 減少重複下載
  2. 條件執行 (只在必要時運行)
  3. 並行執行加快速度
  4. Self-hosted runners (如需要)
```

### Self-hosted Runners (可選)

```bash
# 設置 self-hosted runner
# 在自己的伺服器上運行，不消耗 GitHub Actions 分鐘數

# 1. 到 GitHub Settings → Actions → Runners → New self-hosted runner
# 2. 按照指示設置
# 3. 在 workflow 中使用

jobs:
  build:
    runs-on: self-hosted  # 使用 self-hosted runner
```

---

## 進階功能

### 1. Matrix Testing

```yaml
jobs:
  test:
    strategy:
      matrix:
        php: [8.2, 8.3]
        laravel: [10, 11]
        include:
          - php: 8.3
            laravel: 11
            coverage: true

    steps:
      - name: Setup PHP ${{ matrix.php }}
        uses: shivammathur/setup-php@v2
        with:
          php-version: ${{ matrix.php }}
```

### 2. Reusable Workflows

```yaml
# .github/workflows/reusable-test.yml
name: Reusable Test Workflow

on:
  workflow_call:
    inputs:
      php-version:
        required: true
        type: string

jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4
      - name: Run Tests
        run: echo "Testing with PHP ${{ inputs.php-version }}"

# 使用
jobs:
  test-php-82:
    uses: ./.github/workflows/reusable-test.yml
    with:
      php-version: '8.2'
```

### 3. Composite Actions

```yaml
# .github/actions/setup-app/action.yml
name: Setup Application
description: Setup Laravel and Next.js application

runs:
  using: composite
  steps:
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.3'

    - name: Setup Node
      uses: actions/setup-node@v4
      with:
        node-version: '18'

    - name: Install Dependencies
      shell: bash
      run: |
        cd my_profile_laravel && composer install
        cd ../frontend && npm ci
```

---

## 故障排除

### 常見問題

#### 問題 1: Tests 超時

```yaml
# 解決：增加 timeout
jobs:
  test:
    timeout-minutes: 30  # 預設是 360 分鐘
    steps:
      - name: Run Tests
        timeout-minutes: 15  # 單步驟 timeout
```

#### 問題 2: Cache 未生效

```yaml
# 確保 cache key 正確
- uses: actions/cache@v3
  with:
    path: vendor
    key: composer-${{ runner.os }}-${{ hashFiles('**/composer.lock') }}
    restore-keys: |
      composer-${{ runner.os }}-
```

#### 問題 3: Docker build 太慢

```yaml
# 使用 buildx cache
- name: Build and push
  uses: docker/build-push-action@v5
  with:
    cache-from: type=gha
    cache-to: type=gha,mode=max
```

---

## 檢查清單

### 設置完成檢查

- [ ] GitHub Actions workflows 已建立
- [ ] 所有必要的 Secrets 已設置
- [ ] 分支保護規則已配置
- [ ] 測試 workflow 執行成功
- [ ] 部署 workflow 測試成功
- [ ] 通知設置正常（Slack）
- [ ] Coverage 報告正常上傳
- [ ] Docker images 成功構建
- [ ] Staging 自動部署成功
- [ ] Production 手動部署測試成功

### 維護檢查（每月）

- [ ] 檢查 workflow 執行統計
- [ ] 優化執行時間
- [ ] 更新 Actions versions
- [ ] 檢查 security alerts
- [ ] Review 失敗的 runs

---

## 輸出範例

```
🚀 CI/CD Pipeline Setup Completed!

✅ Generated Files:
- .github/workflows/ci-cd.yml
- .github/workflows/pr-check.yml
- .github/workflows/scheduled-tasks.yml
- .github/actions/setup-app/action.yml

✅ Configuration:
- PHP Version: 8.3
- Node Version: 18
- MySQL Version: 8.0
- Coverage Tool: Xdebug
- Container Registry: GHCR

⚠️  Required Secrets (請在 GitHub 設置):
- STAGING_SSH_KEY
- STAGING_HOST
- STAGING_USER
- PRODUCTION_SSH_KEY
- PRODUCTION_HOST
- PRODUCTION_USER
- SNYK_TOKEN
- SLACK_WEBHOOK

📋 Next Steps:
1. 設置 GitHub Secrets
2. 配置分支保護規則
3. 建立測試 PR 驗證 CI/CD
4. 監控第一次部署

📊 Expected Performance:
- Code Quality: ~2 minutes
- Tests: ~5 minutes
- E2E Tests: ~9 minutes
- Security Scan: ~3 minutes
- Docker Build: ~4 minutes
- Total: ~23 minutes

🔗 Useful Links:
- GitHub Actions: https://github.com/<org>/<repo>/actions
- Branch Protection: https://github.com/<org>/<repo>/settings/branches
- Secrets: https://github.com/<org>/<repo>/settings/secrets
```

---

**相關命令**:
- `/setup-monitoring` - 設置監控系統
- `/deploy` - 部署到生產環境
- `/test` - 執行全面測試
