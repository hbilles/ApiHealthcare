<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160406_000000_apihealthcare_addNameIndexToProfessionsSpecialties extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$professionsTable = $this->dbConnection->schema->getTable('{{apihealthcare_professions}}');
		$specialtiesTable = $this->dbConnection->schema->getTable('{{apihealthcare_specialties}}');

		if ($professionsTable)
		{
			// Add name index to craft_apihealthcare_professions
			craft()->db->createCommand()->createIndex('apihealthcare_professions', 'name', true);
		}

		if ($specialtiesTable)
		{
			// Add name index to craft_apihealthcare_specialties
			craft()->db->createCommand()->createIndex('apihealthcare_specialties', 'name', true);
		}

		return true;
	}
}
