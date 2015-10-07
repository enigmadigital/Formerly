<?php
namespace Craft;

class Formerly_SubmissionsWidget extends BaseWidget
{

  public function getName()
  {
    return Craft::t('Recent Formerly Submissions');
  }

  public function getTitle()
  {
    if (strlen($this->getSettings()->customtitle) > 0)
      return $this->getSettings()->customtitle;

    $form = craft()->formerly_forms->getFormById($this->getSettings()->form);

    return "Formerly - " . $form->name;
  }

  protected function defineSettings()
  {
    return array(
      'form' => array(AttributeType::Number),
      'limit' => array(AttributeType::Number, 'min' => 0, 'default' => 10),
      'columns' => array(AttributeType::Number, 'min' => 2, 'default' => 4),
      'colspan' => array(AttributeType::Number, 'default' => 2),
      'bars' => array(AttributeType::Number, 'min' => 0, 'default' => 7),
      'barColour' => array(AttributeType::String, 'default' => '#366D80'),
      'textColour' => array(AttributeType::String, 'default' => '#366D80'),
      'customtitle' => array(AttributeType::String),
      'groupby' => array(AttributeType::String, 'default' => 'Days'),
    );
  }
  public function getColspan()
  {
    $settings = $this->getSettings();

    if(isset($settings->colspan))
    {
      if($settings->colspan > 0)
      {
        return $settings->colspan;
      }
    }

    return 1;
  }

  public function getSettingsHtml()
  {

    $myforms = array();

    foreach (craft()->formerly_forms->getAllForms() as $form)
      $myforms[$form->id] = $form->name;

    return craft()->templates->render('formerly/_widgets/settings', array(
      'formerlyforms' => $myforms,
      'settings' => $this->getSettings(),
    ));
  }

