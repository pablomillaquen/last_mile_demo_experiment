<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<style>
    body { font-family: DejaVu Sans, sans-serif; font-size: 10pt; color: #333; }
    h1 { font-size: 18pt; color: #1a56db; border-bottom: 2px solid #1a56db; padding-bottom: 5px; }
    h2 { font-size: 14pt; color: #374151; margin-top: 20px; }
    h3 { font-size: 12pt; color: #4b5563; margin-top: 15px; }
    p { margin: 6px 0; word-break: break-word; }
    table { width: 100%; border-collapse: collapse; margin: 10px 0; font-size: 6.5pt; table-layout: auto; }
    th { background: #f3f4f6; text-align: left; padding: 3px 4px; border: 1px solid #d1d5db; word-break: break-word; }
    td { padding: 2px 4px; border: 1px solid #e5e7eb; word-break: break-word; }
    tr:nth-child(even) { background: #f9fafb; }
    ul, ol { margin: 6px 0; padding-left: 20px; }
    li { margin: 2px 0; }
    code { background: #f3f4f6; padding: 1px 4px; border-radius: 3px; font-size: 8pt; }
    strong { color: #111827; }
    .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #d1d5db; font-size: 7pt; color: #9ca3af; text-align: center; }
    .page-break { page-break-before: always; }
    img { max-width: 100%; height: auto; }
</style>
</head>
<body>
    {!! $content !!}
    <div class="footer">
        <p>experiment: {{ $experiment->identifier }} | generated_at: {{ now()->toIso8601String() }}</p>
    </div>
</body>
</html>
