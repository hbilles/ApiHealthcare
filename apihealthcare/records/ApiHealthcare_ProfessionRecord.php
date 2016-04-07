<?php
namespace Craft;

class ApiHealthcare_ProfessionRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'apihealthcare_professions';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'certId'      => array(AttributeType::Number, 'required' => true),
			'slug'        => array(AttributeType::Slug, 'required' => true),
			'name'        => array(AttributeType::String, 'required' => true),
			'sortOrder'   => array(AttributeType::Number, 'required' => true),
			'show'        => AttributeType::Bool
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('certId'), 'unique' => true),
			array('columns' => array('slug'), 'unique' => true),
			array('columns' => array('name'), 'unique' => true)
		);
	}

	/**
	 * @return array
	 */
	public function scopes()
	{
		return array(
			'ordered' => array('order' => 'sortOrder')
		);
	}
}
