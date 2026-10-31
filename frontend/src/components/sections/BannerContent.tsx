import Link from "next/link";
import { ChevronLeft } from "lucide-react";
import type { Banner } from "@/lib/types";

const ALIGN = {
  start: "justify-start text-start",
  center: "justify-center text-center",
  end: "justify-end text-end",
} as const;

/** طبقة الغمقان فوق الصورة (القيمة من الداش بورد 0-80) — للكلام الغامق بتبقى طبقة فاتحة */
export function BannerOverlay({ banner }: { banner: Banner }) {
  const amount = Math.min(80, Math.max(0, banner.overlay ?? 45)) / 100;
  if (!amount) return null;
  const color = banner.text_color === "dark" ? `rgb(255 255 255 / ${amount})` : `rgb(30 14 20 / ${amount})`;
  return <div className="absolute inset-0" style={{ backgroundColor: color }} aria-hidden="true" />;
}

/** الكلام والزرار فوق الصورة بالمكان واللون اللي اتختاروا */
export function BannerText({
  banner,
  href,
  heading: Heading = "h2",
  size = "md",
  showButtonAsLink = true,
  className = "",
}: {
  banner: Banner;
  href?: string | null;
  heading?: "h1" | "h2" | "h3";
  size?: "sm" | "md" | "lg";
  /** لو البانر كله رابط، الزرار بيبقى شكل بس */
  showButtonAsLink?: boolean;
  className?: string;
}) {
  if (!banner.title && !banner.subtitle && !banner.button_text) return null;

  const dark = banner.text_color === "dark";
  const align = banner.text_position ?? "start";
  const titleSize = { sm: "text-2xl sm:text-3xl", md: "text-3xl sm:text-5xl", lg: "text-[2.6rem] sm:text-6xl lg:text-7xl" }[size];
  const button = (
    <>
      {banner.button_text}
      <ChevronLeft className="h-4 w-4 transition-transform duration-300 ltr:rotate-180 rtl:group-hover/btn:-translate-x-1 ltr:group-hover/btn:translate-x-1" />
    </>
  );
  const buttonClass = `group/btn ${dark ? "btn-primary" : size === "lg" ? "btn-gold" : "btn-light"} ${size === "sm" ? "mt-4" : "mt-8"} ${size === "lg" ? "px-9 py-4 text-base" : ""}`;

  return (
    <div className={`relative flex w-full ${ALIGN[align]} ${className}`}>
      <div className={`${size === "lg" ? "max-w-2xl" : "max-w-xl"} ${dark ? "text-ink" : "text-white"}`}>
        {/* خط ذهبي رفيع فوق العنوان */}
        {size !== "sm" && banner.title && (
          <span
            className={`mb-5 flex items-center gap-2 text-gold-light ${align === "center" ? "justify-center" : align === "end" ? "justify-end" : ""}`}
            aria-hidden="true"
          >
            <span className="h-px w-10 bg-current sm:w-14" />
            <span className="text-[10px]">✦</span>
            <span className="h-px w-10 bg-current sm:w-14" />
          </span>
        )}
        {banner.title && (
          <Heading className={`font-display font-bold leading-[1.2] ${titleSize} ${dark ? "text-ink" : "text-white drop-shadow-[0_2px_24px_rgb(0_0_0/0.25)]"}`}>{banner.title}</Heading>
        )}
        {banner.subtitle && (
          <p className={`mt-4 leading-8 ${size === "lg" ? "text-base font-light sm:text-xl sm:leading-9" : size === "sm" ? "text-sm" : ""} ${dark ? "text-ink/75" : "text-white/90"}`}>
            {banner.subtitle}
          </p>
        )}
        {banner.button_text &&
          (showButtonAsLink && href ? (
            <Link href={href} className={buttonClass}>{button}</Link>
          ) : (
            <span className={buttonClass}>{button}</span>
          ))}
      </div>
    </div>
  );
}
