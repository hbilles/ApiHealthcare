<?php
namespace Craft;

class ApiHealthcare_LocationModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'           => AttributeType::Number,
			'name'         => AttributeType::String,
			'abbreviation' => AttributeType::Slug,
			'show'         => AttributeType::Bool
		);
	}
}