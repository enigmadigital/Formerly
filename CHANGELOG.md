# Change Log

## [1.5.3]
- Add Mailchimp integration into individual forms.

## [1.5.2]
- Merge PRs [#52](https://github.com/xodigital/Formerly/pull/52) [#53](https://github.com/xodigital/Formerly/pull/53) [#80](https://github.com/xodigital/Formerly/pull/80) [#81](https://github.com/xodigital/Formerly/pull/81) [#82](https://github.com/xodigital/Formerly/pull/82) [#83](https://github.com/xodigital/Formerly/pull/83)

## [1.5.1]
- Fix email template submission so twig filters can be used. This helps with a lot of reported issues. You can now format dates the way you want. And also output multi value fields like checkbox lists using {mycheckboxlist|join(',')}

## [1.5.0]
- HOT FIX - Submissions view not working in Craft 2.5. It now works. I haven't quite got the new settings filter working, but the priority was to get a release out there that works

## [1.4.0]
- Add Customer error and validation pattern to question
- collapse question details (with the extra fields it was getting out of control)
- add getQuestionbyHandle method
- id {id} and {siteUrl} tag replacement to emails. id is replaced by submissionid, siteUrl by craft()->config->get("siteUrl")
- comma listing bug in submission view
- Add timezoneoffset setting to adjust mysql dates for charting (eg. add 10 hours for Sydney)

  ```php
  'formerly' => array(
      'timezoneOffset' => '+10:00', //Australia/Sydney
    ),
  ```

## [1.3.6]
- Desktop widget
- parameter option to submissions page - pass sourcekey=<formId> in the url and it will default to that form

## [1.3.5]
- change settings back to use newer syntax
- Add html markup to default email
- add sendEmails flag, so you can disable email sending in an environment (eg. dev)

  ```php
  'formerly' => array(
      'sendEmails' => false
  ),
  ```
- add writeEmailBodyToFilePath setting, logs emails in json format in the path specified

  ```php
  'formerly' => array(
    'writeEmailBodyToFilePath' => '/vagrant/emails',
  )
  ```

## [1.3.4]
- Add to and from date filters to export
- replace \n with <br> for multilinetext submissions
- just show multioption selected value not all available options
- Fix memory error when exporting large csv, queries in blocks 500 submissions rather than all

## [1.3.3]
- fix to honeypot code so syntax works in php 5.2

## [1.3.2]
- support for ajax posting of forms
- file upload type (add 'assetFolderId' => <id of asset folder to store files> to your config)
- remove check for if current form email has already submitted form, this should be the exception rather than rule. You may want users to be able to post multiple times (for example a bug reporting form). If you want this functionality add it yourself using an ajax action:
- add handle instruction to questions so it is clearer what tag to use in emails
- Expose instructions so you can enter extra long labels
- Add value to list items so you can now enter label and value
- new "customlist" type, similar to "custom" but for lists
- Don't show checkboxes in the submission list view (they take way too much space)
- Create RawHTML type for output blocks of html on a form
- Don't crash if form tags are wrong in the email template, just leave the tags un-replaced
- bug fix - don't crash if styles are in the email template
- Simple honeypot checking (add 'honeyPotName' => 'my_cool_name' to general settings, submission code will check for the existance of a post value with that name)

## [1.3.1]
- Remove limit to 100 export items
- Fix bug to display multiple options in submission results (alexbrindalsl)
- Fix multiple form dropdown not remembering which form selected and and - select form option (Silvaire)
- Fix export multiple options (joshangell)
- Add "custom" field type (alexbrindalsl's coded field type)
- fix checkbox bug in sample

## [1.3.0]
- Add ability to use the format `John Smith <john.smith@website.com>` in the
from field.

## [1.2.0]
- Add ability to delete submissions.
- Fix error when viewing submissions.

## [1.1.2]
- Updated edit form UI to follow new Craft 2.3 Matrix appearance.

## [1.1.1]
- Fix exception thrown when submitting forms.

## [1.1.0]
- Add ability to export form submissions as CSV.

## [1.0.2]
- Fix error when submitting form while not logged into Craft.

## [1.0.1]
- Fix error preventing questions from saving properly.

## [1.0.0]
- Initial release!
