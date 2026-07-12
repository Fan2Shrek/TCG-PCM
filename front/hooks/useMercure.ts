import { useEffect, useRef } from "react";

export default function useMercure(
  url: string | null | undefined,
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  callbacks: Record<string, (event: any) => void>,
) {
    const callbacksRef = useRef(callbacks);

    useEffect(() => {
        callbacksRef.current = callbacks;
    });

    useEffect(() => {
        if (!url) return;

        const eventSource = new EventSource(url, {withCredentials: true});
        eventSource.onmessage = (message) => {
            const event = JSON.parse(message.data);

			if (!Object.keys(callbacksRef.current).includes(event.type)) {
			  console.warn(`Warning unhandled event ${event.type}`)

			  return;
			}

            callbacksRef.current[event.type](event);
        };

        return () => {
            eventSource.close();
        };
    }, [url]);
}
