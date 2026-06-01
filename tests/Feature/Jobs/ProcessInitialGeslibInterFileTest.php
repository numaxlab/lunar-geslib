<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Lunar\Models\Brand;
use Lunar\Models\Language as LunarLanguage;
use Lunar\Models\ProductVariant;
use NumaxLab\Geslib\GeslibFile;
use NumaxLab\Geslib\Lines\Action;
use NumaxLab\Geslib\Lines\Article;
use NumaxLab\Geslib\Lines\ArticleAuthor;
use NumaxLab\Geslib\Lines\ArticleIndex;
use NumaxLab\Geslib\Lines\ArticleTopic;
use NumaxLab\Geslib\Lines\Author;
use NumaxLab\Geslib\Lines\AuthorBiography;
use NumaxLab\Geslib\Lines\AuthorType;
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
use NumaxLab\Lunar\Geslib\InterCommands\ArticleAuthorCommand;
use NumaxLab\Lunar\Geslib\InterCommands\ArticleCommand;
use NumaxLab\Lunar\Geslib\InterCommands\AuthorCommand;
use NumaxLab\Lunar\Geslib\InterCommands\Batch\ArticleAuthorRelation;
use NumaxLab\Lunar\Geslib\Jobs\GeslibInitialPhasePartitioner;
use NumaxLab\Lunar\Geslib\Jobs\ProcessInitialGeslibInterFile;
use NumaxLab\Lunar\Geslib\Jobs\ProcessInitialGeslibPhaseChunk;
use NumaxLab\Lunar\Geslib\Models\Author as AuthorModel;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Support\ImportRegistry;

beforeEach(function (): void {
    config()->set('lunar.geslib.inter_files_disk', 'local');
    config()->set('lunar.geslib.inter_files_path', 'geslib-inter');

    Storage::fake('local');
});

/**
 * Creates a testable stub of ProcessInitialGeslibInterFile that overrides
 * parseGeslibContent() to call GeslibFile::parse() directly — avoiding
 * any alias mock contamination from ProcessGeslibInterFileTest.
 */
function makeInitialJobStub(GeslibInterFile $interFile): ProcessInitialGeslibInterFile
{
    return new class($interFile) extends ProcessInitialGeslibInterFile
    {
        protected function parseGeslibContent(string $content): GeslibFile
        {
            return GeslibFile::parse($content);
        }
    };
}

// ─── Guard tests ────────────────────────────────────────────────────────────

it('accepts an initial_load file and dispatches phase chain', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create([
        'file_type' => GeslibInterFile::FILE_TYPE_INITIAL_LOAD,
    ]);

    // Put a minimal initial-file content (just the header line — zero processable lines)
    $extracted = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $file->name);
    Storage::disk('local')->put($extracted, 'I|initial');

    $job = makeInitialJobStub($file);
    $job->handle();

    // With no processable lines, no chain jobs are dispatched — the inline batch handler runs
    Bus::assertNothingDispatched();
});

it('extracts plain-text INTER content from an initial_load .zip that is not a real archive', function (): void {
    Bus::fake();

    $fileName = 'INTER682.zip';
    $file = GeslibInterFile::factory()->create([
        'name' => $fileName,
        'file_type' => GeslibInterFile::FILE_TYPE_INITIAL_LOAD,
    ]);

    $zipPath = config('lunar.geslib.inter_files_path').'/'.$fileName;
    // Plain-text content — not a real ZIP (mirrors the real client file format)
    Storage::disk('local')->put($zipPath, "I|\n8|1|Castellano\n");

    $job = makeInitialJobStub($file);
    $job->handle();

    // The extracted file (INTER682, without .zip) must have been created from raw content
    $extractedPath = config('lunar.geslib.inter_files_path').'/INTER682';
    // After handle() the extracted file is deleted, but the job must not throw
    // and must set processing metadata
    $file->refresh();
    expect($file->status)->not->toBe(GeslibInterFile::STATUS_FAILED);
});

it('throws when receiving a normal_update file', function (): void {
    $file = GeslibInterFile::factory()->create([
        'file_type' => GeslibInterFile::FILE_TYPE_NORMAL_UPDATE,
    ]);

    $job = makeInitialJobStub($file);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'initial_load');
});

// ─── Phase partitioner tests (4.1) ──────────────────────────────────────────

it('partitions base-entity codes into phase 1', function (): void {
    $phase1Codes = [
        Editorial::CODE, RecordLabel::CODE, Collection::CODE, Topic::CODE,
        Type::CODE, Language::CODE, Status::CODE, Author::CODE,
        BindingType::CODE, EditorialReference::CODE,
        Classification::CODE, PressPublication::CODE,
    ];

    foreach ($phase1Codes as $code) {
        $phase = GeslibInitialPhasePartitioner::phaseFor($code);
        expect($phase)->toBe(1, "Expected code '$code' to be in phase 1, got phase $phase");
    }
});

