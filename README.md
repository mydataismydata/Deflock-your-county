# Deflock your county

Five-page site for a local group organizing against automated license plate
readers. Built to run on shared hosting with no database, no build step and no
writable directory. It ships configured for St. Johns County, Florida, and
every string that names a place sits in one file.

## What it needs

PHP 8.0 or newer with `mail()` enabled, and Apache with `mod_rewrite`. That is
the default Ionos shared hosting configuration. There is nothing to install and
nothing to compile.

The site loads no fonts, scripts, images or stylesheets from any other domain.
That is deliberate for a privacy campaign, and it also means the pages work
behind a blocker.

## Layout

```
index.php          Home: the problem, the four asks, links onward
about.php          Who the group is and what it does
resources.php      External links, grouped, defined in one array at the top
get-involved.php   Meetings, officials, phone/email/comment scripts, records
contact.php        Form and its handler, posts to itself
404.php            Error page, wired up in .htaccess
router.php         Development only. Reproduces the live URLs under php -S
docker-compose.yml Development only. Runs the site under real Apache
docker/            The image for that container, and the captured-mail drop

includes/
  place.php        Every string that names a county, state, statute or official
  config.php       Your install: mail addresses, form secret, meetings, socials
  header.php       Document head, fixed header, grain overlay, primary nav
  footer.php       Footer and closing markup
  mailer.php       Form tokens and the single mail() call
  .htaccess        Denies web access to this directory

assets/css/site.css   One stylesheet. Design tokens are in :root at the top.
assets/js/scene-motion.js  Heading reveal, scene brightening, header state, menu.
assets/fonts/         Four self-hosted woff2 files. Nothing loads from a CDN.
assets/img/           The four photographs, as webp.
.htaccess             Clean URLs, HTTPS redirect, security headers, caching
```

## Configuring your install

Edit `includes/config.php`. Six values matter:

| Setting | What to put in it |
| --- | --- |
| `SITE_URL` | The live address, no trailing slash |
| `CONTACT_TO` | The one inbox the form delivers to |
| `CONTACT_FROM` | A mailbox **on this domain**. Ionos drops mail claiming another domain |
| `FORM_SECRET` | 64 random hex characters, generated fresh per install. A `DEFLOCK_FORM_SECRET` environment variable overrides it where the host can set one; Ionos cannot |
| `CHANNELS` | Social links. Empty strings are skipped, not printed |
| `MEETINGS` | Upcoming dates. An empty array prints a standing notice instead |

Generate the secret:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

The contact form refuses to send while `FORM_SECRET` is left at its shipped
value, so a half-finished install fails loudly rather than quietly.

## What stops form spam

Five checks, in the order the handler runs them. None of them stores anything,
calls out to a third party, or asks a visitor to identify a bus.

| Check | What it catches |
| --- | --- |
| `Sec-Fetch-Site` must be `same-origin` | A scripted POST. Browsers set this header themselves and a page cannot write it, so a script has to forge it on purpose. Browsers older than Safari 16.4 send no `Sec-Fetch` header, and fall back to `Origin` |
| A hidden field must come back empty | A bot that fills every input. The field's name is an HMAC of `FORM_SECRET`, so it differs per install and a canned payload cannot know to skip it |
| The signed timestamp must be `FORM_MIN_SECONDS` to `FORM_MAX_SECONDS` old | A submission faster than a person can type, and a token lifted off the page and replayed later |
| The name must hold no web address | The `Name: Jane http://buy.example` pattern |
| The message must hold no link markup and at most two links | Link-farm payloads |

`FORM_REQUIRE_BROWSER_HEADERS` turns the first check off. Set it to `false`
only if a host strips both `Sec-Fetch-Site` and `Origin`, which would turn
every real submission away. Send yourself a message through the live form
after changing it either way.

Keep `Referrer-Policy` at `same-origin` rather than `no-referrer`. Outside
sites receive nothing under either one. `no-referrer` additionally makes the
browser send `Origin: null` on the form's own POST, which breaks the fallback
for old browsers.

## Security headers

`includes/config.php` sends the content security policy and the four headers
beside it. It is the only place that sets them for a page.

They are not in `.htaccess`, for two reasons. A host without `mod_headers`
skips that block without saying so, and Ionos shared hosting is such a host,
which is how the live site ran with no policy at all. On a host that does have
the module, `Header always set` writes to `err_headers_out`, which Apache
appends to the response PHP has already built rather than replacing it, so
every header would arrive twice.

`.htaccess` keeps one header, `X-Content-Type-Options`, scoped to the static
file extensions. Apache serves the stylesheet, the script, the fonts and the
images without PHP ever running, so nothing else would reach them.

Check what a host actually sends, and check for doubles:

```bash
curl -sI https://your-site.example/ | grep -ic "content-security-policy"
```

That should print `1`.

## Moving it to another county

Rewrite `includes/place.php`. Nothing else names a place, and that is checked:
swapping the file from St. Johns County, Florida to Travis County, Texas leaves
no trace of the old county anywhere in the rendered pages.

