'use client';

import Link from 'next/link';
import { useState } from 'react';
import { Menu, X, User, LogOut, Settings, LayoutDashboard } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Avatar } from '@/components/ui/avatar';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';

interface HeaderProps {
  user?: {
    id: number;
    username?: string;
    name?: string;
    email?: string;
    role: 'admin' | 'salesperson' | 'user';
    full_name?: string;
    avatar?: string | null;
  } | null;
  onLogout?: () => void;
}

export function Header({ user, onLogout }: HeaderProps) {
  const [mobileMenuOpen, setMobileMenuOpen] = useState(false);

  const publicLinks = [
    { href: '/', label: '首頁' },
    { href: '/search', label: '搜尋業務員' },
  ];

  const salespersonLinks = [
    { href: '/dashboard', label: '個人中心', icon: LayoutDashboard },
    { href: '/dashboard/profile', label: '個人資料', icon: User },
  ];

  const adminLinks = [
    { href: '/admin', label: '管理後台', icon: LayoutDashboard },
    { href: '/admin/approvals', label: '審核管理' },
    { href: '/admin/users', label: '使用者管理' },
    { href: '/admin/statistics', label: '統計報表' },
  ];

  const getDashboardLinks = () => {
    if (user?.role === 'admin') return adminLinks;
    if (user?.role === 'salesperson') return salespersonLinks;
    return [];
  };

  return (
    <header className="sticky top-0 z-40 w-full border-b border-slate-200 bg-white/95 backdrop-blur supports-[backdrop-filter]:bg-white/80">
      <div className="container mx-auto px-4 sm:px-6 lg:px-8">
        <div className="flex h-16 items-center justify-between">
          {/* Logo */}
          <div className="flex items-center">
            <Link href="/" className="flex items-center space-x-2">
              <div className="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500">
                <span className="text-xl font-bold text-white">Y</span>
              </div>
              <span className="text-2xl font-bold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
                YAMU
              </span>
            </Link>
          </div>

          {/* Desktop Navigation */}
          <nav className="hidden md:flex items-center space-x-6">
            {publicLinks.map((link) => (
              <Link
                key={link.href}
                href={link.href}
                className="text-sm font-medium text-slate-700 hover:text-primary-600 transition-colors"
              >
                {link.label}
              </Link>
            ))}
          </nav>

          {/* Right Side Actions */}
          <div className="flex items-center space-x-4">
            {user ? (
              <>
                {/* User Menu */}
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <button className="flex items-center space-x-3 hover:opacity-80 transition-opacity">
                      <Avatar
                        src={user.avatar}
                        fallback={
                          user.full_name?.substring(0, 2) ||
                          user.name?.substring(0, 2).toUpperCase() ||
                          user.username?.substring(0, 2).toUpperCase() ||
                          user.email?.substring(0, 2).toUpperCase() ||
                          'U'
                        }
                        size="sm"
                      />
                      <div className="hidden md:block text-left">
                        <p className="text-sm font-medium text-slate-900">
                          {user.full_name || user.name || user.username || user.email || '使用者'}
                        </p>
                        <p className="text-xs text-slate-500">
                          {user.role === 'admin' ? '管理員' : user.role === 'salesperson' ? '業務員' : '使用者'}
                        </p>
                      </div>
                    </button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end" className="w-56">
                    <DropdownMenuLabel>我的帳號</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    {getDashboardLinks().map((link) => (
                      <DropdownMenuItem key={link.href} asChild>
                        <Link href={link.href} className="cursor-pointer">
                          {link.icon && <link.icon className="mr-2 h-4 w-4" />}
                          {link.label}
                        </Link>
                      </DropdownMenuItem>
                    ))}

                    {/* 設定選項 - 僅管理員顯示 */}
                    {user.role === 'admin' && (
                      <>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                          <Link href="/admin/settings" className="cursor-pointer">
                            <Settings className="mr-2 h-4 w-4" />
                            設定
                          </Link>
                        </DropdownMenuItem>
                      </>
                    )}

                    <DropdownMenuSeparator />
                    <DropdownMenuItem
                      onClick={onLogout}
                      className="cursor-pointer text-error-600"
                    >
                      <LogOut className="mr-2 h-4 w-4" />
                      登出
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </>
            ) : (
              <>
                {/* Login/Register Buttons */}
                <Button variant="ghost" asChild className="hidden md:inline-flex">
                  <Link href="/login">登入</Link>
                </Button>
                <Button asChild>
                  <Link href="/register">註冊</Link>
                </Button>
              </>
            )}

            {/* Mobile Menu Button */}
            <button
              type="button"
              className="md:hidden inline-flex items-center justify-center rounded-lg p-2 text-slate-700 hover:bg-slate-100"
              onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
            >
              <span className="sr-only">開啟選單</span>
              {mobileMenuOpen ? (
                <X className="h-6 w-6" />
              ) : (
                <Menu className="h-6 w-6" />
              )}
            </button>
          </div>
        </div>

        {/* Mobile Navigation Menu */}
        {mobileMenuOpen && (
          <div className="md:hidden py-4 space-y-2 border-t border-slate-200">
            {publicLinks.map((link) => (
              <Link
                key={link.href}
                href={link.href}
                className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
                onClick={() => setMobileMenuOpen(false)}
              >
                {link.label}
              </Link>
            ))}

            {user && getDashboardLinks().map((link) => (
              <Link
                key={link.href}
                href={link.href}
                className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
                onClick={() => setMobileMenuOpen(false)}
              >
                {link.label}
              </Link>
            ))}

            {!user && (
              <div className="pt-4 space-y-2 border-t border-slate-200">
                <Link
                  href="/login"
                  className="block px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100 rounded-lg"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  登入
                </Link>
                <Link
                  href="/register"
                  className="block px-4 py-2 text-sm font-medium text-white bg-primary-600 hover:bg-primary-700 rounded-lg text-center"
                  onClick={() => setMobileMenuOpen(false)}
                >
                  註冊
                </Link>
              </div>
            )}

            {user && (
              <button
                onClick={() => {
                  onLogout?.();
                  setMobileMenuOpen(false);
                }}
                className="w-full text-left px-4 py-2 text-sm font-medium text-error-600 hover:bg-error-50 rounded-lg"
              >
                登出
              </button>
            )}
          </div>
        )}
      </div>
    </header>
  );
}
