<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Token {{ $queue->formatted_serial ?? $queue->serial_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Noto+Sans+Bengali:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Noto Sans Bengali','Segoe UI',sans-serif;background:#f1f5f9;display:flex;flex-direction:column;align-items:center;padding:20px}
        .controls{display:flex;gap:10px;margin-bottom:20px}
        .controls button{padding:10px 24px;border:none;border-radius:8px;font-size:14px;font-weight:600;cursor:pointer;transition:all .2s}
        .btn-print{background:#2563eb;color:#fff}
        .btn-print:hover{background:#1d4ed8}
        .btn-close{background:#e2e8f0;color:#475569}
        .btn-close:hover{background:#cbd5e1}
        .token{width:80mm;background:#fff;border:1px dashed #cbd5e1;padding:6mm 5mm;font-size:12px;color:#1e293b;line-height:1.5}
        .token-header{text-align:center;border-bottom:2px dashed #94a3b8;padding-bottom:4mm;margin-bottom:4mm}
        .clinic-name{font-size:14px;font-weight:800;color:#0f172a;margin-bottom:1mm}
        .clinic-name-bn{font-size:12px;font-weight:600;color:#334155;margin-bottom:2mm}
        .doctor-name{font-size:11px;font-weight:600;color:#475569}
        .doctor-specialization{font-size:9px;color:#64748b;margin-top:1mm}
        .token-body{text-align:center;margin:4mm 0}
        .serial-label{font-size:9px;font-weight:600;text-transform:uppercase;letter-spacing:2px;color:#94a3b8;margin-bottom:1mm}
        .serial-number{font-size:36px;font-weight:900;color:#1e293b;line-height:1.1;letter-spacing:2px}
        .serial-number.prefix{font-size:28px}
        .patient-name{font-size:13px;font-weight:700;color:#334155;margin-top:3mm}
        .token-info{border-top:1px dashed #94a3b8;padding-top:3mm;margin-top:3mm}
        .info-row{display:flex;justify-content:space-between;font-size:10px;color:#64748b;margin-bottom:1.5mm}
        .info-row .label{font-weight:500}
        .info-row .value{font-weight:700;color:#334155}
        .token-footer{text-align:center;border-top:2px dashed #94a3b8;padding-top:3mm;margin-top:4mm}
        .waiting-message{font-size:10px;font-weight:600;color:#d97706;background:#fffbeb;border:1px solid #fde68a;border-radius:4px;padding:2mm 3mm;margin-bottom:2mm}
        .token-id{font-size:8px;color:#94a3b8;margin-top:2mm}
        .priority-badge{display:inline-block;font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:1px;padding:1mm 3mm;border-radius:3px;margin-top:2mm}
        .priority-emergency{background:#fef2f2;color:#dc2626;border:1px solid #fca5a5}
        .priority-urgent{background:#fff7ed;color:#ea580c;border:1px solid #fdba74}
        .priority-vip{background:#faf5ff;color:#9333ea;border:1px solid #c4b5fd}
        .priority-normal{background:#f8fafc;color:#64748b;border:1px solid #e2e8f0}
        @media print{body{background:#fff;padding:0;margin:0}.controls{display:none!important}.token{width:100%;border:none;padding:3mm 4mm;page-break-inside:avoid}@page{size:80mm auto;margin:0}}
    </style>
</head>
<body>
    <div class="controls no-print">
        <button class="btn-print" onclick="window.print()">&#128424; Print Token</button>
        <button class="btn-close" onclick="window.close()">&#10005; Close</button>
    </div>
    <div class="token">
        <div class="token-header">
            @if($clinicName)
                <div class="clinic-name">{{ $clinicName }}</div>
            @endif
            @if($clinicNameBn)
                <div class="clinic-name-bn">{{ $clinicNameBn }}</div>
            @endif
            <div class="doctor-name">Dr. {{ $doctor->name }}</div>
            @if($doctor->specialization)
                <div class="doctor-specialization">{{ $doctor->specialization }}</div>
            @endif
        </div>
        <div class="token-body">
            <div class="serial-label">Your Token Number</div>
            @php
                $serialDisplay = $queue->formatted_serial ?? str_pad($queue->serial_number, 3, '0', STR_PAD_LEFT);
                $hasPrefix = str_contains($serialDisplay, '-');
            @endphp
            <div class="serial-number {{ $hasPrefix ? 'prefix' : '' }}">{{ $serialDisplay }}</div>
            @if($queue->patient)
                <div class="patient-name">{{ $queue->patient->name }}</div>
            @endif
            @if($queue->priority && $queue->priority !== 'normal')
                <div class="priority-badge priority-{{ $queue->priority }}">{{ strtoupper($queue->priority) }}</div>
            @endif
        </div>
        <div class="token-info">
            <div class="info-row">
                <span class="label">Date</span>
                <span class="value">{{ now()->format('d M, Y') }}</span>
            </div>
            <div class="info-row">
                <span class="label">Time</span>
                <span class="value">{{ now()->format('h:i A') }}</span>
            </div>
            @if($chamberName)
                <div class="info-row">
                    <span class="label">Chamber</span>
                    <span class="value">{{ $chamberName }}</span>
                </div>
            @endif
            @if($queue->status)
                <div class="info-row">
                    <span class="label">Status</span>
                    <span class="value">{{ ucfirst($queue->status) }}</span>
                </div>
            @endif
        </div>
        <div class="token-footer">
            <div class="waiting-message">
                &#9203; Please wait for your turn. We will call your token number.
                <br>অনুগ্রহ করে আপনার পালার অপেক্ষা করুন।
            </div>
            <div class="token-id">Token: {{ $serialDisplay }} | {{ now()->format('Y-m-d H:i') }}</div>
        </div>
    </div>
    <script>
        document.addEventListener('keydown',function(e){if(e.key==='Escape')window.close();if(e.ctrlKey&&e.key==='p'){e.preventDefault();window.print()}});
    </script>
</body>
</html>
