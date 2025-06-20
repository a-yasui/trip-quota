<?php

namespace App\Enums;

enum TimezoneEnum: string
{
    case ASIA_TOKYO = 'Asia/Tokyo';
    case UTC = 'UTC';
    case AMERICA_NEW_YORK = 'America/New_York';
    case AMERICA_LOS_ANGELES = 'America/Los_Angeles';
    case EUROPE_LONDON = 'Europe/London';
    case EUROPE_PARIS = 'Europe/Paris';
    case ASIA_SEOUL = 'Asia/Seoul';
    case ASIA_HONG_KONG = 'Asia/Hong_Kong';
    case ASIA_SHANGHAI = 'Asia/Shanghai';
    case AUSTRALIA_SYDNEY = 'Australia/Sydney';

    /**
     * タイムゾーンの表示名を取得
     */
    public function label(): string
    {
        return match ($this) {
            self::ASIA_TOKYO => '日本標準時 (JST)',
            self::UTC => '協定世界時 (UTC)',
            self::AMERICA_NEW_YORK => '米国東部時間 (EST/EDT)',
            self::AMERICA_LOS_ANGELES => '米国太平洋時間 (PST/PDT)',
            self::EUROPE_LONDON => '英国時間 (GMT/BST)',
            self::EUROPE_PARIS => '中央ヨーロッパ時間 (CET/CEST)',
            self::ASIA_SEOUL => '韓国標準時 (KST)',
            self::ASIA_HONG_KONG => '香港時間 (HKT)',
            self::ASIA_SHANGHAI => '中国標準時 (CST)',
            self::AUSTRALIA_SYDNEY => 'オーストラリア東部時間 (AEST/AEDT)',
        };
    }

    /**
     * 全てのタイムゾーンをvalue => labelの配列で取得
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $timezone) {
            $options[$timezone->value] = $timezone->label();
        }
        return $options;
    }
}