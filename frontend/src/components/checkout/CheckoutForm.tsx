"use client";

import Image from "next/image";
import { useRouter } from "next/navigation";
import { useMemo, useState } from "react";
import { Banknote, Check, Copy, Loader2, Lock, Upload, Wallet, X } from "lucide-react";
import { useI18n } from "@/i18n/client";
import { cartSubtotal, useCart } from "@/lib/cart";
import { useMyOrders } from "@/lib/myOrders";
import type { DeliveryZone, Order, Settings } from "@/lib/types";
import { useHydrated } from "@/lib/useHydrated";
import { API_PUBLIC_URL, cn, formatPrice } from "@/lib/utils";
import { EmptyState } from "../EmptyState";
import { FlowerMark } from "../Logo";
import { earliestDeliveryDate } from "./dates";

type Errors = Record<string, string>;

export function CheckoutForm({ settings, zones }: { settings: Settings; zones: DeliveryZone[] }) {
  const { t, lp, locale } = useI18n();
  const c = t.checkout;
  const router = useRouter();
  const hydrated = useHydrated();
  const { items, clear } = useCart();
  const saveOrder = useMyOrders((s) => s.add);
  const { payment, checkout, currency } = settings;

  const methods = [
    payment.cod_enabled && { id: "cod" as const, label: c.cod, desc: c.codDesc, Icon: Banknote },
    payment.transfer_enabled && { id: "transfer" as const, label: c.transfer, desc: c.transferDesc, Icon: Wallet },
  ].filter(Boolean) as { id: "cod" | "transfer"; label: string; desc: string; Icon: typeof Banknote }[];

  const minDate = useMemo(() => earliestDeliveryDate(checkout.same_day_cutoff), [checkout.same_day_cutoff]);

  const [zoneId, setZoneId] = useState<string>("");
  const [method, setMethod] = useState<"cod" | "transfer">(methods[0]?.id ?? "cod");
  const [withCard, setWithCard] = useState(true);
  const [card, setCard] = useState("");
  const [proof, setProof] = useState<File | null>(null);
  const [errors, setErrors] = useState<Errors>({});
  const [submitting, setSubmitting] = useState(false);

  const subtotal = cartSubtotal(items);
  const zone = zones.find((z) => String(z.id) === zoneId);
  const fee = zone?.fee ?? 0;
  const total = subtotal + fee;
  const belowMin = checkout.min_order > 0 && subtotal < checkout.min_order;

  if (!hydrated) return <div className="h-96 animate-pulse rounded-3xl bg-sand" />;

  if (items.length === 0) {
    return (
      <EmptyState text={t.cart.emptyShort} action={{ href: lp("/products"), label: t.cart.choose }} />
    );
  }

  const submit = async (e: React.FormEvent<HTMLFormElement>) => {
    e.preventDefault();
    const fd = new FormData(e.currentTarget);

    const local: Errors = {};
    if (method === "transfer" && !proof) local.payment_proof = c.proofRequired;
    if (zones.length && !zoneId) local.delivery_zone_id = c.zoneRequired;
    if (Object.keys(local).length) {
      setErrors(local);
      scrollToFirstError(local);
      return;
    }

    items.forEach((item, i) => {
      fd.append(`items[${i}][product_id]`, String(item.id));
      fd.append(`items[${i}][quantity]`, String(item.quantity));
    });
    fd.delete("payment_choice");
    fd.set("payment_method", method);
    fd.set("hide_sender", fd.get("hide_sender") ? "1" : "0");
    if (!withCard) fd.delete("card_message");
    if (proof) fd.set("payment_proof", proof);
    const phone = String(fd.get("sender_phone") ?? "");

    setSubmitting(true);
    setErrors({});
    try {
      const res = await fetch(`${API_PUBLIC_URL}/api/orders?lang=${locale}`, { method: "POST", body: fd, headers: { Accept: "application/json" } });
      const json = await res.json().catch(() => ({}));

      if (res.status === 422) {
        const errs: Errors = Object.fromEntries(Object.entries(json.errors ?? {}).map(([k, v]) => [k.startsWith("items") ? "items" : k, (v as string[])[0]]));
        setErrors(errs);
        scrollToFirstError(errs);
        return;
      }
      if (!res.ok) throw new Error(json.message ?? "Request failed");

      const order: Order = json.data;
      sessionStorage.setItem(`order:${order.number}`, JSON.stringify(order));
      saveOrder({ number: order.number, phone, total: order.total, created_at: order.created_at });
      clear();
      router.replace(lp(`/order/${order.number}`));
    } catch {
      setErrors({ form: c.networkError });
    } finally {
      setSubmitting(false);
    }
  };

  return (
    <form onSubmit={submit} noValidate className="grid gap-8 lg:grid-cols-[1fr_400px]">
      <div className="space-y-6">
        <Fieldset step={1} title={c.sender}>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field name="sender_name" label={c.yourName} errors={errors} required autoComplete="name" />
            <Field name="sender_phone" label={c.yourPhone} errors={errors} required type="tel" inputMode="tel" autoComplete="tel" dir="ltr" placeholder="01xxxxxxxxx" />
          </div>
          <Field name="sender_email" label={c.email} errors={errors} type="email" autoComplete="email" dir="ltr" />
          <label className="flex cursor-pointer items-center gap-3 rounded-2xl bg-sand/50 px-4 py-3 text-sm">
            <input type="checkbox" name="hide_sender" value="1" className="h-4 w-4 accent-brand-700" />
            <span>{c.hideSender}</span>
          </label>
        </Fieldset>

        <Fieldset step={2} title={c.recipient}>
          <div className="grid gap-4 sm:grid-cols-2">
            <Field name="recipient_name" label={c.recipientName} errors={errors} required />
            <Field name="recipient_phone" label={c.recipientPhone} errors={errors} required type="tel" inputMode="tel" dir="ltr" placeholder="01xxxxxxxxx" />
          </div>
          {zones.length > 0 && (
            <div>
              <label htmlFor="delivery_zone_id" className="label">{c.zone} *</label>
              <select id="delivery_zone_id" name="delivery_zone_id" value={zoneId} onChange={(e) => setZoneId(e.target.value)} className={cn("input", errors.delivery_zone_id && "border-brand-500")} required>
                <option value="">{c.chooseZone}</option>
                {zones.map((z) => (
                  <option key={z.id} value={z.id}>{z.name} — {z.fee > 0 ? formatPrice(z.fee, currency) : c.freeDelivery}</option>
                ))}
              </select>
              <FieldError name="delivery_zone_id" errors={errors} />
            </div>
          )}
          <Field name="address" label={c.address} errors={errors} required textarea placeholder={c.addressPlaceholder} />
          <div className="grid gap-4 sm:grid-cols-2">
            <Field name="delivery_date" label={c.date} errors={errors} type="date" min={minDate} defaultValue={minDate} required />
            {checkout.time_slots.length > 0 && (
              <div>
                <label htmlFor="delivery_time" className="label">{c.time}</label>
                <select id="delivery_time" name="delivery_time" className="input">
                  {checkout.time_slots.map((slot) => <option key={slot} value={slot}>{slot}</option>)}
                </select>
              </div>
            )}
          </div>
        </Fieldset>

        <Fieldset step={3} title={c.card}>
          <label className="flex cursor-pointer items-center gap-3 text-sm font-medium">
            <input type="checkbox" checked={withCard} onChange={(e) => setWithCard(e.target.checked)} className="h-4 w-4 accent-brand-700" />
            {c.wantCard}
          </label>
          {withCard && (
            <div>
              <label htmlFor="card_message" className="label">{c.cardMessage}</label>
              {/* شكل الكارت نفسه */}
              <div className="relative rounded-2xl border border-gold/40 bg-[#fffaf3] p-1 shadow-soft">
                <FlowerMark className="pointer-events-none absolute end-3 top-3 h-8 w-8 text-brand-100" />
                <textarea
                  id="card_message"
                  name="card_message"
                  rows={4}
                  maxLength={checkout.card_max_length}
                  value={card}
                  onChange={(e) => setCard(e.target.value)}
                  placeholder={c.cardPlaceholder}
                  className="w-full resize-none rounded-xl bg-transparent px-4 py-3 font-display text-lg leading-9 placeholder:text-muted/60 focus:outline-none"
                />
              </div>
              <div className="mt-2 flex flex-wrap items-center justify-between gap-2">
                <div className="flex flex-wrap gap-1.5">
                  {c.cardSuggestions.map((s) => (
                    <button key={s} type="button" onClick={() => setCard((v) => (v ? `${v} ${s}` : s).slice(0, checkout.card_max_length))} className="rounded-full border border-line bg-white px-3 py-1 text-xs transition hover:border-gold hover:text-gold-dark">
                      {s}
                    </button>
                  ))}
                </div>
                <span className={cn("text-xs", card.length >= checkout.card_max_length ? "text-brand-600" : "text-muted")}>
                  {card.length}/{checkout.card_max_length}
                </span>
              </div>
              <FieldError name="card_message" errors={errors} />
            </div>
          )}
          <Field name="notes" label={c.notes} errors={errors} textarea placeholder={c.notesPlaceholder} />
        </Fieldset>

        <Fieldset step={4} title={c.payment}>
          <div className="grid gap-3 sm:grid-cols-2" role="radiogroup" aria-label={c.payment}>
            {methods.map(({ id, label, desc, Icon }) => (
              <label
                key={id}
                className={cn(
                  "flex cursor-pointer items-start gap-3 rounded-2xl border p-4 transition duration-300",
                  method === id ? "border-gold bg-gold/5 shadow-soft ring-1 ring-gold/40" : "border-line bg-white hover:border-gold/50",
                )}
              >
                <input type="radio" name="payment_choice" value={id} checked={method === id} onChange={() => setMethod(id)} className="sr-only" />
                <Icon className={cn("mt-0.5 h-6 w-6 shrink-0", method === id ? "text-gold-dark" : "text-muted")} strokeWidth={1.5} />
                <span>
                  <span className="block font-semibold">{label}</span>
                  <span className="mt-0.5 block text-xs text-muted">{desc}</span>
                </span>
                {method === id && <span className="ms-auto flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-gold text-white"><Check className="h-3.5 w-3.5" strokeWidth={2.5} /></span>}
              </label>
            ))}
          </div>

          {method === "transfer" && <TransferBox total={total} currency={currency} payment={payment} proof={proof} setProof={setProof} errors={errors} />}
          <FieldError name="payment_method" errors={errors} />
        </Fieldset>
      </div>

      <aside className="h-fit lg:sticky lg:top-28">
        <div className="relative space-y-4 overflow-hidden rounded-3xl bg-white p-7 shadow-luxe ring-1 ring-line/60">
          <span className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold to-transparent" aria-hidden="true" />
          <h2 className="text-2xl font-bold">{t.cart.summary}</h2>
          <ul className="divide-y divide-line">
            {items.map((item) => (
              <li key={item.id} className="flex items-center gap-3 py-3">
                <div className="relative h-16 w-14 shrink-0">
                  <div className="absolute inset-0 overflow-hidden rounded-xl bg-sand">
                    {item.image && <Image src={item.image} alt="" fill sizes="56px" className="object-cover" />}
                  </div>
                  <span className="absolute -end-1.5 -top-1.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-gold px-1 text-[11px] font-bold text-white ring-2 ring-white">{item.quantity}</span>
                </div>
                <span className="flex-1 font-display text-base font-bold leading-snug">{item.name}</span>
                <span className="text-sm font-semibold tabular-nums">{formatPrice(item.price * item.quantity, currency)}</span>
              </li>
            ))}
          </ul>
          <dl className="space-y-2 border-t border-line pt-4 text-sm">
            <div className="flex justify-between"><dt className="text-muted">{t.cart.items}</dt><dd>{formatPrice(subtotal, currency)}</dd></div>
            <div className="flex justify-between">
              <dt className="text-muted">{t.cart.delivery}</dt>
              <dd>{zone ? (fee > 0 ? formatPrice(fee, currency) : c.free) : c.selectZone}</dd>
            </div>
            <div className="flex items-baseline justify-between border-t border-line pt-4 font-semibold">
              <dt>{t.cart.total}</dt><dd className="font-display text-3xl font-bold tabular-nums">{formatPrice(total, currency)}</dd>
            </div>
          </dl>

          {belowMin && <p className="rounded-xl bg-brand-50 p-3 text-sm text-brand-800">{c.minOrder} {formatPrice(checkout.min_order, currency)}</p>}
          {(errors.items || errors.form) && <p role="alert" className="rounded-xl bg-brand-50 p-3 text-sm text-brand-800">{errors.items ?? errors.form}</p>}

          <button type="submit" disabled={submitting || belowMin} className="btn-primary w-full py-4 text-base">
            {submitting ? <><Loader2 className="h-5 w-5 animate-spin" /> {c.submitting}</> : <>{c.submit} — {formatPrice(total, currency)}</>}
          </button>
          <p className="flex items-center justify-center gap-1.5 text-center text-xs text-muted"><Lock className="h-3.5 w-3.5 text-gold" strokeWidth={1.75} /> {c.contactNote}</p>
        </div>
      </aside>
    </form>
  );
}

