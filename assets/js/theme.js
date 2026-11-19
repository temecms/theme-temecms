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
    if (!pre || !navigator.clipboard || $('.codeblock', figure)) return; // the docs' code frame has its own Copy
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

// ---------------------------------------------------------------------------------------------------------------
// The directory, Submit, Showcase and the docs. Each part looks for its own markers and does nothing without them.
// ---------------------------------------------------------------------------------------------------------------
const DAY = 86400000;
const todayStart = () => { const d = new Date(); d.setHours(0, 0, 0, 0); return d.getTime(); };
const ago = (iso) => {
    const then = new Date(`${iso}T00:00:00`).getTime();
    if (Number.isNaN(then)) return null;
    const days = Math.round((todayStart() - then) / DAY);
    if (days <= 0) return 'today';
    if (days === 1) return 'yesterday';
    if (days < 14) return `${days} days ago`;
    if (days < 60) return `${Math.round(days / 7)} weeks ago`;
    if (days < 365) return `${Math.round(days / 30)} months ago`;
    return null;
};

// "Updated 2 days ago": a date becomes words for how long ago, keeping the date itself as its title.
$$('time[data-rel]').forEach((t) => {
    const words = ago(t.getAttribute('datetime'));
    if (!words) return;
    if (!t.hasAttribute('data-rel-only')) t.title = t.textContent;
    t.textContent = words;
});

// Copy beside a command or a code block.
const addCopy = (box, source, label) => {
    if (!navigator.clipboard || $('.copy-btn', box)) return;
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'copy-btn';
    btn.setAttribute('aria-label', label);
    btn.innerHTML = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V6a2 2 0 0 0-2-2H6a2 2 0 0 0-2 2v8a2 2 0 0 0 2 2h2"/></svg><span>Copy</span>';
    btn.addEventListener('click', async () => {
        const span = $('span', btn);
        try { await navigator.clipboard.writeText(source.innerText.replace(/\n$/, '')); span.textContent = 'Copied'; } catch { span.textContent = 'Not copied'; }
        setTimeout(() => { span.textContent = 'Copy'; }, 1800);
    });
    return btn;
};
$$('.cmd').forEach((box) => { const b = addCopy(box, $('code', box), 'Copy the command'); if (b) box.append(b); });
$$('.codeblock').forEach((box) => { const b = addCopy(box, $('pre', box), 'Copy the code'); if (b) $('.cb-bar', box).append(b); });

// "/" puts the cursor in a page's search field.
const slashTo = (input) => document.addEventListener('keydown', (e) => {
    if (e.key === '/' && !e.metaKey && !e.ctrlKey && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName) && !document.activeElement.isContentEditable) {
        e.preventDefault();
        input.focus();
    }
});

