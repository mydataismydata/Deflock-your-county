<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$page  = 'about';
$title = 'About the group';
$blurb = SITE_NAME . ' is an unaffiliated group of county residents asking the county '
       . 'to end its automated license plate reader program.';

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="shell">
    <span class="eyebrow">About</span>
    <h1>We are normal people who do not want mass surveillance.</h1>
    <div class="hero__row">
      <p class="lede">
        We are residents of <?= e(COUNTY) ?>. No staff, no budget, no party
        affiliation and no connection to any vendor. What we have is the view
        that a camera network recording every driver in the county is an
        unreasonable search, and that the county should switch it off. Driving
        to the Publix is not probable cause.
      </p>
    </div>
  </div>
</section>

<section class="bay">
  <div class="shell">

    <span class="tag">How did this get in <?= e(COUNTY) ?>?</span>
    <div class="measure stack">
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
    </div>

  </div>
</section>

<section class="bay">
  <div class="shell">

    <span class="tag">What we can do now</span>
    <h2>Four things, in rough order of how much time they take</h2>

    <div class="rows mt-l">

      <div class="row">
        <h3>Show up</h3>
        <p>
          Commission meetings have a public comment period. Most of the time
          nobody uses it to talk about this.
        </p>
      </div>

      <div class="row">
        <h3>Explain it to neighbors</h3>
        <p>
          Most people have never heard of a Vehicle Fingerprint and are not
          pleased to learn what one is.
        </p>
      </div>

      <div class="row">
        <h3>File records requests</h3>
        <p>
          Contracts, invoices, usage policies, audit logs and data sharing
          agreements are public records under <?= e(RECORDS_LAW) ?>. We ask for
          them and publish what comes back.
        </p>
      </div>

      <div class="row">
        <h3>Map the hardware</h3>
        <p>
          Cameras get photographed and logged on
          <a href="https://deflock.org" rel="noopener">DeFlock</a>, an open map
          of reader locations built on OpenStreetMap. Anybody can add one.
        </p>
      </div>

    </div>

  </div>
</section>

<section class="bay">
  <div class="shell">

    <span class="tag">What we are asking for</span>
    <div class="measure stack">
      <p>
        <strong>End the program.</strong> Cancel the contract, take the cameras
        down, and delete what they have already collected. That is the ask.
      </p>
      <p>
        We do not campaign for a shorter retention period or a tighter search
        rule. Those arguments accept the record as legitimate and then haggle
        over its housekeeping. Photographing every car and keeping the result is
        a search of people nobody suspects, and the Fourth Amendment does not
        leave room for the county to run one.
      </p>
      <p>
        We mean the county taking the cameras down, by a vote, on the record. We
        do not touch the hardware and we will not help anyone who wants to.
        Damaging a camera hands the other side its argument and puts the person
        who did it in front of a judge.
      </p>
      <p>
        Deputies investigated crimes before these cameras went up and can
        investigate them after they come down. Where there is reason to suspect
        a person, a judge can issue a warrant. That is what a warrant is for.
      </p>
    </div>

  </div>
</section>

<section class="bay">
  <div class="shell">

    <span class="tag">How we operate</span>
    <h2>Rules we hold ourselves to</h2>

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
    <hr class="mb-0">
    <div class="measure mt-xl">
      <h2 class="h-sub">There is no membership to join</h2>
      <p>
        Speak at a commission meeting, or do not. Send one email to one
        commissioner and you have already done more than most people in the
        county. Start on the Get Involved page.
      </p>
      <p class="mb-0 mt-m">
        <a class="btn" href="/get-involved">See what to do &rarr;</a>
      </p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
