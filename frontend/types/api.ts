// ============================================
// API Response Types
// ============================================

export interface ApiResponse<T = any> {
  success: boolean;
  message: string;
  data?: T;
  errors?: Record<string, string[]>;
}

export interface PaginationMeta {
  total: number;
  per_page: number;
  current_page: number;
  last_page: number;
  from: number;
  to: number;
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: PaginationMeta;
}

// ============================================
// User & Auth Types
// ============================================

export type UserRole = 'admin' | 'salesperson' | 'user';
export type UserStatus = 'pending' | 'active' | 'inactive';

export interface User {
  id: number;
  username: string;
  email: string;
  role: UserRole;
  status: UserStatus;
  email_verified_at: string | null;
  created_at: string;
  updated_at: string;
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface RegisterUserRequest {
  name: string;
  email: string;
  password: string;
}

export interface RegisterSalespersonRequest {
  name: string;
  email: string;
  password: string;
  full_name: string;
  phone: string;
  bio?: string;
  specialties?: string;
  service_regions?: string[];
}

// Legacy type for backward compatibility
export interface RegisterRequest {
  username: string;
  email: string;
  password: string;
  full_name: string;
  phone: string;
}

export interface AuthResponse {
  user: User;
  access_token: string;
  refresh_token: string;
  token_type: 'Bearer';
  expires_in: number;
}

// ============================================
// Salesperson Types
// ============================================

export type ApprovalStatus = 'pending' | 'approved' | 'rejected';

export interface SalespersonProfile {
  id: number;
  user_id: number;
  company_id: number | null;
  full_name: string;
  phone: string;
  bio: string | null;
  specialties: string | null;
  service_regions: string[];
  avatar: string | null;
  approval_status: ApprovalStatus;
  approved_by: number | null;
  approved_at: string | null;
  created_at: string;
  updated_at: string;
  company?: Company;
  experiences?: Experience[];
  certifications?: Certification[];
}

export interface Company {
  id: number;
  name: string;
  tax_id: string | null;
  is_personal: boolean;
  created_by: number;
  created_at: string;
  updated_at: string;
}

export interface Experience {
  id: number;
  user_id: number;
  company: string;
  position: string;
  start_date: string;
  end_date: string | null;
  description: string | null;
  approval_status: ApprovalStatus;
  approved_by: number | null;
  approved_at: string | null;
  rejected_reason: string | null;
  created_at: string;
  updated_at: string;
}

export interface Certification {
  id: number;
  user_id: number;
  name: string;
  issuer: string;
  issue_date: string;
  expiry_date: string | null;
  description: string | null;
  file_url: string | null;
  approval_status: ApprovalStatus;
  approved_by: number | null;
  approved_at: string | null;
  rejected_reason: string | null;
  created_at: string;
  updated_at: string;
}

export interface Industry {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  created_at: string;
  updated_at: string;
}

export interface Region {
  id: number;
  name: string;
  slug: string;
  parent_id: number | null;
  created_at: string;
  updated_at: string;
}

// ============================================
// Search Types
// ============================================

export interface SearchParams {
  keyword?: string;
  company?: string;
  industry_id?: number;
  region_id?: number;
  page?: number;
  per_page?: number;
  sort?: 'latest' | 'popular' | 'relevant';
}

export interface SalespersonSearchResult {
  id: number;
  full_name: string;
  phone: string;
  bio: string | null;
  specialties: string | null;
  avatar: string | null;
  company_name: string | null;
  industry_name: string | null;
  service_regions: string[];
  created_at: string;
}

// ============================================
// Approval Status Types
// ============================================

export interface ApprovalStatusData {
  profile_status: ApprovalStatus;
  company_status: ApprovalStatus | null;
  certifications: Array<{
    id: number;
    name: string;
    approval_status: ApprovalStatus;
    rejected_reason: string | null;
  }>;
  experiences: Array<{
    id: number;
    company: string;
    position: string;
    approval_status: ApprovalStatus;
    rejected_reason: string | null;
  }>;
}

// ============================================
// Admin Types
// ============================================

export interface PendingApprovalItem {
  id: number;
  type: 'user' | 'company' | 'certification' | 'experience';
  name: string;
  created_at: string;
  details?: any;
}

export interface Statistics {
  total_salespersons: number;
  active_salespersons: number;
  pending_salespersons: number;
  total_companies: number;
  pending_approvals: number;
}
