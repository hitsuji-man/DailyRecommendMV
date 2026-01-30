import VideoDetailView from "./VideoDetailView";

export default async function VideoDetailPage({
  params,
}: {
  params: Promise<{ id: string }>;
}) {
  const { id } = await params;

  return <VideoDetailView videoId={id} />;
}
