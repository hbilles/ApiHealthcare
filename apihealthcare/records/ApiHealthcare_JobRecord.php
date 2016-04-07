<?php
namespace Craft;

class ApiHealthcare_JobRecord extends BaseRecord
{
	/**
	 * @return string
	 */
	public function getTableName()
	{
		return 'apihealthcare_jobs';
	}

	/**
	 * @return array
	 */
	protected function defineAttributes()
	{
		return array(
			'jobId'          => array(AttributeType::Number, 'required' => true),
			'jobType'        => array(AttributeType::String, 'required' => true),
			'jobTypeSlug'    => array(AttributeType::Slug, 'required' => true),
			'profession'     => array(AttributeType::String, 'required' => true),
			'professionSlug' => array(AttributeType::Slug, 'required' => true),
			'specialty'      => array(AttributeType::String, 'required' => true),
			'specialtySlug'  => array(AttributeType::Slug, 'required' => true),
			'state'          => array(AttributeType::String, 'required' => true),
			'city'           => array(AttributeType::String),
			'zipCode'        => array(AttributeType::String),
			'dateStart'      => array(AttributeType::DateTime, 'required' => true),
			'isHotJob'       => AttributeType::Bool,
			'description'    => AttributeType::String,
		);
	}

	/**
	 * @return array
	 */
	public function defineIndexes()
	{
		return array(
			array('columns' => array('jobId'), 'unique' => true),
			array('columns' => array('jobTypeSlug'), 'unique' => false),
			array('columns' => array('professionSlug'), 'unique' => false),
			array('columns' => array('specialtySlug'), 'unique' => false),
			array('columns' => array('state'), 'unique' => false),
			array('columns' => array('isHotJob'), 'unique' => false),
		);
	}

	/**
	 * @return array
	 */
	public function scopes()
	{
		return array(
			'ordered' => array('order' => 'dateStart DESC')
		);
	}
}
