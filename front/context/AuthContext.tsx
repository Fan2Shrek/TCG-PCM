'use client';

import { createContext, useContext, useEffect, useState, ReactNode } from "react";
import { getCurrentUser } from "@/lib/utils";

type User = {
  username: string;
};

type AuthContextType = {
  user: User | null;
  isAuthenticated: boolean;
  loading: boolean;
  login: (token: string) => void;
  logout: () => void;
};

export const AuthContext = createContext<AuthContextType>({
  user: null,
  isAuthenticated: false,
  loading: true,
  login: () => {},
  logout: () => {},
});

export const useAuth = () => useContext(AuthContext);

export const AuthProvider = ({ children }: { children: ReactNode }) => {
  const [user, setUser] = useState<User | null>(null);
  const [loading, setLoading] = useState(true);

  useEffect(() => {
    setUser(getCurrentUser());
    setLoading(false);
  }, []);

  const login = (token: string) => {
    document.cookie = `token=${token}; path=/; max-age=36000; secure; samesite=strict`;
    setUser(getCurrentUser());
  };

  const logout = () => {
    document.cookie = `token=; path=/; max-age=0`;
    setUser(null);
  };

  return (
    <AuthContext.Provider value={{ user, isAuthenticated: !!user, loading, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
};
