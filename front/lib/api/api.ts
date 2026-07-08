const baseUrl = process.env.NEXT_PUBLIC_API_URL || "http://localhost:8000/api";

export const getImage = (img: string) => {
  try {
    new URL(img);
    return img;
  } catch {
    return `${baseUrl.replaceAll("api", "")}${img}`;
  }
};