function scrollToFirstError(errors: Errors) {
  const first = Object.keys(errors)[0];
  const el = document.getElementById(first) ?? document.querySelector(`[name="${first}"]`);
  el?.scrollIntoView({ behavior: "smooth", block: "center" });
  if (el instanceof HTMLElement) el.focus({ preventScroll: true });
}

function Fieldset({ step, title, children }: { step: number; title: string; children: React.ReactNode }) {
  return (
    <fieldset className="space-y-5 rounded-3xl bg-white p-6 shadow-soft ring-1 ring-line/60 sm:p-8">
      <legend className="sr-only">{title}</legend>
      <h2 className="flex items-center gap-4 border-b border-line pb-5 text-2xl font-bold">
        <span className="flex h-11 w-11 shrink-0 items-center justify-center rounded-full border border-gold/50 font-display text-lg text-gold-dark tabular-nums">
          {String(step).padStart(2, "0")}
        </span>
        {title}
      </h2>
      {children}
    </fieldset>
  );
}

type FieldProps = React.InputHTMLAttributes<HTMLInputElement> & { name: string; label: string; errors: Errors; textarea?: boolean };

function Field({ name, label, errors, textarea, required, ...rest }: FieldProps) {
  const error = errors[name];
  const cls = cn("input", error && "border-brand-500 focus:border-brand-500");
  return (
    <div>
      <label htmlFor={name} className="label">{label}{required && " *"}</label>
      {textarea ? (
        <textarea id={name} name={name} rows={3} required={required} className={cn(cls, "resize-none")} placeholder={rest.placeholder} aria-invalid={!!error} aria-describedby={error ? `${name}-error` : undefined} />
      ) : (
        <input id={name} name={name} required={required} className={cls} aria-invalid={!!error} aria-describedby={error ? `${name}-error` : undefined} {...rest} />
      )}
      <FieldError name={name} errors={errors} />
    </div>
  );
}