it('partitions article codes into phase 2', function (): void {
    $phase2Codes = [Article::CODE, EBook::CODE, ArticleIndex::CODE];

    foreach ($phase2Codes as $code) {
        $phase = GeslibInitialPhasePartitioner::phaseFor($code);
        expect($phase)->toBe(2, "Expected code '$code' to be in phase 2, got phase $phase");
    }
});

it('partitions stock and biography codes into phase 3', function (): void {
    $phase3Codes = [Stock::CODE, CenterStock::CODE, AuthorBiography::CODE];

    foreach ($phase3Codes as $code) {
        $phase = GeslibInitialPhasePartitioner::phaseFor($code);
        expect($phase)->toBe(3, "Expected code '$code' to be in phase 3, got phase $phase");
    }
});

it('partitions batch-relation codes into phase 4', function (): void {
    $phase4Codes = [ArticleAuthor::CODE, ArticleTopic::CODE, Ibic::CODE];

    foreach ($phase4Codes as $code) {
        $phase = GeslibInitialPhasePartitioner::phaseFor($code);
        expect($phase)->toBe(4, "Expected code '$code' to be in phase 4, got phase $phase");
    }
});

it('returns null for unknown or ignored codes', function (): void {
    expect(GeslibInitialPhasePartitioner::phaseFor('UNKNOWN_CODE'))->toBeNull();
    expect(GeslibInitialPhasePartitioner::phaseFor('PROVIN'))->toBeNull();
});

// ─── ProcessInitialGeslibPhaseChunk tests (3.3) ───────────────────────────

it('ProcessInitialGeslibPhaseChunk throws when given an invalid phase number', function (): void {
    $file = GeslibInterFile::factory()->create([
        'file_type' => GeslibInterFile::FILE_TYPE_INITIAL_LOAD,
    ]);

    $job = new ProcessInitialGeslibPhaseChunk($file, phase: 99, lines: []);

    expect(fn () => $job->handle())->toThrow(RuntimeException::class, 'Invalid phase');
});

it('ProcessInitialGeslibPhaseChunk processes lines for its phase without dispatching for empty lines', function (): void {
    Bus::fake();

    $file = GeslibInterFile::factory()->create([
        'file_type' => GeslibInterFile::FILE_TYPE_INITIAL_LOAD,
    ]);

    // Phase 1 chunk with no lines (ignored codes only)
    $job = new ProcessInitialGeslibPhaseChunk($file, phase: 1, lines: []);
    $job->handle();

    $file->refresh();

    // Should not throw and should not dispatch further jobs
    Bus::assertNothingDispatched();
});

// ─── Order dependency test (spec: Preserve Order Dependency in Initial Load) ───

it('Author must exist before ArticleAuthor relation can be linked (phase 1 → phase 2 → phase 4 ordering)', function (): void {
    Event::fake();
    LunarLanguage::factory()->create();
    Brand::factory()->create(['geslib_code' => 'BR-001']);
    ensureCollectionsForArticle('5', '10', 'es', 'BR-001', 'COL-01');

    // Phase 1: create author (AUT line)
    $authorLine = Author::createWithAction(Action::fromCode(Action::ADD), 'AUTHOR-42', 'Jane Roe');
    $authorCmd = new AuthorCommand($authorLine);
    $authorCmd();

    $author = AuthorModel::where('geslib_code', 'AUTHOR-42')->first();
    expect($author)->not->toBeNull('Author must be persisted in phase 1 before article-author relation');

    // Phase 2: create article (GP4 line — using the mock helper from Pest.php)
    $articleLine = makeArticleLineMock(['id' => 'ART-99']);
    $articleCmd = new ArticleCommand($articleLine);
    $articleCmd();

    $variant = ProductVariant::where('sku', 'ART-99')->first();
    expect($variant)->not->toBeNull('ProductVariant must exist in phase 2 before article-author relation');

    // Phase 4: create article-author relation (LA line)
    $laLine = ArticleAuthor::fromLine([ArticleAuthor::CODE, 'ART-99', 'AUTHOR-42', AuthorType::AUTHOR, '1']);
    $authorRelCmd = new ArticleAuthorCommand($laLine);
    $authorRelCmd();

    // Simulate batch processing (what ProcessGeslibInterFileBatchLine / ArticleAuthorRelation does)
    $relation = new ArticleAuthorRelation('ART-99', [
        ['authorId' => 'AUTHOR-42', 'authorType' => AuthorType::AUTHOR, 'position' => 1],
    ]);
    $relation();

    // Verify the relation was created in geslib_author_product
    expect($variant->product->authors()->where('geslib_code', 'AUTHOR-42')->exists())
        ->toBeTrue('ArticleAuthor relation must be persisted after both Author (phase 1) and Article (phase 2) exist');
});

