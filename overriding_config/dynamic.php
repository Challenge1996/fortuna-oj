<?php  if (!isset($dynamic_format)) exit('No direct script access allowed');

// Here we define the config variables that can be modified by administrators
// Default values are set in static config files
// Remember to modify the language translation config

// currently only 'enum' datatype is supported

$dynamic_format = array (
	'language' => array (
		'datatype' => 'enum',
		'enum_value' => array (
			'english' => 'english',
			'chinese' => 'chinese'
		)
	),
	'allow_message' => array (
		'datatype' => 'enum',
		'enum_value' => array (
			'yes' => true,
			'no' => false
		)
	),
	'allow_custom_test' => array (
		'datatype' => 'enum',
		'enum_value' => array (
			'yes' => true,
			'no' => false
		)
	),
	'allow_add_problem' => array (
		'datatype' => 'enum',
		'enum_value' => array (
			'yes' => true,
			'no' => false
		)
	),
	'allow_download_first_wrong' => array (
		'datatype' => 'enum',
		'enum_value' => array (
			'yes' => true,
			'no' => false
		)
	),
	'allow_forum' => array (
		'datatype' => 'enum',
		'enum_value' => array (
			'yes' => true,
			'no' => false
		)
	),
	'allow_normal_user_public' => array (
		'datatype' => 'enum',
		'enum_value' => array (
			'yes' => true,
			'no' => false,
			'default_public' => 'default_public'
		)
	),
	'disable_new_user' => array (
		'datatype' => 'enum',
		'enum_value' => array (
			'yes' => true,
			'no' => false
		)
	),
	'estimate_score' => array (
		'datatype' => 'enum',
		'enum_value' => array (
			'yes' => true,
			'no' => false
		)
	)
);
