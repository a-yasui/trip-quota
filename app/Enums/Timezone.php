<?php

namespace App\Enums;

use DateTimeZone;
use DateTime;

enum Timezone: string
{
    // アジア地域
    case ASIA_TOKYO = 'Asia/Tokyo';
    case ASIA_SEOUL = 'Asia/Seoul';
    case ASIA_SHANGHAI = 'Asia/Shanghai';
    case ASIA_SINGAPORE = 'Asia/Singapore';
    case ASIA_BANGKOK = 'Asia/Bangkok';
    case ASIA_KOLKATA = 'Asia/Kolkata';
    case ASIA_DUBAI = 'Asia/Dubai';
    case ASIA_HONG_KONG = 'Asia/Hong_Kong';
    case ASIA_TAIPEI = 'Asia/Taipei';
    case ASIA_MANILA = 'Asia/Manila';
    case ASIA_KUALA_LUMPUR = 'Asia/Kuala_Lumpur';
    case ASIA_JAKARTA = 'Asia/Jakarta';
    case ASIA_HO_CHI_MINH = 'Asia/Ho_Chi_Minh';

    // ヨーロッパ地域
    case EUROPE_LONDON = 'Europe/London';
    case EUROPE_PARIS = 'Europe/Paris';
    case EUROPE_BERLIN = 'Europe/Berlin';
    case EUROPE_ROME = 'Europe/Rome';
    case EUROPE_MADRID = 'Europe/Madrid';
    case EUROPE_AMSTERDAM = 'Europe/Amsterdam';
    case EUROPE_ZURICH = 'Europe/Zurich';
    case EUROPE_MOSCOW = 'Europe/Moscow';
    case EUROPE_ISTANBUL = 'Europe/Istanbul';

    // 北米地域
    case AMERICA_NEW_YORK = 'America/New_York';
    case AMERICA_LOS_ANGELES = 'America/Los_Angeles';
    case AMERICA_CHICAGO = 'America/Chicago';
    case AMERICA_DENVER = 'America/Denver';
    case AMERICA_TORONTO = 'America/Toronto';
    case AMERICA_VANCOUVER = 'America/Vancouver';
    case AMERICA_MEXICO_CITY = 'America/Mexico_City';

    // 南米地域
    case AMERICA_SAO_PAULO = 'America/Sao_Paulo';
    case AMERICA_BUENOS_AIRES = 'America/Argentina/Buenos_Aires';
    case AMERICA_SANTIAGO = 'America/Santiago';
    case AMERICA_LIMA = 'America/Lima';
    case AMERICA_BOGOTA = 'America/Bogota';

    // オセアニア地域
    case PACIFIC_HONOLULU = 'Pacific/Honolulu';
    case PACIFIC_AUCKLAND = 'Pacific/Auckland';
    case AUSTRALIA_SYDNEY = 'Australia/Sydney';
    case AUSTRALIA_MELBOURNE = 'Australia/Melbourne';
    case AUSTRALIA_PERTH = 'Australia/Perth';
    case AUSTRALIA_BRISBANE = 'Australia/Brisbane';

    // アフリカ地域
    case AFRICA_CAIRO = 'Africa/Cairo';
    case AFRICA_JOHANNESBURG = 'Africa/Johannesburg';
    case AFRICA_NAIROBI = 'Africa/Nairobi';
    case AFRICA_LAGOS = 'Africa/Lagos';
    case AFRICA_CASABLANCA = 'Africa/Casablanca';

    /**
     * タイムゾーンのUTCからの時間差を取得
     */
    public function getUtcOffset(): string
    {
        $timezone = new DateTimeZone($this->value);
        $offset = $timezone->getOffset(new DateTime('now', new DateTimeZone('UTC')));
        
        $hours = abs(intval($offset / 3600));
        $minutes = abs(intval(($offset % 3600) / 60));
        
        $sign = $offset >= 0 ? '+' : '-';
        
        return sprintf('UTC%s%02d:%02d', $sign, $hours, $minutes);
    }

