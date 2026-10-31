import type { Metadata } from "next";
import Link from "next/link";
import { notFound } from "next/navigation";
import { Clock, Mail, ShieldCheck, Truck } from "lucide-react";
import { Breadcrumbs, type Crumb } from "@/components/Breadcrumbs";
import { JsonLd } from "@/components/JsonLd";
import { ProductCard } from "@/components/ProductCard";
import { ProductGallery } from "@/components/ProductGallery";
import { ProductPurchase } from "@/components/ProductPurchase";
import { SetAlternates } from "@/components/SetAlternates";
import { Sections } from "@/components/sections/Sections";
import { Carousel } from "@/components/sections/Carousel";
import { TrackView } from "@/components/TrackView";
import { localePath } from "@/i18n/config";
import { api } from "@/lib/api";
import { resolveLocale } from "@/lib/locale";
import { getSettings } from "@/lib/settings";
import { absoluteUrl, categoryUrl, decodeSlug, discountPercent, excerpt, formatPrice, productUrl, seoAlternates, whatsappLink } from "@/lib/utils";

type Props = { params: Promise<{ lang: string; slug: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale, t } = await resolveLocale(params);
  const res = await api.product(decodeSlug((await params).slug), locale);
  if (!res) return { title: t.product.notFound };
  const p = res.data;

  const title = p.meta_title || p.name;
  const description = p.meta_description || p.short_description || excerpt(p.description) || p.name;
  const images = [p.image, ...p.gallery].filter(Boolean) as string[];
  const alternates = seoAlternates(locale, productUrl(p.slugs.ar), productUrl(p.slugs.en));

  return {
    title,
    description,
    alternates,
    openGraph: { type: "website", title, description, url: alternates.canonical, images: images.slice(0, 4).map((url) => ({ url, alt: p.name })) },
    twitter: { card: "summary_large_image", title, description, images: images.slice(0, 1) },
    other: {
      "product:price:amount": String(p.final_price),
      "product:price:currency": "EGP",
      "product:availability": p.in_stock ? "in stock" : "out of stock",
    },
  };
}

const perkIcons = [Clock, Mail, Truck, ShieldCheck];