// The browse page: search, category, works with, sort, chips, page numbers, and the strips on the first view.
$$('[data-dir]').forEach((root) => {
    const perPage = 9;
    const noun = root.dataset.noun;
    const nounOne = root.dataset.nounOne;
    const items = $$('[data-item]', root);
    const results = $('[data-results]', root);
    const input = $('[data-dir-q]', root);
    const catButtons = $$('[data-cat]', root);
    const catSel = $('[data-cat-sel]', root);
    const worksSels = $$('[data-works-sel]', root);
    const sortSel = $('[data-sort-sel]', root);
    const shelves = $('[data-shelves]', root);
    const count = $('[data-count]', root);
    const chips = $('[data-chips]', root);
    const pager = $('[data-pager]', root);
    const empty = $('[data-empty]', root);
    const clear = $('[data-clear]', root);
    const listHead = $('#list-h', root);
    const state = { q: '', cat: 'all', works: 'any', sort: 'updated', page: 1 };
    $('[data-sort-box]', root)?.removeAttribute('hidden');
    $$('[data-see]', root).forEach((b) => { b.hidden = false; });
    const fromHash = decodeURIComponent(location.hash.replace(/^#category=/, ''));
    if (location.hash.startsWith('#category=') && catButtons.some((b) => b.dataset.cat === fromHash)) state.cat = fromHash;
    const catName = (key) => catButtons.find((b) => b.dataset.cat === key)?.querySelector('span')?.textContent ?? key;
    const cmp = {
        updated: (a, b) => (b.dataset.updated || '').localeCompare(a.dataset.updated || '') || a.dataset.name.localeCompare(b.dataset.name),
        newest: (a, b) => (b.dataset.listed || '').localeCompare(a.dataset.listed || '') || a.dataset.name.localeCompare(b.dataset.name),
        name: (a, b) => a.dataset.name.localeCompare(b.dataset.name),
    };
    const icon = '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>';
    const chip = (words, reset) => {
        const b = document.createElement('button');
        b.type = 'button';
        b.className = 'fchip';
        b.innerHTML = `<span></span>${icon}`;
        b.firstChild.textContent = words;
        b.setAttribute('aria-label', `Remove the filter: ${words}`);
        b.addEventListener('click', () => { reset(); run(); });
        return b;
    };
    const run = (scroll = false) => {
        const q = state.q.trim().toLowerCase();
        const shown = items.filter((el) => {
            if (state.cat !== 'all' && !(el.dataset.cats || '').split(' ').includes(state.cat)) return false;
            if (state.works !== 'any' && !(el.dataset.works || '').split(' ').includes(state.works)) return false;
            return !q || el.textContent.toLowerCase().includes(q);
        }).sort(cmp[state.sort]);
        const pages = Math.max(1, Math.ceil(shown.length / perPage));
        state.page = Math.min(state.page, pages);
        const from = (state.page - 1) * perPage;
        items.forEach((el) => { el.hidden = true; });
        shown.forEach((el, i) => { results.append(el); el.hidden = i < from || i >= from + perPage; });
        count.textContent = `${shown.length} ${shown.length === 1 ? nounOne : noun}`;
        const filtered = q || state.cat !== 'all' || state.works !== 'any';
        if (shelves) shelves.hidden = Boolean(filtered) || state.sort !== 'updated' || state.page > 1;
        if (listHead) listHead.textContent = state.cat === 'all' ? `All ${noun}` : catName(state.cat);
        chips.replaceChildren();
        if (state.q.trim()) chips.append(chip(`“${state.q.trim()}”`, () => { state.q = ''; input.value = ''; }));
        if (state.cat !== 'all') chips.append(chip(catName(state.cat), () => { state.cat = 'all'; }));
        if (state.works !== 'any') chips.append(chip(`Works with ${state.works}`, () => { state.works = 'any'; }));
        catButtons.forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.cat === state.cat)));
        if (catSel) catSel.value = state.cat;
        worksSels.forEach((s) => { s.value = state.works; });
        if (sortSel) sortSel.value = state.sort;
        empty.hidden = shown.length > 0;
        if (clear) clear.hidden = !filtered;
        pager.replaceChildren();
        pager.hidden = shown.length === 0;
        if (shown.length) {
            const range = document.createElement('span');
            range.className = 'range num';
            range.textContent = `Showing ${from + 1} to ${Math.min(from + perPage, shown.length)} of ${shown.length}`;
            const nums = document.createElement('div');
            nums.className = 'pages';
            const btn = (label, page, html, current = false) => {
                const b = document.createElement('button');
                b.type = 'button';
                b.setAttribute('aria-label', label);
                if (html) b.innerHTML = html; else b.textContent = String(page);
                if (current) b.setAttribute('aria-current', 'page');
                b.disabled = page < 1 || page > pages;
                b.addEventListener('click', () => { state.page = page; run(true); });
                return b;
            };
            nums.append(btn('Previous page', state.page - 1, '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M19 12H5M11 6l-6 6 6 6"/></svg>'));
            for (let p = 1; p <= pages; p += 1) nums.append(btn(`Page ${p}`, p, null, p === state.page));
            nums.append(btn('Next page', state.page + 1, '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>'));
            pager.append(range, nums);
        }
        if (scroll) (listHead || results).scrollIntoView({ block: 'start' });
    };
    input?.addEventListener('input', () => { state.q = input.value; state.page = 1; run(); });
    catButtons.forEach((b) => b.addEventListener('click', () => { state.cat = b.dataset.cat; state.page = 1; run(); }));
    catSel?.addEventListener('change', () => { state.cat = catSel.value; state.page = 1; run(); });
    worksSels.forEach((s) => s.addEventListener('change', () => { state.works = s.value; state.page = 1; run(); }));
    sortSel?.addEventListener('change', () => { state.sort = sortSel.value; state.page = 1; run(); });
    $$('[data-see]', root).forEach((b) => b.addEventListener('click', () => { state.sort = b.dataset.see; state.page = 1; run(true); }));
    clear?.addEventListener('click', () => { Object.assign(state, { q: '', cat: 'all', works: 'any', page: 1 }); if (input) input.value = ''; run(); });
    if (input) slashTo(input);
    run();
});

