"use client";

import Link from "next/link";
import { FlowerMark } from "@/components/Logo";
import { useI18n } from "@/i18n/client";

export default function NotFound() {
  const { t, lp } = useI18n();
  return (
    <div className="container-page flex flex-col items-center py-24 text-center">
      <span className="flex h-28 w-28 items-center justify-center rounded-full border border-gold/30"><FlowerMark className="h-14 w-14 text-brand-200" /></span>
      <p className="mt-8 font-display text-7xl font-bold text-gold sm:text-8xl">404</p>
      <h1 className="mt-4 text-3xl font-bold sm:text-4xl">{t.notFound.title}</h1>
      <p className="mt-3 max-w-md leading-8 text-muted">{t.notFound.text}</p>
      <div className="mt-8 flex flex-wrap justify-center gap-3">
        <Link href={lp("/")} className="btn-primary">{t.nav.home}</Link>
        <Link href={lp("/products")} className="btn-outline">{t.nav.allBouquets}</Link>
      </div>
    </div>
  );
}
