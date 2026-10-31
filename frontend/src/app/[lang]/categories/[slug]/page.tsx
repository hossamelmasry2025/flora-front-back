import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { notFound } from "next/navigation";
import { PageHeader } from "@/components/PageHeader";
import { Breadcrumbs, type Crumb } from "@/components/Breadcrumbs";
import { JsonLd } from "@/components/JsonLd";
import { ProductListing } from "@/components/ProductListing";
import { SetAlternates } from "@/components/SetAlternates";
import { Sections } from "@/components/sections/Sections";
import { localePath } from "@/i18n/config";
import { api } from "@/lib/api";
import { resolveLocale } from "@/lib/locale";
import { isFilteredListing, parseListingParams, type RawSearchParams } from "@/lib/search-params";
import { getSettings } from "@/lib/settings";
import { absoluteUrl, categoryUrl, decodeSlug, excerpt, productUrl, seoAlternates } from "@/lib/utils";

type Props = { params: Promise<{ lang: string; slug: string }>; searchParams: Promise<RawSearchParams> };

export async function generateMetadata({ params, searchParams }: Props): Promise<Metadata> {
  const { locale, t } = await resolveLocale(params);
  const slug = decodeSlug((await params).slug);
  const p = parseListingParams(await searchParams);
  const [category, s] = await Promise.all([api.category(slug, locale), getSettings(locale)]);
  if (!category) return { title: t.category.notFound };

  const page = p.page > 1 ? `?page=${p.page}` : "";
  const title = category.meta_title || `${category.name} — ${t.category.titleSuffix}`;
  const description = category.meta_description || category.description || excerpt(category.content) || t.category.fallbackDescription(category.name, s.site_name);
  const image = category.banner_image ?? category.image;
  const alternates = seoAlternates(locale, categoryUrl(category.slugs.ar) + page, categoryUrl(category.slugs.en) + page);

  return {
    title: p.page > 1 ? `${title} — ${t.common.page(p.page)}` : title,
    description,
    alternates,
    robots: isFilteredListing(p) ? { index: false, follow: true } : undefined,
    openGraph: { title, description, url: alternates.canonical, images: image ? [image] : undefined },
  };
}

export default async function CategoryPage({ params, searchParams }: Props) {
  const { locale, t, lp } = await resolveLocale(params);
  const slug = decodeSlug((await params).slug);
  const p = parseListingParams(await searchParams);

  const [category, result, settings] = await Promise.all([
    api.category(slug, locale),
    api.products({ category: slug, page: p.page, sort: p.sort, per_page: 12 }, locale),
    getSettings(locale),
  ]);
  if (!category) notFound();

  const crumbs: Crumb[] = [{ name: t.nav.categories, href: "/categories" }];
  if (category.parent) crumbs.push({ name: category.parent.name, href: categoryUrl(category.parent.slug) });
  crumbs.push({ name: category.name });

  return (
    <>
      <SetAlternates ar={localePath("ar", categoryUrl(category.slugs.ar))} en={localePath("en", categoryUrl(category.slugs.en))} />
      {category.banner_image && (
        <div className="relative h-48 bg-sand sm:h-72">
          <Image src={category.banner_image} alt={category.name} fill sizes="100vw" className="object-cover" loading="eager" fetchPriority="high" />
        </div>
      )}
      <div className="container-page pt-8 sm:pt-12">
        <Breadcrumbs items={crumbs} locale={locale} />

        <PageHeader title={category.name} subtitle={category.description} className="!mb-0" />

        {category.children.length > 0 && (
          <ul className="scrollbar-none -mx-4 mt-8 flex gap-2 overflow-x-auto px-4">
            {category.children.map((c) => (
              <li key={c.id} className="shrink-0">
                <Link href={lp(categoryUrl(c.slug))} className="block rounded-full border border-line bg-white/70 px-5 py-2.5 text-sm font-medium transition duration-300 hover:border-gold hover:text-gold-dark">
                  {c.name}
                </Link>
              </li>
            ))}
          </ul>
        )}

      </div>

      {/* بانرات وسكاشن فوق المنتجات (من الداش بورد) */}
      {p.page === 1 && category.sections_top?.length > 0 && (
        <div className="-mb-6 [&>section:first-child]:pt-8">
          <Sections sections={category.sections_top} currency={settings.currency} locale={locale} firstIsPageHeading={false} />
        </div>
      )}

      <div className="container-page py-10">
        <ProductListing result={result} currency={settings.currency} basePath={lp(categoryUrl(category.slug))} params={{ sort: p.sort }} locale={locale} />

        {/* نص SEO أسفل الصفحة */}
        {category.content && p.page === 1 && (
          <section className="mx-auto mt-14 max-w-3xl border-t border-line pt-10 sm:mt-16">
            <div className="prose-content" dangerouslySetInnerHTML={{ __html: category.content }} />
          </section>
        )}
      </div>

      {p.page === 1 && category.sections_bottom?.length > 0 && (
        <Sections sections={category.sections_bottom} currency={settings.currency} locale={locale} firstIsPageHeading={false} />
      )}

      <JsonLd
        data={{
          "@context": "https://schema.org",
          "@type": "CollectionPage",
          name: category.name,
          description: category.description ?? undefined,
          url: absoluteUrl(lp(categoryUrl(category.slug))),
          inLanguage: locale,
          mainEntity: {
            "@type": "ItemList",
            numberOfItems: result.meta.total,
            itemListElement: result.data.map((prod, i) => ({
              "@type": "ListItem",
              position: (result.meta.current_page - 1) * result.meta.per_page + i + 1,
              url: absoluteUrl(lp(productUrl(prod.slug))),
              name: prod.name,
            })),
          },
        }}
      />
    </>
  );
}
