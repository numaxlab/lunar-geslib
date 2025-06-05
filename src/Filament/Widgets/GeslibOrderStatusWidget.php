<?php

namespace NumaxLab\Lunar\Geslib\Filament\Widgets;

use Filament\Widgets\Widget;
use Illuminate\Database\Eloquent\Model; // Required for $record type hint
use NumaxLab\Lunar\Geslib\Models\LunarGeslibOrderSyncLog;
use NumaxLab\Lunar\Geslib\Filament\Resources\GeslibOrderSyncLogResource; // For linking

class GeslibOrderStatusWidget extends Widget
{
    protected static string $view = 'lunar-geslib::filament.widgets.geslib-order-status-widget';

    public ?Model $record = null; // This should be automatically available if widget is on a resource page

    public string $syncStatus = 'N/A';
    public string $geslibIdentifier = 'N/A';
    public string $lastSyncTimestamp = 'N/A';
    public bool $hasLogs = false;
    public string $logDetailsLink = '#';

    protected functionมีความ $listeners = ['recordUpdated' => 'updateRecord'];

    public function updateRecord(Model $record): void
    {
        $this->record = $record;
        $this->fetchSyncStatus();
    }

    public function mount(): void
    {
        if ($this->record) {
            $this->fetchSyncStatus();
        }
    }

    public function fetchSyncStatus(): void
    {
        if (!$this->record || !property_exists($this->record, 'id') || !$this->record->id) {
            $this->hasLogs = false;
            $this->syncStatus = 'Order context not available';
            return;
        }

        $latestLog = LunarGeslibOrderSyncLog::where('order_id', $this->record->id)
            ->latest('created_at')
            ->first();

        if ($latestLog) {
            $this->hasLogs = true;
            $this->syncStatus = ucfirst($latestLog->status);
            $this->lastSyncTimestamp = $latestLog->created_at->format('Y-m-d H:i:s');

            if (!empty($latestLog->payload_from_geslib)) {
                $payload = json_decode($latestLog->payload_from_geslib, true);
                if (isset($payload['geslib_order_id'])) {
                    $this->geslibIdentifier = $payload['geslib_order_id'];
                } elseif (isset($payload['id'])) {
                     $this->geslibIdentifier = $payload['id'];
                }
            } elseif ($latestLog->status == 'success' && !empty($latestLog->message)) {
                if (preg_match('/Geslib ID: (\S+)/', $latestLog->message, $matches)) {
                    $this->geslibIdentifier = $matches[1];
                }
            }

            $this->logDetailsLink = GeslibOrderSyncLogResource::getUrl('index', ['tableFilters[status][value]' => 'all', 'tableSearchQuery' => $this->record->id]);


        } else {
            $this->hasLogs = false;
            $this->syncStatus = 'Not Synced Yet';
            // Provide a link to search even if no logs yet, in case user wants to check later
            $this->logDetailsLink = GeslibOrderSyncLogResource::getUrl('index', ['tableSearchQuery' => $this->record->id]);
        }
    }
}
