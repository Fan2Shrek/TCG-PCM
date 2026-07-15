"use client";

import {
  FormEvent,
  useContext,
  useEffect,
  useMemo,
  useRef,
  useState,
} from "react";
import { MdChatBubbleOutline, MdClose } from "react-icons/md";
import { GameContext } from "@/contexts/GameContext";
import { Input } from "@/components/ui/input";
import { Button } from "@/components/ui/button";

const MAX_MESSAGE_LENGTH = 500;

export default function GameChat() {
  const { game, chatMessages, currentUsername, actions } =
    useContext(GameContext);
  const [isOpen, setIsOpen] = useState(false);
  const [draft, setDraft] = useState("");
  const [unreadCount, setUnreadCount] = useState(0);
  const listRef = useRef<HTMLDivElement>(null);
  const previousMessageCount = useRef(chatMessages.length);

  const connectedPlayerId = useMemo(() => {
    if (!game || !currentUsername) {
      return null;
    }

    return game.player1.player.name === currentUsername
      ? game.player1.player.id
      : game.player2.player.id;
  }, [game, currentUsername]);

  useEffect(() => {
    if (chatMessages.length > previousMessageCount.current && !isOpen) {
      setUnreadCount(
        (current) =>
          current + (chatMessages.length - previousMessageCount.current),
      );
    }
    previousMessageCount.current = chatMessages.length;
  }, [chatMessages.length, isOpen]);

  useEffect(() => {
    if (isOpen) {
      listRef.current?.scrollTo({ top: listRef.current.scrollHeight });
    }
  }, [isOpen, chatMessages.length]);

  const handleToggle = () => {
    setIsOpen((current) => !current);
    setUnreadCount(0);
  };

  const handleSubmit = (e: FormEvent) => {
    e.preventDefault();

    const trimmed = draft.trim();
    if (!trimmed) {
      return;
    }

    actions.sendChatMessage(trimmed);
    setDraft("");
  };

  return (
    <div className="absolute z-20 bottom-4 left-4 flex flex-col items-start gap-2">
      {isOpen && (
        <div className="w-72 rounded-xl border border-white/20 bg-black/60 backdrop-blur-sm shadow-lg flex flex-col overflow-hidden">
          <div
            ref={listRef}
            className="max-h-64 min-h-32 overflow-y-auto flex flex-col gap-1.5 p-3"
          >
            {chatMessages.length === 0 && (
              <p className="text-white/40 text-xs text-center my-auto">
                Aucun message pour le moment.
              </p>
            )}
            {chatMessages.map((message) => {
              const isMine = message.authorId === connectedPlayerId;

              return (
                <div
                  key={message.id}
                  className={`flex flex-col ${isMine ? "items-end" : "items-start"}`}
                >
                  <span className="text-[10px] text-white/40 px-1">
                    {message.authorUsername}
                  </span>
                  <div
                    className={`rounded-2xl px-3 py-1.5 text-sm max-w-full break-words ${
                      isMine
                        ? "bg-emerald-500/30 text-emerald-50"
                        : "bg-white/10 text-white"
                    }`}
                  >
                    {message.message}
                  </div>
                </div>
              );
            })}
          </div>
          <form
            onSubmit={handleSubmit}
            className="flex items-center gap-2 border-t border-white/10 p-2"
          >
            <Input
              value={draft}
              onChange={(e) => setDraft(e.target.value)}
              maxLength={MAX_MESSAGE_LENGTH}
              placeholder="Écris un message..."
              className="text-white placeholder:text-white/40"
            />
            <Button type="submit" size="sm" disabled={!draft.trim()}>
              Envoyer
            </Button>
          </form>
        </div>
      )}

      <Button
        type="button"
        variant="secondary"
        size="icon"
        onClick={handleToggle}
        className="relative shadow-lg"
      >
        {isOpen ? (
          <MdClose className="h-5 w-5" />
        ) : (
          <MdChatBubbleOutline className="h-5 w-5" />
        )}
        {!isOpen && unreadCount > 0 && (
          <span className="absolute -top-1.5 -right-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-rose-500 px-1 text-[10px] font-semibold text-white">
            {unreadCount > 9 ? "9+" : unreadCount}
          </span>
        )}
      </Button>
    </div>
  );
}
