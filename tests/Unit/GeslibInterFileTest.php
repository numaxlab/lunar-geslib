<?php

namespace NumaxLab\Lunar\Geslib\Tests\Unit;

use Illuminate\Foundation\Testing\RefreshDatabase;
use NumaxLab\Lunar\Geslib\Models\GeslibInterFile;
use NumaxLab\Lunar\Geslib\Tests\TestCase;

class GeslibInterFileTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_geslib_inter_file()
    {
        $data = [
            'name' => 'testfile.txt',
            'type' => 'articles',
            'status' => 'pending',
            'notes' => 'Some notes',
            'started_at' => null,
            'finished_at' => null,
            'processed_lines' => 0,
            'total_lines' => 100,
        ];

        $file = GeslibInterFile::create($data);

        $this->assertInstanceOf(GeslibInterFile::class, $file);
        $this->assertDatabaseHas('geslib_inter_files', $data);
        $this->assertEquals('testfile.txt', $file->name);
        $this->assertEquals('pending', $file->status);
    }

    /** @test */
    public function status_can_be_updated()
    {
        $file = GeslibInterFile::create([
            'name' => 'anotherfile.txt',
            'type' => 'stock',
            'status' => 'pending',
        ]);

        $file->update(['status' => 'processed']);

        $this->assertEquals('processed', $file->fresh()->status);
    }
}
