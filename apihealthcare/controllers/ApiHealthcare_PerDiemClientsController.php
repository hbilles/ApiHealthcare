<?php
namespace Craft;

class ApiHealthcare_OptionsController extends BaseController
{
	public function actionIndex()
	{
		$variables['perDiemClients'] = craft()->apiHealthcare_perDiemClients->getAll();
		return $this->renderTemplate('apihealthcare/perDiemClients', $variables);
	}

	public function actionEdit(array $variables = array())
	{
		// get the existing client for editing
		if (!empty($variables['perDiemClientId']))
		{
			if (empty($variables['perDiemClient']))
			{
				$variables['perDiemClient'] = craft()->apiHealthcare_perDiemClients->getById($variables['perDiemClientId']);
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

	public function actionSave()
	{
		$this->requirePostRequest();

		$perDiemClientId = craft()->request->getPost('perDiemClientId');
		// try to get an existing client
		$perDiemClient = craft()->apiHealthcare_perDiemClients->getById($perDiemClientId);

		if (!$perDiemClient)
		{
			$perDiemClient = new ApiHealthcare_PerDiemClientModel();
		}

		// save posted data to perDiemClient model
		$perDiemClient->name     = craft()->request->getPost('name');
		$perDiemClient->clientId = craft()->request->getPost('clientId');

		// save the client
		if (craft()->apiHealthcare_perDiemClients->save($perDiemClient))
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

	public function actionDelete()
	{
		$this->requirePostRequest();
		$this->requireAjaxRequest();

		$perDiemClientId = craft()->request->getRequiredPost('id');

		$success = craft()->apiHealthcare_perDiemClients->deleteById($perDiemClientId);
		$this->returnJson(array('success' => $success));
	}
}