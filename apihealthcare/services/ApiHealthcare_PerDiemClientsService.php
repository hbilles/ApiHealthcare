<?php
namespace Craft;

class ApiHealthcare_PerDiemClientsService extends ApiHealthcare_BaseService
{
	/**
	 * @return array
	 */
	public function getAll()
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
	public function getById($perDiemClientId)
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
	 * @return comma-delimited string of clientIds
	 */
	public function getIds()
	{
		$perDiemClients = $this->getAll();

		if ($perDiemClients)
		{
			$count = count($perDiemClients);
			$clientIds = '';

			for ($i = 0; $i < $count; ++$i)
			{
				$clientIds .= $perDiemClients[$i]->clientId;

				if ($i + 1 !== $count)
				{
					$clientIds .= ',';
				}
			}

			return $clientIds;
		}
		else
		{
			return null;
		}
	}

	/**
	 * @param string $perDiemClientId
	 */
	public function deleteById($perDiemClientId)
	{
		$perDiemClient = $this->getById($perDiemClientId);

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
	public function save(ApiHealthcare_PerDiemClientModel $perDiemClient)
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