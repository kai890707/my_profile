'use client';

import { useState } from 'react';
import { useRouter, useSearchParams } from 'next/navigation';
import { SearchFilters } from '@/components/features/search/search-filters';
import { SalespersonCard } from '@/components/features/search/salesperson-card';
import { SalespersonCardSkeleton } from '@/components/ui/skeleton';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { ChevronLeft, ChevronRight, Search } from 'lucide-react';
import { useSearchSalespersons } from '@/hooks/useSearch';
import { SearchParams } from '@/types/api';

export function SearchContent() {
  const router = useRouter();
  const searchParams = useSearchParams();

  // 從 URL 讀取搜尋參數
  const [filters, setFilters] = useState<SearchParams>({
    keyword: searchParams.get('keyword') || undefined,
    company: searchParams.get('company') || undefined,
    industry_id: searchParams.get('industry_id')
      ? parseInt(searchParams.get('industry_id')!)
      : undefined,
    region_id: searchParams.get('region_id')
      ? parseInt(searchParams.get('region_id')!)
      : undefined,
    page: searchParams.get('page') ? parseInt(searchParams.get('page')!) : 1,
    per_page: 20,
    sort: (searchParams.get('sort') as 'latest' | 'popular' | 'relevant') || 'latest',
  });

  // 搜尋業務員
  const { data, isLoading, error } = useSearchSalespersons(filters);

  // 更新 URL 查詢參數
  const updateURL = (newFilters: SearchParams) => {
    const params = new URLSearchParams();
    Object.entries(newFilters).forEach(([key, value]) => {
      if (value !== undefined && value !== null && value !== '') {
        params.set(key, value.toString());
      }
    });
    const queryString = params.toString();
    router.push(queryString ? `/search?${queryString}` : '/search', { scroll: false });
  };

  // 處理搜尋
  const handleSearch = (newFilters: SearchParams) => {
    const updatedFilters = { ...newFilters, page: 1 };
    setFilters(updatedFilters);
    updateURL(updatedFilters);
  };

  // 處理分頁
  const handlePageChange = (page: number) => {
    const updatedFilters = { ...filters, page };
    setFilters(updatedFilters);
    updateURL(updatedFilters);
    window.scrollTo({ top: 0, behavior: 'smooth' });
  };

  return (
    <div className="grid lg:grid-cols-4 gap-6">
      {/* 左側篩選 */}
      <aside className="lg:col-span-1">
        <div className="sticky top-20">
          <SearchFilters initialValues={filters} onSearch={handleSearch} />
        </div>
      </aside>

      {/* 右側結果 */}
      <div className="lg:col-span-3 space-y-6">
        {/* 結果統計 */}
        {data && data.meta && (
          <div className="flex items-center justify-between">
            <p className="text-slate-600">
              找到 <span className="font-semibold text-slate-900">{data.meta.total}</span> 位業務員
            </p>
            <p className="text-sm text-slate-500">
              第 {data.meta.from || 0}-{data.meta.to || 0} 筆，共 {data.meta.total} 筆
            </p>
          </div>
        )}

        {/* 載入中 */}
        {isLoading && (
          <div className="grid md:grid-cols-2 gap-6">
            {Array.from({ length: 6 }).map((_, i) => (
              <SalespersonCardSkeleton key={i} />
            ))}
          </div>
        )}

        {/* 錯誤訊息 */}
        {error && (
          <Card>
            <CardContent className="py-12 text-center">
              <p className="text-red-600">載入失敗，請稍後再試</p>
            </CardContent>
          </Card>
        )}

        {/* 搜尋結果 */}
        {!isLoading && data && data.data && data.data.length > 0 && (
          <>
            <div className="grid md:grid-cols-2 gap-6">
              {data.data.map((salesperson) => (
                <SalespersonCard
                  key={salesperson.id}
                  salesperson={salesperson}
                />
              ))}
            </div>

            {/* 分頁 */}
            {data.meta && data.meta.last_page > 1 && (
              <div className="flex items-center justify-center gap-2 mt-8">
                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => handlePageChange(data.meta.current_page - 1)}
                  disabled={data.meta.current_page === 1}
                >
                  <ChevronLeft className="h-4 w-4" />
                </Button>

                {Array.from({ length: data.meta.last_page }, (_, i) => i + 1)
                  .filter((page) => {
                    const current = data.meta.current_page;
                    return (
                      page === 1 ||
                      page === data.meta.last_page ||
                      (page >= current - 2 && page <= current + 2)
                    );
                  })
                  .map((page, index, array) => {
                    const showEllipsis =
                      index > 0 && array[index - 1] !== page - 1;
                    return (
                      <div key={page} className="flex items-center gap-2">
                        {showEllipsis && <span className="text-slate-400">...</span>}
                        <Button
                          variant={page === data.meta.current_page ? 'default' : 'outline'}
                          size="sm"
                          onClick={() => handlePageChange(page)}
                        >
                          {page}
                        </Button>
                      </div>
                    );
                  })}

                <Button
                  variant="outline"
                  size="sm"
                  onClick={() => handlePageChange(data.meta.current_page + 1)}
                  disabled={data.meta.current_page === data.meta.last_page}
                >
                  <ChevronRight className="h-4 w-4" />
                </Button>
              </div>
            )}
          </>
        )}

        {/* 無結果 */}
        {!isLoading && data && data.data && data.data.length === 0 && (
          <Card>
            <CardContent className="py-16 text-center">
              <Search className="h-16 w-16 text-slate-300 mx-auto mb-4" />
              <h3 className="text-xl font-semibold text-slate-900 mb-2">
                找不到符合條件的業務員
              </h3>
              <p className="text-slate-600 mb-6">
                試著調整篩選條件或使用不同的關鍵字搜尋
              </p>
              <Button onClick={() => handleSearch({})}>
                清除所有篩選
              </Button>
            </CardContent>
          </Card>
        )}
      </div>
    </div>
  );
}