// An item's page: Install opens the steps under the header.
const installPanel = $('[data-install-panel]');
if (installPanel) {
    const closeBtn = $('[data-install-close]', installPanel);
    installPanel.hidden = true;
    closeBtn.hidden = false;
    $$('[data-install]').forEach((btn) => {
        btn.setAttribute('aria-expanded', 'false');
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            installPanel.hidden = !installPanel.hidden;
            btn.setAttribute('aria-expanded', String(!installPanel.hidden));
            if (!installPanel.hidden) $('h2', installPanel).setAttribute('tabindex', '-1'), $('h2', installPanel).focus();
        });
    });
    closeBtn.addEventListener('click', () => { installPanel.hidden = true; $$('[data-install]').forEach((b) => { b.setAttribute('aria-expanded', 'false'); b.focus(); }); });
}

// An item's tabs: one panel at a time, arrow keys between tabs, and #changelog (or any panel's name) opens its tab.
$$('[data-tabs]').forEach((list) => {
    const tabs = $$('[role="tab"]', list);
    const panels = tabs.map((t) => document.getElementById(t.getAttribute('aria-controls')));
    list.hidden = false;
    panels.forEach((p, i) => {
        p.setAttribute('role', 'tabpanel');
        p.setAttribute('aria-labelledby', tabs[i].id);
        p.setAttribute('tabindex', '0');
        $('.panel-h', p)?.classList.add('vh');
    });
    const select = (i, focus = false) => {
        tabs.forEach((t, j) => { t.setAttribute('aria-selected', String(i === j)); t.tabIndex = i === j ? 0 : -1; panels[j].hidden = i !== j; });
        if (focus) tabs[i].focus();
    };
    tabs.forEach((t, i) => {
        t.addEventListener('click', () => select(i));
        t.addEventListener('keydown', (e) => {
            const d = { ArrowRight: 1, ArrowLeft: -1 }[e.key];
            if (d) { e.preventDefault(); select((i + d + tabs.length) % tabs.length, true); }
            if (e.key === 'Home') { e.preventDefault(); select(0, true); }
            if (e.key === 'End') { e.preventDefault(); select(tabs.length - 1, true); }
        });
    });
    const wanted = panels.findIndex((p) => `#${p.id.replace(/^p-/, '')}` === location.hash);
    select(wanted >= 0 ? wanted : 0);
});

// A plugin's screenshots: one large at a time, chosen from the thumbnails.
$$('[data-gallery]').forEach((g) => {
    const stages = $$('[data-stage]', g);
    const thumbs = $('[data-thumbs]', g);
    if (!thumbs) return;
    thumbs.hidden = false;
    const btns = $$('[data-shot]', thumbs);
    const show = (i) => { stages.forEach((s, j) => { s.hidden = i !== j; }); btns.forEach((b, j) => b.setAttribute('aria-pressed', String(i === j))); };
    btns.forEach((b, i) => b.addEventListener('click', () => show(i)));
    show(0);
});

