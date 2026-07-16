"use client";

import { useActionState, useState } from "react";
import { useFormStatus } from "react-dom";

import { updateProfilePictureAction, type ProfilePictureActionState } from "@/lib/actions/user";
import { Button } from "@/components/ui/button";
import { Field, FieldGroup, FieldLabel, FieldError } from "@/components/ui/field";
import { getImage } from "@/lib/api/api";

type ProfilePictureFormProps = {
  username: string;
  profilePicturePath?: string;
};

const initialState: ProfilePictureActionState = { error: null };

function SubmitButton() {
  const { pending } = useFormStatus();

  return (
    <Button type="submit" disabled={pending}>
      {pending ? "Envoi..." : "Mettre à jour"}
    </Button>
  );
}

export default function ProfilePictureForm({ username, profilePicturePath }: ProfilePictureFormProps) {
  const [state, formAction] = useActionState(updateProfilePictureAction, initialState);
  const [preview, setPreview] = useState<string | null>(null);

  const currentPath = state.profilePicturePath ?? profilePicturePath;
  const displayedImage = preview || (currentPath ? getImage(currentPath) : "/menu/default_profile_picture.webp");

  const handleFileChange = (event: React.ChangeEvent<HTMLInputElement>) => {
    const file = event.target.files?.[0];
    if (!file) {
      setPreview(null);
      return;
    }
    setPreview(URL.createObjectURL(file));
  };

  return (
    <form action={formAction} className="w-full max-w-sm">
      <FieldGroup>
        <div className="flex flex-col items-center gap-2">
          <div
            className="w-32 h-32 rounded-full bg-cover bg-center border-2 border-ink-outline shadow-[var(--sticker-shadow-sm)]"
            style={{ backgroundImage: `url(${displayedImage})` }}
          />
          <span className="font-bold">{username}</span>
        </div>

        <Field>
          <FieldLabel htmlFor="profilePicture">Nouvelle photo de profil</FieldLabel>
          <input
            id="profilePicture"
            name="profilePicture"
            type="file"
            accept="image/png,image/jpeg,image/webp"
            onChange={handleFileChange}
            className="text-sm"
          />
        </Field>

        {state.error && <FieldError>{state.error}</FieldError>}

        <Field>
          <SubmitButton />
        </Field>
      </FieldGroup>
    </form>
  );
};
