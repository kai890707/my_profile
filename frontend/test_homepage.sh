#!/bin/bash

# Homepage 詳細測試腳本

echo "========================================="
echo "Homepage 測試報告"
echo "========================================="
echo ""

# 顏色定義
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

BASE_URL="http://localhost:3000"

echo -e "${BLUE}測試環境${NC}"
echo "-------------------------------------------"
echo "Frontend URL: $BASE_URL"
echo "Backend API: http://localhost:8080/api"
echo ""

# 1. 測試頁面可訪問性
echo -e "${BLUE}1. 頁面可訪問性測試${NC}"
echo "-------------------------------------------"

HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL/")
if [ "$HTTP_CODE" = "200" ]; then
    echo -e "${GREEN}✓${NC} Homepage 可訪問 - HTTP $HTTP_CODE"
else
    echo -e "${RED}✗${NC} Homepage 無法訪問 - HTTP $HTTP_CODE"
    exit 1
fi
echo ""

# 2. 測試頁面內容
echo -e "${BLUE}2. 頁面內容測試${NC}"
echo "-------------------------------------------"

HOMEPAGE_CONTENT=$(curl -s "$BASE_URL/")

# 檢查關鍵內容
check_content() {
    local content=$1
    local search_text=$2
    local description=$3

    if echo "$content" | grep -q "$search_text"; then
        echo -e "${GREEN}✓${NC} $description"
        return 0
    else
        echo -e "${RED}✗${NC} $description"
        return 1
    fi
}

check_content "$HOMEPAGE_CONTENT" "YAMU" "網站名稱 (YAMU)"
check_content "$HOMEPAGE_CONTENT" "業務員" "業務員關鍵字"
check_content "$HOMEPAGE_CONTENT" "媒合平台" "媒合平台關鍵字"

echo ""

# 3. 測試頁面載入時間
echo -e "${BLUE}3. 頁面載入性能測試${NC}"
echo "-------------------------------------------"

LOAD_TIME=$(curl -s -o /dev/null -w "%{time_total}" "$BASE_URL/")
LOAD_TIME_MS=$(echo "$LOAD_TIME * 1000" | bc | cut -d'.' -f1)

if [ $LOAD_TIME_MS -lt 3000 ]; then
    echo -e "${GREEN}✓${NC} 頁面載入時間: ${LOAD_TIME_MS}ms (優秀 <3000ms)"
elif [ $LOAD_TIME_MS -lt 5000 ]; then
    echo -e "${YELLOW}⚠${NC} 頁面載入時間: ${LOAD_TIME_MS}ms (可接受 <5000ms)"
else
    echo -e "${RED}✗${NC} 頁面載入時間: ${LOAD_TIME_MS}ms (需優化 >5000ms)"
fi
echo ""

# 4. 測試相關頁面連結
echo -e "${BLUE}4. 相關頁面連結測試${NC}"
echo "-------------------------------------------"

test_page() {
    local page_path=$1
    local page_name=$2
    local http_code=$(curl -s -o /dev/null -w "%{http_code}" "$BASE_URL$page_path")

    if [ "$http_code" = "200" ]; then
        echo -e "${GREEN}✓${NC} $page_name ($page_path) - $http_code"
    else
        echo -e "${RED}✗${NC} $page_name ($page_path) - $http_code"
    fi
}

test_page "/search" "搜尋頁面"
test_page "/login" "登入頁面"
test_page "/register" "註冊頁面"

echo ""

# 5. 測試 API 連接
echo -e "${BLUE}5. API 連接測試${NC}"
echo "-------------------------------------------"

API_URL="http://localhost:8080/api/search/salespersons"
API_CODE=$(curl -s -o /dev/null -w "%{http_code}" "$API_URL")

if [ "$API_CODE" = "200" ]; then
    echo -e "${GREEN}✓${NC} 搜尋 API 連接正常 - HTTP $API_CODE"
else
    echo -e "${RED}✗${NC} 搜尋 API 連接失敗 - HTTP $API_CODE"
fi
echo ""

# 6. 測試靜態資源
echo -e "${BLUE}6. 靜態資源測試${NC}"
echo "-------------------------------------------"

# 檢查是否有 CSS
if echo "$HOMEPAGE_CONTENT" | grep -q "stylesheet\|<style"; then
    echo -e "${GREEN}✓${NC} CSS 樣式已載入"
else
    echo -e "${YELLOW}⚠${NC} 未檢測到 CSS 樣式"
fi

# 檢查是否有 JavaScript
if echo "$HOMEPAGE_CONTENT" | grep -q "<script"; then
    echo -e "${GREEN}✓${NC} JavaScript 已載入"
else
    echo -e "${YELLOW}⚠${NC} 未檢測到 JavaScript"
fi

echo ""

# 總結
echo "========================================="
echo -e "${GREEN}Homepage 測試完成${NC}"
echo "========================================="
echo ""
echo "📊 測試摘要:"
echo "  • 頁面可訪問性: ✓"
echo "  • 載入時間: ${LOAD_TIME_MS}ms"
echo "  • 相關頁面: 已測試"
echo "  • API 連接: 已驗證"
echo ""
echo "🌐 訪問網址: $BASE_URL"
echo ""
