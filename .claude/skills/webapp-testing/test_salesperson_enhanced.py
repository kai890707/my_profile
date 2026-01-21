#!/usr/bin/env python3
"""
Enhanced test for salesperson page - finds actual data and tests new components.
"""

from playwright.sync_api import sync_playwright
import sys

def test_enhanced_salesperson():
    print("🧪 Testing Enhanced Salesperson Page...")

    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        try:
            # Navigate and wait
            print("\n📍 Loading salesperson page...")
            page.goto('http://localhost:3001/salesperson/1')
            page.wait_for_load_state('networkidle')
            page.wait_for_timeout(2000)  # Extra wait for data loading

            # Take initial screenshot
            page.screenshot(path='/tmp/enhanced-salesperson-initial.png', full_page=True)
            print("✅ Page loaded")
            print("📸 /tmp/enhanced-salesperson-initial.png")

            # Inspect the actual DOM structure
            print("\n📍 Inspecting page structure...")

            # Check for new ExperienceTimeline component
            print("\n🔍 Checking for Experience Timeline...")

            # Try multiple selectors for experience timeline
            timeline_selectors = [
                'article[role="listitem"]',  # Timeline items
                '.relative.border-l-2',      # Timeline structure
                'ol.relative',               # Timeline list
                'h3:has-text("工作經驗")',   # Section heading
            ]

            for selector in timeline_selectors:
                elements = page.locator(selector)
                count = elements.count()
                if count > 0:
                    print(f"✅ Found {count} elements with selector: {selector}")

            # Check for certification cards
            print("\n🔍 Checking for Certification Cards...")

            cert_selectors = [
                'h3:has-text("專業證照")',    # Section heading
                '.grid.md\\:grid-cols-2',     # Grid layout
                '.rounded-lg.border',         # Card borders
                'button:has-text("全部")',    # Filter button
            ]

            for selector in cert_selectors:
                elements = page.locator(selector)
                count = elements.count()
                if count > 0:
                    print(f"✅ Found {count} elements with selector: {selector}")

            # Get all text content to verify component rendering
            print("\n📋 Page content check...")

            # Check for key phrases
            key_phrases = [
                "工作經驗",
                "專業證照",
                "聯絡資訊",
                "個人簡介",
                "服務地區",
            ]

            for phrase in key_phrases:
                if page.locator(f'text={phrase}').count() > 0:
                    print(f"✅ Found: {phrase}")

            # Test interactions
            print("\n📍 Testing interactions...")

            # Find and click any "展開" buttons
            expand_buttons = page.locator('button:has-text("展開")')
            expand_count = expand_buttons.count()

            if expand_count > 0:
                print(f"✅ Found {expand_count} expand buttons")

                # Click first expand button
                page.screenshot(path='/tmp/enhanced-before-expand.png', full_page=True)
                expand_buttons.first.click()
                page.wait_for_timeout(500)
                page.screenshot(path='/tmp/enhanced-after-expand.png', full_page=True)
                print("✅ Expand/collapse tested")
                print("📸 /tmp/enhanced-before-expand.png")
                print("📸 /tmp/enhanced-after-expand.png")
            else:
                print("ℹ️  No expand buttons found")

            # Test filter functionality
            filter_buttons = page.locator('button:has-text("已驗證")')
            if filter_buttons.count() > 0:
                print("✅ Found filter buttons")
                filter_buttons.first.click()
                page.wait_for_timeout(500)
                page.screenshot(path='/tmp/enhanced-filtered.png', full_page=True)
                print("✅ Filter tested")
                print("📸 /tmp/enhanced-filtered.png")
            else:
                print("ℹ️  No filter buttons found")

            # Responsive testing
            print("\n📍 Testing responsive design...")

            viewports = [
                ("Mobile", 375, 812),
                ("Tablet", 768, 1024),
                ("Desktop", 1920, 1080),
            ]

            for name, width, height in viewports:
                page.set_viewport_size({"width": width, "height": height})
                page.wait_for_timeout(500)
                filename = f'/tmp/enhanced-{name.lower()}.png'
                page.screenshot(path=filename, full_page=True)
                print(f"✅ {name} ({width}x{height}) tested")
                print(f"📸 {filename}")

            # Get HTML content for inspection
            print("\n📍 Saving HTML content...")
            content = page.content()
            with open('/tmp/salesperson-page-content.html', 'w', encoding='utf-8') as f:
                f.write(content)
            print("✅ HTML saved to /tmp/salesperson-page-content.html")

            # Check for console errors
            print("\n📍 Console error check...")
            errors = []

            def handle_console(msg):
                if msg.type == "error":
                    errors.append(msg.text)

            page.on("console", handle_console)
            page.reload()
            page.wait_for_load_state('networkidle')
            page.wait_for_timeout(2000)

            if errors:
                print(f"⚠️  Found {len(errors)} console errors:")
                for error in errors[:5]:  # Show first 5 errors
                    print(f"   - {error}")
            else:
                print("✅ No console errors")

            print("\n" + "="*60)
            print("🎉 Enhanced tests completed!")
            print("="*60)

        except Exception as e:
            print(f"\n❌ Error: {str(e)}")
            import traceback
            traceback.print_exc()
            page.screenshot(path='/tmp/enhanced-error.png', full_page=True)
            print("📸 /tmp/enhanced-error.png")
            browser.close()
            sys.exit(1)

        browser.close()

if __name__ == "__main__":
    test_enhanced_salesperson()
