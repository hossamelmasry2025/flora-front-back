import "server-only";
import { cache } from "react";
import type { Locale } from "@/i18n/config";
import { api } from "./api";
import type { Settings } from "./types";

const fallback = (locale: Locale): Settings => ({
  site_name: locale === "en" ? "Flora" : "فلورا",
  tagline: null, logo: null, favicon: null, og_image: null,
  meta_title: null, meta_description: null, meta_keywords: null,
  google_verification: null, google_analytics_id: null, facebook_pixel_id: null,
  phone: null, whatsapp: null, email: null, address: null, city: null, working_hours: null,
  social: [], announcement: null, footer_about: null, currency: locale === "en" ? "EGP" : "ج.م",
  payment: { cod_enabled: true, transfer_enabled: false, transfer_instructions: null, transfer_accounts: [] },
  checkout: { time_slots: [], min_order: 0, card_max_length: 300, same_day_cutoff: null },
  footer_pages: [], menu_categories: [],
  theme: { brand: "#b02f59", accent: "#b8935a", background: "#fbf7f3", corners: "rounded" },
  header: { menu: [], show_search: true, show_track: true, show_language: true, sticky: true },
  whatsapp_button: true,
  footer: { links: [], show_categories: true, show_pages: true },
});

/** الإعدادات مرة واحدة لكل طلب (لو الباك إند واقع الموقع بيفضل يفتح بالقيم الافتراضية) */
export const getSettings = cache(async (locale: Locale): Promise<Settings> => {
  try {
    return await api.settings(locale);
  } catch (e) {
    console.error("Failed to load settings", e);
    return fallback(locale);
  }
});
