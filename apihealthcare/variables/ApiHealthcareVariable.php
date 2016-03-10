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

	public function getAllSpecialties()
	{
		return craft()->apiHealthcare_specialties->getAll();
	}

	public function getWhitelistedSpecialties()
	{
		return craft()->apiHealthcare_specialties->getWhitelisted();
	}

	public function getWhitelistedLocations()
	{
		return craft()->apiHealthcare_locations->getWhitelisted();
	}

	public function getJobResultsFromUrl()
	{
		return craft()->apiHealthcare_queries->getJobResultsFromUrl();
	}

	// DEPRECATED: use getJobResultsFromUrl() instead
	public function getSearchResultsFromUrl()
	{
		return craft()->apiHealthcare_queries->getJobResultsFromUrl();
	}

	public function getJobListingByCriteria($criteria)
	{
		return craft()->apiHealthcare_queries->getJobListingByCriteria($criteria);
	}

	public function getOrderById($id)
	{
		return craft()->apiHealthcare_queries->getOrderById($id);
	}

	public function getLtOrderById($id)
	{
		return craft()->apiHealthcare_queries->getLtOrderById($id);
	}

	public function getHotJobs($limit = null)
	{
		return craft()->apiHealthcare_queries->getHotJobs($limit);
	}

	public function youSearchedFor()
	{
		return craft()->apiHealthcare_queries->youSearchedFor();
	}

	public function jobSearchMetaTitle()
	{
		return craft()->apiHealthcare_queries->jobSearchTitle(true);
	}

	public function jobSearchTitle()
	{
		return craft()->apiHealthcare_queries->jobSearchTitle();
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