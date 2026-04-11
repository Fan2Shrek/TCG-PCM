export const getCurrentUser = () => {
  const token = document.cookie.split("; ").find(row => row.startsWith("token="))?.split("=")[1];
  if (!token) return null;

  const payload = JSON.parse(atob(token.split(".")[1]));
  return {
	  username: payload.username,
  };
}
