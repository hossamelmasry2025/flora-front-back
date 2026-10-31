import type { Metadata } from "next";
import { CheckoutForm } from "@/components/checkout/CheckoutForm";
import { PageHeader } from "@/components/PageHeader";
import { api } from "@/lib/api";
import { resolveLocale } from "@/lib/locale";
import { getSettings } from "@/lib/settings";

type Props = { params: Promise<{ lang: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { t } = await resolveLocale(params);
  return { title: t.checkout.title, robots: { index: false, follow: false } };
}

export default async function CheckoutPage({ params }: Props) {
  const { locale, t } = await resolveLocale(params);
  const [settings, zones] = await Promise.all([getSettings(locale), api.deliveryZones(locale)]);

  return (
    <div className="container-page py-10 sm:py-16">
      <PageHeader title={t.checkout.title} subtitle={t.checkout.subtitle} />
      <CheckoutForm settings={settings} zones={zones} />
    </div>
  );
}
