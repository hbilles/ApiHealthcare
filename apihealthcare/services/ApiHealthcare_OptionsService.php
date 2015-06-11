<?php
namespace Craft;

class ApiHealthcare_OptionsService extends ApiHealthcare_BaseService
{
	/**
	 * @return array
	 */
	public function getAllProfessions()
	{
		$professionRecords = ApiHealthcare_ProfessionRecord::model()->ordered()->findAll();

		/* Don't do this as we are manually whitelisting Professions
		if (!$professionRecords && $this->updateProfessionRecords())
		{
			$professionRecords = ApiHealthcare_ProfessionRecord::model()->ordered()->findAll();
		}
		*/

		if ($professionRecords)
		{
			return ApiHealthcare_ProfessionModel::populateModels($professionRecords);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param string $professionId
	 * @return ApiHealthcare_ProfessionModel
	 */
	public function getProfessionById($professionId)
	{
		if (!$professionId)
		{
			return false;
		}

		$professionRecord = ApiHealthcare_ProfessionRecord::model()->findById($professionId);

		if ($professionRecord)
		{
			return ApiHealthcare_ProfessionModel::populateModel($professionRecord);
		}
	}

	/**
	 * @param string $slug
	 * @return string
	 */
	public function getProfessionNameBySlug($slug)
	{
		if (!$slug)
		{
			return false;
		}

		$professionRecord = ApiHealthcare_ProfessionRecord::model()->findByAttributes(array(
			'slug' => $slug
		));

		if (!$professionRecord)
		{
			throw new Exception('Profession with slug of ' . $slug . ' not found!');
		}

		return $professionRecord->name;
	}
	
	/**
	 * @return string
	 */
	public function getNextProfessionOrder()
	{
		$professions = $this->getAllProfessions();
		$sortOrder = 0;

		if ($professions)
		{
			foreach ($professions as $profession)
			{
				$sortOrder = $profession->sortOrder > $sortOrder ? $profession->sortOrder : $sortOrder;
			}
		}

		return $sortOrder + 1;
	}

	/**
	 * @return bool
	 */
	public function updateProfessionRecords($reset = false)
	{
		$json = $this->_sendRequest('getCerts');

		if ($json)
		{
			$response = JsonHelper::decode($json);

			if (is_array($response))
			{
				$currentCertIds = array();

				foreach ($response as $item)
				{
					$profession = new ApiHealthcare_ProfessionModel();
					
					$profession->certId    = $item['certId'];
					$profession->name      = $item['certName'];
					$profession->sortOrder = $item['certSortOrder'];
					$profession->slug      = ElementHelper::createSlug($profession->name);

					$currentCertIds[] = $profession->certId;

					$professionRecord = ApiHealthcare_ProfessionRecord::model()->findByAttributes(array(
						'certId' => $profession->certId
					));

					if (!$professionRecord || $reset)
					{
						$professionRecord = $professionRecord ? $professionRecord : new ApiHealthcare_ProfessionRecord();

						$professionRecord->slug  = $profession->slug;
						$professionRecord->name  = $profession->name;
					}

					$professionRecord->certId    = $profession->certId;
					$professionRecord->sortOrder = $profession->sortOrder;

					$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
					try
					{
						$professionRecord->save();

						if ($transaction !== null) { $transaction->commit(); }
					}
					catch (\Exception $e)
					{
						if ($transaction !== null) { $transaction->rollback; }

						throw $e;
					}
				}

				// Delete unused Professions
				$this->deleteUnusedProfessions($currentCertIds);

				return true;
			}

			return false;
		}
	}

	/**
	 * @param ApiHealthcare_ProfessionModel $profession
	 */
	public function saveProfession(ApiHealthcare_ProfessionModel $profession)
	{
		// get record if exists
		if ($profession->id)
		{
			$professionRecord = ApiHealthcare_ProfessionRecord::model()->findById($profession->id);

			if (!$professionRecord)
			{
				throw new Exception(Craft::t('No Profession exists with the ID "{id}"', array('id' => $profession->id)));
			}
		}
		// else create new profession
		else
		{
			$professionRecord = new ApiHealthcare_ProfessionRecord();
		}

		// set the profession's attributes
		$professionRecord->certId    = $profession->certId;
		$professionRecord->name      = $profession->name;
		$professionRecord->slug      = $profession->slug;
		$professionRecord->sortOrder = $profession->sortOrder;
		$professionRecord->show      = (bool) $profession->show;

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// validate the profession
			$professionRecord->validate();
			$profession->addErrors($professionRecord->getErrors());

			if (!$profession->hasErrors())
			{
				// save without running validation again
				$professionRecord->save(false);

				if ($transaction !== null) { $transaction->commit(); }

				$profession->id = $professionRecord->id;

				return true;
			}
			else
			{
				if ($transaction !== null) { $transaction->rollback(); }

				return false;
			}
		}
		catch (\Exception $e)
		{
			if ($transaction !== null) { $transaction->rollback(); }

			throw $e;
		}
	}

	/**
	 * @param string $professionId
	 */
	public function deleteProfessionById($professionId)
	{
		if (!$professionId)
		{
			return false;
		}

		$professionRecord = ApiHealthcare_ProfessionRecord::model()->findById($professionId);
		$professionModel  = ApiHealthcare_ProfessionModel::populateModel($professionRecord);

		if (!$professionRecord)
		{
			return false;
		}

		return $professionRecord->delete();
	}

	/**
	 * @param array $currentCertIds
	 */
	public function deleteUnusedProfessions($currentCertIds)
	{
		if (!$currentCertIds || !is_array($currentCertIds))
		{
			return false;
		}

		$professionRecords = ApiHealthcare_ProfessionRecord::model()->findAll();
		$professionModels  = ApiHealthcare_ProfessionModel::populateModels($professionRecords);

		if (!$professionRecords)
		{
			return false;
		}

		foreach($professionModels as $professionModel)
		{
			if (!in_array($professionModel->certId, $currentCertIds))
			{
				$this->deleteProfession($professionModel);
			}
		}
	}

	/**
	 * @param ApiHealthcare_ProfessionModel $profession
	 */
	public function deleteProfession(ApiHealthcare_ProfessionModel $profession)
	{
		if (!$profession)
		{
			return false;
		}

		$professionRecord = ApiHealthcare_ProfessionRecord::model()->findById($profession->id);

		return $professionRecord->delete();
	}





	/**
	 * @return array
	 */
	public function getAllSpecialties()
	{
		$specialtyRecords = ApiHealthcare_SpecialtyRecord::model()->ordered()->findAll();

		/* Don't do this as we are manually whitelisting Specialties
		if (!$specialtyRecords && $this->updateSpecialtyRecords())
		{
			$specialtyRecords = ApiHealthcare_SpecialtyRecord::model()->ordered()->findAll();
		}
		*/

		return ApiHealthcare_SpecialtyModel::populateModels($specialtyRecords);
	}

	/**
	 * @param string $specialtyId
	 * @return ApiHealthcare_SpecialtyModel
	 */
	public function getSpecialtyById($specialtyId)
	{
		if (!$specialtyId)
		{
			return false;
		}

		$specialtyRecord = ApiHealthcare_SpecialtyRecord::model()->findById($specialtyId);

		if ($specialtyRecord)
		{
			return ApiHealthcare_SpecialtyModel::populateModel($specialtyRecord);
		}
	}

	/**
	 * @param string $slug
	 * @return string
	 */
	public function getSpecialtyNameBySlug($slug)
	{
		if (!$slug)
		{
			return false;
		}

		$specialtyRecord = ApiHealthcare_SpecialtyRecord::model()->findByAttributes(array(
			'slug' => $slug
		));

		if (!$specialtyRecord)
		{
			throw new Exception('Specialty with slug of ' . $slug . ' not found!');
		}

		return $specialtyRecord->name;
	}

	/**
	 * @return bool
	 */
	public function updateSpecialtyRecords($reset = false)
	{
		$json = $this->_sendRequest('getSpecs');

		if ($json)
		{
			$response = JsonHelper::decode($json);

			if (is_array($response))
			{
				$currentSpecIds = array();

				foreach ($response as $item)
				{
					$specialty = new ApiHealthcare_SpecialtyModel();
					
					$specialty->specId    = $item['specId'];
					$specialty->name      = $item['specName'];
					$specialty->slug      = ElementHelper::createSlug($specialty->name);

					$currentSpecIds[] = $specialty->specId;

					$specialtyRecord = ApiHealthcare_SpecialtyRecord::model()->findByAttributes(array(
						'specId' => $specialty->specId
					));

					if (!$specialtyRecord || $reset)
					{
						$specialtyRecord = $specialtyRecord ? $specialtyRecord : new ApiHealthcare_SpecialtyRecord();

						$specialtyRecord->slug  = $specialty->slug;
						$specialtyRecord->name  = $specialty->name;
					}

					$specialtyRecord->specId = $specialty->specId;

					$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
					try
					{
						$specialtyRecord->save();

						if ($transaction !== null) { $transaction->commit(); }
					}
					catch (\Exception $e)
					{
						if ($transaction !== null) { $transaction->rollback; }

						throw $e;
					}
				}

				// Delete unused Specialties
				$this->deleteUnusedSpecialties($currentSpecIds);

				return true;
			}

			return false;
		}
	}

	/**
	 * @param ApiHealthcare_SpecialtyModel $specialty
	 */
	public function saveSpecialty(ApiHealthcare_SpecialtyModel $specialty)
	{
		// get record if exists
		if ($specialty->id)
		{
			$specialtyRecord = ApiHealthcare_SpecialtyRecord::model()->findById($specialty->id);

			if (!$specialtyRecord)
			{
				throw new Exception(Craft::t('No Specialty exists with the ID "{id}"', array('id' => $specialty->id)));
			}
		}
		// else create new specialty
		else
		{
			$specialtyRecord = new ApiHealthcare_SpecialtyRecord();
		}

		// set the specialty's attributes
		$specialtyRecord->specId    = $specialty->specId;
		$specialtyRecord->name      = $specialty->name;
		$specialtyRecord->slug      = $specialty->slug;
		$specialtyRecord->show      = (bool) $specialty->show;

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// validate the specialty
			$specialtyRecord->validate();
			$specialty->addErrors($specialtyRecord->getErrors());

			if (!$specialty->hasErrors())
			{
				// save without running validation again
				$specialtyRecord->save(false);

				if ($transaction !== null) { $transaction->commit(); }

				$specialty->id = $specialtyRecord->id;

				return true;
			}
			else
			{
				if ($transaction !== null) { $transaction->rollback(); }

				return false;
			}
		}
		catch (\Exception $e)
		{
			if ($transaction !== null) { $transaction->rollback(); }

			throw $e;
		}
	}

