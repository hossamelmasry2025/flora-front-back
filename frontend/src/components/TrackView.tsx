"use client";

import { useEffect } from "react";
import { API_PUBLIC_URL } from "@/lib/utils";

/** بيسجل مشاهدة للمنتج (لترتيب "الأكثر طلباً") من غير ما يأثر على كاش الصفحة */
export function TrackView({ slug }: { slug: string }) {
  useEffect(() => {
    const key = `viewed:${slug}`;
    try {
      if (sessionStorage.getItem(key)) return;
      sessionStorage.setItem(key, "1");
    } catch {}
    fetch(`${API_PUBLIC_URL}/api/products/${encodeURIComponent(slug)}/view`, { method: "POST", keepalive: true }).catch(() => {});
  }, [slug]);

  return null;
}
