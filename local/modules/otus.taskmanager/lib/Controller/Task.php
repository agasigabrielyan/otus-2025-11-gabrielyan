<?php
namespace Otus\TaskManager\Controller;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\Authentication;
use Otus\TaskManager\ORM\TaskUserTable;
use Otus\TaskManager\ORM\TaskLogTable;
use Bitrix\Iblock\Elements\ElementOtustasksTable;
use Bitrix\Main\Type\DateTime;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class Task extends Controller
{
    public function configureActions(): array
    {
        return [
            'add' => [
                'prefilters' => [
                    new Csrf(),
                    new Authentication()
                ],
                'postfilters' => []
            ],
        ];
    }

    public function addAction(array $data)
    {
        $task = ElementOtustasksTable::add([
            'NAME' => $data['NAME'] ?? 'Без названия',
            'PREVIEW_TEXT' => $data['DESCRIPTION'] ?? '',
            'CREATED_BY' => $this->getCurrentUser()->getId(),
        ]);

        if (!$task->isSuccess()) {
            return ['error' => implode(', ', $task->getErrorMessages())];
        }

        $taskId = $task->getId();

        if (!empty($data['USERS']) && is_array($data['USERS'])) {
            foreach ($data['USERS'] as $userId) {
                TaskUserTable::add([
                    'TASK_ID' => $taskId,
                    'USER_ID' => (int)$userId,
                    'ASSIGNED_AT' => new DateTime(),
                ]);
            }
        }

        TaskLogTable::add([
            'TASK_ID' => $taskId,
            'ACTION' => 'Создание задачи',
            'USER_ID' => $this->getCurrentUser()->getId(),
            'CREATED_AT' => new DateTime(),
        ]);

        return [
            'success' => true,
            'taskId' => $taskId,
        ];
    }

    

}