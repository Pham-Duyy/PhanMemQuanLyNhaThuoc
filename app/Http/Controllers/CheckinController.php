<?php
namespace App\Http\Controllers;

use App\Models\CheckinLog;
use App\Models\ShiftAssignment;
use App\Models\User;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;

class CheckinController extends Controller
{
    // ── Trang chấm công (public — không cần login) ────────────────────
    public function index()
    {
        // Lấy pharmacy từ subdomain hoặc session
        // Trong demo dùng pharmacy_id=1 hoặc từ auth nếu có
        $pharmacyId = auth()->check() ? auth()->user()->pharmacy_id : 1;

        // Danh sách nhân viên có ca hôm nay
        $todayStaff = ShiftAssignment::where('pharmacy_id', $pharmacyId)
            ->whereDate('work_date', today())
            ->with('user:id,name,position,work_pin', 'shift:id,name,color,start_time,end_time')
            ->get()
            ->map(function ($a) {
                $log = CheckinLog::where('user_id', $a->user_id)
                    ->whereDate('work_date', today())
                    ->first();
                return [
                    'assignment' => $a,
                    'user' => $a->user,
                    'shift' => $a->shift,
                    'log' => $log,
                    'has_pin' => !empty($a->user->work_pin),
                ];
            });

        // Nhân viên đang check-in hôm nay
        $checkedIn = CheckinLog::where('pharmacy_id', $pharmacyId)
            ->whereDate('work_date', today())
            ->where('status', 'checked_in')
            ->with('user:id,name,position')
            ->get();

        $now = Carbon::now();

        return view('checkin.index', compact('todayStaff', 'checkedIn', 'now', 'pharmacyId'));
    }

