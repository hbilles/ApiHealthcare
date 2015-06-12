<?php
namespace Craft;

class ApiHealthcareVariable
{
	public function getAllProfessions()
	{
		return craft()->apiHealthcare_options->getAllProfessions();
	}

	public function getWhitelistedProfessions()
	{
		return craft()->apiHealthcare_options->getWhitelistedProfessions();
	}

	public function getAllSpecialties()
	{
		return craft()->apiHealthcare_options->getAllSpecialties();
	}

	public function getWhitelistedSpecialties()
	{
		return craft()->apiHealthcare_options->getWhitelistedSpecialties();
	}

	public function getSearchResultsFromUrl()
	{
		return craft()->apiHealthcare_queries->getSearchResultsFromUrl();
	}

	public function getLtOrderById($id)
	{
		return craft()->apiHealthcare_queries->getLtOrderById($id);
	}

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
}