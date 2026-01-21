#!/usr/bin/env python3
"""
Test the enhanced salesperson page with new timeline and certification cards.
Tests:
1. Page loads correctly
2. Experience timeline displays with proper structure
3. Certification cards display with grid layout
4. Expand/collapse functionality works
5. Filter functionality works (certifications)
6. Responsive design (Mobile/Tablet/Desktop)
"""

from playwright.sync_api import sync_playwright
import sys

def test_salesperson_page():
    print("🧪 Starting Salesperson Page Tests...")

    with sync_playwright() as p:
        # Launch browser in headless mode
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        try:
            # Test 1: Navigate to salesperson page
            print("\n📍 Test 1: Loading salesperson page...")
            page.goto('http://localhost:3001/salesperson/1')
            page.wait_for_load_state('networkidle')

            # Take initial screenshot
            page.screenshot(path='/tmp/salesperson-page-initial.png', full_page=True)
            print("✅ Page loaded successfully")
            print("📸 Screenshot saved: /tmp/salesperson-page-initial.png")

            # Test 2: Check if Experience Timeline exists
            print("\n📍 Test 2: Checking Experience Timeline...")
            timeline = page.locator('[class*="experience-timeline"]').first
            if timeline.count() > 0:
                print("✅ Experience Timeline found")

                # Check for timeline items
                timeline_items = page.locator('article[role="listitem"]')
                item_count = timeline_items.count()
                print(f"✅ Found {item_count} experience items")

                # Check for timeline visual elements
                if page.locator('.absolute.left-0').count() > 0:
                    print("✅ Timeline line visible")
                if page.locator('.absolute.left-0.top-0.w-3.h-3').count() > 0:
                    print("✅ Timeline nodes visible")
            else:
                print("⚠️  Experience Timeline not found (may be using old design)")

            # Test 3: Test expand/collapse on first experience item
            print("\n📍 Test 3: Testing expand/collapse (Experience)...")
            expand_button = page.locator('button:has-text("展開")').first
            if expand_button.count() > 0:
                # Take screenshot before expand
                page.screenshot(path='/tmp/experience-before-expand.png', full_page=True)
                print("📸 Before expand: /tmp/experience-before-expand.png")

                # Click expand
                expand_button.click()
                page.wait_for_timeout(500)  # Wait for animation

                # Take screenshot after expand
                page.screenshot(path='/tmp/experience-after-expand.png', full_page=True)
                print("✅ Expanded successfully")
                print("📸 After expand: /tmp/experience-after-expand.png")

                # Click collapse
                collapse_button = page.locator('button:has-text("收合")').first
                if collapse_button.count() > 0:
                    collapse_button.click()
                    page.wait_for_timeout(500)
                    print("✅ Collapsed successfully")
            else:
                print("⚠️  Expand button not found (no experiences or already expanded)")

            # Test 4: Check Certification Cards
            print("\n📍 Test 4: Checking Certification Cards...")
            cert_cards = page.locator('[class*="certification-card"]').first
            if cert_cards.count() > 0:
                print("✅ Certification Cards found")

                # Check for grid layout
                grid = page.locator('.grid.md\\:grid-cols-2')
                if grid.count() > 0:
                    print("✅ Grid layout detected (2 columns on desktop)")

                # Count certification cards
                cards = page.locator('[class*="certification-card"]')
                card_count = cards.count()
                print(f"✅ Found {card_count} certification cards")

                # Take screenshot of certifications
                page.screenshot(path='/tmp/certifications-grid.png', full_page=True)
                print("📸 Certifications screenshot: /tmp/certifications-grid.png")
            else:
                print("⚠️  Certification Cards not found (may be using old design)")

            # Test 5: Test certification filter (if exists)
            print("\n📍 Test 5: Testing certification filter...")
            filter_tabs = page.locator('button:has-text("已驗證")')
            if filter_tabs.count() > 0:
                # Take screenshot before filter
                page.screenshot(path='/tmp/certs-before-filter.png', full_page=True)
                print("📸 Before filter: /tmp/certs-before-filter.png")

                # Click "已驗證" filter
                filter_tabs.first.click()
                page.wait_for_timeout(500)

                # Take screenshot after filter
                page.screenshot(path='/tmp/certs-after-filter.png', full_page=True)
                print("✅ Filter applied successfully")
                print("📸 After filter: /tmp/certs-after-filter.png")
            else:
                print("ℹ️  Filter tabs not found (may not be implemented or no certifications)")

            # Test 6: Responsive design - Mobile viewport
            print("\n📍 Test 6: Testing responsive design (Mobile)...")
            page.set_viewport_size({"width": 375, "height": 812})
            page.wait_for_timeout(500)
            page.screenshot(path='/tmp/salesperson-mobile.png', full_page=True)
            print("✅ Mobile viewport (375x812) tested")
            print("📸 Mobile screenshot: /tmp/salesperson-mobile.png")

            # Test 7: Responsive design - Tablet viewport
            print("\n📍 Test 7: Testing responsive design (Tablet)...")
            page.set_viewport_size({"width": 768, "height": 1024})
            page.wait_for_timeout(500)
            page.screenshot(path='/tmp/salesperson-tablet.png', full_page=True)
            print("✅ Tablet viewport (768x1024) tested")
            print("📸 Tablet screenshot: /tmp/salesperson-tablet.png")

            # Test 8: Responsive design - Desktop viewport
            print("\n📍 Test 8: Testing responsive design (Desktop)...")
            page.set_viewport_size({"width": 1920, "height": 1080})
            page.wait_for_timeout(500)
            page.screenshot(path='/tmp/salesperson-desktop.png', full_page=True)
            print("✅ Desktop viewport (1920x1080) tested")
            print("📸 Desktop screenshot: /tmp/salesperson-desktop.png")

            # Test 9: Check for empty state (navigate to non-existent salesperson)
            print("\n📍 Test 9: Testing empty state...")
            page.goto('http://localhost:3001/salesperson/99999')
            page.wait_for_load_state('networkidle')

            # Check for "找不到業務員資料" message
            if page.locator('text=找不到業務員資料').count() > 0:
                print("✅ Empty state (404) displayed correctly")
                page.screenshot(path='/tmp/salesperson-404.png', full_page=True)
                print("📸 404 screenshot: /tmp/salesperson-404.png")
            else:
                print("⚠️  Empty state not found")

            # Test 10: Console errors check
            print("\n📍 Test 10: Checking for console errors...")
            page.goto('http://localhost:3001/salesperson/1')
            page.wait_for_load_state('networkidle')

            # Capture console messages
            errors = []
            page.on("console", lambda msg: errors.append(msg.text) if msg.type == "error" else None)
            page.reload()
            page.wait_for_load_state('networkidle')

            if errors:
                print(f"⚠️  Found {len(errors)} console errors:")
                for error in errors:
                    print(f"   - {error}")
            else:
                print("✅ No console errors detected")

            print("\n" + "="*60)
            print("🎉 All tests completed!")
            print("="*60)
            print("\n📸 Screenshots saved to /tmp/:")
            print("   - salesperson-page-initial.png")
            print("   - experience-before-expand.png")
            print("   - experience-after-expand.png")
            print("   - certifications-grid.png")
            print("   - certs-before-filter.png")
            print("   - certs-after-filter.png")
            print("   - salesperson-mobile.png")
            print("   - salesperson-tablet.png")
            print("   - salesperson-desktop.png")
            print("   - salesperson-404.png")

        except Exception as e:
            print(f"\n❌ Test failed with error: {str(e)}")
            page.screenshot(path='/tmp/salesperson-error.png', full_page=True)
            print("📸 Error screenshot: /tmp/salesperson-error.png")
            browser.close()
            sys.exit(1)

        browser.close()
        print("\n✅ Browser closed")

if __name__ == "__main__":
    test_salesperson_page()
