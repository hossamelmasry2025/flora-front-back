import type { Metadata } from "next";
import Link from "next/link";
import { PageHeader } from "@/components/PageHeader";
import { Breadcrumbs } from "@/components/Breadcrumbs";
import { PriceFilter } from "@/components/PriceFilter";
import { ProductListing } from "@/components/ProductListing";
import { Sections } from "@/components/sections/Sections";
import { api } from "@/lib/api";
import { resolveLocale } from "@/lib/locale";
import { isFilteredListing, parseListingParams, type RawSearchParams } from "@/lib/search-params";
import { getSettings } from "@/lib/settings";
import { categoryUrl, seoAlternates } from "@/lib/utils";

type Props = { params: Promise<{ lang: string }>; searchParams: Promise<RawSearchParams> };

export async function generateMetadata({ params, searchParams }: Props): Promise<Metadata> {
  const { locale, t } = await resolveLocale(params);
  const p = parseListingParams(await searchParams);
  const s = await getSettings(locale);

  if (p.q) {
    return { title: t.search.resultsFor(p.q), robots: { index: false, follow: true } };
  }

  const path = p.page > 1 ? `/products?page=${p.page}` : "/products";
  return {
    title: p.page > 1 ? `${t.listing.allTitle} — ${t.common.page(p.page)}` : t.listing.allTitle,
    description: t.listing.allDescription(s.site_name),
    alternates: seoAlternates(locale, path),
    robots: isFilteredListing(p) ? { index: false, follow: true } : undefined,
  };
}

export default async function ProductsPage({ params, searchParams }: Props) {
  const { locale, t, lp } = await resolveLocale(params);
  const p = parseListingParams(await searchParams);
  const [result, settings, categories, sections] = await Promise.all([
    api.products({ ...p, per_page: 12 }, locale),
    getSettings(locale),
    api.categories(locale),
    api.sections("products_page", locale),
  ]);

  return (
    <>
    {!p.q && p.page === 1 && sections.length > 0 && <Sections sections={sections} currency={settings.currency} locale={locale} firstIsPageHeading={false} />}
    <div className="container-page py-8 sm:py-12">
      <Breadcrumbs items={[{ name: p.q ? t.search.results : t.nav.allBouquets }]} locale={locale} />

      <PageHeader title={p.q ? t.search.resultsFor(p.q) : t.listing.allTitle}>
        <form action={lp("/products")} role="search" className="mt-6 flex max-w-xl gap-2">
          <label htmlFor="q" className="sr-only">{t.search.label}</label>
          <input id="q" name="q" type="search" defaultValue={p.q} placeholder={t.search.pagePlaceholder} className="input rounded-full" />
          <button type="submit" className="btn-primary shrink-0">{t.search.button}</button>
        </form>
      </PageHeader>

      <div className="grid gap-10 lg:grid-cols-[240px_1fr]">
        <aside className="space-y-6 lg:sticky lg:top-28 lg:self-start" aria-label={t.listing.filters}>
          <div className="rounded-3xl bg-white/70 p-5 ring-1 ring-line/60">
            <h2 className="mb-3 text-lg font-bold">{t.nav.categories}</h2>
            <ul className="scrollbar-none -mx-1 flex gap-2 overflow-x-auto px-1 lg:flex-col lg:gap-0.5">
              {categories.map((c) => (
                <li key={c.id} className="shrink-0">
                  <Link href={lp(categoryUrl(c.slug))} className="block rounded-full border border-line px-4 py-2 text-sm transition hover:border-gold hover:text-gold-dark lg:rounded-xl lg:border-0 lg:px-3 lg:hover:bg-sand/50">
                    {c.name}
                  </Link>
                </li>
              ))}
            </ul>
          </div>
          <PriceFilter min={p.min} max={p.max} currency={settings.currency} />
        </aside>

        <section aria-label={t.listing.products}>
          <ProductListing
            result={result}
            currency={settings.currency}
            basePath={lp("/products")}
            locale={locale}
            params={{ q: p.q, sort: p.sort, min: p.min?.toString(), max: p.max?.toString(), sale: p.sale ? "1" : undefined }}
          />
        </section>
      </div>
    </div>
    </>
  );
}
