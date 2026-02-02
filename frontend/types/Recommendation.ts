export type Recommendation = {
  id: number;
  videoDbId: number; // 自分の動画ID
  videoId: string; // YouTube ID
  title: string;
  description: string;
  channelId: string;
  channelTitle: string;
  thumbnail: {
    url: string;
    width: number;
    height: number;
  };
  publishedAt: string;
  viewCount: number;
  likeCount: number;
  sourceType: "trend" | "playlist";
  recommendDate: string;
  isFavorite: boolean;
  canFavorite: boolean;
  canViewRecommendations: boolean;
  canViewFavorites: boolean;
  canViewHistories: boolean;
};
