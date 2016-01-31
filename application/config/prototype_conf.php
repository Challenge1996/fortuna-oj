<?php 

$config['py_theme']['orange'] = array(
		"template" => "orange_template.php",
		'areas' => array(
						"title" => array(
							'type' => 'plain',
							'content' => 'Welcome to Prototype Library'
							),
						"top" => array(
							'type' => 'view',
							'content' => 'areas/top_view' // the view name
							),
						"post",
						"column" => array(
							'type' => 'view',
							'content' => 'areas/column_view' // the view name
							),
						"footer" => array(
							'type' => 'view',
							'content' => 'areas/footer_view' // the view name
							),
					)
);
