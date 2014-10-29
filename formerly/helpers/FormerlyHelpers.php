<?php
namespace Craft;

class FormerlyHelpers {
	public static function generateHandle($sourceVal)
	{
		// Remove HTML tags
		$handle = preg_replace('/<(.*?)>/', '', $sourceVal);

		// Remove inner-word punctuation
		$handle = preg_replace('/[\'"‘’“”\[\]\(\)\{\}:]/', '', $handle);

		// Make it lowercase
		$handle = strtolower($handle);

		// Convert extended ASCII characters to basic ASCII
		$handle = StringHelper::asciiString($handle);

		// Handle must start with a letter
		$handle = preg_replace('/^[^a-z]+/', '', $handle);

		// Get the "words"
		$words = array_filter(preg_split('/[^a-z0-9]+/', $handle));
		$handle = '';

		// Make it camelCase
		for ($i = 0; $i < count($words); $i++)
		{
			if ($i == 0)
			{
				$handle .= $words[$i];
			}
			else
			{
				$handle .= strtoupper($words[$i][0]) . substr($words[$i], 1);
			}
		}

		return $handle;
	}
}
