<?php
namespace Craft;

class ApiHealthcare_JobsController extends BaseController
{
	protected $allowAnonymous = array('actionGetJobSearchUrl', 'actionTriggerUpdate');

	public function actionIndex()
	{
		$variables['jobs'] = craft()->apiHealthcare_jobs->getAll();
		return $this->renderTemplate('apihealthcare/jobs', $variables);
	}

	public function actionUpdate()
	{
		if (craft()->apiHealthcare_jobs->updateRecords())
		{
			return $this->actionIndex();
		}
	}

	public function actionGetJobSearchUrl()
	{
		$this->requirePostRequest();

		$query = new ApiHealthcare_JobModel();

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

		$searchUrl = craft()->apiHealthcare_jobs->getJobSearchUrl($query);

		if ($searchUrl)
		{
			$this->redirect($searchUrl);
		}
		else
		{
			$this->redirect('/404');
		}
	}

	public function actionTriggerUpdate()
	{
		if (craft()->apiHealthcare_jobs->updateRecords())
		{
			return 'Jobs updated successfully.';
		}
		else
		{
			return 'Failed to update jobs.';
		}
	}
}