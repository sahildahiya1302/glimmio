// Haversine distance in meters
export function distanceMeters(lat1, lon1, lat2, lon2) {
  if (
    [lat1, lon1, lat2, lon2].some(
      (v) => v === undefined || v === null || Number.isNaN(Number(v))
    )
  ) return Infinity;

  const toRad = (d) => (d * Math.PI) / 180;
  const R = 6371000; // Earth radius in meters
  const dLat = toRad(lat2 - lat1);
  const dLon = toRad(lon2 - lon1);
  const a =
    Math.sin(dLat / 2) * Math.sin(dLat / 2) +
    Math.cos(toRad(lat1)) * Math.cos(toRad(lat2)) *
    Math.sin(dLon / 2) * Math.sin(dLon / 2);

  return 2 * R * Math.atan2(Math.sqrt(a), Math.sqrt(1 - a));
}