  public function getBodyHtml()
  {

    date_default_timezone_set(craft()->getTimeZone());

    $limit = $this->getSettings()->limit;
    $bars =  $this->getSettings()->bars - 1;
    $form = craft()->formerly_forms->getFormById($this->getSettings()->form);

    $variables = array();
    $variables['name'] = $form->name;
    $variables['id'] = $form->id;
    $variables['bars'] = $bars;
    $variables['groupby'] = $this->getSettings()->groupby;

    $timeOffset = '+10:00'; //Australia/Sydney
    if (craft()->config->exists(Formerly_ConfigSettings::SettingsGroupName) &&
      array_key_exists(Formerly_ConfigSettings::TimeZoneOffset, craft()->config->get(Formerly_ConfigSettings::SettingsGroupName))) {
      $timeOffset = craft()->config->get(Formerly_ConfigSettings::SettingsGroupName)[Formerly_ConfigSettings::TimeZoneOffset];
    }

      if ($bars > 0) {
      $variables['barColour'] = $this->getSettings()->barColour;
      $variables['textColour'] = $this->getSettings()->textColour;
      $criteria2 = craft()->elements->getCriteria('Formerly_Submission');
      $criteria2->formId = $this->getSettings()->form;
      $query = craft()->elements->buildElementsQuery($criteria2);

      $maxCount = 1;
      $graphData = array();

      if ($this->getSettings()->groupby == "Days") {
        $startDate = date('Y-m-d', time() + (60 * 60 * 24 * -1 * $bars));
        $selectString = "COUNT(`formId`) as Submisssions, DATE(CONVERT_TZ(elements.datecreated,'+00:00','" . $timeOffset . "')) Day";
        $rows = $query
          ->select($selectString)
          ->group("Day")
          ->where("formId=" . $this->getSettings()->form . " AND elements.dateCreated > '" . $startDate . "'")
          ->order("Day")
          ->queryAll();
        for ($i = $bars; $i >= 0; $i--) {
          $data = new \stdClass();
          $data->count = 0;
          $data->maxHeight = 0;

          $data->date = date('Y-m-d', time() + (60 * 60 * 24 * -1 * $i));
          $data->shortdate = date('d M', time() + (60 * 60 * 24 * -1 * $i));
          foreach ($rows as $grow) {
            if ($grow['Day'] == $data->date) {
              $data->count = $grow['Submisssions'];
              if ($data->count > $maxCount)
                $maxCount = $data->count;
              break;
            }
          }

          $graphData[] = $data;
        }
      }
      elseif ($this->getSettings()->groupby == "Months") {
        $startDate = new DateTime('-' . $bars . ' Months');
        $startDate = $startDate->format('Y-m-01');
        $selectString = "COUNT(`formId`) as Submisssions, MONTH(CONVERT_TZ(elements.datecreated,'+00:00','" . $timeOffset . "')) as Month, YEAR(CONVERT_TZ(elements.datecreated,'+00:00','" . $timeOffset . "')) Year";
        $rows = $query
          ->select($selectString)
          ->group("Year, Month")
          ->where("formId=" . $this->getSettings()->form . " AND elements.dateCreated > '" . $startDate . "'")
          ->order("Year, Month")
          ->queryAll();

        for ($i = $bars; $i >= 0; $i--) {
          $data = new \stdClass();
          $data->count = 0;
          $data->maxHeight = 0;

          $gDate = new DateTime('-' . $i . ' Months');

          $data->date = $gDate->format('Y-m');
          $data->shortdate = $gDate->format('M-y');
          foreach ($rows as $grow) {
            $dM = $grow['Year'] . '-' . str_pad($grow['Month'], 2, "0", STR_PAD_LEFT);

            if ($data->date == $dM) {
              $data->count = $grow['Submisssions'];
              if ($data->count > $maxCount)
                $maxCount = $data->count;
              break;
            }
          }

          $graphData[] = $data;
        }
      }
      elseif ($this->getSettings()->groupby == "Weeks") {
          $startDate = new DateTime('-' . $bars . ' Weeks');
          $startDate = $startDate->format('Y-m-01');
          $selectString = "COUNT(`formId`) as Submisssions, WEEK(CONVERT_TZ(elements.datecreated,'+00:00','" . $timeOffset . "')) as Week, YEAR(CONVERT_TZ(elements.datecreated,'+00:00','" . $timeOffset . "')) Year";
          $rows = $query
            ->select($selectString)
            ->group("Year, Week")
            ->where("formId=" . $this->getSettings()->form . " AND elements.dateCreated > '" . $startDate . "'")
            ->order("Year, Week")
            ->queryAll();

          for ($i = $bars; $i >= 0; $i--) {
            $data = new \stdClass();
            $data->count = 0;
            $data->maxHeight = 0;

            $gDate = new DateTime('-' . $i . ' Weeks');
            $data->date = $gDate->format('YW');
            $data->shortdate = $gDate->format('d-M');
            foreach ($rows as $grow) {
              $dW = date("YW", strtotime($grow['Year'] . 'W' . $grow['Week']));

              if ($data->date == $dW) {
                $data->count = $grow['Submisssions'];
                if ($data->count > $maxCount)
                  $maxCount = $data->count;
                break;
              }
            }

            $graphData[] = $data;
          }
      }

      foreach ($graphData as $gd) {
        $gd->height = round(250 * ($gd->count / $maxCount));
      }

      $variables['graphData'] = $graphData;
    }

    if ($limit > 0) {
      $criteria = craft()->elements->getCriteria('Formerly_Submission');
      $criteria->limit = $this->getSettings()->limit;
      $criteria->formId = $this->getSettings()->form;
      $criteria->order = 'dateCreated desc';

      foreach ($criteria->find() as $submission) {
        $colCount = 0;
        foreach ($form->getQuestions() as $question) {
          if ($colCount == 0) {
            $row['Id'] = $submission->id;
            $row['Time'] = $submission->dateCreated->format('d/m/Y H:i:s');
            $colCount = 2;
          }
          $colCount++;
          if ($colCount <= $this->getSettings()->columns) {
            if ($question->type != 'RawHTML') {
              $columnName = str_replace($form->handle . '_', '', $question->handle);
              $columnName = str_replace(Formerly_QuestionType::CustomListHandle, '', $columnName);
              $columnName = str_replace(Formerly_QuestionType::RawHTMLHandle, '', $columnName);
              $columnName = str_replace(Formerly_QuestionType::CustomHandle, '', $columnName);
              $columnName = ucwords($columnName);

              $row[$columnName] = $submission->{$question->handle};
              $value = $submission->{$question->handle};
              if ($value instanceof MultiOptionsFieldData) {
                $options = $value->getOptions();

                $summary = array();
                if ($question->type == Formerly_QuestionType::CustomList) {
                  for ($j = 0; $j < count($value); ++$j) {
                    $v = $value[$j];
                    if ($v->selected) {
                      $summary[] = $v->value;
                    }
                  }
                } else {
                  for ($j = 0; $j < count($options); ++$j) {
                    $option = $options[$j];
                    if ($option->selected)
                      $summary[] = $option->value;
                  }
                }
                $row[$columnName] = implode($summary, ', ');
              } elseif ($question->type == Formerly_QuestionType::MultilineText) {
                $row[$columnName] = str_replace('<br />', "\n", $value);
              } else {
                if ($question->type != Formerly_QuestionType::RawHTML)
                  $row[$columnName] = $value;
              }
            }
          }
        }
        $variables['rows'][] = $row;
      }
    }

    return craft()->templates->render('formerly/_widgets/submissions', $variables);
  }
}