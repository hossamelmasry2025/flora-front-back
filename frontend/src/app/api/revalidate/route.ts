import { revalidateTag } from "next/cache";
import { timingSafeEqual } from "node:crypto";
import { CMS_TAG } from "@/lib/api";

/** الداش بورد بتنادي هنا بعد أي تعديل، فالموقع يتحدث فوراً */
export async function POST(request: Request) {
  const secret = process.env.REVALIDATE_SECRET;
  const body = (await request.json().catch(() => ({}))) as { secret?: string };

  if (!secret || !body.secret || !safeEqual(body.secret, secret)) {
    return Response.json({ ok: false }, { status: 401 });
  }

  revalidateTag(CMS_TAG, { expire: 0 });
  return Response.json({ ok: true, revalidated: CMS_TAG });
}

function safeEqual(a: string, b: string) {
  const x = Buffer.from(a);
  const y = Buffer.from(b);
  return x.length === y.length && timingSafeEqual(x, y);
}
