<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    /**
     * Get appointments: ongoing or history
     * Query: ?filter=ongoing|history
     */
    public function index(Request $req)
    {
        // Automatically update expired appointments
        AppointmentService::updateExpiredAppointments();

        $filter = $req->query('filter', 'ongoing');
        $userId = Auth::id();
        $now = Carbon::now();

        if ($filter === 'ongoing') {
            // Ongoing: status ongoing dan belum lewat jam pickup
            $appts = Appointment::where('user_id', $userId)
                ->where('status', 'ongoing')
                ->where(function($q) use ($now) {
                    // Jika tanggal sama dengan hari ini, cek waktu
                    $q->where('pickup_date', '>', $now->format('Y-m-d'))
                      ->orWhere(function($subQ) use ($now) {
                          $subQ->where('pickup_date', '=', $now->format('Y-m-d'))
                               ->where('pickup_time', '>=', $now->format('H:i'));
                      });
                })
                ->orderBy('pickup_date')
                ->orderBy('pickup_time')
                ->get();
        } else {
            // History: status completed, cancelled, atau sudah lewat jam
            $appts = Appointment::where('user_id', $userId)
                ->where(function($q) use ($now) {
                    // Completed atau cancelled
                    $q->where('status', '!=', 'ongoing')
                      // Atau sudah lewat jam (meskipun status ongoing)
                      ->orWhere(function($subQ) use ($now) {
                          $subQ->where('status', 'ongoing')
                               ->where(function($timeQ) use ($now) {
                                   $timeQ->where('pickup_date', '<', $now->format('Y-m-d'))
                                         ->orWhere(function($sameDay) use ($now) {
                                             $sameDay->where('pickup_date', '=', $now->format('Y-m-d'))
                                                     ->where('pickup_time', '<', $now->format('H:i'));
                                         });
                               });
                      });
                })
                ->orderByDesc('pickup_date')
                ->orderByDesc('pickup_time')
                ->get();
        }

        return response()->json($appts);
    }

    /**
     * Create new appointment
     */
    public function store(Request $req)
    {
        $data = $req->validate([
            'item_type' => 'required|string|max:255',
            'quantity' => 'required|integer|min:1',
            'volume' => 'required|numeric|min:0.1',
            'pickup_date' => 'required|date|after_or_equal:today',
            'pickup_time' => 'required|date_format:H:i',
        ]);

        $appt = Appointment::create([
            ...$data,
            'user_id' => Auth::id(),
            'status' => 'ongoing'
        ]);

        return response()->json($appt, 201);
    }

    /**
     * Get single appointment detail
     */
    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);
        return response()->json($appointment);
    }

    /**
     * Update appointment (reschedule or add quantity)
     */
    public function update(Request $req, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        // Hanya bisa update jika masih ongoing
        if ($appointment->status !== 'ongoing') {
            return response()->json(['message' => 'Hanya dapat mengupdate appointment yang masih ongoing'], 400);
        }

        $data = $req->validate([
            'pickup_date' => 'sometimes|date|after_or_equal:today',
            'pickup_time' => 'sometimes|date_format:H:i',
            'quantity' => 'sometimes|integer|min:1',
            'item_type' => 'sometimes|string|max:255',
            'volume' => 'sometimes|numeric|min:0.1'
        ]);

        $appointment->update($data);

        return response()->json($appointment);
    }

    /**
     * Cancel appointment
     */
    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);

        // Hanya bisa cancel jika masih ongoing
        if ($appointment->status !== 'ongoing') {
            return response()->json(['message' => 'Hanya dapat membatalkan appointment yang masih ongoing'], 400);
        }

        $appointment->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Appointment berhasil dibatalkan']);
    }
}
