<?php
namespace Craft;

class ApiHealthcare_LocationsController extends BaseController
{

	public function actionIndex()
	{
		$variables['locations'] = craft()->apiHealthcare_locations->getAll();
		return $this->renderTemplate('apihealthcare/locations', $variables);
	}

	public function actionPopulateStates()
	{
		if (craft()->apiHealthcare_locations->populateStates())
		{
			return $this->actionIndex();
		}
	}

	public function actionEditSearchSettings()
	{
		// get the existing locations for editing
		$variables = array();
		$variables['locations'] = craft()->apiHealthcare_locations->getAll();
		$variables['title'] = 'Edit Location Search Options';

		$this->renderTemplate('apihealthcare/locations/_editSearchSettings', $variables);
	}

	public function actionSaveSearchSettings()
	{
		$this->requirePostRequest();
		$postedLocations = craft()->request->getPost('locations');
		$locations = array();

		$allSystemsGo = true;

		if ($postedLocations)
		{
			foreach ($postedLocations as $locationId => $postedLocation)
			{
				// try to get an existing location
				$location = craft()->apiHealthcare_locations->getById($locationId);

				if (!$location)
				{
					throw new Exception(Craft::t('No location exists with the ID "{id}"', array('id' => $locationId)));
				}

				// save posted data to location model
				$location->show = (bool) $postedLocation['show'];
				// save the location
				if (!craft()->apiHealthcare_locations->save($location))
				{
					$allSystemsGo = false;
					break;
				}
			}
		}
		else
		{
			$allSystemsGo = false;
		}

		if ($allSystemsGo)
		{
			// Success!
			craft()->userSession->setNotice(Craft::t('Settings Saved.'));
			$this->redirectToPostedUrl();
		}
		else
		{
			// Boo!
			craft()->userSession->setError(Craft::t('Settings could not be saved.'));
		}
	}
}