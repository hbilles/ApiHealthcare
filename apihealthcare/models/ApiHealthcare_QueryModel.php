<?php
namespace Craft;

class ApiHealthcare_QueryModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'orderType'   => AttributeType::String,
			'profession'  => AttributeType::String,
			'specialty'   => AttributeType::String,
			'state'       => AttributeType::String,
			'city'        => AttributeType::String,
			'zipCode'     => AttributeType::Number,
			'shiftStart'  => AttributeType::DateTime,
			'status'      => AttributeType::String,
			'jobId'       => AttributeType::Number,
			'clientName'  => AttributeType::String,
			'description' => AttributeType::String,
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