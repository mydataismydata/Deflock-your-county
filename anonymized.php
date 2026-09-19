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
      The claim that this data is truly anonymous or "scrubbed" is a foundational
      element of the surveillance industry's marketing, designed to appease
      privacy concerns while preserving the commercial and operational utility
      of the database. In reality, the value of this data is immense, and the
      concept of "anonymization" in the context of persistent, location-based
      tracking is a fiction.
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
        specific vehicle is at a specific time, is inherently non-anonymous.
        Even if you remove a name or a plate number, you are left with a
        "pattern of life." If an unidentified vehicle is seen entering a
        community every night at 6:00 PM and at a specific office building every
        morning at 8:30 AM, you have essentially identified the individual
        associated with that vehicle without ever needing a license plate
        number. By correlating these movement patterns with other public
        datasets, such as property records, voter rolls and social media
        check-ins, "connecting the dots" is not just possible; it is trivial for
        automated systems.
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
        <span class="row-name">Network effect</span>
        <p>
          The more data Flock collects, the more valuable their system becomes
          to law enforcement agencies. By hosting data from thousands of
          jurisdictions, they create a national dragnet. The value lies in the
          "connective tissue" of the network, being able to track a vehicle
          across state lines or county borders.
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
        When companies claim they scrub PII, meaning personally identifiable
        information, they are usually referring to stripping out the name,
        address, or owner info linked directly to the plate in their internal
        database. However, they retain the "unique identifier" of the plate
        itself. Because the plate is persistent, it acts as a permanent tracking
        ID.
      </p>
      <p>
        The system doesn't need to know the driver's name to know that the same
        vehicle, meaning the same person, has been spotted 400 times in a month.
        The tracking remains continuous; only the link to the government
        registration record is ostensibly "hidden" from certain users.
      </p>
    </div>
  </div>
</section>

<!-- ================================================== institutional ==== -->
<section class="section" id="institutional">
  <div class="shell">
    <h2 class="h-section" data-reveal>The institutional reality.</h2>
    <p class="lede lede--tight">
      This is not merely a technical issue; it is a feature of the surveillance
      state. By creating a massive, centralized, and "anonymized" database, Flock
      and its partners achieve two goals.
    </p>

    <div class="defs">
      <div>
        <h3>Liability mitigation</h3>
        <p>They can claim they are not tracking "people," just "data points."</p>
      </div>
      <div>
        <h3>Systemic power</h3>
        <p>
          They build a permanent, searchable historical record of human movement
          that is available to any entity with enough money or the right
          "mutual aid" access.
        </p>
      </div>
    </div>
  </div>
</section>

<!-- ======================================================== closing ==== -->
<section class="section section--tight">
  <div class="shell">
    <p class="quote">
      In short, "anonymized" tracking is still tracking.
    </p>
    <div class="prose mt-m">
      <p>
        The ability to identify an individual through movement patterns is a
        standard capability of modern data analytics. When you possess the
        coordinates and timestamps for a specific vehicle's daily habits, you
        have stripped away the privacy of the person operating that vehicle,
        regardless of whether you have attached a name to the file.
      </p>
    </div>
  </div>
</section>

<section class="section section--close">
  <div class="shell">
    <div class="panel">
      <h2 class="display-3">This is what the county pays for.</h2>
      <p class="lede">
        The board votes on the contract, the budget line and the renewal. One
        email to one commissioner puts a resident's name on the record against
        it.
      </p>
      <div class="pill-row">
        <a class="pill" href="/get-involved">Write to your commissioner &rarr;</a>
        <a class="pill pill--ghost" href="/resources">Read the primary sources</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
