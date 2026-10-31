"use client";

import { create } from "zustand";
import { createJSONStorage, persist } from "zustand/middleware";

export type SavedOrder = { number: string; phone: string; total: number; created_at: string };

/**
 * طلبات العميل محفوظة على جهازه (رقم الطلب + الموبايل) عشان يتابعها في أي وقت
 * من صفحة "تتبع طلبك" من غير ما يكتب البيانات تاني.
 */
export const useMyOrders = create<{ orders: SavedOrder[]; add: (o: SavedOrder) => void; remove: (n: string) => void }>()(
  persist(
    (set) => ({
      orders: [],
      add: (o) => set((s) => ({ orders: [o, ...s.orders.filter((x) => x.number !== o.number)].slice(0, 10) })),
      remove: (n) => set((s) => ({ orders: s.orders.filter((x) => x.number !== n) })),
    }),
    { name: "flora-my-orders", storage: createJSONStorage(() => localStorage) },
  ),
);
