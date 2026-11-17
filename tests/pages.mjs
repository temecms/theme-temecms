// Browser checks of every page of the seeded site, at 1280 and 375 wide: it answers 200, it has one h1,
// headings are in order, every picture has alt text and a size, nothing scrolls sideways, and axe finds no violation.
// Run against a seeded site:  BASE=http://localhost:8000 node tests/pages.mjs      (exit code 1 on any failure)
import { chromium } from 'playwright-core';
import AxeBuilder from '@axe-core/playwright';
import fs from 'node:fs';
import path from 'node:path';
import { PAGES, BASE } from './screenshots.mjs';

const outDir = path.join(import.meta.dirname, '.output');
fs.mkdirSync(outDir, { recursive: true });
const browser = await chromium.launch({ channel: 'chrome' });
const results = [];
let failures = 0;
for (const [name, url] of Object.entries(PAGES)) {
    for (const [w, h] of [[1280, 900], [375, 800]]) {
        const context = await browser.newContext({ viewport: { width: w, height: h } });
        const page = await context.newPage();
        const res = await page.goto(BASE + url, { waitUntil: 'networkidle' });
        const facts = await page.evaluate(() => {
            const heads = [...document.querySelectorAll('h1,h2,h3,h4,h5,h6')].map((x) => Number(x.tagName[1]));
            let skipped = false;
            heads.forEach((lvl, i) => { if (i > 0 && lvl > heads[i - 1] + 1) skipped = true; });
            return {
                h1: document.querySelectorAll('h1').length,
                main: document.querySelectorAll('main').length,
                skipped,
                imgsWithoutAlt: [...document.querySelectorAll('img')].filter((i) => !i.hasAttribute('alt')).length,
                imgsWithoutSize: [...document.querySelectorAll('img')].filter((i) => !i.getAttribute('width') || !i.getAttribute('height')).length,
                sideways: document.documentElement.scrollWidth > window.innerWidth + 1,
                lang: document.documentElement.lang,
            };
        });
        const axe = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'best-practice']).analyze();
        const problems = [];
        if (res?.status() !== 200) problems.push(`status ${res?.status()}`);
        if (facts.h1 !== 1) problems.push(`h1 x${facts.h1}`);
        if (facts.main !== 1) problems.push(`main x${facts.main}`);
        if (facts.skipped) problems.push('a heading level is skipped');
        if (facts.imgsWithoutAlt) problems.push(`${facts.imgsWithoutAlt} img without alt`);
        if (facts.imgsWithoutSize) problems.push(`${facts.imgsWithoutSize} img without width/height`);
        if (facts.sideways) problems.push('scrolls sideways');
        for (const v of axe.violations) problems.push(`axe ${v.id} (${v.impact}): ${v.nodes.length} node(s), e.g. ${v.nodes[0]?.target?.join(' ')}`);
        if (problems.length) failures += 1;
        results.push({ page: name, width: w, status: res?.status(), problems });
        console.log(`${problems.length ? 'FAIL' : 'ok  '} ${name} ${w}${problems.length ? ' :: ' + problems.join(' | ') : ''}`);
        await context.close();
    }
}
await browser.close();
fs.writeFileSync(path.join(outDir, 'report.json'), JSON.stringify(results, null, 2));
console.log(`${results.length - failures} of ${results.length} checks clean`);
process.exit(failures ? 1 : 0);
