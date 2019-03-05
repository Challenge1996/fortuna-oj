<?php  if (!isset($dynamic_format)) exit('No direct script access allowed');

// Here we define the config variables that can be modified by administrators
// Default values are set in static config files
// Remember to modify the language translation config

// currently only 'enum' datatype is supported

$dynamic_format = array (
	'basic_system_config' => array (
		'datatype' => 'group_begin',
	),
		'language' => array (
			'datatype' => 'enum',
			'enum_value' => array (
				'english' => 'english',
				'chinese' => 'chinese'
			)
		),
		'disable_new_user' => array (
			'datatype' => 'enum',
			'enum_value' => array (
				'yes' => true,
				'no' => false
			)
		),
	'basic_system_config_end' => array (
		'datatype' => 'group_end',
	),
	'problemset' => array (
		'datatype' => 'group_begin',
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
		'solution_upload_priviledge' => array (
			'datatype' => 'enum',
			'enum_value' => array (
				'yes' => true,
				'admin' => 'admin',
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
	'problemset_end' => array (
		'datatype' => 'group_end',
	),
	'contest' => array (
		'datatype' => 'group_begin',
	),
		'allow_forum' => array (
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
		),
	'contest_end' => array (
		'datatype' => 'group_end',
	),
	'payment' => array (
		'datatype' => 'group_begin',
	),
		'enable_payment' => array (
			'datatype' => 'enum',
			'enum_value' => array (
				'yes' => true,
				'no' => false
			)
		),
		'payment_auto_finish' => array (
			'datatype' => 'enum',
			'enum_value' => array (
				'yes' => true,
				'no' => false
			)
		),
		'expire_notify_day_num' => array (
			'datatype' => 'input'
		),
	'payment_end' => array (
		'datatype' => 'group_end',
	),
	'misc' => array (
		'datatype' => 'group_begin',
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
	'misc_end' => array (
		'datatype' => 'group_end',
	)
);
