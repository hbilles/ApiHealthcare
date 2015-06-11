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

		$params['request']['shiftStart']    = (isset($query->dateStart)) ? $query->dateStart : null;
		$params['request']['dateStart']     = (isset($query->dateStart)) ? $query->dateStart : null;
		$params['request']['status']        = (isset($query->status))     ? $query->status : null;
		//$params['request']['orderBy1']      = 'shiftStartTime';
		//$params['request']['orderBy1']      = 'dateStart';
		$params['request']['orderBy2']      = 'state';
		$params['request']['orderBy3']      = 'city';

		if ($queryUri)
		{
			$queryUriArray = explode('/', $queryUri);

			// NOTE: the first spot in $queryUriArray is empty, so start from index '1'
			$params['jobTypeSlug']              = ($queryUriArray[1] !== 'all') ? urldecode($queryUriArray[1]) : null;
			$params['request']['certification'] = ($queryUriArray[2] !== 'all') ? craft()->apiHealthcare_options->getProfessionNameBySlug($queryUriArray[2]) : null;
			$params['request']['specialty']     = ($queryUriArray[3] !== 'all') ? craft()->apiHealthcare_options->getSpecialtyNameBySlug($queryUriArray[3]) : null;
			$params['request']['clientStateIn'] = ($queryUriArray[4] !== 'all') ? urldecode($queryUriArray[4]) : null;
			$params['request']['clientCityIn']  = ($queryUriArray[5] !== 'all') ? urldecode($queryUriArray[5]) : null;
			// NOTE: client asked not to filter by Zip
			//$params['filter']['zipCode']        = ($queryUriArray[6] !== 'all') ? urldecode($queryUriArray[6]) : null;
			$params['filter']['zipCode'] = null;

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
	 * @param array $a
	 * @param array $b
	 * @return bool
	 */
	private function _sortByDateStart($a, $b)
	{
		return strcmp($a->dateStart, $b->dateStart);
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
		$query->dateStart = '2015-05-01';
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

				// Search Per Diem
				if ($params['jobTypeSlug'] === 'per-diem')
				{
					$params['request']['orderBy1'] = 'shiftStartTime';
					$jsonString = $this->_sendRequest('getOrders', $params['request']);

					$results = $this->_prepResults($jsonString, $params['filter']);
				}
				// Search Long-Term
				else if ($params['jobTypeSlug'] === 'travel-contracts')
				{
					$params['request']['orderBy1'] = 'dateStart';
					$jsonString = $this->_sendRequest('getLtOrders', $params['request']);

					$results = $this->_prepResults($jsonString, $params['filter']);
				}
				// Search Everything
				else {
					// Parse Orders
					$params['request']['orderBy1'] = 'shiftStartTime';
					$jsonOrders = $this->_sendRequest('getOrders', $params['request']);

					$resultsOrders = $this->_prepResults($jsonOrders, $params['filter']);


					// Parse Long-Term Orders
					$params['request']['orderBy1'] = 'dateStart';
					$jsonLtOrders = $this->_sendRequest('getLtOrders', $params['request']);

					$resultsLtOrders = $this->_prepResults($jsonLtOrders, $params['filter']);


					// Merge & Sort Orders & Long-Term Orders
					$results = array_merge($resultsOrders, $resultsLtOrders);
					//usort($results, "$this->_sortByDateStart");
					usort($results, function($a, $b) {
						return strcmp($a->dateStart, $b->dateStart);
					});
				}
			}
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