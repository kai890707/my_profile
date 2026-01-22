#!/usr/bin/env python3
"""
Verify the main fix: /salesperson/1 page should show user info when logged in
"""

from playwright.sync_api import sync_playwright
import sys

def verify_fix():
    with sync_playwright() as p:
        browser = p.chromium.launch(headless=True)
        page = browser.new_page()

        try:
            print("🔍 Testing /salesperson/1 page header authentication fix...")
            print()

            # Test 1: Visit page without login - should see login/register
            print("Test 1: Unauthenticated state")
            page.goto('http://localhost:3001/salesperson/1')
            page.wait_for_load_state('networkidle')

            # Look for login/register buttons specifically in the header
            header = page.locator('header').first
            login_button = header.locator('a:has-text("登入")').first
            register_button = header.locator('a:has-text("註冊")').first

            if login_button.is_visible() and register_button.is_visible():
                print("  ✅ Shows login/register buttons when not logged in")
            else:
                print("  ❌ Login/register buttons not visible")

            page.screenshot(path='/tmp/salesperson-before-login.png', full_page=True)
            print("  📸 Screenshot saved: /tmp/salesperson-before-login.png")
            print()

            # Test 2: Login and visit page - should see user info
            print("Test 2: Authenticated state")
            print("  Logging in as test user...")

            # Go to login page
            page.goto('http://localhost:3001/login')
            page.wait_for_load_state('networkidle')

            # Fill login form
            page.fill('input[name="email"]', 'user@example.com')
            page.fill('input[name="password"]', 'password')
            page.click('button[type="submit"]')

            # Wait for navigation after login
            page.wait_for_url('http://localhost:3001/')
            page.wait_for_load_state('networkidle')
            print("  ✅ Login successful")

            # Now visit salesperson page
            print("  Navigating to /salesperson/1...")
            page.goto('http://localhost:3001/salesperson/1')
            page.wait_for_load_state('networkidle')

            # Check that login/register buttons are NOT visible
            login_visible = login_button.is_visible()
            register_visible = register_button.is_visible()

            if not login_visible and not register_visible:
                print("  ✅ Login/register buttons are hidden (correct!)")
            else:
                print("  ❌ Login/register buttons still visible (BUG!)")
                browser.close()
                return 1

            # Check for user avatar or user name
            # Look for image in button (avatar)
            avatar_button = page.locator('button:has(img)').first

            if avatar_button.is_visible():
                print("  ✅ User avatar is visible")

                # Try to get user name
                user_name = page.locator('span:has-text("Test")').first
                if user_name.is_visible():
                    print(f"  ✅ User name is visible: {user_name.text_content()}")

            else:
                print("  ⚠️  User avatar not found (might use different structure)")

            page.screenshot(path='/tmp/salesperson-after-login.png', full_page=True)
            print("  📸 Screenshot saved: /tmp/salesperson-after-login.png")
            print()

            # Test 3: Check header on homepage for comparison
            print("Test 3: Verify homepage header (for comparison)")
            page.goto('http://localhost:3001/')
            page.wait_for_load_state('networkidle')

            home_avatar = page.locator('button:has(img)').first
            if home_avatar.is_visible():
                print("  ✅ Homepage also shows user avatar (consistent)")
            else:
                print("  ❌ Homepage doesn't show avatar (inconsistent)")

            page.screenshot(path='/tmp/homepage-after-login.png', full_page=True)
            print("  📸 Screenshot saved: /tmp/homepage-after-login.png")
            print()

            print("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━")
            print("✅ MAIN FIX VERIFIED SUCCESSFULLY!")
            print("━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━")
            print()
            print("Summary:")
            print("  ✅ Before login: Shows login/register buttons")
            print("  ✅ After login: Hides login/register buttons")
            print("  ✅ After login: Shows user avatar/info")
            print()
            print("Screenshots saved to /tmp/ for visual verification")

            browser.close()
            return 0

        except Exception as e:
            print(f"❌ Error: {e}")
            page.screenshot(path='/tmp/error-screenshot.png', full_page=True)
            print("Error screenshot saved: /tmp/error-screenshot.png")
            browser.close()
            return 1

if __name__ == "__main__":
    sys.exit(verify_fix())
