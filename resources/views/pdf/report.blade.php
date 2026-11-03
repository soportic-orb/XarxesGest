{{-- Insights export and monthly report as a PDF (dompdf). Values come escaped; a
     table cell that is null shows as a dash, like the Markdown export. --}}
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" dir="{{ $direction }}">
<head>
    <meta charset="utf-8">
    <title>{{ $export['title'] }}</title>
    <style>
        @page { margin: 28px 32px; }
        body { font-family: 'DejaVu Sans', sans-serif; font-size: 9px; color: #18181b; }
        .brand { font-size: 11px; font-weight: bold; color: #7c3aed; margin-bottom: 4px; }
        h1 { font-size: 18px; margin: 0 0 6px; }
        h2 { font-size: 12px; margin: 18px 0 6px; padding-bottom: 3px; border-bottom: 1px solid #e4e4e7; }
        .meta { color: #52525b; margin: 0 0 10px; padding: 0; list-style: none; }
        .meta li { margin: 1px 0; }
        .summary { background: #f4f4f5; border-radius: 6px; padding: 10px 12px; margin: 10px 0 4px; }
        .summary .headline { font-size: 12px; font-weight: bold; margin: 0 0 6px; }
        .summary h3 { font-size: 10px; margin: 8px 0 3px; }
        .summary ul { margin: 0; padding-{{ $direction === 'rtl' ? 'right' : 'left' }}: 14px; }
        table { width: 100%; border-collapse: collapse; page-break-inside: auto; }
        tr { page-break-inside: avoid; }
        th { background: #f4f4f5; font-weight: bold; text-align: {{ $direction === 'rtl' ? 'right' : 'left' }}; }
        th, td { border: 1px solid #e4e4e7; padding: 3px 5px; vertical-align: top; }
        td.number { text-align: right; white-space: nowrap; }
        .empty { color: #71717a; font-style: italic; }
    </style>
</head>
<body>
    <div class="brand">{{ config('app.name') }}</div>
    <h1>{{ $export['title'] }}</h1>
    <ul class="meta">
        @foreach ($export['meta'] as [$label, $value])
            <li><strong>{{ $label }}:</strong> {{ $value }}</li>
        @endforeach
    </ul>

    @if ($summary !== null)
        <div class="summary">
            <p class="headline">{{ $summary['headline'] }}</p>
            @if ($summary['highlights'] !== [])
                <h3>{{ __('analytics.reports.highlights') }}</h3>
                <ul>
                    @foreach ($summary['highlights'] as $highlight)
                        <li>{{ $highlight }}</li>
                    @endforeach
                </ul>
            @endif
            @if ($summary['recommendations'] !== [])
                <h3>{{ __('analytics.reports.recommendations') }}</h3>
                <ul>
                    @foreach ($summary['recommendations'] as $recommendation)
                        <li>{{ $recommendation }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    @foreach ($export['sections'] as $section)
        <h2>{{ $section['title'] }}</h2>
        @php($rows = is_array($section['rows']) ? $section['rows'] : iterator_to_array($section['rows'], false))
        @if ($rows === [])
            <p class="empty">{{ __('analytics.reports.no_rows') }}</p>
        @else
            <table>
                <thead>
                    <tr>
                        @foreach ($section['headers'] as $header)
                            <th>{{ $header }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach ($rows as $row)
                        <tr>
                            @foreach ($row as $cell)
                                <td @class(['number' => is_int($cell) || is_float($cell)])>{{ $cell ?? '—' }}</td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    @endforeach
</body>
</html>
