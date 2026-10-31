import type { Locale } from "@/i18n/config";
import { getDictionary } from "@/i18n/dictionaries";
import { whatsappLink } from "@/lib/utils";
import { SocialIcon } from "./SocialIcon";

export function WhatsAppFloat({ number, locale }: { number: string | null; locale: Locale }) {
  const t = getDictionary(locale);
  const href = whatsappLink(number, t.whatsapp.message);
  if (!href) return null;

  return (
    <a
      href={href}
      target="_blank"
      rel="noopener noreferrer"
      aria-label={t.whatsapp.aria}
      className="fixed bottom-5 start-5 z-30 flex h-14 w-14 items-center justify-center rounded-full bg-[#25D366] text-white shadow-lift transition hover:scale-110"
    >
      <span className="absolute inset-0 animate-ping rounded-full bg-[#25D366] opacity-20" aria-hidden="true" />
      <SocialIcon platform="whatsapp" className="relative h-7 w-7" />
    </a>
  );
}
