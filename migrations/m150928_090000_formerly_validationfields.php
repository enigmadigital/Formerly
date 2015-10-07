<?php
namespace Craft;

class m150928_090000_formerly_validationfields extends BaseMigration
{
  public function safeUp()
  {

    $table = new Formerly_QuestionRecord;
    $this->addColumnAfter($table->getTableName(), 'errorMessage', ColumnType::LongText, 'type');
    $this->addColumnAfter($table->getTableName(), 'validationPattern', 'Varchar(255)', 'type');
    return true;
  }
}