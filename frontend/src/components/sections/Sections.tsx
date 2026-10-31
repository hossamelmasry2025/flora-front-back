import Image from "next/image";
import Link from "next/link";
import { ChevronLeft, Clock, CreditCard, Flower2, Gift, Heart, Mail, Phone, Quote, ShieldCheck, Star, Truck, Wallet } from "lucide-react";
import { localePath, type Locale } from "@/i18n/config";
import { getDictionary } from "@/i18n/dictionaries";
import type { Banner, CategoryCard, HomeSection, ProductCard as Product, SectionStyle } from "@/lib/types";
import { categoryUrl } from "@/lib/utils";
import { ArtImage } from "../ArtImage";
import { BannerOverlay, BannerText } from "./BannerContent";
import { JsonLd } from "../JsonLd";
import { FlowerMark } from "../Logo";
import { ProductCard, ProductGrid } from "../ProductCard";
import { Carousel } from "./Carousel";
import { Hero } from "./Hero";
import { SectionHeading } from "./SectionHeading";

type Ctx = { locale: Locale; currency: string };

/** الخلفية الافتراضية لكل نوع لو ما اتحددش من الداش بورد */
const DEFAULT_BG: Partial<Record<HomeSection["type"], SectionStyle["bg"]>> = { features: "white", testimonials: "soft", cta: "brand" };
const DEFAULT_SPACING: Partial<Record<HomeSection["type"], SectionStyle["spacing"]>> = { features: "sm" };

const BG: Record<SectionStyle["bg"], string> = {
  none: "",
  white: "bg-white border-y border-line/60",
  soft: "bg-gradient-to-b from-sand/50 via-cream to-cream",
  sand: "bg-sand/60",
  brand: "on-brand bg-gradient-to-br from-brand-900 via-brand-950 to-brand-950 text-white",
};
const SPACING: Record<SectionStyle["spacing"], string> = { none: "py-0", sm: "py-6 sm:py-8", md: "py-10 sm:py-14", lg: "py-14 sm:py-20" };

/** غلاف كل سكشن: الخلفية والمسافات والمحاذاة اللي اتختارت في الداش بورد */
function StyledSection({ section, children }: { section: HomeSection; children: React.ReactNode }) {
  const bg = section.style?.bg && section.style.bg !== "none" ? section.style.bg : (DEFAULT_BG[section.type] ?? "none");
  const spacing = section.style?.spacing ?? DEFAULT_SPACING[section.type] ?? "md";
  const center = section.style?.align === "center" || ["testimonials", "faq", "cta"].includes(section.type);
  return (
    <section aria-label={section.title ?? undefined} className={`relative overflow-hidden ${BG[bg]} ${SPACING[spacing]} ${bg === "none" ? "section-plain" : ""} ${center ? "section-center" : ""}`}>
      <div className="reveal">{children}</div>
    </section>
  );
}

/** بيرسم السكاشن بالترتيب اللي في الداش بورد (الرئيسية، التصنيفات، صفحات المتجر...) */
export function Sections({
  sections,
  currency,
  locale,
  firstIsPageHeading = true,
}: {
  sections: HomeSection[];
  currency: string;
  locale: Locale;
  firstIsPageHeading?: boolean;
}) {
  const ctx = { locale, currency };
  return (
    <>
      {sections.map((s, i) => {
        if (s.type === "slider") return <Hero key={i} slides={s.data.items} settings={s.data.settings} isPageHeading={firstIsPageHeading && i === 0} />;
        const inner = renderInner(s, ctx);
        return inner ? <StyledSection key={i} section={s}>{inner}</StyledSection> : null;
      })}
    </>
  );
}

