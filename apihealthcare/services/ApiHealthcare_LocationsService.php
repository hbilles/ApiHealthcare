<?php
namespace Craft;

class ApiHealthcare_LocationsService extends BaseApplicationComponent
{
	/**
	 * @return array
	 */
	public function getAll()
	{
		$locationRecords = ApiHealthcare_LocationRecord::model()->ordered()->findAll();

		if ($locationRecords)
		{
			return ApiHealthcare_LocationModel::populateModels($locationRecords);
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
		$locationRecords = ApiHealthcare_LocationRecord::model()->ordered()->findAllByAttributes(array(
			'show' => true
		));

		if ($locationRecords)
		{
			return ApiHealthcare_LocationModel::populateModels($locationRecords);
		}
		else
		{
			return false;
		}
	}

	/**
	 * @param string $locationId
	 * @return ApiHealthcare_LocationModel
	 */
	public function getById($locationId)
	{
		if (!$locationId)
		{
			return false;
		}

		$locationRecord = ApiHealthcare_LocationRecord::model()->findById($locationId);

		if ($locationRecord)
		{
			return ApiHealthcare_LocationModel::populateModel($locationRecord);
		}
	}

	/**
	 * @return bool
	 */
	public function populateStates()
	{
		$states = array(
			'AL' => 'Alabama',
			'AK' => 'Alaska',
			'AZ' => 'Arizona',
			'AR' => 'Arkansas',
			'CA' => 'California',
			'CO' => 'Colorado',
			'CT' => 'Connecticut',
			'DE' => 'Delaware',
			'DC' => 'District of Columbia',
			'FL' => 'Florida',
			'GA' => 'Georgia',
			'HI' => 'Hawaii',
			'ID' => 'Idaho',
			'IL' => 'Illinois',
			'IN' => 'Indiana',
			'IA' => 'Iowa',
			'KS' => 'Kansas',
			'KY' => 'Kentucky',
			'LA' => 'Louisiana',
			'ME' => 'Maine',
			'MD' => 'Maryland',
			'MA' => 'Massachusetts',
			'MI' => 'Michigan',
			'MN' => 'Minnesota',
			'MS' => 'Mississippi',
			'MO' => 'Missouri',
			'MT' => 'Montana',
			'NE' => 'Nebraska',
			'NV' => 'Nevada',
			'NH' => 'New Hampshire',
			'NJ' => 'New Jersey',
			'NM' => 'New Mexico',
			'NY' => 'New York',
			'NC' => 'North Carolina',
			'ND' => 'North Dakota',
			'OH' => 'Ohio',
			'OK' => 'Oklahoma',
			'OR' => 'Oregon',
			'PA' => 'Pennsylvania',
			'RI' => 'Rhode Island',
			'SC' => 'South Carolina',
			'SD' => 'South Dakota',
			'TN' => 'Tennessee',
			'TX' => 'Texas',
			'UT' => 'Utah',
			'VT' => 'Vermont',
			'VA' => 'Virginia',
			'WA' => 'Washington',
			'WV' => 'West Virginia',
			'WI' => 'Wisconsin',
			'WY' => 'Wyoming',
		);

		foreach ($states as $locationAbbreviation => $locationName)
		{
			$location = new ApiHealthcare_LocationModel();

			$location->name         = $locationName;
			$location->abbreviation = $locationAbbreviation;
			$location->show         = false;

			// save the state
			if (!$this->save($location))
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param ApiHealthcare_LocationModel $location
	 */
	public function save(ApiHealthcare_LocationModel $location)
	{
		// get record if exists
		if ($location->id)
		{
			$locationRecord = ApiHealthcare_LocationRecord::model()->findById($location->id);

			if (!$locationRecord)
			{
				throw new Exception(Craft::t('No Location exists with the ID "{id}"', array('id' => $location->id)));
			}
		}
		// else create new location
		else
		{
			$locationRecord = new ApiHealthcare_LocationRecord();
		}

		// set the location's attributes
		$locationRecord->name         = $location->name;
		$locationRecord->abbreviation = $location->abbreviation;
		$locationRecord->show         = (bool) $location->show;

		$transaction = craft()->db->getCurrentTransaction() === null ? craft()->db->beginTransaction() : null;
		try
		{
			// validate the location
			$locationRecord->validate();
			$location->addErrors($locationRecord->getErrors());

			if (!$location->hasErrors())
			{
				// save without running validation again
				$locationRecord->save(false);

				if ($transaction !== null) { $transaction->commit(); }

				$location->id = $locationRecord->id;

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
	 * @param string $locationId
	 */
	public function deleteById($locationId)
	{
		if (!$locationId)
		{
			return false;
		}

		$locationRecord = ApiHealthcare_LocationRecord::model()->findById($locationId);
		$locationModel  = ApiHealthcare_LocationModel::populateModel($locationRecord);

		if (!$locationRecord)
		{
			return false;
		}

		return $locationRecord->delete();
	}

	/**
	 * @param ApiHealthcare_LocationModel $location
	 */
	public function delete(ApiHealthcare_LocationModel $location)
	{
		if (!$location)
		{
			return false;
		}

		$locationRecord = ApiHealthcare_LocationRecord::model()->findById($location->id);

		return $locationRecord->delete();
	}
}