"use client";

import { create } from "zustand";
import { createJSONStorage, persist } from "zustand/middleware";
import type { ProductCard } from "./types";

export type CartItem = {
  id: number;
  slug: string;
  name: string;
  price: number;
  image: string | null;
  quantity: number;
};

type CartState = {
  items: CartItem[];
  isOpen: boolean;
  add: (product: ProductCard, quantity?: number) => void;
  setQuantity: (id: number, quantity: number) => void;
  remove: (id: number) => void;
  clear: () => void;
  open: () => void;
  close: () => void;
};

/** السلة بتتحفظ في المتصفح — الأسعار النهائية بيحسبها السيرفر وقت الطلب */
export const useCart = create<CartState>()(
  persist(
    (set) => ({
      items: [],
      isOpen: false,
      add: (product, quantity = 1) =>
        set((state) => {
          const existing = state.items.find((i) => i.id === product.id);
          const items = existing
            ? state.items.map((i) => (i.id === product.id ? { ...i, quantity: Math.min(i.quantity + quantity, 50) } : i))
            : [
                ...state.items,
                {
                  id: product.id,
                  slug: product.slug,
                  name: product.name,
                  price: product.final_price,
                  image: product.image,
                  quantity,
                },
              ];
          return { items, isOpen: true };
        }),
      setQuantity: (id, quantity) =>
        set((state) => ({
          items: state.items.map((i) => (i.id === id ? { ...i, quantity: Math.max(1, Math.min(quantity, 50)) } : i)),
        })),
      remove: (id) => set((state) => ({ items: state.items.filter((i) => i.id !== id) })),
      clear: () => set({ items: [] }),
      open: () => set({ isOpen: true }),
      close: () => set({ isOpen: false }),
    }),
    {
      name: "flora-cart",
      storage: createJSONStorage(() => localStorage),
      partialize: (state) => ({ items: state.items }),
    },
  ),
);

export const cartCount = (items: CartItem[]) => items.reduce((n, i) => n + i.quantity, 0);
export const cartSubtotal = (items: CartItem[]) => items.reduce((n, i) => n + i.price * i.quantity, 0);
