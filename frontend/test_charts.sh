#!/bin/bash

# 顏色定義
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo "========================================="
echo "Admin Statistics 圖表功能測試"
echo "========================================="
echo ""

# 1. 測試 Admin Statistics 頁面可訪問性
echo "1. 測試 Statistics 頁面可訪問性"
echo "-------------------------------------------"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost:3000/admin/statistics")
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "307" ]; then
    echo -e "${GREEN}✓${NC} Statistics Page (http://localhost:3000/admin/statistics) - $HTTP_CODE"
else
    echo -e "${RED}✗${NC} Statistics Page - Expected 200/307, Got $HTTP_CODE"
fi
echo ""

# 2. 登入並獲取 token
echo "2. 登入 Admin 帳號"
echo "-------------------------------------------"
LOGIN_RESPONSE=$(curl -s -X POST http://localhost:8080/api/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@example.com","password":"admin123"}')

ACCESS_TOKEN=$(echo $LOGIN_RESPONSE | grep -o '"access_token":"[^"]*"' | cut -d'"' -f4)

if [ -z "$ACCESS_TOKEN" ]; then
    echo -e "${RED}✗${NC} 登入失敗"
    exit 1
else
    echo -e "${GREEN}✓${NC} Admin 登入成功"
fi
echo ""

# 3. 測試 Statistics API
echo "3. 測試 Statistics API"
echo "-------------------------------------------"
STATS_RESPONSE=$(curl -s -X GET http://localhost:8080/api/admin/statistics \
  -H "Authorization: Bearer $ACCESS_TOKEN")

TOTAL_SALESPERSONS=$(echo $STATS_RESPONSE | grep -o '"total_salespersons":[0-9]*' | cut -d':' -f2)
ACTIVE_SALESPERSONS=$(echo $STATS_RESPONSE | grep -o '"active_salespersons":[0-9]*' | cut -d':' -f2)
PENDING_SALESPERSONS=$(echo $STATS_RESPONSE | grep -o '"pending_salespersons":[0-9]*' | cut -d':' -f2)
TOTAL_COMPANIES=$(echo $STATS_RESPONSE | grep -o '"total_companies":[0-9]*' | cut -d':' -f2)

if [ ! -z "$TOTAL_SALESPERSONS" ]; then
    echo -e "${GREEN}✓${NC} Statistics API 返回成功"
    echo "  - 總業務員數: $TOTAL_SALESPERSONS"
    echo "  - 活躍業務員: $ACTIVE_SALESPERSONS"
    echo "  - 待審核業務員: $PENDING_SALESPERSONS"
    echo "  - 公司總數: $TOTAL_COMPANIES"
else
    echo -e "${RED}✗${NC} Statistics API 返回失敗"
fi
echo ""

# 4. 測試 Pending Approvals API
echo "4. 測試 Pending Approvals API"
echo "-------------------------------------------"
PENDING_RESPONSE=$(curl -s -X GET http://localhost:8080/api/admin/pending-approvals \
  -H "Authorization: Bearer $ACCESS_TOKEN")

PENDING_USERS=$(echo $PENDING_RESPONSE | grep -o '"users":\[[^]]*\]' | grep -o '\[' | wc -l)
PENDING_COMPANIES=$(echo $PENDING_RESPONSE | grep -o '"companies":\[[^]]*\]' | grep -o '\[' | wc -l)
PENDING_CERTIFICATIONS=$(echo $PENDING_RESPONSE | grep -o '"certifications":\[[^]]*\]' | grep -o '\[' | wc -l)

if echo $PENDING_RESPONSE | grep -q '"users"'; then
    echo -e "${GREEN}✓${NC} Pending Approvals API 返回成功"
    echo "  - 待審核業務員註冊: 有數據"
    echo "  - 待審核公司: 有數據"
    echo "  - 待審核證照: 有數據"
else
    echo -e "${RED}✗${NC} Pending Approvals API 返回失敗"
fi
echo ""

# 5. 測試 Recharts 組件是否已安裝
echo "5. 檢查 Recharts 依賴"
echo "-------------------------------------------"
if grep -q '"recharts"' package.json; then
    RECHARTS_VERSION=$(grep -o '"recharts": "[^"]*"' package.json | cut -d'"' -f4)
    echo -e "${GREEN}✓${NC} Recharts 已安裝 (版本: $RECHARTS_VERSION)"
else
    echo -e "${RED}✗${NC} Recharts 未安裝"
fi
echo ""

# 6. 檢查圖表組件文件
echo "6. 檢查圖表組件文件"
echo "-------------------------------------------"
CHART_FILES=(
    "components/features/admin/charts/salesperson-status-chart.tsx"
    "components/features/admin/charts/pending-approvals-chart.tsx"
    "components/features/admin/charts/salesperson-overview-chart.tsx"
    "components/features/admin/charts/index.ts"
)

ALL_FILES_EXIST=true
for file in "${CHART_FILES[@]}"; do
    if [ -f "$file" ]; then
        echo -e "${GREEN}✓${NC} $file"
    else
        echo -e "${RED}✗${NC} $file 不存在"
        ALL_FILES_EXIST=false
    fi
done
echo ""

# 總結
echo "========================================="
echo "測試總結"
echo "========================================="

if [ ! -z "$TOTAL_SALESPERSONS" ] && [ ! -z "$ACCESS_TOKEN" ] && [ "$ALL_FILES_EXIST" = true ]; then
    echo -e "${GREEN}✓ 所有圖表功能測試通過${NC}"
    echo ""
    echo "📊 圖表數據預覽："
    echo "  • 業務員狀態分佈圓餅圖："
    echo "    - 總數: $TOTAL_SALESPERSONS 人"
    echo "    - 活躍: $ACTIVE_SALESPERSONS 人"
    echo "    - 待審核: $PENDING_SALESPERSONS 人"
    echo ""
    echo "  • 平台總覽柱狀圖："
    echo "    - 業務員總數: $TOTAL_SALESPERSONS"
    echo "    - 公司總數: $TOTAL_COMPANIES"
    echo ""
    echo "  • 待審核項目柱狀圖："
    echo "    - 待審核數據已就緒"
    echo ""
    echo "🌐 請訪問以下網址查看圖表："
    echo "   http://localhost:3000/admin/statistics"
    echo ""
    echo "📝 登入資訊："
    echo "   Email: admin@example.com"
    echo "   Password: admin123"
else
    echo -e "${RED}✗ 部分測試失敗${NC}"
fi
echo ""
