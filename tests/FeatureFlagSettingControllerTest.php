<?php

namespace Tests;

use Illuminate\Http\Request;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Facades\App;
use Illuminate\Support\ViewErrorBag;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlag;
use Illuminate\Foundation\Testing\RefreshDatabase;
use FriendsOfCat\LaravelFeatureFlags\ExportImportRepository;
use FriendsOfCat\LaravelFeatureFlags\AddExampleFeaturesTableSeeder;
use FriendsOfCat\LaravelFeatureFlags\FeatureFlagSettingsController;
use FriendsOfCat\LaravelFeatureFlags\Exceptions\InvalidJsonException;

class FeatureFlagSettingControllerTest extends TestCase
{
    use RefreshDatabase;

    public function testShouldSeeNewSettings()
    {
        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->create()->getData();
        $flag = $data['flag'];
        $this->assertNotEmpty($flag);
    }

    public function testShouldGetSettings()
    {
        $example_features = new AddExampleFeaturesTableSeeder();
        $example_features->run();

        $example_controller = App::make(FeatureFlagSettingsController::class);
        $repo = new ExportImportRepository();
        $data = $example_controller->getSettings($repo)->getData();
        $exports = $data['exports'];
        $this->assertEquals($exports[0]['key'], 'add-twitter-field');
        $this->assertEquals($exports[1]['key'], 'see-twitter-field');
    }

    public function testShouldImportSettings()
    {
        $example_features = new AddExampleFeaturesTableSeeder();
        $example_features->run();

        $request = new Request();
        $feature_flag = '[{ "key": "add-twitter-field", "variants": "off"}]';

        $request->merge(['features' => $feature_flag]);


        $example_controller = App::make(FeatureFlagSettingsController::class);
        $repo = new ExportImportRepository();
        $data = $example_controller->import($request, $repo);
        $this->assertEquals($data->getSession()->get('message'), 'Created and or Updated Features');
    }


    public function testShouldImportSettingsFail()
    {
        $example_features = new AddExampleFeaturesTableSeeder();
        $example_features->run();

        $request = new Request();


        $request->merge(['features' => []]);


        $example_controller = App::make(FeatureFlagSettingsController::class);
        $repo = new ExportImportRepository();
        $data = $example_controller->import($request, $repo);
        $this->assertEquals($data->getSession()->get('message'), 'Could not import feature flags');
    }

    public function testStripQuotes()
    {
        $request = new Request();
        $request->merge(['key' => 'test_A', 'variants' => '"on"']);

        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->store($request);

        $flag = FeatureFlag::first();
        $this->assertNotNull($flag);
        $this->assertEquals("on", $flag->variants);
        $this->assertEquals($data->getSession()->get('message'), 'Created Feature');
    }

    public function testStripSingleQuotes()
    {
        $request = new Request();
        $request->merge(['key' => 'test_A', 'variants' => '\'on\'']);

        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->store($request);

        $flag = FeatureFlag::first();
        $this->assertNotNull($flag);
        $this->assertEquals("on", $flag->variants);
        $this->assertEquals($data->getSession()->get('message'), 'Created Feature');
    }

    public function testShouldStore()
    {
        $request = new Request();
        $request->merge(['key' => 'test_A', 'variants' => 'on']);


        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->store($request);
        $this->assertEquals($data->getSession()->get('message'), 'Created Feature');
    }

    public function testShouldStoreFail()
    {
        $request = new Request();
        $request->merge(['key' => null, 'variants' => null]);


        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->store($request);
        $this->assertEquals($data->getSession()->get('message'), 'Could not find feature flag');
    }



    public function testShouldEdit()
    {

        $example_features = new AddExampleFeaturesTableSeeder();
        $example_features->run();

        $feature_flag = FeatureFlag::where('key', 'add-twitter-field')->first();

        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->edit($feature_flag->id)->getData();
        $flag = $data['flag'];
        $this->assertNotEmpty($flag);
        $this->assertEquals($flag->key, 'add-twitter-field');
    }

    public function testShouldEditFail()
    {
        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->edit(1001);
        $this->assertEquals($data->getSession()->get('message'), 'Could not find feature flag');
    }


    public function testShouldUpdate()
    {
        $example_features = new AddExampleFeaturesTableSeeder();
        $example_features->run();

        $request = new Request();
        $request->merge(['variants' => '["on"]']);
        $feature_flag = FeatureFlag::where('key', 'add-twitter-field')->first();

        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->update($request, $feature_flag->id);

        $feature_flag = FeatureFlag::where('key', 'add-twitter-field')->first();
        $this->assertEquals($feature_flag->variants[0], 'on');

        $this->assertEquals($data->getSession()->get('message'), sprintf("Feature Flag Updated %d", $feature_flag->id));
    }


    public function testShouldUpdateFail()
    {
        $request = new Request();

        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->update($request, null);

        $this->assertEquals($data->getSession()->get('message'), "Could not find feature flag");
    }

    public function testUpdateShouldThrowInvalidJsonError()
    {
        $ff = factory(FeatureFlag::class)->create();

        $request = new Request([
            'key' => 'invalid',
            'variants' => 'my-invalid-json-string/'
        ]);

        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->update($request, $ff->id);

        $errorBag = new ViewErrorBag();
        $errorBag->put('default', new MessageBag(["Invalid JSON format."]));

        $this->assertEquals($data->getSession()->get('errors'), $errorBag);
    }

    public function testShouldDestroy()
    {
        $example_features = new AddExampleFeaturesTableSeeder();
        $example_features->run();

        $feature_flag = FeatureFlag::where('key', 'add-twitter-field')->first();

        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->destroy($feature_flag->id);

        $feature_flag_count = FeatureFlag::where('key', 'add-twitter-field')->count();

        $this->assertEquals($data->getSession()->get('message'), sprintf("Feature Flag deleted %d", $feature_flag->id));
        $this->assertEquals($feature_flag_count, 0);
    }

    public function testShouldDestroyFail()
    {
        $example_features = new AddExampleFeaturesTableSeeder();
        $example_features->run();

        $example_controller = App::make(FeatureFlagSettingsController::class);
        $data = $example_controller->destroy(null);

        $feature_flag_count = FeatureFlag::where('key', 'add-twitter-field')->count();

        $this->assertEquals($data->getSession()->get('message'), "Could not find feature flag");
        $this->assertEquals($feature_flag_count, 1);
    }
}
