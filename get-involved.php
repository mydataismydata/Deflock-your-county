<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$page  = 'involved';
$title = 'Get involved';
$blurb = 'Meeting dates, who to call in ' . COUNTY . ', what to say when you reach '
       . 'them, and how to file a public records request.';

require __DIR__ . '/includes/header.php';

$channels = array_filter(CHANNELS, static fn (string $url): bool => trim($url) !== '');
?>

<section class="hero">
  <div class="shell">
    <span class="eyebrow">Get involved</span>
    <h1>Fifteen minutes is enough to start.</h1>
    <p class="lede">
      One email to one commissioner puts a resident's name on the record against
      a contract that usually passes in silence. Everything on this page is
      something you can finish in an evening.
    </p>
    <div class="btn-row">
      <a class="btn" href="#officials">Write to an official</a>
      <a class="btn btn--ghost" href="#meetings">See the meetings</a>
    </div>
  </div>
</section>

<!-- ====================================================== meetings ==== -->
<section class="bay" id="meetings">
  <div class="shell">

    <span class="eyebrow">Calendar</span>
    <h2>Upcoming meetings</h2>
    <p class="measure quiet">
      County meetings are public and all residents can speak during public
      comment.
    </p>

<?php if (MEETINGS === []): ?>
    <div class="callout mt-m w-note">
      <h3>Nothing on the calendar yet</h3>
      <p class="mb-0">
        Dates go up here as soon as they are set. Ask us to tell you when the
        next one lands through the <a href="/contact">contact form</a>.
      </p>
    </div>
<?php else: ?>
    <div class="meetings mt-m">
<?php foreach (MEETINGS as $m): ?>
      <div class="meeting">
        <div class="meeting__when"><?= e($m['when']) ?></div>
        <div>
          <h3><?= e($m['what']) ?></h3>
          <p><?= e($m['where']) ?><?= $m['note'] !== '' ? ' &middot; ' . e($m['note']) : '' ?></p>
        </div>
      </div>
<?php endforeach; ?>
    </div>
<?php endif; ?>

    <p class="small quiet mt-s">
      County agendas are published ahead of each meeting at
      <a href="<?= e(COUNTY_SITE_URL) ?>" rel="noopener"><?= e(COUNTY_SITE_LABEL) ?></a>. Check the
      agenda before you travel, because items move and meetings get cancelled.
    </p>

  </div>
</section>

<!-- ====================================================== channels ==== -->
<section class="bay bay--raised" id="follow">
  <div class="shell">

    <span class="eyebrow">Stay in touch</span>
    <h2>Follow along</h2>

<?php if ($channels === []): ?>
    <p class="measure">
      Accounts are not up yet. Until they are, the
      <a href="/contact">contact form</a> is the way to reach us and the way to
      get told about the next meeting.
    </p>
<?php else: ?>
    <p class="measure quiet">
      Same posts on each, so pick whichever one you already read.
    </p>
    <ul class="linklist mt-m">
<?php foreach ($channels as $name => $url): ?>
      <li><a href="<?= e($url) ?>" rel="me noopener"><b><?= e($name) ?></b></a></li>
<?php endforeach; ?>
    </ul>
<?php endif; ?>

  </div>
</section>

<!-- ===================================================== officials ==== -->
<section class="bay" id="officials">
  <div class="shell">

    <span class="eyebrow">Contact your elected officials</span>
    <h2>Who decides, and what to ask them</h2>
    <p class="measure lede">
      Two different bodies matter here and they answer different questions.
      Sending the right question to the wrong one wastes a letter.
    </p>

    <div class="grid grid--3 mt-xl">
<?php foreach (BODIES as $body): ?>
      <div class="card">
        <h3><?= e($body['name']) ?></h3>
        <p><strong><?= e($body['why']) ?></strong></p>
        <p class="quiet"><?= e($body['how']) ?></p>
<?php if ($body['seats'] !== []): ?>
        <ul class="small quiet seatlist">
<?php foreach ($body['seats'] as $seat): ?>
          <li><?= e($seat) ?>: name and email to be added</li>
<?php endforeach; ?>
        </ul>
<?php endif; ?>
        <span class="card__more">
          <a href="<?= e($body['link'][1]) ?>" rel="noopener"><?= e($body['link'][0]) ?> &rarr;</a>
        </span>
      </div>
<?php endforeach; ?>
    </div>

    <hr>

    <h3 class="h-sub">The four asks</h3>
    <p class="measure">
      Keep every message to these. They are specific, they are cheap for the
      county to do, and none of them stops an investigation. That last part
      matters, because the usual answer to this campaign is that we want to
      protect criminals.
    </p>

    <ol class="steps w-form mt-m">
      <li>
        <h3>Publish the contract and the cost</h3>
        <p>The vendor, the term, the annual price and what the vendor may do with the data.</p>
      </li>
      <li>
        <h3>Require a warrant to search history</h3>
        <p>With the emergency exceptions courts already recognize. Live hotlist alerts are a separate question from pulling a month of someone's movements.</p>
      </li>
      <li>
        <h3>Release the audit log quarterly</h3>
        <p>Number of searches, which outside agencies ran them, and the case types. The system already records all of it.</p>
      </li>
      <li>
        <h3>Vote on renewal in the open</h3>
        <p>A regular agenda item with public comment, not a consent item that passes unread.</p>
      </li>
    </ol>

    <hr>

    <h3 class="h-sub">Say this on the phone</h3>
    <p class="measure quiet">
      Staff log calls by topic and count. Ninety seconds is plenty.
    </p>
    <div class="script w-form">Hi, my name is [name] and I live in [neighborhood], District [number].

