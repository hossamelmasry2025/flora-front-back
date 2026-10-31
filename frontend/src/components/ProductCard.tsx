import Image from "next/image";
import Link from "next/link";
import { localePath, type Locale } from "@/i18n/config";
import { getDictionary } from "@/i18n/dictionaries";
import type { ProductCard as Product } from "@/lib/types";
import { discountPercent, formatPrice, productUrl } from "@/lib/utils";
import { AddToCartButton } from "./AddToCartButton";
import { FlowerMark } from "./Logo";

const SIZES = "(min-width: 1280px) 300px, (min-width: 768px) 33vw, 50vw";

export function ProductCard({ product, currency, locale, priority = false }: { product: Product; currency: string; locale: Locale; priority?: boolean }) {
  const t = getDictionary(locale);
  const href = localePath(locale, productUrl(product.slug));
  const discount = discountPercent(product.price, product.sale_price);

  return (
    <article className="group relative flex h-full flex-col">
      <div className="relative aspect-[4/5] overflow-hidden rounded-3xl bg-sand shadow-soft ring-1 ring-line/50 transition duration-500 group-hover:shadow-luxe">
        <Link href={href} className="absolute inset-0" tabIndex={-1} aria-hidden="true">
          {product.image ? (
            <>
              <Image
                src={product.image}
                alt=""
                fill
                sizes={SIZES}
                className={`object-cover transition duration-[1200ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-[1.06] ${product.hover_image ? "group-hover:opacity-0" : ""}`}
                loading={priority ? "eager" : "lazy"}
                fetchPriority={priority ? "high" : "auto"}
              />
              {/* الصورة التانية بتظهر مع الماوس */}
              {product.hover_image && (
                <Image
                  src={product.hover_image}
                  alt=""
                  fill
                  sizes={SIZES}
                  className="scale-[1.06] object-cover opacity-0 transition duration-[1200ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-100 group-hover:opacity-100"
                  loading="lazy"
                />
              )}
            </>
          ) : (
            <span className="flex h-full items-center justify-center">
              <FlowerMark className="h-16 w-16 text-brand-200" />
            </span>
          )}
        </Link>

        <div className="pointer-events-none absolute start-3 top-3 flex flex-col gap-1.5">
          {discount > 0 && (
            <span className="rounded-full bg-brand-900 px-2.5 py-1 text-[11px] font-bold tracking-wide text-white shadow-soft">{t.product.discount(discount)}</span>
          )}
          {!product.in_stock && <span className="rounded-full bg-white/90 px-2.5 py-1 text-[11px] font-bold text-ink backdrop-blur">{t.product.outOfStock}</span>}
        </div>

        {product.in_stock && (
          <>
            {/* الكمبيوتر: شريط "أضف للسلة" بيطلع من تحت */}
            <div className="absolute inset-x-3 bottom-3 hidden translate-y-4 opacity-0 transition duration-500 ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:translate-y-0 group-focus-within:opacity-100 lg:block">
              <AddToCartButton product={product} variant="bar" />
            </div>
            {/* الموبايل: زرار صغير ثابت */}
            <AddToCartButton product={product} className="absolute bottom-3 end-3 lg:hidden" />
          </>
        )}
      </div>

      <div className="flex flex-1 flex-col px-1 pt-4">
        <h3 className="line-clamp-2 font-display text-base font-bold leading-snug sm:text-lg">
          <Link href={href} className="transition-colors hover:text-brand-700">{product.name}</Link>
        </h3>
        <p className="mt-1.5 flex flex-wrap items-baseline gap-x-2 leading-tight">
          <span className={`text-[15px] font-semibold tabular-nums sm:text-base ${product.sale_price ? "text-brand-700" : "text-ink/90"}`}>
            {formatPrice(product.final_price, currency)}
          </span>
          {product.sale_price && <del className="text-xs text-muted tabular-nums">{formatPrice(product.price, currency)}</del>}
        </p>
      </div>
    </article>
  );
}

export function ProductGrid({ products, currency, locale, priorityCount = 0 }: { products: Product[]; currency: string; locale: Locale; priorityCount?: number }) {
  return (
    <ul className="grid grid-cols-2 gap-x-3 gap-y-8 sm:gap-x-6 sm:gap-y-12 md:grid-cols-3 xl:grid-cols-4">
      {products.map((p, i) => (
        <li key={p.id}>
          <ProductCard product={p} currency={currency} locale={locale} priority={i < priorityCount} />
        </li>
      ))}
    </ul>
  );
}
