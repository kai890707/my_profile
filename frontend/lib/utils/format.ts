/**
 * 格式化日期
 */
export function formatDate(date: string | Date | null | undefined): string {
  if (!date) {
    return '-';
  }

  const dateObj = new Date(date);

  // 檢查日期是否有效
  if (isNaN(dateObj.getTime())) {
    return '-';
  }

  return new Intl.DateTimeFormat('zh-TW', {
    year: 'numeric',
    month: '2-digit',
    day: '2-digit',
  }).format(dateObj);
}

/**
 * 格式化相對時間
 */
export function formatRelativeTime(date: string | Date | null | undefined): string {
  if (!date) {
    return '-';
  }

  const now = new Date();
  const target = new Date(date);

  // 檢查日期是否有效
  if (isNaN(target.getTime())) {
    return '-';
  }

  const diffInSeconds = Math.floor((now.getTime() - target.getTime()) / 1000);

  if (diffInSeconds < 60) return '剛剛';
  if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} 分鐘前`;
  if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} 小時前`;
  if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} 天前`;

  return formatDate(date);
}
