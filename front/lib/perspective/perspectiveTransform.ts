export function transformViewportToBoardSpace(pointerPos: { x: number; y: number }) {
  const board = document.querySelector(".game-board") as HTMLElement;
  if (!board) return pointerPos;

  const rect = board.getBoundingClientRect();

  const style = getComputedStyle(board);

  // THIS includes scale() in computed transform in most browsers
  const transform = style.transform === "none" ? new DOMMatrix() : new DOMMatrix(style.transform);

  const point = new DOMPoint(pointerPos.x - rect.left, pointerPos.y - rect.top);

  const local = point.matrixTransform(transform.inverse());

  console.log(local);
  return {
    x: local.x,
    y: local.y,
  };
}
