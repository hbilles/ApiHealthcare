<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m150709_202338_apihealthcare_addedLocationsTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$locationsTable = $this->dbConnection->schema->getTable('{{apihealthcare_locations}}');

		if ($locationsTable === null)
		{
			// Create the craft_apihealthcare_locations table
			craft()->db->createCommand()->createTable('apihealthcare_locations', array(
				'name'         => array('required' => true),
				'abbreviation' => array('required' => true),
				'show'         => array('maxLength' => 1, 'default' => false, 'required' => true, 'column' => 'tinyint', 'unsigned' => true),
			), null, true);

			// Add indexes to craft_apihealthcare_locations
			craft()->db->createCommand()->createIndex('apihealthcare_locations', 'name', true);
			craft()->db->createCommand()->createIndex('apihealthcare_locations', 'abbreviation', true);
		}

		return true;
	}
}
