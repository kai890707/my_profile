import type { SearchParams } from '@/types/api';

export const queryKeys = {
  auth: {
    me: ['auth', 'me'] as const,
  },
  search: {
    salespersons: (params: SearchParams) => ['search', 'salespersons', params] as const,
    detail: (id: number) => ['search', 'salespersons', id] as const,
  },
  salesperson: {
    profile: ['salesperson', 'profile'] as const,
    experiences: ['salesperson', 'experiences'] as const,
    certifications: ['salesperson', 'certifications'] as const,
    approvalStatus: ['salesperson', 'approval-status'] as const,
  },
  admin: {
    pendingApprovals: ['admin', 'pending-approvals'] as const,
    users: ['admin', 'users'] as const,
    statistics: ['admin', 'statistics'] as const,
    industries: ['admin', 'settings', 'industries'] as const,
    regions: ['admin', 'settings', 'regions'] as const,
  },
} as const;
