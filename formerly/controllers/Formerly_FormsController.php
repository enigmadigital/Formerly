<?php
namespace Craft;

include_once(dirname(__FILE__) . '/../helpers/FormerlyHelpers.php');

class Formerly_FormsController extends BaseController
{
	public function actionIndex()
	{
		$variables['forms'] = craft()->formerly_forms->getAllForms();

		$this->renderTemplate('formerly/forms/_index', $variables);
	}

	public function actionEditForm(array $variables = array())
	{
		if (!empty($variables['formId']))
		{
			if (empty($variables['form']))
			{
				$variables['form']      = craft()->formerly_forms->getFormById($variables['formId']);
				$variables['questions'] = craft()->formerly_forms->getQuestionsByFormId($variables['formId'], 'id');

				if (!$variables['form'])
				{
					throw new HttpException(404);
				}
			}

			$variables['title'] = $variables['form']->name;
		}
		else
		{
			if (empty($variables['form']))
			{
				$variables['form']      = new Formerly_FormModel();
				$variables['questions'] = array();
			}

			$variables['title'] = Craft::t('New form');
		}

		$variables['crumbs'] = array(
			array('label' => Craft::t('Formerly'), 'url' => UrlHelper::getUrl('formerly')),
		);

		$this->renderTemplate('formerly/forms/_edit', $variables);
	}

	public function actionSaveForm()
	{
		$this->requirePostRequest();

		$formId = craft()->request->getPost('formId');

		$oldForm   = craft()->formerly_forms->getFormById($formId);
		$form      = craft()->formerly_forms->getFormById($formId);
		$questions = array();

		if (!$form)
		{
			$form = new Formerly_FormModel();
		}

		$form->name        = craft()->request->getPost('name');
		$form->handle      = craft()->request->getPost('handle');
		$form->toAddress   = craft()->request->getPost('toAddress');
		$form->fromAddress = craft()->request->getPost('fromAddress');
		$form->subject     = craft()->request->getPost('subject');

		$postedQuestions = craft()->request->getPost('questions');
		$sortOrder = 0;

		foreach ($postedQuestions as $questionId => $postedQuestion)
		{
			$question = craft()->formerly_forms->getQuestionById($questionId);

			if (!$question)
			{
				$question = new Formerly_QuestionModel();
			}

			$question->name      = $postedQuestion['name'];
			$question->handle    = FormerlyHelpers::generateHandle($question->name);
			$question->required  = (bool) $postedQuestion['required'];
			$question->type      = $postedQuestion['type'];
			$question->sortOrder = ++$sortOrder;

			if (isset($postedQuestion['options']))
			{
				$options = array();

				foreach ($postedQuestion['options'] as $postedOption)
				{
					$options[] = array(
						'label'   => $postedOption['label'],
						'value'   => $postedOption['label'],
						'default' => (bool) $postedOption['default'],
					);
				}

				$question->options = $options;
			}

			$questions[$questionId] = $question;
		}

		$ok = true;

		if (craft()->formerly_forms->saveForm($form))
		{
			$existingQuestions = craft()->formerly_forms->getQuestionsByFormId($form->id, 'id');
			$questionsToDelete = array_diff_key($existingQuestions, $questions);

			foreach ($questionsToDelete as $question)
			{
				craft()->formerly_forms->deleteQuestion($question);
			}

			foreach ($questions as $question)
			{
				$question->formId    = $form->id;

				if (!craft()->formerly_forms->saveQuestion($question))
				{
					$ok = false;

					break;
				}
			}
		}
		else
		{
			$ok = false;
		}

		craft()->urlManager->setRouteVariables(array(
			'form'      => $form,
			'questions' => $questions,
		));

		if ($ok)
		{
			craft()->userSession->setNotice(Craft::t('Form saved.'));
			$this->redirectToPostedUrl($form);
		}
		else
		{
			craft()->userSession->setError(Craft::t('Couldn’t save form.'));
		}
	}

	public function actionDeleteForm()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$formId = craft()->request->getRequiredPost('id');

		craft()->formerly_forms->deleteFormById($formId);

		$this->returnJson(array('success' => true));
	}
}
