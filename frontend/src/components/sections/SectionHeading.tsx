import Link from "next/link";
import { ArrowLeft } from "lucide-react";

export function SectionHeading({ title, subtitle, link, linkLabel }: { title?: string | null; subtitle?: string | null; link?: string | null; linkLabel?: string }) {
  if (!title && !subtitle) return null;

  return (
    <div className="section-head mb-7 flex items-end justify-between gap-4 sm:mb-9">
      <div>
        {/* زخرفة ذهبية رفيعة فوق العنوان */}
        <span className="eyebrow mb-3" aria-hidden="true">
          <span className="text-[10px] leading-none">✦</span>
        </span>
        {title && <h2 className="section-title flex items-center gap-3">{title}</h2>}
        {subtitle && <p className="mt-3 max-w-xl text-sm leading-7 text-muted sm:text-base">{subtitle}</p>}
      </div>
      {link && (
        <Link href={link} className="group flex shrink-0 items-center gap-2.5 pb-1 text-sm font-semibold text-ink">
          <span className="link-underline">{linkLabel}</span>
          <span className="flex h-9 w-9 items-center justify-center rounded-full border border-gold/50 text-gold-dark transition duration-300 group-hover:border-gold group-hover:bg-gold group-hover:text-white">
            <ArrowLeft className="h-4 w-4 ltr:rotate-180" strokeWidth={1.75} />
          </span>
        </Link>
      )}
    </div>
  );
}
