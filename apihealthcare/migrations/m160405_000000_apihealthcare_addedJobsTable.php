<?php
namespace Craft;

/**
 * The class name is the UTC timestamp in the format of mYYMMDD_HHMMSS_pluginHandle_migrationName
 */
class m160405_000000_apihealthcare_addedJobsTable extends BaseMigration
{
	/**
	 * Any migration code in here is wrapped inside of a transaction.
	 *
	 * @return bool
	 */
	public function safeUp()
	{
		$jobsTable = $this->dbConnection->schema->getTable('{{apihealthcare_jobs}}');

		if ($jobsTable === null)
		{
			// Create the craft_apihealthcare_jobs table
			craft()->db->createCommand()->createTable('apihealthcare_jobs', array(
				'jobId'          => array(ColumnType::Int, 'required' => true),
				'jobType'        => array(ColumnType::Varchar, 'required' => true),
				'jobTypeSlug'    => array(ColumnType::Varchar, 'required' => true),
				'profession'     => array(ColumnType::Varchar, 'required' => true),
				'professionSlug' => array(ColumnType::Varchar, 'required' => true),
				'specialty'      => array(ColumnType::Varchar, 'required' => true),
				'specialtySlug'  => array(ColumnType::Varchar, 'required' => true),
				'state'          => array(ColumnType::Varchar, 'required' => true),
				'city'           => array(ColumnType::Varchar, 'required' => true),
				'zipCode'        => array(ColumnType::Varchar, 'required' => true),
				'dateStart'      => array(ColumnType::Varchar, 'required' => true),
				'isHotJob'       => array('maxLength' => 1, 'default' => false, 'required' => true, 'column' => 'tinyint', 'unsigned' => true),
				'description'    => array(ColumnType::Text),
			), null, true);

			// Add indexes to craft_apihealthcare_jobs
			craft()->db->createCommand()->createIndex('apihealthcare_jobs', 'jobId', true);
			craft()->db->createCommand()->createIndex('apihealthcare_jobs', 'jobTypeSlug', false);
			craft()->db->createCommand()->createIndex('apihealthcare_jobs', 'professionSlug', false);
			craft()->db->createCommand()->createIndex('apihealthcare_jobs', 'specialtySlug', false);
			craft()->db->createCommand()->createIndex('apihealthcare_jobs', 'state', false);
			craft()->db->createCommand()->createIndex('apihealthcare_jobs', 'isHotJob', false);
		}

		return true;
	}
}
