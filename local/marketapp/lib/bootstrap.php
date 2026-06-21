<?php

declare(strict_types=1);

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);
define('BX_NO_ACCELERATOR_RESET', true);
define('BX_SECURITY_SKIP_FRAMECHECK', true);

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

require_once __DIR__ . '/AppConfig.php';
require_once __DIR__ . '/TenantAuth.php';
require_once __DIR__ . '/TenantRepository.php';
require_once __DIR__ . '/InstallLog.php';
require_once __DIR__ . '/FrameHeaders.php';
