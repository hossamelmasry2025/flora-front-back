import Image from "next/image";
import Link from "next/link";

/** لو فيه لوجو مرفوع من الداش بورد بيظهر، غير كده اسم المتجر مع وردة */
export function Logo({
  name,
  logo,
  href = "/",
  homeLabel = "الصفحة الرئيسية",
  className = "",
  tone = "dark",
}: {
  name: string;
  logo: string | null;
  href?: string;
  homeLabel?: string;
  className?: string;
  /** light: للخلفيات الغامقة (الفوتر) */
  tone?: "dark" | "light";
}) {
  return (
    <Link href={href} className={`flex items-center gap-2 ${className}`} aria-label={`${name} — ${homeLabel}`}>
      {logo ? (
        <Image src={logo} alt={name} width={160} height={48} className="h-10 w-auto object-contain sm:h-12" loading="eager" />
      ) : (
        <>
          <FlowerMark className={`h-9 w-9 ${tone === "light" ? "text-brand-300" : "text-brand-600"}`} />
          <span className={`font-display text-2xl font-bold sm:text-3xl ${tone === "light" ? "text-white" : "text-ink"}`}>{name}</span>
        </>
      )}
    </Link>
  );
}

export function FlowerMark({ className = "" }: { className?: string }) {
  return (
    <svg viewBox="0 0 48 48" fill="none" className={className} aria-hidden="true">
      <g fill="currentColor">
        <ellipse cx="24" cy="13" rx="7" ry="10" opacity=".9" />
        <ellipse cx="24" cy="13" rx="7" ry="10" opacity=".75" transform="rotate(72 24 24)" />
        <ellipse cx="24" cy="13" rx="7" ry="10" opacity=".6" transform="rotate(144 24 24)" />
        <ellipse cx="24" cy="13" rx="7" ry="10" opacity=".75" transform="rotate(216 24 24)" />
        <ellipse cx="24" cy="13" rx="7" ry="10" opacity=".9" transform="rotate(288 24 24)" />
      </g>
      <circle cx="24" cy="24" r="5" fill="#b8935a" />
    </svg>
  );
}
