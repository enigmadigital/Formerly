<?php
/**
 * Created by PhpStorm.
 * User: User
 * Date: 10/30/2014
 * Time: 10:13 AM
 */

namespace Craft;


class FormerlyVariable
{
	public function form($handle)
	{
		return craft()->formerly_forms->getFormByHandle($handle);
	}

	public function submissions($handle = null)
	{
		$criteria = craft()->elements->getCriteria('Formerly_Submission');

		if (null !== $handle) {
			$criteria->formId = $this->form($handle)->id;
		}

		return $criteria;
	}
}
