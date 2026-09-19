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
      <?= e(COUNTY_MEETING_RULE) ?> Dates and agendas go up on the
      <a href="<?= e(COUNTY_CALENDAR_URL) ?>" rel="noopener">county calendar</a>
      ahead of each meeting. Check the agenda before you travel, because items
      move and meetings get cancelled.
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
      hear when something is going on the commission agenda.
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
        <span class="card__more">
          <a href="<?= e($body['link'][1]) ?>" rel="noopener"><?= e($body['link'][0]) ?> &rarr;</a>
        </span>
      </div>
<?php endforeach; ?>
    </div>

<?php
$seats = array_merge(...array_column(BODIES, 'seats'));
?>
<?php if ($seats !== []): ?>
    <h3 class="h-sub mt-xl">Who represents you</h3>
    <p class="measure quiet">
      Write to your own district first. Copying the other four costs you nothing
      and puts your name on four more records.
    </p>

    <div class="rows mt-m">
<?php foreach ($seats as $seat): ?>
      <div class="row">
        <h3><?= e($seat['district']) ?></h3>
        <p>
          <strong><?= e($seat['name']) ?></strong><?= $seat['role'] !== '' ? ', ' . e($seat['role']) : '' ?><br>
          <span class="seat__contact">
            <a href="mailto:<?= e($seat['email']) ?>"><?= e($seat['email']) ?></a>
            &middot;
            <a href="tel:<?= e($seat['tel']) ?>"><?= e($seat['phone']) ?></a>
          </span>
        </p>
      </div>
<?php endforeach; ?>
    </div>
<?php endif; ?>

    <hr>

    <h3 class="h-sub">The four asks</h3>
    <p class="measure">
      One demand, and four steps that carry it out. Keep every message to these.
      The usual answer to this campaign is that we want to protect criminals, so
      say the other half out loud: a judge can still issue a warrant for anyone
      the sheriff's office has reason to suspect, which is the whole purpose of
      a warrant.
    </p>

    <ol class="steps w-form mt-m">
      <li>
        <h3>Vote against renewal and cancel the contract</h3>
        <p>Do not renew it, and end the term that is running as soon as the contract allows.</p>
      </li>
      <li>
        <h3>Turn the cameras off and take them down</h3>
        <p>Every unit the county pays for, whether it is fixed to a pole or mounted on a vehicle.</p>
      </li>
      <li>
        <h3>Delete what has been collected</h3>
        <p>Including the copies on the vendor's servers and anything shared with outside agencies. Ask each of them to confirm the deletion in writing.</p>
      </li>
      <li>
        <h3>Take the vote in the open</h3>
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

I'd like the Commissioner to vote against renewing that contract, and to have
the cameras taken down. These cameras photograph every car that passes them and
keep the record. That is a search of people nobody suspects of anything, and I
do not think the county has the authority to run one.

This does not stop any investigation. If deputies have reason to suspect
somebody, they can go to a judge and get a warrant.

Is there an address where I can send that in writing? Thank you for your time.</div>

    <h3 class="h-sub mt-xl">Send this by email</h3>
    <p class="measure quiet">
      Written messages to a commission are public record, which is the point.
      Put your district in the first line so it is not filed as an out-of-county
      form letter.
    </p>
    <div class="script w-form">Subject: Please vote to end the license plate reader contract

Dear Commissioner [name],

I live in [neighborhood], District [number]. I am writing about the automated
license plate readers operating in <?= e(COUNTY) ?>.

I am asking you to end the program. Specifically:

1. Vote against the next renewal, and cancel the contract that is running.
2. Turn the cameras off and take them down.
3. Delete the captures the county holds. Get written confirmation from the
   vendor, and from every agency the county shared with, that their copies are
   deleted too.
4. Take that vote at a regular meeting with public comment, not on consent.

These cameras photograph every car that passes them. They keep the plate, the
make, the color and any marking on the vehicle, and they stamp each record with
a time and a place. Nobody in that record is suspected of anything. The Fourth
Amendment requires probable cause and a description of what is to be searched,
and a system that records everyone offers neither.

None of this stops an investigation. A judge can still issue a warrant for
anyone the sheriff's office has reason to suspect.

Will you vote to end the contract? I would like a yes or a no.

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

Nobody in that record is suspected of anything. That is a search of everyone,
run first and justified afterwards, and I do not believe this county has the
authority to run it.

I am asking this board to end the program.

One. Vote against renewal and cancel the contract.
Two. Turn the cameras off and take them down.
Three. Delete what has been collected, including the vendor's copies.
Four. Take that vote at a regular meeting, with public comment, not on consent.

This stops no investigation. If deputies have reason to suspect somebody, they
can go to a judge and get a warrant, the same as they did before these cameras
went up.

I would like to know whether this board will end it. Thank you.</div>

    <div class="callout w-form mt-xl">
      <h3>Three things that make a message land</h3>
      <ul class="mb-0">
        <li>Use your own words for at least one sentence. Identical letters get counted once.</li>
        <li>Give your district. Officials sort constituents from everyone else first.</li>
        <li>Ask a question that needs an answer. "Will you vote to end the contract?" is harder to file away than "please consider."</li>
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
