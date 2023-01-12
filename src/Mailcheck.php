<?php

declare(strict_types=1);

namespace Kaiju\Mailcheck;

final class Mailcheck
{
    public const DOMAIN_THRESHOLD = 2;
    public const SECOND_LEVEL_THRESHOLD = 2;
    public const TOP_LEVEL_THRESHOLD = 2;

    private string $account = '';

    /**
     * @var string[]
     */
    private array $domains = [
        'aim.com',
        'aol.com',
        'att.net',
        'bellsouth.net',
        'btinternet.com',
        'charter.net',
        'comcast.net',
        'cox.net',
        'earthlink.net',
        'gmail.com',
        'google.com',
        'googlemail.com',
        'icloud.com',
        'mac.com',
        'me.com',
        'msn.com',
        'optonline.net',
        'optusnet.com.au',
        'qq.com',
        'rocketmail.com',
        'rogers.com',
        'sbcglobal.net',
        'shaw.ca',
        'sky.com',
        'sympatico.ca',
        'telus.net',
        'verizon.net',
        'web.de',
        'xtra.co.nz',
        'ymail.com',
    ];

    /**
     * @var string[]
     */
    private array $topLevelDomains = [
        'at',
        'be',
        'biz',
        'ca',
        'ch',
        'co.il',
        'co.jp',
        'co.nz',
        'co.uk',
        'com',
        'com.au',
        'com.tw',
        'cz',
        'de',
        'dk',
        'edu',
        'es',
        'eu',
        'fr',
        'gov',
        'gr',
        'hk',
        'hu',
        'ie',
        'in',
        'info',
        'it',
        'jp',
        'kr',
        'mil',
        'net',
        'net',
        'net.au',
        'nl',
        'no',
        'org',
        'ru',
        'se',
        'sg',
        'us',
    ];

    /**
     * @var string[]
     */
    private array $secondLevelDomains = [
        'gmx',
        'hotmail',
        'live',
        'mail',
        'outlook',
        'yahoo',
    ];

    private string $domain = '';

    private string $secondLevelDomain = '';

    private string $topLevelDomain = '';

    /**
     * @return string[]
     */
    public static function getEmailParts(string $email): array
    {
        if (false !== $lastAtPos = strrpos($email, '@')) {
            return [substr($email, 0, $lastAtPos), substr($email, $lastAtPos + 1)];
        }

        return [null, null];
    }

    private function splitEmail(string $email): bool
    {
        [$this->account, $this->domain] = self::getEmailParts(mb_strtolower(trim($email)));
        if (blank($this->domain) || blank($this->account)) {
            return false;
        }

        $this->topLevelDomain = $this->secondLevelDomain = '';
        $domainParts = explode('.', $this->domain);
        $numDomainParts = count($domainParts);

        if ($numDomainParts === 0) {
            // The address does not have a top-level domain
            return false;
        } elseif ($numDomainParts === 1) {
            // The address has only a top-level domain (valid under RFC)
            $this->topLevelDomain = $domainParts[0];
        } else {
            // The address has a domain and a top-level domain
            $this->secondLevelDomain = $domainParts[0];
            $this->topLevelDomain = implode('.', array_slice($domainParts, 1));
        }

        return true;
    }

    private static function findClosestDomain(string $domain, array $domains, ?callable $distanceFunction = null, int $threshold = self::DOMAIN_THRESHOLD): ?string
    {
        $minDist = 99;
        /** @var : ?string $closest_domain */
        $closestDomain = null;
        $distanceFunction = 'sift4_distance';

        foreach ($domains as $dmn) {
            if (same_string($domain, $dmn)) {
                return $domain;
            }

            // $dist = self::sift3Distance($domain, $dmn);
            $dist = $distanceFunction($domain, $dmn);
            if ($dist < $minDist) {
                $minDist = $dist;
                $closestDomain = $dmn;
            }
        }

        return ($minDist <= $threshold) && (!blank($closestDomain)) ? $closestDomain : null;
    }

    public function suggestDomain(string $email, ?callable $distanceFunction = null): ?string
    {
        // If the email is invalid, or a valid 2nd-level + top-level, do not suggest anything.
        if (!$this->splitEmail($email) ||
            (
                in_array($this->topLevelDomain, $this->topLevelDomains, true) &&
                in_array($this->secondLevelDomain, $this->secondLevelDomains, true)
            )
        ) {
            return null;
        }

        $closestDomain = self::findClosestDomain($this->domain, $this->domains, $distanceFunction, self::DOMAIN_THRESHOLD);
        if (!blank($closestDomain)) {
            if (same_string($closestDomain, $this->domain)) {
                // The email address exactly matches one of the supplied domains; do not return a suggestion.
                return null;
            }

            // The email address closely matches one of the supplied domains; return a suggestion
            return $closestDomain;
        }

        // The email address does not closely match one of the supplied domains
        if (!blank($this->domain)) {
            $closestDomain = $this->domain;
            $found = false;

            $closestSecondLevelDomain = self::findClosestDomain($this->secondLevelDomain, $this->secondLevelDomains, $distanceFunction, self::SECOND_LEVEL_THRESHOLD);
            if (!blank($closestSecondLevelDomain) && !same_string($closestSecondLevelDomain, $this->secondLevelDomain)) {
                // The email address may have a misspelled second-level domain; return a suggestion.
                $closestDomain = str_replace($this->secondLevelDomain, $closestSecondLevelDomain, $closestDomain);
                $found = true;
            }

            $closestTopLevelDomain = self::findClosestDomain($this->topLevelDomain, $this->topLevelDomains, $distanceFunction, self::TOP_LEVEL_THRESHOLD);
            if (!blank($closestTopLevelDomain) && !same_string($closestTopLevelDomain, $this->topLevelDomain)) {
                // The email address may have a misspelled top-level domain; return a suggestion.
                // $closestDomain = str_replace($this->topLevelDomain, $closestTopLevelDomain, $closestDomain);
                $closestDomain = preg_replace($this->topLevelDomain . '$', $closestTopLevelDomain, $closestDomain);
                $found = true;
            }

            if ($found) {
                return $closestDomain;
            }
        }

        /*
         * The email address exactly matches one of the supplied domains, does not closely match any domain and
         * does not appear to simply have a misspelled top-level domain, or is an invalid email address;
         * do not return a suggestion.
        */
        return null;
    }

    public function suggest(string $email): ?EmailParts
    {
        $suggestedDomain = $this->suggestDomain($email);

        return blank($suggestedDomain) ? null : new EmailParts($this->account, $suggestedDomain);
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getSecondLevelDomain(): string
    {
        return $this->secondLevelDomain;
    }

    public function getTopLevelDomain(): string
    {
        return $this->topLevelDomain;
    }

    /**
     * @return string[]
     */
    public function getDomains(): array
    {
        return $this->domains;
    }

    /**
     * @param string[] $domains
     */
    public function setDomains(array $domains): void
    {
        $this->domains = $domains;
    }

    /**
     * @return string[]
     */
    public function getTopLevelDomains(): array
    {
        return $this->topLevelDomains;
    }

    /**
     * @param string[] $domains
     */
    public function setTopLevelDomains(array $domains): void
    {
        $this->topLevelDomains = $domains;
    }

    /**
     * @return string[]
     */
    public function getSecondLevelDomains(): array
    {
        return $this->secondLevelDomains;
    }

    /**
     * @param string[] $domains
     */
    public function setSecondLevelDomains(array $domains): void
    {
        $this->secondLevelDomains = $domains;
    }
}
