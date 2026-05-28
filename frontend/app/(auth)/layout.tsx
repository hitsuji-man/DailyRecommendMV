import Header from "@/components/Header";

export default function AuthLayout({
  children,
}: {
  children: React.ReactNode;
}) {
  return (
    <>
      <Header />
      <main className="pt-[72px] md:pt-0">{children}</main>
    </>
  );
}
