<?php
namespace Craft;

class ApiHealthcare_JobModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'             => AttributeType::Number,
			'jobId'          => AttributeType::Number,
			'jobStatus'      => AttributeType::String,
			'jobType'        => AttributeType::String,
			'jobTypeSlug'    => AttributeType::Slug,
			'profession'     => AttributeType::String,
			'professionSlug' => AttributeType::Slug,
			'specialty'      => AttributeType::String,
			'specialtySlug'  => AttributeType::Slug,
			'state'          => AttributeType::String,
			'city'           => AttributeType::String,
			'zipCode'        => AttributeType::String,
			'dateStart'      => AttributeType::DateTime,
			'isHotJob'       => AttributeType::Bool,
			'description'    => AttributeType::String,
		);
	}
}