export type FavoriteVideo = {
  userId: number;
  videoDbId: number;
  videoId: string;
  title: string;
  channelTitle: string;
  thumbnail: {
    url: string;
    width: number;
    height: number;
  };
  publishedAt: string;
  viewCount: number;
  likeCount: number;
  isFavorite: boolean;
  canViewFavorites: boolean;
};
