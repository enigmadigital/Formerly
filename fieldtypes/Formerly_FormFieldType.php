<?php
namespace Craft;

class Formerly_FormFieldType extends BaseFieldType
{
	public function getName()
	{
		return Craft::t('Formerly Form');
	}

	public function getInputHtml($name, $value)
	{
		$forms = craft()->formerly_forms->getAllForms();

        $options = array(0 => '-- Select a form --'	);
		foreach ($forms as $form)
		{
			$options[$form->handle] = $form->name;
		}

		return craft()->templates->render('_includes/forms/select', array(
			'name'    => $name,
            'value'   => (get_class($value) == 'Craft\Formerly_FormModel') ? $value->handle : null,
			'options' => $options
		));
	}

	public function prepValue($value)
	{
		return craft()->formerly_forms->getFormByHandle($value);
	}
}
