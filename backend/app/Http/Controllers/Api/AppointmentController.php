<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class AppointmentController extends Controller
{
    public function index(Request $req)
    {
        // filter: ongoing | history
        $filter = $req->query('filter','ongoing');
        $now = now();

        if ($filter === 'ongoing') {
            $appts = Appointment::where('user_id', Auth::id())
                ->where('status', 'scheduled')
                ->where('scheduled_at', '>=', $now)
                ->orderBy('scheduled_at')->get();
        } else {
            $appts = Appointment::where('user_id', Auth::id())
                ->where(function($q) use ($now){
                    $q->where('scheduled_at','<',$now)
                      ->orWhere('status','canceled');
                })
                ->orderByDesc('scheduled_at')->get();
        }
        return response()->json($appts);
    }

    public function store(Request $req)
    {
        $data = $req->validate([
            'item_type'=>'required|string',
            'quantity'=>'required|integer|min:1',
            'volume'=>'required|numeric|min:0',
            'scheduled_at'=>'required|date'
        ]);
        $data['user_id'] = Auth::id();
        $appt = Appointment::create($data);
        return response()->json($appt,201);
    }

    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);
        return response()->json($appointment);
    }

    public function update(Request $req, Appointment $appointment)
    {
        $this->authorize('update', $appointment);
        $data = $req->validate([
            'scheduled_at'=>'sometimes|date',
            'quantity'=>'sometimes|integer|min:1',
            'item_type'=>'sometimes|string',
        ]);
        $appointment->update($data);
        return response()->json($appointment);
    }

    public function destroy(Appointment $appointment)
    {
        $this->authorize('delete', $appointment);
        // soft-cancel: set status to canceled
        $appointment->update(['status' => 'canceled']);
        return response()->json(['message'=>'canceled']);
    }
}
