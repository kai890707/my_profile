'use client';

import { useState, useEffect } from 'react';
import { useForm } from 'react-hook-form';
import { zodResolver } from '@hookform/resolvers/zod';
import { z } from 'zod';
import { useRouter } from 'next/navigation';
import { useDebounce } from 'use-debounce';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { useCreateCompany, useSearchCompanies } from '@/hooks/useCompanies';
import { useUpdateProfile } from '@/hooks/useSalesperson';
import type { Company } from '@/types/api';

type CompanyType = 'registered' | 'personal' | null;

// 註冊公司表單 Schema
const registeredCompanySchema = z.object({
  tax_id: z.string().regex(/^\d{8}$/, '統一編號必須是 8 位數字'),
  name: z.string().min(2, '公司名稱至少需要 2 個字元').max(200, '公司名稱不能超過 200 個字元'),
});

// 個人工作室表單 Schema
const personalStudioSchema = z.object({
  name: z.string().min(2, '工作室名稱至少需要 2 個字元').max(200, '工作室名稱不能超過 200 個字元'),
});

type RegisteredCompanyForm = z.infer<typeof registeredCompanySchema>;
type PersonalStudioForm = z.infer<typeof personalStudioSchema>;

export default function CreateCompanyPage() {
  const router = useRouter();
  const [companyType, setCompanyType] = useState<CompanyType>(null);
  const [taxIdInput, setTaxIdInput] = useState('');
  const [debouncedTaxId] = useDebounce(taxIdInput, 500);
  const [foundCompany, setFoundCompany] = useState<Company | null>(null);

  const createCompanyMutation = useCreateCompany();
  const updateProfileMutation = useUpdateProfile();

  // Search for existing companies by tax_id
  const { data: searchResult, isLoading: isSearching } = useSearchCompanies({
    tax_id: debouncedTaxId.length === 8 ? debouncedTaxId : undefined,
  });

  // Update found company when search results change
  useEffect(() => {
    if (searchResult && searchResult.exists && searchResult.companies.length > 0) {
      setFoundCompany(searchResult.companies[0]);
    } else {
      setFoundCompany(null);
    }
  }, [searchResult]);

  // Registered company form
  const registeredForm = useForm<RegisteredCompanyForm>({
    resolver: zodResolver(registeredCompanySchema),
  });

  // Personal studio form
  const personalForm = useForm<PersonalStudioForm>({
    resolver: zodResolver(personalStudioSchema),
  });

  // Handle tax_id input change
  const handleTaxIdChange = (e: React.ChangeEvent<HTMLInputElement>) => {
    const value = e.target.value.replace(/\D/g, '').slice(0, 8);
    setTaxIdInput(value);
    registeredForm.setValue('tax_id', value);
  };

  // Submit registered company (create new)
  const onSubmitRegistered = (data: RegisteredCompanyForm) => {
    createCompanyMutation.mutate({
      name: data.name,
      tax_id: data.tax_id,
      is_personal: false,
    });
  };

  // Submit personal studio
  const onSubmitPersonal = (data: PersonalStudioForm) => {
    createCompanyMutation.mutate({
      name: data.name,
      tax_id: null,
      is_personal: true,
    });
  };

  // Join existing company
  const handleJoinCompany = () => {
    if (!foundCompany) return;

    updateProfileMutation.mutate({
      company_id: foundCompany.id,
    });
  };

  // Step 1: Choose company type
  if (companyType === null) {
    return (
      <div className="max-w-4xl mx-auto py-8 px-4">
        <div className="space-y-8">
          <div className="text-center">
            <h1 className="text-3xl font-bold text-foreground">建立公司資料</h1>
            <p className="text-slate-600 mt-2 text-base">
              請選擇您要建立的公司類型
            </p>
          </div>

          <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
            {/* 註冊公司選項 */}
            <button
              onClick={() => setCompanyType('registered')}
              className="group relative flex flex-col items-center justify-center p-8 border-2 border-slate-200 rounded-2xl hover:border-primary-500 hover:bg-primary-50 transition-all"
            >
              <div className="w-16 h-16 bg-slate-100 group-hover:bg-primary-100 rounded-full flex items-center justify-center mb-4 transition-colors">
                <svg className="w-8 h-8 text-slate-600 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-foreground mb-2">註冊公司</h3>
              <p className="text-sm text-slate-600 text-center">
                有統一編號的正式公司
              </p>
              <div className="mt-3 px-3 py-1 bg-blue-100 text-blue-700 text-xs font-semibold rounded-full">
                需要統編
              </div>
            </button>

            {/* 個人工作室選項 */}
            <button
              onClick={() => setCompanyType('personal')}
              className="group relative flex flex-col items-center justify-center p-8 border-2 border-slate-200 rounded-2xl hover:border-primary-500 hover:bg-primary-50 transition-all"
            >
              <div className="w-16 h-16 bg-slate-100 group-hover:bg-primary-100 rounded-full flex items-center justify-center mb-4 transition-colors">
                <svg className="w-8 h-8 text-slate-600 group-hover:text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                </svg>
              </div>
              <h3 className="text-xl font-bold text-foreground mb-2">個人工作室</h3>
              <p className="text-sm text-slate-600 text-center">
                個人經營，不需統一編號
              </p>
              <div className="mt-3 px-3 py-1 bg-green-100 text-green-700 text-xs font-semibold rounded-full">
                無需統編
              </div>
            </button>
          </div>

          <button
            onClick={() => router.back()}
            className="w-full py-3 text-sm text-slate-600 hover:text-slate-800 font-medium transition-colors"
          >
            取消
          </button>
        </div>
      </div>
    );
  }

  // Step 2A: Registered company form
  if (companyType === 'registered') {
    return (
      <div className="max-w-3xl mx-auto py-8 px-4">
        <div className="space-y-8">
          <div className="text-center">
            <button
              onClick={() => {
                setCompanyType(null);
                setTaxIdInput('');
                setFoundCompany(null);
                registeredForm.reset();
              }}
              className="inline-flex items-center text-sm text-slate-600 hover:text-slate-800 mb-4"
            >
              <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
              返回選擇
            </button>
            <h1 className="text-3xl font-bold text-foreground">建立註冊公司</h1>
            <p className="text-slate-600 mt-2 text-base">
              輸入統一編號以查詢或建立公司
            </p>
          </div>

          <div className="bg-white border border-slate-200 rounded-xl p-8 shadow-sm">
            <form onSubmit={registeredForm.handleSubmit(onSubmitRegistered)} className="space-y-6">
              {/* Tax ID Input */}
              <div>
                <Input
                  label="統一編號"
                  type="text"
                  placeholder="12345678"
                  value={taxIdInput}
                  onChange={handleTaxIdChange}
                  error={registeredForm.formState.errors.tax_id?.message}
                  helperText="8 位數字"
                  required
                />
                {isSearching && taxIdInput.length === 8 && (
                  <p className="text-sm text-blue-600 mt-2">搜尋中...</p>
                )}
              </div>

              {/* Found Company - Show company info + Join button */}
              {foundCompany && taxIdInput.length === 8 && (
                <div className="bg-green-50 border-2 border-green-200 rounded-xl p-6">
                  <div className="flex items-start">
                    <svg className="h-6 w-6 text-green-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clipRule="evenodd" />
                    </svg>
                    <div className="flex-1">
                      <h3 className="text-sm font-semibold text-green-900 mb-2">
                        找到既有公司
                      </h3>
                      <div className="bg-white border border-green-200 rounded-lg p-4 mb-4">
                        <p className="text-sm font-semibold text-slate-900">{foundCompany.name}</p>
                        <p className="text-xs text-slate-600 mt-1">統編：{foundCompany.tax_id}</p>
                      </div>
                      <p className="text-sm text-green-800 mb-4">
                        此公司已存在於系統中，您可以加入此公司。
                      </p>
                      <Button
                        type="button"
                        onClick={handleJoinCompany}
                        isLoading={updateProfileMutation.isPending}
                        className="w-full"
                      >
                        {updateProfileMutation.isPending ? '加入中...' : '加入此公司'}
                      </Button>
                    </div>
                  </div>
                </div>
              )}

              {/* Not Found - Show name input + Create button */}
              {!foundCompany && taxIdInput.length === 8 && !isSearching && (
                <div className="bg-blue-50 border-2 border-blue-200 rounded-xl p-6">
                  <div className="flex items-start">
                    <svg className="h-6 w-6 text-blue-600 mt-0.5 mr-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                      <path fillRule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clipRule="evenodd" />
                    </svg>
                    <div className="flex-1">
                      <h3 className="text-sm font-semibold text-blue-900 mb-2">
                        未找到公司
                      </h3>
                      <p className="text-sm text-blue-800 mb-4">
                        此統編尚未在系統中註冊，請輸入公司名稱以建立新公司。
                      </p>

                      <div className="space-y-4">
                        <Input
                          label="公司名稱"
                          type="text"
                          placeholder="三商美邦人壽保險股份有限公司"
                          error={registeredForm.formState.errors.name?.message}
                          required
                          {...registeredForm.register('name')}
                        />

                        <Button
                          type="submit"
                          isLoading={createCompanyMutation.isPending}
                          className="w-full"
                        >
                          {createCompanyMutation.isPending ? '建立中...' : '建立公司'}
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              )}
            </form>
          </div>

          <button
            onClick={() => {
              setCompanyType(null);
              setTaxIdInput('');
              setFoundCompany(null);
              registeredForm.reset();
            }}
            className="w-full py-3 text-sm text-slate-600 hover:text-slate-800 font-medium transition-colors"
          >
            取消
          </button>
        </div>
      </div>
    );
  }

  // Step 2B: Personal studio form
  if (companyType === 'personal') {
    return (
      <div className="max-w-3xl mx-auto py-8 px-4">
        <div className="space-y-8">
          <div className="text-center">
            <button
              onClick={() => {
                setCompanyType(null);
                personalForm.reset();
              }}
              className="inline-flex items-center text-sm text-slate-600 hover:text-slate-800 mb-4"
            >
              <svg className="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
              </svg>
              返回選擇
            </button>
            <h1 className="text-3xl font-bold text-foreground">建立個人工作室</h1>
            <p className="text-slate-600 mt-2 text-base">
              填寫您的工作室名稱
            </p>
          </div>

          <div className="bg-white border border-slate-200 rounded-xl p-8 shadow-sm">
            <form onSubmit={personalForm.handleSubmit(onSubmitPersonal)} className="space-y-6">
              <Input
                label="工作室名稱"
                type="text"
                placeholder="小明保險工作室"
                error={personalForm.formState.errors.name?.message}
                helperText="無需統一編號，適合個人經營"
                required
                {...personalForm.register('name')}
              />

              <div className="bg-green-50 border border-green-200 rounded-lg p-4">
                <p className="text-sm text-green-800">
                  <strong>提示：</strong>個人工作室不需要統一編號，適合獨立經營的業務員使用。
                </p>
              </div>

              <Button
                type="submit"
                className="w-full text-lg font-bold tracking-wide"
                size="lg"
                isLoading={createCompanyMutation.isPending}
              >
                {createCompanyMutation.isPending ? '建立中...' : '建立工作室'}
              </Button>
            </form>
          </div>

          <button
            onClick={() => {
              setCompanyType(null);
              personalForm.reset();
            }}
            className="w-full py-3 text-sm text-slate-600 hover:text-slate-800 font-medium transition-colors"
          >
            取消
          </button>
        </div>
      </div>
    );
  }

  return null;
}
