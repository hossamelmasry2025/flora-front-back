import { notFound } from "next/navigation";

/** أي رابط مش موجود جوه اللغة بيعرض صفحة 404 بتصميم الموقع */
export default function CatchAll() {
  notFound();
}
