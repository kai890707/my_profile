# UI/UX 設計規格：Avatar Fallback 優化

**功能**: 修復 Avatar Fallback 的 TypeError Bug
**版本**: 1.0
**建立日期**: 2026-01-12
**設計師**: Product Designer (AI)

---

## 1. 設計目標

### 1.1 使用者體驗目標

**核心目標**：確保使用者在任何情況下都能看到合理的 Avatar 顯示，不會因為資料不完整而導致頁面崩潰。

**設計原則**：
1. **優雅降級（Graceful Degradation）**：當完整資料不可用時，自動使用備選方案
2. **一致性（Consistency）**：所有 Avatar 使用相同的 fallback 策略
3. **可預測性（Predictability）**：使用者能理解為什麼顯示特定的 fallback
4. **無縫體驗（Seamless Experience）**：fallback 切換不應該引起注意

### 1.2 商業目標

- **零錯誤率**：消除 TypeError 導致的頁面崩潰
- **用戶信心**：即使資料不完整，使用者仍能正常瀏覽
- **維護成本**：統一的 fallback 策略降低維護複雜度

---

## 2. 使用者旅程分析

### 2.1 主要使用情境

#### 情境 1：瀏覽業務員列表（搜尋頁面）

**使用者流程**：
```
1. 使用者訪問搜尋頁面 (/search)
   ↓
2. 看到業務員卡片列表
   ↓
3. 每個卡片顯示業務員的 Avatar
   ↓
4. 當 full_name 不存在時
   ├─ 目前：頁面崩潰，顯示錯誤 ❌
   └─ 改進：顯示合理的 fallback，頁面正常 ✅
```

**使用者期望**：
- 看到業務員的頭像或代表性的 fallback
- 即使資料不完整，也能繼續瀏覽
- Fallback 應該有意義（例如：顯示名字的首字母）

**痛點**：
- ❌ 頁面完全無法使用
- ❌ 看不到任何業務員資訊
- ❌ 使用者可能認為網站有問題

#### 情境 2：查看業務員詳細資料

**使用者流程**：
```
1. 使用者點擊業務員卡片
   ↓
2. 進入業務員詳細頁面 (/salesperson/[id])
   ↓
3. 頁面頂部顯示大頭貼
   ↓
4. 當 full_name 不存在時
   ├─ 目前：頁面崩潰 ❌
   └─ 改進：顯示 fallback ✅
```

**使用者期望**：
- 看到業務員的詳細資訊
- Avatar 應該更明顯（尺寸較大）
- Fallback 應該專業且易於識別

#### 情境 3：業務員登入後查看 Dashboard

**使用者流程**：
```
1. 業務員登入系統
   ↓
2. 訪問個人 Dashboard (/dashboard)
   ↓
3. 看到自己的 Avatar
   ↓
4. 當 full_name 未設定時
   ├─ 目前：可能崩潰 ❌
   └─ 改進：顯示基於 username 的 fallback ✅
```

**使用者期望**：
- 看到自己的個人資料
- Avatar 應該能代表自己
- 如果沒有設定名字，應該有提示

#### 情境 4：Header 中的使用者選單

**使用者流程**：
```
1. 使用者登入後
   ↓
2. Header 右上角顯示使用者 Avatar
   ↓
3. 點擊 Avatar 展開下拉選單
   ↓
4. 當 full_name 不存在時
   └─ 目前：Header 已有良好處理（參考範例） ✅
```

**參考實作**（header.tsx 第 95-101 行）：
```tsx
fallback={
  user.full_name?.substring(0, 2) ||
  user.name?.substring(0, 2).toUpperCase() ||
  user.username?.substring(0, 2).toUpperCase() ||
  user.email?.substring(0, 2).toUpperCase() ||
  'U'
}
```

這是我們要在全系統推廣的標準做法。

---

## 3. Avatar Fallback 視覺設計

### 3.1 設計原則

#### 原則 1：視覺層次

Avatar fallback 應該保持一致的視覺品質：
```
有圖片（最佳）> 名字首字母 > Username 首字母 > Email 首字母 > 預設圖示
```

