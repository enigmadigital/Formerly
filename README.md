# Formerly 1.3.5

## Installing

1. Copy the `formerly` directory into your `craft/plugins` directory
2. Browse to Settings > Plugins in the Craft CP
3. Click on the Install button next to Formerly

## Setting up forms

Users (both admin and client) can easily create forms in the Formerly admin
section, under the Forms tab. A form consists of:

* A name, used as the title of the form that a visitor to the site may see
* A handle, used to programatically refer to this form
* Several emails that will be sent upon the submission of a form by a visitor
* Several questions which make up the form

## Outputting the form

A form can be rendered using, e,g.

```twig
{% set form = craft.formerly.form('handle') %}
{% include 'form.html' with { form: form } %}
```

Where `form.html` is Twig code that iterates through the form's questions,
creating form markup. An example  `form.html` is provided with the source code
for this plugin.

You can also obtain a form instance by having a content editor select a form
using the Formerly field type, e.g.

```twig
{% include 'form.html' with { form: entry.form } %}
```

## Viewing submissions

Form submissions are a Craft Element, which means that you can query them using
regular `ElementCriteriaModel`s. For example, to list submissions to the
`competition` form who have selected Australia as their country:

```twig
{% set submissions = craft.formerly.submissions
  .form('competition')
  .country('Australia')
  .sort('dateCreated desc') %}
{% for submission in submissions %}
  Name: {{ submission.name }}
  Email: {{ submission.email }}
  {# ... #}
{% endfor %}
```

## Updates
* 1.3.5
  * change settings back to use newer syntax
  * Add html markup to default email
  * add sendEmails flag, so you can disable email sending in an environment (eg. dev)
      ```php
      'formerly' => array(
          'sendEmails' => false
      ),      
      ```
  * add writeEmailBodyToFilePath setting, logs emails in json format in the path specified
      ```php
      'formerly' => array(
        'writeEmailBodyToFilePath' => '/vagrant/emails',
      )
      ```
* 1.3.4
  * Add to and from date filters to export
  * replace \n with <br> for multilinetext submissions
  * just show multioption selected value not all available options
  * Fix memory error when exporting large csv, queries in blocks 500 submissions rather than all
* 1.3.3
  * fix to honeypot code so syntax works in php 5.2
* 1.3.2
  * support for ajax posting of forms
  * file upload type (add 'assetFolderId' => <id of asset folder to store files> to your config)
  * remove check for if current form email has already submitted form, this should be the exception rather than rule. You may want users to be able to post multiple times (for example a bug reporting form). If you want this functionality add it yourself using an ajax action:
  * add handle instruction to questions so it is clearer what tag to use in emails
  * Expose instructions so you can enter extra long labels
  * Add value to list items so you can now enter label and value
  * new "customlist" type, similar to "custom" but for lists
  * Don't show checkboxes in the submission list view (they take way too much space)
  * Create RawHTML type for output blocks of html on a form
  * Don't crash if form tags are wrong in the email template, just leave the tags un-replaced
  * bug fix - don't crash if styles are in the email template
  * Simple honeypot checking (add 'honeyPotName' => 'my_cool_name' to general settings, submission code will check for the existance of a post value with that name)

* 1.3.1
  * Remove limit to 100 export items
  * Fix bug to display multiple options in submission results (alexbrindalsl)
  * Fix multiple form dropdown not remembering which form selected and and - select form option (Silvaire)
  * Fix export multiple options (joshangell)
  * Add "custom" field type (alexbrindalsl's coded field type)
  * fix checkbox bug in sample
* 1.3.0
    * Add ability to use the format `John Smith <john.smith@website.com>` in the
    from field.
* 1.2.0
	* Add ability to delete submissions.
	* Fix error when viewing submissions.
* 1.1.2
	* Updated edit form UI to follow new Craft 2.3 Matrix appearance.
* 1.1.1
	* Fix exception thrown when submitting forms.
* 1.1.0
	* Add ability to export form submissions as CSV.
* 1.0.2
	* Fix error when submitting form while not logged into Craft.
* 1.0.1
	* Fix error preventing questions from saving properly.
* 1.0.0
	* Initial release!

## Todo

* Better validation
* More question attributes, e.g. placeholder, error messages
