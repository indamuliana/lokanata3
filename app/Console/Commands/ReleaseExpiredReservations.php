<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Command;

class ReleaseExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:release-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Lepaskan inventori dari booking website pending yang telah melebihi masa tahan 2 jam';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $count = Reservation::releaseExpired();
        $this->info("Berhasil memeriksa dan melepaskan {$count} reservasi yang kedaluwarsa.");
        return Command::SUCCESS;
    }
}
