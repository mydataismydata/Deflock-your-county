# Deflock St. Johns

Five-page static site for a St. Johns County, Florida group organizing around
automated license plate readers. Built to run on Ionos shared hosting with no
database, no build step and no writable directory.

## What it needs

PHP 8.0 or newer with `mail()` enabled, and Apache with `mod_rewrite`. That is
the default Ionos shared hosting configuration. There is nothing else to
install.

The site loads no fonts, scripts, images or stylesheets from any other domain.
That is deliberate for a privacy campaign and it also means the pages work
behind a blocker.

## Layout

```
index.php          Home: the problem, the four asks, links onward
about.php          Who the group is and what it does
resources.php      External links, grouped, defined in one array at the top
get-involved.php   Meetings, officials, phone/email/comment scripts, records
contact.php        Form and its handler, posts to itself
404.php            Error page, wired up in .htaccess

includes/
  config.php       Every setting you are likely to change
  header.php       Document head, masthead, primary nav
  footer.php       Footer and closing markup
  mailer.php       Form tokens and the single mail() call
  .htaccess        Denies web access to this directory

assets/css/site.css   One stylesheet. Design tokens are in :root at the top.
assets/js/nav.js      Collapses the nav on narrow screens. Nothing else.
.htaccess             Clean URLs, HTTPS redirect, security headers, caching
```

## Setting it up

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

## Deploying to Ionos

Upload the repository contents to the document root, usually `/` on an Ionos
webspace. Include the dotfiles: `.htaccess` at the root and
`includes/.htaccess` both matter.

```bash
rsync -av --delete \
  --exclude '.git' --exclude '.gitignore' --exclude 'CONTENT-TODO.md' \
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
php -S localhost:8000
```

The built-in server ignores `.htaccess`, so clean URLs will not work. Use
`/about.php` instead of `/about` while developing. Everything else behaves the
same.

## Restyling

Open `assets/css/site.css` and change the tokens in `:root`. Colors, type
scale, line widths, border weight and the button shadow offset all come from
there. Nothing below that block hardcodes a color.

The pages use no inline `style` attributes, which is what lets `.htaccess` ship
a Content-Security-Policy with no `unsafe-inline`. If you add an inline style,
the browser will drop it. Add a class instead.

## Before it goes live

`CONTENT-TODO.md` lists every placeholder and every factual claim that needs a
source attached. Work through it first.
