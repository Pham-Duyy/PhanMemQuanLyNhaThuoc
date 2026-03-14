<?php
namespace App\Http\Controllers;

use App\Models\WorkShift;
use App\Models\ShiftAssignment;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ShiftController extends Controller
{
    private function pid(): int
    {
        return auth()->user()->pharmacy_id;
    }

    // ── 1. Danh sách ca + lịch tuần ──────────────────────────────────────
    public function index(Request $request)
    {
        $pid = $this->pid();

        // Tuần hiện tại hoặc tuần được chọn
        $weekStart = $request->filled('week')
            ? Carbon::parse($request->week)->startOfWeek(Carbon::MONDAY)
            : Carbon::now()->startOfWeek(Carbon::MONDAY);
        $weekEnd = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

        $shifts = WorkShift::where('pharmacy_id', $pid)
            ->where('is_active', true)
            ->orderBy('start_time')
            ->get();

        $staff = User::where('pharmacy_id', $pid)
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'position']);

        // Load tất cả assignments trong tuần
        $assignments = ShiftAssignment::where('pharmacy_id', $pid)
            ->whereBetween('work_date', [$weekStart, $weekEnd])
            ->with(['user:id,name', 'shift:id,name,color,start_time,end_time'])
            ->get()
            ->groupBy(fn($a) => $a->user_id . '_' . $a->work_date->format('Y-m-d'));

        // Thống kê nhanh hôm nay
        $todayStats = ShiftAssignment::where('pharmacy_id', $pid)
            ->whereDate('work_date', today())
            ->selectRaw('status, count(*) as cnt')
            ->groupBy('status')
            ->pluck('cnt', 'status');

        return view('shifts.index', compact(
            'shifts',
            'staff',
            'assignments',
            'weekStart',
            'weekEnd',
            'todayStats'
        ));
    }

    // ── 2. Xếp ca (AJAX / form) ──────────────────────────────────────────
    public function assign(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'shift_id' => 'required|exists:work_shifts,id',
            'work_date' => 'required|date',
        ]);

        $pid = $this->pid();
        $shift = WorkShift::findOrFail($request->shift_id);

        $assignment = ShiftAssignment::updateOrCreate(
            [
                'pharmacy_id' => $pid,
                'user_id' => $request->user_id,
                'shift_id' => $request->shift_id,
                'work_date' => $request->work_date,
            ],
            [
                'status' => 'scheduled',
                'wage_amount' => $shift->shift_wage,
                'assigned_by' => auth()->id(),
                'note' => $request->note,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json(['success' => true, 'assignment' => $assignment->load('shift')]);
        }
        return back()->with('success', 'Đã xếp ca thành công.');
    }

    // ── 3. Xếp ca tự động cả tuần ────────────────────────────────────────
    public function autoAssign(Request $request)
    {
        $request->validate([
            'week' => 'required|date',
            'pattern' => 'required|in:rotate,fixed,random',
        ]);

        $pid = $this->pid();
        $weekStart = Carbon::parse($request->week)->startOfWeek(Carbon::MONDAY);
        $shifts = WorkShift::where('pharmacy_id', $pid)->where('is_active', true)
            ->orderBy('start_time')->get();
        $staff = User::where('pharmacy_id', $pid)->where('is_active', true)->get();

        if ($shifts->isEmpty() || $staff->isEmpty()) {
            return back()->with('error', 'Chưa có ca hoặc nhân viên.');
        }

        $created = 0;
        DB::transaction(function () use ($pid, $weekStart, $shifts, $staff, $request, &$created) {
            foreach ($staff as $idx => $user) {
                for ($d = 0; $d < 6; $d++) { // Thứ 2 - Thứ 7
                    $date = $weekStart->copy()->addDays($d);

                    if ($request->pattern === 'rotate') {
                        // Xoay ca: mỗi nhân viên 1 ca, xoay theo tuần
                        $shift = $shifts[($idx + $d) % $shifts->count()];
                    } elseif ($request->pattern === 'fixed') {
                        // Cố định: mỗi nhân viên luôn cùng ca
                        $shift = $shifts[$idx % $shifts->count()];
                    } else {
                        // Random
                        $shift = $shifts->random();
                    }

                    $exists = ShiftAssignment::where([
                        'pharmacy_id' => $pid,
                        'user_id' => $user->id,
                        'work_date' => $date->format('Y-m-d'),
                    ])->exists();

                    if (!$exists) {
                        ShiftAssignment::create([
                            'pharmacy_id' => $pid,
                            'user_id' => $user->id,
                            'shift_id' => $shift->id,
                            'work_date' => $date->format('Y-m-d'),
                            'status' => 'scheduled',
                            'wage_amount' => $shift->shift_wage,
                            'assigned_by' => auth()->id(),
                        ]);
                        $created++;
                    }
                }
            }
        });

        return back()->with('success', "Đã tự động xếp $created ca cho tuần " . $weekStart->format('d/m/Y') . '.');
    }

    // ── 4. Cập nhật trạng thái ca ────────────────────────────────────────
    public function updateStatus(Request $request, ShiftAssignment $assignment)
    {
        $request->validate([
            'status' => 'required|in:scheduled,confirmed,completed,absent,late',
            'actual_start' => 'nullable|date_format:H:i',
            'actual_end' => 'nullable|date_format:H:i',
        ]);

        $data = ['status' => $request->status];

        if ($request->filled('actual_start')) {
            $data['actual_start'] = $request->actual_start;
        }
        if ($request->filled('actual_end')) {
            $data['actual_end'] = $request->actual_end;
            if ($request->filled('actual_start')) {
                $start = Carbon::parse($request->actual_start);
                $end = Carbon::parse($request->actual_end);
                $hours = $end->diffInMinutes($start) / 60;
                $data['actual_hours'] = round($hours, 1);
            }
        }

        $assignment->update($data);

        if ($request->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Cập nhật trạng thái ca thành công.');
    }

    // ── 5. Xóa ca đã xếp ────────────────────────────────────────────────
    public function removeAssignment(ShiftAssignment $assignment)
    {
        $assignment->delete();
        if (request()->expectsJson()) {
            return response()->json(['success' => true]);
        }
        return back()->with('success', 'Đã xóa ca.');
    }

    // ── 6. Báo cáo chấm công tháng ──────────────────────────────────────
    public function attendance(Request $request)
    {
        $pid = $this->pid();
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $staff = User::where('pharmacy_id', $pid)
            ->where('is_active', true)
            ->with([
                'shifts' => function ($q) use ($from, $to) {
                    $q->whereBetween('work_date', [$from, $to])
                        ->with('shift:id,name,hours,shift_wage');
                }
            ])
            ->orderBy('name')
            ->get();

        // Tổng hợp từng nhân viên
        $summary = $staff->map(function ($user) use ($month, $year) {
            $assignments = $user->shifts;
            return [
                'user' => $user,
                'total_shifts' => $assignments->count(),
                'completed' => $assignments->where('status', 'completed')->count(),
                'absent' => $assignments->where('status', 'absent')->count(),
                'late' => $assignments->where('status', 'late')->count(),
                'total_hours' => $assignments->whereIn('status', ['completed', 'late'])
                    ->sum('actual_hours') ?:
                    $assignments->whereIn('status', ['completed', 'late'])
                        ->sum(fn($a) => $a->shift?->hours ?? 0),
                'total_wage' => $assignments->whereIn('status', ['completed', 'late'])
                    ->sum('wage_amount'),
            ];
        });

        // Lịch chấm công chi tiết (cho bảng ngày × nhân viên)
        $calendar = ShiftAssignment::where('pharmacy_id', $pid)
            ->whereBetween('work_date', [$from, $to])
            ->with(['user:id,name', 'shift:id,name,color'])
            ->get()
            ->groupBy(fn($a) => $a->work_date->format('d'));

        $daysInMonth = $from->daysInMonth;

        return view('shifts.attendance', compact(
            'staff',
            'summary',
            'calendar',
            'month',
            'year',
            'daysInMonth',
            'from'
        ));
    }

    // ── 7. Tính bảng lương ──────────────────────────────────────────────
    public function payroll(Request $request)
    {
        $pid = $this->pid();
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);

        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $payrolls = Payroll::where('pharmacy_id', $pid)
            ->where('month', $month)->where('year', $year)
            ->with('user:id,name,position,base_salary')
            ->get()
            ->keyBy(fn($p) => (int) $p->user_id); // cast int để match $user->id

        $staff = User::where('pharmacy_id', $pid)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        return view('shifts.payroll', compact('payrolls', 'staff', 'month', 'year', 'from'));
    }

    // ── 8. Tạo/tính lại bảng lương ──────────────────────────────────────
    public function generatePayroll(Request $request)
    {
        $request->validate([
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020',
        ]);

        $pid = $this->pid();
        $month = (int) $request->month;
        $year = (int) $request->year;
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $staff = User::where('pharmacy_id', $pid)->where('is_active', true)->get();

        if ($staff->isEmpty()) {
            return back()->with('error', 'Không có nhân viên nào để tính lương.');
        }

        $count = 0;

        DB::transaction(function () use ($staff, $pid, $month, $year, $from, $to, &$count) {
            foreach ($staff as $user) {
                // Load cả relationship 'shift' để tính giờ/lương ca đúng
                $assignments = ShiftAssignment::where('pharmacy_id', $pid)
                    ->where('user_id', $user->id)
                    ->whereBetween('work_date', [$from, $to])
                    ->with('shift:id,hours,shift_wage')
                    ->get();

                $totalShifts = $assignments->count();
                $absentDays = $assignments->where('status', 'absent')->count();
                $lateDays = $assignments->where('status', 'late')->count();

                // Chỉ tính ca đã hoàn thành hoặc trễ (có làm việc)
                $worked = $assignments->whereIn('status', ['completed', 'late']);

                // Tổng giờ: ưu tiên actual_hours, fallback sang shift.hours
                $totalHours = $worked->sum(function ($a) {
                    return $a->actual_hours ?? ($a->shift?->hours ?? 0);
                });

                // Tổng lương ca: ưu tiên wage_amount đã ghi, fallback sang shift.shift_wage
                $shiftSalary = $worked->sum(function ($a) {
                    return $a->wage_amount > 0
                        ? $a->wage_amount
                        : ($a->shift?->shift_wage ?? 0);
                });

                $baseSalary = (float) ($user->base_salary ?? 0);
                $netSalary = $baseSalary + $shiftSalary;

                Payroll::updateOrCreate(
                    [
                        'pharmacy_id' => $pid,
                        'user_id' => $user->id,
                        'month' => $month,
                        'year' => $year,
                    ],
                    [
                        'total_shifts' => $totalShifts,
                        'total_hours' => round($totalHours, 1),
                        'absent_days' => $absentDays,
                        'late_days' => $lateDays,
                        'base_salary' => $baseSalary,
                        'shift_salary' => round($shiftSalary, 0),
                        'net_salary' => round($netSalary, 0),
                        'status' => 'draft',
                    ]
                );
                $count++;
            }
        });

        return back()->with('success', "✅ Đã tính bảng lương cho $count nhân viên — Tháng $month/$year.");
    }

    // ── 9. Duyệt / xác nhận đã trả lương ────────────────────────────────
    public function confirmPayroll(Request $request, Payroll $payroll)
    {
        $request->validate(['action' => 'required|in:confirm,pay']);

        if ($request->action === 'confirm') {
            $payroll->update([
                'status' => 'confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);
            $msg = 'Đã duyệt bảng lương.';
        } else {
            $payroll->update(['status' => 'paid']);
            $msg = 'Đã đánh dấu đã trả lương.';
        }

        return back()->with('success', $msg);
    }

    // ── 10. Quản lý ca (CRUD) ────────────────────────────────────────────
    public function shifts()
    {
        $shifts = WorkShift::where('pharmacy_id', $this->pid())
            ->withCount(['assignments as total_used' => fn($q) => $q->whereMonth('work_date', now()->month)])
            ->orderBy('start_time')->get();

        return view('shifts.manage', compact('shifts'));
    }

    public function storeShift(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'shift_wage' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:20',
            'note' => 'nullable|string',
        ]);

        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);
        $hours = $end->diffInMinutes($start) / 60;

        WorkShift::create(array_merge($data, [
            'pharmacy_id' => $this->pid(),
            'hours' => round($hours, 1),
            'color' => $data['color'] ?? '#0EA5A0',
        ]));

        return back()->with('success', 'Đã tạo ca làm việc.');
    }

    public function updateShift(Request $request, WorkShift $shift)
    {
        $data = $request->validate([
            'name' => 'required|string|max:50',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i',
            'shift_wage' => 'required|numeric|min:0',
            'color' => 'nullable|string|max:20',
            'is_active' => 'boolean',
            'note' => 'nullable|string',
        ]);

        $start = Carbon::parse($data['start_time']);
        $end = Carbon::parse($data['end_time']);
        $shift->update(array_merge($data, ['hours' => round($end->diffInMinutes($start) / 60, 1)]));

        return back()->with('success', 'Đã cập nhật ca.');
    }

    // ── 11. Nhân viên xem lịch ca của mình ──────────────────────────────
    public function mySchedule(Request $request)
    {
        $user = auth()->user();
        $month = (int) $request->get('month', now()->month);
        $year = (int) $request->get('year', now()->year);
        $from = Carbon::create($year, $month, 1)->startOfMonth();
        $to = $from->copy()->endOfMonth();

        $myShifts = ShiftAssignment::where('user_id', $user->id)
            ->whereBetween('work_date', [$from, $to])
            ->with('shift:id,name,color,start_time,end_time,shift_wage')
            ->orderBy('work_date')
            ->get();

        $myPayroll = Payroll::where('user_id', $user->id)
            ->where('month', $month)->where('year', $year)
            ->first();

        $daysInMonth = $from->daysInMonth;

        $stats = [
            'total' => $myShifts->count(),
            'completed' => $myShifts->where('status', 'completed')->count(),
            'absent' => $myShifts->where('status', 'absent')->count(),
            'upcoming' => $myShifts->where('work_date', '>=', today())->where('status', 'scheduled')->count(),
        ];

        return view('shifts.my_schedule', compact('myShifts', 'myPayroll', 'month', 'year', 'from', 'stats', 'daysInMonth'));
    }
}