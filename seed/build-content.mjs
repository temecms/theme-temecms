// Gathers the content modules into content.json, which seed.php reads. Run: node seed/build-content.mjs
import fs from 'node:fs';
import path from 'node:path';

const dir = path.join(import.meta.dirname, 'content');
const load = async (f) => (fs.existsSync(path.join(dir, f)) ? (await import(`./content/${f}`)).default : null);
const out = {
    media: await load('media.mjs'),
    pages: (await load('pages.mjs')) ?? [],
    posts: (await load('posts.mjs')) ?? [],
    docs: (await load('docs.mjs')) ?? [],
    plugins: (await load('plugins.mjs')) ?? [],
    themes: (await load('themes.mjs')) ?? [],
    regions: (await load('regions.mjs')) ?? {},
};
const extra = { home: { emphasis: 'create.' }, migration: { kicker: 'Moving from WordPress' }, hosting: { kicker: 'Hosting', emphasis: 'Your site.' }, developers: { kicker: 'Developers', emphasis: 'PostgreSQL.' }, faq: { kicker: 'FAQ' }, roadmap: { kicker: 'Roadmap' }, 'publish-a-plugin': { kicker: 'Publish a plugin' }, blog: { kicker: 'Blog' } };
for (const pg of out.pages) { pg.fields = { ...(pg.fields ?? {}), ...(extra[pg.key] ?? {}) }; }
const counters = {};
for (const d of out.docs) { const g = d.fields.guide; counters[g] = (counters[g] ?? 0) + 1; d.fields.order = counters[g]; }
for (const t of out.themes) { if (t.key === 'teme-default') { t.fields.featured_image = { id: 'seed:site-card', alt: 'A front page in the Teme Default theme: a lead story with its picture, then a clean feed of stories.', decorative: false }; } }
fs.writeFileSync(path.join(import.meta.dirname, 'content.json'), JSON.stringify(out, null, 1));
console.log(Object.entries(out).map(([k, v]) => `${k}:${Array.isArray(v) ? v.length : Object.keys(v).length}`).join(' '));
