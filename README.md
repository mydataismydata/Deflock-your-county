# Deflock your county

Site for a local group organizing against automated license plate readers.
Built to run on shared hosting with no database and no build step. One
directory is writable, and only the candidate survey writes to it. It ships
configured for St. Johns County, Florida, and every string that names a place
sits in one file.

## What it needs

PHP 8.0 or newer with `mail()` enabled, Apache with `mod_rewrite`, and
permission for PHP to create one directory for the candidate survey. That is
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
candidate-surveys.php  Tiles of every candidate who answered, one candidate's answers,
                   and the questions alone at /candidate-surveys/questions
survey.php         The questionnaire a candidate's one-use link opens. Unlisted
survey-admin.php   Sign-in, invites and moderation for the survey. Unlisted
404.php            Error page, wired up in .htaccess
router.php         Development only. Reproduces the live URLs under php -S
docker-compose.yml Development only. Runs the site under real Apache
docker/            The image for that container, and the captured-mail drop
tools/
  survey-admin.php Command line only. Adds, changes and removes survey admins
data/              Created on the host by the survey. Never in git, never deployed

includes/
  place.php        Every string that names a county, state, statute or official
  config.php       Your install: mail addresses, form secret, meetings, socials
  header.php       Document head, fixed header, grain overlay, primary nav
  footer.php       Footer and closing markup
  mailer.php       Form tokens and the mail() call every form sends through
  survey.php       The survey's storage, one-use links, admin sign-in and mail
  survey-text.php  The questionnaire's wording and the invite email's defaults
  survey-admins.php  The admin accounts. Written by tools/survey-admin.php, not in git
  survey-invite.php  The invite email's default subject and message. Not in git
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

## The candidate survey

Candidates answer the group's questionnaire on this site rather than in a
Google Form. The wording lives in `includes/survey-text.php`.

1. An admin signs in at `/survey-admin`, enters a candidate's name and email
   address, and sends the invite. The email is the subject and message on
   that page. `{{candidate-name}}` in either becomes the name, and
   `{{survey-link}}` in the message becomes the candidate's link. A message
   without `{{survey-link}}` gets the link on its own line at the end. Any
   other `{{...}}` is refused before anything is sent, because a misspelled
   placeholder would reach the candidate as typed.
2. The link opens `/survey?t=…`. It takes one set of answers. A second
   submission on the same link is refused, and so is a link that was
   withdrawn or replaced.
3. On submission the answers are published at `/candidate-surveys` at once.
   A plain-text copy goes to the candidate and another to the admin who sent
   the invite, and each copy's Reply-To points at the other person.

Each response is stored against its invite, so it stays tied to the address
the link was sent to as well as to the name typed in the signature. The email
address is never published.

### The invites table

Every invite is one row in a table under Invites on `/survey-admin`, newest
first: email, name, office, party, incumbent, when it was sent, when it was
answered, and its status. A long name or office is cut short in the row, and
hovering over it shows the whole of it. Selecting an email address opens that
invite above the table, to edit its name, office, party and incumbent, or to
send a new link, withdraw it, read its answers, or hide them.

Selecting a column heading sorts by it. The first click sorts A to Z, newest
first, incumbents first or answered first, by column. The second reverses it,
and the third goes back to newest invite first. The counts above the table
filter it: "7 answered" shows only those, and selecting it again or the total
shows every row. Both choices last while the tab is open, so they survive the
reload after an edit. Sorting and filtering happen in the page, in
`assets/js/survey-admin.js`.

The status is one of:

- **answered**: a response exists. It links to the public page.
- **waiting**: emailed, and not answered yet.
- **queued**: in the send queue, with no link made yet.
- **unsent**: the server refused its email. "Send a new link" tries again.
- **withdrawn**: its link no longer opens the survey.
- **hidden**: answered, and taken off the public page.

Invites from before 11 October 2026 kept no record of whether their email
went out. They show as waiting, with the time the link was made as the time
sent.

### The send queue

An import that names an address nobody has invited yet adds it to a send
queue instead of emailing it. Nothing goes out until an admin presses "Start
sending". The queue then sends one invite every 90 seconds, in the order of
the file, with the saved subject and message.

