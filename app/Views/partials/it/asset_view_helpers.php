<?php
/**
 * Shared helpers for IT asset list views.
 */
if (!function_exists('it_asset_device_meta')) {
    function it_asset_device_meta(string $groupName): array
    {
        $g = strtolower(trim($groupName));

        if (str_contains($g, 'laptop')) {
            return ['laptop', 'fa-laptop'];
        }
        if (str_contains($g, 'desktop') || str_contains($g, 'pc')) {
            return ['desktop', 'fa-desktop'];
        }
        if (str_contains($g, 'monitor') || str_contains($g, 'display')) {
            return ['monitor', 'fa-tv'];
        }
        if (str_contains($g, 'phone') || str_contains($g, 'mobile')) {
            return ['phone', 'fa-mobile-alt'];
        }
        if (str_contains($g, 'printer')) {
            return ['printer', 'fa-print'];
        }

        return ['default', 'fa-hdd'];
    }
}
