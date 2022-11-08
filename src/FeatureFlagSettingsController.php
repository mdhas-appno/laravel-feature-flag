<?php

namespace FriendsOfCat\LaravelFeatureFlags;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use FriendsOfCat\LaravelFeatureFlags\Exceptions\InvalidJsonException;

class FeatureFlagSettingsController extends Controller
{

    use FeatureFlagHelper;

    public function getSettings(ExportImportRepository $repo)
    {
        try {
            $settings = FeatureFlag::all();
            $token = csrf_token();
            $exports = $repo->export();
            return view('laravel-feature-flag::settings', compact('settings', 'token', 'exports'));
        } catch (Exception $e) {
            Log::error("Error getting settings");
            Log::error($e);

            return redirect("/")->withMessage("Error visiting Settings page");
        }
    }

    public function create()
    {
        $flag = new FeatureFlag();

        return view('laravel-feature-flag::create', compact('flag'));
    }

    public function import(Request $request, ExportImportRepository $repo)
    {
        try {
            $decoded = $this->parseIncomingFeaturePayload($request->features);
            $repo->import($decoded);
            return redirect()->route('laravel-feature-flag.index')->withMessage("Created and or Updated Features");
        } catch (\Exception $e) {
            Log::error("Error importing feature flags");
            Log::error($e);
            return redirect()->route('laravel-feature-flag.index')->withMessage("Could not import feature flags");
        }
    }

    protected function parseIncomingFeaturePayload($features)
    {
        if (is_array($features)) {
            throw new Exception("Feature came in as array");
        }

        return json_decode($features, true);
    }

    public function store(Request $request)
    {
        try {
            $flag = new FeatureFlag();
            $flag->key = $request->input('key');
            $flag->variants = $this->formatVariant($request->input('variants'));
            $flag->save();
            return redirect()->route('laravel-feature-flag.index')->withMessage("Created Feature");
        } catch (\Exception $e) {
            return redirect()->route('laravel-feature-flag.index')->withMessage("Could not find feature flag");
        }
    }

    public function edit($id)
    {
        try {
            $flag = FeatureFlag::findOrFail($id);

            if (Session::has('variants')) {
                $flag->variants = Session::get('variants');
            }

            return view('laravel-feature-flag::edit', compact('flag'));
        } catch (Exception $e) {
            return redirect()->route('laravel-feature-flag.index')->withMessage("Could not find feature flag");
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $flag = FeatureFlag::findOrFail($id);

            $variants = json_decode($request->input('variants'), true);
            if (! $variants) {
                throw new InvalidJsonException();
            }

            $flag->variants = ($request->input('variants')) ? json_decode($request->input('variants'), true) : null;
            $flag->save();

            return redirect()->route(
                'laravel-feature-flag.index'
            )->withMessage(sprintf("Feature Flag Updated %d", $id));
        } catch (InvalidJsonException $e) {
            return redirect()->back()->withErrors("Invalid JSON format.")
                ->withVariants($request->input('variants'));
        } catch (Exception $e) {
            return redirect()->route('laravel-feature-flag.index')->withMessage("Could not find feature flag");
        }
    }

    public function destroy($id)
    {
        try {
            $flag = FeatureFlag::findOrFail($id);

            $flag->delete();

            return redirect()->route(
                'laravel-feature-flag.index'
            )->withMessage(sprintf("Feature Flag deleted %d", $id));
        } catch (Exception $e) {
            return redirect()->route('laravel-feature-flag.index')
                ->withMessage("Could not find feature flag");
        }
    }
}
