export type ProductCard = {
  id: number;
  name: string;
  slug: string;
  short_description: string | null;
  price: number;
  sale_price: number | null;
  final_price: number;
  image: string | null;
  hover_image?: string | null;
  in_stock: boolean;
  is_featured: boolean;
};

export type Slugs = { ar: string; en: string };

export type Product = ProductCard & {
  slugs: Slugs;
  sku: string | null;
  description: string | null;
  gallery: string[];
  details: { label: string; value: string }[];
  categories: { name: string; slug: string }[];
  meta_title: string | null;
  meta_description: string | null;
  updated_at: string | null;
};

export type CategoryCard = {
  id: number;
  name: string;
  slug: string;
  description: string | null;
  image: string | null;
  products_count: number | null;
};

export type Category = CategoryCard & {
  slugs: Slugs;
  content: string | null;
  banner_image: string | null;
  meta_title: string | null;
  meta_description: string | null;
  parent: { name: string; slug: string } | null;
  children: CategoryCard[];
  sections_top: HomeSection[];
  sections_bottom: HomeSection[];
};

export type Paginated<T> = {
  data: T[];
  meta: { current_page: number; last_page: number; per_page: number; total: number };
};

export type Settings = {
  site_name: string;
  tagline: string | null;
  logo: string | null;
  favicon: string | null;
  og_image: string | null;
  meta_title: string | null;
  meta_description: string | null;
  meta_keywords: string | null;
  google_verification: string | null;
  google_analytics_id: string | null;
  facebook_pixel_id: string | null;
  phone: string | null;
  whatsapp: string | null;
  email: string | null;
  address: string | null;
  city: string | null;
  working_hours: string | null;
  social: { platform: string; url: string }[];
  announcement: string | null;
  footer_about: string | null;
  currency: string;
  payment: {
    cod_enabled: boolean;
    transfer_enabled: boolean;
    transfer_instructions: string | null;
    transfer_accounts: { name: string; number: string; holder: string | null }[];
  };
  checkout: {
    time_slots: string[];
    min_order: number;
    card_max_length: number;
    same_day_cutoff: string | null;
  };
  footer_pages: { title: string; slug: string }[];
  menu_categories: { name: string; slug: string }[];
  theme: { brand: string; accent: string; background: string; corners: "rounded" | "soft" | "sharp" };
  header: {
    menu: { label: string; link: string; highlight: boolean }[];
    show_search: boolean;
    show_track: boolean;
    show_language: boolean;
    sticky: boolean;
  };
  whatsapp_button: boolean;
  footer: { links: { label: string; link: string }[]; show_categories: boolean; show_pages: boolean };
};

export type Banner = {
  id?: number;
  image: string | null;
  mobile_image: string | null;
  title: string | null;
  subtitle: string | null;
  button_text: string | null;
  link: string | null;
  /** مكان الكلام: start = بداية السطر (يمين في العربي) */
  text_position?: "start" | "center" | "end";
  text_color?: "light" | "dark";
  /** غمقان الصورة 0-80 */
  overlay?: number;
  size?: "full" | "half" | "third";
  height?: "sm" | "md" | "lg";
};

export type SliderSettings = {
  autoplay: boolean;
  interval: number;
  height: "sm" | "md" | "lg" | "full";
  arrows: boolean;
  dots: boolean;
  effect: "fade" | "slide";
};

export type SectionStyle = { bg: "none" | "white" | "soft" | "sand" | "brand"; spacing: "none" | "sm" | "md" | "lg"; align: "start" | "center" };

type SectionBase<T extends string, D> = { type: T; title: string | null; subtitle: string | null; style?: SectionStyle; data: D };

export type HomeSection =
  | SectionBase<"slider", { items: Banner[]; settings: SliderSettings }>
  | SectionBase<"banners", { items: Banner[] }>
  | SectionBase<"categories", { style: "grid" | "circles" | "carousel"; items: CategoryCard[] }>
  | SectionBase<"products", { style: "grid" | "carousel"; link: string | null; items: ProductCard[] }>
  | SectionBase<"features", { items: { icon: string; title: string; text?: string }[] }>
  | SectionBase<"text", { content: string }>
  | SectionBase<"testimonials", { items: { name: string; text: string; rating?: number | string }[] }>
  | SectionBase<"faq", { items: { question: string; answer: string }[] }>
  | SectionBase<"cta", { button_text: string | null; link: string | null; secondary_text: string | null; secondary_link: string | null }>
  | SectionBase<"video", { youtube_id: string }>;

export type Page = {
  title: string;
  subtitle: string | null;
  image: string | null;
  slug: string;
  slugs: Slugs;
  content: string | null;
  sections: HomeSection[];
  meta_title: string | null;
  meta_description: string | null;
  updated_at: string | null;
};

export type DeliveryZone = { id: number; name: string; fee: number };

export type Order = {
  number: string;
  status: string;
  status_label: string;
  payment_method: "cod" | "transfer";
  payment_method_label: string;
  payment_status: string;
  payment_status_label: string;
  has_card: boolean;
  history: { status: string; label: string; at: string | null }[];
  recipient_name: string;
  delivery_zone: string | null;
  delivery_date: string | null;
  delivery_time: string | null;
  subtotal: number;
  delivery_fee: number;
  total: number;
  created_at: string;
  items: { name: string; image: string | null; price: number; quantity: number; total: number }[];
};
