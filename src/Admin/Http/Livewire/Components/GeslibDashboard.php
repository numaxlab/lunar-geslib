<?php

namespace NumaxLab\LunarGeslib\Admin\Http\Livewire\Components;

use Illuminate\Support\Facades\Cache; // Added for throttling
use Illuminate\Support\Facades\Notification as NotificationFacade; // Added for sending notifications
use Livewire\Component;
use NumaxLab\LunarGeslib\Models\GeslibInterFile;
use NumaxLab\LunarGeslib\Models\LunarGeslibOrderSyncLog;
use NumaxLab\LunarGeslib\Notifications\GeslibConfigurationError; // Added

class GeslibDashboard extends Component
{
    // Properties for File Import Section
    public $lastImportRunStatus = 'N/A';
    public $lastImportRunTimestamp = 'N/A';
    public $totalFilesProcessed = 0;
    public $totalRecordsCreated = 0; // Placeholder - needs logic
    public $totalRecordsUpdated = 0; // Placeholder - needs logic
    public $totalRecordsDeleted = 0; // Placeholder - needs logic
    public $recentImportErrors = [];

    // Properties for Order Export Section
    public $ordersAwaitingSync = 0;
    public $ordersSuccessfullySynced = 0;
    public $ordersFailedSync = 0;
    public $recentOrderSyncErrors = [];

    // Properties for Configuration Check Section
    public $configValues = [];

    public function mount()
    {
        $this->fetchFileImportData();
        $this->fetchOrderExportData(); // Will mostly show "No data" initially
        $this->fetchConfigData();
    }

    public function fetchFileImportData()
    {
        $lastRun = GeslibInterFile::latest()->first();
        if ($lastRun) {
            $this->lastImportRunStatus = $lastRun->status ?? 'Unknown'; // Assuming GeslibInterFile has a status
            $this->lastImportRunTimestamp = $lastRun->created_at ? $lastRun->created_at->toDateTimeString() : 'Unknown';
        }

        $this->totalFilesProcessed = GeslibInterFile::count();
        // TODO: Implement more detailed record stats if possible from GeslibInterFile structure
        // For now, these are placeholders:
        $this->totalRecordsCreated = GeslibInterFile::where('status', 'processed')->count(); // Example
        $this->totalRecordsUpdated = 0; // Needs more info on how to determine this
        $this->totalRecordsDeleted = GeslibInterFile::where('status', 'deleted')->count(); // Example, if applicable

        $this->recentImportErrors = GeslibInterFile::where('status', 'error') // Assuming 'error' status
                                        ->latest()
                                        ->take(5)
                                        ->get();
    }

    public function fetchOrderExportData()
    {
        // Querying the new table - will likely be empty initially
        $this->ordersAwaitingSync = LunarGeslibOrderSyncLog::where('status', 'pending')->count(); // Example status
        $this->ordersSuccessfullySynced = LunarGeslibOrderSyncLog::where('status', 'success')->count();
        $this->ordersFailedSync = LunarGeslibOrderSyncLog::where('status', 'error')->count();

        $this->recentOrderSyncErrors = LunarGeslibOrderSyncLog::where('status', 'error')
                                            ->latest()
                                            ->take(5)
                                            ->get();
    }

    public function fetchConfigData()
    {
        $geslibConfig = config('lunar.geslib');
        $configForDisplay = [];
        $criticalErrors = [];

        if ($geslibConfig) {
            // Check Inter Files Disk
            if (isset($geslibConfig['inter_files_disk']) && $geslibConfig['inter_files_disk']) {
                $configForDisplay['Inter Files Disk'] = 'Set';
            } else {
                $configForDisplay['Inter Files Disk'] = 'Not Set';
                $criticalErrors['inter_files_disk'] = 'Inter Files Disk is not configured.';
            }

            // Check Inter Files Path
            if (isset($geslibConfig['inter_files_path']) && $geslibConfig['inter_files_path']) {
                $configForDisplay['Inter Files Path'] = 'Set';
            } else {
                $configForDisplay['Inter Files Path'] = 'Not Set';
                $criticalErrors['inter_files_path'] = 'Inter Files Path is not configured.';
            }

            // Check Product Types Taxation
            if (empty($geslibConfig['product_types_taxation'])) {
                $configForDisplay['Product Types Taxation'] = 'Not Set or Empty';
                // This might not be critical enough for a notification, depending on usage
                // $criticalErrors['product_types_taxation'] = 'Product Types Taxation is not configured.';
            } else {
                $configForDisplay['Product Types Taxation'] = 'Set (' . count($geslibConfig['product_types_taxation']) . ' entries)';
            }

            // Check Notification Settings
            $configForDisplay['Notifications Enabled'] = !empty($geslibConfig['notifications']['enabled']) ? 'Yes' : 'No';
            $configForDisplay['Notifications Mail To'] = !empty($geslibConfig['notifications']['mail_to']) ? 'Set' : 'Not Set (Notifications will fail)';
            if (empty($geslibConfig['notifications']['mail_to']) && !empty($geslibConfig['notifications']['enabled'])) {
                 $criticalErrors['notifications_mail_to'] = 'Notifications are enabled, but Mail To address is not set.';
            }
            $configForDisplay['Notifications Throttle Minutes'] = $geslibConfig['notifications']['throttle_period_minutes'] ?? 'N/A';

        } else {
            $configForDisplay['Geslib Configuration'] = 'Not Loaded or Empty';
            $criticalErrors['geslib_config_missing'] = 'Geslib configuration file (lunar.geslib) is missing or empty.';
        }

        $this->configValues = $configForDisplay;

        // Handle sending notifications for critical errors
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

    public function render()
    {
        return view('lunar-geslib::admin.livewire.components.geslib-dashboard')
            ->layout('adminhub::layouts.app'); // Assuming Lunar admin uses this layout
    }
}
