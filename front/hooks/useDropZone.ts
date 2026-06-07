import { useEffect, RefObject, useCallback, useState } from "react";
import {
  registerDropZone,
  unregisterDropZone,
} from "@/lib/dropZones/dropzoneRegistry";
import { DropZone, DropResult } from "@/lib/dropZones/types/dropZone";
import { BasicCard } from "@/lib/cards/types/card";
import { emitter } from "@/lib/eventBus";

type UseDropZoneOptions = {
  id: string;
  ref: RefObject<HTMLDivElement | null>;
  getDropResult: (card: BasicCard) => DropResult;
};

export function useDropZone({ id, ref, getDropResult }: UseDropZoneOptions) {
  const [isDragging, setIsDragging] = useState(false);
  const [isHovered, setIsHovered] = useState(false);

  const getRect = useCallback(() => {
    if (!ref.current) {
      throw new Error(`DropZone with id ${id} has no ref.current`);
    }
    return ref.current.getBoundingClientRect();
  }, [id, ref]);

  useEffect(() => {
    const dropZone: DropZone = {
      id,
      getRect,
      getDropResult,
    };

    registerDropZone(dropZone);

    return () => {
      unregisterDropZone(id);
    };
  }, [id, getRect, getDropResult]);

  useEffect(() => {
    const onStart = () => setIsDragging(true);
    const onEnd = () => {
      setIsDragging(false);
      setIsHovered(false);
    };

    const onMove = (payload: {
      pos: { x: number; y: number };
      card: BasicCard;
    }) => {
      const rect = getRect();
      const inside =
        payload.pos.x >= rect.left &&
        payload.pos.x <= rect.right &&
        payload.pos.y >= rect.top &&
        payload.pos.y <= rect.bottom;

      setIsHovered(inside);
    };

    emitter.on("card:drag:start", onStart);
    emitter.on("card:drag:end", onEnd);
    emitter.on("card:drag:move", onMove);

    return () => {
      emitter.off("card:drag:start", onStart);
      emitter.off("card:drag:end", onEnd);
      emitter.off("card:drag:move", onMove);
    };
  }, [getRect]);

  return {
    isDragging,
    isHovered,
  };
}
