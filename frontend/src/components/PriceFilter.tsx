"use client";

import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useState } from "react";
import { useI18n } from "@/i18n/client";

export function PriceFilter({ min, max, currency }: { min?: number; max?: number; currency: string }) {
  const { t } = useI18n();
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [from, setFrom] = useState(min?.toString() ?? "");
  const [to, setTo] = useState(max?.toString() ?? "");

  const apply = (e: React.FormEvent) => {
    e.preventDefault();
    const qs = new URLSearchParams(searchParams.toString());
    if (from) qs.set("min", from);
    else qs.delete("min");
    if (to) qs.set("max", to);
    else qs.delete("max");
    qs.delete("page");
    router.push(`${pathname}${qs.size ? `?${qs}` : ""}`);
  };

  const reset = () => {
    setFrom("");
    setTo("");
    const qs = new URLSearchParams(searchParams.toString());
    qs.delete("min");
    qs.delete("max");
    qs.delete("page");
    router.push(`${pathname}${qs.size ? `?${qs}` : ""}`);
  };

  return (
    <form onSubmit={apply} className="rounded-3xl bg-white/70 p-5 ring-1 ring-line/60">
      <h2 className="mb-3 text-lg font-bold">{t.listing.price} ({currency})</h2>
      <div className="flex items-center gap-2">
        <input type="number" inputMode="numeric" min={0} value={from} onChange={(e) => setFrom(e.target.value)} placeholder={t.listing.from} className="input px-3 py-2" aria-label={t.listing.minPrice} />
        <span className="text-muted">—</span>
        <input type="number" inputMode="numeric" min={0} value={to} onChange={(e) => setTo(e.target.value)} placeholder={t.listing.to} className="input px-3 py-2" aria-label={t.listing.maxPrice} />
      </div>
      <div className="mt-3 flex gap-2">
        <button type="submit" className="btn-primary flex-1 py-2">{t.listing.apply}</button>
        {(min || max) && (
          <button type="button" onClick={reset} className="btn-outline py-2">{t.listing.clear}</button>
        )}
      </div>
    </form>
  );
}
