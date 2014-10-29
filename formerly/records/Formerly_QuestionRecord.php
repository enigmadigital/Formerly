<?php
namespace Craft;

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
