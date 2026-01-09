# YAMU 業務員推廣系統 - Laravel API

[![CI](https://github.com/yourusername/my_profile_laravel/workflows/CI/badge.svg)](https://github.com/yourusername/my_profile_laravel/actions)
[![PHPStan Level 9](https://img.shields.io/badge/PHPStan-level%209-brightgreen.svg)](https://phpstan.org/)
[![PHP 8.4](https://img.shields.io/badge/PHP-8.4-777BB4.svg)](https://www.php.net/)
[![Laravel 11](https://img.shields.io/badge/Laravel-11-FF2D20.svg)](https://laravel.com/)

> 業務員推廣管理系統 - Laravel 11 後端 API，從 CodeIgniter 4 遷移而來

---

## 📋 目錄

- [專案概述](#專案概述)
- [快速開始](#快速開始)
- [技術棧](#技術棧)
- [專案結構](#專案結構)
- [開發指南](#開發指南)
- [測試](#測試)
- [部署](#部署)
- [API 文件](#api-文件)

---

## 專案概述

### 系統功能

- **業務員管理**: 個人檔案、公司資訊、工作經歷、證照管理
- **認證系統**: JWT 雙 Token (Access + Refresh) 機制
- **權限控制**: RBAC 角色權限管理 (Admin, Salesperson, User)
- **審核流程**: 公司、證照、個人資料審核機制
- **搜尋功能**: 業務員公開搜尋、多條件篩選
- **參考資料**: 產業類別、服務地區管理

### 系統狀態

| 模組 | 狀態 | 測試覆蓋率 | 備註 |
|------|------|-----------|------|
| Module 01: Project Setup | ✅ 完成 | N/A | 環境配置、套件安裝 |
| Module 02: Database Layer | ✅ 完成 | N/A | 8 個資料表、Models、Migrations |
| Module 03: Auth Module | ✅ 完成 | 100% | JWT 認證系統 |
| Module 04: API Endpoints | ✅ 完成 | 100% | 31 個 API 端點 |
| Module 05: Business Logic | ✅ 完成 | 100% | Service Layer + Repository Pattern |
| Module 06: Testing | ✅ 完成 | 80%+ | 201 tests passing (804 assertions) |
| Module 07: Deployment | ✅ 完成 | N/A | Docker + CI/CD + Blue-Green |

**總進度**: 7/7 模組完成 (100%)

---

## 快速開始

### 前置需求

- Docker & Docker Compose
- PHP 8.3+ (本機開發)
- Composer 2.6+
- Git

### 安裝步驟

```bash
# 1. Clone 專案
git clone https://github.com/yourusername/my_profile_laravel.git
cd my_profile_laravel

# 2. 複製環境變數
cp .env.example .env

# 3. 啟動 Docker 容器
docker-compose up -d

# 4. 安裝依賴
docker exec my_profile_laravel_app composer install

# 5. 生成金鑰
docker exec my_profile_laravel_app php artisan key:generate
docker exec my_profile_laravel_app php artisan jwt:secret

# 6. 執行資料庫遷移
docker exec my_profile_laravel_app php artisan migrate

# 7. 執行資料種子
docker exec my_profile_laravel_app php artisan db:seed

# 8. 測試 API
curl http://localhost:8082/api/health
```

### 預設存取點

- **API Base URL**: http://localhost:8082/api
- **Health Check**: http://localhost:8082/api/health
- **MySQL**: localhost:3307 (root / 123456)
- **Redis**: localhost:6379

---

## 技術棧

### 核心技術

| 類別 | 技術 | 版本 |
|------|------|------|
| **Framework** | Laravel | 11.x |
| **PHP** | PHP | 8.4 |
| **Database** | MySQL | 8.0 |
| **Cache** | Redis | 7.x |
| **Authentication** | tymon/jwt-auth | 2.x |
| **Testing** | Pest PHP | 3.x |
| **Static Analysis** | PHPStan | Level 9 |
| **Container** | Docker | 24.x |

### 開發套件

```json
{
  "require": {
    "php": "^8.3",
    "laravel/framework": "^11.0",
    "tymon/jwt-auth": "^2.0"
  },
  "require-dev": {
    "phpstan/phpstan": "^1.10",
    "pestphp/pest": "^3.0",
    "larastan/larastan": "^2.0"
  }
}
```

---

## 專案結構

```
my_profile_laravel/
├── app/
│   ├── Http/
│   │   ├── Controllers/Api/        # API Controllers
│   │   ├── Middleware/             # JWT Auth, Role Check
│   │   ├── Requests/               # Form Request Validation
│   │   └── Resources/              # API Response Resources
│   ├── Services/                   # Business Logic Layer
│   ├── Models/                     # Eloquent Models (8 個)
│   └── Exceptions/                 # Custom Exceptions
├── database/
│   ├── migrations/                 # 資料庫遷移 (15 個)
│   ├── seeders/                    # 資料種子
│   └── factories/                  # Model Factories
├── tests/
│   ├── Feature/                    # API 整合測試 (165 tests)
│   │   ├── Auth/                   # 認證測試 (44 tests)
│   │   ├── Profile/                # 個人檔案測試 (51 tests)
│   │   ├── Company/                # 公司管理測試 (44 tests)
│   │   └── Admin/                  # 管理員測試 (25 tests)
│   └── Unit/                       # 單元測試 (36 tests)
│       └── Services/               # Service Layer 測試
├── docker/                         # Docker 配置
│   ├── nginx/                      # Nginx 配置
│   ├── php/                        # PHP 配置
│   └── mysql/                      # MySQL 配置
├── scripts/                        # 部署腳本
│   ├── switch-traffic.sh           # 藍綠部署流量切換
│   ├── health-check.sh             # 健康檢查
│   ├── backup-database.sh          # 資料庫備份
│   └── restore-database.sh         # 資料庫還原
└── .github/workflows/              # CI/CD Pipeline
    ├── ci.yml                      # 持續整合
    ├── deploy-staging.yml          # Staging 部署
    └── deploy-production.yml       # Production 部署
```

---

## 開發指南

### 本機開發

```bash
# 啟動開發環境
docker-compose up -d

# 查看 log
docker logs my_profile_laravel_app -f

# 進入容器
docker exec -it my_profile_laravel_app bash

# 執行 artisan 命令
docker exec my_profile_laravel_app php artisan <command>
```

### 程式碼規範

- **Strict Types**: 所有檔案使用 `declare(strict_types=1)`
- **PHPStan Level 9**: 最嚴格的靜態分析
- **Type Declarations**: 完整的參數和返回值類型聲明
- **Readonly Properties**: 使用 readonly class/properties
- **PSR-12**: 遵循 PSR-12 程式碼風格

### 程式碼檢查

```bash
# PHPStan 靜態分析
./vendor/bin/phpstan analyse --memory-limit=2G

# 執行測試
./vendor/bin/pest

# 測試覆蓋率
./vendor/bin/pest --coverage --min=80
```

### Git Workflow

```bash
# 功能開發
git checkout -b feature/new-feature
git commit -m "feat: add new feature"
git push origin feature/new-feature

# 合併到 develop (自動部署到 Staging)
git checkout develop
git merge feature/new-feature
git push origin develop

# 發佈到 Production
git checkout main
git merge develop
git tag v1.0.0
git push origin main --tags
```

---

## 測試

### 執行測試

```bash
# 所有測試
./vendor/bin/pest

# 特定測試
./vendor/bin/pest --filter=AuthTest

# 測試覆蓋率
./vendor/bin/pest --coverage --min=80

# 平行測試
./vendor/bin/pest --parallel
```

### 測試結構

```
tests/
├── Feature/                # 165 tests (API 整合測試)
│   ├── Auth/
│   │   ├── RegisterTest.php
│   │   ├── LoginTest.php
│   │   ├── LogoutTest.php
│   │   ├── MeTest.php
│   │   └── RefreshTest.php
│   ├── Profile/            # 業務員個人檔案
│   ├── Company/            # 公司管理
│   └── Admin/              # 管理員功能
└── Unit/                   # 36 tests (單元測試)
    └── Services/
        ├── AuthServiceTest.php
        ├── CompanyServiceTest.php
        └── SalespersonProfileServiceTest.php
```

### 測試結果

```
Tests:    201 passed (165 Feature + 36 Unit)
Duration: 6.77s
Assertions: 804
Coverage: 80%+
```

---

## 部署

### 環境

- **Development**: 本機 Docker 環境
- **Staging**: 自動部署 (push to `develop`)
- **Production**: 藍綠部署 (push to `main`)

### 部署流程

```bash
# 1. Staging 部署 (自動)
git push origin develop
# → GitHub Actions 自動部署到 Staging

# 2. Production 部署 (自動)
git tag v1.0.0
git push origin main --tags
# → GitHub Actions 自動執行藍綠部署

# 3. 手動部署
./scripts/deploy-production.sh
```

### 藍綠部署

- **零停機時間**: 新版本先啟動，通過健康檢查後才切換流量
- **快速回滾**: 保留舊版本容器，出問題立即切回
- **健康檢查**: 自動驗證新環境狀態

詳細部署文件請參考: [DEPLOYMENT.md](./DEPLOYMENT.md)

---

## API 文件

### 認證端點

```bash
# 註冊
POST /api/auth/register
Content-Type: application/json
{
  "username": "john_doe",
  "email": "john@example.com",
  "password": "password123",
  "full_name": "王小明",
  "phone": "0912345678"
}

# 登入
POST /api/auth/login
{
  "email": "john@example.com",
  "password": "password123"
}

# 刷新 Token
POST /api/auth/refresh
Authorization: Bearer <refresh_token>

# 取得當前用戶資訊
GET /api/auth/me
Authorization: Bearer <access_token>

# 登出
POST /api/auth/logout
Authorization: Bearer <access_token>
```

### 業務員端點

```bash
# 取得個人檔案
GET /api/profile
Authorization: Bearer <access_token>

# 更新個人檔案
PUT /api/profile
Authorization: Bearer <access_token>

# 公開搜尋業務員
GET /api/profiles?search=keyword&industry_id=1&region_id=2
```

### 完整 API 規範

完整的 API 端點規範請參考 OpenSpec 文件:
- [API Endpoints](../openspec/specs/api/endpoints.md)
- [Data Models](../openspec/specs/models/data-models.md)
- [Business Rules](../openspec/specs/business-rules.md)

---

## 監控與日誌

### 健康檢查

```bash
# 基本健康檢查
curl http://localhost:8082/api/health
# Response: "healthy"

# 詳細健康檢查
curl http://localhost:8082/api/health/detailed
# Response: JSON with database, cache, app status
```

### 日誌查看

```bash
# Application logs
docker exec my_profile_laravel_app tail -f storage/logs/laravel.log

# API logs
docker exec my_profile_laravel_app tail -f storage/logs/api.log

# Security logs
docker exec my_profile_laravel_app tail -f storage/logs/security.log
```

---

## 貢獻指南

1. Fork 專案
2. 建立功能分支 (`git checkout -b feature/amazing-feature`)
3. 提交變更 (`git commit -m 'feat: add amazing feature'`)
4. 推送到分支 (`git push origin feature/amazing-feature`)
5. 建立 Pull Request

### Commit 規範

```
feat: 新功能
fix: 修復 bug
docs: 文件更新
style: 程式碼格式調整
refactor: 重構
test: 測試相關
chore: 建置工具或輔助工具變動
```

---

## License

此專案為個人作品集專案。

---

## 聯絡資訊

- **GitHub**: [yourusername](https://github.com/yourusername)
- **Email**: your.email@example.com

---

**Last Updated**: 2026-01-09
**Version**: 1.0.0
