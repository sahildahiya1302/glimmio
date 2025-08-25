// Compute effective leave days between start & end based on type config and holiday list
export function computeDays({ start_date, end_date, half_day, weekend_inclusive, holidaysSet }) {
  const start = new Date(start_date);
  const end = new Date(end_date);
  if (end < start) return 0;

  let days = 0;
  for (let d = new Date(start); d <= end; d.setDate(d.getDate() + 1)) {
    const iso = d.toISOString().slice(0, 10);
    const isWeekend = d.getDay() === 0 || d.getDay() === 6;
    const isHoliday = holidaysSet.has(iso);
    if (!weekend_inclusive && (isWeekend || isHoliday)) continue;
    days += 1;
  }

  if (half_day) days -= 0.5; // half day counts as one day range with -0.5
  return Math.max(days, 0);
}
