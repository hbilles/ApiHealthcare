<?php
namespace Craft;

class ApiHealthcare_SpecialtiesController extends BaseController
{
	public function actionIndex()
	{
		$variables['specialties'] = craft()->apiHealthcare_specialties->getAll();
		return $this->renderTemplate('apihealthcare/specialties', $variables);
	}

	public function actionUpdate()
	{
		if (craft()->apiHealthcare_specialties->updateRecords())
		{
			return $this->actionIndex();
		}
	}

	public function actionReset()
	{
		if (craft()->apiHealthcare_specialties->updateRecords(true))
		{
			return $this->actionIndex();
		}
	}

	public function actionEdit(array $variables = array())
	{
		// get the existing specialty for editing
		if (!empty($variables['specialtyId']))
		{
			if (empty($variables['specialty']))
			{
				$variables['specialty'] = craft()->apiHealthcare_specialties->getById($variables['specialtyId']);
				if (!$variables['specialty'])
				{
					throw new HttpException(404);
				}
			}
			$variables['title'] = $variables['specialty']->name;
		}
		// else create a new specialty
		else
		{
			if (empty($variables['specialty']))
			{
				$variables['specialty'] = new ApiHealthcare_SpecialtyModel();
			}
			$variables['title'] = Craft::t('Enter a new specialty');
		}

		$this->renderTemplate('apihealthcare/specialties/_edit', $variables);
	}

	public function actionSave()
	{
		$this->requirePostRequest();

		$specialtyId = craft()->request->getPost('specialtyId');
		// try to get an existing specialty
		$specialty = craft()->apiHealthcare_specialties->getById($specialtyId);

		if (!$specialty)
		{
			//$specialty = new ApiHealthcare_SpecialtyModel();
		}

		// save posted data to specialty model
		//$specialty->specId    = craft()->request->getPost('specId');
		//$specialty->name      = craft()->request->getPost('name');
		$specialty->slug      = craft()->request->getPost('slug');
		$specialty->show      = (bool) craft()->request->getPost('show');

		// save the specialty
		if (craft()->apiHealthcare_specialties->save($specialty))
		{
			// Success!
			craft()->userSession->setNotice(Craft::t('Specialty Saved.'));
			$this->redirectToPostedUrl($specialty);
		}
		else
		{
			// Boo!
			craft()->userSession->setError(Craft::t('Specialty could not be saved.'));
			// Send the specialty data back to the template
			craft()->urlManager->setRouteVariables(array(
				'specialty' => $specialty
			));
		}
	}

	public function actionEditSearchSettings()
	{
		// get the existing specialties for editing
		$variables = array();
		$variables['specialties'] = craft()->apiHealthcare_specialties->getAll();
		$variables['title'] = 'Edit Specialty Search Options';

		$this->renderTemplate('apihealthcare/specialties/_editSearchSettings', $variables);
	}

	public function actionSaveSearchSettings()
	{
		$this->requirePostRequest();
		$postedSpecialties = craft()->request->getPost('specialties');
		$specialties = array();

		$allSystemsGo = true;

		if ($postedSpecialties)
		{
			foreach ($postedSpecialties as $specialtyId => $postedSpecialty)
			{
				// try to get an existing specialty
				$specialty = craft()->apiHealthcare_specialties->getById($specialtyId);

				if (!$specialty)
				{
					throw new Exception(Craft::t('No specialty exists with the ID "{id}"', array('id' => $specialtyId)));
				}

				// save posted data to specialty model
				$specialty->show = (bool) $postedSpecialty['show'];
				// save the specialty
				if (!craft()->apiHealthcare_specialties->save($specialty))
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