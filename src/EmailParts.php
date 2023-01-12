<?php

declare(strict_types=1);

namespace Kaiju\Mailcheck;

final class EmailParts
{
    public readonly string $fullAddress;

    public function __construct(public readonly string $account, public readonly string $domain)
    {
        $this->fullAddress = $this->account . '@' . $this->domain;
    }
}
