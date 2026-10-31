import { SORT_OPTIONS } from "./sort";

export type RawSearchParams = Record<string, string | string[] | undefined>;

const one = (v: string | string[] | undefined) => (Array.isArray(v) ? v[0] : v)?.trim() || undefined;

/** قراءة آمنة لباراميترات البحث من الرابط */
export function parseListingParams(sp: RawSearchParams) {
  const page = Math.max(1, Number.parseInt(one(sp.page) ?? "1", 10) || 1);
  const sort = one(sp.sort);
  const min = Number(one(sp.min));
  const max = Number(one(sp.max));

  return {
    q: one(sp.q)?.slice(0, 100),
    page,
    sort: sort && sort in SORT_OPTIONS && sort !== "default" ? sort : undefined,
    min: Number.isFinite(min) && min > 0 ? min : undefined,
    max: Number.isFinite(max) && max > 0 ? max : undefined,
    sale: one(sp.sale) === "1" ? (1 as const) : undefined,
  };
}

/** الصفحات المفلترة/المترتبة نسخ من نفس المحتوى، فبنقول لجوجل ما يأرشفهاش */
export function isFilteredListing(p: ReturnType<typeof parseListingParams>) {
  return Boolean(p.q || p.sort || p.min || p.max || p.sale);
}
