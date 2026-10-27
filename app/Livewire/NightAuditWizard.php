<?php

namespace App\Livewire;

use App\Models\NightAudit;
use App\Services\NightAuditService;
use Carbon\Carbon;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.app')]
class NightAuditWizard extends Component
{
    use WithPagination;

    public string $auditDate = '';
    public string $notes = '';
    public bool $confirmingAudit = false;

    public function mount(): void
    {
        $this->authorizeAccess();
        $this->auditDate = now()->toDateString();
    }

    protected function authorizeAccess(): void
    {
        $user = auth()->user();
        if (!$user || (!$user->hasRole('Manager') && !$user->hasRole('Admin') && !$user->hasRole('admin'))) {
            abort(403, 'Akses ditolak. Hanya Manager yang berwenang mengakses dan mengeksekusi Night Audit.');
        }
    }

    public function executeAudit(): void
    {
        $this->authorizeAccess();
        try {
            $audit = NightAuditService::performNightAudit(
                auth()->user(),
                $this->auditDate,
                $this->notes ?: null
            );

            $this->confirmingAudit = false;
            $this->notes = '';
            session()->flash('message', "Night Audit tanggal {$audit->audit_date->format('d/m/Y')} berhasil diselesaikan. Pendapatan kamar Rp " . number_format($audit->total_room_revenue, 0, ',', '.') . " telah diakui.");
        } catch (\Exception $e) {
            $this->confirmingAudit = false;
            session()->flash('error', $e->getMessage());
        }
    }

    public function render()
    {
        $checklist = NightAuditService::getAuditChecklist($this->auditDate);
        $pastAudits = NightAudit::with('auditor')->latest('audit_date')->paginate(10);

        return view('livewire.night-audit-wizard', [
            'checklist' => $checklist,
            'pastAudits' => $pastAudits,
            'isManager' => auth()->user()->hasRole('Manager') || auth()->user()->hasRole('admin'),
        ]);
    }
}
