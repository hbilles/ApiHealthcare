<?php
namespace Craft;

class ApiHealthcare_ProfessionModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'          => AttributeType::Number,
			'certId'      => AttributeType::Number,
			'slug'        => AttributeType::Slug,
			'name'        => AttributeType::String,
			'sortOrder'   => AttributeType::Number,
		);
	}
}