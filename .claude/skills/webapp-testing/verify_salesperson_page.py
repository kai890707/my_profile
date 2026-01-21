#!/usr/bin/env python3
"""
Verify that work experience and certifications are displayed on salesperson detail page
"""

from playwright.sync_api import sync_playwright
import sys

def verify_salesperson_page():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        try:
            print("Navigating to http://localhost:3001/salesperson/1...")
            page.goto('http://localhost:3001/salesperson/1')

            # Wait for page to fully load
            page.wait_for_load_state('networkidle')
            print("Page loaded successfully")

            # Take initial screenshot
            page.screenshot(path='/tmp/salesperson_page.png', full_page=True)
            print("Screenshot saved to /tmp/salesperson_page.png")

            # Check for work experience section
            print("\nChecking for work experience section...")
            experience_section = page.locator('text=工作經驗').first
            if experience_section.is_visible():
                print("✅ Work experience section found")

                # Check for experience items
                experience_items = page.locator('[role="listitem"]').all()
                experience_count = len([item for item in experience_items if '公司' in item.text_content() or 'Company' in item.text_content()])
                print(f"✅ Found {experience_count} experience items")

                # Check for empty state
                empty_state = page.locator('text=尚無工作經驗記錄')
                if empty_state.is_visible():
                    print("⚠️ Empty state displayed for work experience")
                else:
                    print("✅ Work experience data is displayed (not showing empty state)")
            else:
                print("❌ Work experience section not found")

            # Check for certifications section
            print("\nChecking for certifications section...")
            cert_section = page.locator('text=專業證照').first
            if cert_section.is_visible():
                print("✅ Certifications section found")

                # Check for certification cards
                cert_cards = page.locator('[role="list"]').locator('[role="listitem"]').all()
                cert_count = len([card for card in cert_cards if '發證單位' in card.text_content() or 'Issuer' in card.text_content()])
                print(f"✅ Found {cert_count} certification cards")

                # Check for empty state
                empty_state = page.locator('text=尚無專業證照記錄')
                if empty_state.is_visible():
                    print("⚠️ Empty state displayed for certifications")
                else:
                    print("✅ Certification data is displayed (not showing empty state)")
            else:
                print("❌ Certifications section not found")

            # Get console logs
            print("\nConsole logs:")
            console_messages = []
            page.on("console", lambda msg: console_messages.append(f"{msg.type}: {msg.text}"))

            # Reload to capture console logs
            page.reload()
            page.wait_for_load_state('networkidle')

            for msg in console_messages[-10:]:  # Show last 10 messages
                print(f"  {msg}")

            print("\n✅ Verification complete")

        except Exception as e:
            print(f"❌ Error: {e}")
            page.screenshot(path='/tmp/error_screenshot.png', full_page=True)
            print("Error screenshot saved to /tmp/error_screenshot.png")
            browser.close()
            sys.exit(1)

        browser.close()

if __name__ == "__main__":
    verify_salesperson_page()
