// Browser checks of the directory's and the docs' interactive states, light and dark, at 1280 and 375 wide: the docs search open with
// results, the docs menu on a phone, a filtered list, a plugin's Changelog tab and install steps, the theme preview, the Submit page's
// answers and its form's own errors, and the Showcase's site form. In each state axe finds no violation, the script throws nothing,
// and nothing scrolls sideways. Run against a site loaded with the temecms-site plugin and its directory:
//   BASE=http://localhost:8000 node tests/states.mjs [state ...]      (PLUGIN, THEME and SUBMIT name the pages; exit code 1 on any failure)
import { chromium } from 'playwright-core';
import AxeBuilder from '@axe-core/playwright';
import { BASE } from './screenshots.mjs';

const PLUGIN = process.env.PLUGIN ?? '/plugins/teme-seo/';
const THEME = process.env.THEME ?? '/theme-directory/theme-ledger/';
const SUBMIT = process.env.SUBMIT ?? '/submit/';
const STATES = {
    'docs-search': ['/docs/', async (page) => { await page.keyboard.press('Control+k'); await page.keyboard.type('back'); await page.waitForTimeout(300); }],
    'docs-menu': ['/docs/', async (page, w) => { if (w < 900) { await page.click('[data-tree-open]'); await page.waitForTimeout(200); } }],
    'plugins-filtered': ['/plugins/', async (page) => { await page.fill('#dir-q', 'seo'); await page.waitForTimeout(200); }],
    'plugin-changelog': [PLUGIN + '#changelog', async (page) => { await page.waitForTimeout(200); }],
    'plugin-install': [PLUGIN, async (page) => { await page.click('[aria-controls="install-panel"]'); await page.waitForTimeout(200); }],
    'theme-preview': [THEME, async (page) => { await page.click('[data-preview]'); await page.waitForTimeout(300); }],
    'submit-sent': [SUBMIT + '?submitted=pull-request&number=12', async () => {}],
    'submit-refused': [SUBMIT + '?error=repository', async () => {}],
    'submit-empty': [SUBMIT, async (page) => { await page.click('form[data-submit-form] button[type="submit"]'); await page.waitForTimeout(200); }],
    'showcase-form': ['/showcase/', async (page) => { await page.click('[data-open-dlg]'); await page.waitForTimeout(300); }],
};

const want = process.argv.slice(2);
const browser = await chromium.launch(process.env.CHROME ? { executablePath: process.env.CHROME } : { channel: 'chrome' });
let failures = 0;
let total = 0;
for (const [name, [url, act]] of Object.entries(STATES)) {
    if (want.length && !want.includes(name)) continue;
    for (const [w, h, scheme] of [[1280, 900, 'light'], [375, 800, 'light'], [1280, 900, 'dark'], [375, 800, 'dark']]) {
        const context = await browser.newContext({ viewport: { width: w, height: h }, colorScheme: scheme });
        const page = await context.newPage();
        const errors = [];
        page.on('pageerror', (e) => errors.push(e.message));
        await page.goto(BASE + url, { waitUntil: 'networkidle' });
        const problems = [];
        try { await act(page, w); } catch (e) { problems.push(`could not act: ${e.message.split('\n')[0]}`); }
        const sideways = await page.evaluate(() => document.documentElement.scrollWidth > window.innerWidth + 1);
        const axe = await new AxeBuilder({ page }).withTags(['wcag2a', 'wcag2aa', 'wcag21a', 'wcag21aa', 'best-practice']).analyze();
        if (sideways) problems.push('scrolls sideways');
        for (const m of errors) problems.push(`script error: ${m}`);
        for (const v of axe.violations) problems.push(`axe ${v.id} (${v.impact}): ${v.nodes.length} node(s), e.g. ${v.nodes[0]?.target?.join(' ')}`);
        total += 1;
        if (problems.length) failures += 1;
        console.log(`${problems.length ? 'FAIL' : 'ok  '} ${name} ${w} ${scheme}${problems.length ? ' :: ' + problems.join(' | ') : ''}`);
        await context.close();
    }
}
await browser.close();
console.log(`${total - failures} of ${total} checks clean`);
process.exit(failures ? 1 : 0);
