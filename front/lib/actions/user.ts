"use server";

import { revalidatePath } from "next/cache";

import { serverApiPostFormData } from "@/lib/api/server";

export type ProfilePictureActionState = {
  error: string | null;
  profilePicturePath?: string;
};

const ALLOWED_TYPES = ["image/jpeg", "image/png", "image/webp"];
const MAX_FILE_SIZE = 5 * 1024 * 1024;

export async function updateProfilePictureAction(
  _prevState: ProfilePictureActionState,
  formData: FormData,
): Promise<ProfilePictureActionState> {
  const file = formData.get("profilePicture");

  if (!(file instanceof File) || 0 === file.size) {
    return { error: "Veuillez sélectionner une image." };
  }

  if (!ALLOWED_TYPES.includes(file.type)) {
    return { error: "Format non supporté. Utilisez JPEG, PNG ou WEBP." };
  }

  if (file.size > MAX_FILE_SIZE) {
    return { error: "L'image est trop volumineuse (5 Mo maximum)." };
  }

  try {
    const response = await serverApiPostFormData<{ profilePicturePath: string }>(
      "/user/profile_picture",
      formData,
    );
    revalidatePath("/", "layout");

    return { error: null, profilePicturePath: response.profilePicturePath };
  } catch (err) {
    return { error: err instanceof Error ? err.message : "Une erreur est survenue." };
  }
}
