<?php
namespace Craft;

class ApiHealthcarePlugin extends BasePlugin
{

	function getName()
	{
		return Craft::t('API Healthcare');
	}

	function getVersion()
	{
		return '0.1.1';
	}

	function getDeveloper()
	{
		return 'Hite Billes';
	}

	function getDeveloperUrl()
	{
		return 'http://hitebilles.com';
	}

	protected function defineSettings()
	{
		return array(
			'apiUsername'      => AttributeType::String,
			'apiPassword'      => AttributeType::String,
			'apiClusterPrefix' => AttributeType::String,
			'apiSiteName'      => AttributeType::String,
			'searchBaseUri'    => AttributeType::String,
		);
	}

	public function getSettingsHtml()
	{
		return craft()->templates->render('apihealthcare/_settings', array(
			'settings' => $this->getSettings()
		));
	}

	/*
	function hasCpSection()
    {
        return true;
    }
    */
    
    /*
    public function registerCpRoutes()
    {
        return array(

        );

	}
	*/
}
