<?php

namespace app\components;

use DateInterval;
use DateTimeImmutable;
use DateTimeZone;

class TimeHelper
{
    public const TIMEZONE = 'Asia/Jakarta';
    public const DATETIME_FORMAT = 'Y-m-d H:i:s';
    public const DATE_FORMAT = 'Y-m-d';
    public const TIME_FORMAT = 'H:i:s';

    public static function timezone(): DateTimeZone
    {
        return new DateTimeZone(self::TIMEZONE);
    }

    public static function now(): string
    {
        return self::dateTime()->format(self::DATETIME_FORMAT);
    }

    public static function date(): string
    {
        return self::dateTime()->format(self::DATE_FORMAT);
    }

    public static function time(): string
    {
        return self::dateTime()->format(self::TIME_FORMAT);
    }

    public static function hourMinute(): string
    {
        return self::dateTime()->format('H:i');
    }

    public static function year(): string
    {
        return self::dateTime()->format('Y');
    }

    public static function month(): string
    {
        return self::dateTime()->format('m');
    }

    public static function compactTimestamp(): string
    {
        return self::dateTime()->format('YmdHis');
    }

    public static function todayStart(): string
    {
        return self::dateTime()->setTime(0, 0, 0)->format(self::DATETIME_FORMAT);
    }

    public static function tomorrowStart(): string
    {
        return self::dateTime()->setTime(0, 0, 0)->add(new DateInterval('P1D'))->format(self::DATETIME_FORMAT);
    }

    public static function addMinutes(int $minutes): string
    {
        return self::dateTime()->add(new DateInterval('PT' . $minutes . 'M'))->format(self::DATETIME_FORMAT);
    }

    private static function dateTime(): DateTimeImmutable
    {
        return new DateTimeImmutable('now', self::timezone());
    }
}
