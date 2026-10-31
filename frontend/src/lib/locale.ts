import "server-only";
import { notFound } from "next/navigation";
import { isLocale, localePath, type Locale } from "@/i18n/config";
import { getDictionary } from "@/i18n/dictionaries";

/** بيقرا اللغة من الرابط ويرجع اللغة + القاموس + دالة الروابط */
export async function resolveLocale(params: Promise<{ lang: string }>) {
  const { lang } = await params;
  if (!isLocale(lang)) notFound();
  const locale: Locale = lang;
  return { locale, t: getDictionary(locale), lp: (path: string) => localePath(locale, path) };
}
