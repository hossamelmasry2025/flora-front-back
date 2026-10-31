"use client";

import { createContext, useContext } from "react";
import { create } from "zustand";
import { localePath, type Locale } from "./config";
import { getDictionary } from "./dictionaries";

const LocaleContext = createContext<Locale>("ar");

export function I18nProvider({ locale, children }: { locale: Locale; children: React.ReactNode }) {
  return <LocaleContext.Provider value={locale}>{children}</LocaleContext.Provider>;
}

export function useI18n() {
  const locale = useContext(LocaleContext);
  return { locale, t: getDictionary(locale), lp: (path: string) => localePath(locale, path) };
}

/**
 * روابط الصفحة الحالية باللغتين (لزرار تغيير اللغة) — صفحات المنتج والتصنيف ليها روابط مختلفة في كل لغة.
 */
export const useAlternates = create<{ ar: string | null; en: string | null; set: (a: { ar: string; en: string } | null) => void }>((set) => ({
  ar: null,
  en: null,
  set: (a) => set(a ?? { ar: null, en: null }),
}));
