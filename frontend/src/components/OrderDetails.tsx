"use client";

import Image from "next/image";
import { Check, CircleX, ClipboardCheck, Flower2, PackageCheck, Truck } from "lucide-react";
import { useI18n } from "@/i18n/client";
import type { Order } from "@/lib/types";
import { formatPrice } from "@/lib/utils";

const STEPS = [
  { key: "pending", Icon: ClipboardCheck },
  { key: "confirmed", Icon: Check },
  { key: "preparing", Icon: Flower2 },
  { key: "out_for_delivery", Icon: Truck },
  { key: "delivered", Icon: PackageCheck },
];

/** خط زمني بمراحل الطلب ووقت كل مرحلة */
export function OrderTimeline({ order }: { order: Order }) {
  const { t, locale } = useI18n();
  const fmt = (iso: string | null) =>
    iso ? new Date(iso).toLocaleString(locale === "ar" ? "ar-EG" : "en-GB", { weekday: "short", day: "numeric", month: "short", hour: "numeric", minute: "2-digit" }) : null;

  if (order.status === "cancelled") {
    return (
      <div className="flex items-center gap-3 rounded-2xl bg-sand px-5 py-4 text-muted">
        <CircleX className="h-6 w-6 shrink-0" />
        <p className="font-semibold">{t.track.cancelled}</p>
      </div>
    );
  }

  const current = STEPS.findIndex((s) => s.key === order.status);
  const timeOf = (key: string) => [...order.history].reverse().find((h) => h.status === key)?.at ?? null;

  return (
    <div>
      <h3 className="mb-6 text-2xl font-bold">{t.track.timeline}</h3>
      <ol className="relative">
        {STEPS.map(({ key, Icon }, i) => {
          const done = i < current;
          const active = i === current;
          const time = i <= current ? fmt(timeOf(key)) : null;
          return (
            <li key={key} className="relative flex gap-4 pb-7 last:pb-0">
              {i < STEPS.length - 1 && (
                <span className={`absolute start-[19.5px] top-10 h-[calc(100%-2.5rem)] w-px ${i < current ? "bg-gradient-to-b from-gold to-gold/60" : "bg-line"}`} aria-hidden="true" />
              )}
              <span
                className={`relative flex h-10 w-10 shrink-0 items-center justify-center rounded-full transition ${
                  done ? "bg-gold text-white" : active ? "bg-brand-900 text-white ring-4 ring-gold/25" : "border border-line bg-white text-muted"
                }`}
              >
                {active && order.status !== "delivered" && <span className="absolute inset-0 animate-ping rounded-full bg-gold opacity-30" aria-hidden="true" />}
                <Icon className="relative h-5 w-5" strokeWidth={1.5} />
              </span>
              <div className="pt-1.5">
                <p className={`font-display text-lg font-bold ${i <= current ? "text-ink" : "text-muted"}`}>{t.track.steps[key]}</p>
                {active && <p className="mt-0.5 text-sm text-muted">{t.track.stepHints[key]}</p>}
                {time && <p className="mt-0.5 text-xs text-muted/80">{time}</p>}
              </div>
            </li>
          );
        })}
      </ol>
    </div>
  );
}

export function OrderDetails({ order, currency }: { order: Order; currency: string }) {
  const { t, locale } = useI18n();
  const o = t.order;

  return (
    <div className="mt-6 grid gap-6 md:grid-cols-[1fr_1.1fr] lg:gap-8">
      <div className="rounded-3xl bg-white p-6 shadow-soft ring-1 ring-line/60 sm:p-8">
        <OrderTimeline order={order} />
      </div>

      <div className="relative space-y-5 overflow-hidden rounded-3xl bg-white p-6 shadow-soft ring-1 ring-line/60 sm:p-8">
        <span className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold to-transparent" aria-hidden="true" />
        <div className="flex items-center justify-between">
          <span className="text-sm text-muted">{o.number}</span>
          <span className="font-display text-xl font-bold tracking-wider" dir="ltr">{order.number}</span>
        </div>
        <ul className="divide-y divide-line border-y border-line">
          {order.items.map((item, i) => (
            <li key={i} className="flex items-center gap-3 py-3">
              <div className="relative h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-sand">
                {item.image && <Image src={item.image} alt="" fill sizes="56px" className="object-cover" />}
              </div>
              <span className="flex-1 font-display text-base font-bold">{item.name} <span className="font-sans text-sm font-normal text-muted">× {item.quantity}</span></span>
              <span className="text-sm font-semibold">{formatPrice(item.total, currency)}</span>
            </li>
          ))}
        </ul>

        <dl className="grid gap-x-6 gap-y-3 text-sm sm:grid-cols-2">
          <div><dt className="text-muted">{o.recipient}</dt><dd className="font-medium">{order.recipient_name}</dd></div>
          {order.delivery_zone && <div><dt className="text-muted">{o.zone}</dt><dd className="font-medium">{order.delivery_zone}</dd></div>}
          {order.delivery_date && (
            <div>
              <dt className="text-muted">{o.deliveryDate}</dt>
              <dd className="font-medium">
                {new Date(`${order.delivery_date}T12:00:00`).toLocaleDateString(locale === "ar" ? "ar-EG" : "en-GB", { weekday: "long", day: "numeric", month: "long" })}
                {order.delivery_time && ` — ${order.delivery_time}`}
              </dd>
            </div>
          )}
          <div><dt className="text-muted">{o.payment}</dt><dd className="font-medium">{order.payment_method_label} ({order.payment_status_label})</dd></div>
        </dl>

        <dl className="space-y-2 border-t border-line pt-4 text-sm">
          <div className="flex justify-between"><dt className="text-muted">{o.subtotal}</dt><dd>{formatPrice(order.subtotal, currency)}</dd></div>
          <div className="flex justify-between"><dt className="text-muted">{o.delivery}</dt><dd>{formatPrice(order.delivery_fee, currency)}</dd></div>
          <div className="flex items-baseline justify-between border-t border-line pt-3 font-semibold"><dt>{o.total}</dt><dd className="font-display text-2xl font-bold tabular-nums">{formatPrice(order.total, currency)}</dd></div>
        </dl>
      </div>
    </div>
  );
}
