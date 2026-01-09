'use client';

import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarGroup } from '@/components/ui/avatar';
import { Skeleton, SalespersonCardSkeleton, ProfileSkeleton } from '@/components/ui/skeleton';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuLabel,
  DropdownMenuSeparator,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Heart, Star, Zap, Settings, User, LogOut } from 'lucide-react';

export default function UIDemoPage() {
  return (
    <div className="min-h-screen bg-gradient-to-br from-slate-50 via-primary-50 to-secondary-50 p-8">
      <div className="max-w-7xl mx-auto space-y-12">
        {/* Header */}
        <div className="text-center space-y-4">
          <h1 className="text-5xl font-bold bg-gradient-to-r from-primary-600 to-secondary-600 bg-clip-text text-transparent">
            YAMU Design System
          </h1>
          <p className="text-xl text-slate-600">活潑親和的 UI 組件展示</p>
        </div>

        {/* 色彩系統 */}
        <Card>
          <CardHeader>
            <CardTitle>色彩系統</CardTitle>
            <CardDescription>完整的色彩定義，支援多種狀態與情境</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid grid-cols-2 md:grid-cols-4 gap-6">
              {/* Primary */}
              <div>
                <p className="text-sm font-semibold text-slate-700 mb-3">Primary (Sky Blue)</p>
                <div className="space-y-2">
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-primary-50 border border-slate-200" />
                    <span className="text-xs text-slate-600">50</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-primary-100 border border-slate-200" />
                    <span className="text-xs text-slate-600">100</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-primary-200 border border-slate-200" />
                    <span className="text-xs text-slate-600">200</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-primary-300 border border-slate-200" />
                    <span className="text-xs text-slate-600">300</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-primary-400 border border-slate-200" />
                    <span className="text-xs text-slate-600">400</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-primary-500 border border-slate-200" />
                    <span className="text-xs text-slate-600 font-semibold">500 ⭐</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-primary-600 border border-slate-200" />
                    <span className="text-xs text-slate-600">600</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-primary-700 border border-slate-200" />
                    <span className="text-xs text-slate-600">700</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-primary-800 border border-slate-200" />
                    <span className="text-xs text-slate-600">800</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-primary-900 border border-slate-200" />
                    <span className="text-xs text-slate-600">900</span>
                  </div>
                </div>
              </div>

              {/* Secondary */}
              <div>
                <p className="text-sm font-semibold text-slate-700 mb-3">Secondary (Teal)</p>
                <div className="space-y-2">
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-secondary-50 border border-slate-200" />
                    <span className="text-xs text-slate-600">50</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-secondary-100 border border-slate-200" />
                    <span className="text-xs text-slate-600">100</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-secondary-200 border border-slate-200" />
                    <span className="text-xs text-slate-600">200</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-secondary-300 border border-slate-200" />
                    <span className="text-xs text-slate-600">300</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-secondary-400 border border-slate-200" />
                    <span className="text-xs text-slate-600">400</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-secondary-500 border border-slate-200" />
                    <span className="text-xs text-slate-600 font-semibold">500 ⭐</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-secondary-600 border border-slate-200" />
                    <span className="text-xs text-slate-600">600</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-secondary-700 border border-slate-200" />
                    <span className="text-xs text-slate-600">700</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-secondary-800 border border-slate-200" />
                    <span className="text-xs text-slate-600">800</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-secondary-900 border border-slate-200" />
                    <span className="text-xs text-slate-600">900</span>
                  </div>
                </div>
              </div>

              {/* Success */}
              <div>
                <p className="text-sm font-semibold text-slate-700 mb-3">Success (Green)</p>
                <div className="space-y-2">
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-success-50 border border-slate-200" />
                    <span className="text-xs text-slate-600">50</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-success-100 border border-slate-200" />
                    <span className="text-xs text-slate-600">100</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-success-500 border border-slate-200" />
                    <span className="text-xs text-slate-600 font-semibold">500 ⭐</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-success-600 border border-slate-200" />
                    <span className="text-xs text-slate-600">600</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="w-12 h-8 rounded-lg bg-success-700 border border-slate-200" />
                    <span className="text-xs text-slate-600">700</span>
                  </div>
                </div>
              </div>

              {/* Warning & Error */}
              <div className="space-y-6">
                <div>
                  <p className="text-sm font-semibold text-slate-700 mb-3">Warning (Amber)</p>
                  <div className="space-y-2">
                    <div className="flex items-center gap-2">
                      <div className="w-12 h-8 rounded-lg bg-warning-50 border border-slate-200" />
                      <span className="text-xs text-slate-600">50</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-12 h-8 rounded-lg bg-warning-500 border border-slate-200" />
                      <span className="text-xs text-slate-600 font-semibold">500 ⭐</span>
                    </div>
                  </div>
                </div>
                <div>
                  <p className="text-sm font-semibold text-slate-700 mb-3">Error (Red)</p>
                  <div className="space-y-2">
                    <div className="flex items-center gap-2">
                      <div className="w-12 h-8 rounded-lg bg-error-50 border border-slate-200" />
                      <span className="text-xs text-slate-600">50</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-12 h-8 rounded-lg bg-error-500 border border-slate-200" />
                      <span className="text-xs text-slate-600 font-semibold">500 ⭐</span>
                    </div>
                  </div>
                </div>
                <div>
                  <p className="text-sm font-semibold text-slate-700 mb-3">Info (Blue)</p>
                  <div className="space-y-2">
                    <div className="flex items-center gap-2">
                      <div className="w-12 h-8 rounded-lg bg-info-50 border border-slate-200" />
                      <span className="text-xs text-slate-600">50</span>
                    </div>
                    <div className="flex items-center gap-2">
                      <div className="w-12 h-8 rounded-lg bg-info-500 border border-slate-200" />
                      <span className="text-xs text-slate-600 font-semibold">500 ⭐</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* 按鈕組件 */}
        <Card>
          <CardHeader>
            <CardTitle>按鈕 (Button)</CardTitle>
            <CardDescription>多種變體與尺寸的按鈕組件</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              {/* 變體 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">變體</h4>
                <div className="flex flex-wrap gap-3">
                  <Button>Default</Button>
                  <Button variant="secondary">Secondary</Button>
                  <Button variant="outline">Outline</Button>
                  <Button variant="ghost">Ghost</Button>
                  <Button variant="link">Link</Button>
                </div>
              </div>

              {/* 尺寸 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">尺寸</h4>
                <div className="flex flex-wrap items-center gap-3">
                  <Button size="sm">Small</Button>
                  <Button size="default">Default</Button>
                  <Button size="lg">Large</Button>
                </div>
              </div>

              {/* 載入狀態 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">狀態</h4>
                <div className="flex flex-wrap gap-3">
                  <Button isLoading>載入中</Button>
                  <Button disabled>Disabled</Button>
                </div>
              </div>

              {/* 帶圖示 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">帶圖示</h4>
                <div className="flex flex-wrap gap-3">
                  <Button>
                    <Heart className="mr-2 h-4 w-4" />
                    喜歡
                  </Button>
                  <Button>
                    <Star className="mr-2 h-4 w-4" />
                    收藏
                  </Button>
                  <Button>
                    <Zap className="mr-2 h-4 w-4" />
                    立即行動
                  </Button>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* 輸入框組件 */}
        <Card>
          <CardHeader>
            <CardTitle>輸入框 (Input)</CardTitle>
            <CardDescription>表單輸入組件</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-4 max-w-md">
              <Input
                label="電子郵件"
                type="email"
                placeholder="your@email.com"
                required
              />
              <Input
                label="密碼"
                type="password"
                placeholder="請輸入密碼"
                helperText="至少 8 個字元"
              />
              <Input
                label="錯誤範例"
                type="text"
                error="此欄位為必填"
                placeholder="這是錯誤狀態"
              />
              <Input
                label="禁用狀態"
                type="text"
                disabled
                placeholder="無法輸入"
              />
            </div>
          </CardContent>
        </Card>

        {/* 卡片組件 */}
        <div>
          <h2 className="text-3xl font-bold text-slate-900 mb-6">卡片 (Card)</h2>
          <div className="grid md:grid-cols-3 gap-6">
            <Card hover>
              <CardHeader>
                <CardTitle>基礎卡片</CardTitle>
                <CardDescription>這是一張可以 hover 的卡片</CardDescription>
              </CardHeader>
              <CardContent>
                <p className="text-slate-600">
                  卡片內容可以放置任何元素，包括文字、圖片、按鈕等。
                </p>
              </CardContent>
              <CardFooter>
                <Button className="w-full">查看詳情</Button>
              </CardFooter>
            </Card>

            <Card shadow="lg">
              <CardHeader>
                <CardTitle>大陰影卡片</CardTitle>
                <CardDescription>使用較大的陰影效果</CardDescription>
              </CardHeader>
              <CardContent>
                <div className="flex items-center gap-3">
                  <Avatar fallback="YM" size="lg" status="online" />
                  <div>
                    <p className="font-semibold text-slate-900">業務員 A</p>
                    <p className="text-sm text-slate-600">專業顧問</p>
                  </div>
                </div>
              </CardContent>
            </Card>

            <Card padding="lg">
              <CardHeader>
                <CardTitle>大間距卡片</CardTitle>
              </CardHeader>
              <CardContent>
                <div className="flex flex-wrap gap-2">
                  <Badge variant="primary">標籤 1</Badge>
                  <Badge variant="success">標籤 2</Badge>
                  <Badge variant="warning">標籤 3</Badge>
                </div>
              </CardContent>
            </Card>
          </div>
        </div>

        {/* 徽章組件 */}
        <Card>
          <CardHeader>
            <CardTitle>徽章 (Badge)</CardTitle>
            <CardDescription>狀態指示器與標籤</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              {/* 變體 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">變體</h4>
                <div className="flex flex-wrap gap-2">
                  <Badge variant="default">Default</Badge>
                  <Badge variant="primary">Primary</Badge>
                  <Badge variant="secondary">Secondary</Badge>
                  <Badge variant="success">Success</Badge>
                  <Badge variant="warning">Warning</Badge>
                  <Badge variant="error">Error</Badge>
                  <Badge variant="info">Info</Badge>
                </div>
              </div>

              {/* 尺寸 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">尺寸</h4>
                <div className="flex flex-wrap items-center gap-2">
                  <Badge size="sm">Small</Badge>
                  <Badge size="md">Medium</Badge>
                  <Badge size="lg">Large</Badge>
                </div>
              </div>

              {/* 帶點 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">狀態指示點</h4>
                <div className="flex flex-wrap gap-2">
                  <Badge variant="success" dot>已審核</Badge>
                  <Badge variant="warning" dot>待審核</Badge>
                  <Badge variant="error" dot>已拒絕</Badge>
                  <Badge variant="info" dot>處理中</Badge>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* 頭像組件 */}
        <Card>
          <CardHeader>
            <CardTitle>頭像 (Avatar)</CardTitle>
            <CardDescription>用戶頭像組件，支援圖片與縮寫</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-6">
              {/* 尺寸 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">尺寸</h4>
                <div className="flex flex-wrap items-end gap-4">
                  <Avatar size="xs" fallback="XS" />
                  <Avatar size="sm" fallback="SM" />
                  <Avatar size="md" fallback="MD" />
                  <Avatar size="lg" fallback="LG" />
                  <Avatar size="xl" fallback="XL" />
                  <Avatar size="2xl" fallback="2XL" />
                </div>
              </div>

              {/* 狀態 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">狀態指示</h4>
                <div className="flex flex-wrap gap-4">
                  <div className="text-center">
                    <Avatar fallback="在" status="online" size="lg" />
                    <p className="text-xs text-slate-600 mt-2">在線</p>
                  </div>
                  <div className="text-center">
                    <Avatar fallback="離" status="offline" size="lg" />
                    <p className="text-xs text-slate-600 mt-2">離線</p>
                  </div>
                  <div className="text-center">
                    <Avatar fallback="暫" status="away" size="lg" />
                    <p className="text-xs text-slate-600 mt-2">暫離</p>
                  </div>
                  <div className="text-center">
                    <Avatar fallback="忙" status="busy" size="lg" />
                    <p className="text-xs text-slate-600 mt-2">忙碌</p>
                  </div>
                </div>
              </div>

              {/* 頭像群組 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">頭像群組</h4>
                <AvatarGroup max={4}>
                  <Avatar fallback="A" />
                  <Avatar fallback="B" />
                  <Avatar fallback="C" />
                  <Avatar fallback="D" />
                  <Avatar fallback="E" />
                  <Avatar fallback="F" />
                </AvatarGroup>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* 動畫展示 */}
        <Card>
          <CardHeader>
            <CardTitle>動畫效果</CardTitle>
            <CardDescription>內建的過渡與動畫（懸停查看效果）</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="grid md:grid-cols-4 gap-4">
              <div className="p-6 bg-primary-50 rounded-xl text-center cursor-pointer hover-animate-fade-in">
                <p className="text-sm font-semibold text-primary-700">淡入</p>
                <p className="text-xs text-slate-500 mt-2">懸停觸發</p>
              </div>
              <div className="p-6 bg-secondary-50 rounded-xl text-center cursor-pointer hover-animate-slide-in-bottom">
                <p className="text-sm font-semibold text-secondary-700">滑入</p>
                <p className="text-xs text-slate-500 mt-2">懸停觸發</p>
              </div>
              <div className="p-6 bg-success-50 rounded-xl text-center cursor-pointer hover-animate-scale-in">
                <p className="text-sm font-semibold text-success-700">縮放</p>
                <p className="text-xs text-slate-500 mt-2">懸停觸發</p>
              </div>
              <div className="p-6 bg-warning-50 rounded-xl text-center cursor-pointer hover-animate-bounce-in">
                <p className="text-sm font-semibold text-warning-700">彈跳</p>
                <p className="text-xs text-slate-500 mt-2">懸停觸發</p>
              </div>
            </div>
            <div className="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
              <p className="text-sm text-blue-800">
                💡 提示：將滑鼠懸停在任一方塊上可查看動畫效果
              </p>
            </div>
          </CardContent>
        </Card>

        {/* Skeleton 載入組件 */}
        <Card>
          <CardHeader>
            <CardTitle>Skeleton (載入佔位)</CardTitle>
            <CardDescription>數據載入時的佔位效果</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-8">
              {/* 基礎 Skeleton */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">基礎形狀</h4>
                <div className="space-y-4">
                  <div>
                    <p className="text-xs text-slate-500 mb-2">文字行</p>
                    <Skeleton variant="text" />
                  </div>
                  <div>
                    <p className="text-xs text-slate-500 mb-2">圓形</p>
                    <Skeleton variant="circular" className="h-12 w-12" />
                  </div>
                  <div>
                    <p className="text-xs text-slate-500 mb-2">矩形</p>
                    <Skeleton variant="rectangular" className="h-32 w-full" />
                  </div>
                </div>
              </div>

              {/* 組合範例 */}
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">組合範例</h4>
                <div className="grid md:grid-cols-2 gap-4">
                  <div>
                    <p className="text-xs text-slate-500 mb-2">業務員卡片載入</p>
                    <SalespersonCardSkeleton />
                  </div>
                  <div>
                    <p className="text-xs text-slate-500 mb-2">個人資料載入</p>
                    <div className="p-6 bg-white rounded-xl border border-slate-200">
                      <ProfileSkeleton />
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Select 選擇器組件 */}
        <Card>
          <CardHeader>
            <CardTitle>Select (選擇器)</CardTitle>
            <CardDescription>下拉式選擇器組件</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="space-y-6 max-w-md">
              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">產業類別</h4>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="選擇產業類別" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="tech">科技業</SelectItem>
                    <SelectItem value="finance">金融業</SelectItem>
                    <SelectItem value="manufacturing">製造業</SelectItem>
                    <SelectItem value="service">服務業</SelectItem>
                    <SelectItem value="retail">零售業</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">服務地區</h4>
                <Select>
                  <SelectTrigger>
                    <SelectValue placeholder="選擇服務地區" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="north">北部</SelectItem>
                    <SelectItem value="central">中部</SelectItem>
                    <SelectItem value="south">南部</SelectItem>
                    <SelectItem value="east">東部</SelectItem>
                  </SelectContent>
                </Select>
              </div>

              <div>
                <h4 className="text-sm font-semibold text-slate-700 mb-3">排序方式</h4>
                <Select defaultValue="latest">
                  <SelectTrigger>
                    <SelectValue placeholder="選擇排序方式" />
                  </SelectTrigger>
                  <SelectContent>
                    <SelectItem value="latest">最新註冊</SelectItem>
                    <SelectItem value="popular">最受歡迎</SelectItem>
                    <SelectItem value="rating">評分最高</SelectItem>
                  </SelectContent>
                </Select>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Dropdown Menu 下拉選單 */}
        <Card>
          <CardHeader>
            <CardTitle>Dropdown Menu (下拉選單)</CardTitle>
            <CardDescription>互動式下拉選單組件</CardDescription>
          </CardHeader>
          <CardContent>
            <div className="flex flex-wrap gap-4">
              {/* 基礎選單 */}
              <div>
                <p className="text-sm text-slate-600 mb-2">使用者選單</p>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button variant="outline">
                      <User className="mr-2 h-4 w-4" />
                      我的帳號
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="start" className="w-56">
                    <DropdownMenuLabel>我的帳號</DropdownMenuLabel>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem>
                      <User className="mr-2 h-4 w-4" />
                      個人資料
                    </DropdownMenuItem>
                    <DropdownMenuItem>
                      <Settings className="mr-2 h-4 w-4" />
                      設定
                    </DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem className="text-error-600">
                      <LogOut className="mr-2 h-4 w-4" />
                      登出
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>

              {/* 操作選單 */}
              <div>
                <p className="text-sm text-slate-600 mb-2">操作選單</p>
                <DropdownMenu>
                  <DropdownMenuTrigger asChild>
                    <Button>
                      更多操作
                    </Button>
                  </DropdownMenuTrigger>
                  <DropdownMenuContent align="end">
                    <DropdownMenuItem>編輯</DropdownMenuItem>
                    <DropdownMenuItem>複製</DropdownMenuItem>
                    <DropdownMenuItem>分享</DropdownMenuItem>
                    <DropdownMenuSeparator />
                    <DropdownMenuItem className="text-error-600">
                      刪除
                    </DropdownMenuItem>
                  </DropdownMenuContent>
                </DropdownMenu>
              </div>
            </div>
          </CardContent>
        </Card>

        {/* Footer */}
        <div className="text-center py-8 border-t border-slate-200">
          <p className="text-slate-600">
            YAMU Design System v1.0.0
          </p>
          <p className="text-sm text-slate-500 mt-2">
            活潑親和的業務員搜尋平台 UI
          </p>
        </div>
      </div>
    </div>
  );
}
