<?php
namespace Craft;

class ApiHealthcare_SpecialtyRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'apihealthcare_specialties';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'specId'      => array(AttributeType::Number, 'required' => true),
			'slug'        => array(AttributeType::Slug, 'required' => true),
			'name'        => array(AttributeType::String, 'required' => true),
			'show'        => AttributeType::Bool
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('specId'), 'unique' => true),
			array('columns' => array('slug'), 'unique' => true),
			array('columns' => array('name'), 'unique' => true),
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