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
		$formRecord->name        = $form->name;
		$formRecord->handle      = $form->handle;
		$formRecord->toAddress   = $form->toAddress;
		$formRecord->fromAddress = $form->fromAddress;
		$formRecord->subject     = $form->subject;

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

		// get or create field
		$field = $this->fieldForQuestion($question, $form->handle);
		$field->id      = $questionRecord->fieldId;
		$field->groupId = $form->fieldGroupId;

		// set attributes on record
		$questionRecord->formId    = $question->formId;
		$questionRecord->required  = $question->required;
		$questionRecord->sortOrder = $question->sortOrder;

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
			'name'   => $field->name,
			'handle' => $field->handle
		);

		switch ($field->type)
		{
		case 'PlainText':

			$attributes['type'] = isset($field->settings['multiline']) && $field->settings['multiline']
				? Formerly_QuestionType::MultilineText
				: Formerly_QuestionType::PlainText;

			break;

		case 'Dropdown':
		case 'RadioButtons':
		case 'Checkboxes':

			$attributes['type']    = $field->type;
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

			case Formerly_QuestionType::FileUpload:

				// todo

				break;



			case Formerly_QuestionType::Email:
				// todo
				break;

			case Formerly_QuestionType::Tel:
				// todo
				break;

			case Formerly_QuestionType::Url:
				// todo
				break;

			case Formerly_QuestionType::Number:
				// todo
				break;

			case Formerly_QuestionType::Date:
				// todo
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
