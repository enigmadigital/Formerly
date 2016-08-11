<?php
namespace Craft;

class Formerly_SubmissionElementType extends BaseElementType
{
	public function getName()
	{
		return Craft::t('Submissions');
	}

	public function hasContent()
	{
		return true;
	}

	public function hasTitles()
	{
		return false;
	}

	public function getSources($context = null)
	{
		$sources = array();

		foreach (craft()->formerly_forms->getAllForms() as $form)
		{
			$sources['formerly:' . $form->handle] = array(
				'label'    => $form->name,
				'criteria' => array('formId' => $form->id),
				'key' => $form->id,
			);
		}

		return $sources;
	}

	public function defineTableAttributes($source = null)
	{
		$attributes = array(
			'id' => 'ID',
		);

		if (null === $source) {
			foreach (craft()->formerly_forms->getAllForms() as $form)
			{
				foreach ($form->getQuestions() as $question) {
					if ($question->type != Formerly_QuestionType::MultilineText &&
						$question->type != Formerly_QuestionType::CustomList &&
						$question->type != Formerly_QuestionType::Assets &&
						$question->type != Formerly_QuestionType::Checkboxes
					) {
						$attributes[$question->handle] = $form->name . '-' . $question->name;
					}
				}
			}
		} else {
			$form = craft()->formerly_forms->getFormByHandle(substr($source, 9));
			foreach ($form->getQuestions() as $question) {
				if ($question->type != Formerly_QuestionType::MultilineText &&
					$question->type != Formerly_QuestionType::CustomList &&
					$question->type != Formerly_QuestionType::Assets &&
					$question->type != Formerly_QuestionType::Checkboxes
				) {
					$attributes[$question->handle] = $question->name;
				}

				if (count($attributes) >= 5) {
					break;
				}
			}
		}

		$attributes['dateCreated'] = Craft::t('Date Created');

		return $attributes;
	}

	public function getTableAttributeHtml(BaseElementModel $element, $attribute)
	{
		$value = $element->$attribute;
		if($value instanceof MultiOptionsFieldData)
		{
			$options = $value->getOptions();
			$summary = array();

			for ($j = 0; $j < count($options); ++$j)
				{
					$option = $options[$j];
					if($option->selected) {
						$summary[] = $option->label;
					}
				}
			return implode($summary, ', ');
		}
		else
		{
						return parent::getTableAttributeHtml($element, $attribute);
		}
	}

	public function defineCriteriaAttributes()
	{
		return array(
			'form'   => AttributeType::Mixed,
			'formId' => AttributeType::Mixed,
		);
	}

	public function modifyElementsQuery(DbCommand $query, ElementCriteriaModel $criteria)
	{
		$query
			->addSelect('submissions.formId')
			->join('formerly_submissions submissions', 'submissions.id = elements.id');

		if ($criteria->formId)
		{
			$query->andWhere(DbHelper::parseParam('submissions.formId', $criteria->formId, $query->params));
		}

		if ($criteria->form)
		{
			$query->join('formerly_forms forms', 'forms.id = submissions.formId');
			$query->andWhere(DbHelper::parseParam('formerly_forms.handle', $criteria->form, $query->params));
		}
	}

	public function populateElementModel($row)
	{
		return Formerly_SubmissionModel::populateModel($row);
	}

	/**
	 * @inheritDoc IElementType::getAvailableActions()
	 *
	 * @param string|null $source
	 *
	 * @return array|null
	 */
	public function getAvailableActions($source = null)
	{
		// Now figure out what we can do with these
		$actions = array();

		$deleteAction = craft()->elements->getAction('Delete');
		$deleteAction->setParams(array(
			'confirmationMessage' => Craft::t('Are you sure you want to delete the selected submissions?'),
			'successMessage'      => Craft::t('Submissions deleted.'),
		));
		$actions[] = $deleteAction;

		// Allow plugins to add additional actions
		$allPluginActions = craft()->plugins->call('addEntryActions', array($source), true);

		foreach ($allPluginActions as $pluginActions)
		{
			$actions = array_merge($actions, $pluginActions);
		}

		return $actions;
	}
}
