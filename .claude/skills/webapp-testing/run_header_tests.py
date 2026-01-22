#!/usr/bin/env python3
"""
Run header authentication E2E tests with both servers running
"""

import subprocess
import sys

def run_tests():
    """Run Playwright tests"""
    print("Running header authentication E2E tests...")

    # Change to frontend directory
    frontend_dir = "/Users/kai/KAA/my_profile/frontend"

    try:
        # Run Playwright tests
        result = subprocess.run(
            ["npx", "playwright", "test", "tests/e2e/header-auth.spec.ts", "--reporter=list"],
            cwd=frontend_dir,
            capture_output=True,
            text=True,
            timeout=300  # 5 minutes timeout
        )

        print(result.stdout)
        if result.stderr:
            print(result.stderr, file=sys.stderr)

        if result.returncode == 0:
            print("\n✅ All tests passed!")
            return 0
        else:
            print("\n❌ Some tests failed")
            return result.returncode

    except subprocess.TimeoutExpired:
        print("❌ Tests timed out after 5 minutes")
        return 1
    except Exception as e:
        print(f"❌ Error running tests: {e}")
        return 1

if __name__ == "__main__":
    sys.exit(run_tests())
