# 個人作品集專案

這個 repository 包含兩個主要部分：

## 📁 專案結構

```
my_profile/
├── my_profile_ci4/        # 業務推廣系統 Backend API (CodeIgniter 4)
├── openspec/              # OpenSpec 規範文件目錄
│   ├── specs/            # 當前系統規範
│   └── changes/          # 功能變更提案
├── index.html            # 個人作品集首頁
├── portfolio.html        # 作品展示頁
├── styles.css            # 樣式表
├── 需求規格書.md         # 系統需求規格文件
└── 實作計畫書.md         # 系統實作計畫文件
```

---

## 🚀 業務推廣系統 (Backend API)

完整的 RESTful API 系統，使用 CodeIgniter 4 + MySQL 開發。

### 核心功能

- ✅ JWT 認證系統 (登入/註冊/刷新令牌)
- ✅ 三種角色權限管理 (Admin, Salesperson, User)
- ✅ 業務員檔案管理 (個人資料、公司、證照、經歷)
- ✅ 公開搜尋功能 (多條件篩選)
- ✅ 審核流程 (Admin 審核機制)
- ✅ 35 個 API 端點
- ✅ 8 個資料表

### 技術棧

- **Backend Framework**: CodeIgniter 4.6.4
- **Database**: MySQL 8.0
- **Authentication**: JWT (firebase/php-jwt)
- **Deployment**: Docker + Docker Compose
- **Development Methodology**: OpenSpec SDD

### 快速開始

```bash
# 1. 進入專案目錄
cd my_profile_ci4

# 2. 啟動 Docker 容器
docker-compose up -d

# 3. 執行資料庫遷移
docker exec -it my_profile_ci4-app-1 php spark migrate

# 4. 執行資料種子
docker exec -it my_profile_ci4-app-1 php spark db:seed IndustrySeeder
docker exec -it my_profile_ci4-app-1 php spark db:seed RegionSeeder

# 5. 測試 API
curl http://localhost:8080/api/industries
```

### 📚 開發文件

完整的開發流程和 API 文件請參考：

- **[DEVELOPMENT.md](my_profile_ci4/DEVELOPMENT.md)** - 開發工作流程指南
- **[openspec/specs/](openspec/specs/)** - API 規範和資料模型文件
- **[需求規格書.md](需求規格書.md)** - 系統需求規格
- **[實作計畫書.md](實作計畫書.md)** - 實作計畫

### API 存取點

- **API Base URL**: `http://localhost:8080/api`
- **phpMyAdmin**: `http://localhost:8081` (root / 123456)

---

## 🎨 個人作品集網站

靜態 HTML/CSS 網站，使用繁體中文。

### 頁面

- `index.html` - 首頁（個人簡介、作品集連結）
- `portfolio.html` - 作品展示頁
- `styles.css` - 共用樣式

### 預覽

直接在瀏覽器中開啟 HTML 檔案即可預覽，無需建置步驟。

---

## 📖 OpenSpec 規範驅動開發

本專案採用 **Specification-Driven Development (SDD)** 開發方法：

1. **先寫規格，後寫程式** - 所有功能都先撰寫完整規格
2. **規格即文件** - 規格文件同時是 API 文件和開發指南
3. **變更追蹤** - 所有功能變更都有完整的提案和任務拆解

### OpenSpec 目錄說明

- `openspec/specs/` - **當前系統的完整規範**（真實來源）
  - `architecture/overview.md` - 系統架構
  - `api/endpoints.md` - 35 個 API 端點規範
  - `models/data-models.md` - 資料模型和資料庫架構

- `openspec/changes/` - **功能變更提案**（新功能開發）
  - `example-rating-feature/` - 範例：評分功能提案（僅供參考）

### 開發新功能的流程

```bash
# 1. 建立變更提案
openspec change create <feature-name>

# 2. 撰寫規格 (proposal.md, specs/, tasks.md)

# 3. 按照任務清單實作

# 4. 測試完成後，歸檔到 openspec/specs/
```

詳細流程請參考 [DEVELOPMENT.md](my_profile_ci4/DEVELOPMENT.md#開發流程)

---

## 🛠️ 系統需求

### Backend API

- Docker & Docker Compose
- Node.js 18+ (用於 OpenSpec)
- Git

### 前端網站

- 任何現代瀏覽器（Chrome, Firefox, Safari, Edge）

---

## 📝 文件清單

| 文件 | 說明 |
|------|------|
| [需求規格書.md](需求規格書.md) | 系統需求規格文件 |
| [實作計畫書.md](實作計畫書.md) | 系統實作計畫 |
| [my_profile_ci4/DEVELOPMENT.md](my_profile_ci4/DEVELOPMENT.md) | 開發工作流程指南 |
| [openspec/specs/architecture/overview.md](openspec/specs/architecture/overview.md) | 系統架構文件 |
| [openspec/specs/api/endpoints.md](openspec/specs/api/endpoints.md) | API 端點規範 |
| [openspec/specs/models/data-models.md](openspec/specs/models/data-models.md) | 資料模型規範 |
| [AGENTS.md](AGENTS.md) | AI 助理開發指南 |

---

## 📊 專案狀態

### 業務推廣系統 Backend

- ✅ **Phase 1-7**: 完成 (100%)
  - ✅ 環境建置與基礎設定
  - ✅ 資料庫設計與 Models
  - ✅ 使用者認證系統 (JWT)
  - ✅ 業務員功能 API
  - ✅ 搜尋功能 API
  - ✅ Admin 管理功能 API
  - ✅ 測試與文件

- 🔄 **OpenSpec 整合**: 完成
  - ✅ 安裝 OpenSpec CLI
  - ✅ 建立規範目錄結構
  - ✅ 撰寫當前系統規範文件
  - ✅ 建立範例變更提案
  - ✅ 更新開發流程文件

### 前端開發

- 📋 **待規劃** - 前端 UI 開發（React/Vue）

---

## 🤝 貢獻指南

1. 閱讀 [DEVELOPMENT.md](my_profile_ci4/DEVELOPMENT.md) 了解開發流程
2. 使用 OpenSpec 建立變更提案
3. 撰寫完整規格文件
4. 實作功能並測試
5. 提交 Pull Request

---

## 📄 授權

此專案為個人作品集專案。

---

**最後更新**: 2026-01-08
