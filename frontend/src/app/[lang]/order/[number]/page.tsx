import type { Metadata } from "next";
import { OrderConfirmation } from "@/components/OrderConfirmation";
import { resolveLocale } from "@/lib/locale";
import { getSettings } from "@/lib/settings";

type Props = { params: Promise<{ lang: string; number: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { t } = await resolveLocale(params);
  return { title: t.order.receivedTitle, robots: { index: false, follow: false } };
}

export default async function OrderPage({ params }: Props) {
  const { locale } = await resolveLocale(params);
  const [{ number }, settings] = await Promise.all([params, getSettings(locale)]);
  return (
    <div className="container-page py-10 sm:py-16">
      <OrderConfirmation number={number} currency={settings.currency} whatsapp={settings.whatsapp} siteName={settings.site_name} />
    </div>
  );
}
