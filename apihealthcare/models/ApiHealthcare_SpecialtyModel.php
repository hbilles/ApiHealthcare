<?php
namespace Craft;

class ApiHealthcare_SpecialtyModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'          => AttributeType::Number,
			'specId'      => AttributeType::Number,
			'slug'        => AttributeType::Slug,
			'name'        => AttributeType::String,
			'show'        => AttributeType::Bool
		);
	}
}