// A theme's gallery and its full-screen preview.
$$('[data-theme-gallery]').forEach((g) => {
    const views = $$('[data-view]', g);
    const cap = $('[data-view-cap]', g);
    const btns = $$('[data-view-btn]', g);
    const show = (i) => {
        views.forEach((v, j) => { v.hidden = i !== j; });
        btns.forEach((b, j) => b.setAttribute('aria-pressed', String(i === j)));
        if (cap && btns[i]) cap.textContent = btns[i].dataset.cap;
    };
    btns.forEach((b, i) => b.addEventListener('click', () => show(i)));
    const pv = $('[data-pv]');
    if (!pv || typeof pv.showModal !== 'function') return;
    const frame = $('[data-pv-frame]', pv);
    const imgs = $$('[data-pv-img]', pv);
    const viewSel = $('[data-pv-view]', pv);
    const screens = $$('[data-pv-screen]', pv);
    let phone = false;
    const draw = () => {
        const want = phone ? 'phone' : (viewSel ? viewSel.value : '0');
        imgs.forEach((im) => { im.hidden = im.dataset.pvImg !== want; });
        frame.classList.toggle('phone', phone);
        screens.forEach((s) => s.setAttribute('aria-pressed', String((s.dataset.pvScreen === 'phone') === phone)));
        if (viewSel) viewSel.disabled = phone;
    };
    screens.forEach((s) => s.addEventListener('click', () => { phone = s.dataset.pvScreen === 'phone'; draw(); }));
    viewSel?.addEventListener('change', draw);
    $$('[data-preview]').forEach((btn) => btn.addEventListener('click', (e) => {
        e.preventDefault();
        const current = btns.findIndex((b) => b.getAttribute('aria-pressed') === 'true');
        if (viewSel && current >= 0) viewSel.value = String(current);
        draw();
        pv.showModal();
        $('[data-pv-close]', pv).focus();
    }));
    $('[data-pv-close]', pv).addEventListener('click', () => pv.close());
});

// The author's other listings, read from the directory's own page.
$$('[data-author-box]').forEach(async (box) => {
    const from = box.dataset.from;
    if (!from || !window.fetch || !window.DOMParser) return;
    try {
        const res = await fetch(from, { credentials: 'same-origin' });
        if (!res.ok) return;
        const doc = new DOMParser().parseFromString(await res.text(), 'text/html');
        const self = new URL(box.dataset.self, location.href).pathname;
        const mine = [...doc.querySelectorAll('[data-item]')].filter((el) => el.dataset.author === box.dataset.author && new URL(el.getAttribute('href'), location.href).pathname !== self).slice(0, 4);
        if (!mine.length) return;
        const list = $('[data-more-list]', box);
        mine.forEach((el) => {
            const li = document.createElement('li');
            const a = document.createElement('a');
            a.href = el.getAttribute('href');
            const tile = el.querySelector('.pi');
            if (tile) { const t = document.importNode(tile, true); t.classList.add('sm'); a.append(t); }
            const words = document.createElement('div');
            const b = document.createElement('b');
            b.textContent = el.dataset.name;
            const small = document.createElement('small');
            const v = el.querySelector('.num')?.textContent ?? '';
            small.textContent = v ? `Free · ${v}` : 'Free';
            words.append(b, small);
            a.append(words);
            li.append(a);
            list.append(li);
        });
        $('[data-more]', box).hidden = false;
    } catch { /* the author box reads fine without it */ }
});

