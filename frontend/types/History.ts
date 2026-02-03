export type History = {
  id: number;
  userId: number;
  viewedAt: string;
  videoDbId: number;
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
};
