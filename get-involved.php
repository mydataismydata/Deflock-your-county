<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/letters.php';

// A different draft on every request, so two people who copy the letter on
// the same afternoon do not send the same one. Twenty-six snippets make 5,040
// combinations. Nothing between us and the reader may freeze one of them.
header('Cache-Control: private, no-cache, must-revalidate');

$pick = static function (array $choices): string {
    return $choices[random_int(0, count($choices) - 1)];
};

$asks = <<<'TEXT'
* Vote Against Renewal & Cancel the Contract: End the current Flock Safety agreement as soon as terms allow.
* Turn the Cameras Off and Take Them Down: Remove every unit the county pays for, fixed or vehicle-mounted.
* Delete What Has Been Collected: Explicitly require the vendor to purge all local logs and confirm the deletion in writing.
* Take the Vote in the Open: Place this item on a regular agenda with dedicated public comment, not as a consent item.
TEXT;

$sources = "Sources:";
foreach (LETTER_SOURCES as [$label, $url]) {
    $sources .= "\n" . $label . "\n" . $url . "\n";
}

$sheriffLetter = 'Subject: ' . SHERIFF_LETTER_SUBJECT . "\n\n" . SHERIFF_LETTER_BODY;
foreach (SHERIFF_LETTER_SOURCE as [$label, $url]) {
    $sheriffLetter .= "\n\nSource:\n" . $label . "\n" . $url;
}

$letter = implode("\n\n", [
    'Subject: ' . $pick(LETTER_SUBJECT),
    'Dear Commissioner [Last name],',
    $pick(LETTER_STANDING),
    $pick(LETTER_EVIDENCE),
    $pick(LETTER_VALUES),
    $pick(LETTER_LEADIN),
    $asks,
    $pick(LETTER_CLOSE),
    "Sincerely,\n[Your Name]\n[Your Street Address / Neighborhood]\n" . COUNTY . ', ' . STATE_ABBR,
    rtrim($sources),
]);

$page  = 'involved';
$title = 'Get involved';
$blurb = 'Meeting dates, who to call in ' . COUNTY . ', what to say when you reach '
       . 'them, and how to file a public records request.';

/**
 * The meeting list prints as one mono line per standing series rather than as a
 * card each. Meetings that share a body, a venue and a time are one series, so
 * the dates run together and the venue is stated once.
 */
$lower = static function (string $s): string {
    return function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
};

$series = [];
foreach (MEETINGS as $m) {
    $key = $m['what'] . '|' . $m['where'] . '|' . ($m['note'] ?? '');
    if (!isset($series[$key])) {
        $series[$key] = ['what' => $m['what'], 'where' => $m['where'], 'note' => $m['note'] ?? '', 'dates' => [], 'years' => []];
    }
    $stamp = strtotime($m['when']);
    if ($stamp === false) {
        // An entry the date parser does not understand still gets a block, with
        // whatever was written in config.php printed as it stands.
        $series[$key]['dates'][] = ['month' => '', 'day' => $m['when'], 'weekday' => '', 'year' => ''];
        continue;
    }
    $series[$key]['dates'][] = [
        'month'   => date('M', $stamp),
        'day'     => date('j', $stamp),
        'weekday' => date('D', $stamp),
        'year'    => date('Y', $stamp),
    ];
    $series[$key]['years'][date('Y', $stamp)] = true;
}

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="hero__media" aria-hidden="true">
    <img class="hero__media--flag drift drift--mid" src="/assets/img/flag.webp" alt=""
         width="1200" height="896" fetchpriority="high">
    <div class="hero__scrim"></div>
  </div>

  <div class="hero__inner shell">
    <p class="eyebrow"><span class="dot" aria-hidden="true"></span> Get involved</p>
    <h1 class="display-1" data-reveal><span class="accent">Fifteen minutes</span> is enough to start.</h1>
    <p class="lede lede--lg">
      One email to one commissioner puts a resident's name on the record against
      a contract that usually passes in silence. Everything on this page is
      something you can finish in an evening.
    </p>
    <div class="pill-row">
      <a class="pill" href="/assets/docs/st_johns_county_surveillance_ordinance.pdf"
         target="_blank" rel="noopener"
         aria-label="Read the Petition (PDF, opens in a new tab)">Read the Petition</a>
      <a class="pill pill--ghost" href="#officials">Write to an official</a>
      <a class="pill pill--ghost" href="#meetings">See the meetings</a>
    </div>
  </div>
