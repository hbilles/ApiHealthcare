<?php
namespace Craft;

class ApiHealthcare_SpecialtiesService extends ApiHealthcare_BaseService
{
	/**
	 * @return array
	 */
	public function getAll()
	{
		$specialtyRecords = ApiHealthcare_SpecialtyRecord::model()->ordered()->findAll();

		return ApiHealthcare_SpecialtyModel::populateModels($specialtyRecords);
	}

	/**
	 * @return array
	 */
	public function getWhitelisted()
	{
		$specialtyRecords = ApiHealthcare_SpecialtyRecord::model()->ordered()->findAllByAttributes(array(
			'show' => true
		));

		if ($specialtyRecords)
		{
			return ApiHealthcare_SpecialtyModel::populateModels($specialtyRecords);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @return array
	 */
	public function getWhitelistedMenuItems()
	{
		$results = array('all' => 'All');
		$whitelistedSpecialties = $this->getWhitelisted();

		foreach ($whitelistedSpecialties as $specialty)
		{
			$results[$specialty->slug] = $specialty->name;
		}

		return $results;
	}

	/**
	 * @return array
	 */
	public function getAvailable()
	{
		$specialtyModels = $this->getWhitelisted();
		$jobs = craft()->apiHealthcare_jobs->getAll();

		$jobSpecialties = array();
		$availableSpecialties = array();

		foreach ($jobs as $job)
		{
			$jobSpecialties[] = $job->specialtySlug;
		}

		foreach ($specialtyModels as $specialty)
		{
			if (in_array($specialty->slug, $jobSpecialties))
			{
				$availableSpecialties[] = $specialty;
			}
		}

		if (count($availableSpecialties) > 0)
		{
			return $availableSpecialties;
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param string $specialtyId
	 * @return ApiHealthcare_SpecialtyModel
	 */
	public function getById($specialtyId)
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
	 * @return comma-delimited string of specialty names
	 */
	public function getWhitelistedNames()
	{
		$specialties = $this->getWhitelisted();

		if ($specialties)
		{
			$count = count($specialties);
			$specialtyNames = '';

			for ($i = 0; $i < $count; ++$i)
			{
				$specialtyNames .= $specialties[$i]->name;

				if ($i + 1 !== $count)
				{
					$specialtyNames .= ',';
				}
			}

			return $specialtyNames;
		}
		else
		{
			return null;
		}
	}

	/**
	 * @param string $slug
	 * @return string
	 */
	public function getNameBySlug($slug)
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
	 * @param string $name
	 * @return string
	 */
	public function getSlugByName($name)
	{
		if (!$name)
		{
			return false;
		}

		$specialtyRecord = ApiHealthcare_SpecialtyRecord::model()->findByAttributes(array(
			'name' => $name
		));

		if (!$specialtyRecord)
		{
			throw new Exception('Specialty with name of ' . $name . ' not found!');
		}

		return $specialtyRecord->slug;
	}

	/**
	 * @return bool
	 */
	public function updateRecords($reset = false)
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
				$this->deleteUnused($currentSpecIds);

				return true;
			}

			return false;
		}
	}

	/**
	 * @param ApiHealthcare_SpecialtyModel $specialty
	 */
	public function save(ApiHealthcare_SpecialtyModel $specialty)
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
	public function deleteUnused($currentSpecIds)
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
				$this->delete($specialtyModel);
			}
		}
	}

	/**
	 * @param number $id
	 */
	public function delete(ApiHealthcare_SpecialtyModel $specialty)
	{
		if (!$specialty)
		{
			return false;
		}

		$specialtyRecord = ApiHealthcare_SpecialtyRecord::model()->findById($specialty->id);

		return $specialtyRecord->delete();
	}
}