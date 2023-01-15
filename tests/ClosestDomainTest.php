<?php

declare(strict_types=1);

namespace Kaiju\Mailcheck\Tests;

use Kaiju\Mailcheck\Mailcheck;

final class ClosestDomainTest extends BaseTestCase
{
    public function testMostSimilarDomains(): void
    {
        self::assertEquals(Mailcheck::findClosestDomain('google.com', self::DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'google.com');
        self::assertEquals(Mailcheck::findClosestDomain('gmail.com', self::DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'gmail.com');
        self::assertEquals(Mailcheck::findClosestDomain('emaildomain.com', self::DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'emaildomain.com');
        self::assertEquals(Mailcheck::findClosestDomain('gmsn.com', self::DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'msn.com');
        self::assertEquals(Mailcheck::findClosestDomain('gmaik.com', self::DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'gmail.com');
    }

    public function testMostSimilarSecondLevelDomains(): void
    {
        self::assertEquals(Mailcheck::findClosestDomain('hotmial', self::SECOND_LEVEL_DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'hotmail');
        self::assertEquals(Mailcheck::findClosestDomain('tahoo', self::SECOND_LEVEL_DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'yahoo');
        self::assertEquals(Mailcheck::findClosestDomain('livr', self::SECOND_LEVEL_DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'live');
        self::assertEquals(Mailcheck::findClosestDomain('outllok', self::SECOND_LEVEL_DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'outlook');
    }

    public function testMostSimilarTopLevelDomains(): void
    {
        self::assertEquals(Mailcheck::findClosestDomain('cmo', self::TOP_LEVEL_DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'com');
        self::assertEquals(Mailcheck::findClosestDomain('ogr', self::TOP_LEVEL_DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'org');
        self::assertEquals(Mailcheck::findClosestDomain('ifno', self::TOP_LEVEL_DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'info');
        self::assertEquals(Mailcheck::findClosestDomain('com.uk', self::TOP_LEVEL_DOMAINS, self::DISTANCE_FUNCTION, self::THRESHOLD), 'co.uk');
    }
}
