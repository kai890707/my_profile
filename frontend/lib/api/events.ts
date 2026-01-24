import { apiClient } from './client';
import type { ApiResponse } from '@/types/api';

// ========== Event Tracking APIs ==========

export type EventType = 'profile_view' | 'contact_form_submission';

export interface TrackEventRequest {
  event_type: EventType;
  salesperson_id: number;
}

/**
 * 追蹤事件（不需要認證，可匿名）
 * 此 API 不會阻塞 UI，且不會拋出錯誤
 */
export async function trackEvent(data: TrackEventRequest): Promise<void> {
  try {
    // Fire and forget - 不等待回應，不處理錯誤
    apiClient.post<ApiResponse<void>>('/events/track', data);
  } catch (error) {
    // Silently ignore errors for tracking
    console.debug('Event tracking failed:', error);
  }
}
