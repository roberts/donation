<?php

namespace App\Enums;

use Filament\Support\Contracts\HasColor;
use Filament\Support\Contracts\HasLabel;

enum TransactionStatus: string implements HasColor, HasLabel
{
    case Succeeded = 'succeeded';
    case Pending = 'pending';
    case Processing = 'processing';
    case Failed = 'failed';
    case Canceled = 'canceled';
    case Refunded = 'refunded';
    case RequiresPaymentMethod = 'requires_payment_method';

    public function getLabel(): string
    {
        return match ($this) {
            self::Succeeded => 'Succeeded',
            self::Pending => 'Pending',
            self::Processing => 'Processing',
            self::Failed => 'Failed',
            self::Canceled => 'Canceled',
            self::Refunded => 'Refunded',
            self::RequiresPaymentMethod => 'Requires Payment Method',
        };
    }

    public function getColor(): string
    {
        return match ($this) {
            self::Succeeded => 'success',
            self::Pending, self::Processing, self::RequiresPaymentMethod => 'warning',
            self::Failed, self::Canceled => 'danger',
            self::Refunded => 'gray',
        };
    }

    public function isSuccessful(): bool
    {
        return $this === self::Succeeded;
    }

    public function label(): string
    {
        return $this->getLabel();
    }
}
