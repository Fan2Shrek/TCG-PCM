type Props = {
  player: any;
};

export default ({ player }: Props) => {
  return (
    <div className="flex items-center gap-6 bg-green-800 p-3 rounded-lg">
      <div className="font-bold">{player.player.name}</div>

      <div>Coins: {player.coins}</div>

      <div>Cards in hand: {player.hand.length}</div>

      <div>Deck: {player.drawPile.length}</div>

      <div>Discard: {player.discardPile.length}</div>
    </div>
  );
}
