# 設置監控系統

**任務**: 使用 DevOps Engineer Agent 設置完整的監控、日誌和告警系統

---

## 🔴 重要：使用 DevOps Engineer Agent

**所有監控設置必須使用 `devops-engineer` agent**：

```
當需要設置監控系統時，必須使用 Task tool 啟動 devops-engineer agent
```

**devops-engineer 負責**：
- ✅ 設置 Prometheus（指標收集）
- ✅ 設置 Grafana（視覺化儀表板）
- ✅ 設置 Loki（日誌聚合）
- ✅ 設置 Promtail（日誌收集）
- ✅ 配置告警規則（Critical/Warning）
- ✅ 設置通知渠道（Slack、Email、PagerDuty）
- ✅ 建立監控儀表板
- ✅ 設置應用程式指標（Laravel、Next.js）
- ✅ 設置基礎設施指標（CPU、Memory、Disk、Network）

**範例**：
```
Task tool:
- subagent_type: devops-engineer
- prompt: 設置完整的監控系統，包括 Prometheus + Grafana + Loki，配置告警規則和 Slack 通知
```

詳見：`.claude/agents/devops-engineer.md`

---

## 監控架構

```
┌─────────────────────────────────────────────────────────┐
│                    Application Layer                     │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐             │
│  │ Backend  │  │ Frontend │  │  Queue   │             │
│  │ (Laravel)│  │(Next.js) │  │ Worker   │             │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘             │
│       │             │             │                     │
│       └─────────────┴─────────────┘                     │
│                     │                                   │
│              Expose Metrics                             │
│                     │                                   │
└─────────────────────┼───────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────────┐
│                  Prometheus (收集指標)                   │
│  • HTTP Requests                                        │
│  • Database Queries                                     │
│  • Queue Jobs                                           │
│  • Cache Hits/Misses                                    │
│  • Response Times                                       │
│  • Error Rates                                          │
└─────────────────────┬───────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────────┐
│                  Grafana (視覺化)                        │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐             │
│  │Application│ │Infrastructure│ │Business│             │
│  │Dashboard │  │ Dashboard │  │Dashboard│             │
│  └──────────┘  └──────────┘  └──────────┘             │
└─────────────────────────────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────────┐
│              Alertmanager (告警管理)                     │
│  • Critical Alerts → PagerDuty + Slack + SMS           │
│  • Warning Alerts → Slack                              │
│  • Info Alerts → Email                                 │
└─────────────────────────────────────────────────────────┘

┌─────────────────────────────────────────────────────────┐
│                    Logging Layer                         │
│  ┌──────────┐  ┌──────────┐  ┌──────────┐             │
│  │   Logs   │  │   Logs   │  │   Logs   │             │
│  │ Backend  │  │ Frontend │  │  Nginx   │             │
│  └────┬─────┘  └────┬─────┘  └────┬─────┘             │
│       └─────────────┴─────────────┘                     │
│                     │                                   │
└─────────────────────┼───────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────────┐
│                 Promtail (日誌收集)                      │
└─────────────────────┬───────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────────┐
│                   Loki (日誌聚合)                        │
└─────────────────────┬───────────────────────────────────┘
                      ↓
┌─────────────────────────────────────────────────────────┐
│              Grafana (日誌查詢與視覺化)                  │
└─────────────────────────────────────────────────────────┘
```

---

## 執行流程

### Step 1: 安裝監控元件

devops-engineer agent 會建立 `docker-compose.monitoring.yml`：

