"use client";

import Link from "next/link";
import { useSyncExternalStore } from "react";
import { Check } from "lucide-react";
import { useI18n } from "@/i18n/client";
import type { Order } from "@/lib/types";
import { formatPrice, whatsappLink } from "@/lib/utils";
import { FlowerMark } from "./Logo";
import { OrderDetails } from "./OrderDetails";
import { SocialIcon } from "./SocialIcon";

const noop = () => () => {};

export function OrderConfirmation({ number, currency, whatsapp, siteName }: { number: string; currency: string; whatsapp: string | null; siteName: string }) {
  const { t, lp } = useI18n();
  const o = t.order;

  // الطلب اتحفظ في المتصفح لحظة الإرسال
  const raw = useSyncExternalStore(
    noop,
    () => {
      try {
        return sessionStorage.getItem(`order:${number}`);
      } catch {
        return null;
      }
    },
    () => undefined,
  );

  if (raw === undefined) return <div className="mx-auto h-96 max-w-4xl animate-pulse rounded-3xl bg-sand" />;

  const order: Order | null = raw ? JSON.parse(raw) : null;
  const wa = whatsappLink(whatsapp, o.whatsappMessage(siteName, number, order ? formatPrice(order.total, currency) : ""));

  return (
    <div className="mx-auto max-w-4xl">
      <div className="relative overflow-hidden rounded-[2rem] bg-white px-6 py-12 text-center shadow-luxe ring-1 ring-line/60 sm:px-12 sm:py-16">
        <span className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold to-transparent" aria-hidden="true" />
        <FlowerMark className="pointer-events-none absolute -end-12 -top-12 h-48 w-48 text-brand-200 opacity-25" />
        <FlowerMark className="pointer-events-none absolute -bottom-14 -start-14 h-40 w-40 text-brand-200 opacity-25" />
        <span className="relative mx-auto flex h-20 w-20 animate-fade-up items-center justify-center rounded-full border border-gold/40">
          <span className="flex h-14 w-14 items-center justify-center rounded-full bg-gold text-white shadow-lift">
            <Check className="h-7 w-7" strokeWidth={2} />
          </span>
        </span>
        <span className="eyebrow relative mt-6 justify-center" aria-hidden="true"><span className="text-[10px]">✦</span></span>
        <h1 className="relative mt-2 font-display text-4xl font-bold sm:text-5xl">{o.thanks}</h1>
        <p className="relative mt-6 text-sm text-muted">{o.number}</p>
        <p className="relative mt-1 font-display text-3xl font-bold tracking-wider" dir="ltr">{number}</p>
        <p className="relative mx-auto mt-5 max-w-md text-[15px] leading-8 text-muted">{order?.payment_method === "transfer" ? o.transferNote : o.codNote}</p>
        <p className="relative mx-auto mt-2 max-w-md text-xs leading-6 text-muted/80">{o.saved}</p>
        <div className="relative mt-8 flex flex-col justify-center gap-3 sm:flex-row">
          {wa && (
            <a href={wa} target="_blank" rel="noopener noreferrer" className="btn-primary bg-[#25D366] hover:bg-[#1fb257]">
              <SocialIcon platform="whatsapp" className="h-5 w-5" /> {o.confirmWhatsapp}
            </a>
          )}
          <Link href={lp(`/track?number=${number}`)} className="btn-outline">{o.track}</Link>
        </div>
      </div>

      {order && <OrderDetails order={order} currency={currency} />}

      <div className="mt-10 text-center">
        <Link href={lp("/")} className="link-underline text-sm font-semibold text-ink">{t.common.backHome}</Link>
      </div>
    </div>
  );
}
