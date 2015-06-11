<?php
namespace Craft;

class ApiHealthcare_OptionsController extends BaseController
{

	public function actionProfessionsIndex()
	{
		$variables['professions'] = craft()->apiHealthcare_options->getAllProfessions();
		return $this->renderTemplate('apihealthcare/professions', $variables);
	}

	public function actionUpdateProfessions()
	{
		if (craft()->apiHealthcare_options->updateProfessionRecords())
		{
			return $this->actionProfessionsIndex();
		}
	}

	public function actionResetProfessions()
	{
		if (craft()->apiHealthcare_options->updateProfessionRecords(true))
		{
			return $this->actionProfessionsIndex();
		}
	}

	public function actionEditProfession(array $variables = array())
	{
		// get the existing profession for editing
		if (!empty($variables['professionId']))
		{
			if (empty($variables['profession']))
			{
				$variables['profession'] = craft()->apiHealthcare_options->getProfessionById($variables['professionId']);
				if (!$variables['profession'])
				{
					throw new HttpException(404);
				}
			}
			$variables['title'] = $variables['profession']->name;
		}
		// else create a new profession
		else
		{
			if (empty($variables['profession']))
			{
				$variables['profession'] = new ApiHealthcare_ProfessionModel();
			}
			$variables['title'] = Craft::t('Enter a new profession');
		}

		$this->renderTemplate('apihealthcare/professions/_edit', $variables);
	}

	public function actionSaveProfession()
	{
		$this->requirePostRequest();

		$professionId = craft()->request->getPost('professionId');
		// try to get an existing profession
		$profession = craft()->apiHealthcare_options->getProfessionById($professionId);

		if (!$profession)
		{
			//$profession = new ApiHealthcare_ProfessionModel();
			// add the new profession to the end of the sortOrder
			//$profession->sortOrder = craft()->apiHealthcare_options->getNextProfessionOrder();
		}

		// save posted data to profession model
		//$profession->certId    = craft()->request->getPost('certId');
		$profession->name      = craft()->request->getPost('name');
		$profession->slug      = craft()->request->getPost('slug');
		$profession->show      = (bool) craft()->request->getPost('show');

		// save the profession
		if (craft()->apiHealthcare_options->saveProfession($profession))
		{
			// Success!
			craft()->userSession->setNotice(Craft::t('Profession Saved.'));
			$this->redirectToPostedUrl($profession);
		}
		else
		{
			// Boo!
			craft()->userSession->setError(Craft::t('Profession could not be saved.'));
			// Send the profession data back to the template
			craft()->urlManager->setRouteVariables(array(
				'profession' => $profession
			));
		}
	}

	public function actionEditProfessionSearchSettings()
	{
		// get the existing professions for editing
		$variables = array();
		$variables['professions'] = craft()->apiHealthcare_options->getAllProfessions();
		$variables['title'] = 'Edit Profession Search Options';

		$this->renderTemplate('apihealthcare/professions/_editSearchSettings', $variables);
	}

