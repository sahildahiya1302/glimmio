// Extract best-guess client IP (works behind common proxies if trust proxy enabled in future)
export function getClientIp(req) {
  const xff = (req.headers['x-forwarded-for'] || '').split(',').map(s => s.trim()).filter(Boolean);
  if (xff.length) return xff[0];
  return req.connection?.remoteAddress ||
         req.socket?.remoteAddress ||
         req.ip ||
         '';
}

// Basic matcher: supports a.b.c.d single IP or CIDR a.b.c.d/24
export function ipAllowed(ip, allowlistCsv) {
  if (!allowlistCsv) return true;
  const list = allowlistCsv.split(',').map(s => s.trim()).filter(Boolean);
  return list.some(rule => ipInRule(ip, rule));
}

function ipInRule(ip, rule) {
  if (!rule.includes('/')) {
    return ip === rule || ip.endsWith('::ffff:' + rule); // handle IPv4-mapped IPv6
  }
  const [base, cidrBitsStr] = rule.split('/');
  const cidrBits = parseInt(cidrBitsStr, 10);
  const ipNum = ipv4ToInt(ip);
  const baseNum = ipv4ToInt(base);
  if (ipNum === null || baseNum === null) return false;
  const mask = cidrBits === 0 ? 0 : ~((1 << (32 - cidrBits)) - 1) >>> 0;
  return (ipNum & mask) === (baseNum & mask);
}

function ipv4ToInt(ip) {
  const m = ip.match(/(\d+)\.(\d+)\.(\d+)\.(\d+)/);
  if (!m) return null;
  return ((+m[1] << 24) | (+m[2] << 16) | (+m[3] << 8) | (+m[4])) >>> 0;
}