    /**
     * タイムゾーンの表示名を取得
     */
    public function label(): string
    {
        $utcOffset = $this->getUtcOffset();
        
        $name = match($this) {
            // アジア地域
            self::ASIA_TOKYO => '日本時間',
            self::ASIA_SEOUL => '韓国時間',
            self::ASIA_SHANGHAI => '中国時間',
            self::ASIA_SINGAPORE => 'シンガポール時間',
            self::ASIA_BANGKOK => 'タイ時間',
            self::ASIA_KOLKATA => 'インド時間',
            self::ASIA_DUBAI => 'ドバイ時間',
            self::ASIA_HONG_KONG => '香港時間',
            self::ASIA_TAIPEI => '台湾時間',
            self::ASIA_MANILA => 'フィリピン時間',
            self::ASIA_KUALA_LUMPUR => 'マレーシア時間',
            self::ASIA_JAKARTA => 'インドネシア時間',
            self::ASIA_HO_CHI_MINH => 'ベトナム時間',

            // ヨーロッパ地域
            self::EUROPE_LONDON => 'イギリス時間',
            self::EUROPE_PARIS => 'フランス時間',
            self::EUROPE_BERLIN => 'ドイツ時間',
            self::EUROPE_ROME => 'イタリア時間',
            self::EUROPE_MADRID => 'スペイン時間',
            self::EUROPE_AMSTERDAM => 'オランダ時間',
            self::EUROPE_ZURICH => 'スイス時間',
            self::EUROPE_MOSCOW => 'ロシア時間',
            self::EUROPE_ISTANBUL => 'トルコ時間',

            // 北米地域
            self::AMERICA_NEW_YORK => 'アメリカ東部時間',
            self::AMERICA_LOS_ANGELES => 'アメリカ西部時間',
            self::AMERICA_CHICAGO => 'アメリカ中部時間',
            self::AMERICA_DENVER => 'アメリカ山岳部時間',
            self::AMERICA_TORONTO => 'カナダ東部時間',
            self::AMERICA_VANCOUVER => 'カナダ西部時間',
            self::AMERICA_MEXICO_CITY => 'メキシコ時間',

            // 南米地域
            self::AMERICA_SAO_PAULO => 'ブラジル時間',
            self::AMERICA_BUENOS_AIRES => 'アルゼンチン時間',
            self::AMERICA_SANTIAGO => 'チリ時間',
            self::AMERICA_LIMA => 'ペルー時間',
            self::AMERICA_BOGOTA => 'コロンビア時間',

            // オセアニア地域
            self::PACIFIC_HONOLULU => 'ハワイ時間',
            self::PACIFIC_AUCKLAND => 'ニュージーランド時間',
            self::AUSTRALIA_SYDNEY => 'オーストラリア東部時間',
            self::AUSTRALIA_MELBOURNE => 'オーストラリア南東部時間',
            self::AUSTRALIA_PERTH => 'オーストラリア西部時間',
            self::AUSTRALIA_BRISBANE => 'オーストラリア東部時間（クイーンズランド）',

            // アフリカ地域
            self::AFRICA_CAIRO => 'エジプト時間',
            self::AFRICA_JOHANNESBURG => '南アフリカ時間',
            self::AFRICA_NAIROBI => 'ケニア時間',
            self::AFRICA_LAGOS => 'ナイジェリア時間',
            self::AFRICA_CASABLANCA => 'モロッコ時間',
        };
        
        return sprintf('%s (%s, %s)', $name, $this->value, $utcOffset);
    }

    /**
     * すべてのタイムゾーンを取得
     */
    public static function all(): array
    {
        return self::cases();
    }

    /**
     * すべてのタイムゾーンの値を取得
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * すべてのタイムゾーンを [value => label] の形式で取得
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }
        return $options;
    }

    /**
     * 地域ごとにグループ化されたタイムゾーンを取得
     */
    public static function grouped(): array
    {
        return [
            'アジア' => self::getAsiaTimezones(),
            'ヨーロッパ' => self::getEuropeTimezones(),
            '北米' => self::getNorthAmericaTimezones(),
            '南米' => self::getSouthAmericaTimezones(),
            'オセアニア' => self::getOceaniaTimezones(),
            'アフリカ' => self::getAfricaTimezones(),
        ];
    }

    /**
     * アジア地域のタイムゾーンを取得
     */
    public static function getAsiaTimezones(): array
    {
        $timezones = [];
        foreach (self::cases() as $case) {
            if (str_starts_with($case->value, 'Asia/')) {
                $timezones[$case->value] = $case->label();
            }
        }
        return $timezones;
    }

    /**
     * ヨーロッパ地域のタイムゾーンを取得
     */
    public static function getEuropeTimezones(): array
    {
        $timezones = [];
        foreach (self::cases() as $case) {
            if (str_starts_with($case->value, 'Europe/')) {
                $timezones[$case->value] = $case->label();
            }
        }
        return $timezones;
    }

    /**
     * 北米地域のタイムゾーンを取得
     */
    public static function getNorthAmericaTimezones(): array
    {
        $northAmerica = ['America/New_York', 'America/Los_Angeles', 'America/Chicago', 
                         'America/Denver', 'America/Toronto', 'America/Vancouver', 
                         'America/Mexico_City'];
        
        $timezones = [];
        foreach (self::cases() as $case) {
            if (in_array($case->value, $northAmerica)) {
                $timezones[$case->value] = $case->label();
            }
        }
        return $timezones;
    }

    /**
     * 南米地域のタイムゾーンを取得
     */
    public static function getSouthAmericaTimezones(): array
    {
        $southAmerica = ['America/Sao_Paulo', 'America/Argentina/Buenos_Aires', 
                         'America/Santiago', 'America/Lima', 'America/Bogota'];
        
        $timezones = [];
        foreach (self::cases() as $case) {
            if (in_array($case->value, $southAmerica)) {
                $timezones[$case->value] = $case->label();
            }
        }
        return $timezones;
    }

    /**
     * オセアニア地域のタイムゾーンを取得
     */
    public static function getOceaniaTimezones(): array
    {
        $timezones = [];
        foreach (self::cases() as $case) {
            if (str_starts_with($case->value, 'Pacific/') || 
                str_starts_with($case->value, 'Australia/')) {
                $timezones[$case->value] = $case->label();
            }
        }
        return $timezones;
    }

    /**
     * アフリカ地域のタイムゾーンを取得
     */
    public static function getAfricaTimezones(): array
    {
        $timezones = [];
        foreach (self::cases() as $case) {
            if (str_starts_with($case->value, 'Africa/')) {
                $timezones[$case->value] = $case->label();
            }
        }
        return $timezones;
    }
}
