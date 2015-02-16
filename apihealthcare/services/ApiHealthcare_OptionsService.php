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

		if (!$professionRecords && $this->updateProfessionRecords())
		{
			$professionRecords = ApiHealthcare_ProfessionRecord::model()->ordered()->findAll();
		}

		return ApiHealthcare_ProfessionModel::populateModels($professionRecords);
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
	 * @return bool
	 */
	public function updateProfessionRecords()
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

					if (!$professionRecord)
					{
						$professionRecord = new ApiHealthcare_ProfessionRecord();
					}

					$professionRecord->certId    = $profession->certId;
					$professionRecord->slug      = $profession->slug;
					$professionRecord->name      = $profession->name;
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
	 * @param number $id
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

		if (!$specialtyRecords && $this->updateSpecialtyRecords())
		{
			$specialtyRecords = ApiHealthcare_SpecialtyRecord::model()->ordered()->findAll();
		}

		return ApiHealthcare_SpecialtyModel::populateModels($specialtyRecords);
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
	public function updateSpecialtyRecords()
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

					if (!$specialtyRecord)
					{
						$specialtyRecord = new ApiHealthcare_SpecialtyRecord();
					}

					$specialtyRecord->specId    = $specialty->specId;
					$specialtyRecord->slug      = $specialty->slug;
					$specialtyRecord->name      = $specialty->name;

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
}