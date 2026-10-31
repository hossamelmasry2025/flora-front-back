"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import { ChevronLeft, ChevronRight } from "lucide-react";
import { useI18n } from "@/i18n/client";
import type { Banner, SliderSettings } from "@/lib/types";
import { ArtImage } from "../ArtImage";
import { BannerOverlay, BannerText } from "./BannerContent";

const HEIGHT = {
  sm: "h-[48vh] min-h-[320px] max-h-[440px]",
  md: "h-[60vh] min-h-[380px] max-h-[560px]",
  lg: "h-[72vh] min-h-[440px] max-h-[680px]",
  full: "h-[calc(100dvh-7rem)] min-h-[480px]",
};

const DEFAULTS: SliderSettings = { autoplay: true, interval: 6, height: "lg", arrows: true, dots: true, effect: "fade" };

/** السلايدر الرئيسي — كل إعداداته بتيجي من الداش بورد (السلايدر ← إعدادات السلايدر) */
export function Hero({ slides, settings, isPageHeading = false }: { slides: Banner[]; settings?: SliderSettings; isPageHeading?: boolean }) {
  const { t, lp, locale } = useI18n();
  const cfg = { ...DEFAULTS, ...settings };
  const [index, setIndex] = useState(0);
  const [paused, setPaused] = useState(false);
  const touchX = useRef<number | null>(null);
  const count = slides.length;

  const go = useCallback((i: number) => setIndex((i + count) % count), [count]);

  useEffect(() => {
    if (count < 2 || paused || !cfg.autoplay) return;
    const id = setInterval(() => setIndex((i) => (i + 1) % count), cfg.interval * 1000);
    return () => clearInterval(id);
  }, [count, paused, cfg.autoplay, cfg.interval]);

  if (!count) return null;

  return (
    <section
      className="relative overflow-hidden bg-brand-900"
      aria-roledescription="carousel"
      aria-label={t.common.mainOffers}
      onMouseEnter={() => setPaused(true)}
      onMouseLeave={() => setPaused(false)}
      onTouchStart={(e) => (touchX.current = e.touches[0].clientX)}
      onTouchEnd={(e) => {
        if (touchX.current === null) return;
        const dx = e.changedTouches[0].clientX - touchX.current;
        // السحب في اتجاه القراءة = الشريحة الجاية
        if (Math.abs(dx) > 50) go(index + ((dx < 0) !== (locale === "ar") ? 1 : -1));
        touchX.current = null;
      }}
    >
      <div className={`relative ${HEIGHT[cfg.height] ?? HEIGHT.lg}`}>
        {slides.map((s, i) => {
          const active = i === index;
          const href = s.link ? lp(s.link) : null;
          const effect =
            cfg.effect === "slide"
              ? `transition-transform duration-700 ease-out ${active ? "translate-x-0" : i < index ? "rtl:translate-x-full ltr:-translate-x-full" : "rtl:-translate-x-full ltr:translate-x-full"}`
              : `transition-opacity duration-1000 ${active ? "opacity-100" : "opacity-0"}`;

          return (
            <div
              key={s.id ?? i}
              className={`absolute inset-0 ${effect} ${active ? "" : "pointer-events-none"}`}
              aria-hidden={!active}
              role="group"
              aria-roledescription="slide"
            >
              {s.image ? (
                <ArtImage
                  src={s.image}
                  mobileSrc={s.mobile_image}
                  alt={s.title ?? ""}
                  priority={i === 0}
                  eager
                  className={`${active && cfg.effect === "fade" ? "scale-105" : "scale-100"} transition-transform duration-[7000ms] ease-out`}
                />
              ) : (
                <div className="absolute inset-0 bg-gradient-to-bl from-brand-700 via-brand-800 to-brand-900" />
              )}
              <BannerOverlay banner={s} />
              {s.text_color !== "dark" && (
                <div className="absolute inset-0 bg-gradient-to-t from-black/55 via-black/5 to-black/15" aria-hidden="true" />
              )}

              {/* لو مفيش زرار، الصورة كلها بتبقى رابط */}
              {href && !s.button_text && <a href={href} className="absolute inset-0" tabIndex={active ? 0 : -1} aria-label={s.title ?? ""} />}

              <div className="container-page relative flex h-full items-center">
                <BannerText
                  banner={s}
                  href={href}
                  heading={i === 0 && isPageHeading ? "h1" : "h2"}
                  size="lg"
                  className={active ? "animate-fade-up" : ""}
                />
              </div>
            </div>
          );
        })}
      </div>

      {count > 1 && (cfg.dots || cfg.arrows) && (
        <div className="container-page absolute inset-x-0 bottom-6 flex items-center justify-between gap-6 text-white sm:bottom-10">
          {cfg.dots ? (
            <div className="flex items-center gap-4">
              <span className="font-display text-sm tabular-nums tracking-widest text-white/90" aria-hidden="true">
                {String(index + 1).padStart(2, "0")}
                <span className="mx-1.5 text-white/40">/</span>
                <span className="text-white/50">{String(count).padStart(2, "0")}</span>
              </span>
              <div className="flex gap-1.5">
                {slides.map((_, i) => (
                  <button
                    key={i}
                    type="button"
                    onClick={() => go(i)}
                    className="group relative h-6 w-8 sm:w-12"
                    aria-label={t.common.slide(i + 1)}
                    aria-current={i === index}
                  >
                    <span className="absolute inset-x-0 top-1/2 h-px bg-white/30 transition group-hover:bg-white/60" />
                    {/* الخط بيتملي على مدة الشريحة */}
                    <span
                      key={i === index ? `on-${index}` : "off"}
                      className={`absolute inset-x-0 top-1/2 h-px origin-right bg-white ltr:origin-left ${i === index ? "" : "scale-x-0"}`}
                      style={
                        i === index && cfg.autoplay
                          ? { animation: `progress ${cfg.interval}s linear both`, animationPlayState: paused ? "paused" : "running" }
                          : undefined
                      }
                    />
                  </button>
                ))}
              </div>
            </div>
          ) : (
            <span />
          )}
          {cfg.arrows && (
            <div className="hidden gap-2 md:flex">
              <button type="button" onClick={() => go(index - 1)} className="flex h-12 w-12 items-center justify-center rounded-full border border-white/35 backdrop-blur-sm transition duration-300 hover:border-white hover:bg-white hover:text-ink" aria-label={t.common.prev}>
                <ChevronRight className="h-5 w-5 ltr:rotate-180" strokeWidth={1.5} />
              </button>
              <button type="button" onClick={() => go(index + 1)} className="flex h-12 w-12 items-center justify-center rounded-full border border-white/35 backdrop-blur-sm transition duration-300 hover:border-white hover:bg-white hover:text-ink" aria-label={t.common.next}>
                <ChevronLeft className="h-5 w-5 ltr:rotate-180" strokeWidth={1.5} />
              </button>
            </div>
          )}
        </div>
      )}
    </section>
  );
}
