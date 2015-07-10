<?php
namespace Craft;

class ApiHealthcare_ProfessionsController extends BaseController
{

	public function actionIndex()
	{
		$variables['professions'] = craft()->apiHealthcare_professions->getAll();
		return $this->renderTemplate('apihealthcare/professions', $variables);
	}

	public function actionUpdate()
	{
		if (craft()->apiHealthcare_professions->updateRecords())
		{
			return $this->actionIndex();
		}
	}

	public function actionReset()
	{
		if (craft()->apiHealthcare_professions->updateRecords(true))
		{
			return $this->actionIndex();
		}
	}

	public function actionEdit(array $variables = array())
	{
		// get the existing profession for editing
		if (!empty($variables['professionId']))
		{
			if (empty($variables['profession']))
			{
				$variables['profession'] = craft()->apiHealthcare_professions->getById($variables['professionId']);
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

	public function actionSave()
	{
		$this->requirePostRequest();

		$professionId = craft()->request->getPost('professionId');
		// try to get an existing profession
		$profession = craft()->apiHealthcare_professions->getById($professionId);

		if (!$profession)
		{
			//$profession = new ApiHealthcare_ProfessionModel();
			// add the new profession to the end of the sortOrder
			//$profession->sortOrder = craft()->apiHealthcare_professions->getNextOrder();
		}

		// save posted data to profession model
		//$profession->certId    = craft()->request->getPost('certId');
		//$profession->name      = craft()->request->getPost('name');
		$profession->slug      = craft()->request->getPost('slug');
		$profession->show      = (bool) craft()->request->getPost('show');

		// save the profession
		if (craft()->apiHealthcare_professions->save($profession))
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

	public function actionEditSearchSettings()
	{
		// get the existing professions for editing
		$variables = array();
		$variables['professions'] = craft()->apiHealthcare_professions->getAll();
		$variables['title'] = 'Edit Profession Search Options';

		$this->renderTemplate('apihealthcare/professions/_editSearchSettings', $variables);
	}

	public function actionSaveSearchSettings()
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
				$profession = craft()->apiHealthcare_professions->getById($professionId);

				if (!$profession)
				{
					throw new Exception(Craft::t('No profession exists with the ID "{id}"', array('id' => $professionId)));
				}

				// save posted data to profession model
				$profession->show = (bool) $postedProfession['show'];
				// save the profession
				if (!craft()->apiHealthcare_professions->save($profession))
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