function renderInner(s: HomeSection, ctx: Ctx) {
        switch (s.type) {
          case "categories":
            return <CategoriesSection ctx={ctx} title={s.title} subtitle={s.subtitle} style={s.data.style} items={s.data.items} />;
          case "products":
            return <ProductsSection ctx={ctx} title={s.title} subtitle={s.subtitle} style={s.data.style} link={s.data.link} items={s.data.items} />;
          case "banners":
            return <BannersBlock ctx={ctx} title={s.title} subtitle={s.subtitle} items={s.data.items} />;
          case "features":
            return <Features title={s.title} items={s.data.items} />;
          case "text":
            return <TextSection title={s.title} content={s.data.content} />;
          case "testimonials":
            return <Testimonials ctx={ctx} title={s.title} subtitle={s.subtitle} items={s.data.items} />;
          case "faq":
            return <Faq title={s.title} subtitle={s.subtitle} items={s.data.items} />;
          case "cta":
            return <CtaSection ctx={ctx} title={s.title} subtitle={s.subtitle} data={s.data} />;
          case "video":
            return <VideoSection title={s.title} subtitle={s.subtitle} id={s.data.youtube_id} />;
          default:
            return null;
        }
}

function Wrapper({ children, className = "" }: { children: React.ReactNode; className?: string }) {
  return <div className={`container-page ${className}`}>{children}</div>;
}

const linkProps = (href: string) => (href.startsWith("/") ? {} : { target: "_blank", rel: "noopener noreferrer" });

/** دعوة للشراء: عنوان + وصف + زرارين */
function CtaSection({
  ctx,
  title,
  subtitle,
  data,
}: {
  ctx: Ctx;
  title: string | null;
  subtitle: string | null;
  data: { button_text: string | null; link: string | null; secondary_text: string | null; secondary_link: string | null };
}) {
  const href = (l: string) => (l.startsWith("/") ? localePath(ctx.locale, l) : l);
  return (
    <Wrapper className="relative text-center">
      <FlowerMark className="pointer-events-none absolute -start-10 -top-10 h-56 w-56 opacity-[0.06]" />
      <FlowerMark className="pointer-events-none absolute -bottom-16 -end-10 h-72 w-72 opacity-[0.05]" />
      <span className="eyebrow mb-4 justify-center" aria-hidden="true"><span className="text-[10px]">✦</span></span>
      {title && <h2 className="font-display text-3xl font-bold sm:text-5xl">{title}</h2>}
      {subtitle && <p className="mx-auto mt-4 max-w-2xl text-base leading-8 opacity-85 sm:text-lg">{subtitle}</p>}
      <div className="mt-9 flex flex-wrap justify-center gap-3">
        {data.button_text && data.link && (
          <Link href={href(data.link)} className="btn-gold px-9 py-4" {...linkProps(data.link)}>
            {data.button_text} <ChevronLeft className="h-4 w-4 ltr:rotate-180" />
          </Link>
        )}
        {data.secondary_text && data.secondary_link && (
          <Link href={href(data.secondary_link)} className="btn-outline border-current px-8 py-3.5 text-current hover:bg-white/10" {...linkProps(data.secondary_link)}>
            {data.secondary_text}
          </Link>
        )}
      </div>
    </Wrapper>
  );
}

/** فيديو يوتيوب (بيتحمل لما يظهر بس، عشان سرعة الصفحة) */
function VideoSection({ title, subtitle, id }: { title: string | null; subtitle: string | null; id: string }) {
  return (
    <Wrapper>
      <SectionHeading title={title} subtitle={subtitle} />
      <div className="relative mx-auto aspect-video max-w-4xl overflow-hidden rounded-[2rem] bg-ink shadow-lift">
        <iframe
          src={`https://www.youtube-nocookie.com/embed/${id}?rel=0`}
          title={title ?? "Video"}
          loading="lazy"
          allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
          allowFullScreen
          className="absolute inset-0 h-full w-full"
        />
      </div>
    </Wrapper>
  );
}

