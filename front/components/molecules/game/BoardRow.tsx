type BoardRowProps = {
  title: string;
  cards: string[];
};

export default ({ title, cards }: BoardRowProps) => {
  return (
    <div className="flex flex-col items-center gap-2">
      <div className="text-sm opacity-70">{title}</div>

      <div className="flex gap-2">
		<p>cards</p>
		<p>cards</p>
		<p>cards</p>
		<p>cards</p>
      </div>
    </div>
  );
}
