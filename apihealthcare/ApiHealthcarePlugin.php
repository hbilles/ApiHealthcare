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
		return '0.3.0';
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
        	'apihealthcare'                                           => array('action' => 'apiHealthcare/options/professionsIndex'),
        	'apihealthcare/professions'                               => array('action' => 'apiHealthcare/options/professionsIndex'),
        	'apihealthcare/professions/update'                        => array('action' => 'apiHealthcare/options/updateProfessions'),
        	'apihealthcare/professions/reset'                         => array('action' => 'apiHealthcare/options/resetProfessions'),
        	'apihealthcare/professions/(?P<professionId>\d+)'         => array('action' => 'apiHealthcare/options/editProfession'),
        	'apihealthcare/professions/edit-search'                   => array('action' => 'apiHealthcare/options/editProfessionSearchSettings'),
        	'apihealthcare/specialties'                               => array('action' => 'apiHealthcare/options/specialtiesIndex'),
        	'apihealthcare/specialties/update'                        => array('action' => 'apiHealthcare/options/updateSpecialties'),
        	'apihealthcare/specialties/reset'                         => array('action' => 'apiHealthcare/options/resetSpecialties'),
        	'apihealthcare/specialties/(?P<specialtyId>\d+)'          => array('action' => 'apiHealthcare/options/editSpecialty'),
        	'apihealthcare/specialties/edit-search'                   => array('action' => 'apiHealthcare/options/editSpecialtySearchSettings'),
        	'apihealthcare/per-diem-clients'                          => array('action' => 'apiHealthcare/options/perDiemClientsIndex'),
        	'apihealthcare/per-diem-clients/new'                      => array('action' => 'apiHealthcare/options/editPerDiemClient'),
        	'apihealthcare/per-diem-clients/(?P<perDiemClientId>\d+)' => array('action' => 'apiHealthcare/options/editPerDiemClient')
        );

	}
}
