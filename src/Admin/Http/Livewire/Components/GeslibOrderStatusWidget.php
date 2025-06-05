<?php

namespace NumaxLab\LunarGeslib\Admin\Http\Livewire\Components;

use Livewire\Component;
use NumaxLab\LunarGeslib\Models\LunarGeslibOrderSyncLog;
use Lunar\Models\Order; // To type-hint if an Order model is passed, or to fetch it.

class GeslibOrderStatusWidget extends Component
{
    public Order $order; // Assuming the Order model is passed to the component

    public $syncStatus = 'N/A';
    public $geslibIdentifier = 'N/A'; // This might be hard to get from current log structure
    public $lastSyncTimestamp = 'N/A';
    public $hasLogs = false;
    public $logDetailsLink = '#';

    public function mount(Order $order)
    {
        $this->order = $order;
        $this->fetchSyncStatus();
    }

    public function fetchSyncStatus()
    {
        $latestLog = LunarGeslibOrderSyncLog::where('order_id', $this->order->id)
            ->latest('created_at')
            ->first();

        if ($latestLog) {
            $this->hasLogs = true;
            $this->syncStatus = ucfirst($latestLog->status);
            $this->lastSyncTimestamp = $latestLog->created_at->format('Y-m-d H:i:s');

            // Attempt to get a Geslib identifier if it's part of the payload or message
            // This is speculative based on potential log content.
            if (!empty($latestLog->payload_from_geslib)) {
                // Assuming payload_from_geslib is JSON and might contain an ID.
                $payload = json_decode($latestLog->payload_from_geslib, true);
                if (isset($payload['geslib_order_id'])) {
                    $this->geslibIdentifier = $payload['geslib_order_id'];
                } elseif (isset($payload['id'])) {
                     $this->geslibIdentifier = $payload['id'];
                }
            } elseif ($latestLog->status == 'success' && !empty($latestLog->message)) {
                // Try to parse from message if it's like "Order 123 synced with Geslib ID: G12345"
                if (preg_match('/Geslib ID: (\S+)/', $latestLog->message, $matches)) {
                    $this->geslibIdentifier = $matches[1];
                }
            }

            // Link to the detailed order export log, filtered by this order ID
            $this->logDetailsLink = route('adminhub.geslib.order-export-log', ['q_order_id' => $this->order->id]);

        } else {
            $this->hasLogs = false;
            $this->syncStatus = 'Not Synced Yet';
        }
    }

    public function render()
    {
        return view('lunar-geslib::admin.livewire.components.geslib-order-status-widget');
    }
}
