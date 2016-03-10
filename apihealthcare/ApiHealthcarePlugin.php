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
		return '1.0.0';
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

	function hasCpSection()
    {
        return true;
    }
    
    public function registerCpRoutes()
    {
        return array(
        	'apihealthcare'                                           => array('action' => 'apiHealthcare/professions/index'),
        	'apihealthcare/professions'                               => array('action' => 'apiHealthcare/professions/index'),
        	'apihealthcare/professions/update'                        => array('action' => 'apiHealthcare/professions/update'),
        	'apihealthcare/professions/reset'                         => array('action' => 'apiHealthcare/professions/reset'),
        	'apihealthcare/professions/(?P<professionId>\d+)'         => array('action' => 'apiHealthcare/professions/edit'),
        	'apihealthcare/professions/edit-search'                   => array('action' => 'apiHealthcare/professions/editSearchSettings'),
        	'apihealthcare/specialties'                               => array('action' => 'apiHealthcare/specialties/index'),
        	'apihealthcare/specialties/update'                        => array('action' => 'apiHealthcare/specialties/update'),
        	'apihealthcare/specialties/reset'                         => array('action' => 'apiHealthcare/specialties/reset'),
        	'apihealthcare/specialties/(?P<specialtyId>\d+)'          => array('action' => 'apiHealthcare/specialties/edit'),
        	'apihealthcare/specialties/edit-search'                   => array('action' => 'apiHealthcare/specialties/editSearchSettings'),
        	'apihealthcare/per-diem-clients'                          => array('action' => 'apiHealthcare/perDiemClients/index'),
        	'apihealthcare/per-diem-clients/new'                      => array('action' => 'apiHealthcare/perDiemClients/edit'),
        	'apihealthcare/per-diem-clients/(?P<perDiemClientId>\d+)' => array('action' => 'apiHealthcare/perDiemClients/edit'),
        	'apihealthcare/locations'                                 => array('action' => 'apiHealthcare/locations/index'),
        	'apihealthcare/locations/edit-search'                     => array('action' => 'apiHealthcare/locations/editSearchSettings'),
        	'apihealthcare/locations/populate-states'                 => array('action' => 'apiHealthcare/locations/populateStates')
        );

	}
}
