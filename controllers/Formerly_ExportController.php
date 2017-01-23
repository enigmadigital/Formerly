<?php namespace Craft;

class Formerly_ExportController extends BaseController
{

    /**
     * Switch this to true if you require the onPopulateElement hook.
     *
     * This significantly increases memory and CPU consumption.
     *
     * @var bool
     */
    private $enableHooks = false;

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
        $formQuestions = $form->getQuestions();
        $fieldTypes = craft()->fields->getAllFieldTypes();

        set_time_limit('1000');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . ($form->handle . '_submissions.csv'));
        header('Content-Transfer-Encoding: binary');
        $stream = fopen('php://output', 'w');


        $criteria = craft()->elements->getCriteria('Formerly_Submission');
        $criteria->formId = $formId;

        $query = craft()->elements->buildElementsQuery($criteria, $contentTable, $fieldColumns);
        $query->limit(500000);
        $query->order('dateCreated desc');

        if (isset($_POST['fromDate']) &&
            !empty($_POST['fromDate']['date']) &&
            isset($_POST['toDate']) &&
            !empty($_POST['toDate']['date'])
        ) {

            $fromDate = craft()->request->getPost('fromDate');
            $fromDate = DateTime::createFromString($fromDate, craft()->timezone);
            $fromDate->setTime(0, 0, 0);
            $toDate = craft()->request->getPost('toDate');
            $toDate = DateTime::createFromString($toDate, craft()->timezone);
            $toDate->setTime(23, 59, 59);

            $query->andWhere(DbHelper::parseDateParam('elements.dateCreated', '>= ' . $fromDate->format(DateTime::MYSQL_DATETIME), $query->params));
            $query->andWhere(DbHelper::parseDateParam('elements.dateCreated', '<= ' . $toDate->format(DateTime::MYSQL_DATETIME), $query->params));
        } elseif (isset($_POST['fromDate']) && !empty($_POST['fromDate']['date'])) {
            $fromDate = craft()->request->getPost('fromDate');
            $fromDate = DateTime::createFromString($fromDate, craft()->timezone);
            $fromDate->setTime(0, 0, 0);
            $query->andWhere(DbHelper::parseDateParam('elements.dateCreated', '>= ' . $fromDate->format(DateTime::MYSQL_DATETIME), $query->params));
        } else if (isset($_POST['toDate']) && !empty($_POST['toDate']['date'])) {
            $toDate = craft()->request->getPost('toDate');
            $toDate = DateTime::createFromString($toDate, craft()->timezone);
            $toDate->setTime(23, 59, 59);
            $query->andWhere(DbHelper::parseDateParam('elements.dateCreated', '<= ' . $toDate->format(DateTime::MYSQL_DATETIME), $query->params));
        }

        // Write column names first.
        $first = true;
        $queryResult = $query->query();
        $elementType = $criteria->getElementType();
        while (false !== ($result = $queryResult->read())) {
            if ($this->enableHooks) {
                // Make a copy to pass to the onPopulateElement event
                $originalResult = array_merge($result);
            }

            // Separate the content values from the main element attributes
            $content = array(
                'id' => (isset($result['contentId']) ? $result['contentId'] : null),
                'elementId' => $result['id'],
                'locale' => $criteria->locale,
                'title' => (isset($result['title']) ? $result['title'] : null)
            );

            unset($result['title']);

            if ($fieldColumns) {
                foreach ($fieldColumns as $column) {
                    // Account for results where multiple fields have the same handle, but from
                    // different columns e.g. two Matrix block types that each have a field with the
                    // same handle

                    $colName = $column['column'];
                    $fieldHandle = $column['handle'];

                    if (!isset($content[$fieldHandle]) || (empty($content[$fieldHandle]) && !empty($result[$colName]))) {
                        $content[$fieldHandle] = $result[$colName];
                    }

                    unset($result[$colName]);
                }
            }

            $result['locale'] = $criteria->locale;

            if ($this->enableHooks) {
                $submission = $elementType->populateElementModel($result);

                // Was an element returned?
                if (!$submission || !($submission instanceof BaseElementModel)) {
                    continue;
                }

                $submission->setContent($content);

                // Fire an 'onPopulateElement' event
                craft()->elements->onPopulateElement(new Event($this, array(
                    'element' => $submission,
                    'result' => $originalResult
                )));
            } else {
                $result['dateCreated'] = DateTime::createFromFormat(DateTime::MYSQL_DATETIME, $result['dateCreated']);
                $submission = (object)array_merge($content, $result);
            }

            $row = array(
                'Id' => $submission->id,
                'Time' => $submission->dateCreated->format('d/m/Y H:i:s')
            );

            foreach ($formQuestions as $question) {
                if ($question->type == Formerly_QuestionType::RawHTML) {
                    continue;
                }

                $columnName = str_replace(array(
                    $form->handle . '_',
                    Formerly_QuestionType::CustomListHandle,
                    Formerly_QuestionType::RawHTMLHandle,
                    Formerly_QuestionType::CustomHandle,
                ), '', $question->handle);
                $columnName = ucwords($columnName);

                $value = $submission->{$question->handle};
                if (!$this->enableHooks && isset($fieldTypes[$question->type])) {
                    $fieldType = clone $fieldTypes[$question->type];
                    $fieldType->setSettings(array(
                        'options' => $question->options,
                    ));

                    if ($value && is_string($value) && mb_strpos('{[', $value[0]) !== false) {
                        // Presumably this is JSON.
                        $value = JsonHelper::decode($value);
                    }

                    $value = $fieldType->prepValue($value);
                }

                if ($value instanceof MultiOptionsFieldData) {
                    $summary = array();
                    if ($question->type == Formerly_QuestionType::CustomList) {
                        for ($j = 0; $j < count($value); ++$j) {
                            $v = $value[$j];
                            if ($v->selected) {
                                $summary[] = $v->value;
                            }
                        }
                    } else {
                        foreach ($value->getOptions() as $option) {
                            if ($option->selected) {
                                $summary[] = $option->value;
                            }
                        }
                    }
                    $row[$columnName] = implode($summary, ', ');
                } elseif ($question->type == Formerly_QuestionType::MultilineText) {
                    $row[$columnName] = str_replace('<br />', "\n", $value);
                } else {
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
