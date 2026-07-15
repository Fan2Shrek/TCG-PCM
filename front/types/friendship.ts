export type FriendshipUser = {
  id: number;
  username: string;
  profilePicturePath?: string | null;
};

export type Friendship = {
  id: string;
  status: "pending" | "accepted";
  requester: FriendshipUser;
  addressee: FriendshipUser;
  createdAt: string;
  respondedAt?: string | null;
};