	public function actionSaveProfessionSearchSettings()
	{
		$this->requirePostRequest();
		$postedProfessions = craft()->request->getPost('professions');
		$professions = array();

		$allSystemsGo = true;

		if ($postedProfessions)
		{
			foreach ($postedProfessions as $professionId => $postedProfession)
			{
				// try to get an existing profession
				$profession = craft()->apiHealthcare_options->getProfessionById($professionId);

				if (!$profession)
				{
					throw new Exception(Craft::t('No profession exists with the ID "{id}"', array('id' => $professionId)));
				}

				// save posted data to profession model
				$profession->show = (bool) $postedProfession['show'];
				// save the profession
				if (!craft()->apiHealthcare_options->saveProfession($profession))
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





	public function actionSpecialtiesIndex()
	{
		$variables['specialties'] = craft()->apiHealthcare_options->getAllSpecialties();
		return $this->renderTemplate('apihealthcare/specialties', $variables);
	}

	public function actionUpdateSpecialties()
	{
		if (craft()->apiHealthcare_options->updateSpecialtyRecords())
		{
			return $this->actionSpecialtiesIndex();
		}
	}

	public function actionResetSpecialties()
	{
		if (craft()->apiHealthcare_options->updateSpecialtyRecords(true))
		{
			return $this->actionSpecialtiesIndex();
		}
	}

	public function actionEditSpecialty(array $variables = array())
	{
		// get the existing specialty for editing
		if (!empty($variables['specialtyId']))
		{
			if (empty($variables['specialty']))
			{
				$variables['specialty'] = craft()->apiHealthcare_options->getSpecialtyById($variables['specialtyId']);
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

	public function actionSaveSpecialty()
	{
		$this->requirePostRequest();

		$specialtyId = craft()->request->getPost('specialtyId');
		// try to get an existing specialty
		$specialty = craft()->apiHealthcare_options->getSpecialtyById($specialtyId);

		if (!$specialty)
		{
			//$specialty = new ApiHealthcare_SpecialtyModel();
		}

		// save posted data to specialty model
		//$specialty->specId    = craft()->request->getPost('specId');
		$specialty->name      = craft()->request->getPost('name');
		$specialty->slug      = craft()->request->getPost('slug');
		$specialty->show      = (bool) craft()->request->getPost('show');

		// save the specialty
		if (craft()->apiHealthcare_options->saveSpecialty($specialty))
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

	public function actionEditSpecialtySearchSettings()
	{
		// get the existing specialties for editing
		$variables = array();
		$variables['specialties'] = craft()->apiHealthcare_options->getAllSpecialties();
		$variables['title'] = 'Edit Specialty Search Options';

		$this->renderTemplate('apihealthcare/specialties/_editSearchSettings', $variables);
	}

	public function actionSaveSpecialtySearchSettings()
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
				$specialty = craft()->apiHealthcare_options->getSpecialtyById($specialtyId);

				if (!$specialty)
				{
					throw new Exception(Craft::t('No specialty exists with the ID "{id}"', array('id' => $specialtyId)));
				}

				// save posted data to specialty model
				$specialty->show = (bool) $postedSpecialty['show'];
				// save the specialty
				if (!craft()->apiHealthcare_options->saveSpecialty($specialty))
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





	public function actionPerDiemClientsIndex()
	{
		$variables['perDiemClients'] = craft()->apiHealthcare_options->getAllPerDiemClients();
		return $this->renderTemplate('apihealthcare/perDiemClients', $variables);
	}

	public function actionEditPerDiemClient(array $variables = array())
	{
		// get the existing client for editing
		if (!empty($variables['perDiemClientId']))
		{
			if (empty($variables['perDiemClient']))
			{
				$variables['perDiemClient'] = craft()->apiHealthcare_options->getPerDiemClientById($variables['perDiemClientId']);
				if (!$variables['perDiemClient'])
				{
					throw new HttpException(404);
				}
			}
			$variables['title'] = $variables['perDiemClient']->name;
		}
		// else create a new client
		else
		{
			if (empty($variables['perDiemClient']))
			{
				$variables['perDiemClient'] = new ApiHealthcare_PerDiemClientModel();
			}
			$variables['title'] = Craft::t('Enter a new Per Diem Client');
		}

		$this->renderTemplate('apihealthcare/perDiemClients/_edit', $variables);
	}

	public function actionSavePerDiemClient()
	{
		$this->requirePostRequest();

		$perDiemClientId = craft()->request->getPost('perDiemClientId');
		// try to get an existing client
		$perDiemClient = craft()->apiHealthcare_options->getPerDiemClientById($perDiemClientId);

		if (!$perDiemClient)
		{
			$perDiemClient = new ApiHealthcare_PerDiemClientModel();
		}

		// save posted data to perDiemClient model
		$perDiemClient->name     = craft()->request->getPost('name');
		$perDiemClient->clientId = craft()->request->getPost('clientId');

		// save the client
		if (craft()->apiHealthcare_options->savePerDiemClient($perDiemClient))
		{
			// Success!
			craft()->userSession->setNotice(Craft::t('Client Saved.'));
			$this->redirectToPostedUrl($perDiemClient);
		}
		else
		{
			// Boo!
			craft()->userSession->setError(Craft::t('Client could not be saved.'));
			// Send the client data back to the template
			craft()->urlManager->setRouteVariables(array(
				'perDiemClient' => $perDiemClient
			));
		}
	}

	public function actionDeletePerDiemClient()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$perDiemClientId = craft()->request->getRequiredPost('id');

		$success = craft()->apiHealthcare_options->deletePerDiemClientById($perDiemClientId);
		$this->returnJson(array('success' => $success));
	}
}