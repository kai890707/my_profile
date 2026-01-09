import axios from 'axios';
import Cookies from 'js-cookie';

const ACCESS_TOKEN_KEY = 'access_token';
const REFRESH_TOKEN_KEY = 'refresh_token';
const USER_ROLE_KEY = 'user_role';
const API_BASE_URL = process.env.NEXT_PUBLIC_API_BASE_URL || 'http://localhost:8080/api';

interface RefreshResponse {
  status: 'success';
  data: {
    access_token: string;
    expires_in: number;
  };
  message: string;
}

// 同時設置 localStorage 和 cookies（用於 middleware）
export function getAccessToken(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(ACCESS_TOKEN_KEY) || Cookies.get(ACCESS_TOKEN_KEY) || null;
}

export function setAccessToken(token: string): void {
  if (typeof window === 'undefined') return;
  localStorage.setItem(ACCESS_TOKEN_KEY, token);
  // 設置 cookie（1 小時過期）
  Cookies.set(ACCESS_TOKEN_KEY, token, { expires: 1 / 24, sameSite: 'lax' });
}

export function getRefreshToken(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(REFRESH_TOKEN_KEY) || Cookies.get(REFRESH_TOKEN_KEY) || null;
}

export function setRefreshToken(token: string): void {
  if (typeof window === 'undefined') return;
  localStorage.setItem(REFRESH_TOKEN_KEY, token);
  // 設置 cookie（7 天過期）
  Cookies.set(REFRESH_TOKEN_KEY, token, { expires: 7, sameSite: 'lax' });
}

export function getUserRole(): string | null {
  if (typeof window === 'undefined') return null;
  return localStorage.getItem(USER_ROLE_KEY) || Cookies.get(USER_ROLE_KEY) || null;
}

export function setUserRole(role: string): void {
  if (typeof window === 'undefined') return;
  localStorage.setItem(USER_ROLE_KEY, role);
  // 設置 cookie（7 天過期）
  Cookies.set(USER_ROLE_KEY, role, { expires: 7, sameSite: 'lax' });
}

export function clearTokens(): void {
  if (typeof window === 'undefined') return;
  localStorage.removeItem(ACCESS_TOKEN_KEY);
  localStorage.removeItem(REFRESH_TOKEN_KEY);
  localStorage.removeItem(USER_ROLE_KEY);
  Cookies.remove(ACCESS_TOKEN_KEY);
  Cookies.remove(REFRESH_TOKEN_KEY);
  Cookies.remove(USER_ROLE_KEY);
}

export async function refreshAccessToken(): Promise<string | null> {
  const refreshToken = getRefreshToken();
  if (!refreshToken) return null;

  try {
    const response = await axios.post<RefreshResponse>(
      `${API_BASE_URL}/auth/refresh`,
      { refresh_token: refreshToken }
    );

    const newAccessToken = response.data.data.access_token;
    setAccessToken(newAccessToken);
    return newAccessToken;
  } catch (error) {
    clearTokens();
    return null;
  }
}
