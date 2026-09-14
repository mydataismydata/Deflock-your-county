<?php
declare(strict_types=1);

$page  = 'home';
$title = 'Plate readers in St. Johns County';
$blurb = 'Automated license plate readers photograph and log every vehicle that passes them. Deflock St. Johns is a county residents group organizing for a public say in that.';

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="shell">
    <span class="eyebrow">St. Johns County, Florida</span>
    <h1>Your car is photographed, logged and searchable.</h1>
    <p class="lede">
      An automated license plate reader records every vehicle that drives past it.
      Not the stolen ones. Not the ones on a warrant list. Every one. Deflock
      St. Johns is a group of county residents who think a decision like that
      belongs in public, on the record, with a vote attached.
    </p>
    <div class="btn-row">
      <a class="btn btn--loud" href="/get-involved">Get involved</a>
      <a class="btn btn--ghost" href="#problem">Read the problem</a>
    </div>
  </div>
</section>

<section class="bay" id="problem">
  <div class="shell">

    <span class="eyebrow">The problem</span>
    <h2>What the camera on the pole is doing</h2>

    <div class="measure stack">
      <p>
        A fixed plate reader points at a lane of traffic and fires as each
        vehicle passes. Software reads the plate and the issuing state. Flock
        Safety, the vendor behind most of these installations, also records body
        type, color, roof racks, bumper stickers and visible damage, and markets
        the combination as a "Vehicle Fingerprint" that can find a car whose
        plate was never captured.
      </p>
      <p>
        Each hit is stamped with a time and the camera's location. String a few
        cameras together and you have a partial map of where a car went and when.
        String a few hundred together and you have something closer to a travel
        history for everyone in the county.
      </p>
      <p>
        None of this begins with a suspect. The database is built first, from
        everybody, and searched afterwards.
      </p>
    </div>

    <div class="grid grid--3 mt-xxl">

      <div class="card">
        <h3>The record outlasts the trip</h3>
        <p>
          Flock's standard configuration keeps captures for 30 days before
          deletion. Agencies can and do change that number, and anything
          exported into a case file stops being covered by it at all.
        </p>
      </div>

      <div class="card">
        <h3>The search is not local</h3>
        <p>
          Agencies can open their cameras to other agencies, including
          departments in other states. A camera bought with St. Johns County
          money can answer a question asked a thousand miles away.
        </p>
      </div>

      <div class="card">
        <h3>No warrant stands between</h3>
        <p>
          A query is typed into a web console. Whether an officer needs a judge
          to sign off first depends on department policy, and most policies are
          written by the department.
        </p>
      </div>

    </div>

    <div class="flagline">
      Reporting by 404 Media in 2025 documented plate reader searches run for
      immigration enforcement and, in one Texas case, for a woman whose family
      reported she had ended a pregnancy. The cameras did not decide that. The
      people with console access did.
      <cite>Verify and link the original reporting before this page goes live.</cite>
    </div>

  </div>
</section>

<section class="bay bay--raised">
  <div class="shell">

    <span class="eyebrow">What we want</span>
    <h2>Four asks, none of them radical</h2>
    <p class="measure lede">
      We are not asking anyone to ignore a crime. We are asking that a system
      that watches everyone be governed like one.
    </p>

    <ol class="steps mt-xl w-form">
      <li>
        <h3>Publish the contract</h3>
        <p>
          Residents should be able to read what the county bought, what it
          costs, how long it runs and what the vendor is allowed to do with the
          data.
        </p>
      </li>
      <li>
        <h3>Require a warrant to search</h3>
        <p>
          Writing down where a person has driven for the past month is a search.
          It should take a judge, with the narrow exceptions courts already
          recognize.
        </p>
      </li>
      <li>
        <h3>Publish the audit log</h3>
        <p>
          Every query leaves a record: who ran it, when, and the case number
          they attached. A quarterly summary of that log, released publicly,
          costs the county an afternoon.
        </p>
      </li>
      <li>
        <h3>Put an end date on it</h3>
        <p>
          Renewal should require a public hearing and an affirmative vote, not a
          line item that rolls over while nobody is looking.
        </p>
      </li>
    </ol>

    <div class="btn-row">
      <a class="btn" href="/get-involved#officials">Send these to your commissioner</a>
    </div>

  </div>
</section>

<section class="bay">
  <div class="shell">

    <span class="eyebrow">Where to go next</span>
    <h2>Pick a door</h2>

    <div class="grid grid--2 mt-l">

      <a class="card" href="/get-involved">
        <h3>Get involved</h3>
        <p>
          Meeting dates, who to call, and a script for the three minutes you get
          at public comment.
        </p>
        <span class="card__more">Take an action &rarr;</span>
      </a>

      <a class="card" href="/resources">
        <h3>Resources</h3>
        <p>
          The camera map, the legal groundwork, the reporting, and how to
          request county records yourself.
        </p>
        <span class="card__more">Read up &rarr;</span>
      </a>

      <a class="card" href="/about">
        <h3>About us</h3>
        <p>
          Who is behind this, what we are asking for, and what we are not
          asking for.
        </p>
        <span class="card__more">Meet the group &rarr;</span>
      </a>

      <a class="card" href="/contact">
        <h3>Contact</h3>
        <p>
          Spotted a camera, have a record to share, or want to help. One form,
          one inbox.
        </p>
        <span class="card__more">Say something &rarr;</span>
      </a>

    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
