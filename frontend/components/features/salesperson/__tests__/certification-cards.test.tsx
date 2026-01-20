import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { CertificationCards } from '../certification-cards';
import { Certification } from '@/types/api';

describe('CertificationCards', () => {
  const mockCertifications: Certification[] = [
    {
      id: 1,
      user_id: 1,
      name: '國際認證理財規劃師 (CFP)',
      issuer: '中華民國財務規劃師協會',
      issue_date: '2021-03-15',
      expiry_date: '2026-03-15',
      description: '國際認證理財規劃師，具備完整的財務規劃專業知識。',
      file_url: 'https://example.com/cfp-cert.pdf',
      approval_status: 'approved',
      approved_by: 1,
      approved_at: '2021-03-20T00:00:00.000Z',
      rejected_reason: null,
      created_at: '2021-03-15T00:00:00.000Z',
      updated_at: '2021-03-20T00:00:00.000Z',
    },
    {
      id: 2,
      user_id: 1,
      name: '人身保險業務員資格證照',
      issuer: '中華民國人壽保險商業公會',
      issue_date: '2020-01-10',
      expiry_date: null, // 永久有效
      description: '通過人身保險業務員資格測驗，取得合法銷售資格。',
      file_url: null,
      approval_status: 'approved',
      approved_by: 1,
      approved_at: '2020-01-15T00:00:00.000Z',
      rejected_reason: null,
      created_at: '2020-01-10T00:00:00.000Z',
      updated_at: '2020-01-15T00:00:00.000Z',
    },
    {
      id: 3,
      user_id: 1,
      name: '保險核保理論與實務',
      issuer: '中華民國核保學會',
      issue_date: '2019-06-20',
      expiry_date: '2022-06-20', // 已過期
      description: null,
      file_url: null,
      approval_status: 'pending',
      approved_by: null,
      approved_at: null,
      rejected_reason: null,
      created_at: '2019-06-20T00:00:00.000Z',
      updated_at: '2019-06-20T00:00:00.000Z',
    },
  ];

  it('renders certification cards with data', () => {
    render(<CertificationCards certifications={mockCertifications} />);

    // 檢查所有證照是否顯示
    expect(
      screen.getByText('國際認證理財規劃師 (CFP)')
    ).toBeInTheDocument();
    expect(screen.getByText('人身保險業務員資格證照')).toBeInTheDocument();
    expect(screen.getByText('保險核保理論與實務')).toBeInTheDocument();
  });

  it('sorts certifications correctly (approved first, then by issue date)', () => {
    render(<CertificationCards certifications={mockCertifications} />);

    const items = screen.getAllByRole('listitem');

    // 已驗證的應該在前面，且依發證日期倒序
    expect(items[0]).toHaveTextContent('國際認證理財規劃師');
    expect(items[1]).toHaveTextContent('人身保險業務員資格');
    expect(items[2]).toHaveTextContent('保險核保理論與實務');
  });

  it('displays verified badge for approved certifications', () => {
    render(<CertificationCards certifications={mockCertifications} />);

    // 檢查已驗證標記（2個已驗證的證照）
    const verifiedIcons = screen.getAllByLabelText('已驗證');
    expect(verifiedIcons).toHaveLength(2);
  });

  it('displays pending badge for pending certifications', () => {
    render(<CertificationCards certifications={mockCertifications} />);

    // 檢查審核中標記
    expect(screen.getByLabelText('審核中')).toBeInTheDocument();
  });

  it('shows "永久有效" for certifications without expiry date', () => {
    render(<CertificationCards certifications={mockCertifications} />);

    expect(screen.getByText('永久有效')).toBeInTheDocument();
  });

  it('displays "查看證書" button when file_url exists', () => {
    render(<CertificationCards certifications={mockCertifications} />);

    // 只有第一個證照有 file_url
    const viewButtons = screen.getAllByText('查看證書');
    expect(viewButtons).toHaveLength(1);

    // 檢查連結是否正確
    expect(viewButtons[0].closest('a')).toHaveAttribute(
      'href',
      'https://example.com/cfp-cert.pdf'
    );
  });

  it('displays empty state when no certifications', () => {
    render(<CertificationCards certifications={[]} />);

    expect(screen.getByText('尚無專業證照記錄')).toBeInTheDocument();
    expect(screen.getByText('此業務員尚未新增專業證照')).toBeInTheDocument();
  });

  it('displays loading skeleton when loading', () => {
    const { container } = render(
      <CertificationCards certifications={[]} isLoading={true} />
    );

    // 檢查是否有 loading skeleton
    expect(container.querySelector('.animate-pulse')).toBeInTheDocument();
  });

  it('filters certifications when showFilter is enabled', async () => {
    const user = userEvent.setup();

    render(
      <CertificationCards
        certifications={mockCertifications}
        showFilter={true}
      />
    );

    // 檢查篩選下拉選單是否存在
    const filterSelect = screen.getByLabelText('篩選證照');
    expect(filterSelect).toBeInTheDocument();

    // 檢查初始狀態顯示全部
    expect(screen.getByText(/全部 \(3\)/)).toBeInTheDocument();
    expect(screen.getByText(/已驗證 \(2\)/)).toBeInTheDocument();

    // 切換到只顯示已驗證
    await user.selectOptions(filterSelect, 'approved');

    // 只應該顯示 2 個已驗證的證照
    expect(
      screen.getByText('國際認證理財規劃師 (CFP)')
    ).toBeInTheDocument();
    expect(screen.getByText('人身保險業務員資格證照')).toBeInTheDocument();

    // 審核中的不應該顯示
    expect(
      screen.queryByText('保險核保理論與實務')
    ).not.toBeInTheDocument();
  });

  it('shows "目前沒有已驗證的證照" when filtered result is empty', async () => {
    const user = userEvent.setup();

    const pendingOnlyCerts: Certification[] = [
      {
        ...mockCertifications[2],
        approval_status: 'pending',
      },
    ];

    render(
      <CertificationCards
        certifications={pendingOnlyCerts}
        showFilter={true}
      />
    );

    const filterSelect = screen.getByLabelText('篩選證照');

    // 切換到只顯示已驗證
    await user.selectOptions(filterSelect, 'approved');

    expect(screen.getByText('目前沒有已驗證的證照')).toBeInTheDocument();
  });

  it('displays issuer correctly', () => {
    render(<CertificationCards certifications={mockCertifications} />);

    expect(screen.getByText('中華民國財務規劃師協會')).toBeInTheDocument();
    expect(
      screen.getByText('中華民國人壽保險商業公會')
    ).toBeInTheDocument();
    expect(screen.getByText('中華民國核保學會')).toBeInTheDocument();
  });

  it('uses 2-column grid on desktop', () => {
    const { container } = render(
      <CertificationCards certifications={mockCertifications} />
    );

    const grid = container.querySelector('[role="list"]');
    expect(grid).toHaveClass('grid');
    expect(grid).toHaveClass('md:grid-cols-2');
  });
});
