<?php

pest()
    ->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

function getSchemaPath(string $filename): string
{
    return __DIR__.'/Schemas/'.$filename;
}
