# Page snapshot

```yaml
- generic [active] [ref=e1]:
  - generic [ref=e3]:
    - generic [ref=e4]:
      - img [ref=e6]
      - heading "YAMU" [level=1] [ref=e8]
      - paragraph [ref=e9]: 業務員搜尋平台
    - generic [ref=e11]:
      - generic [ref=e12]:
        - heading "登入" [level=2] [ref=e13]
        - paragraph [ref=e14]: 歡迎回來！請登入您的帳號
      - generic [ref=e15]:
        - generic [ref=e16]:
          - generic [ref=e17]: 電子郵件*
          - textbox "your@email.com" [ref=e19]: user@example.com
        - generic [ref=e20]:
          - generic [ref=e21]: 密碼*
          - textbox "請輸入密碼" [ref=e23]: password123
        - button "登入" [ref=e25]
        - link "忘記密碼？" [ref=e27] [cursor=pointer]:
          - /url: "#"
      - generic [ref=e32]: 還沒有帳號？
      - link "立即註冊 →" [ref=e34] [cursor=pointer]:
        - /url: /register
    - paragraph [ref=e36]: © 2026 YAMU. All rights reserved.
  - region "Notifications alt+T"
  - generic [ref=e37]:
    - img [ref=e39]
    - button "Open Tanstack query devtools" [ref=e87] [cursor=pointer]:
      - img [ref=e88]
  - button "Open Next.js Dev Tools" [ref=e141] [cursor=pointer]:
    - img [ref=e142]
  - alert [ref=e145]
```