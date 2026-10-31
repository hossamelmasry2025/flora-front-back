"use client";

import { useRef } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { useI18n } from "@/i18n/client";

/** سلايدر أفقي خفيف (CSS scroll-snap) — بيشتغل بالسحب على الموبايل وبالأسهم على الكمبيوتر */
export function Carousel({ children, label }: { children: React.ReactNode; label: string }) {
  const { t } = useI18n();
  const ref = useRef<HTMLUListElement>(null);

  const scroll = (dir: 1 | -1) => {
    const el = ref.current;
    if (!el) return;
    // في RTL الاتجاه معكوس
    const rtl = getComputedStyle(el).direction === "rtl";
    el.scrollBy({ left: (rtl ? -dir : dir) * el.clientWidth * 0.8, behavior: "smooth" });
  };

  return (
    <div className="relative">
      <ul
        ref={ref}
        className="scrollbar-none -mx-4 flex snap-x snap-mandatory gap-3 overflow-x-auto scroll-px-4 px-4 pb-4 sm:-mx-6 sm:gap-5 sm:px-6"
        aria-label={label}
      >
        {children}
      </ul>
      <button type="button" onClick={() => scroll(-1)} className="absolute -start-5 top-[38%] hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-line bg-white/90 text-ink shadow-lift backdrop-blur transition duration-300 hover:border-brand-900 hover:bg-brand-900 hover:text-white lg:flex" aria-label={t.common.prev}>
        <ChevronRight className="h-5 w-5 ltr:rotate-180" />
      </button>
      <button type="button" onClick={() => scroll(1)} className="absolute -end-5 top-[38%] hidden h-11 w-11 -translate-y-1/2 items-center justify-center rounded-full border border-line bg-white/90 text-ink shadow-lift backdrop-blur transition duration-300 hover:border-brand-900 hover:bg-brand-900 hover:text-white lg:flex" aria-label={t.common.next}>
        <ChevronLeft className="h-5 w-5 ltr:rotate-180" />
      </button>
    </div>
  );
}
