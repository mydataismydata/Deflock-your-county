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
- `CHANNELS` are all empty strings. The footer and Get Involved page print a fallback line while they stay empty.
- `MEETINGS` both say `TBD`.

**`get-involved.php`**

- The `$bodies` array has no commissioner names, email addresses or phone numbers. Every district currently renders as "name and email to be added".
- Confirm the Board of County Commissioners still has five district seats and that the address in `MEETINGS` is current.
- Confirm whether St. Augustine Beach should be listed as a fourth body. It runs its own police department.

**`robots.txt`**

- Update the commented sitemap host, or delete the line.

**`assets/img/favicon.svg`**

- Placeholder mark: a lens with a slash through it. Replace if the group adopts a logo.

## Claims that need a source before publishing

Each of these is written as fact on a page. Attach a link, or cut the sentence.

**`index.php`, "What the camera on the pole is doing"**

- That Flock Safety records body type, color, roof racks, bumper stickers and visible damage, and markets it as "Vehicle Fingerprint". Source: Flock's own product documentation.
- That Flock's standard configuration keeps captures for 30 days. Confirm against current vendor documentation, because defaults change.

**`index.php`, the pull quote**

- The 404 Media reporting on searches run for immigration enforcement, and the Texas case involving a woman whose family reported she had ended a pregnancy. Link the original articles. The page currently carries a visible note saying this is unverified, which needs removing once the links are in.

**`index.php`, "No warrant stands between"**

- That whether a warrant is required depends on department policy. True as a general statement about most jurisdictions. Check whether St. Johns County has published a policy that says otherwise before leaving it.

**Anything about St. Johns County specifically**

- Nothing on this site yet states that St. Johns County operates plate readers, how many, or under what contract. Establish that through a records request before adding it. A wrong number is the fastest way to lose the argument in a commission chamber.

## Links to test

Every external link was written from memory and none has been fetched. Check
each one resolves before publishing, particularly:

- `https://www.myfloridalegal.com/sunshine-manual` (the Attorney General's site has been reorganized)
- `https://www.eff.org/issues/automated-license-plate-readers-alpr`
- `https://www.sjcfl.us`, `https://www.sjso.org`, `https://www.citystaug.com`

## Wording to review

- The About page says the group is unaffiliated and has no budget. Change it if either becomes untrue.
- The About page commits to not helping anyone interfere with equipment. Keep that line. It is the answer to the first bad-faith question you will get.
- The footer says the site sets no cookies and runs no analytics. That is true of what is here now. It stops being true the moment anyone adds an embed.
