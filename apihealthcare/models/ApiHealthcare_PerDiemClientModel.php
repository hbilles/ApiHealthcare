<?php
namespace Craft;

class ApiHealthcare_PerDiemClientModel extends BaseModel
{
	protected function defineAttributes()
	{
		return array(
			'id'          => AttributeType::Number,
			'clientId'    => AttributeType::Number,
			'name'        => AttributeType::String
		);
	}
}