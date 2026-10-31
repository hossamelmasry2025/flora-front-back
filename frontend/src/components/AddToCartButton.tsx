"use client";

import { useEffect, useState } from "react";
import { Check, ShoppingBag } from "lucide-react";
import { useI18n } from "@/i18n/client";
import { useCart } from "@/lib/cart";
import type { ProductCard } from "@/lib/types";

/**
 * icon: زرار دايرة صغير (الموبايل)
 * bar: شريط بيطلع من تحت الصورة لما الماوس يقف على الكارت (الكمبيوتر)
 */
export function AddToCartButton({ product, variant = "icon", className = "" }: { product: ProductCard; variant?: "icon" | "bar"; className?: string }) {
  const { t } = useI18n();
  const add = useCart((s) => s.add);
  const [added, setAdded] = useState(false);

  useEffect(() => {
    if (!added) return;
    const id = setTimeout(() => setAdded(false), 1600);
    return () => clearTimeout(id);
  }, [added]);

  const onClick = () => {
    add(product);
    setAdded(true);
  };
  const Icon = added ? Check : ShoppingBag;

  if (variant === "bar") {
    return (
      <button
        type="button"
        onClick={onClick}
        className={`flex w-full items-center justify-center gap-2 rounded-full bg-white/95 py-3 text-sm font-semibold text-ink shadow-lift backdrop-blur transition duration-300 hover:bg-brand-900 hover:text-white ${className}`}
        aria-label={t.product.addToCartAria(product.name)}
      >
        <Icon className="h-4 w-4" strokeWidth={1.75} />
        {t.product.addToCart}
      </button>
    );
  }

  return (
    <button
      type="button"
      onClick={onClick}
      className={`flex h-10 w-10 items-center justify-center rounded-full bg-white/95 text-ink shadow-lift backdrop-blur transition hover:bg-brand-900 hover:text-white active:scale-95 ${added ? "!bg-brand-900 !text-white" : ""} ${className}`}
      aria-label={t.product.addToCartAria(product.name)}
    >
      <Icon className="h-[18px] w-[18px]" strokeWidth={1.75} />
    </button>
  );
}