#### 原則 2：色彩一致性

所有 fallback 使用相同的漸層背景：
```css
background: linear-gradient(135deg, #0ea5e9 0%, #14b8a6 100%)
/* primary-500 → secondary-500 */
```

**理由**：
- 符合 YAMU 設計系統的主色調
- 親和、活潑的視覺風格
- 與品牌一致性

#### 原則 3：字體樣式

Fallback 文字使用：
```css
color: white;
font-weight: 700 (bold);
font-size: 根據 Avatar 尺寸調整
text-transform: uppercase (英文時)
```

**中文 vs 英文處理**：
- **中文**：直接顯示前兩個字（例如：「張三」）
- **英文**：轉換為大寫（例如：「JO」）

### 3.2 不同尺寸的 Avatar 顯示

#### XS (32px × 32px)
```
┌────────┐
│   張三  │  ← 字體 10px, bold
└────────┘
```
**使用場景**：標籤、小型列表

#### SM (40px × 40px)
```
┌──────────┐
│    張三   │  ← 字體 12px, bold
└──────────┘
```
**使用場景**：Header 使用者選單

#### MD (48px × 48px)
```
┌────────────┐
│     張三    │  ← 字體 14px, bold
└────────────┘
```
**使用場景**：預設尺寸

#### LG (64px × 64px)
```
┌──────────────┐
│              │
│      張三     │  ← 字體 18px, bold
│              │
└──────────────┘
```
**使用場景**：卡片、搜尋結果（✅ **主要使用**）

#### XL (80px × 80px)
```
┌────────────────┐
│                │
│      張三       │  ← 字體 24px, bold
│                │
└────────────────┘
```
**使用場景**：個人資料頁面

#### 2XL (96px × 96px)
```
┌──────────────────┐
│                  │
│       張三        │  ← 字體 28px, bold
│                  │
└──────────────────┘
```
**使用場景**：業務員詳細頁面頂部

### 3.3 Fallback 優先級視覺示例

#### 第 1 優先級：full_name（最佳）
```
輸入：{ full_name: "張三" }
顯示：
┌──────────────┐
│              │
│  ┌─────────┐ │
│  │         │ │
│  │   張三   │ │  ← 漸層背景 + 白色文字
│  │         │ │
│  └─────────┘ │
│              │
└──────────────┘
視覺效果：清晰、專業
```

#### 第 2 優先級：name
```
輸入：{ full_name: null, name: "John" }
顯示：
┌──────────────┐
│              │
│  ┌─────────┐ │
│  │         │ │
│  │   JO    │ │  ← 大寫字母
│  │         │ │
│  └─────────┘ │
│              │
└──────────────┘
視覺效果：清晰、可辨識
```

#### 第 3 優先級：username
```
輸入：{ full_name: null, name: null, username: "zhangsan123" }
顯示：
┌──────────────┐
│              │
│  ┌─────────┐ │
│  │         │ │
│  │   ZH    │ │  ← username 首兩字母大寫
│  │         │ │
│  └─────────┘ │
│              │
└──────────────┘
視覺效果：可辨識
```

#### 第 4 優先級：email
```
輸入：{ full_name: null, name: null, username: null, email: "test@example.com" }
顯示：
┌──────────────┐
│              │
│  ┌─────────┐ │
│  │         │ │
│  │   TE    │ │  ← email 首兩字母大寫
│  │         │ │
│  └─────────┘ │
│              │
└──────────────┘
視覺效果：基本可用
```

#### 第 5 優先級：預設 'U'（最終 fallback）
```
輸入：{ full_name: null, name: null, username: null, email: null }
顯示：
┌──────────────┐
│              │
│  ┌─────────┐ │
│  │         │ │
│  │    U    │ │  ← 預設使用者圖示字母
│  │         │ │
│  └─────────┘ │
│              │
└──────────────┘
視覺效果：通用、不突兀
```

### 3.4 邊界情況的視覺處理