```yaml
version: '3.8'

services:
  prometheus:
    image: prom/prometheus:latest
    container_name: prometheus
    restart: unless-stopped
    volumes:
      - ./docker/prometheus:/etc/prometheus
      - prometheus-data:/prometheus
    command:
      - '--config.file=/etc/prometheus/prometheus.yml'
      - '--storage.tsdb.retention.time=30d'
    ports:
      - "9090:9090"
    networks:
      - monitoring

  grafana:
    image: grafana/grafana:latest
    container_name: grafana
    restart: unless-stopped
    environment:
      - GF_SECURITY_ADMIN_USER=admin
      - GF_SECURITY_ADMIN_PASSWORD=${GRAFANA_PASSWORD}
    volumes:
      - ./docker/grafana/provisioning:/etc/grafana/provisioning
      - ./docker/grafana/dashboards:/var/lib/grafana/dashboards
      - grafana-data:/var/lib/grafana
    ports:
      - "3001:3000"
    depends_on:
      - prometheus
    networks:
      - monitoring

  loki:
    image: grafana/loki:latest
    container_name: loki
    restart: unless-stopped
    volumes:
      - ./docker/loki/loki-config.yml:/etc/loki/local-config.yaml
      - loki-data:/loki
    command: -config.file=/etc/loki/local-config.yaml
    ports:
      - "3100:3100"
    networks:
      - monitoring

  promtail:
    image: grafana/promtail:latest
    container_name: promtail
    restart: unless-stopped
    volumes:
      - ./docker/promtail/promtail-config.yml:/etc/promtail/config.yml
      - /var/log:/var/log:ro
      - ./my_profile_laravel/storage/logs:/var/www/logs:ro
    command: -config.file=/etc/promtail/config.yml
    depends_on:
      - loki
    networks:
      - monitoring

  node-exporter:
    image: prom/node-exporter:latest
    container_name: node-exporter
    restart: unless-stopped
    command:
      - '--path.procfs=/host/proc'
      - '--path.sysfs=/host/sys'
    volumes:
      - /proc:/host/proc:ro
      - /sys:/host/sys:ro
    ports:
      - "9100:9100"
    networks:
      - monitoring

  cadvisor:
    image: gcr.io/cadvisor/cadvisor:latest
    container_name: cadvisor
    restart: unless-stopped
    privileged: true
    volumes:
      - /:/rootfs:ro
      - /var/run:/var/run:ro
      - /sys:/sys:ro
      - /var/lib/docker/:/var/lib/docker:ro
    ports:
      - "8080:8080"
    networks:
      - monitoring

volumes:
  prometheus-data:
  grafana-data:
  loki-data:

networks:
  monitoring:
    driver: bridge
```

啟動監控系統：

```bash
docker-compose -f docker-compose.monitoring.yml up -d
```

### Step 2: 配置 Prometheus

**檔案**: `docker/prometheus/prometheus.yml`

```yaml
global:
  scrape_interval: 15s
  evaluation_interval: 15s
  external_labels:
    cluster: 'production'
    environment: 'prod'

alerting:
  alertmanagers:
    - static_configs:
        - targets:
            - 'alertmanager:9093'

rule_files:
  - 'alerts.yml'

scrape_configs:
  # Prometheus 自身
  - job_name: 'prometheus'
    static_configs:
      - targets: ['localhost:9090']

  # Node Exporter (系統指標)
  - job_name: 'node-exporter'
    static_configs:
      - targets: ['node-exporter:9100']

  # cAdvisor (容器指標)
  - job_name: 'cadvisor'
    static_configs:
      - targets: ['cadvisor:8080']

  # Laravel Backend
  - job_name: 'backend'
    metrics_path: '/metrics'
    static_configs:
      - targets: ['backend:9000']
    relabel_configs:
      - source_labels: [__address__]
        target_label: instance
        replacement: 'backend'

  # MySQL Exporter
  - job_name: 'mysql'
    static_configs:
      - targets: ['mysql-exporter:9104']

  # Redis Exporter
  - job_name: 'redis'
    static_configs:
      - targets: ['redis-exporter:9121']

  # Nginx Exporter
  - job_name: 'nginx'
    static_configs:
      - targets: ['nginx-exporter:9113']
```

### Step 3: 配置告警規則

**檔案**: `docker/prometheus/alerts.yml`