</section>

<!-- ====================================================== meetings ==== -->
<section class="section" id="meetings">
  <div class="shell">
    <h2 class="h-section" data-reveal>Upcoming meetings.</h2>
    <p class="lede lede--tight">
      County meetings are public and all residents can speak during public
      comment.
    </p>

<?php if ($series === []): ?>
    <div class="rule-top mt-l">
      <p class="label-mono">nothing on the calendar yet</p>
      <p class="note mt-s">
        The county posts its dates a few months ahead. Check the
        <a class="plain-link" href="<?= e(COUNTY_CALENDAR_URL) ?>" rel="noopener">county calendar</a>
        in the meantime.
      </p>
    </div>
<?php else: ?>
<?php foreach ($series as $s): ?>
    <div class="rule-top mt-l">
      <p class="label-mono"><?= e($lower($s['what'])) ?></p>
      <p class="meetwhen__place"><?= e($s['where']) ?></p>
<?php if ($s['note'] !== ''): ?>
      <p class="meetwhen__time"><?= e($s['note']) ?></p>
<?php endif; ?>

      <ul class="meetdates">
<?php foreach ($s['dates'] as $d): ?>
        <li>
<?php if ($d['month'] !== ''): ?>
          <span class="m"><?= e($d['month']) ?></span>
          <span class="d"><?= e($d['day']) ?></span>
          <span class="w"><?= e($lower($d['weekday'])) ?><?= count($s['years']) > 1 ? ' ' . e($d['year']) : '' ?></span>
<?php else: ?>
          <span class="d"><?= e($d['day']) ?></span>
<?php endif; ?>
        </li>
<?php endforeach; ?>
      </ul>

<?php if (count($s['years']) === 1): ?>
      <p class="label-mono mt-s"><?= e((string) array_key_first($s['years'])) ?></p>
<?php endif; ?>
    </div>
<?php endforeach; ?>
<?php endif; ?>

    <p class="note mt-m">
      <?= e(COUNTY_MEETING_RULE) ?> Dates and agendas go up on the
      <a class="plain-link" href="<?= e(COUNTY_CALENDAR_URL) ?>" rel="noopener">county calendar</a>
      ahead of each meeting. Check the agenda before you travel, because items
      move and meetings get cancelled.
    </p>
  </div>
</section>

<!-- ===================================================== officials ==== -->
<section class="section" id="officials">
  <div class="shell">
    <h2 class="h-section" data-reveal>Who decides and what to ask them.</h2>
    <p class="lede lede--tight">
      Two different bodies matter here, and they answer different questions.
      Sending the right question to the wrong one wastes a letter.
    </p>

    <!-- Three across only when there are three. Two bodies in a three-column
         grid leaves a hole where the third used to be. -->
    <div class="defs<?= count(BODIES) > 2 ? ' defs--three' : '' ?>">
<?php foreach (BODIES as $body): ?>
      <div>
        <h3><?= e($body['name']) ?></h3>
        <p><strong><?= e($body['why']) ?></strong> <?= e($body['how']) ?></p>
<?php if (($body['link'][1] ?? '') !== ''): ?>
        <p class="mt-s">
          <a class="accent-link" href="<?= e($body['link'][1]) ?>" rel="noopener"><?= e($body['link'][0]) ?>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
          </a>
        </p>
<?php endif; ?>
      </div>
<?php endforeach; ?>
    </div>

<?php $seats = array_merge(...array_column(BODIES, 'seats')); ?>
<?php if ($seats !== []): ?>
    <h3 class="h-block mt-xl">Who represents you.</h3>
    <p class="note mt-s">
      Write to your own district first. Copying the other four costs you nothing
      and puts your name on four more records.
    </p>
<?php if (COUNTY_DISTRICT_MAP !== ''): ?>
    <p class="note mt-s">
      Not sure which district you live in? The county's precinct map shows all
      five. <a class="plain-link" href="<?= e(COUNTY_DISTRICT_MAP) ?>" rel="noopener">Open the district map</a><?= COUNTY_DISTRICT_MAP_NOTE !== '' ? ' (' . e(COUNTY_DISTRICT_MAP_NOTE) . ')' : '' ?>.
      It is on the county's file host rather than here.
    </p>