#### 單字元名字
```
輸入：{ full_name: "李" }
顯示選項 A（推薦）：
┌──────────────┐
│  ┌─────────┐ │
│  │    李    │ │  ← 單字元居中顯示
│  └─────────┘ │
└──────────────┘

顯示選項 B：
┌──────────────┐
│  ┌─────────┐ │
│  │   李U   │ │  ← 補充 'U'
│  └─────────┘ │
└──────────────┘

決策：選擇 A（更簡潔）
```

#### 包含空格的名字
```
輸入：{ full_name: "  Zhang San  " }
處理：先 trim() → "Zhang San"
顯示：
┌──────────────┐
│  ┌─────────┐ │
│  │   ZH    │ │  ← 正確取前兩個字元
│  └─────────┘ │
└──────────────┘
```

#### Emoji 或特殊字符
```
輸入：{ full_name: "😀😀" }
處理：substring() 可能切割 emoji
顯示：
┌──────────────┐
│  ┌─────────┐ │
│  │   😀😀  │ │  ← 可能顯示不完整
│  └─────────┘ │
└──────────────┘

注意：目前不特殊處理，未來可考慮使用 Array.from()
```

---

## 4. 響應式設計

### 4.1 不同裝置上的 Avatar 顯示

#### 手機（< 640px）

**搜尋結果頁面**：
```
┌─────────────────────────┐
│  ┌───┐ 張三              │  ← Avatar: md (48px)
│  │張三│ 三商美邦人壽      │
│  └───┘ 🏢 保險業         │
│                          │
│  簡介：提供完整的職涯... │
│                          │
│  💼 壽險 💼 投資型保單   │
│  📍 服務地區：台北、新北 │
└─────────────────────────┘
```

**Dashboard 頁面**：
```
┌─────────────────────────┐
│       ┌─────────┐        │  ← Avatar: xl (80px)
│       │         │        │
│       │   張三   │        │
│       │         │        │
│       └─────────┘        │
│                          │
│         張三             │
│      業務員 #12345       │
└─────────────────────────┘
```

#### 平板（640px - 1024px）

**搜尋結果**（2 欄佈局）：
```
┌────────────────┬────────────────┐
│  ┌───┐ 張三    │  ┌───┐ 李四    │  ← Avatar: lg (64px)
│  │張三│ 三商... │  │李四│ 南山... │
│  └───┘         │  └───┘         │
└────────────────┴────────────────┘
```

#### 桌面（> 1024px）

**搜尋結果**（3 欄佈局）：
```
┌──────────┬──────────┬──────────┐
│  ┌───┐  │  ┌───┐  │  ┌───┐  │  ← Avatar: lg (64px)
│  │張三│  │  │李四│  │  │王五│  │
│  └───┘  │  └───┘  │  └───┘  │
│  張三    │  李四    │  王五    │
└──────────┴──────────┴──────────┘
```

### 4.2 觸控優化

**Avatar 觸控區域**：
- 最小觸控區域：44px × 44px（符合 Apple Human Interface Guidelines）
- 即使 Avatar 是 sm (40px)，觸控區域也應該是 44px+

**實作方式**：
```tsx
<button className="p-2">  {/* 增加 padding 擴大觸控區域 */}
  <Avatar size="sm" />
</button>
```

---

## 5. 無障礙設計（Accessibility）

### 5.1 螢幕閱讀器支援

#### ARIA 屬性

每個 Avatar 都應該有適當的 `aria-label`：

```tsx
<Avatar
  src={user.avatar}
  fallback={getAvatarFallback(user)}
  aria-label={`${user.full_name || user.username || '使用者'} 的頭像`}
  size="lg"
/>
```

#### 螢幕閱讀器朗讀順序

**情境 1：有 full_name**
```
朗讀：「張三 的頭像，圖片」
```

**情境 2：使用 fallback**
```
朗讀：「張三 的頭像，文字 張三」
```

**情境 3：最終 fallback**
```
朗讀：「使用者 的頭像，文字 U」
```

### 5.2 色彩對比度

**WCAG 2.1 AA 標準**：
- 文字與背景對比度應 ≥ 4.5:1

