<?php

namespace NumaxLab\Lunar\Geslib\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Notification as NotificationFacade;
use NumaxLab\Lunar\Geslib\Notifications\GeslibConfigurationError;

class GeslibConfigCheckWidget extends Widget
{
    protected static string $view = 'lunar-geslib::filament.widgets.geslib-config-check-widget';

    public array $configValues = [];

    public function mount(): void
    {
        $this->fetchConfigData();
    }

    public function fetchConfigData(): void
    {
        $geslibConfig = config('lunar.geslib'); // Assuming config is merged under 'lunar.geslib'
        $configForDisplay = [];
        $criticalErrors = [];

        if ($geslibConfig) {
            // Check Inter Files Disk
            if (!empty($geslibConfig['inter_files_disk'])) {
                $configForDisplay['Inter Files Disk'] = ['value' => $geslibConfig['inter_files_disk'], 'status' => 'Set'];
            } else {
                $configForDisplay['Inter Files Disk'] = ['value' => 'Not Set', 'status' => 'Error'];
                $criticalErrors['inter_files_disk'] = 'Inter Files Disk (`lunar.geslib.inter_files_disk`) is not configured.';
            }

            // Check Inter Files Path
            if (!empty($geslibConfig['inter_files_path'])) {
                $configForDisplay['Inter Files Path'] = ['value' => $geslibConfig['inter_files_path'], 'status' => 'Set'];
            } else {
                $configForDisplay['Inter Files Path'] = ['value' => 'Not Set', 'status' => 'Error'];
                $criticalErrors['inter_files_path'] = 'Inter Files Path (`lunar.geslib.inter_files_path`) is not configured.';
            }

            // Check Product Types Taxation
            if (empty($geslibConfig['product_types_taxation'])) {
                $configForDisplay['Product Types Taxation'] = ['value' => 'Not Set or Empty', 'status' => 'Warning'];
                // This might not be critical enough for a notification, depending on usage.
            } else {
                $configForDisplay['Product Types Taxation'] = ['value' => count($geslibConfig['product_types_taxation']) . ' entries', 'status' => 'Set'];
            }

            // Check Notification Settings
            $configForDisplay['Notifications Enabled'] = ['value' => !empty($geslibConfig['notifications']['enabled']) ? 'Yes' : 'No', 'status' => 'Info'];
            if (!empty($geslibConfig['notifications']['enabled']) && empty($geslibConfig['notifications']['mail_to'])) {
                $configForDisplay['Notifications Mail To'] = ['value' => 'Not Set (Notifications will fail)', 'status' => 'Error'];
                $criticalErrors['notifications_mail_to'] = 'Notifications are enabled, but Mail To address (`lunar.geslib.notifications.mail_to`) is not set.';
            } elseif (!empty($geslibConfig['notifications']['mail_to'])) {
                 $configForDisplay['Notifications Mail To'] = ['value' => $geslibConfig['notifications']['mail_to'], 'status' => 'Set'];
            } else {
                 $configForDisplay['Notifications Mail To'] = ['value' => 'Not Set', 'status' => 'Info'];
            }
            $configForDisplay['Notifications Throttle Minutes'] = ['value' => $geslibConfig['notifications']['throttle_period_minutes'] ?? 'N/A', 'status' => 'Info'];

        } else {
            $configForDisplay['Geslib Configuration File'] = ['value' => '`lunar.geslib` not found or empty', 'status' => 'Error'];
            $criticalErrors['geslib_config_missing'] = 'Geslib configuration (`lunar.geslib`) is missing or empty.';
        }

        $this->configValues = $configForDisplay;

        if (!empty($criticalErrors) && config('lunar.geslib.notifications.enabled') && config('lunar.geslib.notifications.mail_to')) {
            $mailTo = config('lunar.geslib.notifications.mail_to');
            $throttlePeriodMinutes = config('lunar.geslib.notifications.throttle_period_minutes', 60);

            foreach ($criticalErrors as $key => $message) {
                $cacheKey = 'geslib_notification_config_error_' . $key;
                if (!Cache::has($cacheKey)) {
                    NotificationFacade::route('mail', $mailTo)
                        ->notify(new GeslibConfigurationError($key, $message));
                    Cache::put($cacheKey, true, now()->addMinutes($throttlePeriodMinutes));
                }
            }
        }
    }
}
