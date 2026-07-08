export type WaitingRoom = {
  id: string;
  createdAt: string;
  updatedAt: string;
  player1: {
    id: string;
    name: string;
  };
};

export type WaitingRoomsResponse = {
  "hydra:member": WaitingRoom[];
  "hydra:totalItems": number;
  "hydra:view": {
    "@id": string;
    "@type": "hydra:PartialCollectionView";
    "hydra:first": string;
    "hydra:last": string;
    "hydra:next"?: string;
  };
};
