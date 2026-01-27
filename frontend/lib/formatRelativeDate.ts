export function formatRelativeDate(dateString: string): string {
  const now = new Date();
  const date = new Date(dateString);

  const diffMs = now.getTime() - date.getTime();
  const diffSec = Math.floor(diffMs / 1000);
  const diffMin = Math.floor(diffSec / 60);
  const diffHour = Math.floor(diffMin / 60);
  const diffDay = Math.floor(diffHour / 24);

  if (diffSec < 60) {
    return `${diffSec}秒前`;
  }

  if (diffMin < 60) {
    return `${diffMin}分前`;
  }

  if (diffHour < 24) {
    return `${diffHour}時間前`;
  }

  if (diffDay < 7) {
    return `${diffDay}日前`;
  }

  if (diffDay < 30) {
    return `${Math.floor(diffDay / 7)}週間前`;
  }

  if (diffDay < 365) {
    return `${Math.floor(diffDay / 30)}か月前`;
  }

  return `${Math.floor(diffDay / 365)}年前`;
}
