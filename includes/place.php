<?php
/**
 * Everything that names a place.
 *
 * This is the only file another county has to rewrite. No page, partial or
 * stylesheet outside it names a county, a state, a statute or an elected body.
 * Rewrite this file, edit config.php for your own mail settings, and the site
 * is about somewhere else.
 */

declare(strict_types=1);

// ----------------------------------------------------------------- naming --

// The county as it reads in a sentence: "residents of St. Johns County".
const COUNTY = 'St. Johns County';

// The short form used in the wordmark and in tight headings.
const COUNTY_SHORT = 'St. Johns';

const STATE = 'Florida';

// The seat or largest municipality. Used in the mapping and meetings copy.
const COUNTY_SEAT = 'St. Augustine';

// The two halves of the campaign name. They are concatenated into SITE_NAME,
// which the masthead prints as one word followed by the short county name.
const BRAND_HEAD = 'De';
const BRAND_TAIL = 'flock';

const SITE_NAME    = BRAND_HEAD . BRAND_TAIL . ' ' . COUNTY_SHORT;
const SITE_TAGLINE = COUNTY . ', ' . STATE;

// ------------------------------------------------------------ county site --

// The county's own website. Both the label and the address get printed.
const COUNTY_SITE_LABEL = 'sjcfl.us';
const COUNTY_SITE_URL   = 'https://www.sjcfl.us';

// The board's own page, which lists every commissioner, and the page where the
// county publishes its meeting dates and agendas.
const COUNTY_BOARD_URL    = 'https://www.sjcfl.us/board-of-county-commissioners/';
const COUNTY_CALENDAR_URL = 'https://www.sjcfl.us/bcc-calendar/';

// Where the board sits, and the standing schedule it keeps. Both are quoted
// from the county's own Board of County Commissioners page. The rule outlives
// any particular date in MEETINGS, so it is printed alongside the list.
const COUNTY_MEETING_PLACE = 'County Auditorium, 500 San Sebastian View, ' . COUNTY_SEAT;
const COUNTY_MEETING_RULE  = 'The board meets the first and third Tuesday of each month at '
                           . '9:00 am, other than the first Tuesday of January and of July.';

// ---------------------------------------------------------- public records --

// How the records law is cited in a request letter.
const RECORDS_LAW     = 'Chapter 119, Florida Statutes';
const RECORDS_LAW_URL = 'https://www.flsenate.gov/Laws/Statutes';

// Two or three sentences on what the law actually grants. States differ on
// whether a requester must give a reason or live in the state, so this is
// prose rather than a set of flags.
const RECORDS_LAW_NOTE = 'Any person may inspect public records. You do not have '
    . 'to give a reason, you do not have to say who you are, and you do not have '
    . 'to be a Florida resident. An agency may charge for staff time on a large '
    . 'request, so ask for an estimate up front.';

// Above this figure the sample letter asks for an estimate first.
const RECORDS_FEE_CAP = '$25';

// ------------------------------------------------------- legal help, if any --

// A civil liberties organization that takes intake requests in this state.
// Set the url to an empty string to drop the sentence that mentions it.
const LEGAL_HELP_NAME = 'ACLU of Florida';
const LEGAL_HELP_URL  = 'https://www.aclufl.org';

// ---------------------------------------------------------- elected bodies --

// Who to write to, in the order they appear on the Get Involved page. Add the
// names, addresses and phone numbers as you collect them.
const BODIES = [
    [
        'name'  => 'Board of County Commissioners',
        'why'   => 'Approves the contract, the budget line and the renewal. This is the body with a vote on whether the cameras stay.',
        'how'   => 'Five commissioners, elected by district, voted on countywide. Write to the one for your district and copy the rest.',
        'link'  => ['County commission directory', COUNTY_BOARD_URL],
        // The five commissioners as the county's own district pages list them.
        // These are the office lines. The county publishes a cell number for
        // each commissioner as well; we point people at the office.
        'seats' => [
            ['district' => 'District 1', 'name' => 'Christian Whitehurst', 'role' => '',
             'email' => 'bcc1cwhitehurst@sjcfl.us', 'phone' => '(904) 209-0301', 'tel' => '+19042090301'],
            ['district' => 'District 2', 'name' => 'Sarah Arnold', 'role' => '',
             'email' => 'bcc2sarnold@sjcfl.us', 'phone' => '(904) 209-0302', 'tel' => '+19042090302'],
            ['district' => 'District 3', 'name' => 'Clay Murphy', 'role' => 'Chair',
             'email' => 'bcc3cmurphy@sjcfl.us', 'phone' => '(904) 209-0303', 'tel' => '+19042090303'],
            ['district' => 'District 4', 'name' => 'Krista Joseph', 'role' => '',
             'email' => 'bcc4kjoseph@sjcfl.us', 'phone' => '(904) 209-0304', 'tel' => '+19042090304'],
            ['district' => 'District 5', 'name' => 'Ann Taylor', 'role' => 'Vice-Chair',
             'email' => 'bcc5ataylor@sjcfl.us', 'phone' => '(904) 209-0305', 'tel' => '+19042090305'],
        ],
    ],
    [
        'name'  => COUNTY . ' Sheriff\'s Office',
        'why'   => 'Operates the cameras and writes the usage policy. Retention periods and search rules are set here, not by the commission.',
        'how'   => 'The sheriff is elected countywide and answers to voters directly. Ask when the cameras come off the poles, and ask for the written policy while they are still up.',
        'link'  => ['Sheriff\'s Office', 'https://www.sjso.org'],
        'seats' => [],
    ],
    [
        'name'  => 'City of ' . COUNTY_SEAT . ' Commission',
        'why'   => 'The city buys its own equipment for its own police department. County policy does not bind it.',
        'how'   => 'Mayor and four commissioners. Relevant if you live inside city limits.',
        'link'  => ['City of ' . COUNTY_SEAT, 'https://www.citystaug.com'],
        'seats' => [],
    ],
];

// ------------------------------------------------- local block on Resources --

// Appended to the Resources page after the national material.
const LOCAL_RESOURCES = [
    'id'    => 'local',
    'title' => STATE . ' and ' . COUNTY,
    'intro' => 'Florida has one of the broader public records laws in the country. Use it.',
    'links' => [
        [STATE . ' Statutes', RECORDS_LAW_URL,
         'Chapter 119 is the public records law. Chapter 286 covers open meetings.'],
        ['Government-in-the-Sunshine Manual', 'https://www.myfloridalegal.com/open-government/sunshine-manual',
         'The Attorney General\'s annual guide to what is public, who must release it and how long they may take.'],
        [LEGAL_HELP_NAME, LEGAL_HELP_URL,
         'State affiliate. Tracks surveillance legislation in Tallahassee.'],
        [COUNTY, COUNTY_SITE_URL,
         'Commission agendas, meeting video, budget documents and the records request portal.'],
        [COUNTY . ' Sheriff\'s Office', 'https://www.sjso.org',
         'Agency contact details and published policies.'],
        ['City of ' . COUNTY_SEAT, 'https://www.citystaug.com',
         'The city runs its own police department and its own procurement, separately from the county.'],
        ['Public records we have received', 'https://drive.google.com/drive/folders/1ZieGt1Gx8h4ieFz3p_Wq0wIxPxSAk5dI',
         'Contracts, the sheriff\'s plate reader policy, training material and released record sets, exactly as the agencies sent them. Read them yourself rather than taking our word for any of it.'],
    ],
];
