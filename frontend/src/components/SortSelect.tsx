"use client";

import { usePathname, useRouter, useSearchParams } from "next/navigation";
import { useTransition } from "react";
import { useI18n } from "@/i18n/client";

export function SortSelect({ value }: { value: string }) {
  const { t } = useI18n();
  const router = useRouter();
  const pathname = usePathname();
  const searchParams = useSearchParams();
  const [pending, startTransition] = useTransition();

  const onChange = (sort: string) => {
    const qs = new URLSearchParams(searchParams.toString());
    if (sort === "default") qs.delete("sort");
    else qs.set("sort", sort);
    qs.delete("page");
    startTransition(() => router.push(`${pathname}${qs.size ? `?${qs}` : ""}`, { scroll: false }));
  };

  return (
    <label className="flex items-center gap-2 text-sm">
      <span className="text-muted">{t.listing.sort}</span>
      <select
        value={value}
        onChange={(e) => onChange(e.target.value)}
        className={`rounded-full border border-line bg-white/70 px-4 py-2 font-medium transition hover:border-gold/60 focus:border-gold focus:outline-none ${pending ? "opacity-60" : ""}`}
      >
        {Object.entries(t.listing.sortOptions).map(([k, label]) => (
          <option key={k} value={k}>{label}</option>
        ))}
      </select>
    </label>
  );
}
