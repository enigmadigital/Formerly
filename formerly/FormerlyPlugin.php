<?php
namespace Craft;

class FormerlyPlugin extends BasePlugin
{
	public function getName()
	{
	    return 'Formerly';
	}

	public function getVersion()
	{
	    return '1.2.0';
	}

	public function getDeveloper()
	{
	    return 'XO Digital';
	}

	public function getDeveloperUrl()
	{
	    return 'http://www.xodigital.com.au';
	}

	public function hasCpSection()
	{
		return true;
	}

	public function registerCpRoutes()
	{
		return array(
			'formerly/forms'                                          => array('action' => 'formerly/forms/index'),
			'formerly/forms/new'                                      => array('action' => 'formerly/forms/editForm'),
			'formerly/forms/(?P<formId>\d+)'                          => array('action' => 'formerly/forms/editForm'),
			'formerly'                                                => array('action' => 'formerly/submissions/index'),
			'formerly/(?P<formHandle>{handle})/(?P<submissionId>\d+)' => array('action' => 'formerly/submissions/viewSubmission'),
			'formerly/export'                                         => array('action' => 'formerly/export/index'),
			'formerly/export/csv'                                     => array('action' => 'formerly/export/csv')
		);
	}
}
