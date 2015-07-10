<?php
namespace Craft;

class ApiHealthcare_ProfessionsService extends ApiHealthcare_BaseService
{
	/**
	 * @return array
	 */
	public function getAll()
	{
		$professionRecords = ApiHealthcare_ProfessionRecord::model()->ordered()->findAll();

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
	 * @return array
	 */
	public function getWhitelisted()
	{
		$professionRecords = ApiHealthcare_ProfessionRecord::model()->ordered()->findAllByAttributes(array(
			'show' => true
		));

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
	public function getById($professionId)
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
	public function getNameBySlug($slug)
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
	public function updateRecords($reset = false)
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
				$this->deleteUnused($currentCertIds);

				return true;
			}

			return false;
		}
	}

	/**
	 * @param ApiHealthcare_ProfessionModel $profession
	 */
	public function save(ApiHealthcare_ProfessionModel $profession)
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
	public function deleteById($professionId)
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
	public function deleteUnused($currentCertIds)
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
				$this->delete($professionModel);
			}
		}
	}

	/**
	 * @param ApiHealthcare_ProfessionModel $profession
	 */
	public function delete(ApiHealthcare_ProfessionModel $profession)
	{
		if (!$profession)
		{
			return false;
		}

		$professionRecord = ApiHealthcare_ProfessionRecord::model()->findById($profession->id);

		return $professionRecord->delete();
	}
}