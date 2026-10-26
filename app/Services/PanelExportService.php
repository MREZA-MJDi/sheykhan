<?php

namespace App\Services;

use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class PanelExportService
{
    /**
     * Export scoped, server-authorized records as Excel-compatible CSV or Word-compatible HTML.
     *
     * @param array<int, string> $headers
     * @param iterable<array<int, mixed>> $rows
     * @param array<string, scalar|null> $summary
     */
    public function download(
        string $format,
        string $filename,
        string $title,
        array $headers,
        iterable $rows,
        array $summary = [],
    ): Response|StreamedResponse {
        abort_unless(in_array($format, ['csv', 'word'], true), 422);

        $safeFilename = trim((string) preg_replace('/[^a-zA-Z0-9_-]+/', '-', $filename), '-_');
        $safeFilename = $safeFilename !== '' ? $safeFilename : 'sheykhan-export';

        return $format === 'word'
            ? $this->word($safeFilename, $title, $headers, $rows, $summary)
            : $this->csv($safeFilename, $headers, $rows);
    }

    private function csv(string $filename, array $headers, iterable $rows): StreamedResponse
    {
        return response()->streamDownload(static function () use ($headers, $rows): void {
            $stream = fopen('php://output', 'wb');
            if ($stream === false) {
                return;
            }

            // Excel on Windows needs the UTF-8 BOM for Persian text.
            fwrite($stream, "\xEF\xBB\xBF");
            fputcsv($stream, array_map(self::csvCell(...), $headers), ',', '"', '\\');

            foreach ($rows as $row) {
                fputcsv($stream, array_map(self::csvCell(...), array_values((array) $row)), ',', '"', '\\');
            }

            fclose($stream);
        }, $filename . '.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private function word(string $filename, string $title, array $headers, iterable $rows, array $summary): Response
    {
        $escape = static fn (mixed $value): string => htmlspecialchars(
            self::displayCell($value),
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8',
        );

        $summaryHtml = '';
        foreach ($summary as $label => $value) {
            $summaryHtml .= '<tr><th>' . $escape($label) . '</th><td>' . $escape($value) . '</td></tr>';
        }

        $headHtml = implode('', array_map(
            static fn ($header) => '<th scope="col">' . $escape($header) . '</th>',
            $headers,
        ));

        $bodyHtml = '';
        foreach ($rows as $row) {
            $cells = array_values((array) $row);
            $bodyHtml .= '<tr>';
            foreach ($headers as $index => $_header) {
                $bodyHtml .= '<td>' . nl2br($escape($cells[$index] ?? '')) . '</td>';
            }
            $bodyHtml .= '</tr>';
        }

        $html = '<!doctype html><html lang="fa" dir="rtl"><head><meta charset="utf-8">'
            . '<title>' . $escape($title) . '</title>'
            . '<style>body{font-family:Tahoma,Arial,sans-serif;direction:rtl;color:#172033;margin:28px;line-height:1.8}'
            . 'h1{font-size:20px;margin:0 0 8px}p{color:#64748b;font-size:12px}'
            . 'table{width:100%;border-collapse:collapse;margin:14px 0 22px;table-layout:auto}'
            . 'th,td{border:1px solid #cbd5e1;padding:7px 9px;text-align:right;vertical-align:top;font-size:11px}'
            . 'thead th{background:#eaf0f7;font-weight:bold}tbody tr:nth-child(even){background:#f8fafc}'
            . '.summary{width:auto;min-width:45%}.summary th{background:#f1f5f9;width:35%}'
            . '@media print{body{margin:12mm}tr{page-break-inside:avoid}}</style></head><body>'
            . '<h1>' . $escape($title) . '</h1>'
            . '<p>خروجی سامانهٔ شیخان · زمان تهیه: ' . $escape(now()->format('Y-m-d H:i')) . '</p>';

        if ($summaryHtml !== '') {
            $html .= '<table class="summary"><tbody>' . $summaryHtml . '</tbody></table>';
        }

        $html .= '<table><thead><tr>' . $headHtml . '</tr></thead><tbody>' . $bodyHtml
            . '</tbody></table></body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/msword; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '.doc"',
            'Cache-Control' => 'private, no-store, max-age=0',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    private static function csvCell(mixed $value): string
    {
        $cell = self::displayCell($value);

        // Prevent spreadsheet formula injection from user-controlled text.
        if ($cell !== '' && preg_match('/^[=+@\t\r]/u', $cell) === 1) {
            return "'" . $cell;
        }

        return $cell;
    }

    private static function displayCell(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        if (is_bool($value)) {
            return $value ? 'بله' : 'خیر';
        }

        if (is_array($value) || is_object($value)) {
            return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '';
        }

        return str_replace(["\0", "\r\n", "\r"], ['', "\n", "\n"], (string) $value);
    }
}
