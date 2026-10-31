import type { Metadata, Viewport } from "next";
import { Amiri, Cormorant_Garamond, IBM_Plex_Sans_Arabic } from "next/font/google";
import Script from "next/script";
import { CartDrawer } from "@/components/CartDrawer";
import { Footer } from "@/components/Footer";
import { Header } from "@/components/Header";
import { JsonLd } from "@/components/JsonLd";
import { WhatsAppFloat } from "@/components/WhatsAppFloat";
import { Sections } from "@/components/sections/Sections";
import { api } from "@/lib/api";
import { dirOf, locales } from "@/i18n/config";
import { I18nProvider } from "@/i18n/client";
import { resolveLocale } from "@/lib/locale";
import { getSettings } from "@/lib/settings";
import { SITE_URL, absoluteUrl } from "@/lib/utils";
import "../globals.css";

const amiri = Amiri({ subsets: ["arabic", "latin"], weight: ["400", "700"], variable: "--font-amiri", display: "swap" });
const plex = IBM_Plex_Sans_Arabic({ subsets: ["arabic", "latin"], weight: ["400", "500", "600", "700"], variable: "--font-plex", display: "swap" });
const cormorant = Cormorant_Garamond({ subsets: ["latin"], weight: ["500", "600", "700"], style: ["normal", "italic"], variable: "--font-cormorant", display: "swap" });

type Props = { params: Promise<{ lang: string }> };

export function generateStaticParams() {
  return locales.map((lang) => ({ lang }));
}

export async function generateMetadata({ params }: Props): Promise<Metadata> {
  const { locale } = await resolveLocale(params);
  const s = await getSettings(locale);
  const title = s.meta_title || `${s.site_name}${s.tagline ? ` | ${s.tagline}` : ""}`;
  const description = s.meta_description || s.tagline || s.site_name;

  return {
    metadataBase: new URL(SITE_URL),
    title: { default: title, template: `%s | ${s.site_name}` },
    description,
    keywords: s.meta_keywords?.split(/[,،]/).map((k) => k.trim()).filter(Boolean),
    applicationName: s.site_name,
    openGraph: {
      type: "website",
      locale: locale === "ar" ? "ar_EG" : "en_US",
      alternateLocale: locale === "ar" ? "en_US" : "ar_EG",
      siteName: s.site_name,
      title,
      description,
      images: s.og_image ? [{ url: s.og_image, width: 1200, height: 630, alt: s.site_name }] : undefined,
    },
    twitter: { card: "summary_large_image", title, description, images: s.og_image ? [s.og_image] : undefined },
    icons: s.favicon ? { icon: s.favicon, apple: s.favicon } : undefined,
    verification: s.google_verification ? { google: s.google_verification } : undefined,
    robots: { index: true, follow: true, googleBot: { index: true, follow: true, "max-image-preview": "large", "max-snippet": -1 } },
    formatDetection: { telephone: false },
  };
}

export const viewport: Viewport = {
  themeColor: "#2a1d22",
  width: "device-width",
  initialScale: 1,
};

export default async function RootLayout({ children, params }: Readonly<{ children: React.ReactNode; params: Promise<{ lang: string }> }>) {
  const { locale, t, lp } = await resolveLocale(params);
  const [s, topSections] = await Promise.all([getSettings(locale), api.sections("all_pages_top", locale)]);

  // ألوان الموقع من الداش بورد (بنقبل hex بس عشان الأمان)
  const hex = (v: string | undefined, fallback: string) => (v && /^#[0-9a-f]{3,8}$/i.test(v) ? v : fallback);
  const themeStyle = {
    "--brand": hex(s.theme?.brand, "#b02f59"),
    "--accent": hex(s.theme?.accent, "#b8935a"),
    "--page-bg": hex(s.theme?.background, "#fbf7f3"),
  } as React.CSSProperties;

  return (
    <html lang={locale} dir={dirOf(locale)} data-corners={s.theme?.corners ?? "rounded"} style={themeStyle} className={`${amiri.variable} ${plex.variable} ${cormorant.variable}`}>
      <body className="flex min-h-dvh flex-col">
        <I18nProvider locale={locale}>
          <a href="#main" className="sr-only focus:not-sr-only focus:fixed focus:start-4 focus:top-4 focus:z-[60] focus:rounded-full focus:bg-white focus:px-4 focus:py-2 focus:shadow-lift">
            {t.nav.skip}
          </a>
          <Header settings={s} locale={locale} />
          {topSections.length > 0 && <Sections sections={topSections} currency={s.currency} locale={locale} firstIsPageHeading={false} />}
          <main id="main" className="flex-1">{children}</main>
          <Footer settings={s} locale={locale} />
          <CartDrawer currency={s.currency} />
          {s.whatsapp_button !== false && <WhatsAppFloat number={s.whatsapp} locale={locale} />}
        </I18nProvider>

        {/* بيانات المتجر لجوجل: نوعه "محل ورد" + البحث داخل الموقع */}
        <JsonLd
          data={[
            {
              "@context": "https://schema.org",
              "@type": "Florist",
              "@id": absoluteUrl("/#store"),
              name: s.site_name,
              url: absoluteUrl(lp("/")),
              logo: s.logo ?? undefined,
              image: s.og_image ?? s.logo ?? undefined,
              description: s.meta_description ?? undefined,
              telephone: s.phone ?? undefined,
              email: s.email ?? undefined,
              address: s.address ? { "@type": "PostalAddress", streetAddress: s.address, addressLocality: s.city ?? undefined, addressCountry: "EG" } : undefined,
              areaServed: s.city ?? undefined,
              priceRange: "$$",
              currenciesAccepted: "EGP",
              paymentAccepted: "Cash, Bank Transfer",
              sameAs: s.social.map((x) => x.url),
            },
            {
              "@context": "https://schema.org",
              "@type": "WebSite",
              name: s.site_name,
              url: absoluteUrl(lp("/")),
              inLanguage: locale,
              potentialAction: {
                "@type": "SearchAction",
                target: { "@type": "EntryPoint", urlTemplate: `${absoluteUrl(lp("/products"))}?q={search_term_string}` },
                "query-input": "required name=search_term_string",
              },
            },
          ]}
        />

        {s.google_analytics_id && (
          <>
            <Script src={`https://www.googletagmanager.com/gtag/js?id=${s.google_analytics_id}`} strategy="afterInteractive" />
            <Script id="ga" strategy="afterInteractive">
              {`window.dataLayer=window.dataLayer||[];function gtag(){dataLayer.push(arguments);}gtag('js',new Date());gtag('config',${JSON.stringify(s.google_analytics_id)});`}
            </Script>
          </>
        )}
        {s.facebook_pixel_id && (
          <Script id="fb-pixel" strategy="afterInteractive">
            {`!function(f,b,e,v,n,t,s){if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window,document,'script','https://connect.facebook.net/en_US/fbevents.js');fbq('init',${JSON.stringify(s.facebook_pixel_id)});fbq('track','PageView');`}
          </Script>
        )}
      </body>
    </html>
  );
}