I'm calling about the automated license plate readers in the county.

I'd like the Commissioner to ask three things before that contract comes up
again: how long the captures are kept, which outside agencies can search them,
and whether a warrant is required to pull someone's history.

I'm not asking anyone to stop investigating crimes. I'm asking for the policy to
be published.

Is there an address where I can send that in writing? Thank you for your time.</div>

    <h3 class="h-sub mt-xl">Send this by email</h3>
    <p class="measure quiet">
      Written messages to a commission are public record, which is the point.
      Put your district in the first line so it is not filed as an out-of-county
      form letter.
    </p>
    <div class="script w-form">Subject: License plate readers, before the next renewal

Dear Commissioner [name],

I live in [neighborhood], District [number]. I am writing about the automated
license plate readers operating in <?= e(COUNTY) ?>.

I am not asking you to end the program. I am asking that it be governed in
public. Specifically:

1. Publish the contract, the annual cost and the term.
2. Publish the usage policy, including how long captures are kept and who is
   allowed to run a search.
3. Require a warrant for any search of historical location data, with the
   emergency exceptions courts already recognize.
4. Release a quarterly summary of the audit log: how many searches were run,
   by which agencies, and for what case types.
5. Bring renewal to a regular agenda with public comment, not a consent item.

Could you tell me which of these the county already does, and whether you would
support the rest?

Thank you,
[Full name]
[Street address]
[Phone or email]</div>

    <h3 class="h-sub mt-xl">Read this at public comment</h3>
    <p class="measure quiet">
      Sign up before the meeting starts. Most boards give three minutes; this
      runs to roughly two, which leaves room for nerves. Say your name and
      district, because the clerk is writing them down.
    </p>
    <div class="script w-form">Good morning. My name is [name]. I live in District [number].

I am here about the license plate readers.

These cameras photograph every car that passes them. Not the ones on a hotlist.
Every one. The record keeps the plate, the make, the color, any bumper sticker
or damage, and it is stamped with the time and the location of the camera.

I am not asking this board to end the program. I am asking for four things.

One. Publish the contract and the usage policy on the county website.
Two. Require a warrant before anyone searches a person's history.
Three. Release a summary of the audit log every quarter.
Four. Vote on renewal at a regular meeting, with public comment, not on consent.

The county already knows how to do every one of those. None of them stops a
single investigation.

I would like to know whether this board will do them. Thank you.</div>

    <div class="callout w-form mt-xl">
      <h3>Three things that make a message land</h3>
      <ul class="mb-0">
        <li>Use your own words for at least one sentence. Identical letters get counted once.</li>
        <li>Give your district. Officials sort constituents from everyone else first.</li>
        <li>Ask a question that needs an answer. "Will you support this?" is harder to file away than "please consider."</li>
      </ul>
    </div>

  </div>
</section>

<!-- ======================================================= records ==== -->
<section class="bay bay--raised" id="records">
  <div class="shell">

    <span class="eyebrow">Public records</span>
    <h2>File a request yourself</h2>
    <p class="measure">
      <a href="<?= e(RECORDS_LAW_URL) ?>" rel="noopener"><?= e(RECORDS_LAW) ?></a>
      is what makes this work. <?= e(RECORDS_LAW_NOTE) ?>
    </p>
    <p class="measure quiet">
      Send this to the records custodian for the agency you are asking about.
      Then tell us what comes back.
    </p>

    <div class="script w-form">Subject: Public records request, automated license plate readers

To the records custodian,

Under <?= e(RECORDS_LAW) ?>, I request copies of the following:

1. All contracts, purchase orders, invoices and quotes between [agency] and
   Flock Safety, or any other automated license plate reader vendor, from
   [date] to the present.

2. The agency's written policy governing the use of automated license plate
   readers, including retention period and which personnel may run a search.

3. Any data sharing agreement, memorandum of understanding or network sharing
   arrangement that lets an outside agency search data captured by cameras
   this agency operates.

4. A list of the camera locations currently in service.

5. Search audit logs for [start date] through [end date], showing the date of
   each search, the agency and user role that ran it, and the stated reason.
   Personal identifying information may be redacted.

I would prefer these in electronic form. If any portion is exempt, please cite
the specific statutory exemption for each withholding. If the cost will exceed
<?= e(RECORDS_FEE_CAP) ?>, please give me an estimate before you begin.

Thank you,
[Name]
[Email address]</div>

    <p class="small quiet w-form mt-s">
      Keep the request in writing and keep the reply. If an agency refuses or
      goes quiet, the refusal itself becomes useful material.
    </p>

  </div>
</section>

<!-- =========================================================== map ==== -->
<section class="bay" id="map">
  <div class="shell">

    <span class="eyebrow">Mapping</span>
    <h2>Put a camera on the map</h2>

    <div class="measure stack">
      <p>
        <a href="https://deflock.org" rel="noopener">DeFlock</a> maps reader
        locations from public contributions and stores them in OpenStreetMap, so
        an entry survives whether or not any one website does. <?= e(COUNTY) ?>
        is thinly covered, which is a gap you can close on your commute.
      </p>
      <p>
        Photograph the pole from a public road or sidewalk. Note the cross
        streets and which way the camera faces. Do not go onto private property,
        do not touch the equipment, and do not obstruct traffic to get the shot.
      </p>
    </div>

    <div class="btn-row">
      <a class="btn" href="https://deflock.org" rel="noopener">Open the map</a>
      <a class="btn btn--ghost" href="/contact">Send us a sighting</a>
    </div>

  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
