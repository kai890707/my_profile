#!/bin/bash

# Admin 功能測試腳本

echo "========================================="
echo "Admin 功能自動化測試"
echo "========================================="
echo ""

# 獲取 Admin Token
echo "1. 登入 Admin 帳號..."
TOKEN=$(curl -s -X POST "http://localhost:8080/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}' | jq -r '.data.access_token')

if [ -z "$TOKEN" ] || [ "$TOKEN" = "null" ]; then
  echo "❌ 登入失敗"
  exit 1
fi
echo "✅ 登入成功"
echo ""

# 測試 1: Statistics API
echo "2. 測試 Statistics API..."
STATS=$(curl -s -X GET "http://localhost:8080/api/admin/statistics" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")
echo "結果: $(echo $STATS | jq '{status, data}')"
echo ""

# 測試 2: Pending Approvals API
echo "3. 測試 Pending Approvals API..."
PENDING=$(curl -s -X GET "http://localhost:8080/api/admin/pending-approvals" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")
echo "狀態: $(echo $PENDING | jq -r '.status')"
echo "待審核用戶: $(echo $PENDING | jq '.data.users | length')"
echo "待審核資料: $(echo $PENDING | jq '.data.profiles | length')"
echo "待審核公司: $(echo $PENDING | jq '.data.companies | length')"
echo "待審核證照: $(echo $PENDING | jq '.data.certifications | length')"
echo ""

# 測試 3: Users List API
echo "4. 測試 Users List API..."
USERS=$(curl -s -X GET "http://localhost:8080/api/admin/users" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")
echo "總用戶數: $(echo $USERS | jq '.data | length')"
echo ""

# 測試 4: Users List with Filters
echo "5. 測試 Users List API (帶過濾)..."
SALESPERSONS=$(curl -s -X GET "http://localhost:8080/api/admin/users?role=salesperson&status=active" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")
echo "活躍業務員數: $(echo $SALESPERSONS | jq '.data | length')"
echo ""

# 測試 5: Industries API
echo "6. 測試 Industries API..."
INDUSTRIES=$(curl -s -X GET "http://localhost:8080/api/admin/settings/industries" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")
echo "產業類別數: $(echo $INDUSTRIES | jq '.data | length')"
echo "範例: $(echo $INDUSTRIES | jq '.data[0].name')"
echo ""

# 測試 6: Regions API
echo "7. 測試 Regions API..."
REGIONS=$(curl -s -X GET "http://localhost:8080/api/admin/settings/regions" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")
echo "地區數: $(echo $REGIONS | jq '.data | length')"
echo "範例: $(echo $REGIONS | jq '.data[0].name')"
echo ""

# 測試 7: Create Industry (測試新增功能)
echo "8. 測試 Create Industry API..."
NEW_INDUSTRY=$(curl -s -X POST "http://localhost:8080/api/admin/settings/industries" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"name":"測試產業","slug":"test-industry","description":"這是測試用的產業類別"}')
echo "結果: $(echo $NEW_INDUSTRY | jq '{status, message}')"
echo ""

# 測試 8: 再次獲取 Industries 確認新增成功
echo "9. 確認新增成功..."
INDUSTRIES_AFTER=$(curl -s -X GET "http://localhost:8080/api/admin/settings/industries" \
  -H "Authorization: Bearer $TOKEN" \
  -H "Content-Type: application/json")
echo "產業類別數（新增後）: $(echo $INDUSTRIES_AFTER | jq '.data | length')"
echo ""

echo "========================================="
echo "測試完成！"
echo "========================================="
