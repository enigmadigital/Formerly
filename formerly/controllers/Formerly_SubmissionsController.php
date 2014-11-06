<?php
namespace Craft;

class Formerly_SubmissionsController extends BaseController
{
	protected $allowAnonymous = array('actionPostSubmission');

	public function actionIndex()
	{
		$variables['forms'] = craft()->formerly_forms->getAllForms();

		$this->renderTemplate('formerly/submissions/_index', $variables);
	}

	public function actionViewSubmission(array $variables = array())
	{

		if (!empty($variables['submissionId']))
		{
			if (empty($variables['submission']))
			{
				$variables['submission'] = craft()->formerly_submissions->getSubmissionById($variables['submissionId']);

				if (!$variables['submission'])
				{
					throw new HttpException(404);
				}

				$form = $variables['submission']->getForm();

				$variables['crumbs'] = array(
					array('label' => Craft::t('Formerly'), 'url' => UrlHelper::getUrl('formerly')),
					array('label' => $form->name, 'url' => UrlHelper::getUrl('formerly')),
				);
			}
		}
		else
		{
			throw new HttpException(404);
		}

		$this->renderTemplate('formerly/submissions/_view', $variables);
	}

	public function actionPostSubmission()
	{
		$this->requirePostRequest();

		$submission = new Formerly_SubmissionModel();

		$submission->formId = craft()->request->getRequiredPost('formId');
		$submission->setContentFromPost('questions');

		if (craft()->formerly_submissions->postSubmission($submission))
		{
			$this->redirectToPostedUrl($submission);
		}
		else
		{
			craft()->urlManager->setRouteVariables(array(
				'submission' => $submission
			));
		}
	}
}
