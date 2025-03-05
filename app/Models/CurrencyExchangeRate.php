<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * 通貨間の為替レートを管理するテーブル。異なる通貨での支出を一貫した通貨で計算するために使用し、レート適用日やデータソースも記録する。
 * 
 *
 * @property int $id
 * @property string $from_currency
 * @property string $to_currency
 * @property numeric $rate
 * @property \Illuminate\Support\Carbon $rate_date
 * @property string|null $source
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate afterDate($date)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate beforeDate($date)
 * @method static \Database\Factories\CurrencyExchangeRateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate forCurrencyPair($fromCurrency, $toCurrency)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate fromSource($source)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate latest()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereFromCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereRateDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereToCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class CurrencyExchangeRate extends Model
{
    /** @use HasFactory<\Database\Factories\CurrencyExchangeRateFactory> */
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'rate_date',
        'source',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'rate' => 'decimal:6',
        'rate_date' => 'date',
    ];

    /**
     * Scope a query to only include rates for a specific currency pair.
     */
    public function scopeForCurrencyPair($query, $fromCurrency, $toCurrency)
    {
        return $query->where('from_currency', $fromCurrency)
            ->where('to_currency', $toCurrency);
    }

    /**
     * Scope a query to only include rates after a given date.
     */
    public function scopeAfterDate($query, $date)
    {
        return $query->where('rate_date', '>=', $date);
    }

    /**
     * Scope a query to only include rates before a given date.
     */
    public function scopeBeforeDate($query, $date)
    {
        return $query->where('rate_date', '<=', $date);
    }

    /**
     * Scope a query to only include rates from a specific source.
     */
    public function scopeFromSource($query, $source)
    {
        return $query->where('source', $source);
    }

    /**
     * Get the latest rate for a currency pair.
     */
    public function scopeLatest($query)
    {
        return $query->orderBy('rate_date', 'desc');
    }
}
