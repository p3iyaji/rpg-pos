<?php

namespace App\Enums;

enum DiscountType: string
{
    case PERCENTAGE = 'percentage';
    case FIXED = 'fixed';
    case BUY_X_GET_Y = 'buy_x_get_y';

    public function label(): string
    {
        return match ($this) {
            self::PERCENTAGE => 'Percentage Discount',
            self::FIXED => 'Fixed Amount Discount',
            self::BUY_X_GET_Y => 'Buy X Get Y Offer',
        };
    }

    public function isPercentageBased(): bool
    {
        return $this === self::PERCENTAGE;
    }

    public function isFixedAmount(): bool
    {
        return $this === self::FIXED;
    }

    public function isBuyXGetY(): bool
    {
        return $this === self::BUY_X_GET_Y;
    }

    public static function toSelectArray(): array
    {
        return [
            self::PERCENTAGE->value => self::PERCENTAGE->label(),
            self::FIXED->value => self::FIXED->label(),
            self::BUY_X_GET_Y->value => self::BUY_X_GET_Y->label(),
        ];
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromValue(string $value): ?self
    {
        return match ($value) {
            self::PERCENTAGE->value => self::PERCENTAGE,
            self::FIXED->value => self::FIXED,
            self::BUY_X_GET_Y->value => self::BUY_X_GET_Y,
            default => null,
        };
    }
}