import type { Metadata } from "next";
import { Sections } from "@/components/sections/Sections";
import { api } from "@/lib/api";
import { resolveLocale } from "@/lib/locale";
import { getSettings } from "@/lib/settings";
import { seoAlternates } from "@/lib/utils";

type Props = { params: Promise<{ lang: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale } = await resolveLocale(params);
  return { alternates: seoAlternates(locale, "/") };
}

export default async function HomePage({ params }: Props) {
  const { locale } = await resolveLocale(params);
  const [sections, settings] = await Promise.all([api.home(locale), getSettings(locale)]);
  const heroFirst = sections[0]?.type === "slider";

  return (
    <>
      {/* لو السلايدر مش أول حاجة، لازم يبقى فيه عنوان رئيسي للصفحة عشان جوجل */}
      {!heroFirst && <h1 className="sr-only">{settings.meta_title || settings.site_name}</h1>}
      <Sections sections={sections} currency={settings.currency} locale={locale} />
    </>
  );
}
