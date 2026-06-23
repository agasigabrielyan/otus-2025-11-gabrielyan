<?php

use Bitrix\Main\Loader;

Loader::registerAutoLoadClasses('otus.originalcontactsdata', [
    'Otus\\OriginalContactsData\\OriginalContactsDataTable' => 'lib/originalcontactsdatatable.php',
    'Otus\\OriginalContactsData\\Rest\\OriginalContactsDataRest' => 'lib/rest/originalcontactsdatarest.php',
    'Otus\\OriginalContactsData\\RestLog' => 'lib/restlog.php',
]);
