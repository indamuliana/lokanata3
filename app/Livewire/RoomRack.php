<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Room;

class RoomRack extends Component
{
    public $search = '';
    public $statusFilter = 'Semua';
    public $selectedRoom = null;
    public $showDrawer = false;

    public function selectRoom($roomId)
    {
        $this->selectedRoom = Room::with('roomType')->find($roomId);
        $this->showDrawer = true;
    }

    public function closeDrawer()
    {
        $this->showDrawer = false;
        $this->selectedRoom = null;
    }

    public function toggleReadiness($roomId)
    {
        $room = Room::find($roomId);
        if($room) {
            if($room->readiness_status == 'Kotor') {
                $room->readiness_status = 'Sedang Dibersihkan';
            } elseif($room->readiness_status == 'Sedang Dibersihkan') {
                $room->readiness_status = 'Ready';
            } elseif($room->readiness_status == 'Ready') {
                $room->readiness_status = 'Kotor';
            }
            $room->save();
        }
    }

    public function processCheckIn()
    {
        // Simulasi Check-in Cepat
        if($this->selectedRoom && $this->selectedRoom->current_occupancy == 'Available') {
            $this->selectedRoom->current_occupancy = 'Check-in';
            $this->selectedRoom->save();
            
            // Generate dummy reservation & invoice for demonstration
            $res = \App\Models\Reservation::create([
                'code_booking' => 'BK-' . time(),
                'guest_name' => 'Tamu Walk-in',
                'check_in_date' => now(),
                'check_out_date' => now()->addDay(),
                'status' => 'Check-in',
            ]);
            
            $inv = \App\Models\Invoice::create([
                'reservation_id' => $res->id,
                'invoice_number' => 'INV-' . time(),
                'subtotal' => $this->selectedRoom->roomType->base_price_normal,
                'tax_amount' => $this->selectedRoom->roomType->base_price_normal * 0.1,
                'total_amount' => $this->selectedRoom->roomType->base_price_normal * 1.1,
                'balance_due' => $this->selectedRoom->roomType->base_price_normal * 1.1,
            ]);

            session()->flash('message', 'Check-in Berhasil!');
            $this->closeDrawer();
        }
    }

    public function processCheckOut()
    {
        // Mock checkout lockout logic
        if($this->selectedRoom && $this->selectedRoom->current_occupancy == 'Check-in') {
            // Check if there is balance due (Mocked for now)
            $balance_due = 50000; // Force lockout demonstration
            
            if($balance_due > 0 && !auth()->user()->hasRole('Manager')) {
                session()->flash('error', 'Gagal: Tamu masih memiliki sisa tagihan Rp ' . number_format($balance_due, 0, ',', '.'));
                return;
            }

            $this->selectedRoom->current_occupancy = 'Available';
            $this->selectedRoom->readiness_status = 'Kotor';
            $this->selectedRoom->save();

            session()->flash('message', 'Check-out Berhasil, kamar kini kotor.');
            $this->closeDrawer();
        }
    }

    public function render()
    {
        $query = Room::with('roomType');

        if (!empty($this->search)) {
            $query->where(function($q) {
                $q->where('room_number', 'like', '%' . $this->search . '%')
                  ->orWhereHas('roomType', function($q2) {
                      $q2->where('name', 'like', '%' . $this->search . '%');
                  });
            });
        }

        if ($this->statusFilter !== 'Semua') {
            if (in_array($this->statusFilter, ['Kotor', 'Ready', 'Sedang Dibersihkan'])) {
                $query->where('readiness_status', $this->statusFilter);
            } else {
                $query->where('current_occupancy', $this->statusFilter);
            }
        }

        $rooms = $query->get();

        return view('livewire.room-rack', [
            'rooms' => $rooms
        ]);
    }
}
