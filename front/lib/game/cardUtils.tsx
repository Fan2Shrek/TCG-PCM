import { createElement, type ReactNode } from "react";

const markupMapping: Record<
  string,
  (content: string, key: number) => ReactNode
> = {
  value: (content, key) =>
    createElement(
      "span",
      { key, className: "font-bold text-amber-300" },
      content,
    ),
  effect: (content, key) =>
    createElement(
      "span",
      { key, className: "font-bold text-sky-300" },
      content,
    ),
  const: (content, key) =>
    createElement(
      "span",
      { key, className: "font-bold text-zinc-200" },
      content,
    ),
  card: (content, key) =>
    createElement(
      "span",
      { key, className: "font-bold text-fuchsia-300" },
      content,
    ),
};

export const convertDescriptions = (description: string): ReactNode[] => {
  const regex = /<(\w+)>(.*?)<\/\1>/g;

  const result: ReactNode[] = [];
  let lastIndex = 0;
  let match;
  let key = 0;

  while ((match = regex.exec(description)) !== null) {
    const [fullMatch, tag, content] = match;

    if (match.index > lastIndex) {
      result.push(description.slice(lastIndex, match.index));
    }

    if (markupMapping[tag]) {
      result.push(markupMapping[tag](content, key++));
    } else {
      result.push(content);
    }

    lastIndex = match.index + fullMatch.length;
  }

  if (lastIndex < description.length) {
    result.push(description.slice(lastIndex));
  }

  return result;
};
