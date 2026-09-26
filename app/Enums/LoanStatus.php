<?php

namespace App\Enums;

enum LoanStatus: string
{
    case DRAFT          = 'draft';
    case SUBMITTED      = 'submitted';
    case UNDER_REVIEW   = 'under_review';
    case VERIFIED       = 'verified';
    case APPROVED       = 'approved';
    case REJECTED       = 'rejected';
    case AGREEMENT      = 'agreement';
    case DISBURSED      = 'disbursed';
    case ACTIVE         = 'active';
    case OVERDUE        = 'overdue';
    case NPA            = 'npa';
    case RESTRUCTURED   = 'restructured';
    case FORECLOSED     = 'foreclosed';
    case SETTLED        = 'settled';
    case CLOSED         = 'closed';
    case WRITTEN_OFF    = 'written_off';

    public function label(): string
    {
        return match($this) {
            self::DRAFT         => 'Draft',
            self::SUBMITTED     => 'Submitted',
            self::UNDER_REVIEW  => 'Under Review',
            self::VERIFIED      => 'Verified',
            self::APPROVED      => 'Approved',
            self::REJECTED      => 'Rejected',
            self::AGREEMENT     => 'Agreement Signed',
            self::DISBURSED     => 'Disbursed',
            self::ACTIVE        => 'Active',
            self::OVERDUE       => 'Overdue',
            self::NPA           => 'NPA',
            self::RESTRUCTURED  => 'Restructured',
            self::FORECLOSED    => 'Foreclosed',
            self::SETTLED       => 'Settled',
            self::CLOSED        => 'Closed',
            self::WRITTEN_OFF   => 'Written Off',
        };
    }

    public function color(): string
    {
        return match($this) {
            self::DRAFT         => 'secondary',
            self::SUBMITTED     => 'info',
            self::UNDER_REVIEW  => 'warning',
            self::VERIFIED      => 'primary',
            self::APPROVED      => 'success',
            self::REJECTED      => 'danger',
            self::AGREEMENT     => 'primary',
            self::DISBURSED     => 'success',
            self::ACTIVE        => 'success',
            self::OVERDUE       => 'warning',
            self::NPA           => 'danger',
            self::RESTRUCTURED  => 'warning',
            self::FORECLOSED    => 'info',
            self::SETTLED       => 'secondary',
            self::CLOSED        => 'secondary',
            self::WRITTEN_OFF   => 'dark',
        };
    }

    /** Returns allowed next statuses from current status */
    public function allowedTransitions(): array
    {
        return match($this) {
            self::DRAFT         => [self::SUBMITTED],
            self::SUBMITTED     => [self::UNDER_REVIEW, self::REJECTED],
            self::UNDER_REVIEW  => [self::VERIFIED, self::REJECTED],
            self::VERIFIED      => [self::APPROVED, self::REJECTED],
            self::APPROVED      => [self::AGREEMENT, self::REJECTED],
            self::AGREEMENT     => [self::DISBURSED],
            self::DISBURSED     => [self::ACTIVE],
            self::ACTIVE        => [self::OVERDUE, self::FORECLOSED, self::SETTLED, self::CLOSED],
            self::OVERDUE       => [self::ACTIVE, self::NPA, self::FORECLOSED, self::SETTLED, self::RESTRUCTURED],
            self::NPA           => [self::ACTIVE, self::FORECLOSED, self::SETTLED, self::WRITTEN_OFF, self::RESTRUCTURED],
            self::RESTRUCTURED  => [self::ACTIVE, self::OVERDUE],
            self::FORECLOSED    => [self::CLOSED],
            self::SETTLED       => [self::CLOSED],
            default             => [],
        };
    }

    public function canTransitionTo(self $newStatus): bool
    {
        return in_array($newStatus, $this->allowedTransitions());
    }

    public function isTerminal(): bool
    {
        return in_array($this, [self::CLOSED, self::WRITTEN_OFF, self::REJECTED]);
    }

    public function isActive(): bool
    {
        return in_array($this, [self::ACTIVE, self::OVERDUE, self::NPA, self::RESTRUCTURED]);
    }
}
