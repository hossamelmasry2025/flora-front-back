import { NextResponse, type NextRequest } from "next/server";

/**
 * العربي (اللغة الأساسية) من غير بادئة:  /products  ← بيتعرض من /ar/products داخلياً
 * الإنجليزي تحت /en:                     /en/products
 * أي حد يفتح /ar/... بيتحول للرابط من غير بادئة (عشان ما يبقاش فيه نسختين من نفس الصفحة).
 */
export function proxy(request: NextRequest) {
  const { pathname, search } = request.nextUrl;

  if (pathname === "/en" || pathname.startsWith("/en/")) return NextResponse.next();

  if (pathname === "/ar" || pathname.startsWith("/ar/")) {
    const url = request.nextUrl.clone();
    url.pathname = pathname.slice(3) || "/";
    return NextResponse.redirect(url, 308);
  }

  const url = request.nextUrl.clone();
  url.pathname = `/ar${pathname === "/" ? "" : pathname}`;
  url.search = search;
  return NextResponse.rewrite(url);
}

export const config = {
  // كل الصفحات ما عدا الملفات الثابتة والـ API و sitemap/robots
  matcher: ["/((?!api|_next|.*\\..*).*)"],
};
