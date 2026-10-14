<?php

declare(strict_types=1);

namespace Sirix\CsFixerConfig\Fixer;

use PhpCsFixer\AbstractFixer;
use PhpCsFixer\Fixer\WhitespacesAwareFixerInterface;
use PhpCsFixer\Tokenizer\Tokens;
use PhpCsFixer\Tokenizer\TokensAnalyzer;

/**
 * Array formatting adapted from symplify/coding-standard (MIT); see LICENSE.
 *
 * @internal
 */
abstract class AbstractArrayNewlineFixer extends AbstractFixer implements WhitespacesAwareFixerInterface
{
    abstract public static function getRuleName(): string;

    public function getName(): string
    {
        return static::getRuleName();
    }

    public function isCandidate(Tokens $tokens): bool
    {
        return $tokens->isTokenKindFound(T_DOUBLE_ARROW);
    }

    protected function isArray(Tokens $tokens, int $index): bool
    {
        return (new TokensAnalyzer($tokens))->isArray($index);
    }

    /** @return array{start: int, end: int, commas: list<int>, arrows: list<int>} */
    protected function getArrayInfo(Tokens $tokens, int $index): array
    {
        $start  = $tokens[$index]->isGivenKind(T_ARRAY) ? $tokens->getNextMeaningfulToken($index) : $index;
        $block  = Tokens::detectBlockType($tokens[$start]);
        $end    = $tokens->findBlockEnd($block['type'], $start);
        $commas = [];
        $arrows = [];

        for ($i = $start + 1; $i < $end; ++$i) {
            $block = Tokens::detectBlockType($tokens[$i]);

            if (null !== $block && $block['isStart']) {
                $i = $tokens->findBlockEnd($block['type'], $i);

                continue;
            }

            if ($tokens[$i]->equals(',')) {
                $commas[] = $i;
            } elseif ($tokens[$i]->isGivenKind(T_DOUBLE_ARROW)) {
                $arrows[] = $i;
            }
        }

        return [
            'start'  => $start,
            'end'    => $end,
            'commas' => $commas,
            'arrows' => $arrows,
        ];
    }
}
