<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$page  = 'anonymized';
$title = 'Anonymized tracking is still tracking';
$blurb = 'Why "anonymized" license plate reader data is not anonymous, what it is worth, '
       . 'and what de-identification actually removes.';

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero__media" aria-hidden="true">
    <img class="hero__media--network drift" src="/assets/img/network.webp" alt=""
         width="1024" height="1024" fetchpriority="high">
    <div class="hero__vignette"></div>
    <div class="hero__scrim"></div>
  </div>

  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> The anonymization claim</p>
    <h1 class="display-1" data-reveal>Anonymized tracking is <span class="accent">still tracking</span>.</h1>
    <p class="lede lede--lg">
      The claim that this data is truly anonymous, or "scrubbed," is a
      foundational element of the surveillance industry's marketing, designed to
      appease privacy concerns while preserving the commercial and operational
      utility of the database. In reality, the value of this data is immense,
      and the concept of "anonymization" in the context of persistent,
      location-based tracking is a fiction.
    </p>
  </div>
</section>

<!-- ======================================================== pattern ==== -->
<section class="section" id="pattern">
  <div class="shell">
    <h2 class="h-section" data-reveal>The myth of anonymization in spatiotemporal data.</h2>
    <p class="lede lede--tight">
      Here is the breakdown of why this data remains highly valuable and how it
      exposes individuals despite claims of redaction.
    </p>

    <div class="prose mt-m">
      <p>
        In the world of data science, location data, meaning records of where a
        specific vehicle is at a specific time, is <strong>inherently
        non-anonymous</strong>. Even if you remove a name or a plate number, you
        are left with a "pattern of life."
      </p>

      <p class="callout">
        If an unidentified vehicle is seen entering a community every night at
        6:00 PM and at a specific office building every morning at 8:30 AM, you
        have essentially <strong>identified the individual</strong> associated
        with that vehicle without ever needing a license plate number.
      </p>

      <p>
        By correlating these movement patterns with other public datasets, such
        as property records, voter rolls and social media check-ins, "connecting
        the dots" is not just possible; it is trivial for automated systems.
      </p>
    </div>
  </div>
</section>

<!-- ========================================================== value ==== -->
<section class="section" id="value">
  <div class="shell">
    <h2 class="h-section" data-reveal>The value of "anonymized" aggregates.</h2>
    <p class="lede lede--tight">
      Even without identifying specific individuals, the data is a goldmine for
      several reasons.
    </p>

    <ul class="rows mt-m">
      <li>
        <span class="row-name">Predictive modeling</span>
        <p>
          Selling aggregate traffic patterns, peak congestion times, and
          movement flow between jurisdictions is extremely valuable to
          commercial interests, urban planners, and insurance companies.
        </p>
      </li>
      <li>
        <span class="row-name">Behavioral profiling</span>
        <p>
          Corporations and intelligence gatherers use "anonymized" movement data
          to identify high-value targets, track the influence of political
          events, or map the social and economic activity of specific
          neighborhoods, all of which are actionable without knowing the name of
          every driver.
        </p>
      </li>
    </ul>
  </div>
</section>

<!-- ======================================================= de-ident ==== -->
<section class="section section--media" id="de-identification">
  <div class="section__media" aria-hidden="true">
    <img class="drift drift--mid" src="/assets/img/redacted-plate.webp" alt=""
         width="1024" height="1024" loading="lazy">
    <div class="section__vignette"></div>
  </div>

  <div class="shell">
    <h2 class="h-section" data-reveal>The strategy of de-identification.</h2>

    <div class="prose mt-m">
      <p>
        When companies claim they scrub personally identifiable information,
        they are usually referring to stripping out the name, address, or owner
        info linked directly to the plate in their internal database. However,
        <strong>they retain the "unique identifier"</strong> of the plate
        itself. Because the plate is persistent, it acts as a permanent tracking
        ID.
      </p>
      <p>
        The system doesn't need to know the driver's name to know that the same
        vehicle, meaning the same person, has been spotted 400 times in a month.
        The tracking remains continuous; only the link to the government
        registration record is ostensibly "hidden" from certain users.
      </p>
      <p>
        This is not merely a technical issue; it is a <strong>feature of the
        surveillance state</strong>. By creating a massive, centralized, and
        "anonymized" database, Flock and its partners achieve two goals:
      </p>
    </div>

    <ul class="bullets">
      <li>They can claim they are not tracking "people," just "data points."</li>
      <li>
        They build a permanent, searchable historical record of human movement
        that is available to any entity with enough money or the right
        "mutual aid" access.
      </li>
    </ul>
  </div>
</section>

<!-- ======================================================== closing ==== -->
<section class="section">
  <div class="shell">
    <h2 class="h-section" data-reveal>In short, "anonymized" tracking is still tracking.</h2>
    <div class="prose mt-m">
      <p>
        The ability to <strong>identify an individual</strong> through movement
        patterns is a standard capability of modern data analytics. When you
        possess the coordinates and timestamps for a specific vehicle's daily
        habits, you have <strong>stripped away the privacy</strong> of the
        person operating that vehicle, regardless of whether you have attached a
        name to the file.
      </p>
    </div>
  </div>
</section>

<section class="section section--close">
  <div class="shell">
    <div class="panel">
      <h2 class="display-3">Your county taxes pay for all of this.</h2>
      <p class="lede">
        The county commissioners approve the sheriff's budget and the renewal
        cost. Email your commissioner to let them know that
        <em>we the people</em> are against this.
      </p>
      <div class="pill-row">
        <a class="pill" href="/get-involved#officials">Email your commissioner &rarr;</a>
        <a class="pill pill--ghost" href="/resources">Read the primary sources</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
