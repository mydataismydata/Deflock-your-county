# Fonts

`assets/css/site.css` declares `@font-face` for three files that are not in this
repository:

- `archivo-variable.woff2` — Archivo, variable weight 100 to 900
- `ibm-plex-mono-regular.woff2` — IBM Plex Mono 400
- `ibm-plex-mono-medium.woff2` — IBM Plex Mono 500

Both families are licensed under the SIL Open Font License. Download the woff2
files from Google Fonts, or from google-webfonts-helper for ready-made subsets,
and drop them in this directory using exactly those names.

They are not committed because nobody has checked the licence file into this
repo yet. Add `OFL.txt` alongside them when you do.

Until the files exist the browser falls back to the system stack named in
`--face` and `--face-mono`. The site renders correctly either way, which is why
this is not a blocker. Each declaration carries `font-display: swap`, so text is
never invisible while a font is being fetched.

Self-hosting rather than loading from a font CDN is deliberate. The footer tells
visitors the site loads nothing from a third party, and an anti-surveillance
site that quietly reports every pageview to Google would deserve the ridicule.