- **The host's cron sends it.** Add one cron job that fetches
  `/survey-cron?key=...` every minute. In the Ionos control panel that is a
  WebCron job with an HTTP GET of that address. The signed-in admin page shows
  the full address under "background sending" at the foot of the page. The key
  comes from `data/key.php`, so it survives uploads. Without the right key the
  address is a 404.

  Each run lasts at most 35 seconds, inside Ionos's 60-second limit on a web
  cron call. It sends when the next invite falls due within that time, so a
  once-a-minute cron keeps the 90-second spacing. It sends only while the
  queue is started, so "Start sending" and "Pause sending" still control it.
  Once the cron job has run in the last three minutes, `/survey-admin` says
  the page can be closed, and "background sending" reads "on".

  A host with a command-line cron can run the file instead. Plain `php` on
  Ionos is PHP 4, so name a PHP 8 binary:

  ```
  /usr/bin/php8.3-cli ~/site-folder/survey-cron.php
  ```
- **Without the cron job, it needs the page open.** While the queue runs,
  `/survey-admin` asks the server every ten seconds whether 90 seconds have
  passed, using `assets/js/survey-admin.js`. Close every copy of the page and
  the queue waits. Open it again and it carries on. A tab in the background
  may send a little slower than that, never faster.
- **One sender.** A lock file in `data/` makes sure the cron job, open pages
  and other admins still send one every 90 seconds between them.
- **Refusals.** When the server refuses an email, the queue waits 15 minutes
  before it sends anything else, then tries the same invite again. After three
  refusals the invite leaves the queue as unsent, and the queue moves on.
- **Names.** A new address needs a Name when the saved message uses
  `{{candidate-name}}`. The import refuses the row without one.
- "Take out of the queue" withdraws one queued invite. "Send it now" sends it
  at once, outside the queue. "Pause sending" stops the queue and keeps its
  place.

The interval, the wait after a refusal and the number of tries are
`SURVEY_QUEUE_INTERVAL`, `SURVEY_QUEUE_BACKOFF` and `SURVEY_QUEUE_TRIES` in
`includes/survey.php`.

### Admin accounts

The username is an email address, because the admin copy of each answer goes
there. Any characters are accepted apart from control characters. Spaces at
the ends are dropped and capitals do not matter when signing in. A password
needs 8 characters or more and nothing else.

Accounts are made on your own computer, not on the site:

```bash
php tools/survey-admin.php add you@example.org
```

It asks for the password twice without echoing it, and writes
`includes/survey-admins.php`. Git ignores that file, because this repository
is public. Upload it to `includes/` on the host. The deploy bundle picks it up
with the rest of `includes/`. The same tool has `password`, `remove` and
`list`. Changing a password signs that account out everywhere.

A sign-in lasts `SURVEY_SESSION_HOURS`. It is the one cookie the site sets,
and only somebody who signs in to `/survey-admin` receives it. Five wrong
passwords for one username from one address, or twenty from one address,
lock that address out for 15 minutes. The throttle files are named by a keyed
hash, so no IP address is written down.

### Where the answers live

`SURVEY_DATA_DIR` in `includes/config.php`, which is `data/` at the site root
unless you change it. The survey creates it on first use:

```
data/
  .htaccess            refuses all web access
  key.php              the random key that signs admin cookies
  draft.php            the invite subject and message as last saved
  invites/<id>.php     who was invited, when, by whom, and a hash of their link
  responses/<id>.php   what they answered, with the questions as they were asked
  throttle/<hash>.php  recent failed sign-ins
```

Every file begins with a line of PHP that answers 404 and exits, followed by
JSON. A host that ignored the `.htaccess` would still serve nothing. A folder
outside the web root is safer again, if the host lets PHP reach one: set
`SURVEY_DATA_DIR` to its absolute path.

Back it up by downloading the folder. **A deploy must never replace or delete
it.** The bundle never contains it, and the rsync line below skips it.

