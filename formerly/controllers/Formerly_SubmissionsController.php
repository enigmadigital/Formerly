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

	public function actionViewUpload(array $variables = array())
	{
		if (!empty($variables['fileId']))
		{
			$fileId = $variables['fileId'];

			$asset = craft()->assets->getFileById($fileId);

			$data = file_get_contents( $asset->url );
			header("Content-type: " . $asset->mimeType );
			header("Content-disposition: attachment;filename=" . $asset->filename);

			echo $data;
		}

		return;
	}

	public function actionPostSubmission()
	{


		$this->requirePostRequest();

		$submission = new Formerly_SubmissionModel();

		$submission->formId = craft()->request->getRequiredPost('formId');

		//check file upload for errors, craft will not be happy otherwise
		$errors = false;

		if (sizeof($_FILES) > 0 && array_key_exists('questions', $_FILES) && array_key_exists('error', $_FILES['questions'])) {

			foreach ($_FILES['questions']['error'] as $key => $error) {

				switch ($error) {
					case UPLOAD_ERR_OK:
						break;
					case UPLOAD_ERR_NO_FILE:
						break;
					case UPLOAD_ERR_INI_SIZE:
					case UPLOAD_ERR_FORM_SIZE:
						$submission->addError($key, 'File size exceeded size limit.');
						$errors = true;
						//clear key so setContentFromPost works
						unset($_FILES['questions']['name'][$key]);
						unset($_FILES['questions']['error'][$key]);
						unset($_FILES['questions']['type'][$key]);
						unset($_FILES['questions']['size'][$key]);
						unset($_FILES['questions']['tmp_name'][$key]);
						break;
					default:
						$submission->addError($key, 'File upload failed.');
						//clear key so setContentFromPost works
						unset($_FILES['questions']['name'][$key]);
						unset($_FILES['questions']['error'][$key]);
						unset($_FILES['questions']['type'][$key]);
						unset($_FILES['questions']['size'][$key]);
						unset($_FILES['questions']['tmp_name'][$key]);
						$errors = true;
				}
			}


			if ($errors) {
				$submission->setContentFromPost('questions');
				craft()->urlManager->setRouteVariables(array(
					'submission' => $submission
				));

				return;
			}
		}

		$submission->setContentFromPost('questions');

		if (craft()->formerly_submissions->postSubmission($submission))
		{
			if (craft()->request->isAjaxRequest())
				$this->returnJson(array('ok' => 'yes'));
			else
				$this->redirectToPostedUrl($submission);
		}
		else
		{
			if (craft()->request->isAjaxRequest())
				$this->returnJson(array('ok' => 'no', 'errors' => $submission->getErrors()));
			else
				craft()->urlManager->setRouteVariables(array(
					'submission' => $submission
				));
		}
	}

	public function actionDeleteSubmission()
	{
		$this->requireAjaxRequest();

		$submissionId = craft()->request->getRequiredPost('submissionId');
		$ok = craft()->formerly_submissions->deleteSubmissionById($submissionId);

		$this->returnJson(array('ok' => $ok));
	}

	public function actionAlreadySubmitted()
	{
		$this->requireAjaxRequest();

		$email = craft()->request->getRequiredPost('email');
		$formId = craft()->request->getRequiredPost('formid');
		$ok = craft()->formerly_submissions->alreadySubmitted($email, $formId);

		$this->returnJson(array('ok' => 'true', 'alreadysubmitted' => $ok, 'email'=> $email, 'formid' => $formId));
	}
}
