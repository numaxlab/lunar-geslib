<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\TypeDeclaration\Rector\StmtsAwareInterface\DeclareStrictTypesRector;

return RectorConfig::configure()
    ->withPaths([
        __DIR__.'/src',
        __DIR__.'/config',
        __DIR__.'/database',
        __DIR__.'/routes',
    ])
    ->withPreparedSets(
        deadCode: true,
        codeQuality: true,
        typeDeclarations: true,
        privatization: true,
        earlyReturn: true,
        strictBooleans: true,
    )
    ->withRules([
        DeclareStrictTypesRector::class,
    ])
    ->withPhpSets(php84: true);