```yaml
groups:
  # ==================== Critical Alerts ====================
  - name: critical_alerts
    interval: 30s
    rules:
      # 應用程式下線
      - alert: ApplicationDown
        expr: up{job="backend"} == 0
        for: 1m
        labels:
          severity: critical
          team: backend
        annotations:
          summary: "應用程式 {{ $labels.instance }} 離線"
          description: "Backend 服務已離線超過 1 分鐘"
          runbook: "https://wiki.example.com/runbooks/application-down"

      # 資料庫下線
      - alert: DatabaseDown
        expr: up{job="mysql"} == 0
        for: 1m
        labels:
          severity: critical
          team: database
        annotations:
          summary: "資料庫離線"
          description: "MySQL 資料庫已離線超過 1 分鐘"

      # 高錯誤率
      - alert: HighErrorRate
        expr: rate(http_requests_total{status=~"5.."}[5m]) / rate(http_requests_total[5m]) > 0.05
        for: 5m
        labels:
          severity: critical
          team: backend
        annotations:
          summary: "高 HTTP 錯誤率"
          description: "HTTP 5xx 錯誤率超過 5% (當前: {{ $value | humanizePercentage }})"

      # 記憶體使用過高
      - alert: HighMemoryUsage
        expr: (node_memory_MemTotal_bytes - node_memory_MemAvailable_bytes) / node_memory_MemTotal_bytes > 0.9
        for: 5m
        labels:
          severity: critical
          team: infrastructure
        annotations:
          summary: "高記憶體使用率"
          description: "記憶體使用率超過 90% (當前: {{ $value | humanizePercentage }})"

  # ==================== Warning Alerts ====================
  - name: warning_alerts
    interval: 1m
    rules:
      # CPU 使用率高
      - alert: HighCPUUsage
        expr: 100 - (avg by(instance) (rate(node_cpu_seconds_total{mode="idle"}[5m])) * 100) > 80
        for: 10m
        labels:
          severity: warning
          team: infrastructure
        annotations:
          summary: "高 CPU 使用率"
          description: "CPU 使用率超過 80% (當前: {{ $value | printf \"%.2f\" }}%)"

      # 磁碟空間不足
      - alert: DiskSpaceLow
        expr: (node_filesystem_avail_bytes{mountpoint="/"} / node_filesystem_size_bytes{mountpoint="/"}) < 0.15
        for: 10m
        labels:
          severity: warning
          team: infrastructure
        annotations:
          summary: "磁碟空間不足"
          description: "磁碟空間少於 15% (當前: {{ $value | humanizePercentage }})"

      # 回應時間慢
      - alert: SlowResponseTime
        expr: histogram_quantile(0.95, rate(http_request_duration_seconds_bucket[5m])) > 1
        for: 10m
        labels:
          severity: warning
          team: backend
        annotations:
          summary: "回應時間過慢"
          description: "P95 回應時間超過 1 秒 (當前: {{ $value | printf \"%.2f\" }}s)"

      # 資料庫連線數過高
      - alert: HighDatabaseConnections
        expr: mysql_global_status_threads_connected / mysql_global_variables_max_connections > 0.8
        for: 5m
        labels:
          severity: warning
          team: database
        annotations:
          summary: "資料庫連線數過高"
          description: "資料庫連線數超過最大值的 80% (當前: {{ $value | humanizePercentage }})"

      # Queue 堆積
      - alert: QueueBacklog
        expr: queue_jobs_pending > 1000
        for: 15m
        labels:
          severity: warning
          team: backend
        annotations:
          summary: "Queue 任務堆積"
          description: "待處理 Queue 任務超過 1000 個 (當前: {{ $value }})"

  # ==================== Info Alerts ====================
  - name: info_alerts
    interval: 5m
    rules:
      # SSL 證書即將過期
      - alert: SSLCertificateExpiringSoon
        expr: (ssl_certificate_expiry_seconds - time()) / 86400 < 30
        for: 1h
        labels:
          severity: info
          team: infrastructure
        annotations:
          summary: "SSL 證書即將過期"
          description: "SSL 證書將在 {{ $value | printf \"%.0f\" }} 天後過期"

      # 備份失敗
      - alert: BackupFailed
        expr: time() - backup_last_success_timestamp > 86400 * 2
        for: 1h
        labels:
          severity: info
          team: infrastructure
        annotations:
          summary: "備份失敗"
          description: "已經超過 2 天沒有成功備份"
```

### Step 4: 配置 Grafana 儀表板

devops-engineer agent 會建立預設儀表板：

#### 1. Application Dashboard
**檔案**: `docker/grafana/dashboards/application.json`

