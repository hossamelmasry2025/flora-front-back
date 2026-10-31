/** تاريخ النهارده بتوقيت القاهرة بصيغة YYYY-MM-DD */
function cairoNow() {
  const parts = new Intl.DateTimeFormat("en-CA", {
    timeZone: "Africa/Cairo",
    year: "numeric", month: "2-digit", day: "2-digit", hour: "2-digit", minute: "2-digit", hour12: false,
  }).formatToParts(new Date());
  const get = (t: string) => parts.find((p) => p.type === t)?.value ?? "00";
  return { date: `${get("year")}-${get("month")}-${get("day")}`, time: `${get("hour")}:${get("minute")}` };
}

function addDays(date: string, days: number) {
  const d = new Date(`${date}T12:00:00Z`);
  d.setUTCDate(d.getUTCDate() + days);
  return d.toISOString().slice(0, 10);
}

/** أقرب يوم توصيل: النهارده، أو بكره لو عدّى آخر ميعاد لطلبات نفس اليوم */
export function earliestDeliveryDate(cutoff: string | null) {
  const now = cairoNow();
  if (cutoff && now.time >= cutoff.slice(0, 5)) return addDays(now.date, 1);
  return now.date;
}
