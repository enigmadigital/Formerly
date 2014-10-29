<?php
namespace Craft;

include_once(dirname(__FILE__) . '/../enums/Formerly_QuestionType.php');

class Formerly_QuestionModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'            => AttributeType::Number,
			'formId'        => AttributeType::Number,
			'fieldId'       => AttributeType::Number,
			'name'          => AttributeType::String,
			'handle'        => AttributeType::String,
			'required'      => AttributeType::Bool,
			'type'          => array(AttributeType::Enum, 'values' => array(
				Formerly_QuestionType::PlainText,
				Formerly_QuestionType::MultilineText,
				Formerly_QuestionType::Dropdown,
				Formerly_QuestionType::RadioButtons,
				Formerly_QuestionType::Checkboxes,
				//Formerly_QuestionType::FileUpload,
				Formerly_QuestionType::Email,
				Formerly_QuestionType::Tel,
				Formerly_QuestionType::Url,
				Formerly_QuestionType::Number,
				Formerly_QuestionType::Date
			)),
			'options'       => AttributeType::Mixed,
			'sortOrder'     => AttributeType::SortOrder
		);
	}
}
