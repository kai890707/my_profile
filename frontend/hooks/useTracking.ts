import { useEffect } from 'react';
import { useMutation } from '@tanstack/react-query';
import * as eventsApi from '@/lib/api/events';
import type { EventType } from '@/lib/api/events';

// ========== Event Tracking Hooks ==========

/**
 * 追蹤事件 Mutation Hook
 * 注意：此 hook 不會拋出錯誤，也不會阻塞 UI
 */
export function useTrackEventMutation() {
  return useMutation({
    mutationFn: eventsApi.trackEvent,
    // No error handling - silent failure
    onError: () => {
      // Silently ignore errors
    },
  });
}

/**
 * 自動追蹤事件 Hook
 * 在組件掛載時自動發送追蹤事件
 *
 * @param eventType - 事件類型
 * @param salespersonId - 業務員 ID
 * @param enabled - 是否啟用追蹤（預設：true）
 */
export function useTrackEvent(
  eventType: EventType,
  salespersonId: number,
  enabled: boolean = true
) {
  const { mutate } = useTrackEventMutation();

  useEffect(() => {
    if (enabled && salespersonId) {
      mutate({
        event_type: eventType,
        salesperson_id: salespersonId,
      });
    }
  }, [eventType, salespersonId, enabled, mutate]);
}

/**
 * 手動追蹤事件 Hook
 * 返回一個函數，可以手動調用來追蹤事件
 */
export function useTrackEventManually() {
  const { mutate } = useTrackEventMutation();

  return (eventType: EventType, salespersonId: number) => {
    mutate({
      event_type: eventType,
      salesperson_id: salespersonId,
    });
  };
}
