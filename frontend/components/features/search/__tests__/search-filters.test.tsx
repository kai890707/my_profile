import { describe, it, expect, vi, beforeEach } from 'vitest';
import { render, screen, fireEvent, waitFor } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { QueryClient, QueryClientProvider } from '@tanstack/react-query';
import { SearchFilters } from '../search-filters';

// Mock the hooks
vi.mock('@/hooks/useSearch', () => ({
  useIndustries: vi.fn(() => ({
    data: [
      { id: 1, name: '科技業', slug: 'tech' },
      { id: 2, name: '金融業', slug: 'finance' },
    ],
    isLoading: false,
  })),
  useRegions: vi.fn(() => ({
    data: [
      { id: 1, name: '台北市', slug: 'taipei' },
      { id: 2, name: '新北市', slug: 'new-taipei' },
    ],
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

describe('SearchFilters', () => {
  const mockOnSearch = vi.fn();

  beforeEach(() => {
    mockOnSearch.mockClear();
  });

  it('renders all filter fields', () => {
    render(<SearchFilters onSearch={mockOnSearch} />, {
      wrapper: createWrapper(),
    });

    expect(screen.getByText('關鍵字')).toBeInTheDocument();
    expect(screen.getByText('公司名稱')).toBeInTheDocument();
    expect(screen.getByText('產業類別')).toBeInTheDocument();
    expect(screen.getByText('服務地區')).toBeInTheDocument();
    expect(screen.getByText('排序方式')).toBeInTheDocument();
  });

  it('allows typing in keyword input', async () => {
    const user = userEvent.setup();
    render(<SearchFilters onSearch={mockOnSearch} />, {
      wrapper: createWrapper(),
    });

    const keywordInput = screen.getByPlaceholderText('搜尋姓名、簡介...');
    await user.type(keywordInput, '測試關鍵字');

    expect(keywordInput).toHaveValue('測試關鍵字');
  });

  it('allows typing in company input', async () => {
    const user = userEvent.setup();
    render(<SearchFilters onSearch={mockOnSearch} />, {
      wrapper: createWrapper(),
    });

    const companyInput = screen.getByPlaceholderText('輸入公司名稱');
    await user.type(companyInput, 'YAMU 公司');

    expect(companyInput).toHaveValue('YAMU 公司');
  });

  it('submits search with keyword', async () => {
    const user = userEvent.setup();
    render(<SearchFilters onSearch={mockOnSearch} />, {
      wrapper: createWrapper(),
    });

    const keywordInput = screen.getByPlaceholderText('搜尋姓名、簡介...');
    await user.type(keywordInput, '業務員');

    const searchButton = screen.getByRole('button', { name: /搜尋/i });
    await user.click(searchButton);

    expect(mockOnSearch).toHaveBeenCalledWith(
      expect.objectContaining({
        keyword: '業務員',
      })
    );
  });

  it('submits search with company name', async () => {
    const user = userEvent.setup();
    render(<SearchFilters onSearch={mockOnSearch} />, {
      wrapper: createWrapper(),
    });

    const companyInput = screen.getByPlaceholderText('輸入公司名稱');
    await user.type(companyInput, 'YAMU');

    const searchButton = screen.getByRole('button', { name: /搜尋/i });
    await user.click(searchButton);

    expect(mockOnSearch).toHaveBeenCalledWith(
      expect.objectContaining({
        company: 'YAMU',
      })
    );
  });

  it('resets all filters when reset button is clicked', async () => {
    const user = userEvent.setup();
    render(<SearchFilters onSearch={mockOnSearch} />, {
      wrapper: createWrapper(),
    });

    // Fill in some values
    const keywordInput = screen.getByPlaceholderText('搜尋姓名、簡介...');
    await user.type(keywordInput, '測試');

    const companyInput = screen.getByPlaceholderText('輸入公司名稱');
    await user.type(companyInput, 'YAMU');

    // Click reset
    const resetButton = screen.getByRole('button', { name: /重置/i });
    await user.click(resetButton);

    // Check inputs are cleared
    expect(keywordInput).toHaveValue('');
    expect(companyInput).toHaveValue('');

    // Check onSearch is called with empty object
    expect(mockOnSearch).toHaveBeenCalledWith({});
  });

  it('displays initial values when provided', () => {
    const initialValues = {
      keyword: '初始關鍵字',
      company: '初始公司',
    };

    render(<SearchFilters initialValues={initialValues} onSearch={mockOnSearch} />, {
      wrapper: createWrapper(),
    });

    expect(screen.getByPlaceholderText('搜尋姓名、簡介...')).toHaveValue('初始關鍵字');
    expect(screen.getByPlaceholderText('輸入公司名稱')).toHaveValue('初始公司');
  });

  it('does not have empty string values in Select items', () => {
    const { container } = render(<SearchFilters onSearch={mockOnSearch} />, {
      wrapper: createWrapper(),
    });

    // Check that there are no SelectItem elements with empty value prop
    // This is a regression test for the "empty string value" bug
    const selectItems = container.querySelectorAll('[data-radix-collection-item]');

    selectItems.forEach((item) => {
      const value = item.getAttribute('data-value');
      // Value should either be "all" or a number, never an empty string
      expect(value).not.toBe('');
    });
  });

  it('renders industry select with options', () => {
    render(<SearchFilters onSearch={mockOnSearch} />, {
      wrapper: createWrapper(),
    });

    // Verify that select components are rendered
    const selects = screen.getAllByRole('combobox');
    expect(selects.length).toBeGreaterThanOrEqual(3); // industry, region, sort
  });

  it('passes industry_id in initial values correctly', () => {
    render(<SearchFilters initialValues={{ industry_id: 1 }} onSearch={mockOnSearch} />, {
      wrapper: createWrapper(),
    });

    // Component should render without errors with initial industry_id
    expect(screen.getByText('產業類別')).toBeInTheDocument();
  });
});
