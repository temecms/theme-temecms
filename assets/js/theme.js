// TemeCMS: a small enhancement script. Nothing here is needed to read the site: every page works without it.
const $ = (sel, root = document) => root.querySelector(sel);
const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];

// The guide's tree is open beside a wide article and closed above a narrow one.
const tree = $('.tree-details');
if (tree && window.matchMedia('(max-width: 960px)').matches) {
    tree.open = false;
}

// Filtering a list as you type, and by category (the documentation and the plugin directory).
$$('[data-filter]').forEach((scope) => {
    const input = $('[data-filter-input]', scope);
    const chips = $$('[data-filter-cat]', scope);
    const items = $$('[data-item]', scope);
    const empty = $('[data-filter-empty]', scope);
    const count = $('[data-filter-count]', scope);
    const chipBox = $('[data-chips]', scope);
    let cat = 'all';
    if (chipBox) chipBox.hidden = false;
    const run = () => {
        const q = (input ? input.value : '').trim().toLowerCase();
        let shown = 0;
        items.forEach((item) => {
            const inCat = cat === 'all' || (item.dataset.cat || '').split(' ').includes(cat);
            const ok = inCat && (!q || item.textContent.toLowerCase().includes(q));
            item.hidden = !ok;
            if (ok) shown += 1;
        });
        $$('[data-group]', scope).forEach((g) => {
            const own = $$('[data-item]', g);
            if (own.length) g.hidden = !own.some((i) => !i.hidden);
        });
        if (empty) empty.hidden = shown !== 0;
        if (count) count.textContent = `${shown} shown`;
    };
    if (input) {
        input.addEventListener('input', run);
        // The search field here only filters the list on this page; it is not a form to send.
        const form = input.closest('form');
        if (form) form.addEventListener('submit', (e) => e.preventDefault());
    }
    chips.forEach((chip) => chip.addEventListener('click', () => {
        cat = chip.dataset.filterCat;
        chips.forEach((c) => c.setAttribute('aria-pressed', String(c === chip)));
        run();
    }));
    // "/" jumps to the search field, as its placeholder says.
    document.addEventListener('keydown', (e) => {
        if (e.key === '/' && input && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) {
            e.preventDefault();
            input.focus();
        }
    });
});

// "On this page": the heading being read is marked.
const links = $$('.otp a[href^="#"]');
if (links.length) {
    const targets = links.map((a) => document.getElementById(a.getAttribute('href').slice(1))).filter(Boolean);
    const mark = () => {
        let current = targets[0];
        targets.forEach((t) => { if (t.getBoundingClientRect().top < 160) current = t; });
        links.forEach((a) => {
            if (a.getAttribute('href') === `#${current?.id}`) a.setAttribute('aria-current', 'location');
            else a.removeAttribute('aria-current');
        });
    };
    addEventListener('scroll', mark, { passive: true });
    mark();
}

// Open all / Close all on the questions page.
const faq = $('.faq-tools');
if (faq) {
    faq.hidden = false;
    $$('[data-faq]', faq).forEach((b) => b.addEventListener('click', () => $$('.faq details').forEach((d) => { d.open = b.dataset.faq === 'open'; })));
}

// Copy on a code block.
$$('.card-snippet').forEach((figure) => {
    const pre = $('pre', figure);
    if (!pre || !navigator.clipboard) return;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'copy-btn';
    btn.textContent = 'Copy';
    btn.setAttribute('aria-label', 'Copy the code');
    btn.addEventListener('click', async () => {
        try { await navigator.clipboard.writeText(pre.innerText.replace(/\n$/, '')); btn.textContent = 'Copied'; } catch { btn.textContent = 'Not copied'; }
        setTimeout(() => { btn.textContent = 'Copy'; }, 1800);
    });
    figure.append(btn);
});
