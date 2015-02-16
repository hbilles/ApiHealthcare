<?php
namespace Craft;

class ApiHealthcare_QueriesService extends ApiHealthcare_BaseService
{
	/**
	 * @param ApiHealthcare_QueryModel $query
	 * @param string $queryUri
	 * @return array
	 */
	private function _prepSearchParams(ApiHealthcare_QueryModel $query, $queryUri = null)
	{
		$params = array();
		// request params get sent to API
		$params['request'] = array();
		// filter params further filter API response
		$params['filter']  = array();

		$params['request']['shiftStart']    = (isset($query->shiftStart)) ? $query->shiftStart : null;
		$params['request']['status']        = (isset($query->status))     ? $query->status : null;
		$params['request']['orderBy1']      = 'shiftStartTime';
		$params['request']['orderBy2']      = 'state';
		$params['request']['orderBy3']      = 'city';

		if ($queryUri)
		{
			$queryUriArray = explode('/', $queryUri);

			// NOTE: the first spot in $queryUriArray is empty, so start from index '1'
			$params['filter']['orderType']      = ($queryUriArray[1] !== 'all') ? urldecode($queryUriArray[1]) : null;
			$params['request']['certification'] = ($queryUriArray[2] !== 'all') ? craft()->apiHealthcare_options->getProfessionNameBySlug($queryUriArray[2]) : null;
			$params['request']['specialty']     = ($queryUriArray[3] !== 'all') ? craft()->apiHealthcare_options->getSpecialtyNameBySlug($queryUriArray[3]) : null;
			$params['request']['clientStateIn'] = ($queryUriArray[4] !== 'all') ? urldecode($queryUriArray[4]) : null;
			$params['request']['clientCityIn']  = ($queryUriArray[5] !== 'all') ? urldecode($queryUriArray[5]) : null;
			$params['filter']['zipCode']        = ($queryUriArray[6] !== 'all') ? urldecode($queryUriArray[6]) : null;

			if (!$params['filter']['orderType'] && !$params['filter']['zipCode'])
			{
				$params['filter'] = null;
			}
		}

		return $params;
	}

	/**
	 * @param string $jsonString
	 * @param array $params
	 * @return array containing multiple ApiHealthcare_QueryModels
	 */
	private function _prepResults($jsonString, $params = null)
	{
		if (!$jsonString)
		{
			return false;
		}

		$results = array();
		$json = stripslashes($jsonString);
		$array = JsonHelper::decode($json);

		if (is_array($array))
		{
			foreach ($array as $item)
			{
				$match = true;

				// if filter params exist, test against filters
				if (is_array($params) && !empty($params))
				{
					foreach($params as $key => $value)
					{
						$match = ($item[$key] === $value) ? true : false;
					}
				}

				if ($match)
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
			}

			return $results;
		}

		return false;
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

		$query->orderType  = 'all';
		if (!$query->professionSlug) { $query->professionSlug = 'all'; }
		if (!$query->specialtySlug)  { $query->specialtySlug = 'all'; }
		if (!$query->state)          { $query->state = 'all'; }
		if (!$query->city)           { $query->city = 'all'; }
		if (!$query->zipCode)        { $query->zipCode = 'all'; }

		$searchUri = '/'
			. $query->orderType . '/'
			. $query->professionSlug . '/'
			. $query->specialtySlug . '/'
			. $query->state . '/'
			. $query->city . '/'
			. $query->zipCode;

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
			$jsonString = $this->_sendRequest('getOrders', $params['request']);
			$results = $this->_prepResults($jsonString);
		}
		else
		{
			$parts = explode($query->getSearchBaseUri(), $requestUri);

			if (isset($parts[1]) && strlen($parts[1]) > 1)
			{
				$queryUri = $parts[1];
				$params = $this->_prepSearchParams($query, $queryUri);
				$jsonString = $this->_sendRequest('getOrders', $params['request']);
				$results = $this->_prepResults($jsonString, $params['filter']);
			}
		}
		
		return $results;
	}

}