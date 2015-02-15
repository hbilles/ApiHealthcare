<?php
namespace Craft;

class ApiHealthcareController extends BaseController
{
	protected $allowAnonymous = array('actionGetSearchUrl');

	public function actionGetSearchUrl()
	{
		$this->requirePostRequest();

		$query = new ApiHealthcare_QueryModel();

		$query->profession = craft()->request->getPost('profession');
		if ($query->profession)
		{
			$query->profession = urlencode($query->profession);
		}

		$query->specialty  = craft()->request->getPost('specialty');
		if ($query->specialty)
		{
			$query->specialty = urlencode($query->specialty);
		}

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

		$searchUrl = craft()->apiHealthcare->getSearchUrl($query);

		if ($searchUrl)
		{
			$this->redirect($searchUrl);
		}
		else
		{
			$this->redirect('/jobs');
		}
	}
}