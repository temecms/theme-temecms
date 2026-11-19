{{--
    The directory's listings as plain arrays, read from the temecms-site plugin's fields on the plugin and theme types
    (and the showcase type), so every directory template draws the same facts the same way. Included once at the top
    of a template; it defines functions and draws nothing.
--}}
@php
    if (! function_exists('temecms_field')) {
        /** One field's plain value, or null when the type has no such field or it is empty. */
        function temecms_field(object $item, string $handle): mixed
        {
            $view = $item->field($handle);
            if ($view === null || ($view->empty ?? false)) {
                return null;
            }

            return $view->value ?? null;
        }

        /** A text field's words, trimmed; '' when empty or missing. */
        function temecms_text(object $item, string $handle): string
        {
            $value = temecms_field($item, $handle);

            return is_scalar($value) ? trim((string) $value) : '';
        }

        /** A field holding a JSON array (releases, capabilities, screenshots): its entries, each an array; [] when empty or unreadable. */
        function temecms_json_list(object $item, string $handle): array
        {
            $raw = temecms_text($item, $handle);
            if ($raw === '') {
                return [];
            }
            $decoded = json_decode($raw, true);

            return is_array($decoded) ? array_values(array_filter($decoded, 'is_array')) : [];
        }

        /** A comma-separated text field as a list of its non-empty parts. */
        function temecms_csv(object $item, string $handle): array
        {
            return array_values(array_filter(array_map('trim', explode(',', temecms_text($item, $handle))), fn ($part) => $part !== ''));
        }

        /** Teme versions, newest first. */
        function temecms_versions_desc(array $versions): array
        {
            $versions = array_values(array_unique($versions));
            usort($versions, fn ($a, $b) => version_compare((string) $b, (string) $a));

            return $versions;
        }

        /** A colour for a listing's icon tile, steady for its name. */
        function temecms_hue(string $name): int
        {
            return crc32($name) % 360;
        }

        /** Words for a category key when no term name is known: "developer-tools" reads "Developer tools". */
        /** The directory's category keys and their words: the drawing's list for plugins, and the kinds of site for themes. */
        function temecms_category_words(): array
        {
            return [
                'seo' => __('SEO'), 'editing' => __('Editing'), 'content' => __('Content'), 'social' => __('Social'), 'forms' => __('Forms'),
                'analytics' => __('Analytics'), 'advertising' => __('Advertising'), 'security' => __('Security'), 'scheduling' => __('Scheduling'),
                'navigation' => __('Navigation'), 'media' => __('Media'), 'performance' => __('Performance'), 'languages' => __('Languages'),
                'developer' => __('Developer tools'), 'news' => __('News'), 'blog' => __('Blog'), 'documentation' => __('Documentation'),
                'magazine' => __('Magazine'),
            ];
        }

        /** A category key's words: the known list's, else the key itself, capitalised. */
        function temecms_key_words(string $key): string
        {
            return temecms_category_words()[$key] ?? ucfirst(str_replace(['-', '_'], ' ', $key));
        }

        /** A date field as an ISO date, or null. */
        function temecms_date(object $item, string $handle): ?DateTimeImmutable
        {
            $value = temecms_field($item, $handle);

            return $value instanceof DateTimeImmutable ? $value : null;
        }

        /**
         * One listing as the directory's pages draw it: names, facts, links, and the lists read from JSON.
         *
         * @return array<string, mixed>
         */
        function temecms_listing(object $item): array
        {
            $summary = temecms_text($item, 'summary');
            if ($summary === '') {
                $summary = trim((string) ($item->standfirst ?? ''));
            }
            $categories = array_values(array_unique(array_map('strtolower', temecms_csv($item, 'categories'))));
            $kicker = $item->kicker ?? null;
            $labels = [];
            foreach ($categories as $i => $key) {
                // A key the directory knows keeps its words; another takes the term's name, which the site may have written.
                $labels[$key] = ($i === 0 && $kicker !== null && ! isset(temecms_category_words()[$key])) ? (string) $kicker->name : temecms_key_words($key);
            }
            if ($categories === [] && $kicker !== null) {
                $key = strtolower((string) basename(rtrim((string) $kicker->url, '/')));
                $categories = [$key];
                $labels[$key] = isset(temecms_category_words()[$key]) ? temecms_key_words($key) : (string) $kicker->name;
            }
            $works = temecms_versions_desc(temecms_csv($item, 'works_with'));
            $releases = temecms_json_list($item, 'releases');
            $shown = array_values(array_filter($releases, fn ($r) => ! ($r['held'] ?? false)));
            $iconView = $item->field('icon_image');
            $package = temecms_text($item, 'package');
            $github = temecms_text($item, 'author_github');
            $source = temecms_text($item, 'link_source');

            return [
                'title' => (string) $item->title,
                'url' => (string) $item->url,
                'type' => (string) ($item->type ?? ''),
                'summary' => $summary,
                'package' => $package,
                'author' => temecms_text($item, 'author_name'),
                'github' => $github,
                'authorUrl' => $github !== '' ? 'https://github.com/'.rawurlencode($github) : '',
                'version' => temecms_text($item, 'latest_version'),
                'works' => $works,
                'updated' => temecms_date($item, 'updated_on'),
                'listed' => temecms_date($item, 'listed_on'),
                'stars' => temecms_field($item, 'stars'),
                'issues' => temecms_field($item, 'open_issues'),
                'picked' => (bool) temecms_field($item, 'picked'),
                'categories' => $categories,
                'categoryLabels' => $labels,
                'primaryUrl' => $kicker?->url,
                'iconImage' => $iconView?->image,
                'glyph' => temecms_text($item, 'icon'),
                'hue' => temecms_hue($package !== '' ? $package : (string) $item->title),
                'links' => [
                    'source' => $source,
                    'issues' => temecms_text($item, 'link_issues'),
                    'docs' => temecms_text($item, 'link_docs'),
                    'support' => temecms_text($item, 'link_support'),
                ],
                'releases' => $shown,
                'capabilities' => temecms_json_list($item, 'capabilities'),
                'screenshots' => array_values(array_filter(temecms_json_list($item, 'screenshots'), fn ($s) => is_string($s['media'] ?? null) && $s['media'] !== '')),
                'changelog' => temecms_text($item, 'changelog'),
                'image' => $item->image ?? ($item->lead ?? null),
                'report' => 'https://github.com/temecms/directory/issues/new?title='.rawurlencode('Report: '.($package !== '' ? $package : (string) $item->title)),
            ];
        }

        /**
         * A guide's body as the docs draw it: headings with ids and # anchors (none for a README), code in a block with its language and
         * room for Copy, the callouts with an icon and their word, tables in a scroll wrapper. Returns the HTML and
         * the h2 headings for "On this page".
         *
         * @return array{html: string, toc: list<array{id: string, text: string}>}
         */
        function temecms_doc_prose(string $html, bool $anchors = true): array
        {
            if (trim($html) === '') {
                return ['html' => '', 'toc' => []];
            }
            $svg = fn (string $d) => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'.$d.'</svg>';
            $tones = [
                'note' => [__('Note'), $svg('<circle cx="12" cy="12" r="9"/><path d="M12 11v5M12 8h.01"/>')],
                'tip' => [__('Tip'), $svg('<path d="M9 18h6M10 21h4M12 3a6 6 0 0 0-4 10.5c.7.7 1 1.5 1 2.5h6c0-1 .3-1.8 1-2.5A6 6 0 0 0 12 3Z"/>')],
                'warning' => [__('Warning'), $svg('<path d="M12 3 2 20h20Z"/><path d="M12 10v4M12 17h.01"/>')],
                'danger' => [__('Danger'), $svg('<circle cx="12" cy="12" r="9"/><path d="M12 7v6M12 16h.01"/>')],
            ];
            libxml_use_internal_errors(true);
            $dom = new DOMDocument('1.0', 'UTF-8');
            $dom->loadHTML('<?xml encoding="utf-8" ?><body>'.$html.'</body>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
            libxml_clear_errors();
            $fragment = function (string $markup) use ($dom): DOMDocumentFragment {
                $f = $dom->createDocumentFragment();
                $f->appendXML($markup);

                return $f;
            };
            $used = [];
            $toc = [];
            foreach (iterator_to_array($dom->getElementsByTagName('*')) as $el) {
                if (! in_array($el->nodeName, ['h2', 'h3'], true)) {
                    continue;
                }
                $id = $el->getAttribute('id');
                if ($id === '') {
                    $id = trim((string) preg_replace('/[^a-z0-9]+/', '-', strtolower($el->textContent)), '-') ?: 'section';
                }
                $base = $id;
                for ($n = 2; isset($used[$id]); $n++) {
                    $id = $base.'-'.$n;
                }
                $used[$id] = true;
                $el->setAttribute('id', $id);
                $text = trim($el->textContent);
                if ($anchors) {
                    $a = $dom->createElement('a', '#');
                    $a->setAttribute('class', 'anchor');
                    $a->setAttribute('href', '#'.$id);
                    $a->setAttribute('aria-label', __('Link to “:heading”', ['heading' => $text]));
                    $el->insertBefore($a, $el->firstChild);
                }
                if ($el->nodeName === 'h2') {
                    $toc[] = ['id' => $id, 'text' => $text];
                }
            }
            foreach (iterator_to_array($dom->getElementsByTagName('pre')) as $pre) {
                $code = $pre->getElementsByTagName('code')->item(0);
                $lang = '';
                if ($code !== null && preg_match('/(?:^|\s)language-([A-Za-z0-9_+.-]+)/', $code->getAttribute('class'), $m)) {
                    $lang = $m[1];
                }
                $pre->setAttribute('tabindex', '0');
                $box = $dom->createElement('div');
                $box->setAttribute('class', 'codeblock');
                $bar = $dom->createElement('div');
                $bar->setAttribute('class', 'cb-bar');
                $label = $dom->createElement('span', $lang !== '' ? $lang : __('Code'));
                $label->setAttribute('class', 'lang');
                $bar->appendChild($label);
                $pre->parentNode->replaceChild($box, $pre);
                $box->appendChild($bar);
                $box->appendChild($pre);
            }
            foreach (iterator_to_array($dom->getElementsByTagName('div')) as $div) {
                if (! preg_match('/\bcard-callout--(note|tip|warning|danger)\b/', $div->getAttribute('class'), $m)) {
                    continue;
                }
                [$word, $icon] = $tones[$m[1]];
                $div->setAttribute('class', 'callout '.$m[1]);
                $div->setAttribute('role', 'note');
                $inner = $dom->createElement('div');
                while ($div->firstChild !== null) {
                    $inner->appendChild($div->firstChild);
                }
                $inner->insertBefore($fragment('<b>'.htmlspecialchars($word, ENT_XML1).'</b>'), $inner->firstChild);
                $div->appendChild($fragment($icon));
                $div->appendChild($inner);
            }
            foreach (iterator_to_array($dom->getElementsByTagName('table')) as $table) {
                $wrap = $dom->createElement('div');
                $wrap->setAttribute('class', 'table-wrap');
                $wrap->setAttribute('tabindex', '0');
                $wrap->setAttribute('role', 'region');
                $wrap->setAttribute('aria-label', __('Table'));
                $table->parentNode->replaceChild($wrap, $table);
                $wrap->appendChild($table);
            }
            $out = '';
            foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
                $out .= $dom->saveHTML($node);
            }

            return ['html' => $out, 'toc' => $toc];
        }

        /**
         * The documentation's tree from the theme's menus: the "docs" menu, its headings as groups, or else the three
         * guides' menus, one group each. Each group: label, whether it holds the current page, and its items (a link,
         * or a nested group with its own items).
         *
         * @return list<array{label: string, current: bool, items: list<array<string, mixed>>}>
         */
        function temecms_docs_tree(array $navigation, array $guideLabels): array
        {
            $holdsCurrent = function (array $items) use (&$holdsCurrent): bool {
                foreach ($items as $it) {
                    if (($it['current'] ?? false) || $holdsCurrent($it['children'] ?? [])) {
                        return true;
                    }
                }

                return false;
            };
            $groups = [];
            $loose = [];
            foreach ($navigation['docs'] ?? [] as $it) {
                if (($it['children'] ?? []) !== [] || ($it['heading'] ?? false)) {
                    $groups[] = ['label' => (string) $it['label'], 'current' => $holdsCurrent($it['children'] ?? []), 'items' => $it['children'] ?? []];
                } else {
                    $loose[] = $it;
                }
            }
            if ($loose !== []) {
                array_unshift($groups, ['label' => __('Start'), 'current' => $holdsCurrent($loose), 'items' => $loose]);
            }
            if ($groups === []) {
                // No tree menu: the guides' own menus, each top item with pages under it a group, as the drawing's headings.
                foreach ($guideLabels as $slot => $label) {
                    $pages = [];
                    foreach ($navigation[$slot] ?? [] as $it) {
                        if (($it['children'] ?? []) !== []) {
                            $groups[] = ['label' => (string) $it['label'], 'current' => $holdsCurrent($it['children']), 'items' => $it['children']];
                        } else {
                            $pages[] = $it;
                        }
                    }
                    if ($pages !== []) {
                        $groups[] = ['label' => $label, 'current' => $holdsCurrent($pages), 'items' => $pages];
                    }
                }
            }

            return $groups;
        }

        /** The tree's links in reading order, for previous and next. */
        function temecms_docs_flat(array $groups): array
        {
            $flat = [];
            $walk = function (array $items) use (&$walk, &$flat): void {
                foreach ($items as $it) {
                    if (! ($it['heading'] ?? false) && ($it['url'] ?? null) !== null) {
                        $flat[] = $it;
                    }
                    $walk($it['children'] ?? []);
                }
            };
            foreach ($groups as $g) {
                $walk($g['items']);
            }

            return $flat;
        }

        /**
         * The documentation's versions from the docs_versions setting ("1.2=/docs/, 1.1=/docs/1.1/"), the latest first,
         * with the one this address belongs to (the longest address it starts with) marked.
         *
         * @return list<array{label: string, url: string, current: bool, latest: bool}>
         */
        function temecms_docs_versions(string $setting, string $here): array
        {
            $versions = [];
            foreach (array_filter(array_map('trim', explode(',', $setting))) as $i => $pair) {
                [$label, $url] = array_pad(array_map('trim', explode('=', $pair, 2)), 2, '');
                if ($label !== '' && $url !== '') {
                    $versions[] = ['label' => $label, 'url' => $url, 'current' => false, 'latest' => $versions === []];
                }
            }
            $best = -1;
            $bestLength = -1;
            $path = (string) (parse_url($here, PHP_URL_PATH) ?? $here);
            foreach ($versions as $i => $v) {
                $prefix = (string) (parse_url($v['url'], PHP_URL_PATH) ?? $v['url']);
                if (str_starts_with($path, $prefix) && strlen($prefix) > $bestLength) {
                    $best = $i;
                    $bestLength = strlen($prefix);
                }
            }
            if ($versions !== []) {
                $versions[max($best, 0)]['current'] = true;
            }

            return $versions;
        }

        /**
         * What every docs page's frame needs: the tree's groups and its links in order, the versions and the label,
         * the docs home, the feedback link, and the search's list and where to fetch the fuller one.
         *
         * @return array<string, mixed>
         */
        function temecms_docs_frame(?array $navigation, array $themeSettings, mixed $teme, object $site, string $here): array
        {
            $guideLabels = ['docs-editors' => __('The editors’ guide'), 'docs-operators' => __('The operators’ guide'), 'docs-developers' => __('For developers')];
            $groups = temecms_docs_tree($navigation ?? [], $guideLabels);
            $versionLabel = trim((string) ($themeSettings['docs_version'] ?? ''));
            if ($versionLabel === '' && is_array($teme)) {
                $versionLabel = trim((string) ($teme['release'] ?? ''));
            }
            $index = [];
            foreach ($groups as $g) {
                foreach (temecms_docs_flat([$g]) as $it) {
                    $index[] = ['title' => (string) $it['label'], 'url' => (string) $it['url'], 'summary' => '', 'group' => $g['label']];
                }
            }
            $docsHome = (string) ($themeSettings['docs_address'] ?? '');

            return [
                'groups' => $groups,
                'flat' => temecms_docs_flat($groups),
                'versions' => temecms_docs_versions((string) ($themeSettings['docs_versions'] ?? ''), $here),
                'versionLabel' => $versionLabel,
                'docsHome' => $docsHome,
                'feedbackUrl' => (string) ($themeSettings['docs_feedback_address'] ?? ''),
                'index' => $index,
                'indexSrc' => $docsHome,
                'siteSearch' => (string) ($site->url ?? '/').'?s=',
            ];
        }

        /**
         * A README's wide picture followed by a phone picture, set as one composition: the wide picture in the column
         * and the phone picture at a phone's size over its lower corner. A phone picture is one whose text names a
         * phone or that is taller than it is wide. Other pictures stay as they are.
         */
        function temecms_pair_shots(string $html): string
        {
            $wide = '<figure class="card-image card-image--wide"[^>]*>(?:(?!<\/figure>).)*<\/figure>';
            $narrow = '<figure class="card-image card-image--column"[^>]*>(?:(?!<\/figure>).)*<\/figure>';

            return (string) preg_replace_callback('/('.$wide.')\s*('.$narrow.')/s', function (array $m): string {
                $isPhone = preg_match('/<img[^>]*\b(?:alt|src)="[^"]*\b(?:phone|mobile)\b/i', $m[2])
                    || (preg_match('/\bwidth="(\d+)"[^>]*\bheight="(\d+)"/', $m[2], $size) && (int) $size[2] > (int) $size[1]);
                if (! $isPhone) {
                    return $m[0];
                }
                $phone = str_replace('card-image card-image--column', 'card-image card-image--column pair-phone', $m[2]);

                return '<div class="shot-duo">'.$m[1].$phone.'</div>';
            }, $html);
        }

        /** "Teme 1.2": the newest Teme a listing works with, or '' when it names none. */
        function temecms_works_short(array $listing): string
        {
            return $listing['works'] !== [] ? 'Teme '.$listing['works'][0] : '';
        }

        /** "Teme 1.1 to 1.2", or "Teme 1.2" for one version. */
        function temecms_works_range(array $listing): string
        {
            $works = $listing['works'];
            if ($works === []) {
                return '';
            }
            $oldest = end($works);

            return $oldest === $works[0] ? 'Teme '.$works[0] : 'Teme '.$oldest.' to '.$works[0];
        }
    }
@endphp