function CategoriesSection({ ctx, title, subtitle, style, items }: { ctx: Ctx; title: string | null; subtitle: string | null; style: string; items: CategoryCard[] }) {
  const t = getDictionary(ctx.locale);
  const lp = (p: string) => localePath(ctx.locale, p);

  if (style === "circles") {
    return (
      <Wrapper>
        <SectionHeading title={title} subtitle={subtitle} />
        <ul className="scrollbar-none -mx-4 flex gap-5 overflow-x-auto px-4 sm:justify-center">
          {items.map((c) => (
            <li key={c.id} className="shrink-0">
              <Link href={lp(categoryUrl(c.slug))} className="group flex w-24 flex-col items-center gap-3 text-center sm:w-28">
                <span className="relative block h-24 w-24 overflow-hidden rounded-full bg-sand ring-1 ring-gold/40 ring-offset-4 ring-offset-cream transition duration-500 group-hover:ring-gold sm:h-28 sm:w-28">
                  {c.image ? <Image src={c.image} alt={c.name} fill sizes="112px" className="object-cover transition duration-700 group-hover:scale-110" /> : <FlowerMark className="m-auto h-full w-12 text-brand-300" />}
                </span>
                <span className="font-display text-base font-bold">{c.name}</span>
              </Link>
            </li>
          ))}
        </ul>
      </Wrapper>
    );
  }

  const card = (c: CategoryCard, wide = false) => (
    <Link
      href={lp(categoryUrl(c.slug))}
      className={`group relative block overflow-hidden rounded-3xl bg-sand shadow-soft transition duration-500 hover:shadow-luxe ${wide ? "aspect-[16/10] md:aspect-[4/5]" : "aspect-[4/5]"}`}
    >
      {c.image ? (
        <Image src={c.image} alt={c.name} fill sizes="(min-width: 1024px) 25vw, 50vw" className="object-cover transition duration-[1400ms] ease-[cubic-bezier(0.22,1,0.36,1)] group-hover:scale-110" />
      ) : (
        <div className="flex h-full items-center justify-center bg-gradient-to-br from-brand-100 to-sand">
          <FlowerMark className="h-20 w-20 text-brand-300" />
        </div>
      )}
      <div className="absolute inset-0 bg-gradient-to-t from-black/75 via-black/10 to-transparent transition duration-500 group-hover:from-black/80" />
      {/* إطار ذهبي رفيع بيظهر مع الماوس */}
      <span className="pointer-events-none absolute inset-3 rounded-[calc(var(--radius-3xl)-0.5rem)] border border-white/0 transition duration-700 group-hover:inset-4 group-hover:border-gold-light/70" aria-hidden="true" />
      <div className="absolute inset-x-0 bottom-0 flex flex-col items-center p-5 text-center text-white sm:p-7">
        <h3 className="font-display text-xl font-bold text-white sm:text-2xl">{c.name}</h3>
        <span className="mt-1 text-[11px] uppercase tracking-[0.2em] text-white/70 sm:text-xs">
          {c.products_count ? t.common.productsCount(c.products_count) : t.common.shopNow}
        </span>
        <span className="mt-0 flex max-h-0 items-center gap-1.5 overflow-hidden text-xs font-semibold text-gold-light opacity-0 transition-all duration-500 group-hover:mt-3 group-hover:max-h-6 group-hover:opacity-100 sm:text-sm">
          {t.common.shopNow}
          <ChevronLeft className="h-3.5 w-3.5 ltr:rotate-180" />
        </span>
      </div>
    </Link>
  );

  return (
    <Wrapper>
      <SectionHeading title={title} subtitle={subtitle} link={lp("/categories")} linkLabel={t.common.allCategories} />
      {style === "carousel" ? (
        <Carousel label={title ?? t.nav.categories}>
          {items.map((c) => (
            <li key={c.id} className="w-[45%] shrink-0 snap-start sm:w-[30%] lg:w-[23%]">{card(c)}</li>
          ))}
        </Carousel>
      ) : (
        // 3 أعمدة لو العدد بيتقسم على 3 بس (زي 6 تصنيفات) عشان ما يفضلش صف ناقص
        <ul className={`grid grid-cols-2 gap-3 sm:gap-6 md:grid-cols-3 ${items.length % 4 !== 0 && items.length % 3 === 0 ? "" : "lg:grid-cols-4"}`}>
          {items.map((c, i) => {
            // على الموبايل (عمودين) لو العدد فردي، آخر تصنيف بياخد الصف كله بدل ما يفضل لوحده
            const wide = items.length % 2 === 1 && i === items.length - 1;
            return <li key={c.id} className={wide ? "col-span-2 md:col-span-1" : ""}>{card(c, wide)}</li>;
          })}
        </ul>
      )}
    </Wrapper>
  );
}

