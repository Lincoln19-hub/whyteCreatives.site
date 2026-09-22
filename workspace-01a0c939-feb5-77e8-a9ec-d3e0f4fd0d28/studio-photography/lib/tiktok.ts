// ── TikTok latest-works engine (port of the WP theme's server-side scraper) ──
// Reads the creator's public profile page for video IDs, enriches each with
// official oEmbed data (title + thumbnail). No API keys needed.

export interface TikTokVideo {
  id: string;
  url: string;
  title: string;
  thumbnail: string;
}

const UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36";

// Module-level cache (survives across requests in warm serverless instances)
let cache: { key: string; ts: number; data: TikTokVideo[] } | null = null;
const TTL = 6 * 60 * 60 * 1000; // 6 hours
const oembedCache = new Map<string, { ts: number; data: Partial<TikTokVideo> }>();

export function cleanUsername(raw: string): string {
  return raw.trim().replace(/^@/, "");
}

/** Fetch the creator's newest videos (profile-page scrape + oEmbed enrichment) */
export async function getLatestTikTokVideos(rawUsername: string, limit = 12): Promise<TikTokVideo[]> {
  const username = cleanUsername(rawUsername);
  if (!username) return [];

  const key = `${username}:${limit}`;
  if (cache && cache.key === key && Date.now() - cache.ts < TTL) return cache.data;

  // ── 1. Scrape video IDs from the public profile page ──
  const ids: string[] = [];
  try {
    const res = await fetch(`https://www.tiktok.com/@${encodeURIComponent(username)}`, {
      headers: { "User-Agent": UA, "Accept-Language": "en-US,en;q=0.9" },
      cache: "no-store",
    });
    if (res.ok) {
      const html = await res.text();
      const patterns = [/"video":\{"id":"(\d{19})"/, /"id":"(\d{19})"/, /"itemId":"(\d{15,20})"/];
      for (const re of patterns) {
        const m = html.match(new RegExp(re.source, "g"));
        if (m) {
          for (const raw of m) {
            const mm = raw.match(re);
            if (mm?.[1] && !ids.includes(mm[1])) ids.push(mm[1]);
          }
          if (ids.length > 0) break;
        }
      }
    }
  } catch { /* network hiccup → empty */ }

  // ── 2. Enrich with official oEmbed data (title + thumbnail) ──
  const out: TikTokVideo[] = [];
  for (const id of ids.slice(0, limit)) {
    const url = `https://www.tiktok.com/@${username}/video/${id}`;
    const item = await getOembed(url);
    out.push({ id, url, title: item.title ?? "Latest work", thumbnail: item.thumbnail ?? "" });
  }

  if (out.length > 0) {
    cache = { key, ts: Date.now(), data: out };
  }
  return out;
}

async function getOembed(videoUrl: string): Promise<Partial<TikTokVideo>> {
  const hit = oembedCache.get(videoUrl);
  if (hit && Date.now() - hit.ts < 12 * 60 * 60 * 1000) return hit.data;

  const data: Partial<TikTokVideo> = {};
  try {
    const res = await fetch(`https://www.tiktok.com/oembed?url=${encodeURIComponent(videoUrl)}`, {
      headers: { "User-Agent": UA },
      cache: "no-store",
    });
    if (res.ok) {
      const json = await res.json();
      if (json?.title) data.title = String(json.title).slice(0, 140);
      if (json?.thumbnail_url) data.thumbnail = String(json.thumbnail_url);
    }
  } catch { /* defaults used */ }
  oembedCache.set(videoUrl, { ts: Date.now(), data });
  return data;
}
