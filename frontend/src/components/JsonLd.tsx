/** Structured data (Schema.org) — بيخلي جوجل يعرض السعر والتقييمات والمسار في نتايج البحث */
export function JsonLd({ data }: { data: Record<string, unknown> | Record<string, unknown>[] }) {
  return (
    <script
      type="application/ld+json"
      // حماية من XSS: منع إغلاق الـ script من جوه الداتا
      dangerouslySetInnerHTML={{ __html: JSON.stringify(data).replace(/</g, "\\u003c") }}
    />
  );
}
