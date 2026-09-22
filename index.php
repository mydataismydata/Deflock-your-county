<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$page  = 'home';
$title = '';
$blurb = 'Automated license plate readers photograph and log every vehicle that passes '
       . 'them. ' . SITE_NAME . ' is a county residents group asking the county to shut '
       . 'the program down.';

require __DIR__ . '/includes/header.php';
?>

<!-- ========================================================== hero ==== -->
<section class="scene" data-scene>
  <img class="scene__media scene__media--pole drift" src="/assets/img/cameras.webp"
       alt="A Flock Safety plate reader and a dome camera mounted on a wooden utility pole against a black sky"
       width="1264" height="848" fetchpriority="high">
  <!-- The lens of the reader, at its coordinates in the photograph. The viewBox
       and the slice keep it there at every window size. -->
  <svg class="lensring" viewBox="0 0 1264 848" preserveAspectRatio="xMidYMid slice"
       aria-hidden="true" focusable="false">
    <!-- Radius 47 about (834, 324), in the photograph's own pixels. One unit
         is a little under one screen pixel at a typical window, so nudge it in
         ones. The quadrant from three o'clock to six o'clock is a curve rather
         than an arc: it leaves and rejoins the circle on the hour, and at its
         midpoint it runs 2 units inside it. The other three quadrants are
         circular. -->
    <path class="lensring__arc" pathLength="100"
          d="M 881 324
             C 881 346.186 856.186 371 834 371
             A 47 47 0 0 1 787 324
             A 47 47 0 0 1 834 277
             A 47 47 0 0 1 881 324
             Z"/>
  </svg>

  <div class="scene__scrim" aria-hidden="true"></div>

  <div class="scene__inner shell shell--narrow">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> <?= e(SITE_TAGLINE) ?></p>
    <h1 class="h-scene" data-reveal>You are being <span class="accent">recorded, profiled and tracked</span>.</h1>
    <p class="lede">
      "Automated License Plate Readers", or ALPRs, do not take passive snapshots
      of license plates. They record your plate, your car make and model, your
      bumper stickers and the route you drove. Every driver in <?= e(COUNTY) ?>
      is in that record, leaving them vulnerable to constitutionally illegal,
      warrantless searches and misidentification for crimes they did not commit
      by AI with an alarming error rate. We are asking the county to shut the
      program down.
    </p>
    <div class="pill-row">
      <a class="pill" href="/get-involved">Get involved &rarr;</a>
      <a class="pill pill--ghost" href="#problem">What ALPRs do</a>
    </div>
  </div>
</section>

<!-- ======================================================= problem ==== -->
<section class="scene" id="problem" data-scene>
  <div class="scene__media scene__media--blank" aria-hidden="true">
    <span class="photo-note">photograph &middot; a reader at an intersection, shot from the public road</span>
  </div>
  <div class="scene__scrim" aria-hidden="true"></div>

  <div class="scene__inner shell shell--wide split split--wide-right">
    <div>
      <p class="eyebrow"><span class="dot" aria-hidden="true"></span> The problem</p>
      <h2 class="h-scene" data-reveal>What ALPRs do.</h2>
      <p class="note mt-s">
        An Automated License Plate Reader points at a lane of traffic and fires
        as each vehicle passes. None of this begins with a suspect. The database
        is built first, from everybody, and searched afterwards.
      </p>
    </div>

    <ul class="rows">
      <li>
        <span class="row-name">A vehicle fingerprint</span>
        <p>
          Software reads the plate and the issuing state. Flock Safety, the
          vendor behind many of these installations, also records body type,
          color, roof racks, bumper stickers and visible damage, and markets the
          combination as a "Vehicle Fingerprint" that can find a car whose plate
          was never captured.
        </p>
      </li>
      <li>
        <span class="row-name">A travel history</span>
        <p>
          Each hit is stamped with a time and the camera's location. String a
          few hundred cameras together and you have a travel history for
          everyone in the county.
        </p>
      </li>
      <li>
        <span class="row-name">No warrant stands between</span>
        <p>
          We already have a system in place which allows law enforcement to
          track a suspect: a judge issues a warrant. This process bypasses that
          entirely.
        </p>
      </li>
      <li>
        <span class="row-name">The record outlasts the trip</span>
        <p>
          Flock's standard configuration keeps captures for 30 days before
          deletion. Agencies can and do change that number, and anything
          exported into a case file stops being covered by it at all.
        </p>
      </li>
      <li>
        <span class="row-name">The search is not local</span>
        <p>
          Agencies outside <?= e(COUNTY) ?> can, will, and have searched for
          plates inside our county feed.
        </p>
      </li>
    </ul>

    <p class="mt-m">
      <a class="arrow-link" href="/anonymized">They will tell you the data is anonymized
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
      </a>
    </p>
  </div>
