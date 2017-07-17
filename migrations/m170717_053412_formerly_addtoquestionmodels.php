<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_migrationName
 */
class m170717_053412_formerly_addtoquestionmodels extends BaseMigration
{
    /**
     * Any migration code in here is wrapped inside of a transaction.
     *
     * @return bool
     */
    public function safeUp()
    {
        $table = new Formerly_QuestionRecord();
        $this->addColumnAfter($table->getTableName(), 'mcVar', ColumnType::Varchar, 'type');
        return true;
    }
}
