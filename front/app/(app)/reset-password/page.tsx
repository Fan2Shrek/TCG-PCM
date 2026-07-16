import ResetPasswordForm from "@/components/molecules/form/ResetPasswordForm";
import { Card } from "@/components/ui/card";

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
    <main className="flex justify-center sm:mt-32">
      <Card className="w-full max-w-md">
        <ResetPasswordForm token={token ?? ""} />
      </Card>
    </main>
  );
}
