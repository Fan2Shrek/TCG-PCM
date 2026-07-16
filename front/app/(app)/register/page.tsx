import RegisterForm from "@/components/molecules/form/RegisterForm";
import { Card } from "@/components/ui/card";

export default function Register() {
  return (
    <main className="flex justify-center sm:mt-32">
      <Card className="w-full max-w-md">
        <RegisterForm />
      </Card>
    </main>
  );
}
