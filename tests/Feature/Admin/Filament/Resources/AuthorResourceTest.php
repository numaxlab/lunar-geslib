<?php

use Lunar\Models\Language;
use NumaxLab\Lunar\Geslib\Admin\Filament\Resources\AuthorResource;
use NumaxLab\Lunar\Geslib\Models\Author;

use function Pest\Livewire\livewire;

beforeEach(function () {
    Language::factory()->create();

    $this->asStaff(admin: true);
});

it('can list authors', function () {
    $authors = Author::factory()->count(5)->create();

    $this
        ->get(AuthorResource::getUrl('index'))
        ->assertSuccessful();

    livewire(AuthorResource\Pages\ListAuthors::class)
        ->assertCanSeeTableRecords($authors);
});

it('can create an author', function () {
    $this
        ->get(AuthorResource::getUrl('create'))
        ->assertSuccessful();

    $newAuthor = Author::factory()->create();

    livewire(AuthorResource\Pages\CreateAuthor::class)
        ->fillForm([
            'name' => $newAuthor->name,
        ])
        ->call('create')
        ->assertHasNoErrors();

    $this->assertDatabaseHas(Author::class, [
        'name' => $newAuthor->name,
    ]);
});

it('can edit an author', function () {
    $this->get(AuthorResource::getUrl('edit', [
        'record' => Author::factory()->create(),
    ]))->assertSuccessful();
});

it('can handle author media', function () {
    $this->get(AuthorResource::getUrl('media', [
        'record' => Author::factory()->create(),
    ]))->assertSuccessful();
});

it('can handle author urls', function () {
    $this->get(AuthorResource::getUrl('urls', [
        'record' => Author::factory()->create(),
    ]))->assertSuccessful();
});

it('can handle author products', function () {
    $this->get(AuthorResource::getUrl('products', [
        'record' => Author::factory()->create(),
    ]))->assertSuccessful();
});
