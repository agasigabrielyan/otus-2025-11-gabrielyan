<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

\CJSCore::RegisterExt('otus_ui_customization', [
    'js' => '/local/practice/ui-customization/js/script.js',
    'rel' => ['core'],
]);

\CJSCore::Init('otus_ui_customization');