	/**
	 * @param array $currentSpecIds
	 */
	public function deleteUnusedSpecialties($currentSpecIds)
	{
		if (!$currentSpecIds || !is_array($currentSpecIds))
		{
			return false;
		}

		$specialtyRecords = ApiHealthcare_SpecialtyRecord::model()->findAll();
		$specialtyModels  = ApiHealthcare_SpecialtyModel::populateModels($specialtyRecords);

		if (!$specialtyRecords)
		{
			return false;
		}

		foreach($specialtyModels as $specialtyModel)
		{
			if (!in_array($specialtyModel->specId, $currentSpecIds))
			{
				$this->deleteSpecialty($specialtyModel);
			}
		}
	}

	/**
	 * @param number $id
	 */
	public function deleteSpecialty(ApiHealthcare_SpecialtyModel $specialty)
	{
		if (!$specialty)
		{
			return false;
		}

		$specialtyRecord = ApiHealthcare_SpecialtyRecord::model()->findById($specialty->id);

		return $specialtyRecord->delete();
	}





	/**
	 * @return array
	 */
	public function getAllPerDiemClients()
	{
		$perDiemClientRecords = ApiHealthcare_PerDiemClientRecord::model()->ordered()->findAll();

		if ($perDiemClientRecords)
		{
			return ApiHealthcare_PerDiemClientModel::populateModels($perDiemClientRecords);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param string $perDiemClientId
	 * @return ApiHealthcare_PerDiemClientModel
	 */
	public function getPerDiemClientById($perDiemClientId)
	{
		if (!$perDiemClientId)
		{
			return false;
		}

		$perDiemClientRecord = ApiHealthcare_PerDiemClientRecord::model()->findById($perDiemClientId);

		if ($perDiemClientRecord)
		{
			return ApiHealthcare_PerDiemClientModel::populateModel($perDiemClientRecord);
		}
	}

	/**
	 * @param string $perDiemClientId
	 */
	public function deletePerDiemClientById($perDiemClientId)
	{
		$perDiemClient = $this->getPerDiemClientById($perDiemClientId);

		if (!$perDiemClientId || !$perDiemClient) { return false; }

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// delete client
			$affectedRows = craft()->db->createCommand()->delete('apihealthcare_perdiemclients', array('id' => $perDiemClientId));

			if ($transaction !== null) { $transaction->commit(); }
			return (bool) $affectedRows;
		}
		catch (\Exception $e)
		{
			if ($transaction !== null) { $transaction->rollback(); }
			
			throw $e;
		}
	}

	/**
	 * @param ApiHealthcare_PerDiemClientModel $perDiemClient
	 */
	public function savePerDiemClient(ApiHealthcare_PerDiemClientModel $perDiemClient)
	{
		// get record if exists
		if ($perDiemClient->id)
		{
			$perDiemClientRecord = ApiHealthcare_PerDiemClientRecord::model()->findById($perDiemClient->id);

			if (!$perDiemClientRecord)
			{
				throw new Exception(Craft::t('No Client exists with the ID "{id}"', array('id' => $perDiemClient->id)));
			}
		}
		// else create new client
		else
		{
			$perDiemClientRecord = new ApiHealthcare_PerDiemClientRecord();
		}

		// set the client's attributes
		$perDiemClientRecord->name     = $perDiemClient->name;
		$perDiemClientRecord->clientId = $perDiemClient->clientId;

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// validate the client
			$perDiemClientRecord->validate();
			$perDiemClient->addErrors($perDiemClientRecord->getErrors());

			if (!$perDiemClient->hasErrors())
			{
				// save without running validation again
				$perDiemClientRecord->save(false);

				if ($transaction !== null) { $transaction->commit(); }

				$perDiemClient->id = $perDiemClientRecord->id;

				return true;
			}
			else
			{
				if ($transaction !== null) { $transaction->rollback(); }

				return false;
			}
		}
		catch (\Exception $e)
		{
			if ($transaction !== null) { $transaction->rollback(); }

			throw $e;
		}
	}
}