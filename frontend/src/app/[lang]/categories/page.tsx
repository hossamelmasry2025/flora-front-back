import type { Metadata } from "next";
import Image from "next/image";
import Link from "next/link";
import { PageHeader } from "@/components/PageHeader";
import { Breadcrumbs } from "@/components/Breadcrumbs";
import { FlowerMark } from "@/components/Logo";
import { api } from "@/lib/api";
import { resolveLocale } from "@/lib/locale";
import { getSettings } from "@/lib/settings";
import { categoryUrl, seoAlternates } from "@/lib/utils";

type Props = { params: Promise<{ lang: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale, t } = await resolveLocale(params);
  const s = await getSettings(locale);
  return {
    title: t.category.pageTitle,
    description: t.category.pageDescription(s.site_name),
    alternates: seoAlternates(locale, "/categories"),
  };
}

export default async function CategoriesPage({ params }: Props) {
  const { locale, t, lp } = await resolveLocale(params);
  const categories = await api.categories(locale);

  return (
    <div className="container-page py-10 sm:py-16">
      <Breadcrumbs items={[{ name: t.nav.categories }]} locale={locale} />
      <PageHeader title={t.category.heading} />
      <ul className="grid grid-cols-2 gap-4 sm:gap-6 md:grid-cols-3">
        {categories.map((c, i) => (
          <li key={c.id}>
            <Link href={lp(categoryUrl(c.slug))} className="group block overflow-hidden rounded-3xl bg-white shadow-soft ring-1 ring-line/50 transition duration-500 hover:-translate-y-1 hover:shadow-luxe">
              <div className="relative aspect-[4/3] overflow-hidden bg-sand">
                {c.image ? (
                  <Image src={c.image} alt={c.name} fill sizes="(min-width: 768px) 33vw, 50vw" className="object-cover transition duration-[1400ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-110" loading={i < 3 ? "eager" : "lazy"} />
                ) : (
                  <FlowerMark className="absolute inset-0 m-auto h-16 w-16 text-brand-200" />
                )}
              </div>
              <div className="p-5 sm:p-6">
                <h2 className="text-xl font-bold transition-colors group-hover:text-brand-700 sm:text-2xl">{c.name}</h2>
                {c.description && <p className="mt-1 line-clamp-2 text-sm text-muted">{c.description}</p>}
                {c.children.length > 0 && <p className="mt-2 text-xs text-gold-dark">{c.children.map((x) => x.name).join(" • ")}</p>}
              </div>
            </Link>
          </li>
        ))}
      </ul>
    </div>
  );
}
