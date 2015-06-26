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
		$this->onBeforePost(new Event($this, array(
			'submission' => $submission
		)));

		if ($this->saveSubmission($submission))
		{
			$this->sendSubmissionEmails($submission);

			$this->onPost(new Event($this, array(
				'submission' => $submission
			)));

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
            $alreadySubmitted = false;
            $email = '';
            foreach ($_REQUEST['questions'] as $key => $value) {
                if (strpos($key, 'email') > -1)
                    $email = $value;
            }

            $criteria = craft()->elements->getCriteria('Formerly_Submission');
            $criteria-> search = $email;

            foreach($criteria->find() as $submission)
            {
                $alreadySubmitted = true;
            }

            if ($alreadySubmitted)
                return true;
            else {
                $transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
                try {
                    if (craft()->elements->saveElement($submission)) {
                        $submissionRecord->id = $submission->id;

                        $submissionRecord->save(false);

                        if ($transaction !== null) {
                            $transaction->commit();
                        }

                        return true;
                    } else {
                        return false;
                    }
                } catch (\Exception $ex) {
                    if ($transaction !== null) {
                        $transaction->rollback();
                    }
                }
            }
		}

		return false;
	}

	public function deleteSubmissionById($submissionId)
	{
		if (!$submissionId)
		{
			return false;
		}

		$affectedRows = craft()->db->createCommand()->delete('formerly_submissions', array(
			'id' => $submissionId
		));

		return (bool) $affectedRows;
	}

	public function sendSubmissionEmails(Formerly_SubmissionModel $submission)
	{
		if (!$submission)
		{
			return false;
		}

		$form = $submission->getForm();

		if ($form->emails !== null)
		{
			foreach ($form->emails as $emailDef)
			{
				if (empty($emailDef['to'])) continue;

				$email = new EmailModel();
				$email->toEmail = $this->_renderSubmissionTemplate($emailDef['to'], $submission);
				$email->subject = !empty($emailDef['subject']) ? $this->_renderSubmissionTemplate($emailDef['subject'], $submission) : 'Website Enquiry';

				if (!empty($emailDef['from']))
				{
					$from = $this->_renderSubmissionTemplate($emailDef['from'], $submission);

					// https://regex101.com/r/yI0hL1/1
					preg_match('/^(.+)\<(.+)\>$/', $from, $matches);

					if (count($matches) >= 3)
					{
						// The provided from email is in the format Name <email>.
						$email->fromName  = trim($matches[1]);
						$email->fromEmail = trim($matches[2]);
					}
					else
					{
						// Note: If no from email is set, the default is the craft admin email address.
						$email->fromEmail = $from;
					}
				}

				if (!empty($emailDef['body']))
				{
					$email->body     = $this->_renderSubmissionTemplate($emailDef['body'], $submission);
					$email->htmlBody = $email->body;
				}
				else
				{
					$email->body     = $submission->getSummary();
					$email->htmlBody = $email->body;
				}

				if (!empty($email->body))
				{
					craft()->email->sendEmail($email);
				}
			}
		}
	}

	private function _renderSubmissionTemplate($template, Formerly_SubmissionModel $submission)
	{
		$formHandle = $submission->getForm()->handle;

		$formattedTemplate = preg_replace('/(?<![\{\%])\{(?![\{\%])/', '{'.$formHandle.'_', $template);
		$formattedTemplate = preg_replace('/(?<![\}\%])\}(?![\}\%])/', '}', $formattedTemplate);

		return craft()->templates->renderObjectTemplate($formattedTemplate, $submission);
	}

	public function onBeforePost(Event $event)
	{
		$this->raiseEvent('onBeforePost', $event);
	}

	public function onPost(Event $event)
	{
		$this->raiseEvent('onPost', $event);
	}
}
