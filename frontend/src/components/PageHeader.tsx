/** عنوان الصفحة: زخرفة ذهبية + عنوان كبير + وصف — نفس الشكل في كل صفحات الموقع */
export function PageHeader({
  title,
  subtitle,
  center = false,
  icon,
  className = "",
  children,
}: {
  title: string;
  subtitle?: string | null;
  center?: boolean;
  icon?: React.ReactNode;
  className?: string;
  children?: React.ReactNode;
}) {
  return (
    <header className={`${center ? "section-center mx-auto max-w-2xl text-center" : "max-w-3xl"} mb-8 sm:mb-10 ${className}`}>
      {icon ? (
        <span className={`mb-5 flex h-16 w-16 items-center justify-center rounded-full border border-gold/40 text-gold-dark ${center ? "mx-auto" : ""}`}>{icon}</span>
      ) : (
        <span className={`eyebrow mb-3 ${center ? "justify-center" : ""}`} aria-hidden="true">
          <span className="text-[10px] leading-none">✦</span>
        </span>
      )}
      <h1 className="font-display text-4xl font-bold leading-tight sm:text-5xl">{title}</h1>
      {subtitle && <p className="mt-4 text-base leading-8 text-muted">{subtitle}</p>}
      {children}
    </header>
  );
}
