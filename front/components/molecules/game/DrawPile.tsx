"use client";

type DrawPileProps = {
  numCards: number;
  className?: string;
};

export default function DrawPile({ numCards, className = "" }: DrawPileProps) {
  return (
    <div className={` rounded-xl flex flex-col items-center justify-center p-2 ${className}`}>
      <div className={`relative w-card-md aspect-card`}>
        <div className='absolute inset-0 bg-gray-400 rounded-lg transform -rotate-3 opacity-70'></div>
        <div className='absolute inset-0 bg-gray-500 rounded-lg transform rotate-2 opacity-85'></div>
        <div className='absolute inset-0 bg-gray-600 rounded-lg border-2 border-gray-700 flex items-center justify-center text-white font-bold text-lg'>{numCards}</div>
      </div>
    </div>
  );
}
