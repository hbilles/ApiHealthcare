<?php
namespace Craft;

class ApiHealthcare_LocationRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'apihealthcare_locations';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'name'         => array(AttributeType::String, 'required' => true),
			'abbreviation' => array(AttributeType::String, 'required' => true),
			'show'         => AttributeType::Bool
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('name'), 'unique' => true),
			array('columns' => array('abbreviation'), 'unique' => true)
		);
	}

	/**
	 * @return array
	 */
	public function scopes()
	{
		return array(
			'ordered' => array('order' => 'name')
		);
	}
}