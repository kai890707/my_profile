import { describe, it, expect } from 'vitest';
import { render, screen } from '@testing-library/react';
import { ExperienceTimeline } from '../experience-timeline';
import { Experience } from '@/types/api';

describe('ExperienceTimeline', () => {
  const mockExperiences: Experience[] = [
    {
      id: 1,
      user_id: 1,
      company: '三商美邦人壽',
      position: '資深業務經理',
      start_date: '2022-01-15',
      end_date: null, // 在職中
      description: '負責企業客戶開發與維護，協助客戶規劃完整的保險方案。',
      approval_status: 'approved',
      approved_by: 1,
      approved_at: '2022-01-20T00:00:00.000Z',
      rejected_reason: null,
      created_at: '2022-01-15T00:00:00.000Z',
      updated_at: '2022-01-20T00:00:00.000Z',
    },
    {
      id: 2,
      user_id: 1,
      company: { name: '南山人壽' },
      position: '業務專員',
      start_date: '2020-03-01',
      end_date: '2021-12-31',
      description: '開發個人客戶，協助客戶規劃保險計劃。',
      approval_status: 'pending',
      approved_by: null,
      approved_at: null,
      rejected_reason: null,
      created_at: '2020-03-01T00:00:00.000Z',
      updated_at: '2020-03-01T00:00:00.000Z',
    },
    {
      id: 3,
      user_id: 1,
      company: '國泰人壽',
      position: '業務助理',
      start_date: '2018-06-01',
      end_date: '2020-02-28',
      description: null,
      approval_status: 'approved',
      approved_by: 1,
      approved_at: '2018-06-10T00:00:00.000Z',
      rejected_reason: null,
      created_at: '2018-06-01T00:00:00.000Z',
      updated_at: '2018-06-10T00:00:00.000Z',
    },
  ];

  it('renders experience timeline with data', () => {
    render(<ExperienceTimeline experiences={mockExperiences} />);

    // 檢查所有經驗是否顯示
    expect(screen.getByText('資深業務經理')).toBeInTheDocument();
    expect(screen.getByText('業務專員')).toBeInTheDocument();
    expect(screen.getByText('業務助理')).toBeInTheDocument();
  });

  it('sorts experiences correctly (ongoing first, then by start date)', () => {
    render(<ExperienceTimeline experiences={mockExperiences} />);

    const items = screen.getAllByRole('listitem');

    // 第一個應該是在職中的（end_date = null）
    expect(items[0]).toHaveTextContent('資深業務經理');
    expect(items[0]).toHaveTextContent('至今');

    // 第二個是最近離職的
    expect(items[1]).toHaveTextContent('業務專員');

    // 最後一個是最早的
    expect(items[2]).toHaveTextContent('業務助理');
  });

  it('displays verified badge for approved experiences', () => {
    render(<ExperienceTimeline experiences={mockExperiences} />);

    // 檢查已驗證標記
    const verifiedBadges = screen.getAllByText('已驗證');
    expect(verifiedBadges).toHaveLength(2); // 兩個已驗證的經驗
  });

  it('shows "至今" for ongoing experiences', () => {
    render(<ExperienceTimeline experiences={mockExperiences} />);

    expect(screen.getByText('至今')).toBeInTheDocument();
    expect(screen.getByText('在職中')).toBeInTheDocument();
  });

  it('displays empty state when no experiences', () => {
    render(<ExperienceTimeline experiences={[]} />);

    expect(screen.getByText('尚無工作經驗記錄')).toBeInTheDocument();
    expect(screen.getByText('此業務員尚未新增工作經驗')).toBeInTheDocument();
  });

  it('displays loading skeleton when loading', () => {
    const { container } = render(
      <ExperienceTimeline experiences={[]} isLoading={true} />
    );

    // 檢查是否有 loading skeleton
    expect(container.querySelector('.animate-pulse')).toBeInTheDocument();
  });

  it('handles company as string or object', () => {
    render(<ExperienceTimeline experiences={mockExperiences} />);

    // 字串形式的公司
    expect(screen.getByText('三商美邦人壽')).toBeInTheDocument();

    // 物件形式的公司
    expect(screen.getByText('南山人壽')).toBeInTheDocument();
  });

  it('displays description when available', () => {
    render(<ExperienceTimeline experiences={mockExperiences} />);

    expect(
      screen.getByText(/負責企業客戶開發與維護/)
    ).toBeInTheDocument();
    expect(
      screen.getByText(/開發個人客戶，協助客戶規劃保險計劃/)
    ).toBeInTheDocument();
  });

  it('calculates duration correctly', () => {
    render(<ExperienceTimeline experiences={mockExperiences} />);

    // 檢查年資是否正確計算（注意：實際計算會依當前時間而變化）
    // 第二個經驗：2020-03 到 2021-12 = 1年9個月
    expect(screen.getByText(/1年9個月/)).toBeInTheDocument();

    // 第三個經驗：2018-06 到 2020-02 = 1年8個月
    expect(screen.getByText(/1年8個月/)).toBeInTheDocument();
  });
});