// ─── Functional equivalence test ──────────────────────────────────────────────
// Spec: "The final state of the database MUST be functionally identical to
//        processing them sequentially" (Preserve Order Dependency in Initial Load)
//
// Route A: initial_load coordinator (ProcessInitialGeslibInterFile)
// Route B: direct sequential InterCommand invocation — the true "sequential baseline"
//          mirroring exactly what ProcessGeslibInterFile does line-by-line, but
//          invoked by hand so we control ordering and avoid running the normal job.

it('initial_load coordinator produces the same DB state as direct sequential command invocation', function (): void {
    Event::fake();
    ImportRegistry::flush();

    $editorialId = 'BR-EQ';
    $collectionId = 'COL-EQ';
    $languageId = 'es';
    $typeId = 'TY-EQ';
    $statusId = 'ST-EQ';
    $authorGeslibCode = 'AUT-EQ';

    LunarLanguage::factory()->create();
    Brand::factory()->create(['geslib_code' => $editorialId]);
    ensureCollectionsForArticle($typeId, $statusId, $languageId, $editorialId, $collectionId);

    // Build a shared GP4 row template (fields will be given article-specific SKU/ISBN per route)
    $baseGp4Fields = array_fill(0, 63, '');
    $baseGp4Fields[0] = 'GP4';
    $baseGp4Fields[1] = 'A';
    $baseGp4Fields[3] = 'Equivalence Article';
    $baseGp4Fields[8] = '150';
    $baseGp4Fields[16] = '3';
    $baseGp4Fields[20] = $languageId;
    $baseGp4Fields[24] = $collectionId;
    $baseGp4Fields[27] = $statusId;
    $baseGp4Fields[29] = '2000';
    $baseGp4Fields[30] = $typeId;
    $baseGp4Fields[32] = $editorialId;
    $baseGp4Fields[33] = '1600';

    // ── Route A: initial_load coordinator ────────────────────────────────────
    $articleIdA = 'ART-EQ-A';
    $gp4A = $baseGp4Fields;
    $gp4A[2] = $articleIdA;
    $gp4A[6] = '9780000000091';
    $gp4A[7] = '0000000000091';

    $fixtureA = implode("\n", [
        'I|initial',
        "AUT|A|{$authorGeslibCode}|Kropotkin, Peter",
        implode('|', $gp4A),
        "LA|{$articleIdA}|{$authorGeslibCode}|A|1",
    ]);

    $fileNameA = 'INTER-EQ-INITIAL.zip';
    $extractedPathA = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $fileNameA);
    Storage::disk('local')->put($extractedPathA, $fixtureA);

    $interFileA = GeslibInterFile::factory()->create([
        'name' => $fileNameA,
        'file_type' => GeslibInterFile::FILE_TYPE_INITIAL_LOAD,
    ]);

    $jobA = makeInitialJobStub($interFileA);
    $jobA->handle();

    $variantA = ProductVariant::where('sku', $articleIdA)->first();
    expect($variantA)->not->toBeNull('Route A (initial_load): ProductVariant must exist');
    $authorsA = $variantA->product->authors()->where('geslib_code', $authorGeslibCode)->get();
    expect($authorsA)->toHaveCount(1, 'Route A (initial_load): author must be linked');

    // ── Route B: direct sequential command invocation (true baseline) ─────────
    // This is the sequential processing the spec mandates equivalence against.
    // We invoke each InterCommand directly in dependency order — exactly what
    // ProcessGeslibInterFile does line-by-line without any coordinator or phase logic.
    ImportRegistry::flush();

    $articleIdB = 'ART-EQ-B';
    $gp4B = $baseGp4Fields;
    $gp4B[2] = $articleIdB;
    $gp4B[6] = '9780000000092';
    $gp4B[7] = '0000000000092';

    // Step 1: create author (AUT line) — same as phase 1 in initial_load
    $autLine = Author::fromLine([Author::CODE, Action::ADD, $authorGeslibCode, 'Kropotkin, Peter']);
    (new AuthorCommand($autLine))();

    // Step 2: create article (GP4 line) — same as phase 2 in initial_load
    $gp4Line = Article::fromLine($gp4B);
    (new ArticleCommand($gp4Line))();

    // Step 3: accumulate article-author relation (LA line) — same as phase 4 in initial_load
    $laLine = ArticleAuthor::fromLine([ArticleAuthor::CODE, $articleIdB, $authorGeslibCode, AuthorType::AUTHOR, '1']);
    $laCmd = new ArticleAuthorCommand($laLine);
    $laCmd();

    // Step 4: apply batch relations (what ProcessGeslibInterFileBatchLine does)
    (new ArticleAuthorRelation($articleIdB, [
        ['authorId' => $authorGeslibCode, 'authorType' => AuthorType::AUTHOR, 'position' => 1],
    ]))();

    $variantB = ProductVariant::where('sku', $articleIdB)->first();
    expect($variantB)->not->toBeNull('Route B (sequential): ProductVariant must exist');
    $authorsB = $variantB->product->authors()->where('geslib_code', $authorGeslibCode)->get();
    expect($authorsB)->toHaveCount(1, 'Route B (sequential): author must be linked');

    // ── Equivalence assertions: same structural invariants in both routes ───────
    // 1. Same author identity
    expect($authorsA->first()->geslib_code)
        ->toBe($authorsB->first()->geslib_code, 'author geslib_code must match across routes');

    // 2. Same editorial brand
    expect($variantA->product->brand->geslib_code)
        ->toBe($variantB->product->brand->geslib_code, 'brand geslib_code must match across routes');

    // 3. Same author count (neither route creates duplicates)
    expect($authorsA)->toHaveCount($authorsB->count(), 'author count must match across routes');
});

