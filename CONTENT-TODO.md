# Before this site goes live

Two kinds of item below. Placeholders are blanks somebody has to fill in.
Claims are statements already written into the pages that need a source
attached or need cutting.

## Placeholders

**`includes/config.php`**

- `SITE_URL` is `https://example.org`.
- `CONTACT_TO` is `hello@example.org`.
- `CONTACT_FROM` is `website@example.org`. Must be a real mailbox on the live domain.
- `FORM_SECRET` is the shipped placeholder. The form will not send until this changes.
- `CONTACT_PHONE` is a burner, added 19 September 2026, printed on the contact page and
  nowhere else. It is a plain `tel:` link with no obfuscation, because obfuscation does not
  survive a scraper that runs JavaScript and it costs tap-to-call. If the spam gets bad the
  phone gets turned off; set both `CONTACT_PHONE` and `CONTACT_PHONE_TEL` to empty strings
  and the block disappears. The two must agree, and nothing checks that they do.
- `CHANNELS` are all empty strings, and nothing reads them any more. The Scene redesign
  dropped the "Follow along" block from Get Involved, which was the last thing that printed
  them. Either fill them in and add a block back, or delete the constant.
- `MEETINGS` carries the county board dates through 15 December 2026, read from the county
  calendar on 18 September 2026. Top them up when that list runs short. The group holds no
  meetings of its own; add them here if that changes.

**`includes/place.php`**

- `BODIES` carries all five commissioners with their office email and office phone, read from
  the county's district pages on 18 September 2026. Terms expire in 2026 for Districts 2 and 4
  and in 2028 for Districts 1, 3 and 5, so check the names after each election.
- The county also publishes a cell number for each commissioner. We list the office line only.
- Confirm whether St. Augustine Beach should be listed as a fourth body. It runs its own police department.
- `RECORDS_FEE_CAP` is set to $25. Check what St. Johns County actually charges before leaving that figure in the sample letter.

**`robots.txt`**

- Update the commented sitemap host, or delete the line.

**`assets/img/favicon.svg`, `favicon-32.png`, `favicon-180.png`**

- The group's mark: a white eye with a red slash on black, supplied 19 September 2026. The
  placeholder lens it replaced is gone. The header and footer print the site name in
  Instrument Serif and carry no mark, so these are the only place the logo appears today.
- The SVG is traced from the supplied 1024px original and is what modern browsers use. The
  two PNGs are cut from the same file: 32px for browsers with no SVG icon support, 180px for
  an iOS home screen, which ignores SVG either way.
- Redraw the SVG, not the PNGs, if the mark ever changes. Then re-cut the PNGs from it.

**Photographs**

The redesign runs on four images and wants three more. What is in `assets/img/` now:

| File | Where |
| --- | --- |
| `cameras.webp` | Home hero: a reader and a dome camera on a pole |
| `supreme-court.webp` | Home, "End the program" |
| `flag.webp` | Get Involved hero |
| `deflock-map.webp` | Resources hero |

Three home scenes have no photograph yet and print a mono caption in the corner naming the
shot they are waiting for:

- a reader at an intersection, shot from the public road
- a bare pole, or a camera coming down
- a resident at the public comment podium, no face toward camera

Until those exist the scenes render as near-black plates, which reads as deliberate. Drop the
`<span class="photo-note">` out of `index.php` if you would rather not advertise the gap. The
About and Contact heroes are text on black by design and need nothing.

## Claims that need a source before publishing

Each of these is written as fact on a page. Attach a link, or cut the sentence.

**`index.php`, "What the camera on the pole is doing"**

- That Flock Safety records body type, color, roof racks, bumper stickers and visible damage, and markets it as "Vehicle Fingerprint". Source: Flock's own product documentation.
- That Flock's standard configuration keeps captures for 30 days. Confirm against current vendor documentation, because defaults change.

**`index.php`, "The search is not local"**

- "Agencies outside St. Johns County can, will, and have searched for plates inside our county feed." The first two are true of how the vendor's sharing network is built. **"Have" is a claim about this county specifically and nothing on this site supports it yet.** Either get it from an audit log through a records request, or soften the sentence.

**Removed in the redesign, no longer needs sourcing**

- The 404 Media pull quote about immigration enforcement and the Texas pregnancy case is gone from the home page. If you put it back anywhere, it needs the original links.

**`index.php`, the Fourth Amendment quote**

- Quoted accurately and in the public domain. Nothing to check.

**Anything about St. Johns County specifically**

- No page yet states that St. Johns County operates plate readers, how many, or under what contract. **That blocker is now liftable.** The records folder linked from the Resources page already holds a redacted sheriff's office plate reader policy, Flock contract riders dating to September 2022, a 2025 expansion document and training material. Read them and write the county-specific section from the documents, citing each one.
- A wrong number is still the fastest way to lose the argument in a commission chamber. Quote the paperwork, link the file.

**The records folder is on Google Drive**

- It is public, which is what makes it useful. Two things follow. Anyone who clicks it hands Google a request, which sits awkwardly beside the footer promise about third parties, so it is worth saying on the page that the link leaves the site. And the folder stays useful only while its sharing setting stays "anyone with the link"; if that is ever tightened the Resources page will point at a sign-in wall.

## Links

Every outbound link on the site was fetched and checked on 15 September 2026.
All 25 resolve. Two notes:

- **deflock.me now redirects to deflock.org.** Every link was moved to the .org
  address, so there is no redirect hop.
- **The Sunshine Manual link was dead.** `myfloridalegal.com/sunshine-manual`
  returned 404; the Attorney General moved it under `/open-government/`. Fixed,
  and the page it now points at carries the 2025 edition.

`ij.org` and `muckrock.com` answer 403 to automated requests. Both are behind
bot protection and load normally in a browser; that is not a broken link.

Recheck this list before launch and once a quarter after. Government sites
reorganise without redirects, which is how the Sunshine Manual link broke.

## Wording to review

- The About page says the group is unaffiliated and has no budget. Change it if either becomes untrue.
- The About page commits to not helping anyone interfere with equipment. Keep that line. It is the answer to the first bad-faith question you will get.
- The footer says the site sets no cookies and runs no analytics. That is true of what is here now. It stops being true the moment anyone adds an embed.
