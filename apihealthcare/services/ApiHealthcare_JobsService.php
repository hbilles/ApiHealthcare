<?php
namespace Craft;

class ApiHealthcare_JobsService extends ApiHealthcare_BaseService
{
	/**
	 * @return string
	 */
	private function _getSearchBaseUri()
	{
		$settings = craft()->plugins->getPlugin('apiHealthcare')->getSettings();
		if ($settings->searchBaseUri)
		{
			return $settings->searchBaseUri;
		}
		else
		{
			throw new Exception("searchBaseUri field must be set on the plugin's settings page");
		}
	}

	/**
	 * @param string $jsonString
	 * @return array containing multiple ApiHealthcare_JobModels
	 */
	private function _prepImportResults($jsonString)
	{
		if (!$jsonString)
		{
			return false;
		}

		// get white-listed Professions to check against
		$whitelistedProfessions = craft()->apiHealthcare_professions->getWhitelisted();

		$results = array();
		$json = stripslashes($jsonString);
		$array = JsonHelper::decode($jsonString);

		if (is_array($array))
		{
			foreach ($array as $item)
			{
				$match = true;

				foreach ($whitelistedProfessions as $whitelistedProfession)
				{
					$match = ($item['orderCertification'] === $whitelistedProfession->name) ? true : false;
					if ($match) { break; }
				}

				if ($match)
				{
					$result = new ApiHealthcare_JobModel();

					$result->profession     = $item['orderCertification'];
					$result->professionSlug = craft()->apiHealthcare_professions->getSlugByName($item['orderCertification']);
					$result->specialty      = $item['orderSpecialty'];
					$result->specialtySlug  = craft()->apiHealthcare_specialties->getSlugByName($item['orderSpecialty']);
					$result->state          = $item['state'];
					$result->city           = $item['city'];
					$result->zipCode        = $item['zipCode'];
					$result->dateStart      = (isset($item['dateStart'])) ? $item['dateStart'] : $item['shiftStartTime'];
					$result->jobId          = (isset($item['orderId'])) ? $item['orderId'] : $item['lt_orderId'];
					$result->jobType        = (isset($item['orderId'])) ? 'Per Diem' : 'Travel & Local Contracts';
					$result->jobTypeSlug    = (isset($item['orderId'])) ? 'per-diem' : 'travel-contracts';
					$result->isHotJob       = (bool) (isset($item['isHotJob'])) ? $item['isHotJob'] : false;

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
	private function _importJobResultType($type, $params)
	{
		if (!$type || !$params)
		{
			return false;
		}

		if ($type === 'getOrders')
		{
			$params['orderBy1'] = 'shiftStartTime';
			$params['orderByDirection1'] = 'DESC';
			$params['clientId'] = craft()->apiHealthcare_perDiemClients->getIds();
		}
		else if ($type === 'getLtOrders')
		{
			$params['orderBy1'] = 'dateStart';
			$params['orderByDirection1'] = 'DESC';
			// Only pull order from Travel Region
			$params['regionIdIn'] = '3';
		}
		
		$jsonString = $this->_sendRequest($type, $params);

		return $this->_prepImportResults($jsonString);
	}

	/**
	 * @return array $results
	 */
	private function _importJobResults()
	{
		$params = array();
		$params['status']    = 'open';
		$params['specialty'] = craft()->apiHealthcare_specialties->getWhitelistedNames();

		// Parse Orders
		$resultsOrders = $this->_importJobResultType('getOrders', $params);

		// Parse Long-Term Orders
		$resultsLtOrders = $this->_importJobResultType('getLtOrders', $params);

		$results = array_merge($resultsOrders, $resultsLtOrders);

		return $results;
	}

	/**
	 * @param ApiHealthcare_JobModel $queryModel
	 * @return array $results
	 */
	private function _getJobResults(ApiHealthcare_JobModel $queryModel)
	{
		if (!$queryModel)
		{
			return false;
		}
		else
		{
			$attributes = array();
			$conditions = array();

			if ($queryModel->jobTypeSlug !== 'all' && $queryModel->jobTypeSlug !== null)
			{
				$attributes['jobTypeSlug'] = $queryModel->jobTypeSlug;
			}

			if ($queryModel->professionSlug !== 'all' && $queryModel->professionSlug !== null)
			{
				$attributes['professionSlug'] = $queryModel->professionSlug;
			}

			if ($queryModel->specialtySlug !== 'all' && $queryModel->specialtySlug !== null)
			{
				$attributes['specialtySlug'] = $queryModel->specialtySlug;
			}

			if ($queryModel->state !== 'all' && $queryModel->state !== null)
			{
				$attributes['state'] = $queryModel->state;
			}

			if ($queryModel->isHotJob)
			{				
				$attributes['isHotJob'] = (bool) $queryModel->isHotJob;
			}

			$jobRecords = ApiHealthcare_JobRecord::model()->ordered()->findAllByAttributes($attributes);

			if ($jobRecords)
			{
				$jobModels = ApiHealthcare_JobModel::populateModels($jobRecords);
				$sortedJobModels = $this->_sortHotJobs($jobModels);
				
				return $sortedJobModels;
			}
			else
			{
				return false;
			}
		}
	}

	/**
	 * @param string $queryUri
	 * @return array $results
	 */
	private function _getJobResultsFromUri($queryUri)
	{
		if (!$queryUri)
		{
			return false;
		}
		else
		{
			$queryUriArray = explode('/', $queryUri);
			$queryModel = new ApiHealthcare_JobModel();

			// NOTE: the first spot in $queryUriArray is empty, so start from index '1'
			/*
			$queryModel->jobTypeSlug              = ($queryUriArray[1] !== 'all') ? urldecode($queryUriArray[1]) : null;
			$queryModel->professionSlug           = ($queryUriArray[2] !== 'all') ? urldecode($queryUriArray[2]) : null;
			$queryModel->specialtySlug            = ($queryUriArray[3] !== 'all') ? urldecode($queryUriArray[3]) : null;
			$queryModel->state                    = ($queryUriArray[4] !== 'all') ? urldecode($queryUriArray[4]) : null;
			*/
			$queryModel->jobTypeSlug              = urldecode($queryUriArray[1]);
			$queryModel->professionSlug           = urldecode($queryUriArray[2]);
			$queryModel->specialtySlug            = urldecode($queryUriArray[3]);
			$queryModel->state                    = urldecode($queryUriArray[4]);
			// NOTE: not searching by city for now
			//$queryModel->city                     = ($queryUriArray[5] !== 'all') ? urldecode($queryUriArray[5]) : null;
			// NOTE: client asked not to filter by Zip
			//$queryModel->zipCode        = ($queryUriArray[6] !== 'all') ? urldecode($queryUriArray[6]) : null;
			//throw new Exception(Craft::t('$queryModel->professionSlug = ' . $queryModel->professionSlug));
			//throw new Exception(Craft::t('$queryModel->state = ' . $queryModel->state));

			return $this->_getJobResults($queryModel);
		}
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
	 * @param ApiHealthcare_QueryModel $query
	 * @return string
	 */
	public function getJobSearchUrl(ApiHealthcare_JobModel $queryModel)
	{
		if (!$queryModel)
		{
			return false;
		}

		if (!$queryModel->jobTypeSlug)    { $queryModel->jobTypeSlug = 'all'; }
		if (!$queryModel->professionSlug) { $queryModel->professionSlug = 'all'; }
		if (!$queryModel->specialtySlug)  { $queryModel->specialtySlug = 'all'; }
		if (!$queryModel->state)          { $queryModel->state = 'all'; }
		if (!$queryModel->city)           { $queryModel->city = 'all'; }
		if (!$queryModel->zipCode)        { $queryModel->zipCode = 'all'; }

		$searchUri = '/'
			. $queryModel->jobTypeSlug . '/'
			. $queryModel->professionSlug . '/'
			. $queryModel->specialtySlug . '/'
			. $queryModel->state . '/'
			. $queryModel->city . '/'
			. $queryModel->zipCode;

		$searchUri = ($searchUri === '/all/all/all/all/all/all') ? '?show-all=true' : $searchUri;

		return craft()->siteUrl . $this->_getSearchBaseUri() . $searchUri;
	}

	/**
	 * @return array
	 */
	public function getJobResultsFromUrl()
	{
		$queryString = craft()->request->queryStringWithoutPath;
		$requestUri  = craft()->request->requestUri;

		if ($queryString === 'show-all=true')
		{
			return $this->getAll();
		}
		else
		{
			$parts = explode($this->_getSearchBaseUri(), $requestUri);

			if (isset($parts[1]) && strlen($parts[1]) > 1)
			{
				$queryUri = $parts[1];
				return $this->_getJobResultsFromUri($queryUri);				
			}
			else
			{
				return false;
			}

			return false;
		}
	}

	/**
	 * @return string
	 */
	public function youSearchedFor()
	{
		$query = new ApiHealthcare_JobModel();

		$queryString = craft()->request->queryStringWithoutPath;
		$requestUri  = craft()->request->requestUri;

		$result = null;

		if ($queryString === 'show-all=true')
		{
			$result = "Displaying results for: <strong>All Clinician</strong> jobs";
		}
		else
		{
			$parts = explode($this->_getSearchBaseUri(), $requestUri);

			if (isset($parts[1]) && strlen($parts[1]) > 1)
			{
				$queryUri = $parts[1];
				$queryUriArray = explode('/', $queryUri);

				// Assignment Type
				if ($queryUriArray[1] !== 'all')
				{
					if ($queryUriArray[1] === 'per-diem')
					{
						$result = "Displaying results for: <strong>Per-Diem</strong>";
					}
					else
					{
						$result = "Displaying results for: <strong>Travel and Contract</strong>";
					}
					
				}
				else
				{
					$result = "Displaying results for:";
				}

				// Professions
				if ($queryUriArray[2] !== 'all')
				{
					$result .= " <strong>" . craft()->apiHealthcare_professions->getNameBySlug($queryUriArray[2]) . "</strong>";

					// Check Specialty, add hyphen if exists
					if ($queryUriArray[3] !== 'all')
					{
						$result .= " -";
					}
				}

				// Specialties
				if ($queryUriArray[3] !== 'all')
				{
					$result .= " <strong>" . craft()->apiHealthcare_specialties->getNameBySlug($queryUriArray[3]) . "</strong>";
				}

				$result .= " jobs";

				// Location
				if ($queryUriArray[4] !== 'all')
				{
					$result .= " in <strong>" . craft()->apiHealthcare_locations->getNameByAbbreviation($queryUriArray[4]) . "</strong>";
				}
			}
		}
		
		return $result;
	}

	/**
	 * @return string
	 */
	public function jobSearchTitle($meta = false)
	{
		$query = new ApiHealthcare_JobModel();

		$queryString = craft()->request->queryStringWithoutPath;
		$requestUri  = craft()->request->requestUri;

		$result = null;

		if ($queryString === 'show-all=true')
		{
			if ($meta)
			{
				$result = "Medical Clinician Job Search";
			}
			else
			{
				$result = "All Medical Clinician Jobs";
			}
		}
		else
		{
			$parts = explode($this->_getSearchBaseUri(), $requestUri);

			if (isset($parts[1]) && strlen($parts[1]) > 1)
			{
				$queryUri = $parts[1];
				$queryUriArray = explode('/', $queryUri);

				$result = "";

				// Assignment Type
				if ($queryUriArray[1] !== 'all')
				{
					if ($queryUriArray[1] === 'per-diem')
					{
						$result .= "Per-Diem ";
					}
					else
					{
						$result .= "Travel and Contract ";
					}
					
				}

				// Professions
				if ($queryUriArray[2] !== 'all')
				{
					$result .= craft()->apiHealthcare_professions->getNameBySlug($queryUriArray[2]) . " ";

					// Check Specialty, add hyphen if exists
					if ($queryUriArray[3] !== 'all')
					{
						$result .= "- ";
					}
				}

				// Specialties
				if ($queryUriArray[3] !== 'all')
				{
					$result .= craft()->apiHealthcare_specialties->getNameBySlug($queryUriArray[3]) . " ";
				}

				if ($meta)
				{
					$result .= "Job Search";
				}
				else
				{
					$result .= "Jobs";
				}


				// Location
				if ($queryUriArray[4] !== 'all')
				{
					$result .= " in " . craft()->apiHealthcare_locations->getNameByAbbreviation($queryUriArray[4]);
				}
			}
		}
		
		return $result;
	}

	/**
	 * @return array
	 */
	public function getAll()
	{
		$jobRecords = ApiHealthcare_JobRecord::model()->ordered()->findAll();

		if ($jobRecords)
		{
			$jobModels = ApiHealthcare_JobModel::populateModels($jobRecords);
			$sortedJobModels = $this->_sortHotJobs($jobModels);

			return $sortedJobModels;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @return bool
	 */
	public function updateRecords()
	{
		$jobs = $this->_importJobResults();

		if ($jobs && is_array($jobs))
		{
			$currentJobIds = array();

			foreach($jobs as $job)
			{
				$currentJobIds[] = $job->jobId;

				$jobRecord = ApiHealthcare_JobRecord::model()->findByAttributes(array(
					'jobId' => $job->jobId
				));

				if (!$jobRecord)
				{
					$jobRecord = new ApiHealthcare_JobRecord();
					$jobRecord->jobId       = $job->jobId;
					$jobRecord->jobType     = $job->jobType;
					$jobRecord->jobTypeSlug = $job->jobTypeSlug;
				}

				$jobRecord->profession     = $job->profession;
				$jobRecord->professionSlug = $job->professionSlug;
				$jobRecord->specialty      = $job->specialty;
				$jobRecord->specialtySlug  = $job->specialtySlug;
				$jobRecord->state          = $job->state;
				$jobRecord->city           = $job->city;
				$jobRecord->zipCode        = $job->zipCode;
				$jobRecord->dateStart      = $job->dateStart;
				$jobRecord->isHotJob       = $job->isHotJob;
				$jobRecord->description    = $job->description;

				$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
				try
				{
					$jobRecord->save();

					if ($transaction !== null) { $transaction->commit(); }
				}
				catch (\Exception $e)
				{
					if ($transaction !== null) { $transaction->rollback; }

					throw $e;
				}
			}

			// Delete non-current Jobs
			$this->deleteUnused($currentJobIds);

			return true;
		}

		return false;
	}

	/**
	 * @param string $id
	 * @return array
	 */
	public function getOrderById($jobId, $jobTypeSlug)
	{
		if (!$jobId || !$jobTypeSlug)
		{
			return false;
		}

		$jobRecord = ApiHealthcare_JobRecord::model()->findByAttributes(array(
			'jobId'       => $jobId,
			'jobTypeSlug' => $jobTypeSlug
		));
		return ApiHealthcare_JobModel::populateModel($jobRecord);
	}

	/**
	 * @param string $limit
	 * @return array
	 */
	public function getHotJobs($limit = null)
	{
		$queryModel = new ApiHealthcare_JobModel();

		$queryModel->isHotJob = true;

		$results = $this->_getJobResults($queryModel);
		
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


	/**
	 * @param array $criteria
	 * @return array
	 */
	public function getJobListingByCriteria($criteria)
	{
		if (!$criteria)
		{
			return false;
		}

		$queryModel = new ApiHealthcare_JobModel();

		$queryModel->jobTypeSlug    = $criteria['jobType'];
		$queryModel->professionSlug = $criteria['profession'];
		$queryModel->specialtySlug  = $criteria['specialty'];
		$queryModel->state          = $criteria['location'];
		
		return $this->_getJobResults($queryModel);
	}

	/**
	 * @param array $currentJobIds
	 */
	public function deleteUnused($currentJobIds)
	{
		if (!$currentJobIds || !is_array($currentJobIds))
		{
			return false;
		}

		$jobRecords = ApiHealthcare_JobRecord::model()->findAll();
		$jobModels  = ApiHealthcare_JobModel::populateModels($jobRecords);

		if (!$jobRecords)
		{
			return false;
		}

		foreach($jobModels as $jobModel)
		{
			if (!in_array($jobModel->jobId, $currentJobIds))
			{
				$this->delete($jobModel);
			}
		}
	}

	/**
	 * @param ApiHealthcare_JobModel $job
	 */
	public function delete(ApiHealthcare_JobModel $job)
	{
		if (!$job)
		{
			return false;
		}

		$jobRecord = ApiHealthcare_JobRecord::model()->findById($job->id);

		return $jobRecord->delete();
	}

}