<?php

namespace Otus\OriginalContactsData\Rest;

use Bitrix\Main\Event;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Rest\RestException;
use CRestServer;
use CRestUtil;
use Otus\OriginalContactsData\OriginalContactsDataTable;
use Otus\OriginalContactsData\RestLog;

Loc::loadMessages(__FILE__);

/**
 * REST handlers for otus.originalcontactsdata scope (Holin webinar).
 */
class OriginalContactsDataRest
{
    public const SCOPE = 'otus.originalcontactsdata';

    /** REST event name for event.bind (PDF: ONAFTEROOCDADD). */
    public const EVENT_REST_NAME = 'onAfterOocdAdd';

    /** Internal Bitrix event fired after successful add. */
    public const EVENT_INTERNAL_NAME = 'onAfterOtusOriginalContactsDataAdd';

    /**
     * Registers scope and method routing (PDF: OnRestServiceBuildDescription).
     *
     * @return array<string, array<string, mixed>>
     */
    public static function onRestServiceBuildDescription(): array
    {
        return [
            self::SCOPE => [
                self::SCOPE . '.add' => [__CLASS__, 'add'],
                self::SCOPE . '.list' => [__CLASS__, 'getList'],
                self::SCOPE . '.update' => [__CLASS__, 'update'],
                self::SCOPE . '.delete' => [__CLASS__, 'delete'],
                CRestUtil::EVENTS => [
                    self::EVENT_REST_NAME => [
                        'main',
                        self::EVENT_INTERNAL_NAME,
                        [__CLASS__, 'prepareEventData'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Formats outbound REST event payload (PDF slide 13).
     *
     * @param array<int, mixed> $arguments
     * @param array<string, mixed> $handler
     * @return array<string, mixed>
     */
    public static function prepareEventData(array $arguments, array $handler): array
    {
        $event = reset($arguments);
        if ($event instanceof Event) {
            return $event->getParameters();
        }

        return is_array($event) ? $event : [];
    }

    /**
     * Creates a record in OriginalContactsDataTable.
     *
     * @param array<string, mixed> $params
     * @param int $navStart
     * @param CRestServer $server
     * @return int
     * @throws RestException
     */
    public static function add(array $params, int $navStart, CRestServer $server): int
    {
        self::ensureModuleLoaded();
        RestLog::write('add.request', ['params' => $params]);

        $fields = self::prepareFields($params);
        if ($fields['ORIGINAL_DATA'] === '') {
            throw new RestException(
                'ORIGINAL_DATA is required.',
                RestException::ERROR_ARGUMENT,
                CRestServer::STATUS_WRONG_REQUEST
            );
        }

        $result = OriginalContactsDataTable::add($fields);
        if ($result->isSuccess()) {
            $id = (int)$result->getId();
            self::sendAfterAddEvent($id, $fields);
            RestLog::write('add.success', ['id' => $id, 'fields' => $fields]);

            return $id;
        }

        RestLog::write('add.error', ['errors' => $result->getErrorMessages()]);
        throw new RestException(
            json_encode($result->getErrorMessages(), JSON_UNESCAPED_UNICODE),
            RestException::ERROR_ARGUMENT,
            CRestServer::STATUS_OK
        );
    }

    /**
     * Returns records from OriginalContactsDataTable.
     *
     * @param array<string, mixed> $params
     * @param int $navStart
     * @param CRestServer $server
     * @return array<int, array<string, mixed>>
     */
    public static function getList(array $params, int $navStart, CRestServer $server): array
    {
        self::ensureModuleLoaded();

        $filter = [];
        if (isset($params['CONTACT_ID']) && (int)$params['CONTACT_ID'] > 0) {
            $filter['=CONTACT_ID'] = (int)$params['CONTACT_ID'];
        }
        if (isset($params['ID']) && (int)$params['ID'] > 0) {
            $filter['=ID'] = (int)$params['ID'];
        }

        $query = [
            'select' => ['ID', 'CONTACT_ID', 'ORIGINAL_DATA', 'SOURCE', 'DATE_CREATE'],
            'order' => ['ID' => 'DESC'],
            'limit' => min(max((int)($params['limit'] ?? 50), 1), 500),
        ];

        if ($filter !== []) {
            $query['filter'] = $filter;
        }

        $rows = [];
        $dbResult = OriginalContactsDataTable::getList($query);
        while ($row = $dbResult->fetch()) {
            $rows[] = $row;
        }

        return $rows;
    }

    /**
     * Updates a record by ID.
     *
     * @param array<string, mixed> $params
     * @param int $navStart
     * @param CRestServer $server
     * @return bool
     * @throws RestException
     */
    public static function update(array $params, int $navStart, CRestServer $server): bool
    {
        self::ensureModuleLoaded();

        $id = (int)($params['ID'] ?? 0);
        if ($id <= 0) {
            throw new RestException(
                'ID is required.',
                RestException::ERROR_ARGUMENT,
                CRestServer::STATUS_WRONG_REQUEST
            );
        }

        $fields = self::prepareFields($params, false);
        if ($fields === []) {
            throw new RestException(
                'No fields to update.',
                RestException::ERROR_ARGUMENT,
                CRestServer::STATUS_WRONG_REQUEST
            );
        }

        $result = OriginalContactsDataTable::update($id, $fields);
        if ($result->isSuccess()) {
            return true;
        }

        throw new RestException(
            json_encode($result->getErrorMessages(), JSON_UNESCAPED_UNICODE),
            RestException::ERROR_ARGUMENT,
            CRestServer::STATUS_OK
        );
    }

    /**
     * Deletes a record by ID.
     *
     * @param array<string, mixed> $params
     * @param int $navStart
     * @param CRestServer $server
     * @return bool
     * @throws RestException
     */
    public static function delete(array $params, int $navStart, CRestServer $server): bool
    {
        self::ensureModuleLoaded();

        $id = (int)($params['ID'] ?? 0);
        if ($id <= 0) {
            throw new RestException(
                'ID is required.',
                RestException::ERROR_ARGUMENT,
                CRestServer::STATUS_WRONG_REQUEST
            );
        }

        $result = OriginalContactsDataTable::delete($id);
        if ($result->isSuccess()) {
            return true;
        }

        throw new RestException(
            json_encode($result->getErrorMessages(), JSON_UNESCAPED_UNICODE),
            RestException::ERROR_ARGUMENT,
            CRestServer::STATUS_OK
        );
    }

    /**
     * @param array<string, mixed> $params
     * @param bool $requireOriginalData
     * @return array<string, mixed>
     */
    private static function prepareFields(array $params, bool $requireOriginalData = true): array
    {
        $fields = [];

        if (array_key_exists('CONTACT_ID', $params)) {
            $fields['CONTACT_ID'] = $params['CONTACT_ID'] !== null && $params['CONTACT_ID'] !== ''
                ? (int)$params['CONTACT_ID']
                : null;
        }

        if (array_key_exists('ORIGINAL_DATA', $params)) {
            $fields['ORIGINAL_DATA'] = trim((string)$params['ORIGINAL_DATA']);
        } elseif ($requireOriginalData) {
            $fields['ORIGINAL_DATA'] = '';
        }

        if (array_key_exists('SOURCE', $params)) {
            $fields['SOURCE'] = trim((string)$params['SOURCE']);
        }

        return $fields;
    }

    private static function ensureModuleLoaded(): void
    {
        if (!Loader::includeModule('otus.originalcontactsdata')) {
            throw new RestException(
                'Module otus.originalcontactsdata is not loaded.',
                RestException::ERROR_CORE,
                CRestServer::STATUS_INTERNAL
            );
        }
    }

    /**
     * Fires internal event for outbound REST subscribers (PDF slide 14).
     */
    private static function sendAfterAddEvent(int $id, array $fields): void
    {
        $event = new Event('main', self::EVENT_INTERNAL_NAME, [
            'ID' => $id,
            'FIELDS' => array_merge($fields, ['ID' => $id]),
        ]);
        $event->send();
    }
}
