import Link from "next/link";
import { ChevronLeft } from "lucide-react";
import { localePath, type Locale } from "@/i18n/config";
import { getDictionary } from "@/i18n/dictionaries";
import { absoluteUrl } from "@/lib/utils";
import { JsonLd } from "./JsonLd";

/** href بدون بادئة اللغة */
export type Crumb = { name: string; href?: string };

export function Breadcrumbs({ items, locale }: { items: Crumb[]; locale: Locale }) {
  const t = getDictionary(locale);
  const all: Crumb[] = [{ name: t.nav.home, href: "/" }, ...items];
  const lp = (p: string) => localePath(locale, p);

  return (
    <>
      <nav aria-label={t.common.breadcrumb} className="mb-6 text-sm">
        <ol className="flex flex-wrap items-center gap-1.5 text-muted">
          {all.map((c, i) => (
            <li key={i} className="flex items-center gap-1.5">
              {i > 0 && <ChevronLeft className="h-3.5 w-3.5 opacity-60 ltr:rotate-180" aria-hidden="true" />}
              {c.href && i < all.length - 1 ? (
                <Link href={lp(c.href)} className="hover:text-brand-700">{c.name}</Link>
              ) : (
                <span aria-current={i === all.length - 1 ? "page" : undefined} className="font-medium text-ink">{c.name}</span>
              )}
            </li>
          ))}
        </ol>
      </nav>
      <JsonLd
        data={{
          "@context": "https://schema.org",
          "@type": "BreadcrumbList",
          itemListElement: all.map((c, i) => ({
            "@type": "ListItem",
            position: i + 1,
            name: c.name,
            ...(c.href ? { item: absoluteUrl(lp(c.href)) } : {}),
          })),
        }}
      />
    </>
  );
}
