<?php
namespace Craft;

class Formerly_FormsService extends BaseApplicationComponent
{
	public function getAllForms($indexBy = null)
	{
		$formRecords = Formerly_FormRecord::model()->ordered()->findAll();
		return Formerly_FormModel::populateModels($formRecords, $indexBy);
	}

	public function getFormById($formId)
	{
		$formRecord = Formerly_FormRecord::model()->findById($formId);

		if ($formRecord)
		{
			return Formerly_FormModel::populateModel($formRecord);
		}
	}

	public function getFormByHandle($formHandle)
	{
		$formRecord = Formerly_FormRecord::model()->findByAttributes(array(
			'handle' => $formHandle
		));

		if ($formRecord)
		{
			return Formerly_FormModel::populateModel($formRecord);
		}
	}

	public function saveForm(Formerly_FormModel $form)
	{
		if (!$form)
		{
			return false;
		}

		// get or create record
		$formRecord = Formerly_FormRecord::model()->findById($form->id);
		if (!$formRecord)
		{
			$formRecord = new Formerly_FormRecord();
		}

		// get or create field group
		$fieldGroup = new FieldGroupModel();
		$fieldGroup->id   = $form->fieldGroupId;
		$fieldGroup->name = 'Formerly - '.$form->name;

		// set attributes on record
		$formRecord->name    = $form->name;
		$formRecord->handle  = $form->handle;
		$formRecord->emails  = $form->emails;

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// save field group
			if (craft()->fields->saveGroup($fieldGroup))
			{
				$form->fieldGroupId       = $fieldGroup->id;
				$formRecord->fieldGroupId = $fieldGroup->id;
			}
			else
			{
				$form->addErrors($fieldGroup->getErrors());
			}

			// save record
			$formRecord->validate();
			$form->addErrors($formRecord->getErrors());

			if (!$form->hasErrors())
			{
				$formRecord->save(false);

				if ($transaction !== null)
				{
					$transaction->commit();
				}

				$form->id = $formRecord->id;

				return true;
			}
			else
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				return false;
			}
		}
		catch (\Exception $ex)
		{
			if ($transaction !== null)
			{
				$transaction->rollback();
			}

			throw $ex;
		}
	}

	public function deleteFormById($formId)
	{
		$form = $this->getFormById($formId);
		return $this->deleteForm($form);
	}

	public function deleteForm(Formerly_FormModel $form)
	{
		if (!$form || !$form->id)
		{
			return false;
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// delete form group
			craft()->fields->deleteGroupById($form->fieldGroupId);

			// delete form
			$affectedRows = craft()->db->createCommand()->delete('formerly_forms', array(
				'id' => $form->id
			));

			if ($transaction !== null)
			{
				$transaction->commit();
			}

			return (bool) $affectedRows;
		}
		catch (\Exception $ex)
		{
			if ($transaction !== null)
			{
				$transaction->rollback();
			}
		}
	}

	public function getQuestionsByFormId($formId, $indexBy = null)
	{
		$questionRecords = Formerly_QuestionRecord::model()->ordered()->findAllByAttributes(array(
			'formId' => $formId
		));

		$form = $this->getFormById($formId);
		$fields = craft()->fields->getFieldsByGroupId($form->fieldGroupId, 'id');

		$questions = Formerly_QuestionModel::populateModels($questionRecords, $indexBy);

		foreach ($questions as $key => $question)
		{
			$field = $fields[$question->fieldId];
			$question->setAttributes($this->questionAttributesForField($field));

			if ($field->type == Formerly_QuestionType::Assets)
				$question->type = Formerly_QuestionType::Assets;
			if (strpos($field->handle, Formerly_QuestionType::CustomListHandle) > 0) {
				$question->type = Formerly_QuestionType::CustomList;
			}
			if (strpos($field->handle, Formerly_QuestionType::CustomHandle) > 0) {
				$question->type = Formerly_QuestionType::Custom;
			}
			if (strpos($field->handle, Formerly_QuestionType::RawHTMLHandle) > 0) {
				$question->type = Formerly_QuestionType::RawHTML;
			}
		}

		return $questions;
	}

	public function getQuestionById($questionId)
	{
		$questionRecord = Formerly_QuestionRecord::model()->findById($questionId);

		if ($questionRecord)
		{
			$question = Formerly_QuestionModel::populateModel($questionRecord);

			$field = craft()->fields->getFieldById($question->fieldId);
			$question->setAttributes($this->questionAttributesForField($field));

			return $question;
		}
	}

	public function saveQuestion(Formerly_QuestionModel $question)
	{
		if (!$question)
		{
			return false;
		}

		// get form
		$form = $this->getFormById($question->formId);
		if (!$form)
		{
			$question->addError('formId', "No form with ID '$question->formId' exists");
			return false;
		}

		// get or create record
		$questionRecord = Formerly_QuestionRecord::model()->findById($question->id);
		if (!$questionRecord)
		{
			$questionRecord = new Formerly_QuestionRecord();
		}

		if ($question->type == Formerly_QuestionType::CustomList) {
			$question->type = Formerly_QuestionType::Checkboxes;
			$question->handle = Formerly_QuestionType::CustomListHandle . $question->handle;
		}
		elseif ($question->type == Formerly_QuestionType::Custom) {
			$question->handle = Formerly_QuestionType::CustomHandle . $question->handle;
		}
		elseif ($question->type == Formerly_QuestionType::RawHTML) {
			$question->handle = Formerly_QuestionType::RawHTMLHandle . $question->handle;
		}

		// get or create field
		$field = $this->fieldForQuestion($question, $form->handle);
		$field->id      = $questionRecord->fieldId;
		$field->groupId = $form->fieldGroupId;
		$field->instructions 	= $question->instructions;

		// set attributes on record
		$questionRecord->formId    = $question->formId;
		$questionRecord->required  = $question->required;
		$questionRecord->sortOrder = $question->sortOrder;
		$questionRecord->type	   = $question->type;
		$questionRecord->errorMessage = $question->errorMessage;
		$questionRecord->validationPattern = $question->validationPattern;



		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// save field
			if (craft()->fields->saveField($field))
			{
				$question->fieldId       = $field->id;
				$questionRecord->fieldId = $field->id;
			}
			else
			{
				$question->addErrors($field->getErrors());
			}

			// save record
			$questionRecord->validate();
			$question->addErrors($questionRecord->getErrors());

			if (!$question->hasErrors())
			{
				$questionRecord->save(false);

				if ($transaction !== null)
				{
					$transaction->commit();
				}

				$question->id = $questionRecord->id;

				return true;
			}
			else
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}

				return false;
			}
		}
		catch (\Exception $ex)
		{
			if ($transaction !== null)
			{
				$transaction->rollback();
			}

			throw $ex;
		}
	}

	private function questionAttributesForField(FieldModel $field)
	{
		$attributes = array(
			'name'   		=> $field->name,
			'handle' 		=> $field->handle,
			'instructions' 	=> $field->instructions,
		);

		switch ($field->type)
		{
			case 'Dropdown':
			case 'RadioButtons':
			case 'CustomList':
			case 'Checkboxes':

				$attributes['options'] = $field->settings['options'];

				break;

			case 'Assets':

				// todo

				break;
		}

		return $attributes;
	}

	private function fieldForQuestion(Formerly_QuestionModel $question, $prefix = '')
	{
		$field = new FieldModel();

		$field->name   = $question->name;
		$field->handle = $prefix ? $prefix.'_'.$question->handle : $question->handle;

		switch ($question->type)
		{
			case Formerly_QuestionType::PlainText:
			case Formerly_QuestionType::MultilineText:

				$field->type = 'PlainText';
				$field->settings = array(
					'multiline' => $question->type == Formerly_QuestionType::MultilineText
				);

				break;

			case Formerly_QuestionType::Dropdown:
			case Formerly_QuestionType::RadioButtons:
			case Formerly_QuestionType::Checkboxes:

				$field->type = $question->type;
				$field->settings = array(
					'options' => $question->options
				);

				break;

			case Formerly_QuestionType::CustomList:

				$field->type = 'Checkboxes';
				$field->settings = array(
					'options' => $question->options
				);

				break;

			case Formerly_QuestionType::Assets:

				$folderId = 1;
				$allowedKinds = array("excel","image","pdf","text","word");

				if (craft()->config->exists(Formerly_ConfigSettings::SettingsGroupName))
				{
					if (array_key_exists(Formerly_ConfigSettings::UploadAssetFolderId, craft()->config->get(Formerly_ConfigSettings::SettingsGroupName)))
					{
						$folderId = craft()->config->get(Formerly_ConfigSettings::SettingsGroupName)[Formerly_ConfigSettings::UploadAssetFolderId];
					}
					if (array_key_exists(Formerly_ConfigSettings::AllowedKinds, craft()->config->get(Formerly_ConfigSettings::SettingsGroupName)))
					{
						$allowedKinds = craft()->config->get(Formerly_ConfigSettings::SettingsGroupName)[Formerly_ConfigSettings::AllowedKinds];
					}
				}

				$field->type = 'Assets';
				$field->settings = array(
					'useSingleFolder' 				=> '1',
					'sources'						=> array('folder:' . $folderId),
					'defaultUploadLocationSource'	=> '1',
					'defaultUploadLocationSubpath' 	=> '',
					'singleUploadLocationSource'	=> $folderId,
					'singleUploadLocationSubpath'	=> '',
					'restrictFiles'					=> '1',
					'allowedKinds'					=> $allowedKinds,
					'limit'							=> '1'
				);
				break;

			case Formerly_QuestionType::RawHTML:
				$field->type = 'PlainText';

			case Formerly_QuestionType::Email:
				$field->type = 'PlainText';
				break;

			case Formerly_QuestionType::Tel:
				$field->type = 'PlainText';
				break;

			case Formerly_QuestionType::Url:
				$field->type = 'PlainText';
				break;

			case Formerly_QuestionType::Number:
				$field->type = 'Number';
				break;

			case Formerly_QuestionType::Date:
				$field->type = 'Date';
				break;

			case Formerly_QuestionType::Custom:
				$field->type = 'PlainText';
				break;

		}


		return $field;
	}

	public function deleteQuestion(Formerly_QuestionModel $question)
	{
		if (!$question || !$question->id)
		{
			return false;
		}

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// delete field
			craft()->fields->deleteFieldById($question->fieldId);

			// delete question
			$affectedRows = craft()->db->createCommand()->delete('formerly_questions', array(
				'id' => $question->id
			));

			if ($transaction !== null)
			{
				$transaction->commit();
			}

			return (bool) $affectedRows;
		}
		catch (\Exception $ex)
		{
			if ($transaction !== null)
			{
				$transaction->rollback();
			}
		}
	}

	public function getFieldLayout(Formerly_FormModel $form)
	{
		if (!$form || !$form->id)
		{
			return false;
		}

		$questions = $this->getQuestionsByFormId($form->id);

		$fields = array();

		foreach ($questions as $question)
		{
			$field = new FieldLayoutFieldModel();
			$field->fieldId   = $question->fieldId;
			$field->required  = $question->required;
			$field->sortOrder = $question->sortOrder;

			$fields[] = $field;
		}

		$layout = new FieldLayoutModel();
		$layout->type = 'Form';
		$layout->setFields($fields);

		return $layout;
	}
}
