// Full-page screenshots of every page of the site loaded with the temecms-site plugin's content, at 1280 and 375 wide, into docs/screenshots.
// Run against that site:  BASE=http://localhost:8000 node tests/screenshots.mjs [name ...]   (BASE defaults to http://localhost:8000)
import { chromium } from 'playwright-core';
import path from 'node:path';
import fs from 'node:fs';
import { pathToFileURL } from 'node:url';

export const BASE = process.env.BASE ?? 'http://localhost:8000';
export const PAGES = {
    home: '/',
    docs: '/docs/',
    'docs-article': '/docs/install/',
    plugins: '/plugins/',
    plugin: '/plugins/review-notifier/',
    'plugins-submit': '/publish-a-plugin/',
    themes: '/theme-directory/',
    theme: '/theme-directory/teme-default/',
    migration: '/moving-from-wordpress/',
    hosting: '/hosting/',
    developers: '/developers/',
    faq: '/faq/',
    roadmap: '/roadmap/',
    blog: '/blog/',
    'blog-post': '/teme-0-7-0-alpha-1-bylines/',
};
const out = path.join(import.meta.dirname, '..', 'docs', 'screenshots');
if (import.meta.url === pathToFileURL(process.argv[1]).href) {
    fs.mkdirSync(out, { recursive: true });
    const want = process.argv.slice(2);
    const browser = await chromium.launch({ channel: 'chrome' });
    for (const [name, url] of Object.entries(PAGES)) {
        if (want.length && !want.includes(name)) continue;
        for (const [w, h] of [[1280, 900], [375, 800]]) {
            const page = await browser.newPage({ viewport: { width: w, height: h } });
            const res = await page.goto(BASE + url, { waitUntil: 'networkidle' });
            await page.evaluate(async () => { for (let y = 0; y < document.body.scrollHeight; y += 500) { window.scrollTo(0, y); await new Promise((r) => setTimeout(r, 50)); } window.scrollTo(0, 0); });
            await page.waitForLoadState('networkidle');
            await page.screenshot({ path: path.join(out, `${name}-${w}.png`), fullPage: true });
            console.log(name, w, res?.status());
            await page.close();
        }
    }
    await browser.close();
}
