<?php
namespace Craft;

class Formerly_SubmissionsService extends BaseApplicationComponent
{
	public function getSubmissionById($submissionId)
	{
		return craft()->elements->getElementById($submissionId, 'Formerly_Submission');
	}

	public function postSubmission(Formerly_SubmissionModel $submission)
	{
		if ($this->saveSubmission($submission))
		{
			$this->sendSubmissionEmail($submission);
			return true;
		}

		return false;
	}

	public function saveSubmission(Formerly_SubmissionModel $submission)
	{
		$submissionRecord = new Formerly_SubmissionRecord();

		$submissionRecord->formId = $submission->formId;

		$submissionRecord->validate();
		$submission->addErrors($submissionRecord->getErrors());

		if (!$submission->hasErrors())
		{
			$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
			try
			{
				if (craft()->elements->saveElement($submission))
				{
					$submissionRecord->id = $submission->id;

					$submissionRecord->save(false);

					if ($transaction !== null)
					{
						$transaction->commit();
					}

					return true;
				}
				else
				{
					return false;
				}
			}
			catch (\Exception $ex)
			{
				if ($transaction !== null)
				{
					$transaction->rollback();
				}
			}
		}

		return false;
	}

	public function sendSubmissionEmail(Formerly_SubmissionModel $submission)
	{
		if (!$submission)
		{
			return false;
		}

		$form = $submission->getForm();

		if (empty($form->toAddress))
		{
			return false;
		}

		$email = new EmailModel();
		$email->toEmail   = $form->toAddress;
		$email->subject   = !empty($form->subject) ? $form->subject : 'Website Enquiry';
		$email->fromEmail = $form->fromAddress;

		if (empty($form->fromAddress))
		{
			$criteria = craft()->elements->getCriteria(ElementType::User);
			$criteria->admin = true;

			$admin = $criteria->first();

			$email->fromEmail = $admin->email;
		}

		$body = '';

		foreach ($form->getQuestions() as $question)
		{
			$body .= $question->name."\n: ".$submission[$question->handle]."\n\n";
		}

		$email->body = $body;

		craft()->email->sendEmail($email);
	}
}
