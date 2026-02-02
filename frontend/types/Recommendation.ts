export type Recommendation = {
  id: number;
  videoId: string;
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
