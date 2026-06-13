import { clsx, type ClassValue } from "clsx"
import { twMerge } from "tailwind-merge"

export function cn(...inputs: ClassValue[]) {
  return twMerge(clsx(inputs))
}

export const getToken = () => {
  if (typeof document === "undefined") return null;

  return document.cookie.split("; ").find(row => row.startsWith("token="))?.split("=")[1] || null;
}

export const getCurrentUser = () => {
  const token = getToken();
  if (!token) return null;

  const payload = JSON.parse(atob(token.split(".")[1]));
  return {
	  username: payload.username,
  };
}
