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
		// filter params further filter API response to see if a returned value simply equals param value
		$params['filter']  = array();
		// check params are boolean triggers for custom checks to determine if a result should be returned
		$params['check'] = array();

		// Doesn't seem like Advantage keeps job dates up-to-date so don't use that as parameter
		//$params['request']['shiftStart']    = (isset($query->dateStart)) ? $query->dateStart : null;
		//$params['request']['dateStart']     = (isset($query->dateStart)) ? $query->dateStart : null;
		$params['request']['status']        = (isset($query->status))    ? $query->status : null;
		// We'll set orderBy1 in _getRequest()
		$params['request']['orderBy2']      = 'state';
		$params['request']['orderBy3']      = 'city';

		if ($queryUri)
		{
			$queryUriArray = explode('/', $queryUri);

			// NOTE: the first spot in $queryUriArray is empty, so start from index '1'
			$params['jobTypeSlug']              = ($queryUriArray[1] !== 'all') ? urldecode($queryUriArray[1]) : null;
			$params['request']['certification'] = ($queryUriArray[2] !== 'all') ? craft()->apiHealthcare_options->getProfessionNameBySlug($queryUriArray[2]) : null;
			$params['request']['specialty']     = ($queryUriArray[3] !== 'all') ? craft()->apiHealthcare_options->getSpecialtyNameBySlug($queryUriArray[3]) : craft()->apiHealthcare_options->getWhitelistedSpecialtyNames();
			$params['request']['clientStateIn'] = ($queryUriArray[4] !== 'all') ? urldecode($queryUriArray[4]) : null;
			$params['request']['clientCityIn']  = ($queryUriArray[5] !== 'all') ? urldecode($queryUriArray[5]) : null;
			// NOTE: client asked not to filter by Zip
			//$params['filter']['zipCode']        = ($queryUriArray[6] !== 'all') ? urldecode($queryUriArray[6]) : null;
			$params['filter']['zipCode'] = null;

			
			if (!$params['request']['certification'])
			{
				$params['check']['certification'] = true;
			}
			else
			{
				$params['check'] = null;
			}

			if (!$params['filter']['zipCode'])
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
	private function _prepResults($jsonString, $filters = null, $checks = null)
	{
		if (!$jsonString)
		{
			return false;
		}

		// set variable placeholder for certification checks
		$whitelistedProfessions = false;

		$results = array();
		$json = stripslashes($jsonString);
		$array = JsonHelper::decode($json);

		if (is_array($array))
		{
			foreach ($array as $item)
			{
				$match = true;

				// if filters params exist, test against filters
				if (is_array($filters) && !empty($filters))
				{
					foreach($filters as $key => $value)
					{
						$match = ($item[$key] === $value) ? true : false;
					}
				}

				// if checks params exist, test against checks
				if (is_array($checks) && !empty($checks))
				{
					if (isset($checks['certification']) && $checks['certification'])
					{
						$whitelistedProfessions = $whitelistedProfessions ? $whitelistedProfessions : craft()->apiHealthcare_options->getWhitelistedProfessions();

						foreach ($whitelistedProfessions as $whitelistedProfession)
						{
							$match = ($item['orderCertification'] === $whitelistedProfession->name) ? true : false;
							if ($match) { break; }
						}
					}
				}

				if ($match)
				{
					$result = new ApiHealthcare_QueryModel();

					//$result->jobType   = $item['jobType'];
					$result->profession  = $item['orderCertification'];
					$result->specialty   = $item['orderSpecialty'];
					$result->state       = $item['state'];
					$result->city        = $item['city'];
					$result->zipCode     = $item['zipCode'];
					//$result->dateStart   = $item['shiftStartTime'];
					$result->dateStart   = (isset($item['dateStart'])) ? $item['dateStart'] : $item['shiftStartTime'];
					$result->status      = $item['status'];
					$result->jobId       = (isset($item['orderId'])) ? $item['orderId'] : $item['lt_orderId'];
					$result->jobType     = (isset($item['orderId'])) ? 'Per Diem' : 'Travel & Local Contracts';
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
	 * @param string $type
	 * @param array $params
	 * @return array containing multiple ApiHealthcare_QueryModels
	 */
	private function _getResults($type, $params)
	{
		if (!$type || !$params)
		{
			return false;
		}

		if ($type === 'getOrders')
		{
			$params['request']['orderBy1'] = 'shiftStartTime';
			$params['request']['clientId'] = craft()->apiHealthcare_options->getPerDiemClientClientIds();
		}
		else if ($type === 'getLtOrders')
		{
			$params['request']['orderBy1'] = 'dateStart';
		}
		
		$jsonString = $this->_sendRequest($type, $params['request']);

		return $this->_prepResults($jsonString, $params['filter'], $params['check']);
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

		if (!$query->jobTypeSlug)    { $query->jobTypeSlug = 'all'; }
		if (!$query->professionSlug) { $query->professionSlug = 'all'; }
		if (!$query->specialtySlug)  { $query->specialtySlug = 'all'; }
		if (!$query->state)          { $query->state = 'all'; }
		if (!$query->city)           { $query->city = 'all'; }
		if (!$query->zipCode)        { $query->zipCode = 'all'; }

		$searchUri = '/'
			. $query->jobTypeSlug . '/'
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
		$query->dateStart  = date('Y-m-d');
		$query->status     = 'open';

		$queryString = craft()->request->queryStringWithoutPath;
		$requestUri  = craft()->request->requestUri;

		$params = array();
		$results = null;

		//return craft()->apiHealthcare_options->getWhitelistedSpecialtyNames();


		if ($queryString === 'show-all=true')
		{
			$params = $this->_prepSearchParams($query);
			
			// Parse Orders
			$resultsOrders = $this->_getResults('getOrders', $params);

			// Parse Long-Term Orders
			$resultsLtOrders = $this->_getResults('getLtOrders', $params);
		}
		else
		{
			$parts = explode($query->getSearchBaseUri(), $requestUri);

			if (isset($parts[1]) && strlen($parts[1]) > 1)
			{
				$queryUri = $parts[1];
				$params = $this->_prepSearchParams($query, $queryUri);

				// Search Per Diem
				if ($params['jobTypeSlug'] === 'per-diem')
				{
					$results = $this->_getResults('getOrders', $params);
				}
				// Search Long-Term
				else if ($params['jobTypeSlug'] === 'travel-contracts')
				{
					$results = $this->_getResults('getLtOrders', $params);
				}
				// Search Everything
				else
				{
					// Parse Orders
					$resultsOrders = $this->_getResults('getOrders', $params);

					// Parse Long-Term Orders
					$resultsLtOrders = $this->_getResults('getLtOrders', $params);
				}
			}
		}

		if (isset($resultsOrders) && isset($resultsLtOrders))
		{
			// Merge & Sort Orders & Long-Term Orders
			$results = array_merge($resultsOrders, $resultsLtOrders);
			usort($results, function($a, $b) {
				return strcmp($a->dateStart, $b->dateStart);
			});
		}
		else if (isset($resultsOrders))
		{
			$results = $resultsOrders;
		}
		else if (isset($resultsLtOrders))
		{
			$results = $resultsLtOrders;
		}
		
		return $results;
	}

	/**
	 * @param string $id
	 * @return array
	 */
	public function getLtOrderById($id)
	{
		$params = array();
		$params['ltOrderId'] = $id;
		$jsonString = $this->_sendRequest('getLtOrders', $params);

		return $jsonString;
	}

}