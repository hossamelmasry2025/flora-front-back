"use client";

import { useSyncExternalStore } from "react";

const subscribe = () => () => {};

/** true بعد ما الصفحة تتحمل في المتصفح — عشان السلة (localStorage) ما تعملش hydration mismatch */
export function useHydrated() {
  return useSyncExternalStore(
    subscribe,
    () => true,
    () => false,
  );
}
