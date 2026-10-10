<?php
/**
 * The candidate questionnaire: the memo at its head, the nine yes-or-no
 * questions, the open question, the signature, and the default wording of the
 * email that carries each candidate's link.
 *
 * The questions are the group's own, copied from the Google Form this page
 * replaced. Do not smooth them out here.
 *
 * Every response keeps a copy of the questions it answered. Editing a question
 * here changes what the next candidate sees and leaves what an earlier
 * candidate said yes or no to exactly as they saw it.
 */

declare(strict_types=1);

require_once __DIR__ . '/place.php';

// ------------------------------------------------------------------ title --

const SURVEY_KIND  = 'Candidate Questionnaire';
const SURVEY_TOPIC = 'Mass Surveillance & Constitutional Rights';

// The full title, as the subject line of the copies and the top of their text.
const SURVEY_TITLE = 'CANDIDATE QUESTIONNAIRE: ' . SURVEY_TOPIC . ' - ' . STATE;

// ------------------------------------------------------------------- memo --

// Printed as label and value, in this order, above the questions.
const SURVEY_MEMO = [
    'TO'       => 'Candidates / Elected Officials',
    'FROM'     => BRAND_HEAD . BRAND_TAIL . ' ' . COUNTY . ' and Residents of ' . STATE,
    'DATE'     => 'October 10th, 2026',
    'DEADLINE' => 'October 23rd, 2026 at 5:00 PM',
];

const SURVEY_RULES_HEADING = 'Submission Guidelines & Rules of Engagement:';

const SURVEY_RULES = [
    ['Strict Format Enforcement',
     'Questions 1 through 9 require a definitive YES or NO choice. Modified answers, '
     . 'skipped questions, or added qualifying notes within Part 1 will be noted '
     . 'publicly in the voter guide as "Refused to Answer Directly."'],
    ['Open Explanation',
     'Question 10 provides you the opportunity to explain your overarching stance '
     . 'and philosophy in your own words.'],
    ['Public Record',
     'Your exact responses, or your choice to decline participation, will be '
     . 'published in full to the public without editorial modifications or commentary.'],
];

// --------------------------------------------------------------- identity --

const SURVEY_NAME_LABEL   = 'Candidate Full Name (Printed)';
const SURVEY_OFFICE_LABEL = 'Office Sought or Branch';

// ----------------------------------------------------------------- part 1 --

const SURVEY_PART1      = 'PART 1: YES / NO POSITIONS';
const SURVEY_PART1_HINT = '(Select one option per row)';

// The keys are what a stored response files each answer under. Never reuse a
// key for a different question.
const SURVEY_POSITIONS = [
    'q1' => '1. Do you support ending the warrantless collection of American citizens\' '
          . 'data and communications under Section 702 of the Foreign Intelligence '
          . 'Surveillance Act (FISA)?',
    'q2' => '2. Will you oppose any legislation or executive directive that mandates tech '
          . 'companies to build "backdoors" into end-to-end encrypted messaging services '
          . 'for law enforcement?',
    'q3' => '3. Do you support a complete ban on law enforcement agencies purchasing '
          . 'Americans\' private location, browsing, and personal data from commercial '
          . 'data brokers without a judicial warrant?',
    'q4' => '4. Do you support a full legislative repeal of the bulk data collection '
          . 'authorities originally granted under the USA PATRIOT Act?',
    'q5' => '5. Do you support a total ban on the use of biometric facial recognition '
          . 'surveillance technologies by local law enforcement and municipal agencies '
          . 'in public spaces?',
    'q6' => '6. Will you vote to prohibit or dismantle localized networks of automated '
          . 'license plate readers that log, track, and store the daily travel histories '
          . 'of citizens who are not suspected of a crime?',
    'q7' => '7. Do you oppose the integration of local private security cameras into '
          . 'centralized, real-time police monitoring networks without explicit, '
          . 'case-by-case owner consent?',
    'q8' => '8. Will you vote against the funding or utilization of predictive policing '
          . 'software that uses algorithmic data modeling to preemptively target specific '
          . 'neighborhoods?',
    'q9' => '9. Do you support a mandatory local ordinance requiring police departments to '
          . 'obtain explicit city council approval and hold public hearings before '
          . 'deploying any new surveillance technology?',
];

// The two answers a row accepts. Nothing else is stored.
const SURVEY_CHOICES = ['Yes', 'No'];

// ----------------------------------------------------------------- part 2 --

const SURVEY_PART2 = 'PART 2: DETAILED POLICY STANCE';

const SURVEY_STANCE = '10. The Balance of Power: Where do you draw the line between a '
    . 'government\'s duty to protect public safety and its constitutional obligation to '
    . 'respect the privacy of its citizens? Please explain your overarching political '
    . 'stance on mass surveillance, and justify why you believe your approach aligns with '
    . 'the core principles of a free society.';

// -------------------------------------------------------------- signature --

const SURVEY_SIGNATURE_HEADING = 'Digital Signature & Public Record Authorization';

const SURVEY_SIGNATURE_TEXT = 'By typing my full name below, I certify that I am the '
    . 'candidate or an authorized campaign representative, that these answers accurately '
    . 'represent my official platform, and that I understand these responses will be '
    . 'published in full to the public record.';

// ------------------------------------------------------------- the invite --

// Two placeholders the invite email fills in for each candidate. The name
// works in the subject and the message, the link in the message only. A
// message with no link placeholder gets the link on its own line at the end.
const SURVEY_FILL_NAME = '{{candidate-name}}';
const SURVEY_FILL_LINK = '{{survey-link}}';

// What the subject and message fields on /survey-admin start out holding,
// when includes/survey-invite.php does not exist.
//
// The group's real wording lives in that file instead. Git ignores it, because
// it signs off with a member's name and the group's inbox, and this repository
// is public. It returns ['subject' => ..., 'body' => ...]. The deploy bundle
// carries it with the rest of includes/.
//
// An admin can rewrite both on /survey-admin, and the page keeps their version
// until they put the default back.
const SURVEY_INVITE_SUBJECT = 'Candidate questionnaire on mass surveillance';

const SURVEY_INVITE_BODY = "TO: " . SURVEY_FILL_NAME . "\n"
    . "\n"
    . "Dear Candidate,\n"
    . "\n"
    . "Please complete our 10-question survey on mass surveillance:\n"
    . "\n"
    . SURVEY_FILL_LINK;
