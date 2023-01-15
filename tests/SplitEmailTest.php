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
            'account' => $this->mailcheck->getAccount(),
            'domain' => $this->mailcheck->getDomain(),
            'top_level_domain' => $this->mailcheck->getTopLevelDomain(),
            'second_level_domain' => $this->mailcheck->getSecondLevelDomain(),
        ];
    }

    public function testOneLevelDomain(): void
    {
        $actual = $this->getDomainParts('postbox@com');
        $expected = [
            'account' => 'postbox',
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
            'account' => 'test',
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
            'account' => 'test',
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
            'account' => 'test',
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
            'account' => '"foo@bar"',
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
            'account' => 'contains+alias',
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
            'account' => 'contains.period',
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
            'account' => '"contains.and.@.symbols.com"',
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
            'account' => '"()<>[]:;@,\\\"!#$%&\'*+-/=?^_`{}|\ \ \ \ \ ~\ \ \ \ \ \ \ ?\ \ \ \ \ \ \ \ \ \ \ \ ^_`{}|~.a"',
            'domain' => 'example.com',
            'top_level_domain' => 'com',
            'second_level_domain' => 'example',
        ];
        self::assertEquals($expected, $actual);
    }

    public function testTrimSpaces(): void
    {
        $expected = [
            'account' => 'test',
            'domain' => 'example.com',
            'top_level_domain' => 'com',
            'second_level_domain' => 'example',
        ];

        self::assertEquals($expected, $this->getDomainParts(' test@example.com'));

        self::assertEquals($expected, $this->getDomainParts('test@example.com '));

        self::assertEquals($expected, $this->getDomainParts(' test@example.com '));
    }
}
