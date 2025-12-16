<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UpdateExpiredAppointments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'appointments:update-expired';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update appointments status to completed if the pickup time has passed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $now = Carbon::now();

        $expiredAppointments = Appointment::where('status', 'ongoing')
            ->where(function ($query) use ($now) {
                // Jika tanggal sudah lewat
                $query->where('pickup_date', '<', $now->format('Y-m-d'))
                    // Atau tanggal sama tapi waktu sudah lewat
                    ->orWhere(function ($subQuery) use ($now) {
                        $subQuery->where('pickup_date', '=', $now->format('Y-m-d'))
                            ->where('pickup_time', '<', $now->format('H:i'));
                    });
            })
            ->get();

        $count = $expiredAppointments->count();

        foreach ($expiredAppointments as $appointment) {
            $appointment->update(['status' => 'completed']);
        }

        $this->info("Updated {$count} expired appointments to 'completed' status.");

        return 0;
    }
}
