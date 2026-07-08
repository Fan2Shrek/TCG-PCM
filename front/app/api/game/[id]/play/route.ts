import { NextRequest, NextResponse } from "next/server";

import { serverApiPost } from "@/lib/api/server";

export async function POST(request: NextRequest, { params }: { params: Promise<{ id: string }> }) {
  const { id } = await params;
  const body = await request.json();

  try {
    const result = await serverApiPost(`/game/${id}/play`, body);
    return NextResponse.json(result);
  } catch (err) {
    const message = err instanceof Error ? err.message : "API request failed";
    return NextResponse.json({ detail: message }, { status: 400 });
  }
}
