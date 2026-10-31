import { localePath, type Locale } from "@/i18n/config";
import { getDictionary } from "@/i18n/dictionaries";
import type { Paginated, ProductCard } from "@/lib/types";
import { EmptyState } from "./EmptyState";
import { Pagination } from "./Pagination";
import { ProductGrid } from "./ProductCard";
import { SortSelect } from "./SortSelect";

/** شبكة منتجات + ترتيب + باجنيشن (مشتركة بين صفحة المنتجات وصفحة التصنيف) */
export function ProductListing({
  result,
  currency,
  basePath,
  params,
  locale,
}: {
  result: Paginated<ProductCard>;
  currency: string;
  /** الرابط باللغة (مع البادئة) */
  basePath: string;
  params: Record<string, string | undefined>;
  locale: Locale;
}) {
  const t = getDictionary(locale);
  const { data, meta } = result;

  return (
    <>
      <div className="mb-8 flex flex-wrap items-center justify-between gap-3 border-b border-line pb-5">
        <p className="text-sm text-muted">
          {meta.total > 0 ? t.common.productsCount(meta.total) : ""}
          {meta.last_page > 1 && t.common.pageOf(meta.current_page, meta.last_page)}
        </p>
        <SortSelect value={params.sort ?? "default"} />
      </div>

      {data.length > 0 ? (
        <ProductGrid products={data} currency={currency} locale={locale} priorityCount={4} />
      ) : (
        <EmptyState text={t.listing.empty} hint={t.listing.emptyHint} action={{ href: localePath(locale, "/products"), label: t.nav.allBouquets }} />
      )}

      <Pagination current={meta.current_page} last={meta.last_page} basePath={basePath} params={params} locale={locale} />
    </>
  );
}
