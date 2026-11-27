<?php

$base = dirname(__DIR__);

function patch_controller(string $path, string $class, callable $indexBody): void
{
    $code = file_get_contents($path);
    if (! preg_match('/public function index\([^)]*\): View\s*\{.*?\n    \}/s', $code, $m)) {
        echo "No index found in {$path}\n";

        return;
    }
    $new = $indexBody();
    $code = preg_replace('/public function index\([^)]*\): View\s*\{.*?\n    \}/s', $new, $code, 1);
    // Ensure Request import
    if (! str_contains($code, 'use Illuminate\\Http\\Request;')) {
        $code = str_replace(
            'use Illuminate\\Http\\RedirectResponse;',
            "use Illuminate\\Http\\RedirectResponse;\nuse Illuminate\\Http\\Request;",
            $code
        );
    }
    file_put_contents($path, $code);
    echo "Patched controller {$path}\n";
}

// Controllers rewritten with Write below instead for clarity.
echo "Use direct writes.\n";
