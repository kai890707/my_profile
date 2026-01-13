---
category: workflow
tags: [lessons-learned, best-practices, troubleshooting]
priority: medium
last_updated: 2026-01-14
applies_to: All Development
related_docs: [../workflow/sdd-process.md, ../backend/architecture.md, ../frontend/architecture.md]
---

# 經驗積累庫 (Lessons Learned)

## Quick Reference

這是一個持續累積的知識庫，記錄開發過程中的經驗教訓、成功模式和效能技巧。

**目的**:
- 避免重複犯錯
- 分享成功經驗
- 加速問題解決
- 提升開發效率

**更新頻率**: 每次遇到重要經驗時立即記錄

---

## 使用場景

### 適用於

**開發前**:
- 檢查類似功能的常見錯誤
- 參考成功模式
- 了解效能最佳實踐

**開發中**:
- 遇到問題時查找解決方案
- 參考效能優化技巧
- 避免已知陷阱

**開發後**:
- 記錄新發現的問題
- 記錄成功的解決方案
- 更新效能數據

### 不適用於

- 基礎語法教學（應查閱官方文檔）
- 框架使用指南（應查閱 architecture.md）
- 一次性的特殊情況

---

## 目錄結構

```
lessons-learned/
├── README.md                          # 本文件 - 索引與指南
├── common-mistakes/                   # 常見錯誤
│   ├── backend-mistakes.md            # Backend 常見錯誤
│   ├── frontend-mistakes.md           # Frontend 常見錯誤
│   ├── database-mistakes.md           # 資料庫常見錯誤
│   └── api-integration-mistakes.md    # API 整合常見錯誤
├── success-patterns/                  # 成功模式
│   ├── backend-patterns.md            # Backend 成功模式
│   ├── frontend-patterns.md           # Frontend 成功模式
│   ├── testing-patterns.md            # 測試成功模式
│   └── deployment-patterns.md         # 部署成功模式
└── performance-tips/                  # 效能技巧
    ├── backend-performance.md         # Backend 效能優化
    ├── frontend-performance.md        # Frontend 效能優化
    ├── database-performance.md        # 資料庫效能優化
    └── caching-strategies.md          # 快取策略
```

---

## 核心概念

### 經驗記錄原則

**STAR 格式**:
- **S**ituation (情境): 發生什麼問題或情況
- **T**ask (任務): 需要完成什麼
- **A**ction (行動): 採取了什麼措施
- **R**esult (結果): 最終結果如何

**記錄標準**:
- 具體而非抽象（要有代碼範例）
- 可複製而非理論（要能直接應用）
- 有數據支持（效能數字、錯誤率）
- 包含原因分析（為什麼會發生）

---

## 常見錯誤 (Common Mistakes)

記錄開發中常見的錯誤、陷阱和反模式。

**內容包含**:
- 錯誤描述（What went wrong）
- 錯誤代碼範例（Bad example）
- 正確做法（Good example）
- 為什麼會錯（Root cause）
- 如何避免（Prevention）
- 影響範圍（Impact）

**範例格式**:
```markdown
## CM-BE-001: N+1 查詢問題

### 情境
在業務員列表頁載入時，API 回應時間 > 3 秒。

### 錯誤代碼
\```php
$salespersons = Salesperson::all();
foreach ($salespersons as $salesperson) {
    echo $salesperson->user->name;  // N+1 查詢
}
\```

### 問題分析
- 執行了 1 + N 次資料庫查詢
- 100 筆資料 = 101 次查詢
- 每次查詢 30ms，總共 3 秒

### 正確做法
\```php
$salespersons = Salesperson::with('user')->get();
foreach ($salespersons as $salesperson) {
    echo $salesperson->user->name;  // 只有 2 次查詢
}
\```

### 結果
- 查詢數: 101 → 2
- 回應時間: 3000ms → 60ms
- 效能提升: 50x

### 預防措施
- 使用 Laravel Debugbar 檢查查詢數
- Code Review 檢查 Eager Loading
- 測試時監控 N+1 問題
```

**檔案列表**:
- `backend-mistakes.md`: Laravel、PHP、Service Layer 錯誤
- `frontend-mistakes.md`: React、Next.js、TypeScript 錯誤
- `database-mistakes.md`: MySQL、Migration、索引錯誤
- `api-integration-mistakes.md`: API 設計、整合錯誤