export default async function ProductPage({ params }: Props) {
  const { locale, t, lp } = await resolveLocale(params);
  const slug = decodeSlug((await params).slug);
  const [res, settings] = await Promise.all([api.product(slug, locale), getSettings(locale)]);
  if (!res) notFound();

  const { data: p, related, sections = [] } = res;
  const images = [p.image, ...p.gallery].filter(Boolean) as string[];
  const discount = discountPercent(p.price, p.sale_price);
  const mainCategory = p.categories[0];
  const url = absoluteUrl(lp(productUrl(p.slug)));

  const crumbs: Crumb[] = mainCategory
    ? [{ name: mainCategory.name, href: categoryUrl(mainCategory.slug) }, { name: p.name }]
    : [{ name: t.nav.allBouquets, href: "/products" }, { name: p.name }];

  const askLink = whatsappLink(settings.whatsapp, t.product.askMessage(p.name, url));

  return (
    <div className="container-page py-6 sm:py-10">
      <SetAlternates ar={localePath("ar", productUrl(p.slugs.ar))} en={localePath("en", productUrl(p.slugs.en))} />
      <Breadcrumbs items={crumbs} locale={locale} />

      <div className="grid gap-10 lg:grid-cols-[1.1fr_1fr] lg:gap-16">
        <ProductGallery images={images} name={p.name} />

        <div className="lg:py-6">
          {p.categories.length > 0 && (
            <ul className="eyebrow mb-4 flex-wrap gap-y-1">
              {p.categories.map((c, i) => (
                <li key={c.slug} className="flex items-center gap-3">
                  {i > 0 && <span className="text-gold/60" aria-hidden="true">•</span>}
                  <Link href={lp(categoryUrl(c.slug))} className="transition-colors hover:text-brand-700">{c.name}</Link>
                </li>
              ))}
            </ul>
          )}

          <h1 className="font-display text-4xl font-bold leading-tight sm:text-5xl">{p.name}</h1>

          <div className="mt-5 flex flex-wrap items-center gap-3">
            <span className="text-2xl font-semibold tabular-nums text-ink sm:text-3xl">{formatPrice(p.final_price, settings.currency)}</span>
            {p.sale_price && (
              <>
                <del className="text-lg text-muted tabular-nums">{formatPrice(p.price, settings.currency)}</del>
                <span className="rounded-full bg-brand-900 px-3 py-1 text-xs font-bold tracking-wide text-white">{t.product.save(discount)}</span>
              </>
            )}
          </div>

          <span className="mt-6 block h-px w-full bg-gradient-to-l from-gold/60 via-line to-transparent ltr:bg-gradient-to-r" aria-hidden="true" />

          {p.short_description && <p className="mt-6 text-base leading-8 text-ink/75 sm:text-lg sm:leading-9">{p.short_description}</p>}

          <div className="mt-8">
            <ProductPurchase product={p} />
          </div>

          <ul className="mt-8 grid grid-cols-2 gap-x-4 gap-y-5 border-y border-line py-6 text-sm">
            {t.product.perks.map((perk, i) => {
              const Icon = perkIcons[i];
              return (
                <li key={perk} className="flex items-center gap-3 text-ink/85">
                  <span className="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-gold/40 text-gold-dark">
                    <Icon className="h-[18px] w-[18px]" strokeWidth={1.5} />
                  </span>
                  {perk}
                </li>
              );
            })}
          </ul>

          {askLink && (
            <a href={askLink} target="_blank" rel="noopener noreferrer" className="link-underline mt-6 inline-block text-sm font-semibold text-ink">
              {t.product.ask}
            </a>
          )}

          {p.details.length > 0 && (
            <section className="mt-10">
              <h2 className="mb-4 font-display text-xl font-bold">{t.product.specs}</h2>
              <dl className="divide-y divide-line border-y border-line text-sm">
                {p.details.map((d, i) => (
                  <div key={i} className="grid grid-cols-[40%_1fr] gap-3 py-3.5">
                    <dt className="text-muted">{d.label}</dt>
                    <dd className="font-medium">{d.value}</dd>
                  </div>
                ))}
              </dl>
            </section>
          )}
        </div>
      </div>

      {p.description && (
        <section className="mx-auto mt-14 max-w-3xl text-center sm:mt-16">
          <span className="eyebrow mb-3 justify-center" aria-hidden="true"><span className="text-[10px]">✦</span></span>
          <h2 className="section-title mb-6">{t.product.details}</h2>
          <div className="prose-content text-start" dangerouslySetInnerHTML={{ __html: p.description }} />
        </section>
      )}

      {related.length > 0 && (
        <section className="mt-14 sm:mt-16">
          <span className="eyebrow mb-3" aria-hidden="true"><span className="text-[10px]">✦</span></span>
          <h2 className="section-title mb-7 sm:mb-9">{t.product.related}</h2>
          <Carousel label={t.product.relatedLabel}>
            {related.map((r) => (
              <li key={r.id} className="w-[62%] shrink-0 snap-start sm:w-[38%] md:w-[31%] lg:w-[23.5%]">
                <ProductCard product={r} currency={settings.currency} locale={locale} />
              </li>
            ))}
          </Carousel>
        </section>
      )}

      {sections.length > 0 && (
        <div className="-mx-4 mt-10 sm:-mx-6 lg:-mx-8">
          <Sections sections={sections} currency={settings.currency} locale={locale} firstIsPageHeading={false} />
        </div>
      )}

      <TrackView slug={p.slug} />

      <JsonLd
        data={{
          "@context": "https://schema.org",
          "@type": "Product",
          name: p.name,
          description: p.short_description || excerpt(p.description, 500) || p.name,
          image: images,
          sku: p.sku ?? String(p.id),
          brand: { "@type": "Brand", name: settings.site_name },
          category: mainCategory?.name,
          offers: {
            "@type": "Offer",
            url,
            priceCurrency: "EGP",
            price: p.final_price,
            availability: p.in_stock ? "https://schema.org/InStock" : "https://schema.org/OutOfStock",
            itemCondition: "https://schema.org/NewCondition",
            seller: { "@id": absoluteUrl("/#store") },
            shippingDetails: {
              "@type": "OfferShippingDetails",
              shippingDestination: { "@type": "DefinedRegion", addressCountry: "EG" },
              deliveryTime: {
                "@type": "ShippingDeliveryTime",
                handlingTime: { "@type": "QuantitativeValue", minValue: 0, maxValue: 1, unitCode: "DAY" },
                transitTime: { "@type": "QuantitativeValue", minValue: 0, maxValue: 1, unitCode: "DAY" },
              },
            },
          },
        }}
      />
    </div>
  );
}
