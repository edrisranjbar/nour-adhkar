import fontUrl from '../assets/fonts/vazir/Vazirmatn-FD-Regular.woff2?url';
import boldFontUrl from '../assets/fonts/vazir/Vazirmatn-FD-Bold.woff2?url';
import naskhFontUrl from '../assets/fonts/noto-naskh/NotoNaskhArabic.ttf?url';
import logoUrl from '../assets/icons/logo.png?url';

export const palettes = {
  paper: { background: '#F6F3EC', text: '#243D34', muted: '#69776C' },
  white: { background: '#FFFFFF', text: '#243D34', muted: '#69776C' },
  night: { background: '#172C25', text: '#F6F3EC', muted: '#C0CBC3' },
};

export const fontChoices = [
  { value: 'vazirmatn', label: 'وزیرمتن', family: 'NourSocial', url: fontUrl },
  { value: 'vazirmatn-bold', label: 'وزیرمتن ضخیم', family: 'NourSocialBold', url: boldFontUrl },
  { value: 'noto-naskh', label: 'نسخ (Noto Naskh)', family: 'NourSocialNaskh', url: naskhFontUrl },
];
export const defaultDesign = { ...palettes.paper, font: 'vazirmatn', font_size: 60, line_height: 1.85, margin: 125, logo_size: 76, layout: 'centered' };

export function normalizeDesign(design) {
  return { ...defaultDesign, ...(typeof design === 'string' ? palettes[design] : design) };
}

let assets;
async function loadAssets() {
  if (!assets) {
    assets = (async () => {
      const logo = new Image();
      logo.src = logoUrl;
      await logo.decode();
      return logo;
    })().catch(error => { assets = null; throw error; });
  }
  return assets;
}

const loadedFonts = new Map();
async function loadFont(key) {
  const choice = fontChoices.find(font => font.value === key) || fontChoices[0];
  if (!loadedFonts.has(choice.value)) {
    loadedFonts.set(choice.value, new FontFace(choice.family, `url("${choice.url}")`).load().then(font => {
      document.fonts.add(font);
      return choice.family;
    }).catch(error => { loadedFonts.delete(choice.value); throw error; }));
  }
  return loadedFonts.get(choice.value);
}

export function wrapText(ctx, text, maxWidth) {
  const lines = [];
  for (const paragraph of text.split('\n')) {
    let line = '';
    for (const word of paragraph.split(/\s+/).filter(Boolean)) {
      const candidate = line ? `${line} ${word}` : word;
      if (line && ctx.measureText(candidate).width > maxWidth) {
        lines.push(line);
        line = word;
      } else line = candidate;
    }
    lines.push(line);
  }
  return lines;
}

// Preview and download use the same canvas: no screenshot-dependent layout or remote fonts.
export async function renderInstagramImage(verse, design = 'paper') {
  const settings = normalizeDesign(design);
  const [logo, family] = await Promise.all([loadAssets(), loadFont(settings.font)]);
  const canvas = document.createElement('canvas');
  canvas.width = 1080;
  canvas.height = 1350;
  const ctx = canvas.getContext('2d');
  if (!ctx) throw new Error('Canvas unavailable');
  ctx.fillStyle = settings.background;
  ctx.fillRect(0, 0, canvas.width, canvas.height);
  if (settings.layout === 'framed') {
    ctx.strokeStyle = settings.muted;
    ctx.lineWidth = 2;
    ctx.strokeRect(48, 48, 984, 1254);
  }
  ctx.direction = 'rtl';
  ctx.textAlign = 'center';
  ctx.textBaseline = 'middle';
  let lines, size;
  const maxWidth = 1080 - settings.margin * 2;
  const maxHeight = settings.layout === 'upper' ? 560 : 700;
  for (size = settings.font_size; size >= 30; size -= 2) {
    ctx.font = `${size}px ${family}`;
    lines = wrapText(ctx, verse.translation, maxWidth);
    if (lines.length * size * settings.line_height <= maxHeight && lines.every(line => ctx.measureText(line).width <= maxWidth)) break;
  }
  if (size < 30) throw new Error('Text does not fit');
  const lineHeight = size * settings.line_height;
  const center = settings.layout === 'upper' ? 460 : 610;
  const top = center - ((lines.length - 1) * lineHeight) / 2;
  ctx.fillStyle = settings.text;
  lines.forEach((line, index) => ctx.fillText(line, 540, top + index * lineHeight));
  ctx.fillStyle = settings.muted;
  ctx.font = `28px ${family}`;
  ctx.fillText(verse.reference.replace(/\d/g, digit => '۰۱۲۳۴۵۶۷۸۹'[digit]), 540, top + (lines.length - 1) * lineHeight + 100);
  ctx.font = `20px ${family}`;
  ctx.fillText(`ترجمه: ${verse.translator}`, 540, 1190);
  const ratio = logo.naturalWidth / logo.naturalHeight;
  const width = ratio >= 1 ? settings.logo_size : settings.logo_size * ratio;
  const height = ratio >= 1 ? settings.logo_size / ratio : settings.logo_size;
  ctx.drawImage(logo, 1000 - width, 1244 - height, width, height);
  return new Promise((resolve, reject) => canvas.toBlob(blob => blob ? resolve(blob) : reject(new Error('Image export failed')), 'image/jpeg', 0.96));
}
