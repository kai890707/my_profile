---
category: frontend
tags: [testing, vitest, playwright, react-testing-library]
priority: medium
last_updated: 2026-01-13
applies_to: Vitest, Playwright, React Testing Library
related_docs: [architecture.md, ../workflow/sdd-process.md]
---

# 測試策略

## Quick Reference

- 單元測試: Vitest + React Testing Library
- E2E 測試: Playwright
- 覆蓋率目標: >= 80%
- 測試類型: Component Tests, E2E Tests

## 使用場景

**適用於**:
- 組件測試
- 整合測試
- E2E 測試

## 核心概念

前端測試確保 UI 按預期運作，提升用戶體驗。

## Component Test 範例

```tsx
// components/features/salesperson/SalespersonCard.test.tsx
import { render, screen } from '@testing-library/react';
import { SalespersonCard } from './SalespersonCard';

describe('SalespersonCard', () => {
  it('renders salesperson information', () => {
    const salesperson = {
      id: 1,
      user: { name: 'John Doe' },
      position: 'Senior Sales',
      company: { name: 'ABC Corp' },
    };
    
    render(<SalespersonCard salesperson={salesperson} />);
    
    expect(screen.getByText('John Doe')).toBeInTheDocument();
    expect(screen.getByText('Senior Sales')).toBeInTheDocument();
  });
});
```

## E2E Test 範例

```typescript
// e2e/salesperson.spec.ts
import { test, expect } from '@playwright/test';

test('user can view salesperson list', async ({ page }) => {
  await page.goto('/salespersons');
  
  await expect(page.getByRole('heading', { name: '業務員列表' })).toBeVisible();
  
  const cards = page.locator('[data-testid="salesperson-card"]');
  await expect(cards).toHaveCount(10);
});
```

## 執行測試

```bash
# 單元測試
npm test

# E2E 測試
npx playwright test

# 測試覆蓋率
npm run test:coverage
```

## 最佳實踐

- [ ] 組件有測試
- [ ] 關鍵流程有 E2E 測試
- [ ] 使用 data-testid 定位元素
- [ ] Mock API 呼叫
- [ ] 測試用戶互動

## 相關知識

- [SDD 流程](../workflow/sdd-process.md) - 測試在開發流程中的角色
- [架構模式](./architecture.md) - 如何測試組件

---

**維護者**: Development Team
**最後更新**: 2026-01-13
**版本**: 1.0
