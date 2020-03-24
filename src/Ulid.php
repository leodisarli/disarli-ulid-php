<?php

namespace Ulid;

use Exception;
use Ulid\UlidConstants;

class Ulid
{
    public $lastGenTime = 0;
    public $lastRandChars = [];

    public function isValidFormat(
        string $ulid
    ): bool {
        $validator = UlidConstants::TIME_LENGTH + UlidConstants::RANDOM_LENGTH;
        if (strlen($ulid) !== $validator) {
            return false;
        }
        return true;
    }

    public function getTimeFromUlid(
        string $ulid
    ): int {
        if (!$this->isValidFormat($ulid)) {
            throw new Exception('Invalid format ULID string.');
        }

        $timePart = substr(
            $ulid,
            0,
            UlidConstants::TIME_LENGTH
        );
        return $this->decodeTime(
            $timePart
        );
    }

    public function getRandomnessFromString(
        string $ulid
    ): string {
        if (!$this->isValidFormat($ulid)) {
            throw new Exception('Invalid format ULID string.');
        }

        return substr(
            $ulid,
            UlidConstants::TIME_LENGTH,
            UlidConstants::RANDOM_LENGTH
        );
    }

    public function isDuplicatedTime(
        int $time
    ): bool {
        if ($time === $this->lastGenTime) {
            return true;
        }
        return false;
    }

    public function hasIncrementLastRandChars(
        bool $duplicateTime
    ): bool {
        if (!$duplicateTime) {
            for ($i = 0; $i < 16; $i++) {
                $this->lastRandChars[$i] = random_int(0, 31);
            }
            return false;
        }

        for ($i = 15; $i >= 0 && $this->lastRandChars[$i] === 31; $i--) {
            $this->lastRandChars[$i] = 0;
        }
        $this->lastRandChars[$i]++;
        return true;
    }

    public function generate(
        int $time = 0
    ): string {
        if (empty($time)) {
            $time = (int) (microtime(true) * 1000);
        }

        $duplicateTime = $this->isDuplicatedTime(
            $time
        );

        $this->lastGenTime = $time;

        $timeChars = '';
        $randChars = '';

        $encodingChars = UlidConstants::CHARS;

        for ($i = 9; $i >= 0; $i--) {
            $mod = $time % UlidConstants::LENGHT;
            $timeChars = $encodingChars[$mod] . $timeChars;
            $time = ($time - $mod) / UlidConstants::LENGHT;
        }

        $this->hasIncrementLastRandChars(
            $duplicateTime
        );

        for ($i = 0; $i < 16; $i++) {
            $randChars .= $encodingChars[$this->lastRandChars[$i]];
        }

        return $timeChars . $randChars;
    }

    public function decodeTime(
        string $timePart
    ): int {
        $timeChars = str_split(
            strrev($timePart)
        );
        $carry = 0;

        foreach ($timeChars as $index => $char) {
            $encodingIndex = strripos(
                UlidConstants::CHARS,
                $char
            );
            if ($encodingIndex === false) {
                throw new Exception('Invalid ULID character: ' . $char);
            }
            $exponential = pow(UlidConstants::LENGHT, $index);
            $carry += ($encodingIndex * $exponential);
        }
        if ($carry > UlidConstants::TIME_MAX) {
            throw new Exception('Invalid ULID string. Timestamp too large');
        }

        return $carry;
    }
}
