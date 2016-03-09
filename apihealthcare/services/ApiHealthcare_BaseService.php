<?php
namespace Craft;

class ApiHealthcare_BaseService extends BaseApplicationComponent
{
	/**
	 * @param string $action
	 * @param array $params
	 * @return string
	 * @throws Exception
	 */
	protected function _sendRequest($action, $params = null) {
		if (!$action)
		{
			return false;
		}

		require_once(CRAFT_PLUGINS_PATH.'apihealthcare/vendor/ApiHealthcare/ClearConnectLibModified.php');

		$settings = craft()->plugins->getPlugin('apiHealthcare')->getSettings();
		if (!$settings->apiUsername || !$settings->apiPassword || !$settings->apiClusterPrefix || !$settings->apiSiteName)
		{
			throw new Exception("All settings must be set on the plugin's settings page");
		}

		$ccBaseUrl  = 'https://' . $settings->apiClusterPrefix . '.apihealthcare.com/' . $settings->apiSiteName;
		$username   = $settings->apiUsername;
		$password   = $settings->apiPassword;
		$resultType = 'json';

		$clearConnect = new \ClearConnectLib($ccBaseUrl, $username, $password, $resultType);

		if ($params)
		{
			$clearConnect->setParameters($params);
		}

		$clearConnect->getSessionKey();
		try
		{
			$results = $clearConnect->sendRequest($action);
		}
		catch (\Exception $e)
		{
			return $e;
		}

		return $results;
	}
}