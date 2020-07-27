<?php

namespace App\Service;

use App\Dto\TextInputDto;
use App\Entity\User;
use App\Entity\Word;

/**
 * Text processor.
 */
class TextProcessor
{
    /**
     * Parse text into words.
     *
     * @param TextInputDto $textInput
     * @param User $user
     *
     * @return Word[]
     */
    public function parse(TextInputDto $textInput, User $user): array
    {
        /** @var string[]|false $splitted */
        $splitted = preg_split("/[^\w]*([\s]+[^\w]*|$)/", $textInput->getText(), -1, PREG_SPLIT_NO_EMPTY);

        if (!is_array($splitted)) {
            return [];
        }

        /** @var Word[] $words */
        $words = [];

        foreach ($splitted as $candidate) {
            if (strlen($candidate) < 3) {
                continue;
            }

            $candidate = ucfirst(mb_strtolower($candidate));

            if (!array_key_exists($candidate, $words)) {
                $words[$candidate] = new Word(
                    $candidate,
                    0,
                    $user
                );
            }

            $words[$candidate]->increaseCount(1);
        }

        return $words;
    }
}
