<?php
namespace Craft;

class ApiHealthcare_QueryModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'jobType'        => AttributeType::String,
			'jobTypeSlug'    => AttributeType::Slug,
			'profession'     => AttributeType::String,
			'professionSlug' => AttributeType::Slug,
			'specialty'      => AttributeType::String,
			'specialtySlug'  => AttributeType::Slug,
			'state'          => AttributeType::String,
			'city'           => AttributeType::String,
			'zipCode'        => AttributeType::Number,
			'shiftStart'     => AttributeType::DateTime,
			'dateStart'      => AttributeType::DateTime,
			'status'         => AttributeType::String,
			'jobId'          => AttributeType::Number,
			'clientName'     => AttributeType::String,
			'description'    => AttributeType::String,
		);
	}

	public function getSearchBaseUri()
	{
		$settings = craft()->plugins->getPlugin('apiHealthcare')->getSettings();
		if ($settings->searchBaseUri)
		{
			return $settings->searchBaseUri;
		}
		else
		{
			throw new Exception("searchBaseUri field must be set on the plugin's settings page");
		}
	}
}