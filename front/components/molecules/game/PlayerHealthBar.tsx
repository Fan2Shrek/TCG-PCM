import Bar from "@/components/atoms/bar";

type Props = {
  health: number;
  maxHealth: number;
}

export default ({ health, maxHealth }: Props) => {
  return (
	  <div>
		<Bar value={health} total={maxHealth} text={`${health} / ${maxHealth}`} />
	  </div>
	);
}
