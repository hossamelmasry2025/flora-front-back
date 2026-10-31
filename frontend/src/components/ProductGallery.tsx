"use client";

import Image from "next/image";
import { useState } from "react";
import { useI18n } from "@/i18n/client";
import { FlowerMark } from "./Logo";

export function ProductGallery({ images, name }: { images: string[]; name: string }) {
  const { t } = useI18n();
  const [active, setActive] = useState(0);

  if (images.length === 0) {
    return (
      <div className="flex aspect-[4/5] items-center justify-center rounded-[2rem] bg-sand">
        <FlowerMark className="h-24 w-24 text-brand-200" />
      </div>
    );
  }

  return (
    <div className="flex flex-col gap-4 lg:sticky lg:top-28 lg:self-start">
      <div className="relative aspect-[4/5] overflow-hidden rounded-[2rem] bg-sand shadow-luxe sm:aspect-square lg:aspect-[4/5]">
        {images.map((src, i) => (
          <Image
            key={src}
            src={src}
            alt={i === 0 ? name : `${name} — ${i + 1}`}
            fill
            sizes="(min-width: 1024px) 600px, 100vw"
            className={`object-cover transition-opacity duration-500 ${i === active ? "opacity-100" : "opacity-0"}`}
            loading={i === 0 ? "eager" : "lazy"}
            fetchPriority={i === 0 ? "high" : "auto"}
            quality={85}
          />
        ))}
      </div>
      {images.length > 1 && (
        <ul className="flex gap-3" aria-label={t.product.images}>
          {images.map((src, i) => (
            <li key={src}>
              <button
                type="button"
                onClick={() => setActive(i)}
                className={`relative block h-24 w-20 overflow-hidden rounded-2xl bg-sand ring-1 ring-offset-2 ring-offset-cream transition duration-300 ${i === active ? "ring-gold" : "opacity-60 ring-transparent hover:opacity-100"}`}
                aria-label={t.product.imageN(i + 1)}
                aria-current={i === active}
              >
                <Image src={src} alt="" fill sizes="80px" className="object-cover" />
              </button>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
