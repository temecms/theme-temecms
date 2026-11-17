// Helpers for writing the site's content as Tiptap JSON (never HTML). Used by content/*.mjs and
// read by seed.php through content.json. Conventions the theme draws:
//  - a horizontal rule (hr()) ends a section; section 0 is the page's opening;
//  - a paragraph that is only bold text (kicker()) is the small label above a section's heading;
//  - h2 is a section's heading, h3 + paragraphs after it make a card (2 or more h3 = a grid);
//  - a paragraph that is only a bold link (cta()) is drawn as a quiet button;
//  - ol() is drawn as numbered steps, image() as a picture with its caption.
const ALPHA = '0123456789ABCDEFGHJKMNPQRSTVWXYZ';
let n = 0;
export function nodeId() {
    n += 1;
    let s = '';
    let v = n * 2654435761 + 7919;
    for (let i = 0; i < 26; i++) { s += ALPHA[Math.abs(v + i * 31 + (i * i) % 17) % 32]; v = Math.floor(v / 3) + i * 977 + n; }
    return s.slice(0, 26);
}
const text = (t, marks) => (marks ? { type: 'text', text: t, marks } : { type: 'text', text: t });
const inl = (x) => (typeof x === 'string' ? text(x) : x);
export const b = (t) => text(t, [{ type: 'bold' }]);
export const i = (t) => text(t, [{ type: 'italic' }]);
export const c = (t) => text(t, [{ type: 'code' }]);
// target: 'https://…' external; '/path/' a path on this site; 'page:key', 'doc:key', 'post:key' an item of this site by key (resolved to its ID by the seed)
export const a = (t, target, bold = false) => text(t, [...(bold ? [{ type: 'bold' }] : []), { type: 'link', attrs: { href: target.includes(':') && !target.startsWith('http') ? `teme:seed/${target}` : target } }]);
export const p = (...parts) => ({ type: 'paragraph', content: parts.flat().map(inl) });
export const h = (level, ...parts) => ({ type: 'heading', attrs: { level }, content: parts.flat().map(inl) });
export const h2 = (...x) => h(2, ...x);
export const h3 = (...x) => h(3, ...x);
export const kicker = (t) => p(b(t));
export const cta = (t, target) => p(a(t, target, true));
const li = (item) => ({ type: 'listItem', content: [Array.isArray(item) ? p(...item) : item.type ? item : p(item)] });
export const ul = (...items) => ({ type: 'bulletList', content: items.map(li) });
export const ol = (...items) => ({ type: 'orderedList', attrs: { start: 1 }, content: items.map(li) });
export const hr = () => ({ type: 'horizontalRule' });
export const callout = (tone, t) => ({ type: 'callout', attrs: { id: nodeId(), tone, text: t } });
// style: 'primary' | 'secondary'; target as a() or an https address
export const button = (label, target, style = 'primary') => ({ type: 'button', attrs: { id: nodeId(), label, style, ...(target.startsWith('https://') ? { url: target } : { contentId: `teme:seed/${target}` }) } });
export const snippet = (language, code, caption = null) => ({ type: 'snippet', attrs: { id: nodeId(), language, code, caption } });
// mediaKey names a file in seed/media (without .png); alt and caption are this use's own
export const image = (mediaKey, { alt, caption = null, width = 'column' } = {}) => ({ type: 'image', attrs: { id: nodeId(), mediaId: `seed:${mediaKey}`, alt, decorative: false, caption, credit: null, width } });
const cell = (kind, t) => ({ type: kind, attrs: { colspan: 1, rowspan: 1 }, content: [p(...(Array.isArray(t) ? t : [t]))] });
export const table = (headers, rows) => ({ type: 'table', content: [
    { type: 'tableRow', content: headers.map((t) => cell('tableHeader', t)) },
    ...rows.map((r) => ({ type: 'tableRow', content: r.map((t) => cell('tableCell', t)) })),
] });
export const doc = (...blocks) => ({ type: 'doc', content: blocks.flat() });
