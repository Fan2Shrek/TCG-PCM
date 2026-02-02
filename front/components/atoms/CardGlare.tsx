type CardGlareProps = {
  glare: { x: number; y: number };
  isHovering: boolean;
};

const CardGlare = ({ glare, isHovering }: CardGlareProps) => (
  //isHovering conditional bc we only want the animation when the user's cursor leaves the card & it goes back to the center of the card smoothly
  <div className="absolute inset-0 overflow-hidden">
    <div
      className={`w-full h-full pointer-events-none bg-glare-effect mix-blend-screen ${isHovering ? "" : "transition-transform duration-300 ease-[cubic-bezier(.2,.9,.2,1)]"}`}
      style={{
        transform: `translate(${glare.x - 50}%, ${glare.y - 50}%)`,
      }}
    />
  </div>
);

export default CardGlare;