</section>

<!-- ============================================== end the program ==== -->
<section class="scene scene--snug" data-scene>
  <img class="scene__media scene__media--court drift drift--mid" src="/assets/img/supreme-court.webp"
       alt="The west front of the United States Supreme Court building, in black and white, with the words Equal Justice Under Law on the pediment"
       width="1500" height="1020" loading="lazy">
  <div class="scene__scrim" aria-hidden="true"></div>

  <div class="scene__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> What we want</p>
    <h2 class="h-scene" data-reveal>End the program.</h2>
    <p class="lede">
      A warrant needs probable cause and a description of the place to be
      searched. A camera that photographs every car has neither. In
      <a class="plain-link" href="https://www.law.cornell.edu/supremecourt/text/16-402" rel="noopener">Carpenter
      v. United States</a> the Supreme Court held that pulling a person's
      historical location records is a search and needs a warrant. A plate
      reader network builds that record for the whole county before anybody asks
      for it.
    </p>
    <p class="quote mt-l">
      Photographing everyone does not become reasonable because the paperwork
      around it improves.
    </p>
  </div>
</section>

<!-- ========================================================== asks ==== -->
<section class="scene" data-scene>
  <div class="scene__media scene__media--blank" aria-hidden="true">
    <span class="photo-note">photograph &middot; a bare pole, or a camera coming down</span>
  </div>
  <div class="scene__scrim" aria-hidden="true"></div>

  <div class="scene__inner shell shell--wide">
    <h2 class="h-scene" data-reveal>Our objective.</h2>
    <p class="lede">
      Our position is that the program is an unreasonable search and that the
      county should end it. Shorter retention periods or tighter sharing won't
      resolve it.
    </p>

    <ol class="steps">
      <li>
        <h3>Cancel the contract</h3>
        <p>Vote against the next renewal, and end the term that is running as soon as the contract allows.</p>
      </li>
      <li>
        <h3>Remove all ALPR cameras</h3>
        <p>
          Every county ALPR camera must be removed from <?= e(COUNTY) ?>
          roadways. We can opt out of visiting retailers or private
          neighborhoods with Flock cameras; we can't opt out of driving to work.
        </p>
      </li>
      <li>
        <h3>Delete what was collected</h3>
        <p>Including the copies on the vendor's servers and anything shared with agencies outside the county. Ask each of them to confirm it in writing.</p>
      </li>
    </ol>

    <p class="mt-l">
      <a class="arrow-link" href="/get-involved#officials">Send these to your commissioner
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
      </a>
    </p>
  </div>
</section>

<!-- ======================================================= closing ==== -->
<section class="scene scene--snug" data-scene>
  <div class="scene__media scene__media--blank" aria-hidden="true">
    <span class="photo-note">photograph &middot; a resident at the public comment podium, no face toward camera</span>
  </div>
  <div class="scene__scrim" aria-hidden="true"></div>

  <div class="scene__inner shell shell--narrow">
    <h2 class="h-scene" data-reveal>Put your name on the record.</h2>
    <p class="lede">
      One email to one commissioner puts a resident's name on the record against
      a contract that usually passes in silence. Fifteen minutes is enough to
      start.
    </p>
    <div class="pill-row">
      <a class="pill" href="/get-involved">Get involved &rarr;</a>
      <a class="pill pill--ghost" href="/resources">Read the resources</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
