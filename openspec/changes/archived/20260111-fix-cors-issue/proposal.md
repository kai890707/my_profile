# Proposal: 修復前端呼叫 API CORS 問題

**Status**: Draft
**Created**: 2026-01-11
**Priority**: High

---

## Why (問題陳述)

### 目前的問題

Frontend 應用程式在呼叫 Backend API 時遇到 CORS (Cross-Origin Resource Sharing) 錯誤，導致無法正常進行 API 通訊。

**根本原因**:
- Frontend 當前實際運行在 `http://localhost:3002`
- Backend CORS 配置允許的 Origins: `http://localhost:3001` (以及 3000, 5173, 8080)
- **端口不匹配** 導致瀏覽器阻止跨域請求

**影響範圍**:
- 影響的用戶: 所有開發者在本地開發時
- 影響的功能: 所有需要 API 通訊的功能（登入、註冊、資料查詢等）

**技術細節**:
```
Frontend Origin:    http://localhost:3002
Backend CORS 允許:  http://localhost:3001
結果:               ❌ CORS Policy Blocked
```

---

## What (解決方案)

### 功能概述

統一 Frontend 開發環境的端口配置，確保與 Backend CORS 配置一致。

### 核心價值

1. **開發體驗**: 開發者可以正常進行本地開發，無需手動處理 CORS 問題
2. **配置一致性**: Frontend 配置文件(.env.local)、實際運行端口、Backend CORS 配置三者保持一致
3. **環境標準化**: 建立標準的開發環境配置，避免未來類似問題

### 主要功能

1. **確保 Frontend 運行在正確端口**
   - 終止當前運行在 port 3002 的 Next.js 進程
   - 重新啟動 Frontend，確保運行在 port 3001
   - 驗證端口佔用情況

2. **驗證配置一致性**
   - 檢查 `.env.local` 配置: `NEXT_PUBLIC_APP_URL=http://localhost:3000`
   - 檢查 `package.json` 啟動腳本
   - 確認 Backend CORS 配置已包含 `http://localhost:3001`

3. **環境配置文檔化**
   - 更新開發文檔，明確指定 Frontend 應運行在 port 3001
   - 提供端口衝突的排查指南
   - 建立標準的啟動流程

---

## Scope (範圍)

### In Scope (本次實作)

- ✅ 終止當前 port 3002 的 Next.js 進程
- ✅ 確保 Frontend 重新啟動在 port 3001
- ✅ 驗證 Backend CORS 配置已包含 localhost:3001
- ✅ 測試 API 通訊是否正常
- ✅ 更新 `.env.local` 配置為正確端口（如需要）
- ✅ 撰寫端口配置說明文檔

### Out of Scope (未來擴充)

- ❌ 修改 Backend CORS 配置（現有配置已正確）
- ❌ 支援動態端口切換
- ❌ 生產環境 CORS 配置（本次只處理開發環境）
- ❌ 自動端口檢測與切換機制
- ❌ Docker 環境配置

---

## Success Criteria (驗收標準)

### 功能性驗收

- [ ] Frontend 成功運行在 `http://localhost:3001`
- [ ] 登入 API 呼叫成功，無 CORS 錯誤
- [ ] 公開 API（搜尋、健康檢查）呼叫成功
- [ ] 需要認證的 API（Dashboard、Profile）呼叫成功
- [ ] 瀏覽器開發者工具 Network Tab 無 CORS 錯誤訊息
- [ ] OPTIONS 預檢請求返回正確的 CORS Headers

### 非功能性驗收

- [ ] 配置: `.env.local` 的 `NEXT_PUBLIC_APP_URL` 與實際運行端口一致
- [ ] 文檔: 開發文檔中明確說明 Frontend 端口為 3001
- [ ] 穩定性: 重啟服務後端口配置保持正確

### 測試案例

```bash
# 1. 驗證 Frontend 運行端口
curl http://localhost:3001
# 預期: 返回 Next.js 頁面

# 2. 驗證 CORS - 登入 API
curl -X POST http://localhost:8080/api/auth/login \
  -H "Origin: http://localhost:3001" \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"password123"}'
# 預期: 返回 200，包含 Access-Control-Allow-Origin: http://localhost:3001

# 3. 驗證 CORS - 預檢請求
curl -X OPTIONS http://localhost:8080/api/auth/login \
  -H "Origin: http://localhost:3001" \
  -H "Access-Control-Request-Method: POST"
# 預期: 返回 200，包含 CORS Headers

# 4. 瀏覽器測試
# 訪問 http://localhost:3001/login
# 嘗試登入，檢查 Network Tab 是否有 CORS 錯誤
```

---

## Dependencies (相依性)

### 必要的前置條件

- Frontend 開發服務器可以重啟
- Backend API 服務正常運行 (http://localhost:8080)
- Port 3001 未被其他服務佔用

### 技術相依

- Next.js 16.1.1 開發服務器
- Laravel 11 Backend CORS 配置 (config/cors.php)
- Node.js 環境

---

## Risks (風險評估)

| 風險 | 影響 | 機率 | 緩解措施 |
|------|------|------|----------|
| Port 3001 被其他服務佔用 | Medium | Low | 提供端口檢查腳本，自動找到可用端口 |
| 重啟後端口自動切換到其他 | Low | Medium | 明確在 package.json 指定端口，添加啟動驗證 |
| 環境變數配置不一致 | High | Low | 統一檢查並更新所有配置文件 |
| 開發者未使用正確啟動方式 | Medium | Medium | 提供標準啟動腳本和文檔 |

---

## Open Questions (待確認問題)

- [x] Frontend 預期運行端口是什麼？ → **Port 3001**
- [x] Backend CORS 配置是否需要修改？ → **不需要，已包含 3001**
- [x] 是否需要支援多個開發端口？ → **不需要，統一使用 3001**
- [ ] 是否需要在 package.json 明確指定端口？
- [ ] 是否需要添加端口檢查腳本？

---

## Implementation Notes

### 修復步驟概覽

1. **終止舊進程**
   ```bash
   # 查找運行在 3002 的進程
   lsof -ti:3002 | xargs kill -9
   ```

2. **重新啟動 Frontend**
   ```bash
   cd frontend
   PORT=3001 npm run dev
   # 或修改 package.json: "dev": "next dev -p 3001"
   ```

3. **驗證配置**
   ```bash
   # 檢查 .env.local
   cat frontend/.env.local | grep NEXT_PUBLIC_APP_URL
   # 應該顯示: NEXT_PUBLIC_APP_URL=http://localhost:3001
   ```

4. **測試 API 通訊**
   - 訪問 http://localhost:3001
   - 嘗試登入
   - 檢查 Network Tab

---

**Next Step**: 使用 `/spec 20260111-fix-cors-issue` 撰寫詳細規格
