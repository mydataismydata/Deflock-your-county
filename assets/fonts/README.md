# Fonts

Four families, self-hosted. The site loads nothing from a third party, which is
what the footer promises, so these are served from this directory rather than
from a font CDN.

| File | Family | Weights | Used for |
| --- | --- | --- | --- |
| `dm-serif-display-400.woff2` | DM Serif Display | 400 | every heading |
| `instrument-serif-400.woff2` | Instrument Serif | 400 | the wordmark, and nothing else |
| `ibm-plex-sans-var.woff2` | IBM Plex Sans | variable 100–700 | body copy and the interface |
| `geist-mono-var.woff2` | Geist Mono | variable 100–900 | labels, dates, scripts |

IBM Plex Sans and Geist Mono ship as variable fonts, so one file covers every
weight the site uses. The `@font-face` rules in `assets/css/site.css` declare
the axis range rather than a single weight.

All four are latin subsets from Google Fonts, which is the same binary Google
serves. Every one is under the SIL Open Font License 1.1.

To refresh them, request the CSS with a browser user agent and take the URL from
the block whose `unicode-range` starts `U+0000-00FF`:

    curl -s -A "Mozilla/5.0" \
      "https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@100..700&display=swap"