<?php endif; ?>

    <ul class="rows mt-s">
<?php foreach ($seats as $seat): ?>
      <li>
        <span class="row-name"><?= e($seat['district']) ?></span>
        <p>
          <strong><?= e($seat['name']) ?></strong><?= $seat['role'] !== '' ? ', ' . e($seat['role']) : '' ?><br>
          <a class="seat-contact" href="mailto:<?= e($seat['email']) ?>"><?= e($seat['email']) ?></a>
          <span class="seat-sep">&middot;</span>
          <a class="seat-contact" href="tel:<?= e($seat['tel']) ?>"><?= e($seat['phone']) ?></a>
        </p>
      </li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>
  </div>
</section>

<!-- ========================================================== asks ==== -->
<section class="section" id="asks">
  <div class="shell">
    <h2 class="h-section" data-reveal>The four asks.</h2>
    <p class="lede lede--tight">
      One demand, and four steps that carry it out. Include these points in your
      message to keep it oriented to actions that will resolve the problem. The
      usual answer to this campaign is that we want to protect criminals, so say
      the other half out loud: a judge can still issue a warrant for anyone the
      sheriff's office has reason to suspect, which is the whole purpose of a
      warrant.
    </p>

    <div class="defs">
      <div>
        <h3>Vote against renewal and cancel the contract</h3>
        <p>Do not renew it, and end the term that is running as soon as the contract allows.</p>
      </div>
      <div>
        <h3>Turn the cameras off and take them down</h3>
        <p>Every unit the county pays for, whether it is fixed to a pole or mounted on a vehicle.</p>
      </div>
      <div>
        <h3>Delete what has been collected</h3>
        <p>Including the copies on the vendor's servers and anything shared with outside agencies. Ask each of them to confirm the deletion in writing.</p>
      </div>
      <div>
        <h3>Take the vote in the open</h3>
        <p>A regular agenda item with public comment, not a consent item that passes unread.</p>
      </div>
    </div>
  </div>
</section>

<!-- ======================================================= scripts ==== -->
<section class="section" id="scripts">
  <div class="shell">
    <h2 class="h-section" data-reveal>Word for word.</h2>
    <p class="lede lede--tight">
      Four scripts, ready to use. Use your own words for at least one sentence,
      give your district, and ask a question that needs an answer.
    </p>

    <div class="split split--label rule-top rule-top--faint mt-l">
      <div>
        <h3 class="h-block">On the phone</h3>
        <p class="note mt-s">Staff log calls by topic and count. Ninety seconds is plenty.</p>
      </div>
<pre class="script">Hi, my name is [name] and I live in [neighborhood], District [number].

I'm calling about the automated license plate readers in the county.

I'd like the Commissioner to vote against renewing that contract, and to have the cameras taken down. These cameras photograph every car that passes them and keep the record. That is a search of people nobody suspects of anything, and I do not think the county has the authority to run one.

This does not stop any investigation. If deputies have reason to suspect somebody, they can go to a judge and get a warrant.

Is there an address where I can send that in writing? Thank you for your time.</pre>
    </div>

    <div class="split split--label rule-top rule-top--faint mt-l">
      <div>
        <h3 class="h-block">By email</h3>
        <p class="note mt-s">Written messages to a commission are public record, which is the point. This draft is different every time the page loads, so no two people send the same letter. Change a sentence to your own words anyway.</p>
      </div>
<pre class="script"><?= e($letter) ?></pre>
      <p class="mt-s">
        <a class="arrow-link" href="/get-involved?draft=<?= e((string) random_int(1000, 9999)) ?>#scripts">Show me a different draft
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path d="M7 17 17 7"/><path d="M7 7h10v10"/></svg>
        </a>
      </p>
    </div>

    <div class="split split--label rule-top rule-top--faint mt-l">
      <div>
        <h3 class="h-block">To the sheriff</h3>
        <p class="note mt-s">The sheriff's office operates the cameras and writes the policy. The board holds the contract. This one goes to the sheriff, and it is the same letter for everybody.</p>
      </div>
<pre class="script"><?= e($sheriffLetter) ?></pre>
    </div>

    <div class="split split--label rule-top rule-top--faint mt-l">
      <div>
        <h3 class="h-block">At public comment</h3>
        <p class="note mt-s">Sign up before the meeting starts. Most boards give three minutes; this runs to roughly two, which leaves room for nerves. Say your name and district, because the clerk is writing them down.</p>
      </div>
