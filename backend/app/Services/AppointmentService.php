<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;

class AppointmentService
{
    /**
     * Update expired appointments to completed status
     * Call this method periodically to move past appointments to history
     */
    public static function updateExpiredAppointments()
    {
        $now = Carbon::now();

        $expiredAppointments = Appointment::where('status', 'ongoing')
            ->where(function ($query) use ($now) {
                // Tanggal sudah lewat
                $query->where('pickup_date', '<', $now->format('Y-m-d'))
                    // Atau tanggal sama tapi waktu sudah lewat
                    ->orWhere(function ($subQuery) use ($now) {
                        $subQuery->where('pickup_date', '=', $now->format('Y-m-d'))
                            ->where('pickup_time', '<', $now->format('H:i'));
                    });
            })
            ->update(['status' => 'completed']);

        return $expiredAppointments;
    }
}
