import type { MetadataRoute } from "next";
import { absoluteUrl } from "@/lib/utils";

export default function robots(): MetadataRoute.Robots {
  return {
    rules: [
      {
        userAgent: "*",
        allow: "/",
        // صفحات خاصة بالعميل أو نسخ مكررة من صفحات تانية
        disallow: ["/cart", "/checkout", "/order/", "/en/cart", "/en/checkout", "/en/order/", "/api/", "/*?*sort=", "/*?*min=", "/*?*max="],
      },
    ],
    sitemap: absoluteUrl("/sitemap.xml"),
    host: absoluteUrl("/"),
  };
}