---

## 成功模式 (Success Patterns)

記錄實踐驗證有效的設計模式、解決方案和最佳實踐。

**內容包含**:
- 模式名稱和目的
- 適用場景
- 實作步驟
- 代碼範例
- 優缺點分析
- 實際效果數據

**範例格式**:
```markdown
## SP-BE-001: Service Layer 模式

### 目的
將業務邏輯從 Controller 中分離，提升可測試性和可維護性。

### 適用場景
- 業務邏輯複雜（超過 20 行）
- 需要跨 Controller 複用
- 涉及多個 Model 操作
- 需要交易處理

### 實作步驟

1. **建立 Service 類別**
\```php
// app/Services/SalespersonService.php
class SalespersonService
{
    public function createSalesperson(array $data): Salesperson
    {
        return DB::transaction(function () use ($data) {
            $salesperson = Salesperson::create($data);
            $this->sendWelcomeEmail($salesperson);
            return $salesperson->load('user', 'company');
        });
    }
}
\```

2. **Controller 使用 Service**
\```php
class SalespersonController extends Controller
{
    public function __construct(
        private readonly SalespersonService $service
    ) {}

    public function store(StoreSalespersonRequest $request): JsonResponse
    {
        $salesperson = $this->service->createSalesperson(
            $request->validated()
        );

        return response()->json([
            'data' => new SalespersonResource($salesperson),
        ], 201);
    }
}
\```

### 優點
- Controller 保持簡潔（< 10 行）
- 業務邏輯可獨立測試
- 容易複用
- 交易管理集中

### 缺點
- 增加一層抽象
- 小型專案可能過度設計

### 實際效果
- Controller 平均行數: 45 → 12
- 測試覆蓋率: 65% → 95%
- 代碼重複率: 25% → 5%

### 團隊採用率
- 自 2025-12 採用
- 100% 新功能使用此模式
- 重構了 80% 舊代碼
```

**檔案列表**:
- `backend-patterns.md`: Laravel 架構模式
- `frontend-patterns.md`: React 組件模式
- `testing-patterns.md`: 測試策略模式
- `deployment-patterns.md`: 部署流程模式

---

## 效能技巧 (Performance Tips)

記錄實際驗證有效的效能優化技巧和數據。

**內容包含**:
- 優化前後對比
- 具體數據（回應時間、記憶體、CPU）
- 實作步驟
- 適用場景
- 注意事項

**範例格式**:
```markdown
## PT-BE-001: 資料庫查詢結果快取

### 問題
首頁 API 載入時間 500ms，但資料每 5 分鐘才更新一次。

### 解決方案
使用 Redis 快取查詢結果 5 分鐘。

### 實作

\```php
// Before
public function index(): JsonResponse
{
    $salespersons = Salesperson::with('user', 'company')
        ->orderBy('rating', 'desc')
        ->limit(10)
        ->get();

    return response()->json(['data' => $salespersons]);
}

// After
public function index(): JsonResponse
{
    $salespersons = Cache::remember(
        'top_salespersons',
        now()->addMinutes(5),
        fn () => Salesperson::with('user', 'company')
            ->orderBy('rating', 'desc')
            ->limit(10)
            ->get()
    );

    return response()->json(['data' => $salespersons]);
}
\```

### 效能數據

| 指標 | Before | After | 改善 |
|------|--------|-------|------|
| 回應時間 (P50) | 500ms | 15ms | 33x |
| 回應時間 (P95) | 800ms | 20ms | 40x |
| QPS | 20 | 500 | 25x |
| 資料庫查詢 | 每次 3 個 | 每 5 分鐘 3 個 | 100x |
| CPU 使用率 | 45% | 8% | 5.6x |

### 適用場景
- 查詢結果變化不頻繁
- 讀取頻率 >> 寫入頻率
- 查詢成本高（複雜 JOIN、聚合）
- 資料可容忍短暫不一致

### 注意事項
- 快取失效策略（時間 vs 事件）
- 記憶體使用量監控
- 快取穿透問題處理
- 快取預熱策略
```

**檔案列表**:
- `backend-performance.md`: API、資料庫、快取優化
- `frontend-performance.md`: 組件、Bundle、渲染優化
- `database-performance.md`: 索引、查詢、架構優化
- `caching-strategies.md`: 多層快取策略

