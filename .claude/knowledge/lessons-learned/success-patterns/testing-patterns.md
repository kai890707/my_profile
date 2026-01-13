---
category: lessons-learned
tags: [testing, pest, playwright, best-practices]
priority: medium
last_updated: 2026-01-14
applies_to: Pest 3.x, Playwright
related_docs: [../../backend/testing.md, ../../frontend/testing.md]
---

# 測試成功模式

## Quick Reference

記錄實踐驗證有效的測試策略和模式。

---

## SP-TEST-001: AAA 模式 (Arrange-Act-Assert)

### 實作範例
```php
// Pest Test
it('creates a salesperson', function () {
    // Arrange - 準備測試資料
    $user = User::factory()->create();
    $company = Company::factory()->create();
    $data = [
        'user_id' => $user->id,
        'company_id' => $company->id,
        'position' => 'Senior Sales',
    ];

    // Act - 執行操作
    $response = $this->postJson('/api/v1/salespersons', $data);

    // Assert - 驗證結果
    $response->assertCreated();
    $response->assertJsonStructure(['data' => ['id', 'position']]);
    $this->assertDatabaseHas('salespersons', [
        'user_id' => $user->id,
        'position' => 'Senior Sales',
    ]);
});
```

---

## SP-TEST-002: 測試資料工廠模式

### 實作範例
```php
// database/factories/SalespersonFactory.php
class SalespersonFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'company_id' => Company::factory(),
            'position' => fake()->jobTitle(),
            'rating' => fake()->numberBetween(0, 5),
        ];
    }

    public function withHighRating(): static
    {
        return $this->state(fn (array $attributes) => [
            'rating' => fake()->numberBetween(4, 5),
        ]);
    }
}

// 使用
$topSalesperson = Salesperson::factory()->withHighRating()->create();
```

---

## SP-TEST-003: Page Object 模式 (E2E)

### 實作範例
```typescript
// e2e/pages/SalespersonListPage.ts
export class SalespersonListPage {
  constructor(private page: Page) {}

  async goto() {
    await this.page.goto('/salespersons');
  }

  async searchByName(name: string) {
    await this.page.fill('[data-testid="search-input"]', name);
    await this.page.click('[data-testid="search-button"]');
  }

  async getSalespersonCards() {
    return await this.page.locator('[data-testid="salesperson-card"]').all();
  }
}

// 測試
test('can search salespersons', async ({ page }) => {
  const listPage = new SalespersonListPage(page);

  await listPage.goto();
  await listPage.searchByName('John');

  const cards = await listPage.getSalespersonCards();
  expect(cards.length).toBeGreaterThan(0);
});
```

---

**已記錄**: 3 個測試模式

**相關**: [Backend 測試](../../backend/testing.md), [Frontend 測試](../../frontend/testing.md)
