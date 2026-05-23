<?php
namespace Otus\TaskManager\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Type\Date;

class PeriodLock extends Controller
{
    public function configureActions(): array
    {
        return [
            'getStatus' => [
                'prefilters' => [
                    new Authentication(),
                    new Csrf()
                ]
            ]
        ];
    }

    public function getStatusAction(string $entityCode = 'milestones')
    {
        $today = new Date();

        return [
            'isLocked' => true,
            'entityCode' => $entityCode,
            'date' => $today->format("d.m.Y"),
        ];
    }

}