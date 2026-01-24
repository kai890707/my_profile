import { useQuery, useMutation, useQueryClient } from '@tanstack/react-query';
import { toast } from 'sonner';
import * as contactApi from '@/lib/api/contact';
import { trackEvent } from '@/lib/api/events';

// ========== Query Keys ==========

export const contactKeys = {
  contactInfo: (salespersonId: number) => ['contact', 'info', salespersonId] as const,
  contactRequests: ['contact', 'requests'] as const,
};

// ========== Contact Info Hooks ==========

/**
 * 取得業務員公開聯繫資訊
 */
export function useContactInfo(salespersonId: number) {
  return useQuery({
    queryKey: contactKeys.contactInfo(salespersonId),
    queryFn: async () => {
      const response = await contactApi.getContactInfo(salespersonId);
      return response.data;
    },
    enabled: !!salespersonId,
  });
}

// ========== Contact Request Hooks ==========

/**
 * 提交聯繫請求
 */
export function useCreateContactRequest() {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: contactApi.createContactRequest,
    onSuccess: (response, variables) => {
      queryClient.invalidateQueries({ queryKey: contactKeys.contactRequests });
      toast.success(response.message || '聯繫請求已送出！業務員將盡快回覆您。');

      // Track contact form submission event
      trackEvent({
        event_type: 'contact_form_submission',
        salesperson_id: variables.salesperson_id,
      });
    },
    onError: (error: any) => {
      const status = error.response?.status;
      const message = error.response?.data?.message || error.message;

      // Handle specific error cases
      if (status === 429) {
        // Rate limit error
        const errorMessage = message || '您的操作過於頻繁，請稍後再試';
        toast.error(errorMessage, {
          duration: 5000,
        });
      } else if (status === 422) {
        // Validation error
        const errors = error.response?.data?.errors;
        if (errors) {
          // Display first validation error
          const firstError = Object.values(errors)[0];
          if (Array.isArray(firstError) && firstError.length > 0) {
            toast.error(firstError[0]);
          } else {
            toast.error(message || '請檢查輸入的資料');
          }
        } else {
          toast.error(message || '請檢查輸入的資料');
        }
      } else if (status === 401) {
        // Authentication error
        toast.error('請先登入後再進行此操作');
      } else {
        // Generic error
        toast.error(message || '送出失敗，請稍後再試');
      }
    },
  });
}
