<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\Contract\Controllerable;
use Bitrix\Main\Error;
use Bitrix\Main\Type\DateTime;
use Devconsult\Bookshop\BookTable;

class Books extends CBitrixComponent implements Controllerable
{
    private const GRID_ID = 'BOOK_GRID';

    public function configureActions()
    {
        return [
            'addBook' => [
                'prefilters' => [
                    new HttpMethod([HttpMethod::METHOD_POST]),
                    new Csrf(),
                ],
            ],
        ];
    }

    public function addBookAction(string $title, string $author, int $price = 0): ?array
    {
        $title = trim($title);
        $author = trim($author);

        if ($title === '') {
            $this->addError(new Error('Укажите название книги'));
            return null;
        }

        if ($author === '') {
            $this->addError(new Error('Укажите автора'));
            return null;
        }

        if ($price < 0) {
            $this->addError(new Error('Цена не может быть отрицательной'));
            return null;
        }

        global $USER;
        $userId = (is_object($USER) && method_exists($USER, 'GetID')) ? (int)$USER->GetID() : null;

        $result = BookTable::add([
            'TITLE' => $title,
            'AUTHOR' => $author,
            'PRICE' => $price,
            'CREATED_BY' => $userId,
            'UPDATED_BY' => $userId,
            'UPDATED_AT' => new DateTime(),
        ]);

        if (!$result->isSuccess()) {
            foreach ($result->getErrorMessages() as $errorMessage) {
                $this->addError(new Error($errorMessage));
            }

            return null;
        }

        return [
            'id' => $result->getId(),
            'message' => 'Книга успешно добавлена',
        ];
    }

    public function executeComponent()
    {
        $this->arResult['GRID_ID'] = self::GRID_ID;
        $this->arResult['GRID_HEADERS'] = [
            ['id' => 'ID', 'name' => 'ID', 'default' => true],
            ['id' => 'TITLE', 'name' => 'Название', 'default' => true],
            ['id' => 'AUTHOR', 'name' => 'Автор', 'default' => true],
            ['id' => 'PRICE', 'name' => 'Цена', 'default' => true],
            ['id' => 'VERSION', 'name' => 'Версия', 'default' => true],
        ];
        $this->arResult['GRID_ROWS'] = [];

        $books = BookTable::getList([
            'select' => ['ID', 'TITLE', 'AUTHOR', 'PRICE', 'VERSION'],
            'order' => ['ID' => 'DESC'],
        ]);

        while ($book = $books->fetch()) {
            $this->arResult['GRID_ROWS'][] = [
                'id' => (int)$book['ID'],
                'columns' => [
                    'ID' => (int)$book['ID'],
                    'TITLE' => (string)$book['TITLE'],
                    'AUTHOR' => (string)$book['AUTHOR'],
                    'PRICE' => (int)$book['PRICE'],
                    'VERSION' => (int)$book['VERSION'],
                ],
            ];
        }

        $this->includeComponentTemplate();
    }
}