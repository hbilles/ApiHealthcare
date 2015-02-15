<?php
namespace Craft;

class ApiHealthcareService extends BaseApplicationComponent
{
	/**
	 * @param string $action
	 * @param array $params
	 * @return string
	 * @throws Exception
	 */
	private function _sendRequest($action, $params = null) {
		if (!$action)
		{
			return false;
		}

		require_once(CRAFT_PLUGINS_PATH.'apihealthcare/vendor/ApiHealthcare/ClearConnectLib.php');

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

	/**
	 * @param string $action
	 * @param string $key
	 * @return array
	 */
	private function _getOptions($action, $key)
	{
		$response = $this->_sendRequest($action);

		if ($response)
		{
			$responseArray = json_decode($response);

			if ($responseArray)
			{
				$options = array();
				foreach ($responseArray as $responseItem)
				{
					$options[] = $responseItem->$key;
				}

				return $options;
			}

			return false;
		}

		return false;
	}

	/**
	 * @param ApiHealthcare_QueryModel $query
	 * @param string $queryUri
	 * @return array
	 */
	private function _prepSearchParams(ApiHealthcare_QueryModel $query, $queryUri = null)
	{
		$params = array();

		//$params['orderType']     = (isset($query->orderType))  ? urldecode($query->orderType) : null;
		$params['certification'] = (isset($query->profession)) ? urldecode($query->profession) : null;
		$params['specialty']     = (isset($query->specialty))  ? urldecode($query->specialty) : null;
		$params['clientStateIn'] = (isset($query->state))      ? urldecode($query->state) : null;
		$params['clientCityIn']  = (isset($query->city))       ? urldecode($query->city) : null;
		//$params['zipCode']       = (isset($query->zipCode))    ? urldecode($query->zipCode) : null;
		$params['shiftStart']    = (isset($query->shiftStart)) ? $query->shiftStart : null;
		$params['status']        = (isset($query->status))     ? $query->status : null;
		$params['orderBy1']      = 'shiftStartTime';
		$params['orderBy2']      = 'state';
		$params['orderBy3']      = 'city';

		if ($queryUri)
		{
			$queryUriArray = explode('/', $queryUri);

			// NOTE: the first spot in $queryUriArray is empty, so start from index '1'
			//$params['orderType']     = ($queryUriArray[1] !== 'all') ? urldecode($queryUriArray[1]) : null;
			$params['certification'] = ($queryUriArray[2] !== 'all') ? urldecode($queryUriArray[2]) : null;
			$params['specialty']     = ($queryUriArray[3] !== 'all') ? urldecode($queryUriArray[3]) : null;
			$params['clientStateIn'] = ($queryUriArray[4] !== 'all') ? urldecode($queryUriArray[4]) : null;
			$params['clientCityIn']  = ($queryUriArray[5] !== 'all') ? urldecode($queryUriArray[5]) : null;
			//$params['zipCode']       = ($queryUriArray[6] !== 'all') ? $queryUriArray[6] : null;
		}

		return $params;
	}

	/**
	 * @param string $jsonString
	 * @return array containing multiple ApiHealthcare_QueryModels
	 */
	private function _prepResults($jsonString)
	{
		if (!$jsonString)
		{
			return false;
		}

		$results = array();
		$json = stripslashes($jsonString);
		$array = json_decode($json, true);

		if (is_array($array))
		{
			foreach ($array as $item)
			{
				$result = new ApiHealthcare_QueryModel();

				$result->orderType   = $item['orderType'];
				$result->profession  = $item['orderCertification'];
				$result->specialty   = $item['orderSpecialty'];
				$result->state       = $item['state'];
				$result->city        = $item['city'];
				$result->zipCode     = $item['zipCode'];
				$result->shiftStart  = $item['shiftStartTime'];
				$result->status      = $item['status'];
				$result->jobId       = $item['orderId'];
				$result->clientName  = $item['clientName'];
				$result->description = $item['note'];

				$results[] = $result;
			}

			return $results;
		}

		return false;
	}

	/**
	 * @return array
	 */
	public function getProfessions()
	{
		return $this->_getOptions('getCerts', 'certName');
	}

	/**
	 * @return array
	 */
	public function getSpecialties()
	{
		return $this->_getOptions('getSpecs', 'specName');
	}

	/**
	 * @param ApiHealthcare_QueryModel $query
	 * @return string
	 */
	public function getSearchUrl(ApiHealthcare_QueryModel $query)
	{
		if (!$query)
		{
			return false;
		}

		$searchQuery = new ApiHealthcare_QueryModel();

		//$searchQuery->orderType   = (isset($query->orderType))   ? $query->orderType   : 'all';
		$searchQuery->orderType  = 'all';
		$searchQuery->profession = ($query->profession) ? $query->profession : 'all';
		$searchQuery->specialty  = ($query->specialty)  ? $query->specialty  : 'all';
		$searchQuery->state      = ($query->state)      ? $query->state      : 'all';
		$searchQuery->city       = ($query->city)       ? $query->city       : 'all';
		$searchQuery->zipCode    = ($query->zipCode)    ? $query->zipCode    : 'all';

		$searchUri = '/'
			. $searchQuery->orderType . '/'
			. $searchQuery->profession . '/'
			. $searchQuery->specialty . '/'
			. $searchQuery->state . '/'
			. $searchQuery->city . '/'
			. $searchQuery->zipCode;

		$searchUri = ($searchUri === '/all/all/all/all/all/all') ? '?show-all=true' : $searchUri;

		return craft()->siteUrl . $query->getSearchBaseUri() . $searchUri;
	}

	/**
	 * @return array
	 */
	public function getSearchResultsFromUrl()
	{
		$query = new ApiHealthcare_QueryModel();
		$query->shiftStart = date('Y-m-d');
		$query->status     = 'open';

		$queryString = craft()->request->queryStringWithoutPath;
		$requestUri  = craft()->request->requestUri;

		$params = array();
		$results = null;


		if ($queryString === 'show-all=true')
		{
			$params = $this->_prepSearchParams($query);
			$jsonString = $this->_sendRequest('getOrders', $params);
			$results = $this->_prepResults($jsonString);
		}
		else
		{
			$parts = explode($query->getSearchBaseUri(), $requestUri);

			if (isset($parts[1]) && strlen($parts[1]) > 1)
			{
				$queryUri = $parts[1];
				$params = $this->_prepSearchParams($query, $queryUri);
				$jsonString = $this->_sendRequest('getOrders', $params);
				$results = $this->_prepResults($jsonString);
			}
		}
		
		return $results;
	}

}