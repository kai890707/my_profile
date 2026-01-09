# Frontend Specifications

**Project**: YAMU Frontend SPA
**Framework**: Next.js 16.1.1 + TypeScript + React 19
**Status**: ✅ Completed
**Last Updated**: 2026-01-09

---

## Overview

完整的前端規格文檔，涵蓋 UI/UX 設計、組件庫、頁面結構、API 整合和狀態管理。

---

## Specification Files

### 1. ui-components.md
**內容**: UI 組件庫完整規格
- 30+ 個 UI 組件（Button, Input, Card, Modal, etc.)
- Radix UI 整合組件（Dropdown, Dialog, Select, Tabs)
- Layout 組件（Header, Footer, Sidebar)
- 特色組件（SalespersonCard, SearchFilters, StatsCard)

**包含**:
- Props 定義
- 變體說明
- 使用範例
- 檔案位置

### 2. api-integration.md
**內容**: API 整合完整規範
- Axios Client 配置
- Request/Response Interceptors
- TypeScript 類型定義
- React Query 整合
- 錯誤處理機制

**包含**:
- API Client 設置
- 31 個 API 端點整合
- Custom Hooks 設計
- Query Keys 管理

### 3. state-routing.md
**內容**: 狀態管理與路由規範
- React Query (伺服器狀態)
- Zustand (客戶端狀態)
- Next.js App Router 配置
- Route Guards (Middleware)
- 權限控制

**包含**:
- 18 個路由定義
- 狀態管理模式
- 導航處理
- 權限驗證

---

## Design System

詳細設計系統請參考: `frontend/DESIGN_SYSTEM.md`

**色彩方案**:
- Primary: #0EA5E9 (Sky-500)
- Secondary: #14B8A6 (Teal-500)
- Success: #10B981 (Green-500)
- Warning: #F59E0B (Yellow-500)
- Error: #EF4444 (Red-500)

**UI 風格**: 活潑年輕 - 明亮色彩、圓角設計

---

## Project Structure

```
frontend/
├── app/                    # Next.js App Router
│   ├── (public)/          # 公開頁面
│   ├── (auth)/            # 認證頁面
│   ├── (dashboard)/       # 業務員 Dashboard
│   └── (admin)/           # 管理員後台
├── components/
│   ├── ui/                # 基礎 UI 組件
│   ├── layout/            # Layout 組件
│   └── features/          # 功能組件
├── lib/
│   ├── api/               # API 客戶端
│   ├── auth/              # 認證邏輯
│   ├── query/             # React Query 配置
│   └── utils/             # 工具函數
├── hooks/                 # Custom Hooks
├── types/                 # TypeScript 類型
└── store/                 # Zustand Store
```

---

## Implementation Status

### Completed ✅
- ✅ 18 Pages implemented
- ✅ 30+ Components developed
- ✅ 31 API endpoints integrated
- ✅ Authentication system complete
- ✅ Route guards configured
- ✅ Error handling implemented
- ✅ Loading states added
- ✅ Recharts integration done
- ✅ TypeScript 0 errors
- ✅ Build successful

### Pending ⚠️
- ⚠️ Responsive design manual testing
- ⚠️ Cross-browser compatibility testing
- ⚠️ E2E testing (Playwright)
- ⚠️ Performance optimization (optional)

---

## Key Features

### Authentication
- JWT token management (Access + Refresh)
- Auto token refresh
- Role-based access control
- Protected routes

### Public Pages
- Homepage with search
- Advanced search with filters
- Salesperson detail pages
- SEO optimized

### Salesperson Dashboard
- Profile management
- Experience CRUD
- Certification upload
- Approval status tracking

### Admin Panel
- Approval management
- User management
- System settings
- Statistics dashboard with charts

---

## Technologies

**Core**:
- Next.js 16.1.1 (App Router, Turbopack)
- React 19
- TypeScript 5

**UI**:
- Tailwind CSS 3.4.1
- Radix UI (Dropdown, Dialog, Select, Tabs)
- Lucide React (Icons)
- Recharts 2.x (Charts)

**State Management**:
- React Query 5.65.0
- Zustand 5.0.4

**Forms**:
- React Hook Form 7.54.2
- Zod 3.24.1

**HTTP**:
- Axios 1.7.9

---

## Testing

### Automated Tests ✅
- HTTP endpoint tests: 100% pass (14/14)
- TypeScript compilation: 0 errors
- Production build: Success

### Manual Tests ⚠️
- Responsive design: Pending
- Cross-browser: Pending
- Accessibility: Pending

---

## Documentation

**Project Reports**:
- `frontend/PROJECT_COMPLETION_REPORT.md` - Overall completion status
- `frontend/PHASE_8_COMPLETION_REPORT.md` - Latest phase report
- `frontend/WORKFLOW_REVIEW.md` - Development process review

**Testing Guides**:
- `frontend/MANUAL_TESTING_GUIDE.md` - Manual testing instructions
- `frontend/TESTING_STATUS.md` - Current testing status

**Design**:
- `frontend/DESIGN_SYSTEM.md` - Complete design system

---

## Related Specifications

**Backend API**: `openspec/specs/api/endpoints.md`
**Data Models**: `openspec/specs/models/data-models.md`
**Architecture**: `openspec/specs/architecture/overview.md`

---

## Change History

| Date | Change | Status |
|------|--------|--------|
| 2026-01-08 | Initial frontend project proposal | ✅ Completed |
| 2026-01-08 | Phase 1-6 development | ✅ Completed |
| 2026-01-09 | Phase 7-8 testing & charts | ✅ Completed |
| 2026-01-09 | Spec archived to openspec/specs/frontend/ | ✅ Done |

---

**Last Updated**: 2026-01-09
**Maintainer**: Development Team
**Version**: 1.0
