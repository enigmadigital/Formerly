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

			$name  = $question->name;
			$value = $this[$question->handle];

			$summary .= $name . ":\n";

			if ($value instanceof MultiOptionsFieldData)
			{
				$options = $value->getOptions();

				for ($j = 0; $j < count($options); ++$j)
				{
					$option = $options[$j];

					$summary .= $option->label . ': ' . ($option->selected ? 'yes' : 'no');
				 
					if ($j != count($options) - 1)
					{
						$summary .= "\n";
					}
				}
			}
			else
			{
				$summary .= $value;
			}

			if ($i != count($questions) - 1)
			{
				$summary .= "\n\n";
			}
		}

		return implode("\n\n", $summary);
	}
}
