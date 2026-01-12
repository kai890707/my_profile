/**
 * 生成 Avatar 的 fallback 文字
 *
 * 優先級: full_name > name > username > email > 'U'
 *
 * @param user - 包含使用者名稱資訊的物件
 * @returns 2 個字元的 fallback 文字（或預設 'U'）
 *
 * @example
 * getAvatarFallback({ full_name: '張三' }) // '張三'
 * getAvatarFallback({ full_name: null, username: 'john' }) // 'JO'
 * getAvatarFallback({}) // 'U'
 */
export function getAvatarFallback(user: {
  full_name?: string | null;
  name?: string | null;
  username?: string | null;
  email?: string | null;
}): string {
  // 嘗試從 full_name 取得
  const fullName = user.full_name?.trim();
  if (fullName && fullName.length >= 2) {
    return fullName.substring(0, 2).toUpperCase();
  }
  if (fullName && fullName.length === 1) {
    return fullName.toUpperCase();
  }

  // 嘗試從 name 取得
  const name = user.name?.trim();
  if (name && name.length >= 2) {
    return name.substring(0, 2).toUpperCase();
  }
  if (name && name.length === 1) {
    return name.toUpperCase();
  }

  // 嘗試從 username 取得
  const username = user.username?.trim();
  if (username && username.length >= 2) {
    return username.substring(0, 2).toUpperCase();
  }
  if (username && username.length === 1) {
    return username.toUpperCase();
  }

  // 嘗試從 email 取得
  const email = user.email?.trim();
  if (email && email.length >= 2) {
    return email.substring(0, 2).toUpperCase();
  }
  if (email && email.length === 1) {
    return email.toUpperCase();
  }

  // 最終 fallback
  return 'U';
}
