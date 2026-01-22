<?php

declare(strict_types=1);

namespace Liangjin0228\Questionnaire;

use Illuminate\Support\Facades\File;

class AssetManager
{
    public function scripts()
    {
        // Check if we are in local dev mode trying to use HMR from the package context?
        // Usually packages are consumed as built artifacts.
        // We assume the user has published the assets to public/vendor/questionnaire
        // OR we are serving from the package's public directory?

        // Strategy: Look for the Vite manifest copied to public/vendor/questionnaire/.vite/manifest.json
        $manifestPath = public_path('vendor/questionnaire/.vite/manifest.json');

        if (! File::exists($manifestPath)) {
            // Fallback or dev mode warning
            return '<!-- Questionnaire assets not found. Run php artisan vendor:publish --tag=questionnaire-assets -->';
        }

        $manifest = json_decode(File::get($manifestPath), true);

        // Assuming the entry point is 'resources/js/questionnaire/main.js'
        $entry = $manifest['resources/js/questionnaire/main.js'] ?? null;

        if (! $entry) {
            return '<!-- Questionnaire entry point not found in manifest -->';
        }

        $js = $entry['file'];
        $css = $entry['css'][0] ?? null; // Getting first css file

        $html = '';
        if ($css) {
            $url = asset("vendor/questionnaire/{$css}");
            $html .= "<link rel=\"stylesheet\" href=\"{$url}\">";
        }

        if ($js) {
            $url = asset("vendor/questionnaire/{$js}");
            $html .= "<script type=\"module\" src=\"{$url}\"></script>";
        }

        return $html;
    }
}
