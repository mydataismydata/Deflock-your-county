<?php
/**
 * The interchangeable parts of the email to a commissioner.
 *
 * The page picks one entry from each array per request, so two people who copy
 * the draft on the same afternoon do not send the same letter. Five subjects,
 * four openings, seven evidence paragraphs, three value lines, three hand-offs
 * and four closings make 5,040 combinations out of 26 snippets.
 *
 * Two rules for anything added here.
 *
 * Every variant has to be as strong as the others. A weak one does not get
 * sent less often; it gets sent to a fifth of the board.
 *
 * The four asks never vary. They live in get-involved.php, not in this file. A
 * board that receives four different versions of what we want gets to pick the
 * weakest, and the campaign argues with itself in public.
 *
 * The wording is the group's own, taken from .working/letter-snippets.md. Do
 * not smooth it out here. Edit that file and regenerate, so the two agree.
 */

declare(strict_types=1);

require_once __DIR__ . '/place.php';

// Offices sort on the subject before reading anything, and identical
// subjects are the fastest form-letter tell there is. Keep each under about
// 60 characters.
const LETTER_SUBJECT = [
    'Please vote to end the county license plate reader contract',
    'Constituent request: cancel the Flock Safety contract',
    'Duval, Clay and Bradford stopped. Will St. Johns?',
    'Asking you to end the ALPR program in '
        . COUNTY,
    'URGENT: Remove ALPR cameras from '
        . COUNTY,
];

// Establishes that the writer lives here, and states the demand. Where a
// variant carries the district, it sits in the first line on purpose: that
// is where an aide looks to decide constituent or out-of-county form letter.
const LETTER_STANDING = [
    'I live in [neighborhood], and I am writing as a resident and a '
        . 'taxpayer to ask you to end warrantless mass surveillance in '
        . COUNTY
        . '.',
    'I am concerned about the county\'s use of surveillance cameras and '
        . 'believe these are a threat to our privacy. I am a resident of '
        . 'District [number], in [neighborhood].',
    'My name is [name] and I live in [neighborhood]. I am asking you to'
        . ' cancel the county contract for automated license plate readers.',
    'I vote in District [number] and I live in [neighborhood]. I am '
        . 'writing to ask you to take the county out of the business of '
        . 'recording every driver who passes a camera.',
];

// Every claim here has to trace to a link in LETTER_SOURCES. An aide who
// checks one claim and finds it wrong discounts every other letter in the
// pile.
const LETTER_EVIDENCE = [
    'On August 31st, the Florida Department of Transportation revoked '
        . 'the permits it had issued for license plate readers on state '
        . 'roads, citing an exponential increase in Flock cameras, public '
        . 'outcry and concerning reports of misuse. The Jacksonville, Clay '
        . 'County and Bradford County sheriffs all announced they would stop.'
        . ' The '
        . COUNTY
        . ' Sheriff\'s Office has only recently announced that they will take '
        . 'down only the cameras on state roadways.',
    'Every neighboring agency has already stepped back. Jacksonville '
        . 'halted its program, Clay County is removing every reader from its '
        . 'roadways, and Bradford County stopped outright, all after the '
        . 'state pulled the road permits. '
        . COUNTY
        . ' is the holdout, and this board funds the contract that keeps it '
        . 'that way.',
    'The public audit logs at HaveIBeenFlocked.com record 190,266 '
        . 'searches involving the '
        . COUNTY
        . ' Sheriff\'s Office between August 2022 and July 2026, by roughly '
        . '278 different people. Some of those were run by agencies outside '
        . COUNTY
        . '. That is the scale of a system this board pays for and has never '
        . 'voted on in the open.',
    'Two things happened this summer. The state revoked the road '
        . 'permits for these cameras, giving misuse and data privacy as its '
        . 'reasons. And the public audit logs put 190,266 searches against '
        . 'this sheriff\'s office since 2022. Neither has produced a public '
        . 'vote in this county.',
    'Sheriff Hardwick admitted in an interview with the 904 Now that he'
        . ' shares county data with outside agencies, including the FBI.',
    'Our county data was previously shared with many of the more than '
        . '6,000 agencies who also use Flock Safety. Sheriff Hardwick claimed'
        . ' at the last county budget meeting that they no longer share data,'
        . ' which is an acknowledgement that this was a mistake.',
    'Flock collects this data and can package and sell it for profit. '
        . 'The more data they collect, the more valuable it becomes, and the '
        . 'easier it is to tie an "anonymous" data point back to a person.',
];

// One sentence. The bridge between the evidence and the asks.
const LETTER_VALUES = [
    'Our community deserves safety without sacrificing our fundamental '
        . 'right to privacy.',
    'Public safety and a warrant requirement are not in conflict. '
        . 'Deputies investigated crimes before these cameras went up and can '
        . 'investigate them after they come down.',
    'I am not asking the county to do less about crime. I am asking it '
        . 'to stop keeping a record of everyone who has not committed one.',
];

// The hand-off to the four asks.
const LETTER_LEADIN = [
    'I urge you to immediately take these four actions:',
    'I am asking the board for the following:',
    'These are within the board\'s power:',
];

// Every one asks for a commitment rather than consideration. "Please
// consider" is the sentence that lets a letter be filed.
const LETTER_CLOSE = [
    'I look forward to your response detailing how you intend to vote '
        . 'on this matter.',
    'Will you vote to end the contract? Thank you in advance for your '
        . 'response.',
    'Please tell me if you support removing these systems, and when the'
        . ' item will appear on an agenda.',
    'I would like a reply that says if you are for or against these '
        . 'cameras. If you have not decided, please tell me what would decide'
        . ' it.',
];

// Printed at the foot of every letter, whichever evidence paragraph came up. A
// claim without a link is a claim an office can ignore.
const LETTER_SOURCES = [
    ['Florida DOT directive, and the agencies that followed it',
     'https://www.news4jax.com/news/local/2026/08/31/jso-ends-license-plate-reader-use-following-fdots-directive/'],
    [COUNTY . ' Sheriff\'s Office search audit log',
     'https://haveibeenflocked.com/pd/3775-st-johns-county-fl-so/audit?sort=date_desc'],
    ['Sheriff Hardwick interviewed by The 904 Now',
     'https://youtu.be/jaG-TAOtfbg'],
];
