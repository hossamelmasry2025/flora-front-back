"use client";

import { useEffect } from "react";
import { FlowerMark } from "@/components/Logo";
import { useI18n } from "@/i18n/client";

export default function Error({ error, reset }: { error: Error & { digest?: string }; reset: () => void }) {
  const { t } = useI18n();
  useEffect(() => console.error(error), [error]);

  return (
    <div className="container-page flex flex-col items-center py-24 text-center">
      <FlowerMark className="h-20 w-20 text-brand-200" />
      <h1 className="mt-6 text-2xl font-bold">{t.error.title}</h1>
      <p className="mt-2 text-muted">{t.error.text}</p>
      <button type="button" onClick={reset} className="btn-primary mt-8">{t.error.retry}</button>
    </div>
  );
}
