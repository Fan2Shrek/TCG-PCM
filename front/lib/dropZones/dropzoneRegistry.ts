import { DropZone } from "./types/dropZone";

const zones = new Map<string, DropZone>();

export function registerDropZone(zone: DropZone) {
  zones.set(zone.id, zone);
}

export function unregisterDropZone(id: string) {
  zones.delete(id);
}

export function getDropZones() {
  return Array.from(zones.values());
}
