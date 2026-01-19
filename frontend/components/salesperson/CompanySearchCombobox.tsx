'use client';

import * as React from 'react';
import { useState, useCallback, useEffect } from 'react';
import { Check, ChevronsUpDown, Building2, Plus } from 'lucide-react';
import { cn } from '@/lib/utils';
import { Button } from '@/components/ui/button';
import {
  Command,
  CommandEmpty,
  CommandGroup,
  CommandInput,
  CommandItem,
  CommandList,
  CommandSeparator,
} from '@/components/ui/command';
import {
  Dialog,
  DialogContent,
  DialogTitle,
  DialogTrigger,
} from '@/components/ui/dialog';
import { VisuallyHidden } from '@/components/ui/visually-hidden';
import { searchCompanies, type SearchCompaniesResponse } from '@/lib/api/companies';
import type { Company } from '@/types/api';
import { toast } from 'sonner';

// Debounce hook
function useDebounce<T>(value: T, delay: number): T {
  const [debouncedValue, setDebouncedValue] = useState<T>(value);

  useEffect(() => {
    const handler = setTimeout(() => {
      setDebouncedValue(value);
    }, delay);

    return () => {
      clearTimeout(handler);
    };
  }, [value, delay]);

  return debouncedValue;
}

export interface CompanySearchComboboxProps {
  value: Company | null;
  onChange: (company: Company | null) => void;
  onAddNew: () => void;
  disabled?: boolean;
  error?: string;
}

