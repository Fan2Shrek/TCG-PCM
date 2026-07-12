import { NextRequest, NextResponse } from "next/server";

import { serverApiFetch } from "@/lib/api/server";

async function handle(request: NextRequest, path: string[]) {
  const endpoint = `/${path.join("/")}${request.nextUrl.search}`;
  const hasBody = request.method !== "GET" && request.method !== "HEAD";
  const contentType = request.headers.get("content-type");

  try {
    const result = await serverApiFetch(endpoint, {
      method: request.method,
      body: hasBody ? await request.text() : undefined,
      headers: contentType ? { "Content-Type": contentType } : undefined,
    });
    return NextResponse.json(result);
  } catch (err) {
    const message = err instanceof Error ? err.message : "API request failed";
    return NextResponse.json({ detail: message }, { status: 400 });
  }
}

type RouteContext = { params: Promise<{ path: string[] }> };

export async function GET(request: NextRequest, { params }: RouteContext) {
  const { path } = await params;
  return handle(request, path);
}

export async function POST(request: NextRequest, { params }: RouteContext) {
  const { path } = await params;
  return handle(request, path);
}

export async function PATCH(request: NextRequest, { params }: RouteContext) {
  const { path } = await params;
  return handle(request, path);
}
