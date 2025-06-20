<?php

namespace App\Enums;

enum TransportationType: string
{
    case WALKING = 'walking';
    case BIKE = 'bike';
    case CAR = 'car';
    case BUS = 'bus';
    case TRAIN = 'train';
    case FERRY = 'ferry';
    case AIRPLANE = 'airplane';

    /**
     * 日本語表示名を取得
     */
    public function label(): string
    {
        return match ($this) {
            self::WALKING => '徒歩',
            self::BIKE => '自転車',
            self::CAR => '車',
            self::BUS => 'バス',
            self::TRAIN => '電車',
            self::FERRY => 'フェリー',
            self::AIRPLANE => '飛行機',
        };
    }

    /**
     * 絵文字アイコンを取得
     */
    public function icon(): string
    {
        return match ($this) {
            self::WALKING => '🚶',
            self::BIKE => '🚲',
            self::CAR => '🚗',
            self::BUS => '🚌',
            self::TRAIN => '🚆',
            self::FERRY => '⛴️',
            self::AIRPLANE => '✈️',
        };
    }

    /**
     * 詳細フィールドが必要かどうか
     */
    public function requiresDetails(): bool
    {
        return match ($this) {
            self::AIRPLANE, self::TRAIN, self::BUS, self::FERRY => true,
            default => false,
        };
    }

    /**
     * 必須フィールドを取得
     */
    public function getRequiredFields(): array
    {
        return match ($this) {
            self::AIRPLANE => ['airline', 'flight_number'],
            self::TRAIN => ['train_line'],
            self::BUS, self::FERRY => [],
            default => [],
        };
    }

    /**
     * オプションフィールドを取得
     */
    public function getOptionalFields(): array
    {
        return match ($this) {
            self::AIRPLANE => ['departure_airport', 'arrival_airport'],
            self::TRAIN => ['train_type', 'departure_station', 'arrival_station'],
            self::BUS, self::FERRY => ['company', 'departure_terminal', 'arrival_terminal'],
            default => [],
        };
    }

    /**
     * 全ての値を配列で取得
     */
    public static function values(): array
    {
        return array_map(fn (TransportationType $case) => $case->value, self::cases());
    }

    /**
     * 選択肢配列を取得（フォーム用）
     */
    public static function options(): array
    {
        $options = [];
        foreach (self::cases() as $case) {
            $options[$case->value] = $case->label();
        }

        return $options;
    }
}
