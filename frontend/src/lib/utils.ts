import { localePath, type Locale } from "@/i18n/config";

export const SITE_URL = (process.env.NEXT_PUBLIC_SITE_URL ?? "http://localhost:3000").replace(/\/$/, "");
export const API_PUBLIC_URL = (process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000").replace(/\/$/, "");

export function formatPrice(value: number, currency = "ج.م") {
  return `${new Intl.NumberFormat("en-US", { maximumFractionDigits: 2 }).format(value)} ${currency}`;
}

/** Next بيدّي الـ params العربي أحياناً encoded — بنفكه بأمان */
export function decodeSlug(slug: string) {
  try {
    return decodeURIComponent(slug);
  } catch {
    return slug;
  }
}

/** روابط بدون لغة — استخدمها مع lp() أو localePath() */
export const productUrl = (slug: string) => `/products/${encodeURIComponent(slug)}`;
export const categoryUrl = (slug: string) => `/categories/${encodeURIComponent(slug)}`;
export const pageUrl = (slug: string) => `/pages/${encodeURIComponent(slug)}`;
export const absoluteUrl = (path = "/") => `${SITE_URL}${path.startsWith("/") ? path : `/${path}`}`;

/**
 * canonical + hreflang لكل صفحة (عربي / إنجليزي / x-default)
 * arPath و enPath روابط من غير بادئة لغة
 */
export function seoAlternates(locale: Locale, arPath: string, enPath: string = arPath) {
  const ar = localePath("ar", arPath);
  const en = localePath("en", enPath);
  return {
    canonical: locale === "ar" ? ar : en,
    languages: { ar, en, "x-default": ar },
  };
}

/** نص مختصر من HTML للـ meta description */
export function excerpt(html: string | null | undefined, length = 160) {
  if (!html) return "";
  const text = html.replace(/<[^>]+>/g, " ").replace(/&nbsp;/g, " ").replace(/\s+/g, " ").trim();
  return text.length > length ? `${text.slice(0, length - 1).trimEnd()}…` : text;
}

export function whatsappLink(number: string | null | undefined, text?: string) {
  if (!number) return null;
  let digits = number.replace(/\D/g, "");
  if (digits.startsWith("0")) digits = `2${digits}`;
  return `https://wa.me/${digits}${text ? `?text=${encodeURIComponent(text)}` : ""}`;
}

export function discountPercent(price: number, sale: number | null) {
  if (!sale || sale >= price) return 0;
  return Math.round(((price - sale) / price) * 100);
}

export function cn(...classes: (string | false | null | undefined)[]) {
  return classes.filter(Boolean).join(" ");
}
