import { toast } from 'sonner';
import type { AxiosError } from 'axios';

/**
 * API 錯誤響應類型
 */
export interface ApiErrorResponse {
  status: 'error';
  message: string;
  errors?: Record<string, string[]>;
}

/**
 * 錯誤訊息映射
 */
const ERROR_MESSAGES: Record<string, string> = {
  // 網絡錯誤
  'ERR_NETWORK': '網絡連接失敗，請檢查您的網絡設置',
  'ECONNABORTED': '請求超時，請稍後再試',

  // HTTP 狀態碼
  '400': '請求參數錯誤',
  '401': '未登入或登入已過期',
  '403': '沒有權限執行此操作',
  '404': '請求的資源不存在',
  '409': '資源衝突',
  '422': '輸入驗證失敗',
  '429': '請求過於頻繁，請稍後再試',
  '500': '伺服器錯誤，請稍後再試',
  '502': '伺服器錯誤，請稍後再試',
  '503': '服務暫時無法使用，請稍後再試',
  '504': '伺服器響應超時，請稍後再試',
};

/**
 * 取得友善的錯誤訊息
 */
export function getErrorMessage(error: unknown): string {
  if (!error) {
    return '發生未知錯誤';
  }

  // Axios 錯誤
  if (isAxiosError(error)) {
    const axiosError = error as AxiosError<ApiErrorResponse>;

    // 使用後端返回的錯誤訊息
    if (axiosError.response?.data?.message) {
      return axiosError.response.data.message;
    }

    // HTTP 狀態碼對應的訊息
    if (axiosError.response?.status) {
      const statusMessage = ERROR_MESSAGES[axiosError.response.status.toString()];
      if (statusMessage) {
        return statusMessage;
      }
    }

    // 網絡錯誤
    if (axiosError.code) {
      const networkMessage = ERROR_MESSAGES[axiosError.code];
      if (networkMessage) {
        return networkMessage;
      }
    }

    // 通用 Axios 錯誤訊息
    if (axiosError.message) {
      return axiosError.message;
    }
  }

  // Error 對象
  if (error instanceof Error) {
    return error.message;
  }

  // 字串錯誤
  if (typeof error === 'string') {
    return error;
  }

  return '發生未知錯誤';
}

/**
 * 檢查是否為 Axios 錯誤
 */
function isAxiosError(error: unknown): error is AxiosError {
  return (error as AxiosError).isAxiosError === true;
}

/**
 * 顯示錯誤通知
 */
export function showErrorToast(error: unknown, fallbackMessage?: string): void {
  const message = getErrorMessage(error);
  toast.error(fallbackMessage || message);
}

/**
 * 取得表單驗證錯誤
 */
export function getValidationErrors(error: unknown): Record<string, string> | null {
  if (!isAxiosError(error)) {
    return null;
  }

  const axiosError = error as AxiosError<ApiErrorResponse>;
  const errors = axiosError.response?.data?.errors;

  if (!errors) {
    return null;
  }

  // 將 { field: ['error1', 'error2'] } 轉換為 { field: 'error1' }
  const formattedErrors: Record<string, string> = {};
  Object.entries(errors).forEach(([field, messages]) => {
    if (Array.isArray(messages) && messages.length > 0) {
      formattedErrors[field] = messages[0];
    }
  });

  return formattedErrors;
}

/**
 * 處理 API 錯誤
 * - 顯示 Toast 通知
 * - 記錄錯誤（可選）
 * - 返回格式化的錯誤訊息
 */
export function handleApiError(
  error: unknown,
  options?: {
    showToast?: boolean;
    fallbackMessage?: string;
    onError?: (message: string) => void;
  }
): string {
  const message = getErrorMessage(error);

  // 顯示 Toast
  if (options?.showToast !== false) {
    toast.error(options?.fallbackMessage || message);
  }

  // 自定義錯誤處理
  if (options?.onError) {
    options.onError(message);
  }

  // 開發環境下記錄錯誤
  if (process.env.NODE_ENV === 'development') {
    console.error('API Error:', error);
  }

  return message;
}

/**
 * 檢查是否為認證錯誤（401）
 */
export function isAuthError(error: unknown): boolean {
  if (!isAxiosError(error)) {
    return false;
  }

  const axiosError = error as AxiosError;
  return axiosError.response?.status === 401;
}

/**
 * 檢查是否為權限錯誤（403）
 */
export function isForbiddenError(error: unknown): boolean {
  if (!isAxiosError(error)) {
    return false;
  }

  const axiosError = error as AxiosError;
  return axiosError.response?.status === 403;
}

/**
 * 檢查是否為驗證錯誤（422）
 */
export function isValidationError(error: unknown): boolean {
  if (!isAxiosError(error)) {
    return false;
  }

  const axiosError = error as AxiosError;
  return axiosError.response?.status === 422;
}
