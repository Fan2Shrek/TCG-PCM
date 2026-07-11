import LoginForm from "@/components/molecules/form/LoginForm";

export default function Home() {
  return (
    <main className="flex  justify-center sm:mt-32 ">
      <div className="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl border border-black/10">
        <LoginForm />
      </div>
    </main>
  );
}