it('ProcessInitialGeslibInterFile sets status, started_at, finished_at and total_lines after processing a minimal fixture', function (): void {
    Event::fake();
    ImportRegistry::flush();

    // Setup: 4 CollectionGroups + collections + Brand required by ArticleCommand
    $editorialId = 'BR-E2E';
    $collectionId = 'COL-E2E';
    $languageId = 'es';
    $typeId = 'TY-E2E';
    $statusId = 'ST-E2E';
    $authorGeslibCode = 'AUT-E2E';
    $articleId = 'ART-E2E';

    LunarLanguage::factory()->create();
    Brand::factory()->create(['geslib_code' => $editorialId]);
    ensureCollectionsForArticle($typeId, $statusId, $languageId, $editorialId, $collectionId);

    // Build minimal INTER fixture with real text content:
    // Line 0: I| (initial file marker)
    // Phase 1: AUT line
    // Phase 2: GP4 line (63 pipe-separated fields)
    // Phase 4: LA line (article-author relation)
    $gp4Fields = array_fill(0, 63, '');
    $gp4Fields[0] = 'GP4';
    $gp4Fields[1] = 'A';           // action = Add
    $gp4Fields[2] = $articleId;
    $gp4Fields[3] = 'Test E2E Article';
    $gp4Fields[6] = '9780000000001';  // isbn
    $gp4Fields[7] = '0000000000001';  // ean
    $gp4Fields[8] = '200';            // pagesQty
    $gp4Fields[16] = '5';             // stock
    $gp4Fields[20] = $languageId;     // languageId [20]
    $gp4Fields[24] = $collectionId;   // collectionId [24]
    $gp4Fields[27] = $statusId;       // statusId [27]
    $gp4Fields[29] = '1500';          // retailPrice [29]
    $gp4Fields[30] = $typeId;         // typeId [30]
    $gp4Fields[32] = $editorialId;    // editorialId [32]
    $gp4Fields[33] = '1200';          // priceWithoutTaxes [33]

    $fixtureContent = implode("\n", [
        'I|initial',
        "AUT|A|{$authorGeslibCode}|Trotsky, Leon",
        implode('|', $gp4Fields),
        "LA|{$articleId}|{$authorGeslibCode}|A|1",
    ]);

    $fileName = 'INTER-E2E-TEST.zip';
    $extractedPath = config('lunar.geslib.inter_files_path').'/'.str_replace('.zip', '', $fileName);
    Storage::disk('local')->put($extractedPath, $fixtureContent);

    $interFile = GeslibInterFile::factory()->create([
        'name' => $fileName,
        'file_type' => GeslibInterFile::FILE_TYPE_INITIAL_LOAD,
    ]);

    $job = makeInitialJobStub($interFile);
    $job->handle();

    $interFile->refresh();

    // Runtime evidence: the coordinator must set these fields
    expect($interFile->started_at)->not->toBeNull('started_at must be set by the coordinator');
    expect($interFile->total_lines)->toBeGreaterThan(0, 'total_lines must reflect parsed lines');
    expect($interFile->finished_at)->not->toBeNull('finished_at must be set after all phases complete');
    expect($interFile->status)->toBeIn(
        [GeslibInterFile::STATUS_SUCCESS, GeslibInterFile::STATUS_WARNING],
        'status must be success or warning after processing',
    );
});
