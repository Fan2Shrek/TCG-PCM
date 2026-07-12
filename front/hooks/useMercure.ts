import { useEffect } from "react";

export default ( url: string, callbacks: Record<string, Function>) => {
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
