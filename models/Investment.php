<?php

declare(strict_types=1);

namespace app\models;

use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Expression;

/**
 * @property int $id
 * @property int $user_id
 * @property string $name
 * @property string $type
 * @property string|null $ticker
 * @property float|null $quantity
 * @property float|null $last_price
 * @property string|null $price_updated_at
 * @property float $invested_amount
 * @property float $current_value
 * @property string $currency
 * @property string|null $purchase_date
 * @property string|null $note
 * @property string|null $created_at
 * @property string|null $updated_at
 *
 * @property User $user
 */
final class Investment extends ActiveRecord
{
    public const TYPE_STOCKS = 'stocks';
    public const TYPE_CRYPTO = 'crypto';
    public const TYPE_DEPOSIT = 'deposit';
    public const TYPE_REALESTATE = 'realestate';
    public const TYPE_BONDS = 'bonds';
    public const TYPE_FUND = 'fund';
    public const TYPE_OTHER = 'other';

    public static function tableName(): string
    {
        return 'investment';
    }

    public function behaviors(): array
    {
        return [
            'timestamp' => [
                'class' => TimestampBehavior::class,
                'value' => new Expression('NOW()'),
            ],
        ];
    }

    public function rules(): array
    {
        return [
            [['user_id', 'name', 'type'], 'required'],
            [['user_id'], 'integer'],
            [['invested_amount', 'current_value', 'quantity', 'last_price'], 'number', 'min' => 0],
            [['name'], 'string', 'max' => 255],
            [['note'], 'string', 'max' => 500],
            [['ticker'], 'string', 'max' => 20],
            [['currency'], 'string', 'max' => 3],
            [['purchase_date', 'price_updated_at'], 'safe'],
            [['type'], 'in', 'range' => array_keys(self::getTypes())],
            [['user_id'], 'exist', 'targetClass' => User::class, 'targetAttribute' => 'id'],
        ];
    }

    /** True if this asset can auto-fetch its price (crypto / stocks with a ticker). */
    public function supportsLivePrice(): bool
    {
        return !empty($this->ticker) && in_array($this->type, [
            self::TYPE_CRYPTO, self::TYPE_STOCKS, self::TYPE_FUND, self::TYPE_BONDS,
        ], true);
    }

    public static function getTypes(): array
    {
        return [
            self::TYPE_STOCKS => 'Акции',
            self::TYPE_CRYPTO => 'Криптовалюта',
            self::TYPE_DEPOSIT => 'Вклад',
            self::TYPE_REALESTATE => 'Недвижимость',
            self::TYPE_BONDS => 'Облигации',
            self::TYPE_FUND => 'Фонд / ETF',
            self::TYPE_OTHER => 'Другое',
        ];
    }

    public function getTypeLabel(): string
    {
        return self::getTypes()[$this->type] ?? $this->type;
    }

    public function getTypeIcon(): string
    {
        return match ($this->type) {
            self::TYPE_STOCKS => 'fa-chart-line',
            self::TYPE_CRYPTO => 'fa-bitcoin-sign',
            self::TYPE_DEPOSIT => 'fa-piggy-bank',
            self::TYPE_REALESTATE => 'fa-house',
            self::TYPE_BONDS => 'fa-file-invoice-dollar',
            self::TYPE_FUND => 'fa-layer-group',
            default => 'fa-coins',
        };
    }

    /** Absolute profit/loss in the asset currency. */
    public function getProfit(): float
    {
        return (float)$this->current_value - (float)$this->invested_amount;
    }

    /** Profit/loss as percent of invested amount. */
    public function getProfitPercent(): float
    {
        $invested = (float)$this->invested_amount;
        if ($invested <= 0) {
            return 0;
        }
        return round(($this->getProfit() / $invested) * 100, 2);
    }

    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['id' => 'user_id']);
    }
}
