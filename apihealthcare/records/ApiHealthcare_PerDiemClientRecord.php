<?php
namespace Craft;

class ApiHealthcare_PerDiemClientRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'apihealthcare_perdiemclients';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'clientId'    => array(AttributeType::Number, 'required' => true),
			'name'        => array(AttributeType::String, 'required' => true)
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('clientId'), 'unique' => true),
		);
	}

	/**
	 * @return array
	 */
	public function scopes()
	{
		return array(
			'ordered' => array('order' => 'clientId')
		);
	}
}
