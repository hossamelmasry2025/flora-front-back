import Link from "next/link";
import { FlowerMark } from "./Logo";

/** شكل موحد للصفحات الفاضية (سلة فاضية، مفيش نتايج...) */
export function EmptyState({ text, hint, action }: { text: string; hint?: string; action?: { href: string; label: string } }) {
  return (
    <div className="flex flex-col items-center gap-4 rounded-3xl border border-dashed border-gold/40 px-6 py-20 text-center">
      <span className="flex h-24 w-24 items-center justify-center rounded-full border border-gold/30 bg-white/60">
        <FlowerMark className="h-12 w-12 text-brand-200" />
      </span>
      <p className="font-display text-2xl font-bold">{text}</p>
      {hint && <p className="max-w-sm text-sm leading-7 text-muted">{hint}</p>}
      {action && <Link href={action.href} className="btn-primary mt-2">{action.label}</Link>}
    </div>
  );
}
