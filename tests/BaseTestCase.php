<?php

namespace Kaiju\Mailcheck\Tests;

use Kaiju\Mailcheck\Mailcheck;
use PHPUnit\Framework\TestCase;

abstract class BaseTestCase extends TestCase
{
    protected const DOMAINS = [
        'google.com',
        'gmail.com',
        'emaildomain.com',
        'comcast.net',
        'facebook.com',
        'msn.com',
        'gmx.de',
    ];

    protected const SECOND_LEVEL_DOMAINS = [
        'yahoo',
        'hotmail',
        'mail',
        'live',
        'outlook',
        'gmx',
    ];

    protected const TOP_LEVEL_DOMAINS = [
        'co.uk',
        'com',
        'org',
        'info',
        'fr',
    ];

    protected const THRESHOLD = 2;
    protected const DISTANCE_FUNCTION = 'sift3_distance';

    protected Mailcheck $mailcheck;

    protected function setUp(): void
    {
        $this->mailcheck = new Mailcheck();
        $this->mailcheck->setDomains(self::DOMAINS);
        $this->mailcheck->setSecondLevelDomains(self::SECOND_LEVEL_DOMAINS);
        $this->mailcheck->setTopLevelDomains(self::TOP_LEVEL_DOMAINS);
    }
}