// What the site plugin answered a form with: ?submitted=… or ?error=…, shown beside the form.
const answer = new URLSearchParams(location.search);
const showAnswer = (scope) => {
    const done = $('[data-submit-done]', scope);
    const err = $('[data-submit-error]', scope);
    const submitted = answer.get('submitted');
    const error = answer.get('error');
    if (submitted && done) {
        const n = answer.get('number');
        const words = {
            'pull-request': [n ? `Pull request opened: temecms/directory #${n}` : 'Pull request opened on temecms/directory', 'Checks are running. You will get GitHub’s usual emails as the review moves.'],
            'by-hand': ['Thanks. Your submission is in', 'A person adds it to the directory by hand and writes to you if anything is missing.'],
            showcase: ['Thanks. Your site is in', 'A person looks at it before it is listed.'],
        }[submitted] ?? ['Thanks. It is in', ''];
        $('[data-done-title]', done).textContent = words[0];
        $('[data-done-line]', done).textContent = words[1];
        done.hidden = false;
        return true;
    }
    if (error && err) {
        if (error === 'unavailable') {
            err.textContent = 'Submitting from this page isn’t open yet. Use the pull request below on GitHub instead.';
        } else {
            err.textContent = 'Something in the form needs another look.';
            const own = $(`[data-err="${CSS.escape(error)}"]`, scope);
            if (own) { own.hidden = false; const field = document.getElementById(own.id.replace(/-err$/, '')); field?.setAttribute('aria-invalid', 'true'); }
        }
        err.hidden = false;
        return true;
    }
    return false;
};

// Submit: the answer, and the form's own checks before it is sent.
$$('[data-submit-form]').forEach((form) => {
    const scope = form.parentElement;
    if (showAnswer(scope)) ($('[data-submit-done]:not([hidden]), [data-submit-error]:not([hidden])', scope))?.scrollIntoView({ block: 'center' });
    form.noValidate = true;
    form.addEventListener('submit', (e) => {
        let first = null;
        $$('[data-err]', form).forEach((p) => {
            const field = p.dataset.err === 'agree' ? $('#f-agree', form) : document.getElementById(p.id.replace(/-err$/, ''));
            const bad = field && (field.type === 'checkbox' ? !field.checked : (field.required || field.value) && !field.checkValidity());
            const missingCat = p.dataset.err === 'category' && field && field.value === '';
            p.hidden = !(bad || missingCat);
            field?.setAttribute('aria-invalid', String(Boolean(bad || missingCat)));
            if ((bad || missingCat) && !first) first = field;
        });
        if (first) { e.preventDefault(); first.focus(); }
    });
});

// Showcase: filter by kind, and Submit your site in a dialog.
$$('[data-showcase]').forEach((root) => {
    const chipBox = $('[data-kind-chips]', root);
    const chipsK = $$('[data-kind]', chipBox);
    const cards = $$('[data-site]', root);
    const empty = $('[data-show-empty]', root);
    const words = $('[data-empty-words]', root);
    chipBox.hidden = false;
    chipsK.forEach((c) => c.addEventListener('click', () => {
        const k = c.dataset.kind;
        chipsK.forEach((x) => x.setAttribute('aria-pressed', String(x === c)));
        let n = 0;
        cards.forEach((card) => { card.hidden = k !== 'all' && card.dataset.site !== k; if (!card.hidden) n += 1; });
        empty.hidden = n > 0;
        words.textContent = k === 'all' ? words.textContent : words.dataset.template.replace(':kind', c.dataset.kindWords);
    }));
    const formBox = $('[data-dlg]', root);
    if (!formBox) return;
    const dlg = document.createElement('dialog');
    dlg.className = 'dlg';
    dlg.setAttribute('aria-labelledby', 'ss-h');
    formBox.replaceWith(dlg);
    dlg.append(formBox);
    const closeX = $('[data-close]', dlg);
    closeX.hidden = false;
    closeX.addEventListener('click', () => dlg.close());
    $$('[data-open-dlg]', root).forEach((b) => b.addEventListener('click', (e) => { e.preventDefault(); dlg.showModal(); $('input', dlg)?.focus(); }));
    if (showAnswer(dlg)) dlg.showModal();
});