function ProductsSection({ ctx, title, subtitle, style, link, items }: { ctx: Ctx; title: string | null; subtitle: string | null; style: string; link: string | null; items: Product[] }) {
  const t = getDictionary(ctx.locale);
  return (
    <Wrapper>
      <SectionHeading title={title} subtitle={subtitle} link={link ? localePath(ctx.locale, link) : null} linkLabel={t.common.viewAll} />
      {style === "grid" ? (
        <ProductGrid products={items} currency={ctx.currency} locale={ctx.locale} />
      ) : (
        <Carousel label={title ?? t.listing.products}>
          {items.map((p) => (
            <li key={p.id} className="w-[62%] shrink-0 snap-start sm:w-[38%] md:w-[31%] lg:w-[23.5%]">
              <ProductCard product={p} currency={ctx.currency} locale={ctx.locale} />
            </li>
          ))}
        </Carousel>
      )}
    </Wrapper>
  );
}

const BANNER_HEIGHT = {
  sm: "min-h-[180px] sm:min-h-[240px]",
  md: "min-h-[260px] sm:min-h-[380px]",
  lg: "min-h-[360px] sm:min-h-[540px]",
};
const BANNER_SPAN = { full: "md:col-span-6", half: "md:col-span-3", third: "md:col-span-2" };

/**
 * البانرات: كل بانر بعرضه (كامل / نص / تلت) وارتفاعه ومكان كلامه ولونه من الداش بورد.
 * البانرات اللي "نص" أو "تلت" ورا بعض بتظهر جنب بعض لوحدها.
 */
function BannersBlock({ ctx, title, subtitle, items }: { ctx: Ctx; title: string | null; subtitle: string | null; items: Banner[] }) {
  return (
    <Wrapper>
      <SectionHeading title={title} subtitle={subtitle} />
      <ul className="grid gap-4 sm:gap-5 md:grid-cols-6">
        {items.map((b, i) => {
          const size = b.size ?? "full";
          const href = b.link ? localePath(ctx.locale, b.link) : null;
          const sizes = size === "full" ? "(min-width: 1280px) 1216px, 100vw" : size === "half" ? "(min-width: 768px) 50vw, 100vw" : "(min-width: 768px) 33vw, 100vw";
          const inner = (
            <div className={`group relative flex items-center overflow-hidden rounded-[2rem] bg-sand ${BANNER_HEIGHT[b.height ?? "md"]}`}>
              {b.image && <ArtImage src={b.image} mobileSrc={b.mobile_image} alt={b.title ?? ""} sizes={sizes} className="object-cover transition duration-[1500ms] group-hover:scale-105" />}
              <BannerOverlay banner={b} />
              <BannerText banner={b} href={href} heading="h3" size={size === "full" ? "md" : "sm"} showButtonAsLink={false} className="p-6 sm:p-10" />
            </div>
          );
          return (
            <li key={b.id ?? i} className={BANNER_SPAN[size]}>
              {href ? (
                <Link href={href} className="block" {...(b.link?.startsWith("/") ? {} : { target: "_blank", rel: "noopener noreferrer" })}>
                  {inner}
                </Link>
              ) : (
                inner
              )}
            </li>
          );
        })}
      </ul>
    </Wrapper>
  );
}

const featureIcons: Record<string, React.ComponentType<{ className?: string; strokeWidth?: number }>> = {
  truck: Truck, clock: Clock, flower: Flower2, gift: Gift, card: Mail, shield: ShieldCheck,
  wallet: Wallet, star: Star, heart: Heart, phone: Phone, credit: CreditCard,
};

function Features({ title, items }: { title: string | null; items: { icon: string; title: string; text?: string }[] }) {
  return (
    <div>
      <ul className="container-page grid grid-cols-2 gap-y-8 lg:grid-cols-4 lg:divide-x lg:divide-line lg:rtl:divide-x-reverse" aria-label={title ?? undefined}>
        {items.map((f, i) => {
          const Icon = featureIcons[f.icon] ?? Flower2;
          return (
            <li key={i} className="flex flex-col items-center gap-3 px-3 text-center lg:px-6">
              <span className="flex h-14 w-14 shrink-0 items-center justify-center rounded-full border border-gold/40 text-gold-dark">
                <Icon className="h-6 w-6" strokeWidth={1.25} />
              </span>
              <div>
                <h3 className="font-display text-base font-bold sm:text-lg">{f.title}</h3>
                {f.text && <p className="mt-1 text-xs leading-6 text-muted sm:text-sm">{f.text}</p>}
              </div>
            </li>
          );
        })}
      </ul>
    </div>
  );
}

