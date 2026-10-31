"use client";

import { useEffect } from "react";
import { useAlternates } from "@/i18n/client";

/** بيعرّف زرار اللغة برابط الصفحة دي باللغة التانية (للصفحات اللي روابطها مختلفة) */
export function SetAlternates({ ar, en }: { ar: string; en: string }) {
  const set = useAlternates((s) => s.set);
  useEffect(() => {
    set({ ar, en });
    return () => set(null);
  }, [ar, en, set]);
  return null;
}
