<?php
namespace Craft;

class Formerly_SubmissionModel extends BaseElementModel
{
	protected $elementType = 'Formerly_Submission';

	protected function defineAttributes()
	{
		return array_merge(parent::defineAttributes(), array(
			'formId'     => AttributeType::Number,
		));
	}

	public function isEditable()
	{
		return true;
	}

	public function getCpEditUrl()
	{
		$form = $this->getForm();

		if ($form)
		{
			return UrlHelper::getCpUrl('formerly/'.$form->handle.'/'.$this->id);
		}
	}

	public function getFieldLayout()
	{
		$form = $this->getForm();

		if ($form)
		{
			return $form->getFieldLayout();
		}
	}

	public function getForm()
	{
		if ($this->formId)
		{
			return craft()->formerly_forms->getFormById($this->formId);
		}
	}

	public function __toString()
	{
		return "#$this->id";
	}

	public function getSummary()
	{
		$summary = '';

		$questions = $this->getForm()->getQuestions();
		for ($i = 0; $i < count($questions); ++$i)
		{
			$question = $questions[$i];

			$summary .= $question->name . "\n: " . $this[$question->handle];
			if ($i != count($questions) - 1)
			{
				$summary .= "\n\n";
			}
		}

		return $summary;
	}
}
