"use client";

import Card from "@/components/molecules/Card";

export default function CardExamples() {
  const baseCard = {
    id: "guppy",
    frontLayers: [{ src: "/fsp2-guppy.png", depth: 1 }],
    backImage: "/charactercardback.png",
  };

  const handleHover = (pos: { x: number; y: number }, id: string) => {
    console.log("hover", id, pos);
  };

  const handleClick = (pos: { x: number; y: number }, id: string) => {
    console.log("click", id, pos);
  };

  return (
    <>
      <div className="flex flex-row flex-wrap w-full gap-10">
        {/* XS - flipped false (back shown) */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">XS (back)</span>
          <Card card={{ ...baseCard, id: "xs" }} isFlipped={false} size="xs" onHover={handleHover} onClick={handleClick} />
        </div>

        {/* SM - flipped true */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">SM (front)</span>
          <Card card={{ ...baseCard, id: "sm" }} isFlipped={true} size="sm" onHover={handleHover} onClick={handleClick} />
        </div>

        {/* MD - default */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">MD (default)</span>
          <Card card={{ ...baseCard, id: "md" }} isFlipped={true} size="md" onHover={handleHover} onClick={handleClick} />
        </div>

        {/* LG - non-interactive, shows back */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">LG (non-interactive, back)</span>
          <Card card={{ ...baseCard, id: "lg" }} isFlipped={false} size="lg" interactive={false} onClick={handleClick} />
        </div>

        {/* XL - big, front */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">XL (preview)</span>
          <Card card={{ ...baseCard, id: "xl" }} isFlipped={true} size="xl" onHover={handleHover} onClick={handleClick} />
        </div>
      </div>

      <p className="text-sm text-zinc-500">Open the console to see hover/click position events for interactive examples.</p>
    </>
  );
}
