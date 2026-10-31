import Link from "next/link";
import { ChevronLeft, ChevronRight } from "lucide-react";
import type { Locale } from "@/i18n/config";
import { getDictionary } from "@/i18n/dictionaries";

/**
 * باجنيشن بروابط حقيقية (?page=2) عشان جوجل يقدر يزحف لكل الصفحات.
 */
export function Pagination({
  current,
  last,
  basePath,
  locale,
  params = {},
}: {
  current: number;
  last: number;
  basePath: string;
  locale: Locale;
  params?: Record<string, string | undefined>;
}) {
  if (last <= 1) return null;
  const t = getDictionary(locale);

  const href = (page: number) => {
    const qs = new URLSearchParams();
    for (const [k, v] of Object.entries(params)) if (v) qs.set(k, v);
    if (page > 1) qs.set("page", String(page));
    const s = qs.toString();
    return s ? `${basePath}?${s}` : basePath;
  };

  const pages = pageWindow(current, last);
  const item = "flex h-11 min-w-11 items-center justify-center rounded-full px-3 text-sm font-semibold transition";
  // "السابق" ناحية البداية: يمين في العربي وشمال في الإنجليزي
  const PrevIcon = locale === "ar" ? ChevronRight : ChevronLeft;
  const NextIcon = locale === "ar" ? ChevronLeft : ChevronRight;

  return (
    <nav aria-label={t.common.pages} className="mt-12 flex items-center justify-center gap-1.5">
      {current > 1 ? (
        <Link href={href(current - 1)} rel="prev" className={`${item} border border-line bg-white/70 transition hover:border-gold hover:text-gold-dark`} aria-label={t.common.prevPage}>
          <PrevIcon className="h-4 w-4" />
        </Link>
      ) : (
        <span className={`${item} opacity-30`} aria-hidden="true"><PrevIcon className="h-4 w-4" /></span>
      )}

      {pages.map((p, i) =>
        p === "…" ? (
          <span key={`gap-${i}`} className="px-1 text-muted">…</span>
        ) : (
          <Link
            key={p}
            href={href(p)}
            aria-current={p === current ? "page" : undefined}
            className={`${item} ${p === current ? "bg-brand-900 font-display text-white shadow-soft" : "border border-line bg-white/70 transition hover:border-gold hover:text-gold-dark"}`}
          >
            {p}
          </Link>
        ),
      )}

      {current < last ? (
        <Link href={href(current + 1)} rel="next" className={`${item} border border-line bg-white/70 transition hover:border-gold hover:text-gold-dark`} aria-label={t.common.nextPage}>
          <NextIcon className="h-4 w-4" />
        </Link>
      ) : (
        <span className={`${item} opacity-30`} aria-hidden="true"><NextIcon className="h-4 w-4" /></span>
      )}
    </nav>
  );
}

function pageWindow(current: number, last: number): (number | "…")[] {
  const set = new Set([1, last, current, current - 1, current + 1].filter((p) => p >= 1 && p <= last));
  const sorted = [...set].sort((a, b) => a - b);
  const out: (number | "…")[] = [];
  sorted.forEach((p, i) => {
    if (i > 0 && p - sorted[i - 1] > 1) out.push("…");
    out.push(p);
  });
  return out;
}
