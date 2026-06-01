<?php

declare(strict_types=1);

namespace NumaxLab\Lunar\Geslib\Jobs;

use NumaxLab\Geslib\Lines\Article;
use NumaxLab\Geslib\Lines\ArticleAuthor;
use NumaxLab\Geslib\Lines\ArticleIndex;
use NumaxLab\Geslib\Lines\ArticleTopic;
use NumaxLab\Geslib\Lines\Author;
use NumaxLab\Geslib\Lines\AuthorBiography;
use NumaxLab\Geslib\Lines\BindingType;
use NumaxLab\Geslib\Lines\CenterStock;
use NumaxLab\Geslib\Lines\Classification;
use NumaxLab\Geslib\Lines\Collection;
use NumaxLab\Geslib\Lines\EBook;
use NumaxLab\Geslib\Lines\Editorial;
use NumaxLab\Geslib\Lines\EditorialReference;
use NumaxLab\Geslib\Lines\Ibic;
use NumaxLab\Geslib\Lines\Language;
use NumaxLab\Geslib\Lines\PressPublication;
use NumaxLab\Geslib\Lines\RecordLabel;
use NumaxLab\Geslib\Lines\Status;
use NumaxLab\Geslib\Lines\Stock;
use NumaxLab\Geslib\Lines\Topic;
use NumaxLab\Geslib\Lines\Type;

/**
 * Maps Geslib line codes to processing phases for initial load strategy.
 *
 * Phase 1 — Base entities (no article dependencies)
 * Phase 2 — Articles and article indexes (depend on phase 1)
 * Phase 3 — Stock and stock-dependent data (depend on phase 2)
 * Phase 4 — Batch relations: author/topic/ibic (depend on phase 2)
 */
final class GeslibInitialPhasePartitioner
{
    /** @var array<string, int> */
    private const array PHASE_MAP = [
        // Phase 1 — base entities
        Editorial::CODE => 1,
        RecordLabel::CODE => 1,
        Collection::CODE => 1,
        Topic::CODE => 1,
        Type::CODE => 1,
        Language::CODE => 1,
        Status::CODE => 1,
        Author::CODE => 1,
        BindingType::CODE => 1,
        EditorialReference::CODE => 1,
        Classification::CODE => 1,
        PressPublication::CODE => 1,

        // Phase 2 — articles
        Article::CODE => 2,
        EBook::CODE => 2,
        ArticleIndex::CODE => 2,

        // Phase 3 — stock and dependent updates
        Stock::CODE => 3,
        CenterStock::CODE => 3,
        AuthorBiography::CODE => 3,

        // Phase 4 — batch relations (processed via ProcessGeslibInterFileBatchLine)
        ArticleAuthor::CODE => 4,
        ArticleTopic::CODE => 4,
        Ibic::CODE => 4,
    ];

    public static function phaseFor(string $code): ?int
    {
        return self::PHASE_MAP[$code] ?? null;
    }

    /**
     * @param  object[]  $lines
     * @return array<int, object[]>
     */
    public static function partition(array $lines): array
    {
        $phases = [1 => [], 2 => [], 3 => [], 4 => []];

        foreach ($lines as $line) {
            $phase = self::phaseFor($line->getCode());

            if ($phase !== null) {
                $phases[$phase][] = $line;
            }
        }

        return $phases;
    }
}
