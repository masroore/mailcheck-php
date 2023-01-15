<?php

declare(strict_types=1);

namespace Kaiju\Mailcheck\Tests;

final class SplitEmailTest extends BaseTestCase
{
    /**
     * @return string[]
     */
    private function getDomainParts(string $email): array
    {
        $this->mailcheck->suggestDomain($email);

        return [
            'address' => $this->mailcheck->getAccount(),
            'domain' => $this->mailcheck->getDomain(),
            'top_level_domain' => $this->mailcheck->getTopLevelDomain(),
            'second_level_domain' => $this->mailcheck->getSecondLevelDomain(),
        ];
    }

    public function testOneLevelDomain(): void
    {
        $actual = $this->getDomainParts('postbox@com');
        $expected = [
            'address' => 'postbox',
            'domain' => 'com',
            'top_level_domain' => 'com',
            'second_level_domain' => '',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testTwoLevelDomain(): void
    {
        $actual = $this->getDomainParts('test@example.com');
        $expected = [
            'address' => 'test',
            'domain' => 'example.com',
            'top_level_domain' => 'com',
            'second_level_domain' => 'example',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testThreeLevelDomain(): void
    {
        $actual = $this->getDomainParts('test@example.co.uk');
        $expected = [
            'address' => 'test',
            'domain' => 'example.co.uk',
            'top_level_domain' => 'co.uk',
            'second_level_domain' => 'example',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testFourLevelDomain(): void
    {
        $actual = $this->getDomainParts('test@mail.randomsmallcompany.co.uk');
        $expected = [
            'address' => 'test',
            'domain' => 'mail.randomsmallcompany.co.uk',
            'top_level_domain' => 'randomsmallcompany.co.uk',
            'second_level_domain' => 'mail',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testRfcCompliant(): void
    {
        $actual = $this->getDomainParts('"foo@bar"@example.com');
        $expected = [
            'address' => '"foo@bar"',
            'domain' => 'example.com',
            'top_level_domain' => 'com',
            'second_level_domain' => 'example',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testNotRfcCompliant(): void
    {
        self::assertNull($this->mailcheck->suggestDomain('example.com'));
        self::assertNull($this->mailcheck->suggestDomain('abc.example.com'));
        self::assertNull($this->mailcheck->suggestDomain('@example.com'));
        self::assertNull($this->mailcheck->suggestDomain('test@'));
    }

    public function testContainsAlias(): void
    {
        $actual = $this->getDomainParts('contains+alias@example.com');
        $expected = [
            'address' => 'contains+alias',
            'domain' => 'example.com',
            'top_level_domain' => 'com',
            'second_level_domain' => 'example',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testContainsPeriod(): void
    {
        $actual = $this->getDomainParts('contains.period@example.com');
        $expected = [
            'address' => 'contains.period',
            'domain' => 'example.com',
            'top_level_domain' => 'com',
            'second_level_domain' => 'example',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testContainsPeriodAtSign(): void
    {
        $actual = $this->getDomainParts('"contains.and.@.symbols.com"@example.com');
        $expected = [
            'address' => '"contains.and.@.symbols.com"',
            'domain' => 'example.com',
            'top_level_domain' => 'com',
            'second_level_domain' => 'example',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testContainsAllSymbols(): void
    {
        $actual = $this->getDomainParts('"()<>[]:;@,\\\"!#$%&\'*+-/=?^_`{}|\ \ \ \ \ ~\ \ \ \ \ \ \ ?\ \ \ \ \ \ \ \ \ \ \ \ ^_`{}|~.a"@example.com');
        $expected = [
            'address' => '"()<>[]:;@,\\\"!#$%&\'*+-/=?^_`{}|\ \ \ \ \ ~\ \ \ \ \ \ \ ?\ \ \ \ \ \ \ \ \ \ \ \ ^_`{}|~.a"',
            'domain' => 'example.com',
            'top_level_domain' => 'com',
            'second_level_domain' => 'example',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testTrimSpaces(): void
    {
        $expected = [
            'address' => 'test',
            'domain' => 'example.com',
            'top_level_domain' => 'com',
            'second_level_domain' => 'example',
        ];

        self::assertEquals($expected, $this->getDomainParts(' test@example.com'));

        self::assertEquals($expected, $this->getDomainParts('test@example.com '));

        self::assertEquals($expected, $this->getDomainParts(' test@example.com '));
    }
}
