<?php
namespace Craft;

class Formerly_SubmissionRecord extends BaseRecord
{
	public function getTableName()
	{
		return 'formerly_submissions';
	}

	protected function defineAttributes()
	{
		return array(
		);
	}

	public function defineRelations()
	{
		return array(
			'element'  => array(static::BELONGS_TO, 'ElementRecord', 'id', 'required' => true, 'onDelete' => static::CASCADE),
			'form' => array(static::BELONGS_TO, 'Formerly_FormRecord', 'required' => true, 'onDelete' => static::CASCADE),
		);
	}
}
