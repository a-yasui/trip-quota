<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $from_currency
 * @property string $to_currency
 * @property numeric $rate
 * @property \Illuminate\Support\Carbon $effective_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 *
 * @method static \Database\Factories\CurrencyExchangeRateFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereEffectiveDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereFromCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereRate($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereToCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CurrencyExchangeRate whereUpdatedAt($value)
 *
 * @mixin \Eloquent
 */
class CurrencyExchangeRate extends Model
{
    use HasFactory;

    protected $fillable = [
        'from_currency',
        'to_currency',
        'rate',
        'effective_date',
    ];

    protected $casts = [
        'rate' => 'decimal:6',
        'effective_date' => 'date',
    ];
}
