import { apiClient } from './client';
import { setAccessToken, setRefreshToken, clearTokens } from '@/lib/auth/token';
import type {
  RegisterRequest,
  LoginRequest,
  User,
  ApiResponse,
  AuthResponse,
} from '@/types/api';

/**
 * 業務員註冊
 */
export async function register(data: RegisterRequest): Promise<ApiResponse<{ user: User; profile: any }>> {
  const response = await apiClient.post<ApiResponse<{ user: User; profile: any }>>('/auth/register', data);
  return response.data;
}

/**
 * 使用者登入
 */
export async function login(credentials: LoginRequest): Promise<ApiResponse<AuthResponse>> {
  const response = await apiClient.post<ApiResponse<AuthResponse>>('/auth/login', credentials);

  // 儲存 Token
  if (response.data.data) {
    const { access_token, refresh_token } = response.data.data;
    setAccessToken(access_token);
    setRefreshToken(refresh_token);
  }

  return response.data;
}

/**
 * 使用者登出
 */
export async function logout(): Promise<void> {
  try {
    await apiClient.post('/auth/logout');
  } finally {
    // 無論成功失敗都清除本地 Token
    clearTokens();
  }
}

/**
 * 取得當前使用者資訊
 */
export async function getCurrentUser(): Promise<ApiResponse<User>> {
  const response = await apiClient.get<ApiResponse<User>>('/auth/me');
  return response.data;
}
