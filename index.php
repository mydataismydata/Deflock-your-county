<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$page  = 'home';
$title = 'Plate readers in ' . COUNTY;
$blurb = 'Automated license plate readers photograph and log every vehicle that passes '
       . 'them. ' . SITE_NAME . ' is a county residents group organizing for a public '
       . 'say in that.';

require __DIR__ . '/includes/header.php';
?>

<section class="hero hero--sky">
  <div class="clouds" aria-hidden="true">
    <i class="cloud"></i><i class="cloud"></i><i class="cloud"></i><i class="cloud"></i><i class="cloud"></i>
  </div>
  <div class="shell">
    <span class="eyebrow"><?= e(SITE_TAGLINE) ?></span>
    <h1>You are being recorded, profiled and tracked</h1>
    <div class="hero__row">
      <p class="lede">
        "Automated License Plate Readers", or ALPRs, are not passively taking
        snapshots of license plates. They are recording your plate, your car
        make &amp; model, your bumper stickers and tracking your routes. These
        do not just track plates, nor only criminals with warrants &mdash; they
        track every law-abiding driver in <?= e(COUNTY) ?>.
      </p>
      <div class="hero__cta">
        <a class="btn" href="/get-involved">Get involved &rarr;</a>
        <a class="scroll-hint" href="#problem">Scroll</a>
      </div>
    </div>
  </div>
</section>

<section class="bay" id="problem">
  <div class="shell">

    <span class="tag">The problem</span>
    <h2>What ALPRs do</h2>

    <div class="measure stack">
      <p>
        An Automated License Plate Reader points at a lane of traffic and fires
        as each vehicle passes. Software reads the plate and the issuing state.
        Flock Safety, the vendor behind many of these installations, also
        records body type, color, roof racks, bumper stickers and visible
        damage, and markets the combination as a "Vehicle Fingerprint" that can
        find a car whose plate was never captured.
      </p>
      <p>
        Each hit is stamped with a time and the camera's location. String a few
        cameras together and you have a partial map of where a car went and when.
        String a few hundred together and you have a travel history for everyone
        in the county.
      </p>
      <p>
        None of this begins with a suspect. The database is built first, from
        everybody, and searched afterwards.
      </p>
    </div>

    <div class="rows mt-xl">

      <div class="row">
        <span class="row__num">1</span>
        <h3>No warrant stands between</h3>
        <p>
          We already have a system in place which allows law enforcement to
          track a suspect &mdash; a judge issues a warrant. This process
          bypasses that entirely.
        </p>
      </div>

      <div class="row">
        <span class="row__num">2</span>
        <h3>The record outlasts the trip</h3>
        <p>
          Flock's standard configuration keeps captures for 30 days before
          deletion. Agencies can and do change that number, and anything
          exported into a case file stops being covered by it at all.
        </p>
      </div>

      <div class="row">
        <span class="row__num">3</span>
        <h3>The search is not local</h3>
        <p>
          Agencies outside <?= e(COUNTY) ?> can, will, and have searched for
          plates inside our county feed.
        </p>
      </div>

    </div>

  </div>
</section>

<section class="bay">
  <div class="shell">

    <span class="tag">What we want</span>
    <h2>We were warned about mass surveillance</h2>
    <p class="measure quote">
      "The right of the people to be secure in their persons, houses, papers,
      and effects, against unreasonable searches and seizures, shall not be
      violated, and no Warrants shall issue, but upon probable cause, supported
      by Oath or affirmation, and particularly describing the place to be
      searched, and the persons or things to be seized."
    </p>

    <div class="asks mt-xl">
      <div>
        <span class="ask__num">1</span>
        <h3>Publish the contract</h3>
        <p>
          Residents should be able to read what the county bought, what it
          costs, how long it runs and what the vendor is allowed to do with the
          data.
        </p>
      </div>
      <div>
        <span class="ask__num">2</span>
        <h3>No recording without a warrant</h3>
        <p>
          Keeping a record of where a person has driven for the past month is a
          search. It should take a judge, with the narrow exceptions courts
          already recognize.
        </p>
      </div>
      <div>
        <span class="ask__num">3</span>
        <h3>Put an end date on it</h3>
        <p>
          Renewal should require a public hearing and an affirmative vote, not a
          line item that rolls over while nobody is looking.
        </p>
      </div>
    </div>

    <div class="btn-row">
      <a class="btn" href="/get-involved#officials">Send these to your commissioner &rarr;</a>
    </div>

  </div>
</section>

<section class="bay">
  <div class="shell">

    <span class="tag">What can you do?</span>
    <h2>Here's how you can help</h2>

    <div class="grid grid--3 mt-l">

      <a class="card" href="/get-involved">
        <h3>Get involved</h3>
        <p>
          Meeting dates, who to contact, and core concepts for the three minutes
          you get at public comment.
        </p>
        <span class="card__more">Take an action &rarr;</span>
      </a>

      <a class="card" href="/resources">
        <h3>Resources</h3>
        <p>
          Tools and links that you can use to get informed about Flock and
          ALPRs.
        </p>
        <span class="card__more">Find out more &rarr;</span>
      </a>

      <a class="card" href="/about">
        <h3>About us</h3>
        <p>
          Who is behind this, what we are asking for, and what we are not
          asking for.
        </p>
        <span class="card__more">Meet the group &rarr;</span>
      </a>

    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
