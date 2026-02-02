import CardExamples from "@/components/organisms/CardExamples";

export default function Home() {
  // Examples rendered in a client component to allow event handlers

  return (
      <main className="flex flex-col items-center gap-12 p-24 bg-white sm:items-start">
        <CardExamples />
      </main>
  );
}
