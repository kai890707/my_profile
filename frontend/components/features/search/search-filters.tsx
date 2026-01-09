'use client';

import { useState, useEffect } from 'react';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';
import {
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Search, X } from 'lucide-react';
import { SearchParams } from '@/types/api';
import { useIndustries, useRegions } from '@/hooks/useSearch';

interface SearchFiltersProps {
  initialValues?: SearchParams;
  onSearch: (params: SearchParams) => void;
}

export function SearchFilters({ initialValues, onSearch }: SearchFiltersProps) {
  const [filters, setFilters] = useState<SearchParams>(initialValues || {});
  
  const { data: industries, isLoading: industriesLoading } = useIndustries();
  const { data: regions, isLoading: regionsLoading } = useRegions();

  useEffect(() => {
    if (initialValues) {
      setFilters(initialValues);
    }
  }, [initialValues]);

  const handleChange = (key: keyof SearchParams, value: any) => {
    setFilters((prev) => ({
      ...prev,
      [key]: value || undefined,
    }));
  };

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    onSearch(filters);
  };

  const handleReset = () => {
    const resetFilters: SearchParams = {};
    setFilters(resetFilters);
    onSearch(resetFilters);
  };

  return (
    <Card>
      <CardHeader>
        <CardTitle className="text-lg">篩選條件</CardTitle>
      </CardHeader>
      <CardContent>
        <form onSubmit={handleSubmit} className="space-y-4">
          {/* 關鍵字搜尋 */}
          <div>
            <label className="text-sm font-medium text-slate-700 mb-2 block">
              關鍵字
            </label>
            <Input
              placeholder="搜尋姓名、簡介..."
              value={filters.keyword || ''}
              onChange={(e) => handleChange('keyword', e.target.value)}
              icon={<Search className="h-4 w-4" />}
            />
          </div>

          {/* 公司名稱 */}
          <div>
            <label className="text-sm font-medium text-slate-700 mb-2 block">
              公司名稱
            </label>
            <Input
              placeholder="輸入公司名稱"
              value={filters.company || ''}
              onChange={(e) => handleChange('company', e.target.value)}
            />
          </div>

          {/* 產業類別 */}
          <div>
            <label className="text-sm font-medium text-slate-700 mb-2 block">
              產業類別
            </label>
            <Select
              value={filters.industry_id?.toString()}
              onValueChange={(value) => {
                if (value === 'all') {
                  handleChange('industry_id', undefined);
                } else {
                  handleChange('industry_id', parseInt(value));
                }
              }}
            >
              <SelectTrigger>
                <SelectValue placeholder="全部產業" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">全部產業</SelectItem>
                {industries?.map((industry) => (
                  <SelectItem key={industry.id} value={industry.id.toString()}>
                    {industry.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          {/* 服務地區 */}
          <div>
            <label className="text-sm font-medium text-slate-700 mb-2 block">
              服務地區
            </label>
            <Select
              value={filters.region_id?.toString()}
              onValueChange={(value) => {
                if (value === 'all') {
                  handleChange('region_id', undefined);
                } else {
                  handleChange('region_id', parseInt(value));
                }
              }}
            >
              <SelectTrigger>
                <SelectValue placeholder="全部地區" />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="all">全部地區</SelectItem>
                {regions?.map((region) => (
                  <SelectItem key={region.id} value={region.id.toString()}>
                    {region.name}
                  </SelectItem>
                ))}
              </SelectContent>
            </Select>
          </div>

          {/* 排序方式 */}
          <div>
            <label className="text-sm font-medium text-slate-700 mb-2 block">
              排序方式
            </label>
            <Select
              value={filters.sort || 'latest'}
              onValueChange={(value) => handleChange('sort', value)}
            >
              <SelectTrigger>
                <SelectValue />
              </SelectTrigger>
              <SelectContent>
                <SelectItem value="latest">最新註冊</SelectItem>
                <SelectItem value="popular">最受歡迎</SelectItem>
                <SelectItem value="relevant">相關度</SelectItem>
              </SelectContent>
            </Select>
          </div>

          {/* 按鈕組 */}
          <div className="flex gap-2 pt-4">
            <Button type="submit" className="flex-1">
              <Search className="mr-2 h-4 w-4" />
              搜尋
            </Button>
            <Button type="button" variant="outline" onClick={handleReset}>
              <X className="mr-2 h-4 w-4" />
              重置
            </Button>
          </div>
        </form>
      </CardContent>
    </Card>
  );
}
