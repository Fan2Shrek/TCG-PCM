"use client";

import Card from "@/components/molecules/Card";

export default function CardExamples() {
  const baseCardGuppy = {
    id: "guppy",
    frontLayers: [
      { 
        src: "/fsp2-guppy.png", 
        depth: 0,
        foil: "/foil.webp",
        mask: "/mask.webp",
       }],
    backImage: "/default_card_back.png",
  };

  const baseCardIsaac = {
    id: "isaac",
    frontLayers: [
      { src: "/isaac_card_layer_1.png", depth: -20 },
      { src: "/isaac_card_layer_2.webp", depth: -10 },
      { src: "/isaac_card_layer_3.png", depth: 0 },
      { src: "/isaac_card_layer_4.gif", depth: 20 },
    ],
    backImage: "/default_card_back.png",
  }

  const handleHover = (id: string) => {
    console.log("hover", id);
  };

  const handleClick = (id: string) => {
    console.log("click", id);
  };

  return (
    <>
      <div className="flex flex-row flex-wrap w-full gap-10">
        {/* XS - flipped false (back shown) */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">XS (back)</span>
          <Card card={{ ...baseCardGuppy, id: "xs" }} size="xs" onHover={handleHover} onClick={handleClick} />
        </div>

        {/* SM - flipped true */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">SM (front)</span>
          <Card card={{ ...baseCardGuppy, id: "sm" }} size="sm" onHover={handleHover} onClick={handleClick} />
        </div>

        {/* MD - default */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">MD (default)</span>
          <Card card={{ ...baseCardGuppy, id: "md" }} size="md" onHover={handleHover} onClick={handleClick} />
        </div>

        {/* LG - non-interactive */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">LG (non-interactive, back)</span>
          <Card card={{ ...baseCardGuppy, id: "lg" }} size="lg" interactive={false} onClick={handleClick} />
        </div>

        {/* XL - big, front */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">XL (preview)</span>
          <Card card={{ ...baseCardGuppy, id: "xl" }} size="xl" onHover={handleHover} onClick={handleClick} />
        </div>

        {/* card with multiple layers */}
        <div className="flex flex-col items-center gap-2">
          <span className="text-sm text-zinc-600">XL (multiple layers)</span>
          <Card card={{ ...baseCardIsaac, id: "xl-isaac" }} size="xl" onHover={handleHover} onClick={handleClick} />
        </div>
      </div>
    </>
  );
}
