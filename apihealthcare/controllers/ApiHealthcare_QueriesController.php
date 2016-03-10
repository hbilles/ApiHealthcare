<?php
namespace Craft;

class ApiHealthcare_QueriesController extends BaseController
{
	protected $allowAnonymous = array('actionGetJobSearchUrl', 'actionGetSearchUrl');

	public function actionGetJobSearchUrl()
	{
		$this->requirePostRequest();

		$query = new ApiHealthcare_QueryModel();

		$query->jobTypeSlug    = craft()->request->getPost('jobType');
		$query->professionSlug = craft()->request->getPost('profession');
		$query->specialtySlug  = craft()->request->getPost('specialty');

		$locationString = craft()->request->getPost('location');
		if ($locationString)
		{
			// break up the parts of the string into an array
			$locationArray = ArrayHelper::stringToArray($locationString);

			if (is_numeric($locationString))
			{
				$query->zipCode = $locationString;
			}
			elseif (isset($locationArray[0]))
			{
				$query->state   = (isset($locationArray[1])) ? urlencode($locationArray[1]) : urlencode($locationArray[0]);
				$query->city    = (isset($locationArray[1])) ? urlencode($locationArray[0]) : null;
			}
		}

		$searchUrl = craft()->apiHealthcare_queries->getJobSearchUrl($query);

		if ($searchUrl)
		{
			$this->redirect($searchUrl);
		}
		else
		{
			$this->redirect('/404');
		}
	}

	// DEPRECATED: use actionGetJobSearchUrl() instead
	public function actionGetSearchUrl()
	{
		return $this->actionGetJobSearchUrl();
	}
}