<?php

declare(strict_types=1);

namespace Kaiju\Mailcheck;

final class Mailcheck
{
    /**
     * @var string[]
     */
    private array $domains = Domains::FREQUENTLY_USED_DOMAINS;

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

    private int $domainThreshold;

    private int $secondLevelThreshold;

    private int $topLevelThreshold;

    private string $account = '';

    private string $domain = '';

    private string $secondLevelDomain = '';

    private string $topLevelDomain = '';

    private string $originalAddress = '';

    public function __construct()
    {
        $this->domainThreshold = 2;
        $this->secondLevelThreshold = 2;
        $this->topLevelThreshold = 2;
    }

    /**
     * @return string[]
     */
    public static function splitEmailParts(string $email): array
    {
        if (false !== $lastAtPos = strrpos($email, '@')) {
            return [substr($email, 0, $lastAtPos), substr($email, $lastAtPos + 1)];
        }

        return ['', ''];
    }

    public static function normalizeEmail(string $email): ?string
    {
        $email = mb_strtolower(trim($email));

        // return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : null;
        return $email;
    }

    private function scanEmail(string $email): bool
    {
        $this->originalAddress = self::normalizeEmail($email);
        if (blank($this->originalAddress)) {
            return false;
        }

        [$this->account, $this->domain] = self::splitEmailParts($this->originalAddress);
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

    public static function findClosestDomain(string $domain, array $domains, ?callable $distanceFunction, int $threshold): ?string
    {
        $minDist = 99;
        /** @var : ?string $closest_domain */
        $closestDomain = null;
        if (null === $distanceFunction) {
            $distanceFunction = 'sift4_distance';
        }

        foreach ($domains as $dmn) {
            if (same_string($domain, $dmn)) {
                return $domain;
            }

            $dist = $distanceFunction($domain, $dmn);
            if ($dist < $minDist) {
                $minDist = $dist;
                $closestDomain = $dmn;
            }
        }

        return ($minDist <= $threshold) && (filled($closestDomain)) ? $closestDomain : null;
    }

    public function suggestDomain(string $email, ?callable $distanceFunction = null): ?string
    {
        // If the email is invalid, or a valid 2nd-level + top-level, do not suggest anything.
        if (!$this->scanEmail($email) ||
            (
                in_array($this->topLevelDomain, $this->topLevelDomains, true) &&
                in_array($this->secondLevelDomain, $this->secondLevelDomains, true)
            )
        ) {
            return null;
        }

        $closestDomain = self::findClosestDomain($this->domain, $this->domains, $distanceFunction, $this->domainThreshold);
        if (filled($closestDomain)) {
            if (same_string($closestDomain, $this->domain)) {
                // The email address exactly matches one of the supplied domains; do not return a suggestion.
                return null;
            }

            // The email address closely matches one of the supplied domains; return a suggestion
            return $closestDomain;
        }

        // The email address does not closely match one of the supplied domains
        if (filled($this->domain)) {
            $closestDomain = $this->domain;
            $found = false;

            $closestSecondLevelDomain = self::findClosestDomain($this->secondLevelDomain, $this->secondLevelDomains, $distanceFunction, $this->secondLevelThreshold);
            if (filled($closestSecondLevelDomain) && !same_string($closestSecondLevelDomain, $this->secondLevelDomain)) {
                // The email address may have a misspelled second-level domain; return a suggestion.
                $closestDomain = str_replace($this->secondLevelDomain, $closestSecondLevelDomain, $closestDomain);
                $found = true;
            }

            $closestTopLevelDomain = self::findClosestDomain($this->topLevelDomain, $this->topLevelDomains, $distanceFunction, $this->topLevelThreshold);
            if (filled($closestTopLevelDomain) && !same_string($closestTopLevelDomain, $this->topLevelDomain)) {
                // The email address may have a misspelled top-level domain; return a suggestion.
                // $closestDomain = str_replace($this->topLevelDomain, $closestTopLevelDomain, $closestDomain);
                $closestDomain = preg_replace(sprintf('/%s$/', $this->topLevelDomain), $closestTopLevelDomain, $closestDomain);
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

    public function suggest(string $email): ?EmailSuggestion
    {
        $suggestedDomain = $this->suggestDomain($email);

        return blank($suggestedDomain)
            ? null
            : new EmailSuggestion($this->getOriginalAddress(), $this->getAccount(), $suggestedDomain);
    }

    public function check(string $email): string
    {
        $suggestion = $this->suggest($email);

        return null === $suggestion ? $email : $suggestion->fullAddress;
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

    public function getOriginalAddress(): string
    {
        return $this->originalAddress;
    }

    public function getAccount(): string
    {
        return $this->account;
    }

    public function getDomainThreshold(): int
    {
        return $this->domainThreshold;
    }

    public function setDomainThreshold(int $domainThreshold): void
    {
        $this->domainThreshold = $domainThreshold;
    }

    public function getSecondLevelThreshold(): int
    {
        return $this->secondLevelThreshold;
    }

    public function setSecondLevelThreshold(int $secondLevelThreshold): void
    {
        $this->secondLevelThreshold = $secondLevelThreshold;
    }

    public function getTopLevelThreshold(): int
    {
        return $this->topLevelThreshold;
    }

    public function setTopLevelThreshold(int $topLevelThreshold): void
    {
        $this->topLevelThreshold = $topLevelThreshold;
    }
}
