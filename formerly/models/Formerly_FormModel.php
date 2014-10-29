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
			'handle'        => AttributeType::String,
			'toAddress'     => AttributeType::String,
			'fromAddress'   => AttributeType::String,
			'subject'       => AttributeType::String,
		);
	}

	public function getQuestions()
	{
		return craft()->formerly_forms->getQuestionsByFormId($this->id);
	}

	public function getFieldLayout()
	{
		return craft()->formerly_forms->getFieldLayout($this);
	}
}
