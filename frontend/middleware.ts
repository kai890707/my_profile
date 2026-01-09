import { NextResponse } from 'next/server';
import type { NextRequest } from 'next/server';

// 需要認證的路徑
const protectedRoutes = ['/dashboard', '/admin'];

// 只有 Admin 可以訪問的路徑
const adminOnlyRoutes = ['/admin'];

// 只有 Salesperson 可以訪問的路徑
const salespersonOnlyRoutes = ['/dashboard'];

export function middleware(request: NextRequest) {
  const { pathname } = request.nextUrl;
  const accessToken = request.cookies.get('access_token')?.value;
  const userRole = request.cookies.get('user_role')?.value;

  // 檢查是否為受保護的路徑
  const isProtectedRoute = protectedRoutes.some((route) => pathname.startsWith(route));

  if (isProtectedRoute) {
    // 未登入，導向登入頁
    if (!accessToken) {
      const loginUrl = new URL('/login', request.url);
      // 保存原始 URL，登入後返回
      loginUrl.searchParams.set('callbackUrl', pathname);
      return NextResponse.redirect(loginUrl);
    }

    // 已登入，檢查角色權限
    if (userRole) {
      // Admin 嘗試訪問 Dashboard
      if (adminOnlyRoutes.some((route) => pathname.startsWith(route)) && userRole !== 'admin') {
        return NextResponse.redirect(new URL('/403', request.url));
      }

      // Salesperson 嘗試訪問 Admin
      if (salespersonOnlyRoutes.some((route) => pathname.startsWith(route)) && userRole !== 'salesperson') {
        return NextResponse.redirect(new URL('/403', request.url));
      }
    }
  }

  // 已登入用戶訪問 /login 或 /register，導向對應頁面
  if (accessToken && (pathname === '/login' || pathname === '/register')) {
    if (userRole === 'admin') {
      return NextResponse.redirect(new URL('/admin', request.url));
    } else if (userRole === 'salesperson') {
      return NextResponse.redirect(new URL('/dashboard', request.url));
    }
  }

  return NextResponse.next();
}

export const config = {
  matcher: [
    /*
     * Match all request paths except for the ones starting with:
     * - api (API routes)
     * - _next/static (static files)
     * - _next/image (image optimization files)
     * - favicon.ico (favicon file)
     * - public folder
     */
    '/((?!api|_next/static|_next/image|favicon.ico|images|.*\\..*|$).*)',
  ],
};
