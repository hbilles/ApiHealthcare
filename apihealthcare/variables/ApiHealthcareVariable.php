<?php
namespace Craft;

class ApiHealthcareVariable
{
	public function getAllProfessions()
	{
		return craft()->apiHealthcare_professions->getAll();
	}

	public function getWhitelistedProfessions()
	{
		return craft()->apiHealthcare_professions->getWhitelisted();
	}

	public function getAvailableProfessions()
	{
		return craft()->apiHealthcare_professions->getAvailable();
	}

	public function getAllSpecialties()
	{
		return craft()->apiHealthcare_specialties->getAll();
	}

	public function getWhitelistedSpecialties()
	{
		return craft()->apiHealthcare_specialties->getWhitelisted();
	}

	public function getAvailableSpecialties()
	{
		return craft()->apiHealthcare_specialties->getAvailable();
	}

	public function getWhitelistedLocations()
	{
		return craft()->apiHealthcare_locations->getWhitelisted();
	}

	public function getAvailableLocations()
	{
		return craft()->apiHealthcare_locations->getAvailable();
	}

	public function getJobResultsFromUrl()
	{
		return craft()->apiHealthcare_jobs->getJobResultsFromUrl();
	}

	// DEPRECATED: use getJobResultsFromUrl() instead
	public function getSearchResultsFromUrl()
	{
		return craft()->apiHealthcare_jobs->getJobResultsFromUrl();
	}

	public function getJobListingByCriteria($criteria)
	{
		return craft()->apiHealthcare_jobs->getJobListingByCriteria($criteria);
	}

	public function getOrderById($jobId)
	{
		return craft()->apiHealthcare_jobs->getOrderById($jobId, 'per-diem');
	}

	public function getLtOrderById($jobId)
	{
		return craft()->apiHealthcare_jobs->getOrderById($jobId, 'travel-contracts');
	}

	public function getHotJobs($limit = null)
	{
		return craft()->apiHealthcare_jobs->getHotJobs($limit);
	}

	public function youSearchedFor()
	{
		return craft()->apiHealthcare_jobs->youSearchedFor();
	}

	public function jobSearchMetaTitle()
	{
		return craft()->apiHealthcare_jobs->jobSearchTitle(true);
	}

	public function jobSearchTitle()
	{
		return craft()->apiHealthcare_jobs->jobSearchTitle();
	}

	/*
	public function testRequest()
	{
		$queryString = craft()->request->queryString;
		$queryStringWithoutPath = craft()->request->queryStringWithoutPath;
		$requestUri  = craft()->request->requestUri;

		$requestArray = explode('jobs', $requestUri);

		$array = explode('/', $requestArray[1]);
		$string = implode(',', $array);

		return $string;
	}
	*/
}