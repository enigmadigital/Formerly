<?php namespace Craft;

class Formerly_ExportController extends BaseController
{

	public function actionIndex()
	{
		$this->renderTemplate('formerly/export/_index', array());
	}

}