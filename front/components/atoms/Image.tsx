import NextImage, { ImageProps as NextImageProps } from "next/image";
import type { ReactElement } from "react";

export type ImageProps = NextImageProps;

const Image = ({ src, ...rest }: ImageProps): ReactElement | null => {
  const normalizedSrc =
    typeof src === "string" && src.trim() === "" ? null : src;

  if (normalizedSrc === null || normalizedSrc === undefined) {
    return null;
  }

  return <NextImage src={normalizedSrc as ImageProps["src"]} {...rest} />;
};

export default Image;
