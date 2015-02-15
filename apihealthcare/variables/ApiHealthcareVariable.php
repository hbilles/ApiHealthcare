<?php
namespace Craft;

class ApiHealthcareVariable
{
	public function getProfessions()
	{
		return craft()->apiHealthcare->getProfessions();
	}

	public function getSpecialties()
	{
		return craft()->apiHealthcare->getSpecialties();
	}

	public function getSearchResultsFromUrl()
	{
		return craft()->apiHealthcare->getSearchResultsFromUrl();
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