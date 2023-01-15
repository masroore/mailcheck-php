<?php

declare(strict_types=1);

namespace Kaiju\Mailcheck\Tests;

use Kaiju\Mailcheck\EmailSuggestion;

final class SuggestTest extends BaseTestCase
{
    private function checkSuggestion(string $email, string $account, string $domain): void
    {
        $expected = new EmailSuggestion($email, $account, $domain);
        $actual = $this->mailcheck->suggest($email);
        self::assertEquals($expected, $actual);
    }

    public function testReturnsValidSuggestion(): void
    {
        $this->checkSuggestion('test@gmail.co', 'test', 'gmail.com');
        $this->checkSuggestion('test@gmailc.om', 'test', 'gmail.com');
        $this->checkSuggestion('test@gnail.con', 'test', 'gmail.com');
        $this->checkSuggestion('test@GNAIL.con', 'test', 'gmail.com');
        $this->checkSuggestion('test@#gmail.com', 'test', 'gmail.com');
        $this->checkSuggestion('test@emaildomain.co', 'test', 'emaildomain.com');
        $this->checkSuggestion('test@emaildomain.co', 'test', 'emaildomain.com');
        $this->checkSuggestion('test@comcast.nry', 'test', 'comcast.net');
        $this->checkSuggestion('test@homail.con', 'test', 'hotmail.com');
    }

    public function testNoSuggestionReturnsNull(): void
    {
        self::assertNull($this->mailcheck->suggest('mark@facebook.com'));
    }

    public function testInvalidEmailReturnsNull(): void
    {
        self::assertNull($this->mailcheck->suggest(''));
        self::assertNull($this->mailcheck->suggest('test@'));
        self::assertNull($this->mailcheck->suggest('test'));
        self::assertNull($this->mailcheck->suggest('@domain.com'));
    }
}
