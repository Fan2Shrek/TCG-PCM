import { useEffect } from "react";

export default function useMercure(
  url: string,
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  callbacks: Record<string, (event: any) => void>,
) {
    useEffect(() => {
        const eventSource = new EventSource(url, {withCredentials: true});
        eventSource.onmessage = (message) => {
            const event = JSON.parse(message.data);

			if (!Object.keys(callbacks).includes(event.type)) {
			  console.warn(`Warning unhandled event ${event.type}`)

			  return;
			}

            callbacks[event.type](event);
        };

        return () => {
            eventSource.close();
        };
    }, []);
}
