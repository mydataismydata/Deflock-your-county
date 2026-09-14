<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$page  = 'about';
$title = 'About the group';
$blurb = SITE_NAME . ' is an unaffiliated group of county residents working on how '
       . 'automated license plate readers are bought, governed and audited here.';

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="shell">
    <span class="eyebrow">About</span>
    <h1>Neighbors, reading contracts.</h1>
    <p class="lede">
      We are residents of <?= e(COUNTY) ?>. No staff, no budget, no party
      affiliation and no connection to any vendor. What we have is time, a
      public records law, and the view that surveillance bought with county
      money should be governed by the county's residents.
    </p>
  </div>
</section>

<section class="bay">
  <div class="shell">
    <div class="measure stack">

      <h2>Why this, and why here</h2>
      <p>
        Automated license plate readers arrived in <?= e(STATE) ?> jurisdictions the way
        most surveillance does. A vendor offers a trial. The trial produces an
        arrest. The arrest produces a press release. A renewal goes on a consent
        agenda and passes without discussion, and the county now runs a
        permanent record of who drove where.
      </p>
      <p>
        Nobody set out to build that. It assembles itself one uncontested
        renewal at a time, which is exactly why the interruption has to come
        from outside the building.
      </p>
      <p>
        <?= e(COUNTY) ?> is not unusual. That is the point. The same vendor
        pitch is running in counties across the country, and the response has to
        be local because the contracts are local.
      </p>

      <h2>What we actually do</h2>
      <p>
        Four things, in rough order of how much time they take.
      </p>

      <ul class="ticks">
        <li>
          <strong>Map the hardware.</strong> Cameras get photographed and logged
          on <a href="https://deflock.me" rel="noopener">DeFlock</a>, an open map
          of reader locations built on OpenStreetMap. Anybody can add one.
        </li>
        <li>
          <strong>File records requests.</strong> Contracts, invoices, usage
          policies, audit logs and data sharing agreements are public records
          under <?= e(RECORDS_LAW) ?>. We ask for them and publish what comes
          back.
        </li>
        <li>
          <strong>Show up.</strong> Commission meetings have a public comment
          period. Most of the time nobody uses it to talk about this.
        </li>
        <li>
          <strong>Explain it to neighbors.</strong> Most people have never heard
          of a Vehicle Fingerprint and are not pleased to learn what one is.
        </li>
      </ul>

      <h2>What we are not asking for</h2>
      <p>
        It saves everyone time to be blunt about this, because it is the first
        thing we get accused of.
      </p>
      <p>
        We are not asking anyone to stop investigating crimes. We are not asking
        for deputies to be defunded, and we take no position on the sheriff's
        budget. We are not asking residents to break, obscure or interfere with
        any camera, and we will not help anyone who wants to.
      </p>
      <p>
        We are asking that a system which records everybody be subject to a
        published policy, a warrant requirement, a public audit and a renewal
        vote. That is the whole ask.
      </p>

    </div>
  </div>
</section>

<section class="bay bay--raised">
  <div class="shell">

    <span class="eyebrow">How we operate</span>
    <h2>Four rules we hold ourselves to</h2>

    <div class="grid grid--2 mt-l">

      <div class="card">
        <h3>Sources before adjectives</h3>
        <p>
          Every factual claim on this site should trace to a document, a
          recording or a named piece of reporting. If we cannot source it, we
          cut it. Tell us when we get one wrong and we will fix it in public.
        </p>
      </div>

      <div class="card">
        <h3>Nothing illegal, nothing clever</h3>
        <p>
          Public records, public meetings, public streets. We do not touch
          hardware and we do not encourage anyone else to. It is also the
          fastest way to lose the argument.
        </p>
      </div>

      <div class="card">
        <h3>Names are yours to give</h3>
        <p>
          You can attend, read, forward and donate nothing without ever telling
          us who you are. We keep no member list. The contact form delivers to
          one inbox and stores nothing on the server.
        </p>
      </div>

      <div class="card">
        <h3>Not a partisan project</h3>
        <p>
          Objections to warrantless location tracking come from across the
          political map, which is the only reason this kind of campaign ever
          wins a county vote. We keep it that way on purpose.
        </p>
      </div>

    </div>

  </div>
</section>

<section class="bay">
  <div class="shell">
    <div class="slab measure">
      <h2 class="h-sub">There is no membership to join</h2>
      <p>
        Come to a meeting, or do not. Send one email to one commissioner and you
        have already done more than most people in the county. Start on the
        Get Involved page.
      </p>
      <p class="mb-0">
        <a class="btn btn--invert" href="/get-involved">See what to do</a>
      </p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
