<?php

class OtusCurrencies extends \CBitrixComponent implements \Bitrix\Main\Engine\Contract\Controllerable
{
    public function configureActions()
    {

    }
    public function executeComponent()
    {
        $this->includeComponentTemplate();
    }
}