function TextSection({ title, content }: { title: string | null; content: string }) {
  return (
    <Wrapper>
      <div className="relative mx-auto max-w-3xl text-center">
        <FlowerMark className="mx-auto mb-5 h-10 w-10 text-brand-200" />
        {title && <h2 className="section-title relative mb-6">{title}</h2>}
        <div className="prose-content relative text-ink/75" dangerouslySetInnerHTML={{ __html: content }} />
        <span className="mx-auto mt-6 block h-px w-24 bg-gradient-to-r from-transparent via-gold to-transparent" aria-hidden="true" />
      </div>
    </Wrapper>
  );
}

function Testimonials({ ctx, title, subtitle, items }: { ctx: Ctx; title: string | null; subtitle: string | null; items: { name: string; text: string; rating?: number | string }[] }) {
  const t = getDictionary(ctx.locale);
  return (
    <div>
      <div className="container-page">
        <div className="text-center [&>div]:justify-center">
          <SectionHeading title={title} subtitle={subtitle} />
        </div>
        <ul className="grid gap-5 md:grid-cols-3 lg:gap-8">
          {items.map((r, i) => (
            <li key={i}>
              <figure className="relative flex h-full flex-col items-center rounded-3xl bg-white/70 px-6 pb-7 pt-10 text-center shadow-soft ring-1 ring-line/60 backdrop-blur transition duration-500 hover:-translate-y-1 hover:shadow-luxe sm:px-8">
                <Quote className="absolute start-1/2 top-0 h-10 w-10 -translate-x-1/2 -translate-y-1/2 rounded-full bg-cream p-2.5 text-gold ring-1 ring-gold/30 rtl:translate-x-1/2" aria-hidden="true" strokeWidth={1.5} />
                <div className="mb-4 flex gap-1 text-gold" aria-label={t.common.rating(Number(r.rating ?? 5))}>
                  {Array.from({ length: Number(r.rating ?? 5) }).map((_, j) => <Star key={j} className="h-3.5 w-3.5 fill-current" />)}
                </div>
                <blockquote className="flex-1 font-display text-lg leading-8 text-ink/90">{ctx.locale === "ar" ? `«${r.text}»` : `“${r.text}”`}</blockquote>
                <figcaption className="mt-6 flex flex-col items-center gap-1">
                  <span className="h-px w-8 bg-gold/60" aria-hidden="true" />
                  <span className="mt-2 text-sm font-bold tracking-wide">{r.name}</span>
                </figcaption>
              </figure>
            </li>
          ))}
        </ul>
      </div>
    </div>
  );
}

function Faq({ title, subtitle, items }: { title: string | null; subtitle: string | null; items: { question: string; answer: string }[] }) {
  return (
    <Wrapper>
      <div className="mx-auto max-w-3xl">
        <div className="text-center [&>div]:justify-center">
          <SectionHeading title={title} subtitle={subtitle} />
        </div>
        <div className="border-t border-line">
          {items.map((q, i) => (
            <details key={i} className="group border-b border-line py-5" open={i === 0}>
              <summary className="flex cursor-pointer list-none items-center justify-between gap-4 [&::-webkit-details-marker]:hidden">
                <h3 className="font-display text-lg font-bold transition-colors group-open:text-brand-800 sm:text-xl">{q.question}</h3>
                <span className="flex h-8 w-8 shrink-0 items-center justify-center rounded-full border border-gold/50 text-lg font-light text-gold-dark transition duration-500 group-open:rotate-45 group-open:border-gold group-open:bg-gold group-open:text-white">+</span>
              </summary>
              <p className="mt-3 max-w-2xl text-[15px] leading-8 text-muted">{q.answer}</p>
            </details>
          ))}
        </div>
      </div>
      {/* FAQ Schema: ممكن جوجل يعرض الأسئلة تحت نتيجتك في البحث */}
      <JsonLd
        data={{
          "@context": "https://schema.org",
          "@type": "FAQPage",
          mainEntity: items.map((q) => ({ "@type": "Question", name: q.question, acceptedAnswer: { "@type": "Answer", text: q.answer } })),
        }}
      />
    </Wrapper>
  );
}
