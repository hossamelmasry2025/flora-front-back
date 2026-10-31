export const locales = ["ar", "en"] as const;
export type Locale = (typeof locales)[number];
export const defaultLocale: Locale = "ar";

export const isLocale = (v: string): v is Locale => (locales as readonly string[]).includes(v);
export const dirOf = (l: Locale) => (l === "ar" ? "rtl" : "ltr");

/**
 * رابط باللغة المطلوبة: العربي من غير بادئة (/products) والإنجليزي تحت /en (/en/products).
 * الروابط الخارجية بترجع زي ما هي.
 */
export function localePath(locale: Locale, path = "/") {
  if (!path.startsWith("/") || path.startsWith("//")) return path;
  const clean = stripLocale(path);
  if (locale === defaultLocale) return clean;
  return clean === "/" ? "/en" : `/en${clean}`;
}

/** يشيل بادئة /en من الرابط */
export function stripLocale(path: string) {
  if (path === "/en") return "/";
  if (path.startsWith("/en/") || path.startsWith("/en?")) return path.slice(3) || "/";
  return path;
}
