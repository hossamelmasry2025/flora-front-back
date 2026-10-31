"use client";

import Image from "next/image";
import Link from "next/link";
import { Lock, Trash2 } from "lucide-react";
import { useI18n } from "@/i18n/client";
import { cartSubtotal, useCart } from "@/lib/cart";
import { useHydrated } from "@/lib/useHydrated";
import { formatPrice, productUrl } from "@/lib/utils";
import { QuantityControl } from "./CartDrawer";
import { EmptyState } from "./EmptyState";

export function CartView({ currency }: { currency: string }) {
  const { t, lp } = useI18n();
  const { items, setQuantity, remove } = useCart();
  const hydrated = useHydrated();

  if (!hydrated) return <div className="h-64 animate-pulse rounded-3xl bg-sand" />;

  if (items.length === 0) {
    return <EmptyState text={t.cart.emptyShort} action={{ href: lp("/products"), label: t.cart.browse }} />;
  }

  return (
    <div className="grid gap-10 lg:grid-cols-[1fr_380px] lg:gap-14">
      <ul className="divide-y divide-line border-y border-line">
        {items.map((item) => (
          <li key={item.id} className="flex gap-5 py-6">
            <Link href={lp(productUrl(item.slug))} className="relative h-32 w-24 shrink-0 overflow-hidden rounded-2xl bg-sand shadow-soft sm:h-36 sm:w-28">
              {item.image && <Image src={item.image} alt={item.name} fill sizes="112px" className="object-cover transition duration-700 hover:scale-105" />}
            </Link>
            <div className="flex flex-1 flex-col gap-1.5">
              <div className="flex items-start justify-between gap-2">
                <Link href={lp(productUrl(item.slug))} className="font-display text-lg font-bold leading-snug transition-colors hover:text-brand-700 sm:text-xl">
                  {item.name}
                </Link>
                <button type="button" onClick={() => remove(item.id)} className="rounded-full p-2 text-muted transition hover:bg-sand hover:text-ink" aria-label={t.cart.remove(item.name)}>
                  <Trash2 className="h-4 w-4" strokeWidth={1.5} />
                </button>
              </div>
              <span className="text-sm text-muted tabular-nums">{formatPrice(item.price, currency)}</span>
              <div className="mt-auto flex items-center justify-between">
                <QuantityControl value={item.quantity} onChange={(q) => setQuantity(item.id, q)} />
                <span className="font-semibold tabular-nums">{formatPrice(item.price * item.quantity, currency)}</span>
              </div>
            </div>
          </li>
        ))}
      </ul>

      <aside className="h-fit lg:sticky lg:top-28">
        <div className="relative overflow-hidden rounded-3xl bg-white p-7 shadow-luxe ring-1 ring-line/60">
          <span className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold to-transparent" aria-hidden="true" />
          <h2 className="mb-6 text-2xl font-bold">{t.cart.summary}</h2>
          <dl className="space-y-3 text-sm">
            <div className="flex justify-between">
              <dt className="text-muted">{t.cart.items}</dt>
              <dd className="font-semibold tabular-nums">{formatPrice(cartSubtotal(items), currency)}</dd>
            </div>
            <div className="flex justify-between">
              <dt className="text-muted">{t.cart.delivery}</dt>
              <dd className="text-muted">{t.cart.deliveryByZone}</dd>
            </div>
            <div className="flex items-baseline justify-between border-t border-line pt-4">
              <dt className="font-semibold">{t.cart.total}</dt>
              <dd className="font-display text-3xl font-bold tabular-nums">{formatPrice(cartSubtotal(items), currency)}</dd>
            </div>
          </dl>
          <Link href={lp("/checkout")} className="btn-primary mt-7 w-full py-4 text-base">{t.cart.checkout}</Link>
          <p className="mt-4 flex items-center justify-center gap-1.5 text-xs text-muted">
            <Lock className="h-3.5 w-3.5 text-gold" strokeWidth={1.75} /> {t.cart.deliveryNote}
          </p>
        </div>
        <Link href={lp("/products")} className="link-underline mx-auto mt-6 block w-fit text-sm font-medium text-ink">{t.cart.continue}</Link>
      </aside>
    </div>
  );
}
