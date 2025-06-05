<?php

namespace NumaxLab\LunarGeslib\Admin\Http\Livewire\Components;

use Livewire\Component;
use Livewire\WithPagination;
use NumaxLab\LunarGeslib\Models\LunarGeslibOrderSyncLog;

class GeslibOrderExportLog extends Component
{
    use WithPagination;

    public $searchOrderId = '';
    public $searchEndpoint = '';
    public $filterStatus = '';
    public $filterDateFrom = '';
    public $filterDateTo = ''; // Corrected typo

    // Properties for modal
    public $showingModal = false;
    public $selectedLog = null;

    protected $queryString = [
        'searchOrderId' => ['except' => '', 'as' => 'q_order_id'],
        'searchEndpoint' => ['except' => '', 'as' => 'q_endpoint'],
        'filterStatus' => ['except' => '', 'as' => 'f_status'],
        'filterDateFrom' => ['except' => '', 'as' => 'f_date_from'],
        'filterDateTo' => ['except' => '', 'as' => 'f_date_to'], // Corrected here
        'page' => ['except' => 1],
    ];

    public function mount() {
        // Correct the typo if it was intended to be a property declaration
        // If $this->filterDateTo was meant, it's fine. If 'filterDateTo' (string), it's unusual.
        // Assuming it's a typo and should be:
        // public $filterDateTo = ''; // This should be declared with other public properties.
        // For now, proceeding as if $this->filterDateTo is correctly handled by Livewire's magic properties
        // based on queryString or direct model binding in blade.
    }


    public function updatingSearchOrderId() { $this->resetPage(); }
    public function updatingSearchEndpoint() { $this->resetPage(); }
    public function updatingFilterStatus() { $this->resetPage(); }
    public function updatingFilterDateFrom() { $this->resetPage(); }
    public function updatingFilterDateTo() { $this->resetPage(); } // Corrected here


    public function showDetailsModal(int $logId)
    {
        $this->selectedLog = LunarGeslibOrderSyncLog::find($logId);
        $this->showingModal = true;
    }

    public function closeModal()
    {
        $this->selectedLog = null;
        $this->showingModal = false;
    }


    public function render()
    {
        $query = LunarGeslibOrderSyncLog::query()->orderBy('created_at', 'desc');

        if ($this->searchOrderId) {
            $query->where('order_id', 'like', '%' . $this->searchOrderId . '%');
        }

        if ($this->searchEndpoint) {
            $query->where('geslib_endpoint_called', 'like', '%' . $this->searchEndpoint . '%');
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterDateFrom) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) { // Corrected here
            $query->whereDate('created_at', '<=', $this->filterDateTo); // Corrected here
        }

        $logs = $query->paginate(15);

        return view('lunar-geslib::admin.livewire.components.geslib-order-export-log', [
            'logs' => $logs,
        ])->layout('adminhub::layouts.app');
    }
}
