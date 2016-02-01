<?php

// EXAMPLE

// This file will not activated until added into the list in
// index.php and application/config/database.php

// $assign_to_config is loaded in index.php

$assign_to_config['foo']					= 'bar';

// $db is loaded in application/config/database.php

$db['default']['foo']						= 'bar'; // in overriding_config/local.php

// End of File : overriding_config/example.php
