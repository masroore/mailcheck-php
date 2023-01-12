<?php

declare(strict_types=1);

if (!function_exists('mb_strcasecmp')) {
    function mb_strcasecmp(string $a, string $b, ?string $encoding = null): int
    {
        if (null === $encoding) {
            $encoding = mb_internal_encoding();
        }

        return strcmp(mb_strtoupper($a, $encoding), mb_strtoupper($b, $encoding));
    }
}

if (!function_exists('mb_same_string')) {
    function mb_same_string(string $a, string $b, ?string $encoding = null): bool
    {
        return 0 === mb_strcasecmp($a, $b, $encoding);
    }
}

if (!function_exists('same_string')) {
    function same_string(string $a, string $b): bool
    {
        return 0 === strcasecmp($a, $b);
    }
}

function sift3_distance(string $a, string $b, int $maxOffset = 5): int
{
    $len_a = strlen($a);
    $len_b = strlen($b);

    if ($len_a === 0) {
        return $len_b;
    }

    if ($len_b === 0) {
        return $len_a;
    }

    $c = $offset_a = $offset_b = $lcs = 0;
    while (($c + $offset_a < $len_a) && ($c + $offset_b < $len_b)) {
        if ($a[$c + $offset_a] === $b[$c + $offset_b]) {
            ++$lcs;
        } else {
            $offset_a = $offset_b = 0;
            for ($i = 0; $i < $maxOffset; ++$i) {
                if ($c + $i < $len_a && $a[$c + $i] === $b[$c]) {
                    $offset_a = $i;

                    break;
                }
                if ($c + $i < $len_b && $a[$c] === $b[$c + $i]) {
                    $offset_b = $i;

                    break;
                }
            }
        }
        ++$c;
    }

    return (int) (($len_a + $len_b) / 2) - $lcs;
}

function sift4_distance(string $s1, string $s2, int $maxOffset = 5, int $maxDistance = 0): int
{
    if (!$s1 || !strlen($s1)) {
        if (!$s2) {
            return 0;
        }

        return strlen($s2);
    }
    if (!$s2 || !strlen($s2)) {
        return strlen($s1);
    }
    $l1 = strlen($s1);
    $l2 = strlen($s2);
    $c1 = 0; // cursor for string 1
    $c2 = 0; // cursor for string 2
    $lcss = 0; // largest common subsequence
    $local_cs = 0; // local common substring
    $trans = 0; // number of transpositions ('ab' vs 'ba')
    $offset_arr = []; // offset pair array, for computing the transpositions
    while (($c1 < $l1) && ($c2 < $l2)) {
        if (substr($s1, $c1, 1) == substr($s2, $c2, 1)) {
            ++$local_cs;
            $isTrans = false;
            $i = 0;
            while ($i < count($offset_arr)) { // see if current match is a transposition
                $ofs = $offset_arr[$i];
                if ($c1 <= $ofs['c1'] || $c2 <= $ofs['c2']) {
                    $isTrans = abs($c2 - $c1) >= abs($ofs['c2'] - $ofs['c1']);
                    if ($isTrans) {
                        ++$trans;
                    } else {
                        if (!$ofs['trans']) {
                            $ofs['trans'] = true;
                            ++$trans;
                        }
                    }

                    break;
                }
                if ($c1 > $ofs['c2'] && $c2 > $ofs['c1']) {
                    array_splice($offset_arr, $i, 1);
                } else {
                    ++$i;
                }
            }
            $offset_arr[] = ['c1' => $c1, 'c2' => $c2, 'trans' => $isTrans];
        } else {
            $lcss += $local_cs;
            $local_cs = 0;
            if ($c1 != $c2) {
                $c1 = $c2 = min($c1, $c2); // using min allows the computation of transpositions
            }
            if ($maxDistance) {
                $temporaryDistance = max($c1, $c2) - $lcss + $trans;
                if ($temporaryDistance > $maxDistance) {
                    return $temporaryDistance;
                }
            }

            // if matching characters are found, remove 1 from both cursors (they get incremented at the end of the loop)
            // so that we can have only one code block handling matches
            for ($i = 0; $i < $maxOffset && ($c1 + $i < $l1 || $c2 + $i < $l2); ++$i) {
                if (($c1 + $i < $l1) && (substr($s1, $c1 + $i, 1) == substr($s2, $c2, 1))) {
                    $c1 += $i - 1;
                    --$c2;

                    break;
                }
                if (($c2 + $i < $l2) && (substr($s1, $c1, 1) == substr($s2, $c2 + $i, 1))) {
                    --$c1;
                    $c2 += $i - 1;

                    break;
                }
            }
        }
        ++$c1;
        ++$c2;
        // this covers the case where the last match is on the last token in list, so that it can compute transpositions correctly
        if (($c1 >= $l1) || ($c2 >= $l2)) {
            $lcss += $local_cs;
            $local_cs = 0;
            $c1 = $c2 = min($c1, $c2);
        }
    }
    $lcss += $local_cs;

    return max($l1, $l2) - $lcss + $trans; // apply transposition cost to final result
}
