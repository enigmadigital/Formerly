<?php
namespace Craft;

include_once(dirname(__FILE__) . '/../enums/Formerly_QuestionType.php');

class Formerly_QuestionRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'formerly_questions';
	}

	protected function defineAttributes()
	{
		return array(
			'required'      => array(AttributeType::Bool, 'required' => true),
			'sortOrder'     => array(AttributeType::SortOrder, 'required' => true),
			'type'          => array(AttributeType::Enum, 'required' => true, 'values' => array(
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
		);
	}

	public function defineRelations()
	{
		return array(
			'form'        => array(static::BELONGS_TO, 'Formerly_FormRecord', 'onDelete' => static::CASCADE),
			'field'       => array(static::BELONGS_TO, 'FieldRecord', 'onDelete' => static::CASCADE),
		);
	}

	public function scopes()
	{
		return array(
			'ordered' => array('order' => 'sortOrder'),
		);
	}
}
