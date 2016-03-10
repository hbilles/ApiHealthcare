<?php
namespace Craft;

class ApiHealthcare_JobListingFieldType extends BaseFieldType
{
	public function getName()
	{
		return Craft::t('API Healthcare Job Listing');
	}

	public function getInputHtml($name, $value)
	{
		if (!empty($value))
		{
			$value = json_decode($value, true);
		}

		$options = array();
	
		$options['professions'] = craft()->apiHealthcare_professions->getWhitelistedMenuItems();
		$options['specialties'] = craft()->apiHealthcare_specialties->getWhitelistedMenuItems();
		$options['locations']   = craft()->apiHealthcare_locations->getWhitelistedMenuItems();

		craft()->templates->includeCssResource('apihealthcare/css/fieldtype.css');

		return craft()->templates->render('apihealthcare/jobListingFieldType/input', array(
			'handle'      => $name,
			'value'       => $value,
			'options'     => $options
		));
	}
}