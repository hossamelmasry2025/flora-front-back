import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { ArrowLeft } from "lucide-react";
import { Breadcrumbs } from "@/components/Breadcrumbs";
import { FlowerMark } from "@/components/Logo";
import { SetAlternates } from "@/components/SetAlternates";
import { Sections } from "@/components/sections/Sections";
import { getSettings } from "@/lib/settings";
import { localePath } from "@/i18n/config";
import { api } from "@/lib/api";
import { resolveLocale } from "@/lib/locale";
import { decodeSlug, excerpt, pageUrl, seoAlternates } from "@/lib/utils";

type Props = { params: Promise<{ lang: string; slug: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale } = await resolveLocale(params);
  const page = await api.page(decodeSlug((await params).slug), locale);
  if (!page) return {};
  return {
    title: page.meta_title || page.title,
    description: page.meta_description || page.subtitle || excerpt(page.content),
    alternates: seoAlternates(locale, pageUrl(page.slugs.ar), pageUrl(page.slugs.en)),
    openGraph: page.image ? { images: [page.image] } : undefined,
  };
}

export default async function StaticPage({ params }: Props) {
  const { locale, t, lp } = await resolveLocale(params);
  const [page, settings] = await Promise.all([api.page(decodeSlug((await params).slug), locale), getSettings(locale)]);
  if (!page) notFound();

  return (
    <>
      <SetAlternates ar={localePath("ar", pageUrl(page.slugs.ar))} en={localePath("en", pageUrl(page.slugs.en))} />

      {/* غلاف الصفحة */}
      <header className="relative isolate overflow-hidden bg-brand-950">
        {page.image ? (
          <Image src={page.image} alt="" fill sizes="100vw" className="-z-10 object-cover opacity-55" loading="eager" fetchPriority="high" />
        ) : (
          <>
            <FlowerMark className="absolute -end-16 -top-16 -z-10 h-96 w-96 text-white opacity-[0.04]" />
            <FlowerMark className="absolute -bottom-20 -start-10 -z-10 h-72 w-72 text-white opacity-[0.03]" />
          </>
        )}
        <div className="absolute inset-0 -z-10 bg-gradient-to-t from-brand-950 via-brand-950/60 to-brand-950/20" />
        <span className="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent" aria-hidden="true" />
        <div className="container-page py-20 text-center text-white sm:py-32">
          <span className="mb-6 flex items-center justify-center gap-2 text-gold-light" aria-hidden="true">
            <span className="h-px w-12 bg-current" />
            <span className="text-[10px]">✦</span>
            <span className="h-px w-12 bg-current" />
          </span>
          <h1 className="font-display text-5xl font-bold text-white sm:text-6xl">{page.title}</h1>
          {page.subtitle && <p className="mx-auto mt-5 max-w-2xl text-lg font-light leading-9 text-white/85">{page.subtitle}</p>}
        </div>
      </header>

      <div className="container-page py-10 sm:py-16">
        <div className="mx-auto max-w-3xl">
          <Breadcrumbs items={[{ name: page.title }]} locale={locale} />
          <article className="relative mt-4">
            {page.content && <div className="prose-content prose-page relative" dangerouslySetInnerHTML={{ __html: page.content }} />}
          </article>

        </div>
      </div>

      {page.sections?.length > 0 && <Sections sections={page.sections} currency={settings.currency} locale={locale} firstIsPageHeading={false} />}

      <div className="container-page pb-12">
        <div className="mx-auto max-w-3xl">
          {/* دعوة للشراء في آخر الصفحة */}
          <div className="relative mt-10 flex flex-col items-center gap-5 overflow-hidden rounded-[2rem] bg-gradient-to-br from-brand-900 to-brand-950 p-10 text-center text-white shadow-luxe sm:p-14">
            <FlowerMark className="pointer-events-none absolute -end-10 -top-10 h-48 w-48 text-white opacity-5" />
            <span className="eyebrow justify-center text-gold-light" aria-hidden="true"><span className="text-[10px]">✦</span></span>
            <p className="relative font-display text-3xl font-bold sm:text-4xl">{t.about.ctaText}</p>
            <Link href={lp("/products")} className="btn-gold relative px-9 py-4">
              {t.about.cta} <ArrowLeft className="h-4 w-4 ltr:rotate-180" />
            </Link>
          </div>
        </div>
      </div>
    </>
  );
}
