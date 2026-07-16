import LoginForm from "@/components/molecules/form/LoginForm";
import { Card } from "@/components/ui/card";

export default function Home() {
  return (
    <main className="flex justify-center sm:mt-32">
      <Card className="w-full max-w-md">
        <LoginForm />
      </Card>
    </main>
  );
}
