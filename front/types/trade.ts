export type TradeUser = {
  id: number;
  username: string;
  profilePicturePath?: string | null;
};

export type Trade = {
  id: string;
  status: "active" | "completed" | "cancelled";
  initiator: TradeUser;
  recipient: TradeUser;
  initiatorCard: string | null;
  recipientCard: string | null;
  initiatorConfirmed: boolean;
  recipientConfirmed: boolean;
  createdAt: string;
  completedAt?: string | null;
};
