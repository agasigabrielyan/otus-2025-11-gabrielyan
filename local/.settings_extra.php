<?php
return [
    'exception_handling' => [
        'value' => [
            'debug' => true,

            // какие ошибки обрабатывать в целом
            'handled_errors_types' => E_ALL & ~E_NOTICE & ~E_STRICT & ~E_USER_NOTICE & ~E_DEPRECATED,

            // какие ошибки преобразовывать в исключения
            'exception_errors_types' => E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_USER_WARNING & ~E_USER_NOTICE & ~E_COMPILE_WARNING & ~E_DEPRECATED,

            'ignore_silence' => false,
            'assertion_throws_exception' => true,
            'assertion_error_type' => 256,

            'log' => [
                'class_name' => \OtusApp\OtusException\MyCustomFileExceptionHandler::class,
                'settings' => [
                    'file' => '/local/logs/exceptions.log',
                    'log_size' => 10000000,
                ]
            ]
        ],
        'readonly' => false,
    ],
];