    // ── Xử lý Check-IN ───────────────────────────────────────────────
    public function checkIn(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'pin' => 'required|string|min:4|max:6',
            'pharmacy_id' => 'required|integer',
        ]);

        $user = User::findOrFail($request->user_id);

        // Xác thực PIN
        if (empty($user->work_pin) || $user->work_pin !== $request->pin) {
            return response()->json(['success' => false, 'message' => 'Mã PIN không đúng! Vui lòng thử lại.']);
        }

        // Kiểm tra đã check-in chưa
        $existing = CheckinLog::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        if ($existing && $existing->status === 'checked_in') {
            return response()->json([
                'success' => false,
                'message' => $user->name . ' đã check-in lúc ' . $existing->checkin_time . '. Dùng Check-OUT để ra về.',
                'already' => true,
            ]);
        }

        if ($existing && $existing->status === 'checked_out') {
            return response()->json([
                'success' => false,
                'message' => $user->name . ' đã hoàn thành ca hôm nay.',
            ]);
        }

        // Tìm ca hôm nay của nhân viên
        $assignment = ShiftAssignment::where('user_id', $user->id)
            ->where('pharmacy_id', $request->pharmacy_id)
            ->whereDate('work_date', today())
            ->with('shift')
            ->first();

        $now = Carbon::now();
        $lateMinutes = 0;
        $shiftName = 'Không có ca';

        if ($assignment && $assignment->shift) {
            $shiftStart = Carbon::parse($now->format('Y-m-d') . ' ' . $assignment->shift->start_time);
            $lateMinutes = max(0, $now->diffInMinutes($shiftStart, false) * -1);
            $shiftName = $assignment->shift->name;

            // Cập nhật trạng thái assignment
            $status = $lateMinutes > 10 ? 'late' : 'confirmed';
            $assignment->update([
                'status' => $status,
                'actual_start' => $now->format('H:i'),
            ]);
        }

        // Tạo checkin log
        CheckinLog::create([
            'pharmacy_id' => $request->pharmacy_id,
            'user_id' => $user->id,
            'assignment_id' => $assignment?->id,
            'work_date' => today(),
            'checkin_time' => $now->format('H:i:s'),
            'late_minutes' => $lateMinutes,
            'status' => 'checked_in',
        ]);

        $msg = '✅ ' . $user->name . ' đã CHECK-IN lúc ' . $now->format('H:i');
        if ($lateMinutes > 0) {
            $msg .= ' — ⚠️ Muộn ' . $lateMinutes . ' phút';
        } else {
            $msg .= ' — Đúng giờ 👍';
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'name' => $user->name,
            'time' => $now->format('H:i'),
            'late_minutes' => $lateMinutes,
            'shift' => $shiftName,
        ]);
    }

    // ── Xử lý Check-OUT ──────────────────────────────────────────────
    public function checkOut(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'pin' => 'required|string|min:4|max:6',
        ]);

        $user = User::findOrFail($request->user_id);

        if (empty($user->work_pin) || $user->work_pin !== $request->pin) {
            return response()->json(['success' => false, 'message' => 'Mã PIN không đúng!']);
        }

        $log = CheckinLog::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->where('status', 'checked_in')
            ->first();

        if (!$log) {
            return response()->json(['success' => false, 'message' => $user->name . ' chưa check-in hôm nay.']);
        }

        $now = Carbon::now();
        $checkinTime = Carbon::parse($now->format('Y-m-d') . ' ' . $log->checkin_time);
        $actualHours = round($now->diffInMinutes($checkinTime) / 60, 1);
        $earlyMinutes = 0;

        // Tính về sớm
        $assignment = $log->assignment;
        if ($assignment && $assignment->shift) {
            $shiftEnd = Carbon::parse($now->format('Y-m-d') . ' ' . $assignment->shift->end_time);
            $earlyMinutes = max(0, $shiftEnd->diffInMinutes($now, false));

            // Cập nhật assignment
            $assignment->update([
                'actual_end' => $now->format('H:i'),
                'actual_hours' => $actualHours,
                'status' => 'completed',
            ]);
        }

        $log->update([
            'checkout_time' => $now->format('H:i:s'),
            'actual_hours' => $actualHours,
            'early_minutes' => $earlyMinutes,
            'status' => 'checked_out',
        ]);

        $msg = '👋 ' . $user->name . ' đã CHECK-OUT lúc ' . $now->format('H:i');
        $msg .= ' — Làm được ' . $actualHours . ' giờ';
        if ($earlyMinutes > 10) {
            $msg .= ' — ⚠️ Về sớm ' . $earlyMinutes . ' phút';
        }

        return response()->json([
            'success' => true,
            'message' => $msg,
            'name' => $user->name,
            'time' => $now->format('H:i'),
            'actual_hours' => $actualHours,
            'early_minutes' => $earlyMinutes,
        ]);
    }

    // ── Cài PIN (manager set PIN cho nhân viên) ───────────────────────
    public function setPin(Request $request, User $user)
    {
        $request->validate(['pin' => 'required|string|min:4|max:6|regex:/^[0-9]+$/']);

        // QUAN TRỌNG: Dùng DB::table() thay vì Eloquent save()
        // Lý do: forceFill()->save() kích hoạt Eloquent events + $casts
        // → Laravel 11 có 'password' => 'hashed' trong $casts
        // → save() có thể re-process password nếu model instance
        //   đang giữ password trong $attributes → hash 2 lần → mất login
        //
        // DB::table()->update() ghi TRỰC TIẾP vào DB, chỉ cột work_pin,
        // không đụng vào password, không trigger bất kỳ event nào.
        \DB::table('users')
            ->where('id', $user->id)
            ->update([
                'work_pin' => $request->pin,
                'updated_at' => now(),
            ]);

        ActivityLog::log('update', 'user', $user->id, 'Cài PIN chấm công cho: ' . $user->name);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Đã cài PIN thành công cho ' . $user->name,
            ]);
        }

        return back()->with('success', 'Đã cài PIN cho ' . $user->name . '.');
    }

    // ── Lịch sử chấm công (manager xem) ──────────────────────────────
    public function history(Request $request)
    {
        $pharmacyId = auth()->user()->pharmacy_id;
        $date = $request->get('date', today()->format('Y-m-d'));

        $logs = CheckinLog::where('pharmacy_id', $pharmacyId)
            ->whereDate('work_date', $date)
            ->with(['user:id,name,position', 'assignment.shift:id,name,color'])
            ->orderBy('checkin_time')
            ->get();

        if ($request->expectsJson()) {
            return response()->json(['logs' => $logs]);
        }

        return view('checkin.history', compact('logs', 'date'));
    }
}