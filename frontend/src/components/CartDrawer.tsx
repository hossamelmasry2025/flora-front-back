"use client";

import Image from "next/image";
import Link from "next/link";
import { useEffect } from "react";
import { Minus, Plus, ShoppingBag, Trash2, X } from "lucide-react";
import { useI18n } from "@/i18n/client";
import { cartSubtotal, useCart } from "@/lib/cart";
import { useHydrated } from "@/lib/useHydrated";
import { formatPrice, productUrl } from "@/lib/utils";
import { FlowerMark } from "./Logo";

export function CartDrawer({ currency }: { currency: string }) {
  const { t, lp } = useI18n();
  const { items, isOpen, close, setQuantity, remove } = useCart();
  const hydrated = useHydrated();

  useEffect(() => {
    if (!isOpen) return;
    document.body.style.overflow = "hidden";
    const onKey = (e: KeyboardEvent) => e.key === "Escape" && close();
    window.addEventListener("keydown", onKey);
    return () => {
      document.body.style.overflow = "";
      window.removeEventListener("keydown", onKey);
    };
  }, [isOpen, close]);

  if (!hydrated || !isOpen) return null;

  const subtotal = cartSubtotal(items);

  return (
    <div className="fixed inset-0 z-50" role="dialog" aria-modal="true" aria-label={t.cart.title}>
      <div className="absolute inset-0 animate-fade-in bg-ink/50 backdrop-blur-sm" onClick={close} />
      <aside className="animate-drawer-end absolute inset-y-0 end-0 flex w-full max-w-md flex-col bg-cream shadow-2xl">
        <div className="flex items-center justify-between border-b border-line px-6 py-5">
          <h2 className="flex items-center gap-2.5 text-2xl font-bold">
            <ShoppingBag className="h-5 w-5 text-gold" strokeWidth={1.5} /> {t.cart.title}
          </h2>
          <button type="button" onClick={close} className="rounded-full border border-line p-2 transition hover:border-ink hover:bg-ink hover:text-white" aria-label={t.nav.close}>
            <X className="h-5 w-5" />
          </button>
        </div>

        {items.length === 0 ? (
          <div className="flex flex-1 flex-col items-center justify-center gap-4 p-8 text-center">
            <span className="flex h-24 w-24 items-center justify-center rounded-full border border-gold/30">
              <FlowerMark className="h-12 w-12 text-brand-200" />
            </span>
            <p className="font-display text-xl text-ink/80">{t.cart.empty}</p>
            <Link href={lp("/products")} onClick={close} className="btn-primary">
              {t.cart.choose}
            </Link>
          </div>
        ) : (
          <>
            <ul className="flex-1 divide-y divide-line overflow-y-auto px-6">
              {items.map((item) => (
                <li key={item.id} className="flex gap-4 py-4">
                  <Link href={lp(productUrl(item.slug))} onClick={close} className="relative h-24 w-20 shrink-0 overflow-hidden rounded-2xl bg-sand">
                    {item.image && <Image src={item.image} alt={item.name} fill sizes="80px" className="object-cover" />}
                  </Link>
                  <div className="flex flex-1 flex-col">
                    <div className="flex items-start justify-between gap-2">
                      <Link href={lp(productUrl(item.slug))} onClick={close} className="font-display text-base font-bold leading-6 transition-colors hover:text-brand-700">
                        {item.name}
                      </Link>
                      <button type="button" onClick={() => remove(item.id)} className="p-1 text-muted hover:text-brand-700" aria-label={t.cart.remove(item.name)}>
                        <Trash2 className="h-4 w-4" />
                      </button>
                    </div>
                    <div className="mt-auto flex items-center justify-between">
                      <QuantityControl value={item.quantity} onChange={(q) => setQuantity(item.id, q)} />
                      <span className="text-sm font-semibold tabular-nums">{formatPrice(item.price * item.quantity, currency)}</span>
                    </div>
                  </div>
                </li>
              ))}
            </ul>
            <div className="relative space-y-3 bg-white px-6 py-6">
              <span className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent" aria-hidden="true" />
              <div className="flex items-center justify-between text-base">
                <span className="text-muted">{t.cart.total}</span>
                <span className="font-display text-2xl font-bold tabular-nums">{formatPrice(subtotal, currency)}</span>
              </div>
              <p className="text-xs text-muted">{t.cart.deliveryNote}</p>
              <Link href={lp("/checkout")} onClick={close} className="btn-primary w-full py-4 text-base">
                {t.cart.checkout}
              </Link>
              <Link href={lp("/cart")} onClick={close} className="link-underline mx-auto block w-fit text-center text-sm font-medium text-ink">
                {t.cart.view}
              </Link>
            </div>
          </>
        )}
      </aside>
    </div>
  );
}

export function QuantityControl({ value, onChange, size = "sm" }: { value: number; onChange: (v: number) => void; size?: "sm" | "lg" }) {
  const { t } = useI18n();
  const btn = size === "lg" ? "h-11 w-11" : "h-8 w-8";
  return (
    <div className="inline-flex items-center rounded-full border border-line bg-white/80">
      <button type="button" className={`${btn} flex items-center justify-center rounded-full transition hover:bg-sand disabled:opacity-40`} onClick={() => onChange(value + 1)} disabled={value >= 50} aria-label={t.product.increase}>
        <Plus className="h-3.5 w-3.5" />
      </button>
      <span className={`min-w-8 text-center font-semibold tabular-nums ${size === "lg" ? "text-base" : "text-sm"}`} aria-live="polite">
        {value}
      </span>
      <button type="button" className={`${btn} flex items-center justify-center rounded-full transition hover:bg-sand disabled:opacity-40`} onClick={() => onChange(value - 1)} disabled={value <= 1} aria-label={t.product.decrease}>
        <Minus className="h-3.5 w-3.5" />
      </button>
    </div>
  );
}
