<?php

declare(strict_types=1);

final class FrameHeaders
{
    public static function allowBitrix24(): void
    {
        if (headers_sent()) {
            return;
        }

        header_remove('X-Frame-Options');
        header_remove('Content-Security-Policy');

        header(
            'Content-Security-Policy: frame-ancestors '
            . 'https://*.bitrix24.ru https://*.bitrix24.com https://*.bitrix24.by https://*.bitrix24.ua '
            . 'https://*.bitrix24.kz https://*.bitrix24.fr https://*.bitrix24.de https://*.bitrix24.pl '
            . 'https://*.bitrix24.it https://*.bitrix24.es https://*.bitrix24.com.br https://*.bitrix24.cn '
            . 'https://*.bitrix24.in https://*.bitrix24.co https://*.bitrix24.mx https://*.bitrix24.jp '
            . 'https://*.bitrix24.vn https://*.bitrix24.com.tr https://*.bitrix24.id https://*.bitrix24.my '
            . 'https://*.bitrix24.tw https://*.bitrix24.sa https://*.bitrix24.ae https://*.bitrix24.ca '
            . 'https://*.bitrix24.se https://*.bitrix24.nl https://*.bitrix24.fi https://*.bitrix24.no '
            . 'https://*.bitrix24.dk https://*.bitrix24.at https://*.bitrix24.ch https://*.bitrix24.be '
            . 'https://*.bitrix24.pt https://*.bitrix24.cz https://*.bitrix24.sk https://*.bitrix24.hu '
            . 'https://*.bitrix24.ro https://*.bitrix24.bg https://*.bitrix24.hr https://*.bitrix24.rs '
            . 'https://*.bitrix24.si https://*.bitrix24.lt https://*.bitrix24.lv https://*.bitrix24.ee '
            . 'https://*.bitrix24.gr https://*.bitrix24.ie https://*.bitrix24.co.uk https://*.bitrix24.com.au '
            . 'https://*.bitrix24.nz https://*.bitrix24.sg https://*.bitrix24.hk https://*.bitrix24.ph '
            . 'https://*.bitrix24.co.za https://*.bitrix24.com https://*.bitrix24.ru '
            . 'http://*.bitrix24.ru http://*.bitrix24.com \'self\';'
        );
    }
}
