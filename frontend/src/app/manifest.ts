import type { MetadataRoute } from "next";
import { getSettings } from "@/lib/settings";

export default async function manifest(): Promise<MetadataRoute.Manifest> {
  const s = await getSettings("ar");
  return {
    name: s.site_name,
    short_name: s.site_name,
    description: s.meta_description ?? s.tagline ?? undefined,
    start_url: "/",
    display: "standalone",
    background_color: "#fbf7f3",
    theme_color: "#922449",
    lang: "ar",
    dir: "rtl",
    icons: s.favicon ? [{ src: s.favicon, sizes: "512x512", type: "image/png" }] : [{ src: "/icon.svg", sizes: "any", type: "image/svg+xml" }],
  };
}
