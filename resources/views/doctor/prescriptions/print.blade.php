<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>Prescription #{{ $prescription->prescription_number }}</title>
    @php
        $layout = [
            'paper_size' => $doctorSetting->paper_size ?? 'A4',
            'page_w' => $doctorSetting->paper_width_mm ?? 210,
            'page_h' => $doctorSetting->paper_height_mm ?? 297,
            'h_mt' => $doctorSetting->header_margin_top_mm ?? 0,
            'h_mr' => $doctorSetting->header_margin_right_mm ?? 0,
            'h_mb' => $doctorSetting->header_margin_bottom_mm ?? 0,
            'h_ml' => $doctorSetting->header_margin_left_mm ?? 0,
            'h_pt' => $doctorSetting->header_padding_top_mm ?? 5,
            'h_pr' => $doctorSetting->header_padding_right_mm ?? 9,
            'h_pb' => $doctorSetting->header_padding_bottom_mm ?? 5,
            'h_pl' => $doctorSetting->header_padding_left_mm ?? 9,
            'f_mt' => $doctorSetting->footer_margin_top_mm ?? 0,
            'f_mr' => $doctorSetting->footer_margin_right_mm ?? 0,
            'f_mb' => $doctorSetting->footer_margin_bottom_mm ?? 0,
            'f_ml' => $doctorSetting->footer_margin_left_mm ?? 0,
            'f_pt' => $doctorSetting->footer_padding_top_mm ?? 4,
            'f_pr' => $doctorSetting->footer_padding_right_mm ?? 7,
            'f_pb' => $doctorSetting->footer_padding_bottom_mm ?? 4,
            'f_pl' => $doctorSetting->footer_padding_left_mm ?? 7,
        ];
        $paperMap = ['A4' => ['w' => 210, 'h' => 297], 'A5' => ['w' => 148, 'h' => 210], 'Letter' => ['w' => 216, 'h' => 279]];
        $pw = $paperMap[$layout['paper_size']]['w'] ?? $layout['page_w'];
        $ph = $paperMap[$layout['paper_size']]['h'] ?? $layout['page_h'];
        if ($layout['paper_size'] === 'Custom') { $pw = $layout['page_w']; $ph = $layout['page_h']; }
    @endphp
    <style>
        @media print {
            body { margin: 0; padding: 0; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            @page { size: {{ $layout['paper_size'] === 'Custom' ? $pw . 'mm ' . $ph . 'mm' : $layout['paper_size'] }}; margin: 0; }
        }
        body { margin: 0; padding: 0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f5f5f5; display: flex; justify-content: center; }
        .page { width: {{ $pw }}mm; min-height: {{ $ph }}mm; background-color: #fff; box-sizing: border-box; display: flex; flex-direction: column; box-shadow: 0 0 10px rgba(0,0,0,0.1); overflow: hidden; }
        .header { background-color: transparent; padding: {{ $layout['h_pt'] }}mm {{ $layout['h_pr'] }}mm {{ $layout['h_pb'] }}mm {{ $layout['h_pl'] }}mm; margin: {{ $layout['h_mt'] }}mm {{ $layout['h_mr'] }}mm {{ $layout['h_mb'] }}mm {{ $layout['h_ml'] }}mm; display: flex; justify-content: space-between; align-items: center; border-bottom: 2px solid #1a5a3a; }
        .header-left { width: 40%; text-align: left; color: #1a5a3a; }
        .header-left h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .header-left .sub { margin: 3px 0 0 0; font-size: 13px; font-weight: bold; color: #333; }
        .header-left .specialty { margin: 5px 0 0 0; font-size: 15px; font-weight: bold; color: #d91d1d; }
        .header-left .affil { margin: 5px 0 0 0; font-size: 13px; font-weight: bold; color: #1a5a3a; }
        .header-center { width: 20%; text-align: center; display: flex; flex-direction: column; align-items: center; }
        .header-center .logo-tagline { font-size: 10px; font-style: italic; color: #d91d1d; font-weight: bold; }
        .header-center .disclaimer { font-size: 8px; color: #555; line-height: 1.1; margin-top: 3px; display: block; width: 120px; }
        .header-right { width: 40%; text-align: right; color: #0f365c; }
        .header-right h1 { margin: 0; font-size: 24px; font-weight: bold; }
        .header-right .sub { margin: 3px 0 0 0; font-size: 13px; font-weight: bold; color: #333; }
        .header-right .title { margin: 10px 0 0 0; font-size: 15px; font-weight: bold; color: #d91d1d; line-height: 1.2; }
        .header-right .affil { margin: 4px 0 0 0; font-size: 14px; font-weight: bold; color: #0f365c; }
        .patient-bar { padding: 8px 25px; border-bottom: 2px solid #000; display: flex; font-size: 14px; font-weight: bold; color: #333; background: #fff; }
        .patient-bar .field { margin-right: 20px; }
        .patient-bar .field span { border-bottom: 1px dotted #888; display: inline-block; min-width: 120px; }
        .body-wrap { flex-grow: 1; display: flex; min-height: 0; }
        .left-panel { width: 33%; background-color: #e6f4f8; border-right: 1px solid #bce1ec; padding: 20px 15px; display: flex; flex-direction: column; box-sizing: border-box; font-size: 11px; color: #333; line-height: 1.8; }
        .left-panel .cb-row { margin-bottom: 6px; display: flex; align-items: flex-start; }
        .left-panel .cb-row input { margin-right: 6px; margin-top: 3px; }
        .left-panel .cb-row.red { color: #d91d1d; font-weight: bold; }
        .left-panel .section-label { font-weight: bold; font-size: 12px; color: #1a5a3a; margin: 10px 0 4px 0; border-bottom: 1px solid #bce1ec; padding-bottom: 2px; }
        .left-panel .complaint-tag { display: inline-block; padding: 1px 6px; background: #e0f0ff; border: 1px solid #71a9ce; border-radius: 4px; font-size: 10px; margin: 1px; }
        .left-panel .test-tag { display: inline-block; padding: 1px 6px; background: #fff3cd; border: 1px solid #ffc107; border-radius: 4px; font-size: 10px; margin: 1px; }
        .right-panel { width: 67%; padding: 20px 25px; box-sizing: border-box; display: flex; flex-direction: column; }
        .rx-symbol { font-size: 32px; font-weight: bold; font-family: 'Times New Roman', Times, serif; color: #333; }
        .med-table { width: 100%; border-collapse: collapse; margin-top: 10px; font-size: 12px; }
        .med-table td { padding: 3px 6px; border-bottom: 1px dotted #ddd; vertical-align: top; }
        .med-table .med-name { width: 30%; }
        .med-table .med-freq { width: 15%; }
        .med-table .med-day { width: 10%; }
        .med-table .med-remark { width: 25%; }
        .seal-print-row td { padding: 6px 0; border-bottom: 1px dotted #ccc; vertical-align: top; }
        .seal-stamp {
            display: inline-block;
            border: 2px solid #1a3a6e;
            border-radius: 4px;
            padding: 6px 12px;
            font-family: 'Courier New', Courier, monospace;
            font-weight: bold;
            font-size: 10px;
            color: #1a3a6e;
            line-height: 1.5;
            /* white-space: pre-wrap; */
            max-width: 250px;
            position: relative;
            /* transform: rotate(-1.5deg); */
            opacity: 0.85;
        }
        .seal-stamp .seal-stamp-title {
            font-size: 13px;
            font-weight: 900;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            /* border-bottom: 1px solid #1a3a6e; */
            /* padding-bottom: 3px; */
            /* margin-bottom: 3px; */
            display: block;
        }
        .seal-stamp .seal-stamp-body {
            font-size: 9px;
            font-weight: 600;
            text-transform: none;
            letter-spacing: 0;
            display: block;
            color: #059b54;
        }
        /* .seal-stamp::before {
            content: '';
            position: absolute;
            inset: -1px;
            border: 1px solid #1a3a6e;
            border-radius: 6px;
            opacity: 0.3;
            pointer-events: none;
        } */
        .advice-tag { display: inline-block; padding: 1px 8px; background: #e0f0ff; border: 1px solid #71a9ce; border-radius: 10px; font-size: 10px; margin: 1px; }
        .followup-text { font-size: 12px; margin-top: 8px; color: #333; }
        .footer-top { background-color: #ffe600; padding: 6px; text-align: center; font-size: 15px; font-weight: bold; color: #000; border-top: 1px solid #ccc; border-bottom: 1px solid #ccc; }
        .footer-top .normal { font-weight: normal; font-size: 14px; }
        .footer-bottom { background-color: transparent; padding: {{ $layout['f_pt'] }}mm {{ $layout['f_pr'] }}mm {{ $layout['f_pb'] }}mm {{ $layout['f_pl'] }}mm; margin: {{ $layout['f_mt'] }}mm {{ $layout['f_mr'] }}mm {{ $layout['f_mb'] }}mm {{ $layout['f_ml'] }}mm; display: flex; justify-content: space-between; align-items: flex-start; color: #000; box-sizing: border-box; font-size: 12px; line-height: 1.4; }
        .footer-bottom .col { width: 35%; }
        .footer-bottom .col-center { width: 30%; text-align: center; }
        .footer-bottom .col-right { width: 35%; text-align: right; }
        .footer-bottom .heading { font-size: 15px; font-weight: bold; color: #ffeb3b; margin-bottom: 3px; }
        .footer-bottom .heading-sm { font-weight: bold; font-size: 13px; color: #fff; }
        .footer-bottom .urgent { color: #ffcccc; font-weight: bold; font-size: 13px; }
        .footer-bottom .phone { font-size: 14px; font-weight: bold; margin-top: 3px; color: #fff; }
        .footer-bottom .highlight { color: #ffeb3b; font-weight: bold; }
        .footer-bottom .red-highlight { color: #ffcccc; font-weight: bold; }
        .no-print { display: block; text-align: center; padding: 10px; }
        .no-print button { padding: 8px 24px; font-size: 16px; cursor: pointer; background: #1e4d7c; color: #fff; border: none; border-radius: 4px; }
        @media print {
            .no-print { display: none; }
            body { background: #fff; }
            .page { box-shadow: none; }
            .seal-stamp { -webkit-print-color-adjust: exact; print-color-adjust: exact; color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div>
        <div class="no-print">
            <button onclick="window.print()">Print Prescription</button>
            <button onclick="window.location.href='{{ route('doctor.prescriptions.index') }}'" style="background:#6b7280;margin-left:8px;">Close</button>
        </div>

        <div class="page">
            @php
                $doctor = $prescription->doctor;
                $subSpecialties = $doctor->sub_specialties ?? [];
                $chambers = $doctor->chambers;
                if (is_string($chambers)) {
                    $chambers = json_decode($chambers, true) ?? [];
                }
                if (!is_array($chambers)) {
                    $chambers = [];
                }
            @endphp

            @if($customHeader)
                <div class="header">
                    {!! $customHeader->content !!}
                </div>
            @else
            <div class="header">
                <div class="header-left">
                    <h1>{{ $doctor->name }}</h1>
                    @if($doctor->qualification)
                    <div class="sub">{{ $doctor->qualification }}</div>
                    @endif
                    @if($doctor->designation_title)
                    <div class="specialty">{{ $doctor->designation_title }}</div>
                    @endif
                    @foreach($subSpecialties as $specialty)
                    <div class="specialty">{{ $specialty }}</div>
                    @endforeach
                    @if($doctor->affiliated_hospital)
                    <div class="affil">{{ $doctor->affiliated_hospital }}</div>
                    @endif
                </div>

                <div class="header-center">
                    <svg width="60" height="60" viewBox="0 0 100 100" style="margin-bottom:5px;">
                        <path d="M50,20 C35,20 25,30 25,45 C25,55 32,60 35,65 C38,70 40,75 42,80 L58,80 C60,75 62,70 65,65 C68,60 75,55 75,45 C75,30 65,20 50,20 Z" fill="#2d3748"/>
                        <path d="M48,25 C38,26 30,32 28,42 C32,40 38,42 42,46 C45,42 48,35 48,25 Z" fill="#3182ce"/>
                        <path d="M52,25 C62,26 70,32 72,42 C68,40 62,42 58,46 C55,42 52,35 52,25 Z" fill="#e53e3e"/>
                        <circle cx="50" cy="50" r="12" fill="#ecc94b" opacity="0.8"/>
                    </svg>
                    @if($doctor->prescription_slogan)
                    <div class="logo-tagline">{{ $doctor->prescription_slogan }}</div>
                    @endif
                    <div class="disclaimer">This medical prescription is not a valid document for legal issue</div>
                </div>

                <div class="header-right">
                    <h1>{{ $doctor->name_bn }}</h1>
                    @if($doctor->qualification_bn)
                    <div class="sub">{{ $doctor->qualification_bn }}</div>
                    @endif
                    @if($doctor->designation_title)
                    <div class="title">{{ $doctor->designation_title_bn }}</div>
                    @endif
                    @if($doctor->affiliated_hospital)
                    <div class="affil">{{ $doctor->affiliated_hospital_bn }}</div>
                    @endif
                </div>
            </div>
            @endif

            <div class="patient-bar">
                <div class="field">নাম : <span>{{ $prescription->patient->name }}</span></div>
                <div class="field">বয়স : <span>{{ $prescription->patient->date_of_birth ? \Carbon\Carbon::parse($prescription->patient->date_of_birth)->age : 'N/A' }} বছর</span></div>
                <div class="field" style="margin-right:0;margin-left:auto;">তারিখ : <span>{{ $prescription->created_at->format('d/m/Y') }}</span></div>
            </div>

            <div class="body-wrap">
                <div class="left-panel">
                    <div style="border-top:1px solid #ddd; border-bottom:1px solid #ddd; padding-top:8px;font-size:11px;color:#666;">
                        Prescription No: {{ $prescription->prescription_number }}
                    </div>

                    @php
                        $bp_sys = $prescription->bp_systolic;
                        $bp_dia = $prescription->bp_diastolic;
                        $pulse = $prescription->pulse_rate;
                        $spo2 = $prescription->spo2;
                        $bp = ($bp_sys && $bp_dia) ? $bp_sys . '/' . $bp_dia . ' mmHg' : '';
                        $pulseStr = $pulse ? $pulse . '/min' : '';
                        $spo2Str = $spo2 ? number_format($spo2, 1) . '%' : '';
                        $ht = $prescription->height ? number_format($prescription->height, 1) . ' cm' : '';
                        $wt = $prescription->weight ? number_format($prescription->weight, 2) . ' kg' : '';

                        $hasFamilyHistory = false;
                        if (!empty($prescription->family_history_data)) {
                            $fhd = $prescription->family_history_data;
                            $hasFamilyHistory = (!empty($fhd['diseases']) && count($fhd['diseases']) > 0) || (!empty(trim($fhd['notes'] ?? '')));
                        }

                        $hasMenstrualHistory = false;
                        if (!empty($prescription->menstrual_history_data)) {
                            $mhd = $prescription->menstrual_history_data;
                            $hasMenstrualHistory = !empty(trim($mhd['lmp'] ?? '')) || !empty(trim($mhd['cycle'] ?? '')) || !empty(trim($mhd['duration'] ?? '')) || !empty(trim($mhd['flow'] ?? '')) || !empty(trim($mhd['notes'] ?? ''));
                        }

                        $hasDrugHistory = false;
                        if (!empty($prescription->drug_history_data)) {
                            $dhd = $prescription->drug_history_data;
                            $hasDrugHistory = (!empty($dhd['drugs']) && count($dhd['drugs']) > 0) || (!empty(trim($dhd['notes'] ?? '')));
                        }

                        $hasOtNote = false;
                        if (!empty($prescription->ot_note_data)) {
                            $otd = $prescription->ot_note_data;
                            $hasOtNote = !empty(trim($otd['procedure'] ?? '')) || !empty(trim($otd['date'] ?? '')) || !empty(trim($otd['notes'] ?? ''));
                        }

                        $hasAnesthesia = false;
                        if (!empty($prescription->anesthesia_data)) {
                            $anest = $prescription->anesthesia_data;
                            $hasAnesthesia = !empty(trim($anest['type'] ?? '')) || !empty(trim($anest['agent'] ?? '')) || !empty(trim($anest['dose'] ?? '')) || !empty(trim($anest['notes'] ?? ''));
                        }

                        $hasAdvice = $prescription->advices->count() > 0 || $prescription->advice->count() > 0 || !empty(trim($prescription->follow_up_instructions ?? ''));
                    @endphp

                    @if($bp || $pulseStr || $spo2Str || $ht || $wt)
                    <div class="section-label">Vitals</div>
                    <div style="font-size:10px;margin-bottom:6px;display:flex;gap:12px;flex-wrap:wrap;">
                        @if($pulseStr)<span><strong>Pulse:</strong> {{ $pulseStr }}</span>@endif
                        @if($bp)<span><strong>BP:</strong> {{ $bp }}</span>@endif
                        @if($spo2Str)<span><strong>SpO₂:</strong> {{ $spo2Str }}</span>@endif
                        @if($ht)<span><strong>Ht:</strong> {{ $ht }}</span>@endif
                        @if($wt)<span><strong>Wt:</strong> {{ $wt }}</span>@endif
                    </div>
                    @endif

                    @if($prescription->complaints->count() > 0)
                    <div class="section-label">Chief Complaint</div>
                    <ul style="margin:4px 0 8px 0;padding-left:16px;font-size:10px;">
                        @foreach($prescription->complaints as $complaint)
                        <li>{{ $complaint->name }}@if($complaint->pivot?->notes) ({{ $complaint->pivot->notes }})@endif</li>
                        @endforeach
                    </ul>
                    @endif

                    @if($prescription->tests->count() > 0)
                    <div class="section-label">On Examinations</div>
                    <ul style="margin:4px 0 8px 0;padding-left:16px;font-size:10px;">
                        @foreach($prescription->tests as $test)
                        <li>{{ $test->test_name }}</li>
                        @endforeach
                    </ul>
                    @endif

                    @php
                        $reportGroups = $prescription->testReports->groupBy('test_name');
                    @endphp
                    @if($reportGroups->count() > 0)
                    <div class="section-label">Test Report Summary</div>
                    <div style="font-size:10px;">
                        @foreach($reportGroups as $testName => $reports)
                        <div style="margin:2px 0;">
                            <strong style="color:#1a5a3a;">{{ $testName }}:</strong>
                            @foreach($reports as $report)
                            <span>{{ $report->parameter_name }} {{ $report->value ?? '-' }} {{ $report->unit ?? '' }}{{ !$loop->last ? ',' : '' }}</span>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @php $testResults = $prescription->testReportResults; @endphp
                    @if($testResults->count() > 0)
                    <div class="section-label">Test Results</div>
                    <div style="font-size:10px;">
                        @foreach($testResults as $result)
                        <div style="margin:2px 0;display:flex;justify-content:space-between;">
                            <span>{{ $result->test_name }}</span>
                            <span style="font-weight:bold;">{{ $result->result ?? '—' }}</span>
                        </div>
                        @endforeach
                    </div>
                    @endif

                    @if($hasFamilyHistory)
                    <div class="section-label">Family History</div>
                    <div style="font-size:10px;margin-bottom:6px;">
                        @if(!empty($prescription->family_history_data['diseases']))
                            @foreach($prescription->family_history_data['diseases'] as $disease)
                                @if(!empty(trim($disease['name'] ?? '')))
                                <span style="display:inline-block;padding:1px 6px;background:#e8f5e9;border:1px solid #81c784;border-radius:4px;font-size:10px;margin:1px;">
                                    {{ $disease['name'] }}@if(!empty($disease['relation'])) ({{ $disease['relation'] }})@endif
                                </span>
                                @endif
                            @endforeach
                        @endif
                        @if(!empty(trim($prescription->family_history_data['notes'] ?? '')))
                            <div style="margin-top:2px;color:#666;">{{ $prescription->family_history_data['notes'] }}</div>
                        @endif
                    </div>
                    @endif

                    @if($hasMenstrualHistory)
                    <div class="section-label">Menstrual History</div>
                    <div style="font-size:10px;margin-bottom:6px;">
                        @if(!empty(trim($prescription->menstrual_history_data['lmp'] ?? '')))
                            <span><strong>LMP:</strong> {{ $prescription->menstrual_history_data['lmp'] }}</span><br>
                        @endif
                        @if(!empty(trim($prescription->menstrual_history_data['cycle'] ?? '')))
                            <span><strong>Cycle:</strong> {{ $prescription->menstrual_history_data['cycle'] }}</span><br>
                        @endif
                        @if(!empty(trim($prescription->menstrual_history_data['duration'] ?? '')))
                            <span><strong>Duration:</strong> {{ $prescription->menstrual_history_data['duration'] }}</span><br>
                        @endif
                        @if(!empty(trim($prescription->menstrual_history_data['flow'] ?? '')))
                            <span><strong>Flow:</strong> {{ $prescription->menstrual_history_data['flow'] }}</span><br>
                        @endif
                        @if(!empty(trim($prescription->menstrual_history_data['notes'] ?? '')))
                            <div style="margin-top:2px;color:#666;">{{ $prescription->menstrual_history_data['notes'] }}</div>
                        @endif
                    </div>
                    @endif

                    @if($hasDrugHistory)
                    <div class="section-label">Drug History</div>
                    <div style="font-size:10px;margin-bottom:6px;">
                        @if(!empty($prescription->drug_history_data['drugs']))
                            @foreach($prescription->drug_history_data['drugs'] as $drug)
                                @if(!empty(trim($drug['name'] ?? '')))
                                <span style="display:inline-block;padding:1px 6px;background:#fff3e0;border:1px solid #ffb74d;border-radius:4px;font-size:10px;margin:1px;">
                                    {{ $drug['name'] }}@if(!empty(trim($drug['dose'] ?? ''))) {{ $drug['dose'] }}@endif
                                </span>
                                @endif
                            @endforeach
                        @endif
                        @if(!empty(trim($prescription->drug_history_data['notes'] ?? '')))
                            <div style="margin-top:2px;color:#666;">{{ $prescription->drug_history_data['notes'] }}</div>
                        @endif
                    </div>
                    @endif

                    @if($hasOtNote)
                    <div class="section-label">OT Note / Procedure Done</div>
                    <div style="font-size:10px;margin-bottom:6px;">
                        @if(!empty(trim($prescription->ot_note_data['procedure'] ?? '')))
                            <span><strong>Procedure:</strong> {{ $prescription->ot_note_data['procedure'] }}</span><br>
                        @endif
                        @if(!empty(trim($prescription->ot_note_data['date'] ?? '')))
                            <span><strong>Date:</strong> {{ $prescription->ot_note_data['date'] }}</span><br>
                        @endif
                        @if(!empty(trim($prescription->ot_note_data['notes'] ?? '')))
                            <div style="margin-top:2px;color:#666;">{{ $prescription->ot_note_data['notes'] }}</div>
                        @endif
                    </div>
                    @endif

                    @if($hasAnesthesia)
                    <div class="section-label">Anesthesia (LA / Surface)</div>
                    <div style="font-size:10px;margin-bottom:6px;">
                        @if(!empty(trim($prescription->anesthesia_data['type'] ?? '')))
                            <span><strong>Type:</strong> {{ $prescription->anesthesia_data['type'] }}</span><br>
                        @endif
                        @if(!empty(trim($prescription->anesthesia_data['agent'] ?? '')))
                            <span><strong>Agent:</strong> {{ $prescription->anesthesia_data['agent'] }}</span><br>
                        @endif
                        @if(!empty(trim($prescription->anesthesia_data['dose'] ?? '')))
                            <span><strong>Dose:</strong> {{ $prescription->anesthesia_data['dose'] }}</span><br>
                        @endif
                        @if(!empty(trim($prescription->anesthesia_data['notes'] ?? '')))
                            <div style="margin-top:2px;color:#666;">{{ $prescription->anesthesia_data['notes'] }}</div>
                        @endif
                    </div>
                    @endif

                    @if($prescription->procedures->count() > 0)
                    <div class="section-label">Procedure</div>
                    <ul style="margin:4px 0 8px 0;padding-left:16px;font-size:10px;">
                        @foreach($prescription->procedures as $procedure)
                            @if(!empty(trim($procedure->procedure_name ?? '')))
                            <li>{{ $procedure->procedure_name }}</li>
                            @endif
                        @endforeach
                    </ul>
                    @endif

                    @if($prescription->treatmentPlans->count() > 0)
                    <div class="section-label">Treatment Plan</div>
                    <ul style="margin:4px 0 8px 0;padding-left:16px;font-size:10px;">
                        @foreach($prescription->treatmentPlans as $plan)
                            @if(!empty(trim($plan->treatment_plan_name ?? '')))
                            <li>{{ $plan->treatment_plan_name }}</li>
                            @endif
                        @endforeach
                    </ul>
                    @endif

                    @if($hasAdvice)
                    <div style="font-size:11px;color:#333;line-height:1.8;">
                        <div class="section-label">পরামর্শ ও ফলো-আপ</div>
                        <div style="font-size:10px;margin-bottom:6px;">
                            @if($prescription->advices->count() > 0 || $prescription->advice->count() > 0)
                            <ul style="margin:0;padding-left:16px;list-style-type:disc;">
                                @foreach($prescription->advices as $advice)
                                <li>{{ $advice->name }}</li>
                                @endforeach
                                @foreach($prescription->advice as $advice)
                                <li>{{ $advice->advice }}</li>
                                @endforeach
                            </ul>
                            @endif
                            @if(!empty(trim($prescription->follow_up_instructions ?? '')))
                            <div style="margin-top:4px;color:#555;">{{ $prescription->follow_up_instructions }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>

                <div class="right-panel">
                    <div class="rx-symbol">R<sub>x</sub></div>

                    @if($prescription->items->count() > 0)
                    <table class="med-table">
                        @foreach($prescription->items->sortBy('sort_order') as $item)
                            @if($item->type === 'seal')
                            <tr class="seal-print-row">
                                <td class="med-name">
                                    <div class="seal-stamp">
                                        @if($item->seal_text)<span class="seal-stamp-title" style="font-wight:900;">{!! nl2br(e($item->seal_text)) !!}</span>@endif
                                        @if($item->seal_details)<span class="seal-stamp-body">{!! nl2br(e($item->seal_details)) !!}</span>@endif
                                    </div>
                                </td>
                                <td class="med-freq"></td>
                                <td class="med-day">{{ $item->duration ?? '—' }} দিন</td>
                                <td class="med-remark"></td>
                            </tr>
                            @else
                            <tr>
                                <td class="med-name">
                                    @php
                                        $catName = $item->medicine?->category?->name ?? '';
                                        $catAbbr = match($catName) {
                                            'Tablet' => 'Tab',
                                            'Capsule' => 'Cap',
                                            'Syrup' => 'Syr',
                                            'Injection' => 'Inj',
                                            'Cream' => 'Cream',
                                            'Drops' => 'Drop',
                                            'Ointment' => 'Oint',
                                            'Gel' => 'Gel',
                                            default => $catName ? ucfirst(mb_substr($catName, 0, 3)) : '',
                                        };
                                    @endphp
                                    <strong>{{ $catAbbr ? $catAbbr . '. ' : '' }}{{ $item->medicine_name }}{{ $item->dosage && !str_contains($item->medicine_name, $item->dosage) ? ' ' . $item->dosage : '' }}</strong>
                                    @if($item->seal_text)
                                    <br><span style="font-weight:bold;font-size:11px;color:#333;">{{ $item->seal_text }}</span>
                                    @endif
                                    @if($item->seal_details)
                                    <br><span style="font-weight:normal;font-size:11px;color:#333;">{{ $item->seal_details }}</span>
                                    @endif
                                </td>
                                <td class="med-freq">{{ $item->frequency ?? '—' }}</td>
                                <td class="med-day">{{ $item->duration ?? '—' }} দিন</td>
                                <td class="med-remark">{{ $item->instructions ?? '' }}</td>
                            </tr>
                            @endif
                        @endforeach
                    </table>
                    @else
                    <div style="color:#999;font-style:italic;font-size:14px;margin-top:10px;">No medicine prescribed at this visit.</div>
                    @endif

                    
                    @if($prescription->follow_up_date)
                        <div style="margin-top:auto; border-top:1px solid #ddd;padding-top:8px;font-size:11px;color:#666;"">পরবর্তী সাক্ষাৎ: {{ \Carbon\Carbon::parse($prescription->follow_up_date)->format('d/m/Y') }}</div>
                        @endif
                     
                </div>
            </div>

            <div>
                @if($customFooter)
                    <div class="footer-bottom">{!! $customFooter->content !!}</div>
                @else
                @php $chamberCount = count($chambers); @endphp
                @if($chamberCount > 0)
                {{-- <div class="footer-top">
                    @foreach($chambers as $chamber)
                        @if(!$loop->first) &nbsp;|&nbsp; @endif
                        {{ $chamber['phone'] ?? '' }}
                        @if(!empty($chamber['booking_hotline'])) / {{ $chamber['booking_hotline'] }} @endif
                    @endforeach
                </div> --}}

                <div class="footer-bottom">
                    @foreach($chambers as $chamber)
                    @php
                        $colWidth = $chamberCount === 1 ? '100%' : ($chamberCount === 2 ? '50%' : '33.33%');
                    @endphp
                    <div style="width:{{ $colWidth }};padding:0 10px;box-sizing:border-box;{{ $loop->last ? 'text-align:right;' : '' }}{{ $loop->first && $chamberCount > 1 ? 'text-align:left;' : '' }}{{ $loop->iteration === 2 && $chamberCount === 3 ? 'text-align:center;' : '' }}">
                        <div class="heading">{{ $chamber['name'] ?? '' }}</div>
                        @if(!empty($chamber['address']))
                        <div style="font-size:11px;">{{ $chamber['address'] }}</div>
                        @endif
                        {{-- @if(!empty($chamber['phone']))
                        <div style="font-weight:bold;color:#ffeb3b;font-size:13px;margin-top:2px;">মোবাইল: {{ $chamber['phone'] }}</div>
                        @endif --}}
                        @if(!empty($chamber['booking_hotline']))
                        <div style="font-weight:bold;color:#ffeb3b;font-size:13px;">সিরিয়াল: {{ $chamber['booking_hotline'] }}</div>
                        @endif
                        @if(!empty($chamber['hours']))
                        <div style="margin-top:4px;" class="highlight">রোগী দেখার সময় :</div>
                        <div>{{ $chamber['hours'] }}
                            @if(!empty($chamber['closed_days']))
                            <span class="red-highlight"> | {{ $chamber['closed_days'] }} বন্ধ</span>
                            @endif
                        </div>
                        @endif
                    </div>
                    @endforeach
                    @if($doctor->emergency_contact && $chamberCount < 3)
                    <div style="width:{{ $chamberCount === 1 ? '100%' : '50%' }};text-align:center;padding:0 10px;box-sizing:border-box;">
                        <div class="urgent">চিকিৎসা সংক্রান্ত জরুরী প্রয়োজনে</div>
                        <div class="phone">মোবাঃ {{ $doctor->emergency_contact }}</div>
                    </div>
                    @endif
                </div>
                @else
                <div class="footer-bottom" style=";text-align:center;color:#000;">
                    @if($doctor->emergency_contact)
                    <div style="width:100%;">
                        <div class="urgent">চিকিৎসা সংক্রান্ত জরুরী প্রয়োজনে</div>
                        <div class="phone">মোবাঃ {{ $doctor->emergency_contact }}</div>
                    </div>
                    @endif
                </div>
                @endif
                @endif
            </div>
        </div>
    </div>
</body>
</html>
