import Link from "next/link";
import { Search } from "lucide-react";
import { localePath, type Locale } from "@/i18n/config";
import { getDictionary } from "@/i18n/dictionaries";
import type { Settings } from "@/lib/types";
import { categoryUrl } from "@/lib/utils";
import { Logo } from "./Logo";
import { CartButton, LanguageSwitcher, MobileMenu, NavLink } from "./HeaderClient";

export function Header({ settings, locale }: { settings: Settings; locale: Locale }) {
  const t = getDictionary(locale);
  const lp = (p: string) => localePath(locale, p);

  const h = settings.header ?? { menu: [], show_search: true, show_track: true, show_language: true, sticky: true };

  // القائمة من الداش بورد (شكل الموقع ← القائمة)، ولو فاضية بتتعمل من التصنيفات
  const nav: { href: string; label: string; highlight?: boolean }[] = h.menu.length
    ? h.menu.map((m) => ({ href: lp(m.link), label: m.label, highlight: m.highlight }))
    : [
        { href: lp("/"), label: t.nav.home },
        ...settings.menu_categories.slice(0, 3).map((c) => ({ href: lp(categoryUrl(c.slug)), label: c.name })),
        { href: lp("/products"), label: t.nav.allBouquets },
      ];
  const trackHref = lp("/track");

  return (
    <>
      {settings.announcement && (
        <div className="relative bg-brand-950 px-4 py-2.5 text-center text-[11px] font-medium tracking-wide text-white/85 sm:text-xs">
          <span className="text-gold-light" aria-hidden="true">✦ </span>
          {settings.announcement}
          <span className="text-gold-light" aria-hidden="true"> ✦</span>
          <span className="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent" aria-hidden="true" />
        </div>
      )}
      <header className={`${h.sticky ? "sticky top-0" : "relative"} z-40 border-b border-line/70 bg-cream/85 backdrop-blur-xl backdrop-saturate-150`}>
        <div className="container-page flex h-16 items-center gap-3 sm:h-[5.25rem]">
          <MobileMenu nav={[...nav, { href: trackHref, label: t.nav.trackOrder }, { href: lp("/pages/about-us"), label: t.nav.about }]} siteName={settings.site_name} />
          <Logo name={settings.site_name} logo={settings.logo} href={lp("/")} homeLabel={t.nav.home} />

          <nav aria-label={t.nav.mainMenu} className="ms-10 hidden xl:block">
            <ul className="flex items-center gap-7">
              {nav.map((item) => (
                <li key={item.href}>
                  <NavLink href={item.href} highlight={item.highlight}>{item.label}</NavLink>
                </li>
              ))}
            </ul>
          </nav>

          <div className="ms-auto flex items-center gap-1 sm:gap-2">
            {h.show_search && (
            <form action={lp("/products")} role="search" className="relative hidden md:block">
              <label htmlFor="header-search" className="sr-only">{t.search.label}</label>
              <input
                id="header-search"
                name="q"
                type="search"
                placeholder={t.search.placeholder}
                className="w-44 rounded-full border border-line bg-white/60 py-2.5 pe-10 ps-4 text-sm transition-all duration-500 placeholder:text-muted/70 focus:w-64 focus:border-gold/60 focus:bg-white focus:outline-none focus:ring-4 focus:ring-gold/10"
              />
              <button type="submit" aria-label={t.search.button} className="absolute end-1.5 top-1/2 -translate-y-1/2 rounded-full p-1.5 text-muted hover:text-ink">
                <Search className="h-4 w-4" />
              </button>
            </form>
            )}

            {/* تاب تتبع الطلب */}
            {h.show_track && (
            <>
            <Link
              href={trackHref}
              className="hidden items-center gap-2 whitespace-nowrap rounded-full border border-gold/40 px-4 py-2.5 text-sm font-semibold text-ink transition duration-300 hover:border-gold hover:bg-white sm:flex"
            >
              <TruckIcon /> {t.nav.trackOrder}
            </Link>
            <Link href={trackHref} aria-label={t.nav.trackOrder} className="rounded-full p-2.5 text-ink hover:bg-white sm:hidden">
              <TruckIcon />
            </Link>
            </>
            )}

            {h.show_language && <LanguageSwitcher />}
            {h.show_search && (
              <Link href={lp("/products")} aria-label={t.search.button} className="rounded-full p-2.5 text-ink hover:bg-white md:hidden">
                <Search className="h-5 w-5" />
              </Link>
            )}
            <CartButton />
          </div>
        </div>
      </header>
    </>
  );
}

function TruckIcon() {
  return (
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" className="h-[18px] w-[18px] rtl:-scale-x-100" aria-hidden="true">
      <path d="M14 18V6a2 2 0 0 0-2-2H4a2 2 0 0 0-2 2v11a1 1 0 0 0 1 1h2" />
      <path d="M15 18H9" />
      <path d="M19 18h2a1 1 0 0 0 1-1v-3.65a1 1 0 0 0-.22-.624l-3.48-4.35A1 1 0 0 0 17.52 8H14" />
      <circle cx="17" cy="18" r="2" />
      <circle cx="7" cy="18" r="2" />
    </svg>
  );
}
