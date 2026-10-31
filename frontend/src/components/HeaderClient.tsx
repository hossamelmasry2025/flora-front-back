"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useEffect, useState } from "react";
import { Globe, Menu, Search, ShoppingBag, X } from "lucide-react";
import { localePath, stripLocale } from "@/i18n/config";
import { useAlternates, useI18n } from "@/i18n/client";
import { cartCount, useCart } from "@/lib/cart";
import { useHydrated } from "@/lib/useHydrated";

export function CartButton() {
  const { t } = useI18n();
  const items = useCart((s) => s.items);
  const open = useCart((s) => s.open);
  const hydrated = useHydrated();
  const count = hydrated ? cartCount(items) : 0;

  return (
    <button
      type="button"
      onClick={open}
      className="relative rounded-full p-2.5 text-ink transition hover:bg-white"
      aria-label={t.cart.label(count)}
    >
      <ShoppingBag className="h-5 w-5 sm:h-[22px] sm:w-[22px]" strokeWidth={1.6} />
      {count > 0 && (
        <span className="absolute -end-0.5 -top-0.5 flex h-5 min-w-5 items-center justify-center rounded-full bg-gold px-1 text-[11px] font-bold text-white ring-2 ring-cream">
          {count}
        </span>
      )}
    </button>
  );
}

/** رابط في القائمة بيتلوّن لما تكون على الصفحة بتاعته */
export function NavLink({ href, children, highlight = false }: { href: string; children: React.ReactNode; highlight?: boolean }) {
  const pathname = decodeURI(usePathname());
  const target = decodeURI(href);
  const isHome = target === "/" || target === "/en";
  const active = pathname === target || (!isHome && pathname.startsWith(target));

  return (
    <Link
      href={href}
      aria-current={active ? "page" : undefined}
      className={`link-underline whitespace-nowrap py-1.5 text-[15px] font-medium transition-colors duration-300 ${
        highlight ? "text-brand-700" : active ? "text-ink" : "text-ink/70 hover:text-ink"
      }`}
    >
      {highlight && <span className="me-1.5 text-gold" aria-hidden="true">✦</span>}
      {children}
    </Link>
  );
}

/** زرار تغيير اللغة — بيروح لنفس الصفحة باللغة التانية */
export function LanguageSwitcher({ className = "" }: { className?: string }) {
  const { locale, t } = useI18n();
  const pathname = usePathname();
  const alternates = useAlternates();
  const target = locale === "ar" ? "en" : "ar";
  const href = alternates[target] ?? localePath(target, stripLocale(pathname));

  return (
    <Link
      href={href}
      hrefLang={target}
      lang={target}
      aria-label={t.nav.switchToLabel}
      className={`flex items-center gap-1.5 whitespace-nowrap rounded-full px-3 py-2.5 text-sm font-semibold text-ink transition hover:bg-white ${className}`}
    >
      <Globe className="h-[18px] w-[18px]" strokeWidth={1.6} />
      <span className="hidden sm:inline">{t.nav.switchTo}</span>
    </Link>
  );
}

export function MobileMenu({ nav, siteName }: { nav: { href: string; label: string }[]; siteName: string }) {
  const { t, lp } = useI18n();
  const [open, setOpen] = useState(false);
  const pathname = decodeURI(usePathname());

  useEffect(() => {
    document.body.style.overflow = open ? "hidden" : "";
    return () => {
      document.body.style.overflow = "";
    };
  }, [open]);

  return (
    <>
      <button type="button" onClick={() => setOpen(true)} className="-ms-2 rounded-full p-2 xl:hidden" aria-label={t.nav.openMenu}>
        <Menu className="h-6 w-6" />
      </button>
      {open && (
        <div className="fixed inset-0 z-50 xl:hidden" role="dialog" aria-modal="true" aria-label={t.nav.menu}>
          <div className="absolute inset-0 animate-fade-in bg-ink/50 backdrop-blur-sm" onClick={() => setOpen(false)} />
          <div className="animate-drawer-start absolute inset-y-0 start-0 flex w-[85%] max-w-sm flex-col bg-cream shadow-2xl">
            <div className="flex items-center justify-between border-b border-line p-5">
              <span className="font-display text-2xl font-bold text-ink">{siteName}</span>
              <button type="button" onClick={() => setOpen(false)} className="rounded-full p-2 hover:bg-brand-50" aria-label={t.nav.close}>
                <X className="h-5 w-5" />
              </button>
            </div>
            <form action={lp("/products")} role="search" className="relative p-4" onSubmit={() => setOpen(false)}>
              <input name="q" type="search" placeholder={t.search.placeholder} className="input pe-11" aria-label={t.search.label} />
              <Search className="pointer-events-none absolute end-8 top-1/2 h-4 w-4 -translate-y-1/2 text-muted" />
            </form>
            <nav className="flex-1 overflow-y-auto px-2" aria-label={t.nav.menu}>
              {nav.map((item) => (
                <Link
                  key={item.href}
                  href={item.href}
                  onClick={() => setOpen(false)}
                  className={`flex items-center justify-between border-b border-line/70 px-3 py-4 font-display text-xl transition hover:text-brand-700 ${
                    pathname === decodeURI(item.href) ? "text-brand-700" : "text-ink"
                  }`}
                >
                  {item.label}
                  <span className="text-xs text-gold" aria-hidden="true">✦</span>
                </Link>
              ))}
            </nav>
            <div className="border-t border-line p-4" onClick={() => setOpen(false)}>
              <LanguageSwitcher className="w-full justify-center border border-line bg-white [&>span]:inline" />
            </div>
          </div>
        </div>
      )}
    </>
  );
}
