@extends('layouts.app')
@section('title','Lịch ca của tôi')
@section('page-title','Lịch làm việc của tôi')

@section('content')
{{-- Bộ lọc tháng --}}
<div class="d-flex gap-2 align-items-center mb-3 flex-wrap">
    <a href="{{ route('shifts.my-schedule', ['month'=>$month-1<1?12:$month-1,'year'=>$month-1<1?$year-1:$year]) }}"
       class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-left"></i></a>
    <h5 class="mb-0 fw-bold px-2">Tháng {{ $month }}/{{ $year }}</h5>
    <a href="{{ route('shifts.my-schedule', ['month'=>$month+1>12?1:$month+1,'year'=>$month+1>12?$year+1:$year]) }}"
       class="btn btn-sm btn-outline-secondary"><i class="bi bi-chevron-right"></i></a>
    <a href="{{ route('shifts.my-schedule') }}" class="btn btn-sm btn-outline-primary">Tháng này</a>
</div>

{{-- Stats --}}
<div class="row g-3 mb-3">
    @foreach([
        ['total','primary','📅','Tổng ca'],
        ['completed','success','✅','Hoàn thành'],
        ['absent','danger','❌','Vắng'],
        ['upcoming','info','⏳','Ca sắp tới'],
    ] as [$key,$color,$ico,$lbl])
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm text-center py-3">
            <div class="fw-bold fs-2 text-{{ $color }}">{{ $stats[$key] }}</div>
            <div class="text-muted small">{{ $ico }} {{ $lbl }}</div>
        </div>
    </div>
    @endforeach
</div>

<div class="row g-3">
    {{-- Lịch tháng --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header py-3 px-4 border-0 bg-white">
                <h6 class="mb-0 fw-bold">📅 Lịch tháng {{ $month }}</h6>
            </div>
            <div class="card-body p-3">
                {{-- Header ngày trong tuần --}}
                <div class="row g-1 mb-2">
                    @foreach(['T2','T3','T4','T5','T6','T7','CN'] as $d)
                    <div class="col text-center">
                        <span class="small fw-bold text-muted">{{ $d }}</span>
                    </div>
                    @endforeach
                </div>

                @php
                    $firstDay  = $from->copy()->dayOfWeek; // 0=Sun
                    $firstDay  = $firstDay === 0 ? 6 : $firstDay - 1; // convert to Mon=0
                    $shiftsByDay = $myShifts->keyBy(fn($s) => $s->work_date->format('j'));
                @endphp

                <div class="row g-1">
                    {{-- Padding đầu tháng --}}
                    @for($i = 0; $i < $firstDay; $i++)
                    <div class="col" style="aspect-ratio:1;"></div>
                    @endfor

                    @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $a = $shiftsByDay->get($day);
                        $isToday = $from->copy()->setDay($day)->isToday();
                        $isPast  = $from->copy()->setDay($day)->isPast();
                    @endphp
                    <div class="col">
                        <div class="rounded-2 p-1 text-center h-100"
                             style="min-height:64px;
                                    {{ $isToday ? 'background:#DBEAFE;border:2px solid #3B82F6;' : ($a ? 'background:'.$a->shift->color.'15;border:1px solid '.$a->shift->color.';' : 'background:#F8FAFC;border:1px solid #E2E8F0;') }}">
                            <div class="fw-bold {{ $isToday ? 'text-primary' : ($isPast ? 'text-muted' : '') }}"
                                 style="font-size:12px;">{{ $day }}</div>
                            @if($a)
                            <div class="mt-1">
                                <span class="badge rounded-pill px-1" style="font-size:9px;background:{{ $a->shift->color }};color:#fff;">
                                    {{ $a->shift->name }}
                                </span>
                                <div style="font-size:9px;color:#64748B;margin-top:1px;">
                                    {{ substr($a->shift->start_time,0,5) }}
                                </div>
                                @if($a->status==='completed')
                                <div style="font-size:11px;">✅</div>
                                @elseif($a->status==='absent')
                                <div style="font-size:11px;">❌</div>
                                @elseif($a->status==='late')
                                <div style="font-size:11px;">⏰</div>
                                @endif
                            </div>
                            @endif
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>
    </div>

    {{-- Danh sách + lương --}}
    <div class="col-lg-4">
        {{-- Bảng lương tháng này --}}
        @if($myPayroll)
        <div class="card border-0 shadow-sm mb-3" style="border-left:4px solid #16A34A!important;">
            <div class="card-header py-2 px-3 border-0 bg-success-subtle">
                <h6 class="mb-0 fw-bold text-success small">💰 Lương tháng {{ $month }}/{{ $year }}</h6>
            </div>
            <div class="card-body px-3 py-3">
                @foreach([
                    ['Lương cơ bản', number_format($myPayroll->base_salary,0,',','.').'đ', ''],
                    ['Lương ca', number_format($myPayroll->shift_salary,0,',','.').'đ', 'text-success'],
                    ['Thưởng', number_format($myPayroll->bonus,0,',','.').'đ', 'text-info'],
                    ['Khấu trừ', '-'.number_format($myPayroll->deduction,0,',','.').'đ', 'text-danger'],
                ] as [$lbl,$val,$cls])
                <div class="d-flex justify-content-between py-1 border-bottom small">
                    <span class="text-muted">{{ $lbl }}</span>
                    <span class="fw-semibold {{ $cls }}">{{ $val }}</span>
                </div>
                @endforeach
                <div class="d-flex justify-content-between pt-2 mt-1">
                    <span class="fw-bold">Thực lĩnh</span>
                    <span class="fw-bold text-success fs-5">{{ number_format($myPayroll->net_salary,0,',','.')}}đ</span>
                </div>
                <div class="mt-2">
                    <span class="badge bg-{{ $myPayroll->status_color }}">{{ $myPayroll->status_label }}</span>
                </div>
            </div>
        </div>
        @endif

        {{-- Danh sách ca sắp tới --}}
        <div class="card border-0 shadow-sm">
            <div class="card-header py-2 px-3 border-0 bg-white">
                <h6 class="mb-0 fw-bold small">⏳ Ca sắp tới</h6>
            </div>
            <div class="card-body p-0">
                @php $upcoming = $myShifts->where('work_date', '>=', today())->where('status','scheduled')->take(8); @endphp
                @forelse($upcoming as $a)
                <div class="d-flex align-items-center gap-3 px-3 py-2 border-bottom">
                    <div class="text-center rounded-2 px-2 py-1"
                         style="background:{{ $a->shift->color }}20;min-width:44px;">
                        <div class="fw-bold" style="font-size:15px;color:{{ $a->shift->color }};">
                            {{ $a->work_date->format('d') }}
                        </div>
                        <div style="font-size:9px;color:#64748B;">
                            {{ ['CN','T2','T3','T4','T5','T6','T7'][$a->work_date->dayOfWeek] }}
                        </div>
                    </div>
                    <div class="flex-fill">
                        <div class="fw-semibold small">{{ $a->shift->name }}</div>
                        <div class="text-muted" style="font-size:11px;">
                            {{ substr($a->shift->start_time,0,5) }} – {{ substr($a->shift->end_time,0,5) }}
                        </div>
                    </div>
                    <span class="badge" style="background:{{ $a->shift->color }}20;color:{{ $a->shift->color }};border:1px solid {{ $a->shift->color }};">
                        {{ $a->shift->hours }}h
                    </span>
                </div>
                @empty
                <div class="text-center py-4 text-muted small">
                    <i class="bi bi-calendar-check fs-3 d-block mb-1"></i>
                    Không có ca nào sắp tới
                </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection