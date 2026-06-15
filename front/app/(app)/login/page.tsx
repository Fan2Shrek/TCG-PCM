import LoginForm from "@/components/molecules/form/LoginForm";

export default function Home() {
  return (
      <main className="flex flex-col items-center gap-12 p-24 sm:items-start">
        <LoginForm />
      </main>
  );
}
