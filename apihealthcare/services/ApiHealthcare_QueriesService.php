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
		// by default, we will need to check the returned results against the certification whitelist later
		$params['check']['certification'] = true;

		$params['request']['specialty']     = craft()->apiHealthcare_specialties->getWhitelistedNames();
		// Doesn't seem like Advantage keeps job dates up-to-date so don't use that as parameter
		//$params['request']['shiftStart']    = (isset($query->dateStart)) ? $query->dateStart : null;
		//$params['request']['dateStart']     = (isset($query->dateStart)) ? $query->dateStart : null;
		$params['request']['status']        = (isset($query->status))    ? $query->status : null;
		// We'll set orderBy1 in _getRequest()
		//$params['request']['orderBy2']      = 'state';
		//$params['request']['orderBy3']      = 'city';

		if ($queryUri)
		{
			$queryUriArray = explode('/', $queryUri);

			// NOTE: the first spot in $queryUriArray is empty, so start from index '1'
			$params['jobTypeSlug']              = ($queryUriArray[1] !== 'all') ? urldecode($queryUriArray[1]) : null;
			$params['request']['certification'] = ($queryUriArray[2] !== 'all') ? craft()->apiHealthcare_professions->getNameBySlug($queryUriArray[2]) : null;
			$params['request']['specialty']     = ($queryUriArray[3] !== 'all') ? craft()->apiHealthcare_specialties->getNameBySlug($queryUriArray[3]) : craft()->apiHealthcare_specialties->getWhitelistedNames();
			$params['request']['clientStateIn'] = ($queryUriArray[4] !== 'all') ? urldecode($queryUriArray[4]) : null;
			$params['request']['clientCityIn']  = ($queryUriArray[5] !== 'all') ? urldecode($queryUriArray[5]) : null;
			// NOTE: client asked not to filter by Zip
			//$params['filter']['zipCode']        = ($queryUriArray[6] !== 'all') ? urldecode($queryUriArray[6]) : null;
			$params['filter']['zipCode'] = null;

			
			if ($params['request']['certification'])
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

		// set variable flag for certification checks
		$whitelistedProfessions = false;

		// set variable for number of Hot Jobs in results
		$hotJobsCount = 0;

		$results = array();
		$json = stripslashes($jsonString);
		//$array = JsonHelper::decode($json);
		$array = JsonHelper::decode($jsonString);

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
						$whitelistedProfessions = $whitelistedProfessions ? $whitelistedProfessions : craft()->apiHealthcare_professions->getWhitelisted();

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
					$result->isHotJob    = (bool) (isset($item['isHotJob'])) ? $item['isHotJob'] : false;
					// We use 'note' for orders and 'transportationNote' or 'housingNote' for ltOrders
					// At client's request we do not use 'note' for ltOrders
					if (isset($item['orderId']))
					{
						$result->description = $item['note'];
					}
					else
					{
						if ($item['transportationNote'] && $item['housingNote'])
						{
							$result->description = $item['transportationNote'] . "\r\n\r\n" . $item['housingNote'];
						}
						else if ($item['transportationNote'])
						{
							$result->description = $item['transportationNote'];
						}
						else if ($item['housingNote'])
						{
							$result->description = $item['housingNote'];
						}
					}

					// flag hot jobs
					if ($result->isHotJob)
					{
						$hotJobsCount .= 1;
					}

					$results[] = $result;
				}
			}

			// Only sort hot jobs if there are
			if ($hotJobsCount > 0)
			{
				$results = $this->_sortHotJobs($results);
			}

			return $results;
		}

		return false;
	}

	/**
	 * @param array $results
	 * @return sorted array
	 */
	private function _sortHotJobs($results)
	{
		$hot    = array();
		$notHot = array();
		$rCount = count($results);

		for ($i = 0; $i < $rCount; $i++)
		{ 
			if ($results[$i]->isHotJob)
			{
				$hot[] = $results[$i];
			}
			else
			{
				$notHot[] = $results[$i];
			}
		}

		$results = array_merge($hot, $notHot);

		return $results;
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
			$params['request']['orderByDirection1'] = 'DESC';
			$params['request']['clientId'] = craft()->apiHealthcare_perDiemClients->getIds();
		}
		else if ($type === 'getLtOrders')
		{
			$params['request']['orderBy1'] = 'dateStart';
			$params['request']['orderByDirection1'] = 'DESC';
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
		//$query->dateStart  = date('Y-m-d');
		$query->status     = 'open';

		$queryString = craft()->request->queryStringWithoutPath;
		$requestUri  = craft()->request->requestUri;

		$params = array();
		$results = null;

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
			
			// sort by reverse chronological order
			usort($results, function($a, $b) {
				return strcmp($b->dateStart, $a->dateStart);
			});
			
			$results = $this->_sortHotJobs($results);
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
	public function getOrderById($id)
	{
		$params = array();
		$params['orderId'] = $id;
		$jsonString = $this->_sendRequest('getOrders', $params);

		return $jsonString;

		$results = $this->_prepResults($jsonString);

		if ($results)
		{
			return $results[0];
		}
		else
		{
			return false;
		}
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

		$results = $this->_prepResults($jsonString);

		if ($results)
		{
			return $results[0];
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param string $limit
	 * @return array
	 */
	public function getHotJobs($limit = null)
	{
		$query         = new ApiHealthcare_QueryModel();
		$query->status = 'open';

		
		$params  = $this->_prepSearchParams($query);
		$params['request']['hotJobsOnly'] = true;
		$results = $this->_getResults('getLtOrders', $params);

		if ($results)
		{
			if ($limit)
			{
				return array_slice($results, 0, $limit);
			}
			else
			{
				return $results;
			}
		}
		else
		{
			return false;
		}
	}

}