**檢查**：
```
背景：漸層 (primary-500 → secondary-500)
文字：白色 (#ffffff)

對比度計算：
- primary-500 (#0ea5e9) vs 白色：3.8:1 ❌ (不足)
- 調整：使用 primary-600 (#0284c7) vs 白色：4.6:1 ✅

建議漸層：
background: linear-gradient(135deg, #0284c7 0%, #0d9488 100%)
```

### 5.3 鍵盤導航

**可點擊的 Avatar**（如 Header 使用者選單）：
- 必須可以用 Tab 鍵聚焦
- 必須有清晰的 focus 指示器

```tsx
<button
  className="focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 rounded-full"
>
  <Avatar ... />
</button>
```

---

## 6. 互動狀態設計

### 6.1 載入狀態

**Avatar 圖片載入中**：
```tsx
// 1. 顯示骨架屏
<div className="h-16 w-16 rounded-full bg-slate-200 animate-pulse" />

// 2. 圖片載入完成後淡入
<Avatar
  className="opacity-0 animate-fade-in"
  onLoad={() => setLoaded(true)}
/>
```

### 6.2 錯誤狀態

**圖片載入失敗**：
```tsx
<Avatar
  src={user.avatar}
  fallback={getAvatarFallback(user)}
  onError={() => {
    // 自動切換到 fallback
    // 不需要特殊處理，Avatar 組件會自動顯示 fallback
  }}
/>
```

### 6.3 懸停狀態（如果可點擊）

**卡片中的 Avatar**：
```css
/* 整張卡片懸停時 */
.card:hover .avatar {
  transform: scale(1.05);
  transition: transform 200ms ease-out;
}
```

**Header 使用者選單 Avatar**：
```css
/* 懸停時 */
.avatar-button:hover {
  opacity: 0.8;
  transition: opacity 150ms ease-out;
}
```

---

## 7. 動畫與過渡效果

### 7.1 Fallback 切換動畫

當從圖片切換到 fallback 時（例如圖片載入失敗）：

```css
/* 淡入效果 */
@keyframes fade-in {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.avatar-fallback {
  animation: fade-in 200ms ease-out;
}
```

### 7.2 載入動畫

**骨架屏脈動**：
```css
@keyframes pulse {
  0%, 100% {
    opacity: 1;
  }
  50% {
    opacity: 0.5;
  }
}

.skeleton-avatar {
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
```

---

## 8. 效能考量

### 8.1 渲染效能

**Fallback 計算時機**：
- ✅ 在元件渲染前計算（一次性）
- ❌ 不在每次 render 時重新計算

**實作**：
```tsx
// ✅ 正確：計算一次
const fallback = useMemo(
  () => getAvatarFallback(user),
  [user]
);

// ❌ 錯誤：每次 render 都計算
<Avatar fallback={getAvatarFallback(user)} />
```

### 8.2 圖片優化

**Avatar 圖片**：
- 使用 Next.js Image 組件
- 自動 lazy loading
- 自動 WebP 轉換
- 適當的尺寸（不載入過大圖片）

---

## 9. 視覺回歸測試清單

### 9.1 測試場景

- [ ] **搜尋頁面**
  - [ ] 顯示有 full_name 的業務員
  - [ ] 顯示沒有 full_name 的業務員
  - [ ] 顯示各種 fallback 情況
  - [ ] 懸停效果正常
  - [ ] 響應式佈局正常

- [ ] **業務員詳細頁面**
  - [ ] 大頭貼正常顯示
  - [ ] Fallback 正常顯示
  - [ ] 圖片載入失敗時切換到 fallback

- [ ] **Dashboard 頁面**
  - [ ] 個人 Avatar 正常
  - [ ] 統計卡片中的 Avatar 正常

- [ ] **Header**
  - [ ] 使用者選單 Avatar 正常
  - [ ] 下拉選單顯示正常
  - [ ] 響應式切換正常

### 9.2 瀏覽器測試

- [ ] Chrome (最新版)
- [ ] Firefox (最新版)
- [ ] Safari (最新版)
- [ ] Edge (最新版)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

### 9.3 螢幕尺寸測試