---

## 使用方式

### 對 AI 開發助手

**開發前查閱**:
```bash
# 檢查常見錯誤
grep -r "N+1" .claude/knowledge/lessons-learned/common-mistakes/

# 參考成功模式
cat .claude/knowledge/lessons-learned/success-patterns/backend-patterns.md

# 查看效能技巧
cat .claude/knowledge/lessons-learned/performance-tips/backend-performance.md
```

**開發中記錄**:
1. 遇到問題並解決後立即記錄
2. 使用 STAR 格式
3. 包含代碼範例和數據
4. 更新相關檔案

**標準流程**:
```markdown
1. 遇到問題 → 查閱 common-mistakes/ 是否有記錄
2. 嘗試解決 → 參考 success-patterns/ 尋找模式
3. 效能優化 → 查閱 performance-tips/ 找技巧
4. 解決完成 → 記錄新經驗到對應檔案
```

### 對人類開發者

**快速查找**:
- 使用檔案編號（CM-BE-001、SP-FE-002、PT-DB-003）
- 使用標籤搜尋（#n+1, #cache, #performance）
- 按類別瀏覽（Backend/Frontend/Database）

**貢獻新經驗**:
1. 選擇適當類別（常見錯誤/成功模式/效能技巧）
2. 使用標準格式撰寫
3. 包含完整代碼範例和數據
4. 提交 PR 並經過 Review

---

## 檔案編號規範

**格式**: `{類別}-{領域}-{序號}`

**類別代碼**:
- `CM`: Common Mistakes (常見錯誤)
- `SP`: Success Patterns (成功模式)
- `PT`: Performance Tips (效能技巧)

**領域代碼**:
- `BE`: Backend
- `FE`: Frontend
- `DB`: Database
- `API`: API Integration
- `TEST`: Testing
- `DEPLOY`: Deployment

**範例**:
- `CM-BE-001`: Backend 常見錯誤第 1 項
- `SP-FE-002`: Frontend 成功模式第 2 項
- `PT-DB-003`: Database 效能技巧第 3 項

---

## 品質標準

### 必須包含

**常見錯誤**:
- [ ] 錯誤代碼範例
- [ ] 正確代碼範例
- [ ] 問題原因分析
- [ ] 預防措施
- [ ] 影響範圍

**成功模式**:
- [ ] 適用場景
- [ ] 實作步驟
- [ ] 代碼範例
- [ ] 優缺點分析
- [ ] 實際效果數據

**效能技巧**:
- [ ] 優化前後代碼
- [ ] 具體效能數據
- [ ] 適用場景
- [ ] 注意事項

### 文件風格

- 具體：有代碼範例，不空談理論
- 可測量：有數字、百分比、時間
- 可複製：讀者可直接應用
- 有因果：解釋為什麼，不只是怎麼做
- 專業：無 emoji，簡潔專業

---

## 維護策略

### 更新頻率

**立即更新**:
- 發現新的嚴重錯誤
- 驗證有效的重大優化
- 新的成功模式實踐

**定期更新**:
- 每月回顧並更新數據
- 每季度整理和歸類
- 每年度移除過時內容

### 內容審查

**審查標準**:
- 代碼是否仍然有效
- 數據是否需要更新
- 是否有更好的解決方案
- 是否與新版本框架衝突

**移除標準**:
- 技術已過時（如 PHP 7 內容）
- 問題已在框架層級解決
- 不再適用於當前專案

---

## 統計數據

**當前狀態**（2026-01-14）:
- 常見錯誤: 4 個類別
- 成功模式: 4 個類別
- 效能技巧: 4 個類別
- 總經驗項目: 待積累

**目標**:
- 每月新增: 5-10 項經驗
- 一年內累積: 60-120 項經驗
- 覆蓋率: 80% 常見問題有記錄

---

## 相關知識

- [SDD 流程](../workflow/sdd-process.md) - 何時記錄經驗
- [Backend 架構](../backend/architecture.md) - Backend 技術背景
- [Frontend 架構](../frontend/architecture.md) - Frontend 技術背景
- [量化指標](../workflow/metrics-standards.md) - 效能數據標準

---

**維護者**: Development Team
**最後更新**: 2026-01-14
**版本**: 1.0
