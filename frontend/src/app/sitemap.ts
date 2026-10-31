import type { MetadataRoute } from "next";
import { localePath } from "@/i18n/config";
import { api } from "@/lib/api";
import { absoluteUrl, categoryUrl, pageUrl, productUrl } from "@/lib/utils";

export const revalidate = 3600;

type Entry = MetadataRoute.Sitemap[number];

/** نفس الصفحة بالعربي والإنجليزي، وكل واحدة بتشاور على التانية (hreflang) */
function pair(arPath: string, enPath: string, extra: Omit<Entry, "url" | "alternates">): Entry[] {
  const languages = { ar: absoluteUrl(localePath("ar", arPath)), en: absoluteUrl(localePath("en", enPath)) };
  return [
    { url: languages.ar, alternates: { languages }, ...extra },
    { url: languages.en, alternates: { languages }, ...extra },
  ];
}

/** sitemap.xml — بيتحدّث لوحده بكل المنتجات والتصنيفات والصفحات باللغتين */
export default async function sitemap(): Promise<MetadataRoute.Sitemap> {
  const now = new Date();
  const statics = [
    ...pair("/", "/", { lastModified: now, changeFrequency: "daily", priority: 1 }),
    ...pair("/products", "/products", { lastModified: now, changeFrequency: "daily", priority: 0.9 }),
    ...pair("/categories", "/categories", { lastModified: now, changeFrequency: "weekly", priority: 0.8 }),
    ...pair("/track", "/track", { lastModified: now, changeFrequency: "yearly", priority: 0.3 }),
  ];

  try {
    const data = await api.sitemap();
    return [
      ...statics,
      ...data.categories.flatMap((c) =>
        pair(categoryUrl(c.slugs.ar), categoryUrl(c.slugs.en), { lastModified: new Date(c.updated_at), changeFrequency: "weekly", priority: 0.8 }),
      ),
      ...data.products.flatMap((p) =>
        pair(productUrl(p.slugs.ar), productUrl(p.slugs.en), {
          lastModified: new Date(p.updated_at),
          changeFrequency: "weekly",
          priority: 0.7,
          images: p.image ? [p.image] : undefined,
        }),
      ),
      ...data.pages.flatMap((p) => pair(pageUrl(p.slugs.ar), pageUrl(p.slugs.en), { lastModified: new Date(p.updated_at), changeFrequency: "monthly", priority: 0.4 })),
    ];
  } catch {
    return statics;
  }
}
