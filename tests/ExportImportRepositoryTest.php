<?php

namespace Tests;

use Illuminate\Support\Facades\File;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use FriendsOfCat\LaravelFeatureFlags\ExportImportRepository;

class ExportImportRepositoryTest extends TestCase
{
    use RefreshDatabase;

    public function testShouldExportFeatureFlags()
    {

        factory(FeatureFlag::class)->create([
            'key' => 'foo',
            'variants' => ["on"]
        ]);

        $repo = new ExportImportRepository();

        $results = $repo->export();

        $this->assertNotNull($results);
        $this->assertEquals([
            [
                'key' => "foo",
                "variants" => ['on']
            ]
        ], $results);
    }

    public function testShouldImportResults()
    {
        $exported = File::get(__DIR__ . '/fixtures/exported.json');
        $exported = json_decode($exported, true);
        $repo = new ExportImportRepository();

        $repo->import($exported);

        $ff = FeatureFlag::all();
        $this->assertNotNull($ff);
        $this->assertCount(1, $ff);
        $this->assertEquals("foo", $ff->first()->key);
    }

    public function testShouldNotDuplicateResults()
    {
        factory(FeatureFlag::class)->create(
            [
                'key' => 'foo',
                'variants' => ["on"]
            ]
        );

        $exported = File::get(__DIR__ . '/fixtures/exported.json');
        $exported = json_decode($exported, true);
        $repo = new ExportImportRepository();

        $repo->import($exported);

        $ff = FeatureFlag::all();
        $this->assertNotNull($ff);
        $this->assertCount(1, $ff);
        $this->assertEquals("foo", $ff->first()->key);
    }

    public function testUpdatesExistingResult()
    {
        factory(FeatureFlag::class)->create(
            [
                'key' => 'foo',
                'variants' => ["off"]
            ]
        );

        $exported = File::get(__DIR__ . '/fixtures/exported.json');
        $exported = json_decode($exported, true);
        $repo = new ExportImportRepository();

        $repo->import($exported);

        $ff = FeatureFlag::all();
        $this->assertNotNull($ff);
        $this->assertCount(1, $ff);
        $this->assertEquals("foo", $ff->first()->key);
        $this->assertEquals("on", $ff->first()->variants[0]);
    }
}
