<?php

declare(strict_types=1);

namespace Kaiju\Mailcheck;

final class EmailSuggestion
{
    public readonly string $fullAddress;

    public function __construct(public readonly string $originalAddress, public readonly string $account, public readonly string $domain)
    {
        $this->fullAddress = $this->account . '@' . $this->domain;
    }
}