// The docs: the tree as a sheet on phones, the version switcher, "Was this page helpful?", and search (Ctrl K, ⌘ K or /).
const docsTree = $('[data-docs-tree]');
if (docsTree) {
    const open = $('[data-tree-open]');
    if (open) {
        open.hidden = false;
        const setOpen = (on) => { docsTree.dataset.open = String(on); open.setAttribute('aria-expanded', String(on)); document.body.style.overflow = on ? 'hidden' : ''; };
        open.addEventListener('click', () => { setOpen(true); $('[data-tree-close]', docsTree).focus(); });
        $('[data-tree-close]', docsTree).addEventListener('click', () => { setOpen(false); open.focus(); });
        document.addEventListener('keydown', (e) => { if (e.key === 'Escape' && docsTree.dataset.open === 'true') { setOpen(false); open.focus(); } });
    }
    const ver = $('[data-docs-ver]', docsTree);
    ver?.addEventListener('change', () => { location.href = ver.value; });
    if (window.matchMedia('(max-width: 1100px)').matches) $$('.tree-group', docsTree).forEach((d) => { if (!$('[aria-current="page"]', d)) d.open = false; });
}
$$('[data-helpful]').forEach((box) => {
    box.hidden = false;
    const thanks = $('[data-thanks]', box);
    $$('[data-answer]', box).forEach((b) => b.addEventListener('click', () => {
        $$('[data-answer]', box).forEach((x) => x.setAttribute('aria-pressed', String(x === b)));
        const link = $('[data-no-link]', thanks);
        thanks.firstChild?.nodeType === 3 && thanks.firstChild.remove();
        thanks.prepend(document.createTextNode(`${b.dataset.answer === 'yes' ? thanks.dataset.yes : thanks.dataset.no} `));
        if (link) link.hidden = b.dataset.answer !== 'no';
        thanks.hidden = false;
    }));
});
const sov = $('[data-sov]');
if (sov) {
    const q = $('[data-sov-q]', sov);
    const res = $('[data-sov-res]', sov);
    const opener = $('[data-open-search]');
    const isMac = /Mac|iPhone|iPad/.test(navigator.platform || navigator.userAgent);
    // One entry per page, whether its address came written in full or as a path; a fuller one keeps its summary and group.
    const pathOf = (url) => { try { return new URL(url, location.href).pathname; } catch { return url; } };
    const byPath = new Map();
    const add = (e) => { const key = pathOf(e.url); const had = byPath.get(key); if (had) { had.summary ||= e.summary; had.group ||= e.group; } else { byPath.set(key, e); index.push(e); } };
    let index = [];
    try { JSON.parse(sov.dataset.index || '[]').forEach(add); } catch { index = []; }
    let fuller = false;
    const loadFuller = async () => {
        if (fuller || !sov.dataset.src || !window.fetch) return;
        fuller = true;
        try {
            const r = await fetch(sov.dataset.src, { credentials: 'same-origin' });
            const other = new DOMParser().parseFromString(await r.text(), 'text/html').querySelector('[data-sov]');
            const more = JSON.parse(other?.dataset.index || '[]');
            more.forEach(add);
            if (!sov.hidden) draw();
        } catch { /* the tree's own list is enough */ }
    };
    let active = 0;
    const esc = (s) => s.replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const mark = (text, term) => {
        const safe = esc(text);
        if (!term) return safe;
        const at = text.toLowerCase().indexOf(term);
        return at < 0 ? safe : `${esc(text.slice(0, at))}<mark>${esc(text.slice(at, at + term.length))}</mark>${esc(text.slice(at + term.length))}`;
    };
    const draw = () => {
        const term = q.value.trim().toLowerCase();
        const hits = [];
        index.forEach((e) => {
            if (!term || e.title.toLowerCase().includes(term) || (e.summary || '').toLowerCase().includes(term)) hits.push({ title: e.title, url: e.url, line: e.summary || '', group: e.group });
            (e.headings || []).forEach((h) => { if (term && h.text.toLowerCase().includes(term)) hits.push({ title: h.text, url: `${e.url}#${h.id}`, line: e.title, group: e.group }); });
        });
        res.replaceChildren();
        if (!hits.length) {
            const p = document.createElement('div');
            p.className = 'none';
            p.textContent = res.dataset.none.replace(':q', q.value.trim());
            if (sov.dataset.siteSearchUrl) {
                const a = document.createElement('a');
                a.href = sov.dataset.siteSearchUrl + encodeURIComponent(q.value.trim());
                a.textContent = res.dataset.siteSearch;
                a.setAttribute('role', 'option');
                p.append(document.createElement('br'), a);
            }
            res.append(p);
            return;
        }
        const groups = new Map();
        hits.slice(0, 40).forEach((h) => { if (!groups.has(h.group)) groups.set(h.group, []); groups.get(h.group).push(h); });
        let n = 0;
        groups.forEach((list, name) => {
            const h = document.createElement('h3');
            h.textContent = name;
            h.setAttribute('role', 'presentation');
            res.append(h);
            list.forEach((hit) => {
                const a = document.createElement('a');
                a.href = hit.url;
                a.id = `sov-${n}`;
                a.setAttribute('role', 'option');
                a.setAttribute('aria-selected', String(n === active));
                a.innerHTML = `<b>${mark(hit.title, term)}</b>${hit.line ? `<small>${mark(hit.line, term)}</small>` : ''}`;
                res.append(a);
                n += 1;
            });
        });
        q.setAttribute('aria-activedescendant', `sov-${active}`);
    };
    let back = null;
    const openSearch = () => {
        back = document.activeElement;
        sov.hidden = false;
        sov.classList.add('open');
        document.body.style.overflow = 'hidden';
        active = 0;
        draw();
        q.focus();
        loadFuller();
    };
    const closeSearch = () => { sov.hidden = true; sov.classList.remove('open'); document.body.style.overflow = ''; back?.focus(); };
    if (opener) {
        opener.hidden = false;
        if (isMac) { const m = $('[data-mod]', opener); if (m) m.textContent = '⌘'; opener.setAttribute('aria-label', 'Search the docs (⌘ K)'); }
        opener.addEventListener('click', openSearch);
    }
    q.addEventListener('input', () => { active = 0; draw(); });
    q.addEventListener('keydown', (e) => {
        const opts = $$('[role="option"]', res);
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
            e.preventDefault();
            active = (active + (e.key === 'ArrowDown' ? 1 : -1) + opts.length) % Math.max(opts.length, 1);
            opts.forEach((o, i) => o.setAttribute('aria-selected', String(i === active)));
            opts[active]?.scrollIntoView({ block: 'nearest' });
            q.setAttribute('aria-activedescendant', opts[active]?.id ?? '');
        }
        if (e.key === 'Enter' && opts[active]) { e.preventDefault(); location.href = opts[active].href; }
    });
    $('[data-sov-close]', sov).addEventListener('click', closeSearch);
    sov.addEventListener('click', (e) => { if (e.target === sov) closeSearch(); });
    document.addEventListener('keydown', (e) => {
        if ((e.key === 'k' || e.key === 'K') && (e.metaKey || e.ctrlKey)) { e.preventDefault(); if (sov.hidden) openSearch(); else closeSearch(); }
        else if (e.key === '/' && sov.hidden && !/INPUT|TEXTAREA|SELECT/.test(document.activeElement.tagName)) { e.preventDefault(); openSearch(); }
        else if (e.key === 'Escape' && !sov.hidden) closeSearch();
    });
}
