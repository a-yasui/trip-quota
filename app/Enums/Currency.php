<?php

namespace App\Enums;

enum Currency: string
{
    case JPY = 'JPY';
    case USD = 'USD';
    case EUR = 'EUR';
    case KRW = 'KRW';
    case CNY = 'CNY';
    case TWD = 'TWD';
    case HKD = 'HKD';
    case THB = 'THB';
    case SGD = 'SGD';

    /**
     * 通貨の表示名を取得
     */
    public function label(): string
    {
        return match ($this) {
            self::JPY => '日本円 (JPY)',
            self::USD => '米ドル (USD)',
            self::EUR => 'ユーロ (EUR)',
            self::KRW => '韓国ウォン (KRW)',
            self::CNY => '中国元 (CNY)',
            self::TWD => '台湾ドル (TWD)',
            self::HKD => '香港ドル (HKD)',
            self::THB => 'タイバーツ (THB)',
            self::SGD => 'シンガポールドル (SGD)',
        };
    }

    /**
     * すべての通貨の選択肢を取得
     */
    public static function options(): array
    {
        return [
            self::JPY->value => self::JPY->label(),
            self::USD->value => self::USD->label(),
            self::EUR->value => self::EUR->label(),
            self::KRW->value => self::KRW->label(),
            self::CNY->value => self::CNY->label(),
            self::TWD->value => self::TWD->label(),
            self::HKD->value => self::HKD->label(),
            self::THB->value => self::THB->label(),
            self::SGD->value => self::SGD->label(),
        ];
    }
}
