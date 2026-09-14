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

includes/
  place.php        Every string that names a county, state, statute or official
  config.php       Your install: mail addresses, form secret, meetings, socials
  header.php       Document head, masthead, primary nav
  footer.php       Footer and closing markup
  mailer.php       Form tokens and the single mail() call
  .htaccess        Denies web access to this directory

assets/css/site.css   One stylesheet. Design tokens are in :root at the top.
assets/js/nav.js      Collapses the nav on narrow screens. Nothing else.
.htaccess             Clean URLs, HTTPS redirect, security headers, caching
```

## Configuring your install

Edit `includes/config.php`. Six values matter:

| Setting | What to put in it |
| --- | --- |
| `SITE_URL` | The live address, no trailing slash |
| `CONTACT_TO` | The one inbox the form delivers to |
| `CONTACT_FROM` | A mailbox **on this domain**. Ionos drops mail claiming another domain |
| `FORM_SECRET` | 64 random hex characters, generated fresh per install |
| `CHANNELS` | Social links. Empty strings are skipped, not printed |
| `MEETINGS` | Upcoming dates. An empty array prints a standing notice instead |

Generate the secret:

```bash
php -r "echo bin2hex(random_bytes(32)), PHP_EOL;"
```

The contact form refuses to send while `FORM_SECRET` is left at its shipped
value, so a half-finished install fails loudly rather than quietly.

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
php -S localhost:8000 router.php
```

`router.php` gives the built-in server the clean URLs, the 404 page and the
block on `includes/`, all of which Apache handles through `.htaccess` on the
live host. Without it you would be testing different behavior than you ship.

With Docker instead of a local PHP:

```bash
docker run --rm -p 8000:8000 -v "$PWD":/app -w /app php:8.3-cli php -S 0.0.0.0:8000 router.php
```

`mail()` will fail in either case, because neither has a mail transport. To see
what the form would have sent, point PHP at a file instead of a mailer:

```bash
php -d sendmail_path='cat >> /tmp/sent.eml' -S localhost:8000 router.php
```

## Restyling

Open `assets/css/site.css` and change the tokens in `:root`. Colors, type
scale, line widths, border weight and the button shadow offset all come from
there. Nothing below that block hardcodes a color.

The pages use no inline `style` attributes, which is what lets `.htaccess` ship
a Content-Security-Policy with no `unsafe-inline`. If you add an inline style
the browser will drop it. Add a class instead.

## Before it goes live

`CONTENT-TODO.md` lists every placeholder and every factual claim that needs a
source attached. Work through it first.
