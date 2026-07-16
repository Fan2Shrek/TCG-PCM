import ForgotPasswordForm from "@/components/molecules/form/ForgotPasswordForm";
import { Card } from "@/components/ui/card";

export default function ForgotPasswordPage() {
  return (
    <main className="flex justify-center sm:mt-32">
      <Card className="w-full max-w-md">
        <ForgotPasswordForm />
      </Card>
    </main>
  );
}
