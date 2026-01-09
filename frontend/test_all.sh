#!/bin/bash

# 全面測試腳本 - YAMU Frontend

echo "========================================="
echo "YAMU Frontend 全面測試"
echo "========================================="
echo ""

# 顏色定義
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# 測試計數器
TOTAL_TESTS=0
PASSED_TESTS=0
FAILED_TESTS=0

# 測試函數
test_page() {
  local page_name=$1
  local page_url=$2
  TOTAL_TESTS=$((TOTAL_TESTS + 1))

  HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$page_url")

  if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✓${NC} $page_name ($page_url) - $HTTP_CODE"
    PASSED_TESTS=$((PASSED_TESTS + 1))
  else
    echo -e "${RED}✗${NC} $page_name ($page_url) - $HTTP_CODE"
    FAILED_TESTS=$((FAILED_TESTS + 1))
  fi
}

# API 測試函數
test_api() {
  local api_name=$1
  local api_url=$2
  local expected_code=${3:-200}
  TOTAL_TESTS=$((TOTAL_TESTS + 1))

  HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$api_url")

  if [ "$HTTP_CODE" = "$expected_code" ]; then
    echo -e "${GREEN}✓${NC} $api_name - $HTTP_CODE"
    PASSED_TESTS=$((PASSED_TESTS + 1))
  else
    echo -e "${RED}✗${NC} $api_name - Expected $expected_code, Got $HTTP_CODE"
    FAILED_TESTS=$((FAILED_TESTS + 1))
  fi
}

# ===== 1. 前端頁面測試 =====
echo "1. 測試前端頁面可訪問性"
echo "-------------------------------------------"

# 公開頁面
test_page "Homepage" "http://localhost:3000/"
test_page "Search Page" "http://localhost:3000/search"
test_page "Login Page" "http://localhost:3000/login"
test_page "Register Page" "http://localhost:3000/register"
test_page "403 Page" "http://localhost:3000/403"

echo ""

# ===== 2. 後端 API 測試 =====
echo "2. 測試後端 API 可訪問性"
echo "-------------------------------------------"

# 搜尋 API
test_api "Search API (salespersons)" "http://localhost:8080/api/search/salespersons"

# 需要認證的 API (預期 401)
test_api "Dashboard Profile API (no auth)" "http://localhost:8080/api/salesperson/profile" "401"
test_api "Admin Statistics API (no auth)" "http://localhost:8080/api/admin/statistics" "401"

echo ""

# ===== 3. 認證流程測試 =====
echo "3. 測試認證流程"
echo "-------------------------------------------"

# 嘗試登入並獲取 token
echo "正在登入 Admin 帳號..."
LOGIN_RESPONSE=$(curl -s -X POST "http://localhost:8080/api/auth/login" \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}')

TOKEN=$(echo $LOGIN_RESPONSE | jq -r '.data.access_token')

if [ "$TOKEN" != "null" ] && [ -n "$TOKEN" ]; then
  echo -e "${GREEN}✓${NC} Admin 登入成功"
  PASSED_TESTS=$((PASSED_TESTS + 1))
  TOTAL_TESTS=$((TOTAL_TESTS + 1))

  # 測試需要認證的 API
  echo ""
  echo "4. 測試已認證 API"
  echo "-------------------------------------------"

  # Admin APIs
  API_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Authorization: Bearer $TOKEN" \
    "http://localhost:8080/api/admin/statistics")

  if [ "$API_CODE" = "200" ]; then
    echo -e "${GREEN}✓${NC} Admin Statistics API (with auth) - $API_CODE"
    PASSED_TESTS=$((PASSED_TESTS + 1))
  else
    echo -e "${RED}✗${NC} Admin Statistics API (with auth) - $API_CODE"
    FAILED_TESTS=$((FAILED_TESTS + 1))
  fi
  TOTAL_TESTS=$((TOTAL_TESTS + 1))

  API_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Authorization: Bearer $TOKEN" \
    "http://localhost:8080/api/admin/pending-approvals")

  if [ "$API_CODE" = "200" ]; then
    echo -e "${GREEN}✓${NC} Admin Pending Approvals API - $API_CODE"
    PASSED_TESTS=$((PASSED_TESTS + 1))
  else
    echo -e "${RED}✗${NC} Admin Pending Approvals API - $API_CODE"
    FAILED_TESTS=$((FAILED_TESTS + 1))
  fi
  TOTAL_TESTS=$((TOTAL_TESTS + 1))

  API_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Authorization: Bearer $TOKEN" \
    "http://localhost:8080/api/admin/users")

  if [ "$API_CODE" = "200" ]; then
    echo -e "${GREEN}✓${NC} Admin Users API - $API_CODE"
    PASSED_TESTS=$((PASSED_TESTS + 1))
  else
    echo -e "${RED}✗${NC} Admin Users API - $API_CODE"
    FAILED_TESTS=$((FAILED_TESTS + 1))
  fi
  TOTAL_TESTS=$((TOTAL_TESTS + 1))

  API_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Authorization: Bearer $TOKEN" \
    "http://localhost:8080/api/admin/settings/industries")

  if [ "$API_CODE" = "200" ]; then
    echo -e "${GREEN}✓${NC} Admin Industries API - $API_CODE"
    PASSED_TESTS=$((PASSED_TESTS + 1))
  else
    echo -e "${RED}✗${NC} Admin Industries API - $API_CODE"
    FAILED_TESTS=$((FAILED_TESTS + 1))
  fi
  TOTAL_TESTS=$((TOTAL_TESTS + 1))

  API_CODE=$(curl -s -o /dev/null -w "%{http_code}" \
    -H "Authorization: Bearer $TOKEN" \
    "http://localhost:8080/api/admin/settings/regions")

  if [ "$API_CODE" = "200" ]; then
    echo -e "${GREEN}✓${NC} Admin Regions API - $API_CODE"
    PASSED_TESTS=$((PASSED_TESTS + 1))
  else
    echo -e "${RED}✗${NC} Admin Regions API - $API_CODE"
    FAILED_TESTS=$((FAILED_TESTS + 1))
  fi
  TOTAL_TESTS=$((TOTAL_TESTS + 1))

else
  echo -e "${RED}✗${NC} Admin 登入失敗"
  FAILED_TESTS=$((FAILED_TESTS + 1))
  TOTAL_TESTS=$((TOTAL_TESTS + 1))
fi

echo ""

# ===== 測試結果總結 =====
echo "========================================="
echo "測試結果總結"
echo "========================================="
echo "總測試數: $TOTAL_TESTS"
echo -e "${GREEN}通過: $PASSED_TESTS${NC}"
echo -e "${RED}失敗: $FAILED_TESTS${NC}"

SUCCESS_RATE=$(awk "BEGIN {printf \"%.1f\", ($PASSED_TESTS/$TOTAL_TESTS)*100}")
echo "成功率: $SUCCESS_RATE%"

echo ""

if [ $FAILED_TESTS -eq 0 ]; then
  echo -e "${GREEN}🎉 所有測試通過！${NC}"
  exit 0
else
  echo -e "${YELLOW}⚠️  有 $FAILED_TESTS 個測試失敗${NC}"
  exit 1
fi