顯示指標：
- HTTP Requests (Total, Rate, by Status Code)
- Response Time (P50, P95, P99)
- Error Rate (5xx errors)
- Database Queries (Count, Duration)
- Queue Jobs (Pending, Processing, Failed)
- Cache Performance (Hit Rate, Miss Rate)
- Active Users
- API Endpoints Performance

#### 2. Infrastructure Dashboard
**檔案**: `docker/grafana/dashboards/infrastructure.json`

顯示指標：
- CPU Usage (by core, average)
- Memory Usage (Used, Available, Swap)
- Disk Usage (by partition)
- Network Traffic (In/Out)
- Docker Containers (Running, Stopped, Resource Usage)
- Load Average (1m, 5m, 15m)

#### 3. Business Dashboard
**檔案**: `docker/grafana/dashboards/business.json`

顯示指標：
- New User Registrations (Daily, Monthly)
- Active Users (DAU, MAU)
- User Login Frequency
- API Usage by Endpoint
- Popular Features
- Conversion Rates

### Step 5: 配置日誌系統

#### Loki 配置
**檔案**: `docker/loki/loki-config.yml`

```yaml
auth_enabled: false

server:
  http_listen_port: 3100

ingester:
  lifecycler:
    ring:
      kvstore:
        store: inmemory
      replication_factor: 1
  chunk_idle_period: 5m
  chunk_retain_period: 30s

schema_config:
  configs:
    - from: 2020-05-15
      store: boltdb
      object_store: filesystem
      schema: v11
      index:
        prefix: index_
        period: 168h

storage_config:
  boltdb:
    directory: /loki/index
  filesystem:
    directory: /loki/chunks

limits_config:
  enforce_metric_name: false
  reject_old_samples: true
  reject_old_samples_max_age: 168h

chunk_store_config:
  max_look_back_period: 0s

table_manager:
  retention_deletes_enabled: true
  retention_period: 720h  # 30 days
```

#### Promtail 配置
**檔案**: `docker/promtail/promtail-config.yml`

```yaml
server:
  http_listen_port: 9080
  grpc_listen_port: 0

positions:
  filename: /tmp/positions.yaml

clients:
  - url: http://loki:3100/loki/api/v1/push

scrape_configs:
  # Laravel Logs
  - job_name: laravel
    static_configs:
      - targets:
          - localhost
        labels:
          job: laravel
          app: backend
          __path__: /var/www/logs/*.log
    pipeline_stages:
      - json:
          expressions:
            timestamp: timestamp
            level: level
            message: message
            context: context
      - labels:
          level:
      - timestamp:
          source: timestamp
          format: RFC3339

  # Nginx Access Logs
  - job_name: nginx-access
    static_configs:
      - targets:
          - localhost
        labels:
          job: nginx
          log_type: access
          __path__: /var/log/nginx/access.log
    pipeline_stages:
      - regex:
          expression: '^(?P<remote_addr>\S+) - (?P<remote_user>\S+) \[(?P<time_local>[^\]]+)\] "(?P<method>\S+) (?P<request>\S+) (?P<protocol>\S+)" (?P<status>\d+) (?P<body_bytes_sent>\d+)'
      - labels:
          method:
          status:

  # Nginx Error Logs
  - job_name: nginx-error
    static_configs:
      - targets:
          - localhost
        labels:
          job: nginx
          log_type: error
          __path__: /var/log/nginx/error.log

  # System Logs
  - job_name: syslog
    static_configs:
      - targets:
          - localhost
        labels:
          job: syslog
          __path__: /var/log/syslog
```

### Step 6: 設置應用程式指標

#### Laravel Prometheus Exporter

```bash
# 安裝
composer require ensi/laravel-prometheus
```

**配置**: `config/prometheus.php`

