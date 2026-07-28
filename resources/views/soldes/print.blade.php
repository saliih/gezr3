<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>تخطيط الاستهلاك - {{ $date }}</title>
    <style>
        @page { size: A4 landscape; margin: 12mm; }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            font-size: 11px;
            color: #000;
            direction: rtl;
        }

        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid #000;
            padding-bottom: 6px;
            margin-bottom: 10px;
        }

        .header h1 { font-size: 16px; }
        .header .date { font-size: 13px; font-weight: bold; }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            background: #1e2a3a;
            color: #fff;
            padding: 10px 8px;
            text-align: center;
            font-size: 11px;
            border: 1px solid #000;
        }

        td {
            padding: 10px 8px;
            border: 1px solid #ccc;
            text-align: center;
            vertical-align: middle;
            height: 36px;
        }

        tr:nth-child(even) td { background: #f5f5f5; }

        .write-col { background: #fffde7 !important; color: #000; }

        th.write-col-header { min-width: 200px; font-size: 12px; }
        td.write-merged { min-width: 200px; background: #fffde7 !important; }

        .footer {
            margin-top: 10px;
            font-size: 10px;
            color: #555;
            text-align: center;
        }

        @media print {
            body { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>

<div class="header">
    <div>
        <h1><i>GEZR</i> — تخطيط الاستهلاك</h1>
        <div style="font-size:11px; color:#444; margin-top:3px;">الصنابير غير المسجّلة بعد</div>
    </div>
    <div class="date">تاريخ التخطيط : {{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</div>
</div>

@if($soldes->isEmpty())
    <p style="text-align:center; padding:20px; color:#888;">لا توجد صنابير غير مسجّلة لهذا التاريخ.</p>
@else
<table>
    <thead>
        <tr>
            <th>#</th>
            <th>الصنبور</th>
            <th>الفلاح</th>
            <th>رقم الملف</th>
            <th>الهاتف</th>
            <th>العداد القديم</th>
            <th class="write-col write-col-header" colspan="3">العداد الجديد &nbsp;|&nbsp; الاستهلاك &nbsp;|&nbsp; ملاحظات</th>
        </tr>
    </thead>
    <tbody>
        @foreach($soldes as $i => $solde)
        <tr>
            <td>{{ $i + 1 }}</td>
            <td><strong>{{ $solde->vannes ?? '—' }}</strong></td>
            <td>{{ $solde->client?->name ?? '—' }}</td>
            <td>{{ $solde->client?->num_dossier ?? '—' }}</td>
            <td dir="ltr">{{ $solde->client?->tel ?? '—' }}</td>
            <td dir="ltr">{{ number_format($solde->old_counter ?? 0) }}</td>
            <td class="write-merged" colspan="3">&nbsp;</td>
        </tr>
        @endforeach
    </tbody>
</table>

<div class="footer">
    عدد الصنابير : {{ $soldes->count() }} &nbsp;|&nbsp;
    طُبع بتاريخ : {{ now()->format('d/m/Y H:i') }}
</div>
@endif

<script>window.onload = () => window.print();</script>
</body>
</html>
