<?php
namespace Craft;

class ApiHealthcare_JobListingFieldType extends BaseFieldType
{
	public function getName()
	{
		return Craft::t('API Healthcare Job Listing');
	}

	public function defineContentAttribute()
	{
		return AttributeType::Mixed;
	}

	public function getInputHtml($name, $value)
	{
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