```php
<?php

return [
    'namespace' => env('PROMETHEUS_NAMESPACE', 'app'),

    'metrics_route_enabled' => env('PROMETHEUS_METRICS_ROUTE_ENABLED', true),
    'metrics_route_path' => env('PROMETHEUS_METRICS_ROUTE_PATH', '/metrics'),
    'metrics_route_middleware' => explode(',', env('PROMETHEUS_METRICS_ROUTE_MIDDLEWARE', '')),

    'storage_adapter' => env('PROMETHEUS_STORAGE_ADAPTER', 'memory'),

    'redis_host' => env('REDIS_HOST', '127.0.0.1'),
    'redis_port' => env('REDIS_PORT', 6379),
    'redis_password' => env('REDIS_PASSWORD', null),
    'redis_database' => env('REDIS_DATABASE', 0),

    'collectors' => [
        \Ensi\LaravelPrometheus\MetricCollectors\HttpRequestCollector::class,
        \Ensi\LaravelPrometheus\MetricCollectors\DatabaseQueryCollector::class,
        \Ensi\LaravelPrometheus\MetricCollectors\QueueJobCollector::class,
        \Ensi\LaravelPrometheus\MetricCollectors\CacheCollector::class,
    ],
];
```

**Middleware**: `app/Http/Kernel.php`

```php
protected $middleware = [
    // ...
    \Ensi\LaravelPrometheus\Http\Middleware\CollectMetrics::class,
];
```

### Step 7: 配置通知渠道

#### Slack 通知

```yaml
# docker/alertmanager/alertmanager.yml
global:
  slack_api_url: 'YOUR_SLACK_WEBHOOK_URL'

route:
  group_by: ['alertname', 'cluster', 'service']
  group_wait: 10s
  group_interval: 10s
  repeat_interval: 12h
  receiver: 'slack-critical'
  routes:
    - match:
        severity: critical
      receiver: 'slack-critical'
      continue: true
    - match:
        severity: warning
      receiver: 'slack-warning'
    - match:
        severity: info
      receiver: 'email'

receivers:
  - name: 'slack-critical'
    slack_configs:
      - channel: '#alerts-critical'
        title: '🚨 Critical Alert'
        text: |
          *Alert:* {{ .GroupLabels.alertname }}
          *Severity:* {{ .CommonLabels.severity }}
          *Summary:* {{ .CommonAnnotations.summary }}
          *Description:* {{ .CommonAnnotations.description }}
        send_resolved: true

  - name: 'slack-warning'
    slack_configs:
      - channel: '#alerts-warning'
        title: '⚠️ Warning Alert'
        text: |
          *Alert:* {{ .GroupLabels.alertname }}
          *Description:* {{ .CommonAnnotations.description }}
        send_resolved: true

  - name: 'email'
    email_configs:
      - to: 'team@example.com'
        from: 'alerts@example.com'
        smarthost: 'smtp.example.com:587'
        auth_username: 'alerts@example.com'
        auth_password: 'password'
```

### Step 8: 驗證監控系統

```bash
# 1. 檢查服務狀態
docker-compose -f docker-compose.monitoring.yml ps

# 預期輸出：所有服務都是 Up 狀態
# prometheus    Up    9090/tcp
# grafana       Up    3000/tcp
# loki          Up    3100/tcp
# promtail      Up
# node-exporter Up    9100/tcp
# cadvisor      Up    8080/tcp

# 2. 測試 Prometheus
curl http://localhost:9090/api/v1/targets
# 應該看到所有 targets 都是 UP 狀態

# 3. 訪問 Grafana
open http://localhost:3001
# 登入: admin / <GRAFANA_PASSWORD>
# 檢查 dashboards 是否正常顯示

# 4. 測試告警
# 手動觸發一個告警（例如停止 backend 服務）
docker-compose stop backend
# 等待 1-2 分鐘，檢查是否收到 Slack 通知

# 5. 測試日誌查詢
# 在 Grafana Explore 中查詢日誌
# LogQL: {job="laravel"} |= "error"
```

---

## 監控指標說明

### 應用程式指標

```prometheus
# HTTP Requests
http_requests_total{method="GET", route="/api/users", status="200"}
http_request_duration_seconds{method="GET", route="/api/users"}

# Database
database_query_duration_seconds{query_type="select"}
database_connections_active
database_connections_idle

# Queue
queue_jobs_pending{queue="default"}
queue_jobs_processing{queue="default"}
queue_jobs_failed{queue="default"}

# Cache
cache_hits_total
cache_misses_total
cache_get_duration_seconds
```

### 基礎設施指標

