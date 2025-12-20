<?php
return [
    'exception_handling' => [
        'value' => [
            'debug' => true,
            'ignore_silence' => false,
            'assertion_throws_exception' => true,
            'assertion_error_type' => 256,
            'log' => [
                'class_name' => OtusApp\OtusException\MyCustomFileExceptionHandler::class,
                'settings' => [
                    'file' => 'local/logs/exceptions.log',
                    'log_size' => 10000000,
                ]
            ]
        ],
        'readonly' => false,
    ],
];
