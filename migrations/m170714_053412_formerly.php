<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m170714_053412_formerly extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
        $table = new Formerly_FormRecord();
        $this->addColumnAfter($table->getTableName(), 'mailchimp', ColumnType::Bool, 'emails');
        $this->addColumnAfter($table->getTableName(), 'mailchimpUser', 'Varchar(255)', 'mailchimp');
        $this->addColumnAfter($table->getTableName(), 'mailchimpApiKey', 'Varchar(255)', 'mailchimpUser');
        $this->addColumnAfter($table->getTableName(), 'mailchimpListId', 'Varchar(255)', 'mailchimpApiKey');
        return true;
	}
}
