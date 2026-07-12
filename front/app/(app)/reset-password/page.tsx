import ResetPasswordForm from "@/components/molecules/form/ResetPasswordForm";

type ResetPasswordPageProps = {
  searchParams: Promise<{
    token?: string;
  }>;
};

export default async function ResetPasswordPage({
  searchParams,
}: ResetPasswordPageProps) {
  const { token } = await searchParams;

  return (
    <main className="flex  justify-center sm:mt-32 ">
      <div className="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl border border-black/10">
        <ResetPasswordForm token={token ?? ""} />
      </div>
    </main>
  );
}
