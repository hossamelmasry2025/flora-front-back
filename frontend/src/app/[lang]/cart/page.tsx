import type { Metadata } from "next";
import { CartView } from "@/components/CartView";
import { PageHeader } from "@/components/PageHeader";
import { Sections } from "@/components/sections/Sections";
import { api } from "@/lib/api";
import { resolveLocale } from "@/lib/locale";
import { getSettings } from "@/lib/settings";

type Props = { params: Promise<{ lang: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { t } = await resolveLocale(params);
  return { title: t.cart.title, robots: { index: false, follow: false } };
}

export default async function CartPage({ params }: Props) {
  const { locale, t } = await resolveLocale(params);
  const [s, sections] = await Promise.all([getSettings(locale), api.sections("cart_page", locale)]);
  return (
    <>
      <div className="container-page py-10 sm:py-16">
        <PageHeader title={t.cart.title} />
        <CartView currency={s.currency} />
      </div>
      {sections.length > 0 && <Sections sections={sections} currency={s.currency} locale={locale} firstIsPageHeading={false} />}
    </>
  );
}
