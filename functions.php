<?php

function info(string $string):void
{
    $color = '1;30';
    $bgColor = '47';

    echo PHP_EOL.getFormatedString($string, $color, $bgColor);
}

function error(string $string):void
{
    $color = '0;37';
    $bgColor = '41';

    echo PHP_EOL.getFormatedString($string, $color, $bgColor);
}

function success(string $string):void
{
    $color = '0;37';
    $bgColor = '42';

    echo PHP_EOL.getFormatedString($string, $color, $bgColor);
}

function getFormatedString(string $string, string $color = '', string $bgColor = ''):string
{
    $responce = '';

    if (!empty($color)) {
        $responce .= "\033[" . $color . 'm';
    }

    if (!empty($bgColor)) {
        $responce .= "\033[" . $bgColor . 'm';
    }

    $responce .= ' '.$string.' ';

    $responce .= "\033[" . '0' . 'm';

    return $responce;
}