What the file holds:

- **Naming.** `COUNTY`, `COUNTY_SHORT`, `STATE`, `COUNTY_SEAT`. `SITE_NAME` and
  the wordmark are built from these.
- **Public records law.** The citation as it appears in a request letter, a link
  to the statute, and two or three sentences on what the law actually grants.
  States differ on whether a requester must give a reason or live in the state,
  so that part is prose rather than a set of flags.
- **`BODIES`.** The elected bodies on the Get Involved page, in order, each with
  the reason it matters and a link to its directory.
- **`LOCAL_RESOURCES`.** The county block on the Resources page. It is spliced in
  after the national material and before the self-defense links.
- **`LEGAL_HELP_NAME` and `LEGAL_HELP_URL`.** Set the URL to an empty string and
  the sentence offering legal intake disappears from the contact page.

Where one value can be derived from another it already is, so changing `COUNTY`
also changes the sheriff's office name and the Resources heading.

## Deploying to Ionos

Upload the repository contents to the document root, usually `/` on an Ionos
webspace. Include the dotfiles: `.htaccess` at the root and
`includes/.htaccess` both matter.

```bash
rsync -av --delete \
  --exclude '.git' --exclude '.gitignore' --exclude 'CONTENT-TODO.md' \
  --exclude 'router.php' \
  ./ user@home123456789.1and1-data.host:/homepages/NN/dNNNNNN/htdocs/
```

Then check three things in a browser:

1. `/about` loads and the address bar keeps the clean URL.
2. `/includes/config.php` returns 403, not your settings.
3. The contact form sends and the mail arrives.

If the form reports that the server could not send it, the host refused the
handoff. Confirm that the mailbox in `CONTACT_FROM` exists on the domain, then
check the Ionos mail logs.

## Running it locally

```bash
docker compose up -d
```

Then open <http://127.0.0.1:8731>. The port is bound to loopback, so nothing
on your network can reach it.

This runs the same Apache modules the live host does, with `AllowOverride All`,
so `.htaccess` is genuinely in effect: clean URLs, the redirect from `/about.php`
to `/about`, the 403 on `includes/`, the security headers and the gzip and
caching rules. Testing against anything that ignores `.htaccess` means testing
behavior you are not going to ship.

The project is mounted live. Edit a file and reload the page. Stop it with
`docker compose down`.

`mail()` has no transport in a container, so the image points `sendmail_path` at
a script that appends the message to `docker/maildrop/sent.eml`. Submit the
contact form and read that file to see exactly what would have been delivered,
headers included. The compose file also sets `DEFLOCK_FORM_SECRET`, which
overrides `FORM_SECRET` so the form works locally without a real secret going
anywhere near the repository.

### Without Docker

```bash
php -S localhost:8000 router.php
```

Lighter, and fine for working on copy or layout. `router.php` gives the built-in
server the clean URLs, the 404 page and the block on `includes/`. It cannot
reproduce the headers, the compression or the caching rules, because those are
Apache directives. To watch what the form would send:

```bash
php -d sendmail_path='cat >> /tmp/sent.eml' -S localhost:8000 router.php
```

## Restyling

Open `assets/css/site.css` and change the tokens in `:root`. Colours, the type
scale, the shell widths, the section rhythm, the corner radii and every duration
come from there. Nothing below that block hardcodes a colour.

The stylesheet and the script are cached for a week by `.htaccess`, so both are
linked with the file's modification time on the end of the URL. Editing either
one changes the stamp, and a returning visitor gets the new file instead of the
week-old one.

The pages use no inline `style` attributes, which is what lets `.htaccess` ship
a Content-Security-Policy with no `unsafe-inline`. If you add an inline style
the browser will drop it. Add a class instead.

Four typefaces, four jobs, no overlap. DM Serif Display sets every heading.
Instrument Serif sets the wordmark and nothing else. IBM Plex Sans sets
everything read in sentences. Geist Mono sets anything that is data rather than
language: the meeting dates, the commissioner contact lines, the scripts.

All four are committed to `assets/fonts/` as latin-subset woff2 and declared as
`@font-face` rules. IBM Plex Sans and Geist Mono are variable fonts, so one file
each covers every weight. Loading them from a font CDN instead would contradict
the line in the footer promising that the site loads nothing from a third party.

The page is black and every text colour is white at a fixed opacity. Depth comes
from hairlines rather than from boxes, and there is one accent hue. The design
language is documented in full in `.working/scene-design-system/README.md`.

`assets/js/scene-motion.js` carries the movement: headings arrive a word at a
time on first sight, a scene's photograph brightens as it crosses the screen,
the header turns solid after 24px of scroll, and the menu collapses behind a
button under 48rem. Everything but the menu stops under
`prefers-reduced-motion: reduce`, and the headings then render as written.

## Before it goes live

`CONTENT-TODO.md` lists every placeholder and every factual claim that needs a
source attached. Work through it first.
