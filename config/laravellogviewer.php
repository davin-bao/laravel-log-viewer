<?php

return array(

    /*
     | if route_prefix = '_tool', then the url is http://<your_domain>/_tool/log
     */
    'route_prefix' => '_log',
    /*
     | default laravel log storage path
     */
    'log_path' => storage_path('logs'),
    /*
     | default date time match string
     | default \[(\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\]
     */
    'datetime_pattern' => '\[(\d{2}:\d{2}:\d{2}\.\d{6})\]',
);