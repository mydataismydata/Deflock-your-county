<?php
declare(strict_types=1);

require_once __DIR__ . '/includes/config.php';

$page  = 'resources';
$title = 'Resources';
$blurb = 'Camera maps, legal groundwork, reporting on license plate readers, public records tools and local government contacts.';

/**
 * Edit this array to change the page. Each group is a heading, an intro
 * paragraph and a list of [label, url, one-line description].
 */
$groups = [
    [
        'id'    => 'maps',
        'title' => 'Look up your own area',
        'intro' => 'Start with your own plate, then look at what is installed '
                 . 'around you. The maps are crowdsourced, which means they stay '
                 . 'incomplete until somebody local fills the gaps.',
        'links' => [
            ['Have I Been Flocked?', 'https://haveibeenflocked.com/',
             'Type in your plate and see whether it appears in the Flock search logs released so far. The Agencies menu at the top breaks the same records down by department, so you can read what your local agency has been searching for.'],
            ['DeFlock', 'https://deflock.org',
             'Open-source map of license plate reader locations, built on OpenStreetMap data. Adding a camera takes a couple of minutes.'],
            ['Atlas of Surveillance', 'https://atlasofsurveillance.org',
             'Documents police technology by agency, from EFF and the Reynolds School of Journalism at the University of Nevada, Reno. Useful for finding what else your department runs.'],
            ['OpenStreetMap', 'https://www.openstreetmap.org',
             'The underlying map. DeFlock submissions land here, so an edit outlives any one project.'],
        ],
    ],
    [
        'id'    => 'tech',
        'title' => 'Understand the technology',
        'intro' => 'What the hardware captures, what the software does with it, '
                 . 'and how the sharing networks are put together.',
        'links' => [
            ['EFF Street-Level Surveillance', 'https://sls.eff.org',
             'Plain-language explainers on plate readers, drones, face recognition, cell-site simulators and the rest of the municipal catalogue.'],
            ['EFF: Automated License Plate Readers', 'https://www.eff.org/issues/automated-license-plate-readers-alpr',
             'The issue page, kept current with litigation and legislative developments.'],
            ['ACLU: Surveillance Technologies', 'https://www.aclu.org/issues/privacy-technology/surveillance-technologies',
             'Reports and campaign material, including the model Community Control Over Police Surveillance ordinance.'],
            ['EPIC', 'https://epic.org',
             'The Electronic Privacy Information Center. Litigation, FOIA work and policy analysis going back to 1994.'],
        ],
    ],
    [
        'id'    => 'legal',
        'title' => 'The legal ground',
        'intro' => 'Whether a month of location history counts as a search under '
                 . 'the Fourth Amendment is being argued in court right now.',
        'links' => [
            ['Carpenter v. United States', 'https://www.law.cornell.edu/supremecourt/text/16-402',
             'The 2018 Supreme Court decision holding that pulling a person\'s historical location records is a search that needs a warrant. It is the case our argument rests on, so read it rather than taking our summary of it.'],
            ['The IJ Database of ALPR Abuse', 'https://ij.org/the-ij-database-of-alpr-abuse/',
             'The Institute for Justice has cataloged more than 200 incidents of plate reader abuse: romantic stalking, wrongful stops and detentions, use by people who are not law enforcement, and other misconduct. Documented and mapped.'],
            ['Institute for Justice', 'https://ij.org',
             'Litigating Fourth Amendment challenges to municipal camera networks, including the case against Norfolk, Virginia.'],
            ['Restore The Fourth', 'https://restorethe4th.com',
             'National volunteer organization focused on the Fourth Amendment, with local chapters.'],
            ['Surveillance Technology Oversight Project', 'https://www.stopspying.org',
             'Litigation and model legislation on municipal surveillance.'],
            ['Center for Democracy & Technology', 'https://cdt.org',
             'Policy research on government data collection and the rules that should attach to it.'],
        ],
    ],
    [
        'id'    => 'press',
        'title' => 'Reporting worth reading',
        'intro' => 'Most of what is publicly known about how these systems get '
                 . 'misused came out of records requests filed by journalists.',
        'links' => [
            ['404 Media', 'https://www.404media.co',
             'Sustained investigative coverage of Flock Safety, including audit logs obtained by records request.'],
            ['EFF Deeplinks', 'https://www.eff.org/deeplinks',
             'EFF staff blog. Faster than the issue pages and usually where new findings appear first.'],
            ['MuckRock', 'https://www.muckrock.com/',
             'Nonprofit newsroom and records-request platform. Journalists, researchers and the public file requests through it, and the results stay published, so you can read what other people have already pried loose.'],
        ],
    ],
    [
        'id'    => 'selfdefense',
        'title' => 'Protect yourself in the meantime',
        'intro' => 'None of this fixes the camera on the pole. It reduces how '
                 . 'much everything else knows about you.',
        'links' => [
            ['Surveillance Self-Defense', 'https://ssd.eff.org',
             'EFF\'s practical guides, organized by threat model rather than by tool.'],
            ['Privacy Guides', 'https://www.privacyguides.org',
             'Maintained recommendations for browsers, messaging, email and DNS, with the reasoning attached.'],
        ],
    ],
];

// The county block lives in place.php so a fork rewrites one file. It sits
// after the national material and before the self-defense links.
array_splice($groups, 4, 0, [LOCAL_RESOURCES]);

require __DIR__ . '/includes/header.php';
?>

<section class="hero">
  <div class="shell">
    <span class="eyebrow">Resources</span>
    <h1>Where to read the primary sources.</h1>
    <p class="lede">
      Everything below is free and almost none of it is ours. Start at the top
      if you want to know whether your own plate has been searched, or jump to
      the local section for the documents agencies here have already released.
    </p>
  </div>
</section>

<?php foreach ($groups as $i => $group): ?>
<section class="bay<?= $i % 2 === 1 ? ' bay--raised' : '' ?>" id="<?= e($group['id']) ?>">
  <div class="shell">
    <h2><?= e($group['title']) ?></h2>
    <p class="measure quiet"><?= e($group['intro']) ?></p>

    <ul class="linklist mt-m">
<?php foreach ($group['links'] as [$label, $url, $note]): ?>
      <li>
        <a href="<?= e($url) ?>" rel="noopener">
          <b><?= e($label) ?></b>
          <span><?= e($note) ?></span>
        </a>
      </li>
<?php endforeach; ?>
    </ul>
  </div>
</section>
<?php endforeach; ?>

<section class="bay">
  <div class="shell">
    <div class="callout measure">
      <h3>Something missing?</h3>
      <p>
        Send it to us. We add links that publish documents, explain the
        technology or help somebody file a request. We do not add anything that
        tells people to interfere with equipment.
      </p>
      <p class="mb-0"><a href="/contact">Suggest a resource</a></p>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
