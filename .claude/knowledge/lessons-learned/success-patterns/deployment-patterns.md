---
category: lessons-learned
tags: [deployment, docker, ci-cd, devops]
priority: medium
last_updated: 2026-01-14
applies_to: Docker, GitHub Actions
related_docs: [../../workflow/deployment.md]
---

# 部署成功模式

## Quick Reference

記錄實踐驗證有效的部署策略和模式。

---

## SP-DEPLOY-001: 零停機部署

### 策略
使用滾動更新 (Rolling Update) 確保服務不中斷。

### 實作
```yaml
# docker-compose.yml
services:
  app:
    image: yamu-backend:latest
    deploy:
      replicas: 3
      update_config:
        parallelism: 1
        delay: 10s
        order: start-first  # 先啟動新容器，再停止舊容器
```

### 優點
- 無停機時間
- 自動回滾失敗的部署
- 減少風險

---

## SP-DEPLOY-002: 健康檢查

### 實作
```php
// routes/api.php
Route::get('/health', function () {
    $healthy = DB::connection()->getPdo() !== null;

    return response()->json([
        'status' => $healthy ? 'ok' : 'error',
        'timestamp' => now()->toIso8601String(),
    ], $healthy ? 200 : 503);
});
```

```yaml
# docker-compose.yml
healthcheck:
  test: ["CMD", "curl", "-f", "http://localhost/api/health"]
  interval: 30s
  timeout: 10s
  retries: 3
```

---

## SP-DEPLOY-003: 環境變數管理

### 實作
```bash
# .env.example (版本控制)
APP_NAME=YAMU
APP_ENV=production
DB_CONNECTION=mysql
DB_HOST=127.0.0.1

# .env (不進版本控制，包含機密)
DB_PASSWORD=secret_password
JWT_SECRET=secret_key
```

---

**已記錄**: 3 個部署模式

**相關**: [部署流程](../../workflow/deployment.md)
