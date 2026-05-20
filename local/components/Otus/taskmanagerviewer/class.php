<?php if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

class OtusTaskManagerViewer extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{

    public function configureActions()
    {

    }

    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }
}