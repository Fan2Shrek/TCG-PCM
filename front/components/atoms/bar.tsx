type Props = {
	value: number;
	total: number;
	text?: string;
}

export default ({ value, total, text }: Props) => {
	const percentage = (value / total) * 100;

	console.log(text)

	return (
		<div className="w-full bg-gray-300 rounded-full h-4 relative">
			<div
				className="bg-green-500 h-4 rounded-full"
				style={{ width: `${percentage}%` }}
			></div>

			{text && (
				<div className="absolute inset-0 flex items-center justify-center text-xs font-medium text-black">
					{text}
				</div>
			)}
		</div>
	);
}
