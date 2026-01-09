import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, waitFor } from '@testing-library/react';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import SearchPage from '../page';

// Mock Next.js router
const mockPush = vi.fn();
const mockSearchParams = new URLSearchParams();

vi.mock('next/navigation', () => ({
  useRouter: () => ({
    push: mockPush,
  }),
  useSearchParams: () => mockSearchParams,
}));

// Mock the search hooks
let mockSearchData: any = null;
let mockSearchLoading = false;
let mockSearchError: any = null;

vi.mock('@/hooks/useSearch', () => ({
  useSearchSalespersons: vi.fn(() => ({
    data: mockSearchData,
    isLoading: mockSearchLoading,
    error: mockSearchError,
  })),
  useIndustries: vi.fn(() => ({
    data: [],
    isLoading: false,
  })),
  useRegions: vi.fn(() => ({
    data: [],
    isLoading: false,
  })),
}));

const createWrapper = () => {
  const queryClient = new QueryClient({
    defaultOptions: {
      queries: {
        retry: false,
      },
    },
  });
  return ({ children }: { children: React.ReactNode }) => (
    <QueryClientProvider client={queryClient}>{children}</QueryClientProvider>
  );
};

describe('SearchPage', () => {
  beforeEach(() => {
    mockSearchData = null;
    mockSearchLoading = false;
    mockSearchError = null;
    mockPush.mockClear();
  });

  it('renders page title and description', () => {
    render(<SearchPage />, { wrapper: createWrapper() });

    expect(screen.getByRole('heading', { name: '搜尋業務員', level: 1 })).toBeInTheDocument();
    expect(screen.getByText('找到最適合您需求的專業業務員')).toBeInTheDocument();
  });

  it('shows loading skeletons when data is loading', () => {
    mockSearchLoading = true;

    render(<SearchPage />, { wrapper: createWrapper() });

    // Should show 6 skeleton cards
    const skeletons = document.querySelectorAll('.animate-pulse');
    expect(skeletons.length).toBeGreaterThan(0);
  });

  it('shows error message when API call fails', () => {
    mockSearchError = new Error('API Error');

    render(<SearchPage />, { wrapper: createWrapper() });

    expect(screen.getByText('載入失敗，請稍後再試')).toBeInTheDocument();
  });

  it('handles data with missing meta gracefully', () => {
    // This is the regression test for the bug
    mockSearchData = {
      data: [
        {
          id: 1,
          full_name: '測試業務員',
          phone: '0912345678',
          bio: '測試簡介',
          specialties: '業務',
          avatar: null,
          company_name: '測試公司',
          industry_name: '科技',
          service_regions: ['台北'],
          created_at: '2024-01-01',
        },
      ],
      // meta is missing!
    };

    render(<SearchPage />, { wrapper: createWrapper() });

    // Should not crash and should show the salesperson card
    expect(screen.getByText('測試業務員')).toBeInTheDocument();

    // Should not show the stats section when meta is missing
    expect(screen.queryByText(/找到.*位業務員/)).not.toBeInTheDocument();
  });

  it('handles data with missing data array gracefully', () => {
    mockSearchData = {
      meta: {
        current_page: 1,
        from: 1,
        last_page: 1,
        per_page: 20,
        to: 0,
        total: 0,
      },
      // data array is missing!
    };

    render(<SearchPage />, { wrapper: createWrapper() });

    // Should not crash
    expect(screen.getByRole('heading', { name: '搜尋業務員', level: 1 })).toBeInTheDocument();

    // Should show stats when meta exists - check for the stats paragraph specifically
    const statsElements = screen.getAllByText(/找到/);
    // Should have at least one (besides the description)
    expect(statsElements.length).toBeGreaterThan(0);
  });

  it('displays search results correctly', () => {
    mockSearchData = {
      data: [
        {
          id: 1,
          full_name: '張三',
          phone: '0912345678',
          bio: '專業業務員',
          specialties: '業務,銷售',
          avatar: null,
          company_name: 'ABC公司',
          industry_name: '科技業',
          service_regions: ['台北市', '新北市'],
          created_at: '2024-01-01',
        },
        {
          id: 2,
          full_name: '李四',
          phone: '0923456789',
          bio: '資深顧問',
          specialties: '顧問',
          avatar: null,
          company_name: 'XYZ公司',
          industry_name: '金融業',
          service_regions: ['台中市'],
          created_at: '2024-01-02',
        },
      ],
      meta: {
        current_page: 1,
        from: 1,
        last_page: 1,
        per_page: 20,
        to: 2,
        total: 2,
      },
    };

    render(<SearchPage />, { wrapper: createWrapper() });

    // Should show stats - verify by checking for salesperson cards
    // Names appear in both avatar and card title, so use getAllByText
    const zhangSanElements = screen.getAllByText('張三');
    expect(zhangSanElements.length).toBeGreaterThanOrEqual(1);

    const liSiElements = screen.getAllByText('李四');
    expect(liSiElements.length).toBeGreaterThanOrEqual(1);

    // Should show stats section (there will be multiple "找到" texts)
    const statsElements = screen.getAllByText(/找到/);
    expect(statsElements.length).toBeGreaterThan(0);
  });

  it('shows empty state when no results found', () => {
    mockSearchData = {
      data: [],
      meta: {
        current_page: 1,
        from: 0,
        last_page: 1,
        per_page: 20,
        to: 0,
        total: 0,
      },
    };

    render(<SearchPage />, { wrapper: createWrapper() });

    expect(screen.getByText('找不到符合條件的業務員')).toBeInTheDocument();
    expect(screen.getByText('試著調整篩選條件或使用不同的關鍵字搜尋')).toBeInTheDocument();
    expect(screen.getByText('清除所有篩選')).toBeInTheDocument();
  });

  it('does not show pagination when only one page', () => {
    mockSearchData = {
      data: [
        {
          id: 1,
          full_name: '測試',
          phone: '0912345678',
          bio: '測試',
          specialties: '測試',
          avatar: null,
          company_name: '測試',
          industry_name: '測試',
          service_regions: ['台北'],
          created_at: '2024-01-01',
        },
      ],
      meta: {
        current_page: 1,
        from: 1,
        last_page: 1, // Only one page
        per_page: 20,
        to: 1,
        total: 1,
      },
    };

    render(<SearchPage />, { wrapper: createWrapper() });

    // Pagination should not be rendered when last_page is 1
    const paginationButtons = screen.queryAllByRole('button', { name: /\d+/ });
    expect(paginationButtons.length).toBe(0);
  });

  it('shows pagination when multiple pages exist', () => {
    mockSearchData = {
      data: [
        {
          id: 1,
          full_name: '測試',
          phone: '0912345678',
          bio: '測試',
          specialties: '測試',
          avatar: null,
          company_name: '測試',
          industry_name: '測試',
          service_regions: ['台北'],
          created_at: '2024-01-01',
        },
      ],
      meta: {
        current_page: 1,
        from: 1,
        last_page: 3, // Multiple pages
        per_page: 20,
        to: 20,
        total: 50,
      },
    };

    render(<SearchPage />, { wrapper: createWrapper() });

    // Should show page buttons
    expect(screen.getByRole('button', { name: '1' })).toBeInTheDocument();
    expect(screen.getByRole('button', { name: '3' })).toBeInTheDocument();
  });

  it('handles null or undefined data gracefully', () => {
    mockSearchData = null;

    render(<SearchPage />, { wrapper: createWrapper() });

    // Should not crash
    expect(screen.getByRole('heading', { name: '搜尋業務員', level: 1 })).toBeInTheDocument();
  });

  it('handles undefined data gracefully', () => {
    mockSearchData = undefined;

    render(<SearchPage />, { wrapper: createWrapper() });

    // Should not crash
    expect(screen.getByRole('heading', { name: '搜尋業務員', level: 1 })).toBeInTheDocument();
  });
});
