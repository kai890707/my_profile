# 部署到生產環境

**環境**: $ARGUMENTS (staging | production)

**任務**: 使用 DevOps Engineer Agent 執行自動化部署流程

---

## 🔴 重要：使用 DevOps Engineer Agent

**所有部署任務必須使用 `devops-engineer` agent**：

```
當需要部署到生產環境時，必須使用 Task tool 啟動 devops-engineer agent
```

**devops-engineer 負責**：
- ✅ 部署前檢查（測試、備份、健康狀態）
- ✅ 自動化部署流程（Zero-downtime、Blue-Green）
- ✅ 資料庫 Migration 執行
- ✅ 服務健康檢查
- ✅ 部署後驗證
- ✅ 監控告警設置
- ✅ 回滾機制（如需要）

**範例**：
```
Task tool:
- subagent_type: devops-engineer
- prompt: 部署當前版本到 production 環境，執行完整的部署前檢查、Zero-downtime 部署、並驗證服務健康
```

詳見：`.claude/agents/devops-engineer.md`

---

## 部署環境

### Staging (測試環境)
- **用途**: 測試新功能、整合測試
- **URL**: https://staging.example.com
- **自動部署**: develop 分支合併時自動觸發
- **資料**: 測試資料
- **監控**: 基本監控

### Production (生產環境)
- **用途**: 正式服務
- **URL**: https://example.com
- **部署方式**: 手動批准 + 自動部署
- **資料**: 真實資料
- **監控**: 完整監控 + 告警
- **備份**: 自動每日備份

---

## 部署流程

### 自動部署 (Staging)

當 PR 合併到 `develop` 分支時：

```
PR Merged → develop
    ↓
GitHub Actions Triggered
    ↓
1. Code Quality Checks
   → PHPStan, Pint, ESLint, TypeScript
    ↓
2. Run Tests
   → Backend Tests (100% pass)
   → Frontend Tests
   → E2E Tests
    ↓
3. Security Scan
   → Snyk
   → OWASP Dependency Check
   → Trivy (Docker image scan)
    ↓
4. Build Docker Images
   → Multi-stage build
   → Push to Registry
    ↓
5. Deploy to Staging
   → Pull latest images
   → Run migrations
   → Restart services
   → Health check
    ↓
6. Post-Deployment Tests
   → Smoke tests
   → API tests
    ↓
7. Notify Team
   → Slack notification
```

### 手動部署 (Production)

```bash
# 方式 1: 使用 command
/deploy production

# 方式 2: 手動觸發 GitHub Actions
# 在 GitHub Actions 頁面手動觸發 "Deploy to Production" workflow
```

**部署流程**：

```
Manual Trigger
    ↓
🤖 devops-engineer agent 執行
    ↓
1. Pre-Deployment Checks
   ✓ All tests passed
   ✓ No critical issues
   ✓ Database backup completed
   ✓ Staging deployment successful
   ✓ User approval obtained
    ↓
2. Backup
   → Database backup
   → Storage backup
   → Upload to S3
    ↓
3. Blue-Green Deployment
   → Pull new images
   → Start Green environment
   → Run migrations (if any)
   → Health check Green
   → Switch traffic to Green
   → Shutdown Blue
    ↓
4. Post-Deployment Validation
   → Health check endpoints
   → Run smoke tests
   → Verify metrics
    ↓
5. Monitor
   → Check error rates
   → Check response times
   → Check resource usage
    ↓
6. Notify
   → Slack success message
   → Update deployment log
```

---

## 部署前檢查清單

### 必須檢查項目

#### Code Quality
- [ ] PHPStan Level 9 通過
- [ ] Laravel Pint 檢查通過
- [ ] TypeScript 編譯無錯誤
- [ ] ESLint 0 errors

#### Tests
- [ ] Backend 測試 100% 通過 (201/201)
- [ ] 測試覆蓋率 >= 80%
- [ ] Frontend 測試通過
- [ ] E2E 測試通過

