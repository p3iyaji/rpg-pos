<?php

namespace App\Enums;

enum OrderStatus: string
{
    case PENDING = 'pending';
    case PROCESSING = 'processing';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case REFUNDED = 'refunded';
    case PARTIALLY_REFUNDED = 'partially_refunded';


    public function label(): string
    {
        return match ($this) {
            self::PENDING => 'Pending',
            self::PROCESSING => 'Processing',
            self::COMPLETED => 'Completed',
            self::CANCELLED => 'Cancelled',
            self::REFUNDED => 'Refunded',
            self::PARTIALLY_REFUNDED => 'Partially_refunded'

        };
    }

    public function isPending(): bool
    {
        return $this === self::PENDING;
    }

    public function isProcessing(): bool
    {
        return $this === self::PROCESSING;
    }

    public function isCompleted(): bool
    {
        return $this === self::COMPLETED;
    }

    public function isCancelled(): bool
    {
        return $this === self::CANCELLED;
    }

    public function isRefunded(): bool
    {
        return $this === self::REFUNDED;
    }

    public function isPartiallyRefunded(): bool
    {
        return $this === self::PARTIALLY_REFUNDED;
    }
    public static function toSelectArray(): array
    {
        return [
            self::PENDING->value => self::PENDING->label(),
            self::PROCESSING->value => self::PROCESSING->label(),
            self::COMPLETED->value => self::COMPLETED->label(),
            self::CANCELLED->value => self::CANCELLED->label(),
            self::REFUNDED->value => self::REFUNDED->label(),
            self::PARTIALLY_REFUNDED->value => self::PARTIALLY_REFUNDED->label(),

        ];
    }

    public function color(): string
    {
        return match ($this) {
            self::PENDING => 'bg-yellow-100 text-yellow-800',
            self::PROCESSING => 'bg-blue-100 text-blue-800',
            self::COMPLETED => 'bg-green-100 text-green-800',
            self::CANCELLED => 'bg-red-100 text-red-800',
            self::REFUNDED => 'bg-purple-100 text-purple-800',
            self::PARTIALLY_REFUNDED => 'bg-red-100 text-red-800',

        };
    }

    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    public static function fromValue(string $value): ?self
    {
        return match ($value) {
            self::PENDING->value => self::PENDING,
            self::PROCESSING->value => self::PROCESSING,
            self::COMPLETED->value => self::COMPLETED,
            self::CANCELLED->value => self::CANCELLED,
            self::REFUNDED->value => self::REFUNDED,
            self::PARTIALLY_REFUNDED => self::PARTIALLY_REFUNDED,
            default => null,
        };
    }
}