```prometheus
# CPU
node_cpu_seconds_total{mode="idle"}
node_load1  # 1 minute load average

# Memory
node_memory_MemTotal_bytes
node_memory_MemAvailable_bytes
node_memory_SwapTotal_bytes

# Disk
node_filesystem_avail_bytes{mountpoint="/"}
node_filesystem_size_bytes{mountpoint="/"}
node_disk_io_time_seconds_total

# Network
node_network_receive_bytes_total{device="eth0"}
node_network_transmit_bytes_total{device="eth0"}
```

---

## 日誌查詢範例

### LogQL 查詢

```logql
# 查詢所有錯誤日誌
{job="laravel"} |= "error"

# 查詢特定時間範圍的 500 錯誤
{job="nginx", log_type="access"} |= "500" | json

# 查詢慢查詢（超過 1 秒）
{job="laravel"} | json | duration > 1s

# 統計每分鐘錯誤數
rate({job="laravel"} |= "error" [1m])

# 查詢特定 user 的操作
{job="laravel"} | json | user_id="123"
```

---

## 維護與優化

### 定期維護任務

```bash
# 每週任務
- 檢查磁碟使用率（Prometheus、Loki 資料）
- 檢查告警準確性
- Review false positives

# 每月任務
- 更新 Grafana dashboards
- 優化告警閾值
- 清理舊資料
- 效能優化

# 每季任務
- 檢討監控策略
- 更新文檔
- 團隊培訓
```

### 資料保留策略

```yaml
# Prometheus
--storage.tsdb.retention.time=30d

# Loki
retention_period: 720h  # 30 days

# 長期儲存（可選）
# 使用 Thanos 或 Cortex 進行長期儲存
```

---

## 檢查清單

### 設置完成檢查

- [ ] Prometheus 運行正常
- [ ] Grafana 可訪問
- [ ] Loki + Promtail 收集日誌
- [ ] 所有 targets 都是 UP 狀態
- [ ] Dashboards 顯示正常
- [ ] 告警規則已配置
- [ ] Slack 通知測試成功
- [ ] 應用程式指標正常收集
- [ ] 日誌查詢正常
- [ ] 備份策略已設置

### 日常檢查

- [ ] 檢查 Prometheus targets
- [ ] 檢查 Grafana dashboards
- [ ] Review active alerts
- [ ] 檢查日誌收集狀態
- [ ] 檢查磁碟使用率

---

## 輸出範例

```
🎉 Monitoring System Setup Completed!

✅ Components Installed:
- Prometheus (http://localhost:9090)
- Grafana (http://localhost:3001)
- Loki (http://localhost:3100)
- Promtail (collecting logs)
- Node Exporter (system metrics)
- cAdvisor (container metrics)

✅ Dashboards Created:
- Application Dashboard (ID: 1)
- Infrastructure Dashboard (ID: 2)
- Business Metrics Dashboard (ID: 3)

✅ Alert Rules Configured:
- Critical: 5 rules
- Warning: 6 rules
- Info: 2 rules

✅ Notification Channels:
- Slack (#alerts-critical)
- Slack (#alerts-warning)
- Email (team@example.com)

📊 Metrics Being Collected:
- HTTP Requests: ✓
- Database Queries: ✓
- Queue Jobs: ✓
- Cache Performance: ✓
- System Resources: ✓
- Container Metrics: ✓

📋 Next Steps:
1. 登入 Grafana: http://localhost:3001
   Username: admin
   Password: <GRAFANA_PASSWORD>

2. 檢查 Dashboards 是否正常

3. 測試告警:
   docker-compose stop backend
   (應該在 1-2 分鐘內收到 Slack 通知)

4. 設置儀表板自動刷新

5. 建立自訂儀表板（根據業務需求）

📖 Documentation:
- Prometheus: https://prometheus.io/docs/
- Grafana: https://grafana.com/docs/
- Loki: https://grafana.com/docs/loki/
- Alert Rules: docker/prometheus/alerts.yml
```

---

**相關命令**:
- `/setup-cicd` - 設置 CI/CD pipeline
- `/deploy` - 部署到生產環境
- `/test` - 執行全面測試
