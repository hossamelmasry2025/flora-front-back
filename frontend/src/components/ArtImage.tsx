import { getImageProps } from "next/image";

/**
 * صورة بنسختين (كمبيوتر وموبايل) مع تحسين تلقائي للحجم والصيغة — للبانرات والسلايدر.
 * بتملى العنصر الأب (لازم يكون relative).
 */
export function ArtImage({
  src,
  mobileSrc,
  alt,
  priority = false,
  eager = false,
  className = "",
  sizes = "100vw",
}: {
  src: string;
  mobileSrc?: string | null;
  alt: string;
  priority?: boolean;
  /** تحميل فوري من غير أولوية (لشرائح السلايدر التانية) */
  eager?: boolean;
  className?: string;
  sizes?: string;
}) {
  const common = { alt, sizes, quality: 75, loading: priority || eager ? ("eager" as const) : ("lazy" as const), fetchPriority: priority ? ("high" as const) : ("auto" as const) };
  const { props: desktop } = getImageProps({ ...common, src, width: 1920, height: 800 });
  const mobile = mobileSrc && mobileSrc !== src ? getImageProps({ ...common, src: mobileSrc, width: 800, height: 1000 }).props : null;

  // eslint-disable-next-line @typescript-eslint/no-unused-vars
  const { width, height, style, ...img } = desktop;

  return (
    <picture>
      {mobile && <source media="(max-width: 767px)" srcSet={mobile.srcSet} sizes={sizes} />}
      <source media="(min-width: 768px)" srcSet={desktop.srcSet} sizes={sizes} />
      {/* eslint-disable-next-line jsx-a11y/alt-text */}
      <img {...img} className={`absolute inset-0 h-full w-full object-cover ${className}`} />
    </picture>
  );
}
