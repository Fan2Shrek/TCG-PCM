import NextImage, { ImageProps as NextImageProps } from "next/image";
import type { ReactElement } from "react";

export type ImageProps = NextImageProps;

const Image = ({ ...rest }: ImageProps): ReactElement => {
  return <NextImage {...rest} />;
};

export default Image;
