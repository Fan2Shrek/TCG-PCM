'use client';

import React, { useState } from 'react';
import HoverableCard from '@/components/molecules/HoverableCard';
import CardsHand from './CardsHand';
import { foilEffects } from '../types/card';

const baseCardGuppy = {
  id: 'guppy',
  frontLayers: [
    {
      src: '/fsp2-guppy.png',
      depth: 0,
      foilEffect: foilEffects.RAINBOW,
      foil: '/foil.webp',
      mask: '/mask.webp',
    },
  ],
  backImage: '/default_card_back.png',
};

export default function HandExample() {
  const [hand, setHand] = useState<Array<typeof baseCardGuppy>>([baseCardGuppy, baseCardGuppy, baseCardGuppy, baseCardGuppy, baseCardGuppy, baseCardGuppy]);

  const MAX_HAND = 40;

  const addCard = (n = 1) => {
    setHand((h) => {
      const canAdd = Math.max(0, Math.min(n, MAX_HAND - h.length));
      if (canAdd === 0) return h;
      const additions = Array.from({ length: canAdd }, () => ({ ...baseCardGuppy, id: `hand-${Date.now()}-${Math.random().toString(36).slice(2,8)}` }));
      return [...h, ...additions];
    });
  };

  const removeCard = (n = 1) => {
    setHand((h) => {
      if (h.length === 0) return h;
      const toKeep = Math.max(0, h.length - n);
      return h.slice(0, toKeep);
    });
  };

  const handleHover = (id: string) => {
    console.log('hover', id);
  };

  const handleClick = (id: string) => {
    console.log('click', id);
  };

  return (
    <div className="flex flex-col justify-center items-center w-full gap-12">
      <div className="mb-6 flex gap-2">
        <button onClick={() => addCard(1)} disabled={hand.length >= MAX_HAND} className="px-4 py-2 bg-green-500 text-white rounded disabled:opacity-50">
          +1
        </button>
        <button onClick={() => addCard(10)} disabled={hand.length >= MAX_HAND} className="px-4 py-2 bg-green-600 text-white rounded disabled:opacity-50">
          +10
        </button>
        <button onClick={() => removeCard(1)} disabled={hand.length === 0} className="px-4 py-2 bg-red-500 text-white rounded disabled:opacity-50">
          -1
        </button>
        <button onClick={() => removeCard(10)} disabled={hand.length === 0} className="px-4 py-2 bg-red-600 text-white rounded disabled:opacity-50">
          -10
        </button>
      </div>
      <div className="absolute bottom-5">
        <CardsHand cards={hand} />
      </div>
    </div>
  );
}
