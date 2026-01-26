// ============================================
// Common Types
// ============================================

/**
 * 時間範圍類型
 */
export type TimeRange = 'today' | '7days' | '30days';

/**
 * 聯繫狀態類型
 */
export type ContactStatus = 'pending' | 'contacted' | 'closed';

// ============================================
// 業務員 Dashboard Types
// ============================================

/**
 * 業務員統計數據
 */
export interface SalespersonStats {
  profile_views: number;          // 總瀏覽數
  contact_requests: number;       // 總聯繫數
  growth_rate: number;            // 增長率（%）
  period: TimeRange;              // 統計時間範圍
  previous_profile_views: number; // 上一期瀏覽數
  previous_contact_requests: number; // 上一期聯繫數
}

/**
 * 趨勢數據點
 */
export interface TrendData {
  date: string;                   // 日期 (YYYY-MM-DD)
  profile_views: number;          // 瀏覽數
  contact_requests: number;       // 聯繫數
  unique_visitors?: number;       // 獨立訪客數（可選）
}

/**
 * 聯繫記錄
 */
export interface Contact {
  id: number;
  customer_name: string;          // 客戶姓名
  customer_email: string;         // 客戶 Email
  customer_phone?: string;        // 客戶電話（可選）
  message: string;                // 聯繫訊息
  status: ContactStatus;          // 狀態
  created_at: string;             // 建立時間 (ISO 8601)
  updated_at: string;             // 更新時間
}

// ============================================
// 管理員 Dashboard Types
// ============================================

/**
 * 平台整體概覽
 */
export interface AdminOverview {
  total_salespersons: number;          // 總業務員數
  total_profile_views: number;         // 總瀏覽數
  total_contact_requests: number;      // 總聯繫數
  platform_conversion_rate: number;    // 平台轉換率（%）
  period: string;                      // 統計時間範圍
}

/**
 * 業務員資訊（Top 10）
 */
export interface Salesperson {
  id: number;
  name: string;                   // 業務員姓名
  email: string;                  // 業務員 Email
  profile_views: number;          // 瀏覽數
  contact_requests: number;       // 聯繫數
  conversion_rate: number;        // 轉換率（%）
  rank: number;                   // 排名
}

/**
 * 業務員活躍度
 */
export interface Activity {
  active_salespersons: number;        // 活躍業務員數（過去 7 天有瀏覽）
  inactive_salespersons: number;      // 低活躍業務員數（0 瀏覽）
  total_salespersons: number;         // 總業務員數
  activity_rate: number;              // 活躍率（%）
  period: string;                     // 統計時間範圍
}

// ============================================
// API Response Wrappers
// ============================================

/**
 * 成功回應格式
 */
export interface ApiSuccessResponse<T> {
  success: true;
  data: T;
  message?: string;
}

/**
 * 錯誤回應格式
 */
export interface ApiErrorResponse {
  success: false;
  error: {
    code: string;
    message: string;
    details?: Record<string, string[]>;
  };
}

/**
 * 通用 API 回應類型
 */
export type ApiResponse<T> = ApiSuccessResponse<T> | ApiErrorResponse;
