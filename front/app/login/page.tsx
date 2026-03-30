import LoginForm from "@/components/molecules/form/LoginForm";
import CardExamples from "@/components/organisms/CardExamples";
import HandExample from "@/components/organisms/HandExample";

export default function Home() {
  return (
      <main className="flex flex-col items-center gap-12 p-24 sm:items-start">
        <LoginForm />
      </main>
  );
}
