<?php
namespace Craft;

class Formerly_FormModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'            => AttributeType::Number,
			'fieldGroupId'  => AttributeType::Number,
			'name'          => AttributeType::String,
			'instructions'  => AttributeType::String,
			'handle'        => AttributeType::String,
			'emails'        => AttributeType::Mixed,
		);
	}

	public function getQuestions()
	{
		return craft()->formerly_forms->getQuestionsByFormId($this->id);
	}

	public function getQuestionByHandle($handle) {
		foreach (craft()->formerly_forms->getQuestionsByFormId($this->id) as $question) {
			if ($question->handle == $handle)
				return $question;
		}
		return null;
	}

	public function getFieldLayout()
	{
		return craft()->formerly_forms->getFieldLayout($this);
	}
}
