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
        $criteria->limit = -1;
        set_time_limit('600');

        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . ($form->handle . '_submissions.csv'));
        header('Content-Transfer-Encoding: binary');

        $stream = fopen('php://output', 'w');

        // Write column names first.

        $first = true;

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
                $value = $submission->{$question->handle};
                if($value instanceof MultiOptionsFieldData)
                {
                    $options = $value->getOptions();

                    $summary = array();
                    for ($j = 0; $j < count($options); ++$j)
                    {
                        $option = $options[$j];
                        if($option->selected)
                            $summary[] = $option->label;
                    }
					$row[$columnName] = implode($summary, ', ');
				}
				else
				{
   					$row[$columnName] = $value;
   				}
			}

            if ($first) {
                fputcsv($stream, array_keys($row));
                $first = false;
            }

            fputcsv($stream, $row);

        }

        fclose($stream);
	}

}