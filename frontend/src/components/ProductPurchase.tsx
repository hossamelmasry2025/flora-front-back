"use client";

import { useRouter } from "next/navigation";
import { useState } from "react";
import { ShoppingBag, Zap } from "lucide-react";
import { useI18n } from "@/i18n/client";
import { useCart } from "@/lib/cart";
import type { ProductCard } from "@/lib/types";
import { QuantityControl } from "./CartDrawer";

export function ProductPurchase({ product }: { product: ProductCard }) {
  const { t, lp } = useI18n();
  const [qty, setQty] = useState(1);
  const add = useCart((s) => s.add);
  const close = useCart((s) => s.close);
  const router = useRouter();

  if (!product.in_stock) {
    return <p className="rounded-2xl bg-sand px-5 py-4 font-semibold text-muted">{t.product.unavailable}</p>;
  }

  const buyNow = () => {
    add(product, qty);
    close();
    router.push(lp("/checkout"));
  };

  return (
    <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
      <QuantityControl value={qty} onChange={setQty} size="lg" />
      <button type="button" onClick={() => add(product, qty)} className="btn-outline flex-1 py-3.5 text-base">
        <ShoppingBag className="h-5 w-5" /> {t.product.addToCart}
      </button>
      <button type="button" onClick={buyNow} className="btn-primary flex-1 py-3.5 text-base">
        <Zap className="h-5 w-5" /> {t.product.buyNow}
      </button>
    </div>
  );
}