export function CompanySearchCombobox({
  value,
  onChange,
  onAddNew,
  disabled = false,
  error,
}: CompanySearchComboboxProps) {
  const [open, setOpen] = useState(false);
  const [searchQuery, setSearchQuery] = useState('');
  const [companies, setCompanies] = useState<Company[]>([]);
  const [isLoading, setIsLoading] = useState(false);

  // Debounce search query
  const debouncedSearchQuery = useDebounce(searchQuery, 300);

  // Search companies
  const handleSearch = useCallback(async (query: string) => {
    if (!query || query.length < 1) {
      setCompanies([]);
      return;
    }

    setIsLoading(true);
    try {
      const response: SearchCompaniesResponse = await searchCompanies({
        name: query,
        tax_id: query,
      });

      if (response.success && response.companies) {
        // Limit to 10 results
        setCompanies(response.companies.slice(0, 10));
      } else {
        setCompanies([]);
      }
    } catch (error) {
      console.error('搜尋公司失敗:', error);
      toast.error('搜尋失敗，請稍後再試');
      setCompanies([]);
    } finally {
      setIsLoading(false);
    }
  }, []);

  // Effect to trigger search when debounced query changes
  useEffect(() => {
    if (debouncedSearchQuery) {
      handleSearch(debouncedSearchQuery);
    } else {
      setCompanies([]);
    }
  }, [debouncedSearchQuery, handleSearch]);

  // Handle company selection
  const handleSelect = (company: Company) => {
    onChange(company);
    setOpen(false);
    setSearchQuery('');
  };

  // Handle add new company
  const handleAddNew = () => {
    setOpen(false);
    setSearchQuery('');
    onAddNew();
  };

  return (
    <div className="space-y-2">
      <label className="text-sm font-semibold text-slate-700">
        公司名稱
        <span className="text-red-500 ml-1">*</span>
      </label>

      <Dialog open={open} onOpenChange={setOpen}>
        <DialogTrigger asChild>
          <Button
            variant="outline"
            role="combobox"
            aria-expanded={open}
            aria-label="選擇公司"
            disabled={disabled}
            className={cn(
              'w-full justify-between h-11',
              !value && 'text-slate-400',
              error && 'border-red-500 focus:ring-red-500'
            )}
          >
            <div className="flex items-center gap-2 truncate">
              <Building2 className="h-4 w-4 shrink-0 text-slate-500" />
              {value ? (
                <span className="truncate">{value.name}</span>
              ) : (
                <span>搜尋公司名稱或統一編號</span>
              )}
            </div>
            <ChevronsUpDown className="ml-2 h-4 w-4 shrink-0 text-slate-400" />
          </Button>
        </DialogTrigger>

        <DialogContent className="p-0 max-w-md">
          <VisuallyHidden>
            <DialogTitle>搜尋公司</DialogTitle>
          </VisuallyHidden>
          <Command shouldFilter={false}>
            <CommandInput
              placeholder="輸入公司名稱或統一編號"
              value={searchQuery}
              onValueChange={setSearchQuery}
              disabled={disabled}
            />
            <CommandList>
              {isLoading ? (
                <div className="py-6 text-center text-sm text-slate-500">
                  搜尋中...
                </div>
              ) : (
                <>
                  <CommandEmpty>
                    <div className="py-4 text-center">
                      <p className="text-sm text-slate-500 mb-3">找不到相符的公司</p>
                      <Button
                        variant="outline"
                        size="sm"
                        onClick={handleAddNew}
                        className="mx-auto"
                      >
                        <Plus className="mr-2 h-4 w-4" />
                        新增公司
                      </Button>
                    </div>
                  </CommandEmpty>

                  {companies.length > 0 && (
                    <>
                      <CommandGroup heading="搜尋結果">
                        {companies.map((company) => (
                          <CommandItem
                            key={company.id}
                            value={company.id.toString()}
                            onSelect={() => handleSelect(company)}
                            className="flex flex-col items-start py-3"
                          >
                            <div className="flex items-center gap-2 w-full">
                              <Check
                                className={cn(
                                  'h-4 w-4 shrink-0',
                                  value?.id === company.id ? 'opacity-100' : 'opacity-0'
                                )}
                              />
                              <div className="flex-1 min-w-0">
                                <div className="font-medium text-slate-900 truncate">
                                  {company.name}
                                </div>
                                <div className="text-xs text-slate-500 mt-0.5">
                                  {company.tax_id ? (
                                    <>統編：{company.tax_id}</>
                                  ) : (
                                    <>
                                      {company.is_personal ? (
                                        <span className="text-teal-600">個人工作室</span>
                                      ) : (
                                        <span className="text-slate-400">無統編</span>
                                      )}
                                    </>
                                  )}
                                </div>
                              </div>
                            </div>
                          </CommandItem>
                        ))}
                      </CommandGroup>

                      {companies.length >= 10 && (
                        <div className="px-2 py-2 text-xs text-slate-500 text-center border-t">
                          顯示前 10 筆結果，請輸入更精確的關鍵字
                        </div>
                      )}

                      <CommandSeparator />
                    </>
                  )}

                  {companies.length > 0 && (
                    <CommandGroup>
                      <CommandItem
                        onSelect={handleAddNew}
                        className="justify-center text-primary-600 font-medium"
                      >
                        <Plus className="mr-2 h-4 w-4" />
                        找不到？新增公司
                      </CommandItem>
                    </CommandGroup>
                  )}
                </>
              )}
            </CommandList>
          </Command>
        </DialogContent>
      </Dialog>

      {/* Selected company display */}
      {value && (
        <div className="flex items-center gap-2 p-3 bg-slate-50 rounded-lg border border-slate-200">
          <Building2 className="h-4 w-4 text-slate-500 shrink-0" />
          <div className="flex-1 min-w-0">
            <div className="text-sm font-medium text-slate-900 truncate">
              {value.name}
            </div>
            {value.tax_id && (
              <div className="text-xs text-slate-500">統編：{value.tax_id}</div>
            )}
          </div>
          <Button
            variant="ghost"
            size="sm"
            onClick={() => onChange(null)}
            disabled={disabled}
            className="shrink-0"
          >
            清除
          </Button>
        </div>
      )}

      {/* Error message */}
      {error && (
        <p className="text-sm text-red-600">{error}</p>
      )}

      <p className="text-xs text-slate-500">
        請輸入公司名稱或統一編號進行搜尋
      </p>
    </div>
  );
}
