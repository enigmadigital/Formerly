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

	public function downloadLink($handle) {
		if (sizeof($this[$handle]) > 0) {
			return '<a href="/admin/formerly/survey/file/' . $this[$handle][0]->id . '">Download <i>' . $this[$handle][0]->title . '</i></a>';
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

			if ($question->type != Formerly_QuestionType::Assets &&
				$question->type != Formerly_QuestionType::CustomList &&
				$question->type != Formerly_QuestionType::RawHTML &&
				$question->type != Formerly_QuestionType::Custom )
			{
				$name = $question->name;
				$value = $this[$question->handle];

				$summary .= $name . ":";

				if ($value instanceof MultiOptionsFieldData) {
					$options = $value->getOptions();

					for ($j = 0; $j < count($options); ++$j) {
						$option = $options[$j];

						$summary .= '<br />' . $option->label . ($option->value != $option->label ?  '( ' . $option->value . ' )' : '') . ': ' .  ($option->selected ? 'yes' : 'no') ;


						if ($j != count($options) - 1) {
							$summary .= "<br />";
						}
					}
				} else {
					$summary .= str_replace("\n", '<br />', $value) . '<br />';
				}

				if ($i != count($questions) - 1) {
					$summary .= "<br />";
				}
			}
		}

		return $summary;
	}
}
