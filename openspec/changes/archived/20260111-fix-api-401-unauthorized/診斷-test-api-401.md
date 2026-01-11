# API 401 錯誤診斷測試

**日期**: 2026-01-11
**問題**: `/api/salesperson/profile` 返回 401 Unauthorized

---

## 診斷步驟

### Step 1: 確認登入狀態

請在瀏覽器 Console 執行以下命令：

```javascript
// 檢查 localStorage 中的 Token
console.log('Access Token:', localStorage.getItem('access_token'));
console.log('Refresh Token:', localStorage.getItem('refresh_token'));
```

**預期結果**:
- 應該看到兩個 Token 值（JWT 格式的長字串）
- 如果為 null，表示未正確登入

---

### Step 2: 測試認證 API

在瀏覽器 Console 執行：

```javascript
// 測試 /api/auth/me 端點
fetch('http://localhost:8080/api/auth/me', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('access_token')}`,
    'Content-Type': 'application/json'
  }
})
.then(res => res.json())
.then(data => console.log('Auth Me Response:', data))
.catch(err => console.error('Error:', err));
```

**預期結果**:
- 如果成功 (200): Token 有效
- 如果 401: Token 無效或已過期

---

### Step 3: 測試目標 API

```javascript
// 測試 /api/salesperson/profile 端點
fetch('http://localhost:8080/api/salesperson/profile', {
  headers: {
    'Authorization': `Bearer ${localStorage.getItem('access_token')}`,
    'Content-Type': 'application/json'
  }
})
.then(res => {
  console.log('Status:', res.status);
  return res.json();
})
.then(data => console.log('Profile Response:', data))
.catch(err => console.error('Error:', err));
```

**預期結果**:
- 記錄 HTTP 狀態碼
- 記錄錯誤訊息內容

---

### Step 4: 檢查 Token 格式

```javascript
// 解碼 Token 查看內容
function parseJwt(token) {
  try {
    const base64Url = token.split('.')[1];
    const base64 = base64Url.replace(/-/g, '+').replace(/_/g, '/');
    const jsonPayload = decodeURIComponent(atob(base64).split('').map(c => {
      return '%' + ('00' + c.charCodeAt(0).toString(16)).slice(-2);
    }).join(''));
    return JSON.parse(jsonPayload);
  } catch (e) {
    return null;
  }
}

const token = localStorage.getItem('access_token');
if (token) {
  const decoded = parseJwt(token);
  console.log('Decoded Token:', decoded);
  console.log('Expires at:', new Date(decoded.exp * 1000));
  console.log('Is expired?', decoded.exp * 1000 < Date.now());
  console.log('User ID:', decoded.sub);
  console.log('Role:', decoded.role);
}
```

**檢查項目**:
- Token 是否已過期
- User ID 是否正確
- Role 是否為 'salesperson'

---

### Step 5: 檢查網路請求

在瀏覽器開啟：
1. 開發者工具 (F12)
2. Network Tab
3. 重新整理 Dashboard 頁面
4. 找到 `/api/salesperson/profile` 請求
5. 查看 Headers

**檢查項目**:
- Request Headers 中是否有 `Authorization: Bearer ...`
- Token 值是否正確傳遞
- Response 的具體錯誤訊息

---

## 診斷結果記錄

請將以上所有測試結果貼在這裡：

### 結果 1: localStorage Token 狀態
```
Access Token: [填寫]
Refresh Token: [填寫]
```

### 結果 2: /api/auth/me 測試
```json
{
  // 貼上回應
}
```

### 結果 3: /api/salesperson/profile 測試
```
Status: [填寫]
Response: [貼上回應]
```

### 結果 4: Token 解碼資訊
```json
{
  "exp": [填寫],
  "sub": [填寫],
  "role": [填寫],
  "is_expired": [填寫]
}
```

### 結果 5: Network Tab 觀察
```
Request Headers:
  Authorization: [填寫]

Response Status: [填寫]
Response Body: [填寫]
```

---

## 可能的問題原因

根據診斷結果，可能的原因包括：

### 原因 A: Token 已過期
**症狀**:
- Token 解碼後 `exp` < 當前時間
- `/api/auth/me` 也返回 401

**解決**: Token 刷新機制問題

---

### 原因 B: Token 未正確傳遞
**症狀**:
- Network Tab 中看不到 `Authorization` Header
- 或 Header 值為空/格式錯誤

**解決**: Frontend Axios 攔截器問題

---

### 原因 C: Backend 中間件問題
**症狀**:
- Token 有效（未過期）
- Authorization Header 正確傳遞
- 但仍返回 401

**解決**: Backend JWT 驗證邏輯問題

---

### 原因 D: 用戶角色問題
**症狀**:
- Token 中 `role` 不是 'salesperson'
- 或用戶 `status` 不是 'active'

**解決**: 用戶資料或權限問題

---

### 原因 E: CORS 問題
**症狀**:
- 瀏覽器 Console 顯示 CORS 錯誤
- Preflight OPTIONS 請求失敗

**解決**: Backend CORS 配置問題

---

## 下一步

根據診斷結果，我們將能準確定位問題並更新 Proposal。
