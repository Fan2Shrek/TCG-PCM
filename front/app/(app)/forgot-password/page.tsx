import ForgotPasswordForm from "@/components/molecules/form/ForgotPasswordForm";

export default function ForgotPasswordPage() {
  return (
    <main className="flex  justify-center sm:mt-32 ">
      <div className="w-full max-w-md rounded-2xl bg-white p-8 shadow-xl border border-black/10">
        <ForgotPasswordForm />
      </div>
    </main>
  );
}
