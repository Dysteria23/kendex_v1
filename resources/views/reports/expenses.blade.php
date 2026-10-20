<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 10px; color: #14263a; }
        h1 { font-size: 16px; margin: 0 0 4px; }
        h2 { font-size: 12px; margin: 16px 0 4px; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; background: #e8eeee; }
        th, td { border: 1px solid #c3cfd0; padding: 3px 5px; }
        .r { text-align: right; }
        .muted { color: #4b5d66; }
    </style>
</head>
<body>
    <h1>Kendex · Reporte de gastos</h1>
    <p class="muted">Generado el {{ \App\Support\Dates::display(now()) }} · {{ $rows->count() }} solicitudes · Total {{ \App\Support\Money::format($total) }}</p>
    @if ($filters)
        <p class="muted">Filtros: @foreach ($filters as $k => $v){{ $k }}={{ $v }}@if (! $loop->last), @endif @endforeach</p>
    @endif

    <h2>Resumen</h2>
    <table style="width:48%;float:left;margin-right:2%">
        <tr><th>Departamento</th><th class="r">Total</th></tr>
        @foreach ($byDepartment as $name => $sum)<tr><td>{{ $name }}</td><td class="r">{{ \App\Support\Money::format($sum) }}</td></tr>@endforeach
    </table>
    <table style="width:48%;float:left">
        <tr><th>Categoría</th><th class="r">Total</th></tr>
        @foreach ($byCategory as $name => $sum)<tr><td>{{ $name }}</td><td class="r">{{ \App\Support\Money::format($sum) }}</td></tr>@endforeach
    </table>
    <div style="clear:both"></div>

    <h2>Detalle</h2>
    <table>
        <thead>
            <tr><th>Número</th><th>Fecha</th><th>Responsable</th><th>Depto.</th><th>Categoría</th><th>Descripción</th><th class="r">Monto</th><th class="r">ITBIS</th><th>NCF</th><th>Estado</th></tr>
        </thead>
        <tbody>
            @foreach ($rows as $e)
                <tr>
                    <td>{{ $e->number }}</td>
                    <td>{{ $e->expense_date->format('d/m/Y') }}</td>
                    <td>{{ $e->user->name }}</td>
                    <td>{{ $e->department->name }}</td>
                    <td>{{ $e->category->name }}</td>
                    <td>{{ \Illuminate\Support\Str::limit($e->description, 60) }}</td>
                    <td class="r">{{ number_format($e->amount, 2) }}</td>
                    <td class="r">{{ number_format($e->itbis_amount, 2) }}</td>
                    <td>{{ $e->ncf }}</td>
                    <td>{{ $e->status->label() }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