#### Security
- [ ] 無 high/critical 漏洞
- [ ] Docker 映像掃描通過
- [ ] Secrets 已更新
- [ ] SSL 證書有效

#### Infrastructure
- [ ] 資料庫備份完成
- [ ] 磁碟空間充足 (> 20%)
- [ ] 記憶體使用正常 (< 80%)
- [ ] CPU 使用正常 (< 70%)

#### Monitoring
- [ ] 監控系統運作正常
- [ ] 告警規則已設置
- [ ] 日誌聚合正常
- [ ] 儀表板可訪問

---

## 部署策略

### 1. Zero-Downtime Deployment

**原理**: 在不停止服務的情況下更新應用

```bash
# 1. 啟動新版本容器（8081 port）
docker-compose -f docker-compose.new.yml up -d

# 2. 等待新容器就緒
sleep 30

# 3. 健康檢查
curl -f http://localhost:8081/api/health

# 4. Nginx 切換 upstream
# 從 localhost:8080 → localhost:8081

# 5. 關閉舊版本
docker-compose -f docker-compose.old.yml down
```

### 2. Blue-Green Deployment

**原理**: 維護兩個完全相同的生產環境

```
Blue Environment (當前生產)
  ↓
部署 Green Environment (新版本)
  ↓
測試 Green Environment
  ↓
切換流量: Blue → Green
  ↓
監控 Green Environment
  ↓
確認無問題後關閉 Blue
```

**優點**:
- ✅ 零停機時間
- ✅ 快速回滾（切回 Blue）
- ✅ 完整測試新版本

**缺點**:
- ❌ 需要雙倍資源
- ❌ 資料庫同步複雜

### 3. Canary Deployment

**原理**: 逐步將流量導向新版本

```
1. 5% 流量 → 新版本
   ↓
   監控 1 小時
   ↓
2. 25% 流量 → 新版本
   ↓
   監控 2 小時
   ↓
3. 50% 流量 → 新版本
   ↓
   監控 4 小時
   ↓
4. 100% 流量 → 新版本
```

**適用場景**:
- 高流量應用
- 風險較高的更新
- 需要驗證效能影響

---

## 回滾策略

### 自動回滾觸發條件

- ❌ 健康檢查失敗
- ❌ 錯誤率 > 5%
- ❌ 回應時間 > 3 秒
- ❌ CPU/Memory 使用異常

### 手動回滾

```bash
# 方式 1: 使用 command
/rollback production

# 方式 2: Blue-Green 切換回舊版本
ssh production-server
cd /var/www/my-profile
docker-compose -f docker-compose.blue.yml up -d
# 切換 Nginx upstream 回 Blue
```

**回滾流程**:

```
1. 停止新版本服務
    ↓
2. 啟動舊版本服務
    ↓
3. 還原資料庫（如有 migration）
    ↓
4. 切換流量
    ↓
5. 驗證服務正常
    ↓
6. 通知團隊
```

---

## 監控指標

### 應用程式指標

- **可用性**: Uptime > 99.9%
- **回應時間**: P95 < 500ms, P99 < 1s
- **錯誤率**: < 0.1%
- **吞吐量**: Requests/second

### 基礎設施指標

- **CPU**: < 70%
- **Memory**: < 80%
- **Disk**: > 20% free
- **Network**: < 80% bandwidth

### 業務指標

- **用戶註冊**: 每日新用戶數
- **活躍用戶**: DAU/MAU
- **API 呼叫**: 每分鐘請求數
- **業務轉換**: 轉換率

---

## 告警設置

### Critical (立即處理)

```yaml
alerts:
  - name: ServiceDown
    condition: up == 0
    duration: 1m
    action: PagerDuty + Slack + SMS

  - name: HighErrorRate
    condition: error_rate > 5%
    duration: 5m
    action: PagerDuty + Slack

  - name: DatabaseDown
    condition: mysql_up == 0
    duration: 1m
    action: PagerDuty + Slack + SMS
```

### Warning (需要關注)

