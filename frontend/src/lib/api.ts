import "server-only";
import type { Locale } from "@/i18n/config";
import type { Category, CategoryCard, HomeSection, Page, Paginated, Product, ProductCard, Settings, Slugs } from "./types";

export const API_URL = (process.env.NEXT_PUBLIC_API_URL ?? "http://localhost:8000").replace(/\/$/, "");

/** كل داتا الـ CMS بتتخزن بالتاج ده، والداش بورد بتمسحه أول ما تعدّل أي حاجة */
export const CMS_TAG = "cms";

class NotFoundError extends Error {}

async function get<T>(path: string, locale: Locale, revalidate = 300): Promise<T> {
  const sep = path.includes("?") ? "&" : "?";
  const res = await fetch(`${API_URL}/api/${path}${sep}lang=${locale}`, {
    headers: { Accept: "application/json" },
    next: { revalidate, tags: [CMS_TAG] },
  });

  if (res.status === 404) throw new NotFoundError(path);
  if (!res.ok) throw new Error(`API ${res.status}: ${path}`);

  return res.json() as Promise<T>;
}

/** يرجع null لو العنصر مش موجود (عشان نعرض صفحة 404) */
async function getOrNull<T>(path: string, locale: Locale): Promise<T | null> {
  try {
    return await get<T>(path, locale);
  } catch (e) {
    if (e instanceof NotFoundError) return null;
    throw e;
  }
}

const enc = encodeURIComponent;

export const api = {
  settings: (l: Locale) => get<{ data: Settings }>("settings", l).then((r) => r.data),
  home: (l: Locale) => get<{ data: HomeSection[] }>("home", l).then((r) => r.data),
  categories: (l: Locale) => get<{ data: (CategoryCard & { children: CategoryCard[] })[] }>("categories", l).then((r) => r.data),
  category: (slug: string, l: Locale) => getOrNull<{ data: Category }>(`categories/${enc(slug)}`, l).then((r) => r?.data ?? null),
  product: (slug: string, l: Locale) => getOrNull<{ data: Product; related: ProductCard[]; sections: HomeSection[] }>(`products/${enc(slug)}`, l),
  /** سكاشن صفحات المتجر الثابتة (لو حصلت مشكلة الصفحة بتفتح من غيرها) */
  sections: (key: "products_page" | "cart_page" | "all_pages_top", l: Locale) =>
    get<{ data: HomeSection[] }>(`sections/${key}`, l).then((r) => r.data).catch(() => [] as HomeSection[]),
  page: (slug: string, l: Locale) => getOrNull<{ data: Page }>(`pages/${enc(slug)}`, l).then((r) => r?.data ?? null),
  deliveryZones: (l: Locale) =>
    get<{ data: { id: number; name: string; fee: number }[] }>("delivery-zones", l, 60).then((r) => r.data).catch(() => []),
  sitemap: () =>
    get<{
      data: {
        categories: { slugs: Slugs; updated_at: string }[];
        products: { slugs: Slugs; updated_at: string; image: string | null }[];
        pages: { slugs: Slugs; updated_at: string }[];
      };
    }>("sitemap", "ar", 3600).then((r) => r.data),

  products: (params: ProductQuery, l: Locale) => {
    const qs = new URLSearchParams();
    for (const [k, v] of Object.entries(params)) {
      if (v !== undefined && v !== null && v !== "") qs.set(k, String(v));
    }
    return get<Paginated<ProductCard>>(`products?${qs}`, l, 120);
  },
};

export type ProductQuery = {
  q?: string;
  category?: string;
  min?: number;
  max?: number;
  sort?: string;
  sale?: 1;
  featured?: 1;
  page?: number;
  per_page?: number;
};
