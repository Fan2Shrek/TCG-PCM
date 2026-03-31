import CardExamples from "@/components/organisms/CardExamples";
import HandExample from "@/components/organisms/HandExample";

export default function Home() {
  // Examples rendered in a client component to allow event handlers

  return (
      <main className="flex flex-col items-center gap-12 p-24 sm:items-start">
        <HandExample />
      </main>
  );
}
