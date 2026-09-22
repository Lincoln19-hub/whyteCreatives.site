// ── Google Drive public folder parser (TypeScript port of the theme's PHP engine) ──

export interface DriveFile {
  id: string;
  url: string;        // direct view URL (lh3 CDN)
  downloadUrl: string;
  title: string;
}

const UA = "Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/122.0.0.0 Safari/537.36";
const NON_IMAGE = [".pdf", ".zip", ".rar", ".mus", ".docx", ".xlsx", ".mp3", ".mp4", " pdf", " binary"];

/** Extract the folder id from any public Drive folder URL */
export function driveFolderId(folderUrl: string): string {
  const m1 = folderUrl.match(/\/folders\/([a-zA-Z0-9_-]+)/);
  if (m1) return m1[1];
  const m2 = folderUrl.match(/[?&]id=([a-zA-Z0-9_-]+)/);
  if (m2) return m2[1];
  return "";
}

/** Convert any Drive share/file URL to a direct lh3 CDN image URL */
export function driveDirectUrl(url: string): string {
  const m = url.match(/drive\.google\.com[^\s]*?(?:\/file\/d\/|[?&]id=)([a-zA-Z0-9_-]+)/);
  return m ? `https://lh3.googleusercontent.com/d/${m[1]}` : url;
}

/** Fetch + parse a public Drive folder (embeddedfolderview first, folder page fallback) */
export async function fetchDriveFolder(folderUrl: string): Promise<DriveFile[]> {
  const folderId = driveFolderId(folderUrl);
  if (!folderId) return [];

  const seen = new Set<string>();
  const files: DriveFile[] = [];
  let index = 1;

  const add = (id: string, rawTitle: string) => {
    if (!id || seen.has(id)) return;
    const title = rawTitle.replace(/\s+(Binary|PDF|Image|Video|Archive|Document)$/i, "").replace(/\.(jpe?g|png|webp|gif)$/i, "");
    const lower = rawTitle.toLowerCase();
    if (NON_IMAGE.some((t) => lower.includes(t))) return;
    seen.add(id);
    files.push({
      id,
      url: `https://lh3.googleusercontent.com/d/${id}`,
      downloadUrl: `https://drive.google.com/uc?export=download&id=${id}`,
      title: title || `Photo ${String(index).padStart(2, "0")}`,
    });
    index++;
  };

  // Strategy 1: stable embedded folder listing
  try {
    const r = await fetch(`https://drive.google.com/embeddedfolderview?id=${folderId}#list`, {
      headers: { "User-Agent": UA, "Accept-Language": "en-US,en;q=0.9" },
      cache: "no-store",
    });
    if (r.ok) {
      const html = await r.text();
      const re = /id="entry-([a-zA-Z0-9_-]{25,})"[\s\S]{0,800}?flip-entry-title">([^<]*)</g;
      let m: RegExpExecArray | null;
      while ((m = re.exec(html))) add(m[1], m[2]);
    }
  } catch { /* fall through */ }

  if (files.length === 0) {
    // Strategy 2: regular folder page scrape
    try {
      const r = await fetch(`https://drive.google.com/drive/folders/${folderId}`, {
        headers: { "User-Agent": UA },
        cache: "no-store",
      });
      if (r.ok) {
        const html = await r.text();
        const re = /data-id="([a-zA-Z0-9_-]{25,50})"[^>]*?data-tooltip="([^"]+)"/g;
        let m: RegExpExecArray | null;
        while ((m = re.exec(html))) add(m[1], m[2]);
        if (files.length === 0) {
          const re2 = /\/file\/d\/([a-zA-Z0-9_-]{28,45})\b/g;
          let m2: RegExpExecArray | null;
          while ((m2 = re2.exec(html))) add(m2[1], `Photo ${String(files.length + 1).padStart(2, "0")}`);
        }
      }
    } catch { /* empty */ }
  }

  return files;
}

/** Width-sized lh3 variant for fast grids */
export function sizedUrl(url: string, width = 800): string {
  if (!url.includes("googleusercontent.com/")) return url;
  if (/=w\d+(-h\d+)*(-[a-z]+)*$/.test(url)) return url;
  return `${url}=w${width}`;
}