function FieldError({ name, errors }: { name: string; errors: Errors }) {
  if (!errors[name]) return null;
  return <p id={`${name}-error`} role="alert" className="mt-1.5 text-xs font-medium text-brand-700">{errors[name]}</p>;
}

function TransferBox({
  total, currency, payment, proof, setProof, errors,
}: {
  total: number; currency: string; payment: Settings["payment"]; proof: File | null; setProof: (f: File | null) => void; errors: Errors;
}) {
  const { t } = useI18n();
  const c = t.checkout;
  const [copied, setCopied] = useState<string | null>(null);
  const preview = useMemo(() => (proof ? URL.createObjectURL(proof) : null), [proof]);

  const copy = async (text: string) => {
    try {
      await navigator.clipboard.writeText(text);
      setCopied(text);
      setTimeout(() => setCopied(null), 1500);
    } catch {}
  };

  return (
    <div className="space-y-4 rounded-2xl border border-gold/40 bg-[#fbf6ee] p-5">
      <p className="text-sm leading-7">
        {c.transferIntro} <strong className="text-brand-800">{formatPrice(total, currency)}</strong> {c.transferIntroEnd}
      </p>
      <ul className="space-y-2">
        {payment.transfer_accounts.map((a) => (
          <li key={a.number} className="flex items-center justify-between gap-3 rounded-xl bg-white px-4 py-3">
            <div>
              <p className="text-xs text-muted">{a.name}{a.holder && ` — ${a.holder}`}</p>
              <p className="font-bold tracking-wide" dir="ltr">{a.number}</p>
            </div>
            <button type="button" onClick={() => copy(a.number)} className="flex items-center gap-1 rounded-full border border-line px-3 py-1.5 text-xs font-medium transition hover:border-gold hover:text-gold-dark">
              {copied === a.number ? <><Check className="h-3.5 w-3.5" /> {c.copied}</> : <><Copy className="h-3.5 w-3.5" /> {c.copy}</>}
            </button>
          </li>
        ))}
      </ul>
      {payment.transfer_instructions && <p className="text-sm text-muted">{payment.transfer_instructions}</p>}

      <div>
        <span className="label">{c.proof} *</span>
        {proof && preview ? (
          <div className="flex items-center gap-3 rounded-xl bg-white p-3">
            {/* eslint-disable-next-line @next/next/no-img-element */}
            <img src={preview} alt={c.proof} className="h-16 w-16 rounded-lg object-cover" />
            <span className="flex-1 truncate text-sm">{proof.name}</span>
            <button type="button" onClick={() => setProof(null)} className="rounded-full p-2 hover:bg-brand-50" aria-label={c.removeImage}><X className="h-4 w-4" /></button>
          </div>
        ) : (
          <label htmlFor="payment_proof" className={cn("flex cursor-pointer flex-col items-center gap-2 rounded-xl border-2 border-dashed bg-white px-4 py-6 text-center transition hover:border-gold", errors.payment_proof ? "border-brand-500" : "border-line")}>
            <Upload className="h-6 w-6 text-gold-dark" strokeWidth={1.5} />
            <span className="text-sm font-medium">{c.upload}</span>
            <span className="text-xs text-muted">{c.uploadHint}</span>
          </label>
        )}
        <input
          id="payment_proof"
          type="file"
          accept="image/*"
          className="sr-only"
          onChange={(e) => {
            const f = e.target.files?.[0] ?? null;
            if (f && f.size > 5 * 1024 * 1024) return alert(c.tooLarge);
            setProof(f);
          }}
        />
        <FieldError name="payment_proof" errors={errors} />
      </div>
    </div>
  );
}