- [ ] 手機豎屏 (375px)
- [ ] 手機橫屏 (667px)
- [ ] 平板 (768px)
- [ ] 小筆電 (1024px)
- [ ] 桌機 (1280px)
- [ ] 大螢幕 (1920px)

---

## 10. 設計交付物

### 10.1 需要提供的資產

- ✅ 工具函數：`getAvatarFallback()`
- ✅ TypeScript 類型定義
- ✅ JSDoc 註解
- ✅ 使用範例

### 10.2 設計審查檢核清單

**視覺設計**：
- [ ] 色彩使用符合設計系統
- [ ] 圓角符合設計系統（rounded-full）
- [ ] 字體大小適當
- [ ] 對比度符合 WCAG AA 標準

**互動設計**：
- [ ] 懸停效果流暢
- [ ] 載入狀態友善
- [ ] 錯誤處理優雅
- [ ] 動畫時間適當（150-200ms）

**響應式設計**：
- [ ] 手機版佈局良好
- [ ] 平板版佈局良好
- [ ] 桌面版佈局良好
- [ ] 觸控區域足夠大

**無障礙設計**：
- [ ] ARIA 屬性完整
- [ ] 鍵盤導航正常
- [ ] 螢幕閱讀器友善
- [ ] 色彩對比度達標

---

## 11. 設計決策記錄

### 決策 1：單字元名字的處理

**問題**：當 full_name 只有一個字元時，如何顯示？

**選項**：
- A. 只顯示該字元（例如：「李」）
- B. 補充 'U'（例如：「李U」）

**決定**：選擇 A

**理由**：
- 單字元（尤其是中文姓氏）已經足夠辨識
- 補充 'U' 反而顯得奇怪
- 簡潔優於複雜

### 決策 2：email 的處理

**問題**：是否要驗證 email 格式？

**決定**：不驗證

**理由**：
- 增加複雜度
- 即使格式不正確，也不影響 fallback 顯示
- 如果 email 無效，會 fallback 到預設 'U'

### 決策 3：大寫轉換

**問題**：中文名字是否要轉換大寫？

**決定**：不轉換

**理由**：
- 中文沒有大小寫概念
- `.toUpperCase()` 對中文無效
- 保持中文原樣更自然

**實作**：
```typescript
// 對英文有效，對中文無影響
return fullName.substring(0, 2).toUpperCase();
```

### 決策 4：優先級順序

**問題**：為什麼是 full_name → name → username → email？

**決定**：基於資訊完整性和專業性

**理由**：
- `full_name`：最完整、最專業
- `name`：次要選擇（一般使用者）
- `username`：技術性較強，但可辨識
- `email`：最後手段，專業性較低

---

## 12. 未來改進方向

### Phase 2.0：視覺增強

1. **色彩個性化**
   - 根據名字生成不同的背景顏色
   - 類似 GitHub 的 Avatar 色彩策略
   ```typescript
   function getColorFromName(name: string): string {
     // 基於名字 hash 生成顏色
     const colors = [
       'from-blue-500 to-cyan-500',
       'from-purple-500 to-pink-500',
       'from-green-500 to-teal-500',
       // ...
     ];
     return colors[hashCode(name) % colors.length];
   }
   ```

2. **圖示 fallback**
   - 當完全沒有資料時，顯示 User 圖示而非 'U'
   ```tsx
   {allFieldsEmpty ? (
     <User className="h-8 w-8" />
   ) : (
     <span>{fallbackText}</span>
   )}
   ```

3. **動畫效果**
   - 圖片載入完成時的淡入效果
   - Fallback 切換時的平滑過渡

### Phase 2.1：多語言支援

1. **不同語言的 fallback 策略**
   - 中文：取姓氏（例如：「張」）
   - 英文：取首字母（例如：「JS」for John Smith）
   - 日文：取假名首字

2. **國際化 fallback 文字**
   - 預設 'U' 可以根據語言改變
   - 例如：中文 '用'，日文 'ユ'

---

**審查者**: React Specialist, QA Engineer
**狀態**: Draft → Ready for Implementation
**下一步**: 撰寫組件規格 (components.md)
