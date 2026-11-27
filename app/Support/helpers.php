<?php

if (! function_exists('vasset')) {
    /**
     * Generate a versioned asset URL so browsers pick up CSS/JS after deploy.
     */
    function vasset(string $path, ?bool $secure = null): string
    {
        $url = asset($path, $secure);
        $publicPath = public_path(ltrim(str_replace('\\', '/', $path), '/'));

        if (is_file($publicPath)) {
            $version = (string) filemtime($publicPath);
            $url .= (str_contains($url, '?') ? '&' : '?').'v='.$version;
        }

        return $url;
    }
}
