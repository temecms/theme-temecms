{{--
    The product pictures' other versions, which the theme carries in public/shots: a dark one for a reader in the dark
    scheme, and one drawn for a phone, light and dark. A picture is known by its file's name (editor, posts, updates and
    so on, as the media library stores it). Included once at the top of a template; it defines a function and draws nothing.
--}}
@php
    if (! function_exists('temecms_pictures')) {
        /**
         * The HTML with every <img> whose picture has other versions wrapped in a <picture>: the phone versions under
         * 640 px, the dark ones in the dark scheme, and the <img> itself, unchanged, for the rest. $wide asks for the
         * versions drawn for the full width of the page, where the theme has them, in place of the picture itself.
         */
        function temecms_pictures(string $html, bool $wide = false): string
        {
            static $dir = null;
            $dir ??= dirname((string) (app('view')->getFinder()->getHints()['theme'][0] ?? '')).'/public/shots';
            if (! str_contains($html, '<img') || ! is_dir($dir)) {
                return $html;
            }

            return (string) preg_replace_callback('/(<picture\b.*?<\/picture>)|<img\b[^>]*>/s', function (array $m) use ($dir, $wide): string {
                if (($m[1] ?? '') !== '') {
                    return $m[0];
                }
                $img = $m[0];
                if (! preg_match('/\bsrc="[^"]*\/([a-z0-9-]+?)-w\d+\.webp"/', $img, $name)) {
                    return $img;
                }
                $sources = '';
                $versions = [['-phone-dark', '(max-width: 640px) and (prefers-color-scheme: dark)'], ['-phone', '(max-width: 640px)']];
                $versions = [...$versions, ...($wide && is_file($dir.'/'.$name[1].'-wide.webp') ? [['-wide-dark', '(prefers-color-scheme: dark)'], ['-wide', '']] : [['-dark', '(prefers-color-scheme: dark)']])];
                foreach ($versions as [$suffix, $media]) {
                    $file = $dir.'/'.$name[1].$suffix.'.webp';
                    if (! is_file($file)) {
                        continue;
                    }
                    [$w, $h] = getimagesize($file) ?: [0, 0];
                    $sources .= '<source'.($media !== '' ? ' media="'.e($media).'"' : '').' srcset="'.e(asset('themes/temecms/shots/'.$name[1].$suffix.'.webp')).'" width="'.$w.'" height="'.$h.'">';
                }

                return $sources === '' ? $img : '<picture>'.$sources.$img.'</picture>';
            }, $html);
        }
    }
@endphp
