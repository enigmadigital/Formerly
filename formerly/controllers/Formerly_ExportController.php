<?php namespace Craft;

class Formerly_ExportController extends BaseController
{

	public function actionIndex()
	{
		$this->renderTemplate('formerly/export/_index', array(
			'forms' => craft()->formerly_forms->getAllForms()
		));
	}


	public function actionCsv()
	{
		$formId = craft()->request->getPost('form');
		$form = craft()->formerly_forms->getFormById($formId);

		$criteria = craft()->elements->getCriteria('Formerly_Submission');
		$criteria->formId = $formId;

		$data = array();

		foreach($criteria->find() as $submission)
		{
			$row = array(
				'Id' => $submission->id,
				'Time' => $submission->dateCreated->format('d/m/Y H:i:s')
			);

			foreach($form->getQuestions() as $question)
			{
				$columnName = str_replace($form->handle . '_', '', $question->handle);
				$columnName = ucwords($columnName);

				$row[$columnName] = $submission->{$question->handle};
			}

			$data[] = $row;
		}

		if(count($data) > 0)
		{
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . ($form->handle . '_submissions.csv'));
			header('Content-Transfer-Encoding: binary');

			$stream = fopen('php://output', 'w');

			// Write column names first.
			fputcsv($stream, array_keys($data[0]));

			foreach ($data as $row)
			{
				fputcsv($stream, $row);
			}

			fclose($stream);
		}
		else
		{
			header('Content-type: text/plain');
			echo 'There are no submissions.';
		}
	}

}