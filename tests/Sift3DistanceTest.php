<?php

declare(strict_types=1);

namespace Kaiju\Mailcheck\Tests;

use PHPUnit\Framework\TestCase;

final class Sift3DistanceTest extends TestCase
{
    public function testSift3Distance(): void
    {
        self::assertEquals(sift3_distance('boat', 'boot'), 1);
        self::assertEquals(sift3_distance('boat', 'bat'), 1.5);
        self::assertEquals(sift3_distance('ifno', 'info'), 2);
        self::assertEquals(sift3_distance('hotmial', 'hotmail'), 2);
    }
}
