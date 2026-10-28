import fs from 'node:fs'

// Website-only editorial corrections. Match the entire Arabic text, never a record ID or
// opening phrase: an admin edit must not inherit a citation for a different transmitted text.
export const normalizeArabic = value => String(value || '').normalize('NFKC')
  .replace(/[\u0610-\u061a\u064b-\u065f\u0670\u06d6-\u06ed\u0640]/gu, '')
  .replace(/[یى]/g, 'ي').replace(/ک/g, 'ك').replace(/ٱ/g, 'ا')
  .replace(/[\s،,.;؛:؟!\u200c\u200d\u200e\u200f]+/gu, '')

const entries = JSON.parse(fs.readFileSync(new URL('../content-snapshot/collection-editorial.json', import.meta.url), 'utf8'))
const reviews = new Map(entries.map(entry => [normalizeArabic(entry.expectedArabic), entry]))
export function reviewedDhikr(item) { return reviews.get(normalizeArabic(item.arabic_text)) || null }