```yaml
  - name: HighMemoryUsage
    condition: memory_usage > 85%
    duration: 10m
    action: Slack

  - name: SlowResponse
    condition: p95_latency > 1s
    duration: 5m
    action: Slack

  - name: DiskSpaceLow
    condition: disk_free < 15%
    duration: 30m
    action: Slack
```

---

## 部署後驗證

### 1. 健康檢查

```bash
# API Health Check
curl -f https://example.com/api/health

# 預期回應
{
  "status": "healthy",
  "timestamp": "2026-01-11T14:30:22Z",
  "services": {
    "database": "up",
    "redis": "up",
    "queue": "up"
  }
}
```

### 2. Smoke Tests

```bash
# 執行關鍵功能測試
cd frontend
npx playwright test tests/smoke/

# 測試項目：
# - 首頁載入
# - 使用者登入
# - API 端點可訪問
# - 資料庫讀寫
```

### 3. 監控驗證

```bash
# 檢查 Prometheus 指標
curl http://localhost:9090/api/v1/query?query=up

# 檢查 Grafana 儀表板
open http://grafana.example.com/d/app-dashboard
```

---

## 緊急處理流程

### 發現生產問題

```
1. 評估影響範圍
   → 影響所有用戶 or 部分用戶？
   → 資料是否損壞？
   → 安全性問題？

2. 決策
   → 立即回滾 (Critical)
   → 緊急修復 (High)
   → 排程修復 (Medium/Low)

3. 執行
   → 回滾：5 分鐘內完成
   → 緊急修復：建立 hotfix 分支
   → 排程修復：建立 issue

4. 通知
   → 內部團隊
   → 受影響用戶（如需要）

5. 事後檢討
   → Root Cause Analysis
   → 改進措施
   → 更新文檔
```

---

## 使用範例

### 範例 1: 部署新功能到 Staging

```bash
# PR 合併到 develop 後自動觸發
# 無需手動操作

# 檢查部署狀態
gh run list --workflow="CI/CD Pipeline"

# 查看部署日誌
gh run view <run-id> --log
```

### 範例 2: 部署到 Production

```bash
# 1. 確認 staging 測試通過
/test staging

# 2. 執行部署
/deploy production

# devops-engineer agent 會：
# - 檢查所有前置條件
# - 備份資料庫
# - 執行 Blue-Green 部署
# - 驗證服務健康
# - 監控關鍵指標
# - 發送通知

# 3. 監控儀表板
open https://grafana.example.com
```

### 範例 3: 緊急回滾

```bash
# 發現問題，立即回滾
/rollback production

# devops-engineer agent 會：
# - 切換到舊版本
# - 還原資料庫（如需要）
# - 驗證服務正常
# - 發送告警通知
```

---

## 部署日誌

devops-engineer agent 會自動記錄部署歷史：

```markdown
# deployments/history.md

## 2026-01-11 14:30 - Production Deployment

**Version**: v1.2.0
**Commit**: abc123def
**Deployed By**: DevOps Agent
**Status**: ✅ Success

### Changes
- Added rating API endpoint
- Fixed authentication bug
- Updated frontend UI

### Deployment Details
- Start Time: 14:30:00
- End Time: 14:35:22
- Duration: 5m 22s
- Strategy: Blue-Green
- Downtime: 0s

### Pre-Deployment Checks
- ✅ All tests passed (201/201)
- ✅ Coverage: 82%
- ✅ Security scan: No critical issues
- ✅ Database backup: Completed

### Post-Deployment Validation
- ✅ Health check: Passed
- ✅ Smoke tests: 15/15 passed
- ✅ Error rate: 0.02%
- ✅ P95 latency: 245ms

### Monitoring
- CPU: 45%
- Memory: 62%
- Error Rate: 0.02%
- Response Time (P95): 245ms
```

---

## 相關命令

- `/test` - 執行測試
- `/feature-finish` - 完成功能開發（包含部署檢查）
- `/rollback` - 回滾部署
- `/setup-cicd` - 設置 CI/CD pipeline
- `/setup-monitoring` - 設置監控系統
