<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$page  = 'about';
$title = 'About';
$blurb = SITE_NAME . ' is an unaffiliated group of county residents asking the county '
       . 'to end its automated license plate reader program.';

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> About</p>
    <h1 class="display-1" data-reveal>We are <span class="accent">normal people</span> who do not want mass surveillance.</h1>
    <p class="lede lede--lg">
      We are residents of <?= e(COUNTY) ?>. No staff, no budget, no party
      affiliation and no connection to any vendor. What we have is the view that
      a camera network recording every driver in the county is an unreasonable
      search, and that the county should switch it off. Driving to the Publix is
      not probable cause.
    </p>
  </div>
</section>

<!-- =========================================================== how ==== -->
<section class="section" id="how">
  <div class="shell">
    <h2 class="h-section" data-reveal>How did this get in <?= e(COUNTY) ?>?</h2>
    <div class="prose mt-m">
      <p>
        Automated license plate readers arrived in <?= e(STATE) ?> jurisdictions
        the way most surveillance does. A vendor offers a trial. The trial
        produces an arrest. The arrest produces a press release. A renewal goes
        on a consent agenda and passes without discussion, and the county now
        runs a permanent record of who drove where.
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

<!-- =========================================================== now ==== -->
<section class="section" id="now">
  <div class="shell">
    <h2 class="h-section" data-reveal>What we can do now.</h2>
    <p class="lede lede--tight">Four things, in rough order of how much time they take.</p>

    <ul class="rows mt-m">
      <li>
        <span class="row-name">Show up</span>
        <p>Commission meetings have a public comment period. Most of the time nobody uses it to talk about this.</p>
      </li>
      <li>
        <span class="row-name">Explain it to neighbors</span>
        <p>Most people have never heard of a Vehicle Fingerprint and are not pleased to learn what one is.</p>
      </li>
      <li>
        <span class="row-name">File records requests</span>
        <p>
          Contracts, invoices, usage policies, audit logs and data sharing
          agreements are public records under <?= e(RECORDS_LAW) ?>. We ask for
          them and publish what comes back.
        </p>
      </li>
      <li>
        <span class="row-name">Map the hardware</span>
        <p>
          Cameras get photographed and logged on
          <a class="plain-link" href="https://deflock.org" rel="noopener">DeFlock</a>,
          an open map of reader locations built on OpenStreetMap. Anybody can
          add one.
        </p>
      </li>
    </ul>
  </div>
</section>

<!-- ======================================================= asking ==== -->
<section class="section" id="asking">
  <div class="shell">
    <h2 class="h-section" data-reveal>What we are asking for.</h2>
    <div class="prose mt-m">
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

<!-- ========================================================= rules ==== -->
<section class="section" id="rules">
  <div class="shell">
    <h2 class="h-section" data-reveal>Rules we hold ourselves to.</h2>
    <div class="defs">
      <div>
        <h3>Sources before adjectives</h3>
        <p>
          Every factual claim on this site should trace to a document, a
          recording or a named piece of reporting. If we cannot source it, we
          cut it. Tell us when we get one wrong and we will fix it in public.
        </p>
      </div>
      <div>
        <h3>Nothing illegal, nothing clever</h3>
        <p>
          Public records, public meetings, public streets. We do not touch
          hardware and we do not encourage anyone else to. It is also the
          fastest way to lose the argument.
        </p>
      </div>
      <div>
        <h3>Names are yours to give</h3>
        <p>
          You can attend, read, forward and donate nothing without ever telling
          us who you are. We keep no member list. The contact form delivers to
          one inbox and stores nothing on the server.
        </p>
      </div>
      <div>
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

<!-- ======================================================= closing ==== -->
<section class="section section--close">
  <div class="shell">
    <div class="panel">
      <h2 class="display-3">There is no membership to join.</h2>
      <p class="lede">
        Speak at a commission meeting, or do not. Send one email to one
        commissioner and you have already done more than most people in the
        county. Start on the Get Involved page.
      </p>
      <div class="pill-row">
        <a class="pill" href="/get-involved">See what to do &rarr;</a>
        <a class="pill pill--ghost" href="/contact">Contact us</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
