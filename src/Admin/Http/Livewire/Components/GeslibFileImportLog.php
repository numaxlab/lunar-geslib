<?php

namespace NumaxLab\LunarGeslib\Admin\Http\Livewire\Components;

use Livewire\Component;
use Livewire\WithPagination;
use NumaxLab\LunarGeslib\Models\GeslibInterFile;
use NumaxLab\LunarGeslib\Jobs\ProcessGeslibInterFile; // For reprocessing
use Illuminate\Support\Facades\Bus; // For dispatching jobs

class GeslibFileImportLog extends Component
{
    use WithPagination;

    public $searchFilename = '';
    public $filterStatus = '';
    public $filterDateFrom = '';
    public $filterDateTo = '';

    protected $queryString = [
        'searchFilename' => ['except' => '', 'as' => 'q_filename'],
        'filterStatus' => ['except' => '', 'as' => 'f_status'],
        'filterDateFrom' => ['except' => '', 'as' => 'f_date_from'],
        'filterDateTo' => ['except' => '', 'as' => 'f_date_to'],
        'page' => ['except' => 1],
    ];

    public function updatingSearchFilename()
    {
        $this->resetPage();
    }

    public function updatingFilterStatus()
    {
        $this->resetPage();
    }
    public function updatingFilterDateFrom()
    {
        $this->resetPage();
    }
    public function updatingFilterDateTo()
    {
        $this->resetPage();
    }


    public function reprocessFile($fileId)
    {
        $file = GeslibInterFile::find($fileId);

        if ($file && $file->status === 'error') { // Or some other condition for reprocessing
            // Optionally, update status to 'pending' or 'reprocessing'
            // $file->update(['status' => 'pending', 'notes' => 'Reprocessing attempt initiated.']);

            // Dispatch the job
            // Ensure ProcessGeslibInterFile can be dispatched with just the model.
            // If it needs specific parameters, adjust accordingly.
            Bus::dispatch(new ProcessGeslibInterFile($file));

            session()->flash('message', 'File ID ' . $fileId . ' has been queued for reprocessing.');
        } else {
            session()->flash('error', 'File ID ' . $fileId . ' cannot be reprocessed or was not found.');
        }
        // Refresh data
        $this->render();
    }

    public function render()
    {
        $query = GeslibInterFile::query()->orderBy('created_at', 'desc');

        if ($this->searchFilename) {
            $query->where('name', 'like', '%' . $this->searchFilename . '%');
        }

        if ($this->filterStatus) {
            $query->where('status', $this->filterStatus);
        }

        if ($this->filterDateFrom) {
            $query->whereDate('created_at', '>=', $this->filterDateFrom);
        }

        if ($this->filterDateTo) {
            $query->whereDate('created_at', '<=', $this->filterDateTo);
        }

        $logs = $query->paginate(15); // 15 items per page

        return view('lunar-geslib::admin.livewire.components.geslib-file-import-log', [
            'logs' => $logs,
        ])->layout('adminhub::layouts.app');
    }
}
