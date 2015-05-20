<?php

openlog('yauj pusher', LOG_PID, LOG_USER);

require 'myjob.php';
require 'vendor/chrisboulton/php-resque/resque.php';
