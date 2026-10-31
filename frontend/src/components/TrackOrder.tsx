"use client";

import { useCallback, useEffect, useRef, useState } from "react";
import { ChevronLeft, Loader2, PackageSearch, RefreshCw, Search, X } from "lucide-react";
import { useI18n } from "@/i18n/client";
import { useMyOrders } from "@/lib/myOrders";
import type { Order } from "@/lib/types";
import { useHydrated } from "@/lib/useHydrated";
import { API_PUBLIC_URL, formatPrice, whatsappLink } from "@/lib/utils";
import { OrderDetails } from "./OrderDetails";
import { SocialIcon } from "./SocialIcon";

export function TrackOrder({ initialNumber, currency, whatsapp }: { initialNumber: string; currency: string; whatsapp: string | null }) {
  const { t, locale } = useI18n();
  const hydrated = useHydrated();
  const { orders: saved, remove } = useMyOrders();
  const [query, setQuery] = useState<{ number: string; phone: string } | null>(null);
  const [order, setOrder] = useState<Order | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [loading, setLoading] = useState(false);
  const [checkedAt, setCheckedAt] = useState<Date | null>(null);
  const resultRef = useRef<HTMLDivElement>(null);
  const autoTracked = useRef(false);

  const track = useCallback(
    async (number: string, phone: string) => {
      setLoading(true);
      setError(null);
      setQuery({ number, phone });
      try {
        const res = await fetch(`${API_PUBLIC_URL}/api/orders/${encodeURIComponent(number.trim())}?phone=${encodeURIComponent(phone.trim())}&lang=${locale}`, {
          headers: { Accept: "application/json" },
          cache: "no-store",
        });
        if (res.status === 404) {
          setOrder(null);
          setError(t.track.notFound);
        } else if (!res.ok) {
          setError(t.track.error);
        } else {
          setOrder((await res.json()).data);
          setCheckedAt(new Date());
          setTimeout(() => resultRef.current?.scrollIntoView({ behavior: "smooth", block: "start" }), 50);
        }
      } catch {
        setError(t.track.error);
      } finally {
        setLoading(false);
      }
    },
    [locale, t],
  );

  // لو جاي من صفحة "تم الطلب" والطلب محفوظ على الجهاز، نتابعه على طول
  useEffect(() => {
    if (!hydrated || autoTracked.current || !initialNumber) return;
    const match = saved.find((o) => o.number === initialNumber.toUpperCase());
    if (!match) return;
    const id = setTimeout(() => {
      autoTracked.current = true;
      void track(match.number, match.phone);
    }, 0);
    return () => clearTimeout(id);
  }, [hydrated, initialNumber, saved, track]);

  // تحديث تلقائي كل دقيقة طول ما الصفحة مفتوحة والطلب لسه ما اتسلمش
  useEffect(() => {
    if (!query || !order || ["delivered", "cancelled"].includes(order.status)) return;
    const id = setInterval(() => void track(query.number, query.phone), 60_000);
    return () => clearInterval(id);
  }, [query, order, track]);

  const submit = (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);
    const number = String(fd.get("number") ?? "").trim();
    const phone = String(fd.get("phone") ?? "").trim();
    if (number && phone) void track(number, phone);
  };

  const help = whatsappLink(whatsapp, t.track.helpMessage(order?.number ?? query?.number ?? ""));

  return (
    <>
      <form onSubmit={submit} className="relative mt-10 grid gap-4 overflow-hidden rounded-3xl bg-white p-6 shadow-luxe ring-1 ring-line/60 sm:grid-cols-[1fr_1fr_auto] sm:items-end sm:p-8">
        <span className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold to-transparent sm:col-span-3" aria-hidden="true" />
        <div>
          <label htmlFor="number" className="label">{t.track.number}</label>
          <input id="number" name="number" defaultValue={initialNumber} required dir="ltr" placeholder="FL2610301234" className="input uppercase" />
        </div>
        <div>
          <label htmlFor="phone" className="label">{t.track.phone}</label>
          <input id="phone" name="phone" type="tel" required dir="ltr" placeholder="01xxxxxxxxx" className="input" />
        </div>
        <button type="submit" disabled={loading} className="btn-primary py-3.5">
          {loading ? <Loader2 className="h-5 w-5 animate-spin" /> : <Search className="h-5 w-5" />} {t.track.button}
        </button>
      </form>

      {hydrated && saved.length > 0 && (
        <section className="mt-6" aria-label={t.track.recent}>
          <h2 className="mb-3 flex items-center gap-2 font-sans text-sm font-semibold text-muted">
            <PackageSearch className="h-4 w-4 text-gold" strokeWidth={1.75} /> {t.track.recent}
          </h2>
          <ul className="grid gap-2 sm:grid-cols-2">
            {saved.map((s) => (
              <li key={s.number} className={`flex items-center gap-2 rounded-2xl border bg-white p-2 ps-4 transition ${order?.number === s.number ? "border-gold ring-4 ring-gold/10" : "border-line hover:border-gold/50"}`}>
                <button type="button" onClick={() => void track(s.number, s.phone)} className="flex flex-1 items-center justify-between gap-3 text-start">
                  <span>
                    <span className="block font-display text-lg font-bold tracking-wide" dir="ltr">{s.number}</span>
                    <span className="block text-xs text-muted">
                      {new Date(s.created_at).toLocaleDateString(locale === "ar" ? "ar-EG" : "en-GB", { day: "numeric", month: "short" })} • {formatPrice(s.total, currency)}
                    </span>
                  </span>
                  <span className="flex items-center gap-1 rounded-full border border-gold/40 px-3 py-1.5 text-xs font-semibold text-gold-dark">
                    {t.track.open} <ChevronLeft className="h-3.5 w-3.5 ltr:rotate-180" />
                  </span>
                </button>
                <button type="button" onClick={() => remove(s.number)} className="rounded-full p-2 text-muted transition hover:bg-sand hover:text-ink" aria-label={t.track.forget}>
                  <X className="h-4 w-4" />
                </button>
              </li>
            ))}
          </ul>
        </section>
      )}

      {error && <p role="alert" className="mt-6 rounded-2xl bg-brand-50 p-4 text-center text-sm text-brand-800">{error}</p>}

      {order && (
        <div ref={resultRef} className="scroll-mt-28">
          <div className="mt-8 flex flex-wrap items-center justify-between gap-3">
            <p className="font-display text-2xl font-bold">
              {order.status_label}
            </p>
            <button type="button" onClick={() => query && void track(query.number, query.phone)} className="flex items-center gap-1.5 text-xs text-muted hover:text-brand-700" disabled={loading}>
              <RefreshCw className={`h-3.5 w-3.5 ${loading ? "animate-spin" : ""}`} />
              {t.track.lastUpdate}: {checkedAt?.toLocaleTimeString(locale === "ar" ? "ar-EG" : "en-GB", { hour: "numeric", minute: "2-digit" })}
            </button>
          </div>
          <OrderDetails order={order} currency={currency} />
          {help && (
            <div className="mt-6 flex flex-col items-center justify-between gap-3 rounded-3xl border border-gold/30 bg-white/60 p-5 sm:flex-row sm:px-7">
              <p className="font-display text-lg font-bold">{t.track.help}</p>
              <a href={help} target="_blank" rel="noopener noreferrer" className="btn-primary bg-[#25D366] py-2.5 hover:bg-[#1fb257]">
                <SocialIcon platform="whatsapp" className="h-5 w-5" /> {t.track.helpWhatsapp}
              </a>
            </div>
          )}
        </div>
      )}
    </>
  );
}
