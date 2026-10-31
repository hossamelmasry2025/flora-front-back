import type { Metadata } from "next";
import { Truck } from "lucide-react";
import { PageHeader } from "@/components/PageHeader";
import { TrackOrder } from "@/components/TrackOrder";
import { resolveLocale } from "@/lib/locale";
import { getSettings } from "@/lib/settings";
import { seoAlternates } from "@/lib/utils";

type Props = { params: Promise<{ lang: string }>; searchParams: Promise<{ number?: string }> };

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale, t } = await resolveLocale(params);
  return { title: t.track.title, description: t.track.description, alternates: seoAlternates(locale, "/track") };
}

export default async function TrackPage({ params, searchParams }: Props) {
  const { locale, t } = await resolveLocale(params);
  const [{ number }, settings] = await Promise.all([searchParams, getSettings(locale)]);

  return (
    <div className="container-page py-10 sm:py-16">
      <div className="mx-auto max-w-4xl">
        <PageHeader center title={t.track.title} subtitle={t.track.intro} icon={<Truck className="h-7 w-7 rtl:-scale-x-100" strokeWidth={1.25} />} className="!mb-0" />
        <TrackOrder initialNumber={number ?? ""} currency={settings.currency} whatsapp={settings.whatsapp} />
      </div>
    </div>
  );
}