Nothing in the survey is signed with `FORM_SECRET`. The deploy script mints a
new form secret for every bundle, and anything signed with it would stop
working on each upload. Links are random, and only their hashes are stored, so
an admin sees a link once, when it is made. "Send a new link" replaces it and
emails the new one.

### Taking an answer down

"Hide from the site" on `/survey-admin` takes a response off the public page
and leaves the file in place. "Put back on the site" reverses it. Nothing on
the page deletes an answer. Delete the file in `data/responses/` for that.

### Exporting and importing the invites

"Export the list (CSV)" under Invites on `/survey-admin` downloads every invite
as a spreadsheet file. Its columns are Name, Email, Office, Party, Incumbent,
Sent, Responded, Status and Office as typed. Times are in the county's time
zone. A cell that would start with `=`, `+`, `-` or `@` gets a leading
apostrophe, so a name typed by a candidate cannot run as a formula.

"Import" takes a CSV file back and sets Name, Office, Party and Incumbent on
every invite sent to each row's email address. Capitals and spaces around the
address do not matter. A row whose address has no invite yet joins the send
queue, described above. Only the Email column is required, and other columns
are ignored, so an edited export works as it is.

- A blank cell leaves that field as it was. Clear a field on the invite's own
  row instead.
- Incumbent takes Yes or No. TRUE and FALSE from a checkbox column also work.
- A row with a bad cell is skipped whole, and the page lists it by row number.
- Excel's plain "CSV" format, with Windows-1252 text and semicolons in some
  countries, is read as well as "CSV UTF-8".
- Importing the same file twice changes nothing the second time.

### Office, party and incumbent

Candidates type their own office, and no two type it the same way. An admin
sets the office, party and incumbent on each invite after the fact, by import
or by opening the invite from the table. The office box suggests offices
already entered, so one seat keeps one spelling.

These are stored on the invite, not on the response. The tile on
`/candidate-surveys` shows the admin's office with the party after it in
capitals, as in "State Representative District #18 (D)", and an incumbent
mark. The tiles sort by that office. A candidate's own page carries the
same three in its header and still prints the office as the candidate typed
it under their name. With no office set, the candidate's wording is used.

### Changing the questions

Edit `includes/survey-text.php`. Each response keeps a copy of the questions
it answered, so an edit changes what the next candidate sees and leaves every
published answer under the wording that candidate was actually asked.

### The invite email

The group's default subject and message live in `includes/survey-invite.php`,
which returns `['subject' => ..., 'body' => ...]`. Git ignores it, because it
signs off with a member's name and the group's inbox. The deploy bundle
carries it with the rest of `includes/`. Without it, the page falls back to the
short stand-in at the end of `includes/survey-text.php`.

Wording an admin saves on `/survey-admin` takes precedence over either until
somebody presses "Put back the default subject and message".

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
  --exclude 'router.php' --exclude 'tools/' --exclude '/data/' \
  ./ user@home123456789.1and1-data.host:/homepages/NN/dNNNNNN/htdocs/
```

The `/data/` exclude is what keeps `--delete` away from the survey's answers on
the host. Excluded paths are protected from deletion as well as from upload.
Never add `--delete-excluded` to that line.

Then check five things in a browser:

1. `/about` loads and the address bar keeps the clean URL.
2. `/includes/config.php` returns 403, not your settings.
3. The contact form sends and the mail arrives.
4. `/survey-admin` signs you in. Send yourself an invite, answer it, and
   check that both copies arrive.
5. `/data/key.php` returns 403 or 404. The folder only exists after step 4.

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
headers included. The survey's invites and copies land there too.

To try the survey locally, make a test account somewhere the deploy bundle
will not pick it up, and point the container at it:

```bash
DEFLOCK_SURVEY_ADMINS_FILE=data/dev-admins.php php tools/survey-admin.php add you@example.org
```

Then add `DEFLOCK_SURVEY_ADMINS_FILE: data/dev-admins.php` under
`environment` in `docker-compose.yml` for the session. Links in the emails use
`SITE_URL`, so swap its host for `127.0.0.1:8731` when you open one. The compose file also sets `DEFLOCK_FORM_SECRET`, which
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