<pre class="script">Good morning. My name is [name]. I live in District [number].

I am here about the license plate readers.

These cameras photograph every car that passes them. Not the ones on a hotlist. Every one. The record keeps the plate, the make, the color, any bumper sticker or damage, and it is stamped with the time and the location of the camera.

Nobody in that record is suspected of anything. That is a search of everyone, run first and justified afterwards, and I do not believe this county has the authority to run it.

I am asking this board to end the program.

One. Vote against renewal and cancel the contract.
Two. Turn the cameras off and take them down.
Three. Delete what has been collected, including the vendor's copies.
Four. Take that vote at a regular meeting, with public comment, not on consent.

This stops no investigation. If deputies have reason to suspect somebody, they can go to a judge and get a warrant, the same as they did before these cameras went up.

I would like to know whether this board will end it. Thank you.</pre>
    </div>
  </div>
</section>

<!-- ======================================================= records ==== -->
<section class="section" id="records">
  <div class="shell">
    <h2 class="h-section" data-reveal>File a request yourself.</h2>
    <p class="lede lede--tight">
      <a class="plain-link" href="<?= e(RECORDS_LAW_URL) ?>" rel="noopener"><?= e(RECORDS_LAW) ?></a>
      is what makes this work. <?= e(RECORDS_LAW_NOTE) ?>
    </p>

    <div class="split split--label rule-top rule-top--faint mt-l">
      <div>
        <h3 class="h-block">The request</h3>
        <p class="note mt-s">Send this to the records custodian for the agency you are asking about. Then tell us what comes back.</p>
      </div>
<pre class="script">Subject: Public records request, automated license plate readers

To the records custodian,

Under <?= e(RECORDS_LAW) ?>, I request copies of the following:

1. All contracts, purchase orders, invoices and quotes between [agency] and Flock Safety, or any other automated license plate reader vendor, from [date] to the present.

2. The agency's written policy governing the use of automated license plate readers, including retention period and which personnel may run a search.

3. Any data sharing agreement, memorandum of understanding or network sharing arrangement that lets an outside agency search data captured by cameras this agency operates.

4. A list of the camera locations currently in service.

5. Search audit logs for [start date] through [end date], showing the date of each search, the agency and user role that ran it, and the stated reason. Personal identifying information may be redacted.

I would prefer these in electronic form. If any portion is exempt, please cite the specific statutory exemption for each withholding. If the cost will exceed <?= e(RECORDS_FEE_CAP) ?>, please give me an estimate before you begin.

Thank you,
[Name]
[Email address]</pre>
    </div>

    <p class="fine fine--wide mt-m">
      Keep the request in writing and keep the reply. If an agency refuses or
      goes quiet, the refusal itself becomes useful material.
    </p>
  </div>
</section>

<!-- =========================================================== map ==== -->
<section class="section" id="map">
  <div class="shell">
    <h2 class="h-section" data-reveal>Put a camera on the map.</h2>
    <div class="prose mt-s">
      <p>
        <a class="plain-link" href="https://deflock.org" rel="noopener">DeFlock</a>
        maps reader locations from public contributions and stores them in
        OpenStreetMap, so an entry survives whether or not any one website does.
        <?= e(COUNTY) ?> is thinly covered, which is a gap you can close on your
        commute.
      </p>
      <p>
        Photograph the pole from a public road or sidewalk. Note the cross
        streets and which way the camera faces. Do not go onto private property,
        do not touch the equipment, and do not obstruct traffic to get the shot.
      </p>
    </div>
    <div class="pill-row">
      <a class="pill" href="https://deflock.org" rel="noopener">Open the map &rarr;</a>
    </div>
  </div>
</section>

<!-- ======================================================= closing ==== -->
<section class="section section--close">
  <div class="shell">
    <div class="panel">
      <h2 class="display-3">Hear when it goes on the agenda.</h2>
      <p class="lede">
        Contact us to get added to our distribution list, or to give us any info
        or feedback.
      </p>
      <div class="pill-row">
        <a class="pill" href="/contact">Contact us &rarr;</a>
        <a class="pill pill--ghost" href="/resources">Read the resources</a>
      </div>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
