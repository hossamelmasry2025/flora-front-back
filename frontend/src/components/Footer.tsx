import Link from "next/link";
import { Clock, Mail, MapPin, Phone } from "lucide-react";
import { localePath, type Locale } from "@/i18n/config";
import { getDictionary } from "@/i18n/dictionaries";
import type { Settings } from "@/lib/types";
import { categoryUrl, pageUrl, whatsappLink } from "@/lib/utils";
import { Logo } from "./Logo";
import { SocialIcon } from "./SocialIcon";

export function Footer({ settings, locale }: { settings: Settings; locale: Locale }) {
  const t = getDictionary(locale);
  const lp = (p: string) => localePath(locale, p);
  const year = new Date().getFullYear();
  const f = settings.footer ?? { links: [], show_categories: true, show_pages: true };
  const link = "link-underline text-white/60 transition-colors duration-300 hover:text-gold-light";

  return (
    <footer className="relative overflow-hidden bg-brand-950 text-white/70">
      <span className="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-gold/70 to-transparent" aria-hidden="true" />
      <div className="pointer-events-none absolute -end-32 -top-32 h-96 w-96 rounded-full bg-brand-700/25 blur-3xl" aria-hidden="true" />
      <div className="pointer-events-none absolute -bottom-40 -start-20 h-96 w-96 rounded-full bg-gold/10 blur-3xl" aria-hidden="true" />
      {/* اسم المتجر كبير وباهت ورا الفوتر */}
      <p className="pointer-events-none absolute inset-x-0 -bottom-6 select-none text-center font-display text-[22vw] font-bold leading-none text-white/[0.03] sm:-bottom-12" aria-hidden="true">{settings.site_name}</p>
      <div className="container-page relative grid gap-12 py-16 sm:grid-cols-2 sm:py-20 lg:grid-cols-[1.4fr_1fr_1fr_1.2fr]">
        <div className="space-y-4">
          <Logo name={settings.site_name} logo={settings.logo} href={lp("/")} homeLabel={t.nav.home} tone="light" />
          {settings.footer_about && <p className="max-w-xs text-sm leading-7 text-white/60">{settings.footer_about}</p>}
          {settings.social.length > 0 && (
            <ul className="flex flex-wrap gap-2" aria-label={t.footer.follow}>
              {settings.social.map((s) => (
                <li key={s.url}>
                  <a
                    href={s.url}
                    target="_blank"
                    rel="noopener noreferrer"
                    aria-label={s.platform}
                    className="flex h-10 w-10 items-center justify-center rounded-full border border-white/15 text-white/80 transition duration-300 hover:border-gold hover:bg-gold hover:text-white"
                  >
                    <SocialIcon platform={s.platform} />
                  </a>
                </li>
              ))}
            </ul>
          )}
        </div>

        {f.show_categories && (
          <nav aria-label={t.footer.shopByOccasion}>
            <h2 className="mb-5 font-display text-lg font-bold text-white">{t.footer.shopByOccasion}</h2>
            <ul className="space-y-3 text-sm">
              {settings.menu_categories.map((c) => (
                <li key={c.slug}><Link href={lp(categoryUrl(c.slug))} className={link}>{c.name}</Link></li>
              ))}
              <li><Link href={lp("/products")} className={link}>{t.nav.allBouquets}</Link></li>
            </ul>
          </nav>
        )}

        {(f.show_pages || f.links.length > 0) && (
          <nav aria-label={t.footer.info}>
            <h2 className="mb-5 font-display text-lg font-bold text-white">{t.footer.info}</h2>
            <ul className="space-y-3 text-sm">
              {f.show_pages &&
                settings.footer_pages.map((p) => (
                  <li key={p.slug}><Link href={lp(pageUrl(p.slug))} className={link}>{p.title}</Link></li>
                ))}
              {/* روابط إضافية من الداش بورد (شكل الموقع ← الفوتر) */}
              {f.links.map((l) => (
                <li key={l.link + l.label}>
                  <Link href={lp(l.link)} className={link} {...(l.link.startsWith("/") ? {} : { target: "_blank", rel: "noopener noreferrer" })}>
                    {l.label}
                  </Link>
                </li>
              ))}
              <li><Link href={lp("/track")} className={link}>{t.nav.trackOrder}</Link></li>
            </ul>
          </nav>
        )}

        <div>
          <h2 className="mb-5 font-display text-lg font-bold text-white">{t.footer.contact}</h2>
          <address className="space-y-3.5 text-sm not-italic text-white/60">
            {settings.phone && (
              <a href={`tel:${settings.phone}`} className="flex items-center gap-3 transition-colors hover:text-gold-light">
                <Phone className="h-4 w-4 shrink-0 text-gold" strokeWidth={1.5} /> <span dir="ltr">{settings.phone}</span>
              </a>
            )}
            {settings.whatsapp && (
              <a href={whatsappLink(settings.whatsapp) ?? "#"} target="_blank" rel="noopener noreferrer" className="flex items-center gap-3 transition-colors hover:text-gold-light">
                <SocialIcon platform="whatsapp" className="h-4 w-4 text-gold" /> {t.footer.whatsapp}
              </a>
            )}
            {settings.email && (
              <a href={`mailto:${settings.email}`} className="flex items-center gap-3 transition-colors hover:text-gold-light">
                <Mail className="h-4 w-4 shrink-0 text-gold" strokeWidth={1.5} /> {settings.email}
              </a>
            )}
            {settings.address && (
              <p className="flex items-start gap-3"><MapPin className="mt-1 h-4 w-4 shrink-0 text-gold" strokeWidth={1.5} /> {settings.address}</p>
            )}
            {settings.working_hours && (
              <p className="flex items-start gap-3"><Clock className="mt-1 h-4 w-4 shrink-0 text-gold" strokeWidth={1.5} /> {settings.working_hours}</p>
            )}
          </address>
        </div>
      </div>
      <div className="relative border-t border-white/10">
        <div className="container-page flex flex-col items-center justify-between gap-2 py-6 text-xs tracking-wide text-white/45 sm:flex-row">
          <p>© {year} {settings.site_name}. {t.footer.rights}</p>
          <p>
            {settings.payment.cod_enabled && t.footer.cod}
            {settings.payment.cod_enabled && settings.payment.transfer_enabled && " • "}
            {settings.payment.transfer_enabled && t.footer.transfer}
          </p>
        </div>
      </div>
    </footer>
  );
}
