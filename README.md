# Formerly 1.0.1

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

## Todo

* File uploads
* Better validation
* More question attributes, e.g. placeholder, error messages
