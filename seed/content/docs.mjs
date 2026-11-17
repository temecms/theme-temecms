import { a, b, c, p, h2, h3, ul, ol, callout, snippet, table } from '../lib.mjs';

// A numbered list that carries on from an earlier one (a step's code card sits between the two lists).
const olFrom = (start, ...items) => { const l = ol(...items); l.attrs.start = start; return l; };

const kebab = (t) => t.toLowerCase().replace(/[’']/g, '').replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
const PROOF = { 'Checked by CI': 'ci', 'Checked by tests': 'tests', 'Run on Dokploy': 'dokploy', 'Not yet proved': 'none' };

// ---------- the three written guides ----------
const install = {
    key: 'install',
    type: 'doc',
    title: 'Installing with the Compose recipe',
    slug: 'install',
    excerpt: 'Teme runs from one image and one Compose recipe. You need a server, a domain and a proxy that serves HTTPS. This page takes you from nothing to a running site with its first administrator.',
    fields: { guide: 'operators', group: 'Install', proof: 'ci', order: 1, soon: false, written_for: "the Compose recipe at the image's own tag" },
    body: {
        type: 'doc', content: [
            h2('What you need'),
            ul(
                [b('A server'), ' with Docker Engine and Compose v2 (', c('docker compose version'), ' prints a version).'],
                [b('A domain'), ' whose address points at the server.'],
                [b('A proxy that ends HTTPS in front of Teme.'), ' A platform such as Dokploy or Coolify has one; on a server with no platform, ', a('the plain VPS page', 'doc:running-teme-on-a-plain-vps'), ' gives you one.'],
                [b('Access to the image.'), ' If it is private, a read-only token for the registry that holds it.'],
            ),
            p('The recipe publishes no port. Your proxy reaches the ', c('web'), ' role on port 8080.'),

            h2('To get the recipe'),
            p('The recipe travels inside the image, so it always matches the version you run. Copy it out of the image you are about to run.'),
            ol(['If the image is private, sign in first:']),
            snippet('bash', 'docker login ghcr.io'),
            olFrom(2, ['Copy the recipe, using the exact tag you will run, never a moving tag such as ', c('latest'), ':']),
            snippet('bash', 'docker create --name teme-recipe ghcr.io/example/teme:0.1.0\ndocker cp teme-recipe:/var/www/html/docker ./teme\ndocker rm teme-recipe\ncd teme'),
            p('Everything below happens inside the ', c('teme'), ' folder: ', c('compose.yml'), ' and the ', c('postgres'), ' folder are the files the recipe needs.'),

            h2('To make the secrets'),
            p('Teme reads four secrets from files, so none of them sits in an environment variable. Make them once, and keep them: the application key decrypts what Teme has stored.'),
            snippet('bash', "umask 022\nmkdir -p secrets\nprintf 'base64:%s\\n' \"$(openssl rand -base64 32)\" > secrets/app_key\nopenssl rand -hex 24 > secrets/db_password\nopenssl rand -hex 24 > secrets/postgres_password\ntouch secrets/github_token\nchmod 0644 secrets/app_key secrets/db_password secrets/postgres_password secrets/github_token"),
            p('Leave ', c('github_token'), " empty, or put Teme's read-only GitHub token in it (see ", a('Configuring Teme', 'doc:configuring-teme'), ', Updates).'),
            p('The application key is 32 random bytes, written as ', c('base64:'), " and then the encoded bytes, because Teme's cipher is AES-256-CBC. The files must be readable by the containers, which is why they are ", c('0644'), ': the folder is the protection, so keep it to yourself.'),
            callout('warning', 'Never copy .env.example to a server. It is for development.'),

            h2('To write ', c('.env')),
            p('Create a file named ', c('.env'), ' beside ', c('compose.yml'), ' with the two settings the recipe requires:'),
            snippet('ini', 'TEME_IMAGE=ghcr.io/example/teme:0.1.0\nAPP_URL=https://example.com'),
            p(c('TEME_IMAGE'), ' is the image with its exact tag. ', c('APP_URL'), ' is the address readers type, with ', c('https'), '. Nothing else is required; ', a('Configuring Teme', 'doc:configuring-teme'), ' lists every other setting you may add.'),

            h2('To start'),
            p('Check that the recipe reads, then start it:'),
            snippet('bash', 'docker compose config --quiet\ndocker compose up --detach'),
            p('The recipe starts ', c('postgres'), ', then ', c('deploy'), ', which makes the database ready and exits, then ', c('web'), ', ', c('worker'), ', ', c('worker-long'), ' and ', c('scheduler'), '. ', c('web'), ' is healthy when it answers its health check; wait for that:'),
            snippet('bash', 'docker compose ps'),
            p('The ', c('valkey'), ' profile is the only one: it adds Valkey for a page cache that several servers share. Leave it off on one server.'),

            h2('To make the first administrator'),
            p("The deploy's log ends with the setup page's address and a setup code. Open the address, enter the code, and fill in the form: you are signed in as the site's first administrator."),
            callout('note', 'Lost the log? docker compose exec web php artisan teme:setup-code prints the address and the code again.'),
            p('Or make the first administrator from the shell. The command prints a link; open it to choose a password.'),
            snippet('bash', 'docker compose exec web php artisan teme:user:create --email=you@example.com --name="Your Name" --role=administrator'),

            h2('To check'),
            p("Ask Teme's doctor, and ask the health address:"),
            snippet('bash', 'docker compose exec web php artisan teme:doctor\ncurl -I http://127.0.0.1:8080/up'),
            p('The doctor lists what is wrong, with a line saying how to fix each. A green doctor and a ', c('200'), ' from ', c('/up'), ' mean the site is running. ', a('Troubleshooting', 'doc:troubleshooting'), ' explains each check.'),

            h2('Where things live'),
            p('The recipe keeps three named volumes:'),
            table(['Volume', 'Holds'], [
                [[c('pgdata')], ["PostgreSQL's data."]],
                [[c('media')], ['Uploaded files and their variants, at ', c('storage/app'), ' in every Teme role.']],
                [[c('page-cache')], ['The page cache, shared by every Teme role.']],
            ]),
            p('Every Teme role mounts ', c('media'), ' and ', c('page-cache'), '. A page cache that the roles do not share serves stale pages; the Status page warns you when that happens.'),

            h2('What next'),
            ul(
                ['Put the site behind HTTPS and, if you use it, Cloudflare: ', a('Proxies and HTTPS', 'doc:proxies-and-https'), '.'],
                ['Set up backups before you put anything on the site: ', a('Backups', 'doc:backups'), '.'],
                ['Learn how to update: ', a('Updating and rolling back', 'doc:updating-and-rolling-back'), '.'],
            ),
            p('Next: Configuring Teme.'),
        ],
    },
};

const signingIn = {
    key: 'signing-in',
    type: 'doc',
    title: 'Signing in and two-factor sign-in',
    slug: 'signing-in',
    excerpt: '',
    fields: { guide: 'editors', group: 'Getting started', proof: '', order: 1, soon: false, written_for: '' },
    body: {
        type: 'doc', content: [
            h2('To accept an invitation'),
            p('An administrator invites you by email. The email holds a link to a page headed ', b('Choose your password'), '.'),
            ol(
                ['Open the link in the email.'],
                ['Type a password, and type it again in ', b('Confirm password'), '.'],
                ['Finish. You are signed in.'],
            ),
            p('The link works for a limited time. If it has run out, ask the administrator to send a new one.'),

            h2('To sign in'),
            ol(
                ["Open the admin's address in your browser."],
                ['Type your ', b('Email address'), ' and your ', b('Password'), '.'],
                ['Choose ', b('Sign in'), '.'],
            ),
            p('Tick ', b('Remember me'), " on a computer only you use. It keeps you signed in for a number of days, which the site's settings decide. If the box is missing, the site has turned it off."),
            p('A session ends by itself after a stretch without activity, and after a longest time however busy you are. When it ends, sign in again; your unsaved work in the editor is kept in the browser while you do.'),

            h2('To sign in with a second step'),
            p('If you have turned on two-factor sign-in, the page headed ', b('Two-factor sign-in'), ' asks for an ', b('Authentication code'), ' after your password.'),
            ol(
                ['Open the authenticator app on your phone.'],
                ['Type the six-digit code it shows for this site.'],
                ['Continue.'],
            ),

            h2('To set up two-factor sign-in'),
            p('Two-factor sign-in means a stolen password is not enough to get in. An administrator may require it for your role; if so, the admin asks you to set it up before it lets you in.'),
            ol(
                ['Open your account menu, then ', b('Two-factor sign-in'), " in your account's list, and type your password if asked."],
                ['Choose ', b('Turn on two-factor sign-in'), '.'],
                ['In your authenticator app, scan the QR code. If you cannot scan it, type the setup key shown beside it instead.'],
                ['Type the first six-digit code the app shows, in ', b('Authentication code'), '.'],
                ['Look at ', b('Recovery codes'), '. Save them now, in a password manager. Each signs you in once if you lose your phone.'],
            ),
            callout('warning', 'If you lose both your phone and your recovery codes, only an administrator can get you back in.'),
            p('To turn it off, choose ', b('Turn off…'), ', then ', b('Turn off two-factor sign-in'), '. To replace your recovery codes, choose ', b('Regenerate codes'), '; the old ones stop working.'),

            h2('To use a recovery code'),
            ol(
                ['On the ', b('Two-factor sign-in'), ' page, choose ', b('Use a recovery code instead'), '.'],
                ['Type one of your codes in ', b('Recovery code'), '.'],
                ['Continue.'],
            ),
            p('That code will not work again. Set up two-factor sign-in again soon, so you have a full set.'),
            p('To go back to the app, choose ', b('Use your authenticator app instead'), '.'),

            h2('To reset a forgotten password'),
            ol(
                ['On the sign-in page, choose ', b('Forgot your password?'), '.'],
                ['Type your ', b('Email address'), ' and ask for the link.'],
                ['Open the email, follow its link, and choose a new password.'],
                ['Choose ', b('Back to sign in'), ' and sign in.'],
            ),
            p('For your safety, the page says the same thing whether or not the address has an account.'),

            h2('To confirm your password when the admin asks'),
            p('Some changes are sensitive: they can lock people out or move data. Before one, the admin asks you to type your password again on a page headed ', b('Confirm password'), '.'),
            p('Once you have, it does not ask again for a few minutes. How many is ', c('security.elevation_minutes'), ", an administrator's setting; see the ", a('settings reference', 'doc:settings-reference'), '.'),
            p('To change your password itself, see ', a('your account', 'doc:your-account-and-notifications'), '.'),
        ],
    },
};

const proxies = {
    key: 'proxies-and-https',
    type: 'doc',
    title: 'Proxies and HTTPS',
    slug: 'proxies-and-https',
    excerpt: "Teme never serves HTTPS itself. A proxy in front of it, yours or your platform's, ends HTTPS and passes requests on. This page explains how Teme learns a reader's real address and whether the request was secure, which settings control that, and how to check it.",
    fields: { guide: 'operators', group: 'HTTPS and platforms', proof: 'tests', order: 1, soon: false, written_for: 'the edge settings Teme reads at its own tag' },
    body: {
        type: 'doc', content: [
            p("Teme never serves HTTPS itself. A proxy in front of it, yours or your platform's, ends HTTPS and passes requests on. This page explains how Teme learns a reader's real address and whether the request was secure, which settings control that, and how to check it."),

            h2("How a reader's address reaches Teme"),
            p('A request passes through several hops:'),
            ol(
                ["The reader's browser."],
                ['Cloudflare, if you use it.'],
                ['Your proxy: Traefik, in Dokploy, Coolify and the plain-VPS recipe.'],
                ['nginx, inside the ', c('web'), ' container.'],
                ['PHP, which is Teme.'],
            ),
            p('Each proxy adds the address it received the request from to a header called ', c('X-Forwarded-For'), ', and says whether the reader used HTTPS in ', c('X-Forwarded-Proto'), '. Teme believes those headers only from the proxies you list in ', c('TRUSTED_PROXIES'), '. From anyone else it ignores them, because anyone can send a header. If Teme trusted every sender, one reader could invent any address and walk past every limit that counts requests by address.'),
            p('nginx never takes the address from a header. The image’s CI checks that.'),

            h3('What ', c('TRUSTED_PROXIES'), ' accepts'),
            p('A comma-separated list of addresses and ranges, plus two names:'),
            ul(
                [c('private'), ' means the three ranges that Docker networks use: ', c('10.0.0.0/8'), ', ', c('172.16.0.0/12'), ' and ', c('192.168.0.0/16'), '.'],
                [c('cloudflare'), " means Cloudflare's published ranges, which ", a('Cloudflare in front of Teme', 'doc:cloudflare-in-front-of-teme'), ' lists.'],
            ),
            p('When you leave it unset, production trusts ', c('private'), ' and nothing else, and every other environment trusts no proxy. When you set it, even to nothing, it means exactly what you wrote. Teme refuses to start if it contains ', c('*'), ' or a range that covers every address, because trusting everything is the mistake that setting exists to prevent.'),
            p('For one proxy on the Docker network, which is the recipe’s layout, you set nothing. Behind Cloudflare and Traefik, set ', c('TRUSTED_PROXIES=private,cloudflare'), ', and make Traefik trust Cloudflare as well; ', a('Cloudflare in front of Teme', 'doc:cloudflare-in-front-of-teme'), ' says how.'),

            h2("To set the site's address"),
            p('Set ', c('APP_URL'), ' to the address readers type, with ', c('https'), ':'),
            snippet('ini', 'APP_URL=https://example.com'),
            p('Teme answers requests for that host only. A request for another host name, which a forged ', c('Host'), ' header would make, is refused, so it builds no link and fills no cache. A health check made inside the container, from the container itself, may also name ', c('localhost'), '.'),
            p('If readers get links that begin with ', c('http'), ' instead of ', c('https'), ', ', c('APP_URL'), ' is wrong, or your proxy is not sending ', c('X-Forwarded-Proto'), '. See ', a('Troubleshooting', 'doc:troubleshooting'), '.'),

            h2('HSTS'),
            p('In production, at an ', c('https'), ' address, Teme sends the ', c('Strict-Transport-Security'), ' header on every secure answer. It tells a browser to use HTTPS for this site and nothing else. The default, set by ', c('TEME_HSTS'), ', is:'),
            snippet('plain', 'max-age=31536000'),
            p('That is one year. An empty ', c('TEME_HSTS'), ' sends no header at all, for a site whose proxy sends its own. nginx adds the same header to the answers PHP never sees, such as a static file or a stored page. Set HSTS at one layer, not also at your proxy, or readers get it twice.'),
            p('Teme adds neither ', c('preload'), ' nor ', c('includeSubDomains'), ', because both bind more than this site: a browser that has seen ', c('includeSubDomains'), ' forces HTTPS on every subdomain of the parent for a year, including a test subdomain that has no certificate. Add either to ', c('TEME_HSTS'), ' only when you mean it.'),

            h2('Cookies'),
            p('In production at an ', c('https'), ' address, Teme marks every cookie ', c('Secure'), ', and names the session cookie with a ', c('__Host-'), ' prefix, which a browser accepts only over HTTPS and only for the exact host. Keep ', c('SESSION_DRIVER'), ' as ', c('database'), '.'),

            h2('No port is published'),
            p('The recipe publishes no port. Your proxy reaches the ', c('web'), ' role on port 8080 over the Docker network. Do not publish it: a published port lets anyone reach Teme without going through your proxy.'),

            h2('To check'),
            p('From the server, ask for the health address, then a page, then the admin:'),
            snippet('bash', 'curl -I http://127.0.0.1:8080/up'),
            snippet('bash', 'curl -I https://example.com/'),
            snippet('bash', 'curl -I https://example.com/admin/login'),
            p('The first answers ', c('200'), '. The other two answer through your proxy, over HTTPS, and carry a ', c('strict-transport-security'), " header. If a reader's address looks wrong in the logs, every reader showing the same address is the sign that the proxy is not trusted: check ", c('TRUSTED_PROXIES'), '.'),
        ],
    },
};

const written = { 'editors/Getting started/Signing in and two-factor sign-in': signingIn, 'operators/Install/Installing with the Compose recipe': install, 'operators/HTTPS and platforms/Proxies and HTTPS': proxies };

// ---------- every guide the drawing lists ----------
// [title, kind]: 'link' = drawn as a link (written in the repository), 'soon' = tagged Soon in the drawing; proof: a label or '' (no mark).
const LINKED = 'This guide is written in the repository. Its page is not drawn yet.';
const SOON = 'Not written yet.';
const LIST = [
    ['editors', 'Getting started', [['Signing in and two-factor sign-in', 'link'], ['Your account and notifications', 'link']]],
    ['editors', 'Writing and publishing', [['Writing: formatting, images and cards', 'link'], ['Saving, review, scheduling and publishing', 'link'], ['Revisions and preview', 'link']]],
    ['editors', 'Looking after the site', [['The lists: find, edit, trash and bring back', 'link'], ['Media', 'link'], ['Categories, tags, menus and regions', 'link'], ['Comments', 'link'], ['Redirects', 'link'], ['Settings', 'link'], ['The page cache', 'link'], ['The banners at the top of the admin', 'link'], ['Are the backups safe?', 'link']]],
    ['editors', 'Coming to this guide', [['Importing another site', 'soon'], ['Themes and plugins', 'soon']]],
    ['operators', 'Install', [['Installing with the Compose recipe', 'link', 'Checked by CI'], ['Configuring Teme', 'link', 'Checked by tests'], ['The database', 'link', 'Checked by tests'], ['Sizing a server', 'link', 'Not yet proved']]],
    ['operators', 'HTTPS and platforms', [['Proxies and HTTPS', 'link', 'Checked by tests'], ['Cloudflare in front of Teme', 'link', 'Not yet proved'], ['Running Teme on Dokploy', 'link', 'Run on Dokploy'], ['Running Teme on Coolify', 'link', 'Not yet proved'], ['Running Teme on a plain VPS', 'link', 'Not yet proved']]],
    ['operators', 'Keep it safe and current', [['Backups', 'link', 'Checked by tests'], ['Updating and rolling back', 'link', 'Not yet proved'], ['Restoring, moving and exporting', 'link', 'Not yet proved'], ['Troubleshooting', 'link', 'Checked by tests'], ['The migration runbook', 'link', 'Not yet proved']]],
    ['developers', 'References, generated from the code', [['Theme reference', 'link'], ['Theme view data', 'link'], ['Settings reference', 'link'], ['Fields reference', 'link'], ['Cards reference', 'link'], ['Export format reference', 'link']]],
    ['developers', 'Guides', [["The developers' guide", 'soon'], ['The plugin handbook', 'soon'], ['The theme handbook', 'soon']]],
    ['developers', 'More references', [['Hook reference', 'soon'], ['Read API reference', 'soon'], ['The component kit', 'soon']]],
];

const items = [];
for (const [guide, group, entries] of LIST) {
    entries.forEach(([title, kind, proofLabel], idx) => {
        const sample = written[`${guide}/${group}/${title}`];
        if (sample) { items.push(sample); return; }
        items.push({
            key: kebab(title),
            type: 'doc',
            title,
            slug: kebab(title),
            excerpt: '',
            fields: { guide, group, proof: proofLabel ? PROOF[proofLabel] : '', order: idx + 1, soon: true, written_for: '' },
            body: { type: 'doc', content: [p(kind === 'soon' ? SOON : LINKED)] },
        });
    });
}

export default items;
