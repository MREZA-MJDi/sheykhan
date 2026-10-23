<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\SeoKeyword;
use App\Models\SeoKeywordRanking;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

final class SeoKeywordController extends Controller
{
    private const INTENTS = ['informational', 'commercial', 'transactional', 'navigational'];
    private const PRIORITIES = ['high', 'medium', 'low'];
    private const STATUSES = ['idea', 'planned', 'in_progress', 'optimized', 'tracking', 'done'];

    public function index(Request $request): View
    {
        $this->authorizeSeoSpecialist();

        $keywords = $this->filteredQuery($request)
            ->orderByRaw("CASE priority WHEN 'high' THEN 0 WHEN 'medium' THEN 1 ELSE 2 END")
            ->orderBy('keyword')
            ->paginate(25)
            ->withQueryString();

        return view('owner.seo.keywords.index', [
            'keywords' => $keywords,
            'filters' => $request->only(['q', 'status', 'intent', 'priority', 'cluster']),
            'clusters' => SeoKeyword::query()->whereNotNull('cluster')->where('cluster', '<>', '')
                ->distinct()->orderBy('cluster')->pluck('cluster'),
            'stats' => [
                'total' => SeoKeyword::count(),
                'withoutTarget' => SeoKeyword::whereNull('target_url')->orWhere('target_url', '')->count(),
                'withoutPosition' => SeoKeyword::whereNull('last_observed_position')->count(),
                'highPriority' => SeoKeyword::where('priority', 'high')->count(),
                'inProgress' => SeoKeyword::whereIn('status', ['planned', 'in_progress', 'tracking'])->count(),
            ],
            'intentLabels' => $this->intentLabels(),
            'priorityLabels' => $this->priorityLabels(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function create(): View
    {
        $this->authorizeSeoSpecialist();

        return view('owner.seo.keywords.form', [
            'keyword' => new SeoKeyword([
                'intent' => 'informational',
                'priority' => 'medium',
                'status' => 'idea',
            ]),
            'intentLabels' => $this->intentLabels(),
            'priorityLabels' => $this->priorityLabels(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeSeoSpecialist();
        $data = $request->validate($this->rules());
        $data['keyword'] = preg_replace('/\\s+/u', ' ', trim($data['keyword'])) ?? trim($data['keyword']);
        $key = $this->normalizeKeyword($data['keyword']);

        if (SeoKeyword::where('keyword_key', $key)->exists()) {
            return back()->withInput()->withErrors([
                'keyword' => 'این کلمه یا عبارت قبلاً ثبت شده است. مورد موجود را ویرایش کن یا عبارت متفاوتی انتخاب کن.',
            ]);
        }

        [$payload, $observation] = $this->splitObservation($data);
        $payload['keyword_key'] = $key;
        $payload['created_by'] = $request->user()->id;
        $payload['volume_source'] = filled($payload['search_volume'] ?? null) ? 'manual' : null;

        $keyword = DB::transaction(function () use ($payload, $observation, $request): SeoKeyword {
            $keyword = SeoKeyword::create($payload);
            if ($observation['position'] !== null) {
                $this->recordPosition($keyword, $observation, 'manual', $request->user()->id);
            }

            return $keyword;
        });

        return redirect()->route('owner.seo.keywords.edit', $keyword)
            ->with('success', 'کلمهٔ کلیدی ذخیره شد. رتبه فقط در صورت ثبت مشاهدهٔ واقعی نمایش داده می‌شود.');
    }

    public function edit(SeoKeyword $keyword): View
    {
        $this->authorizeSeoSpecialist();
        $keyword->load(['rankings' => fn ($query) => $query->limit(12)]);

        return view('owner.seo.keywords.form', [
            'keyword' => $keyword,
            'rankings' => $keyword->rankings,
            'intentLabels' => $this->intentLabels(),
            'priorityLabels' => $this->priorityLabels(),
            'statusLabels' => $this->statusLabels(),
        ]);
    }

    public function update(Request $request, SeoKeyword $keyword): RedirectResponse
    {
        $this->authorizeSeoSpecialist();
        $data = $request->validate($this->rules());
        $data['keyword'] = preg_replace('/\\s+/u', ' ', trim($data['keyword'])) ?? trim($data['keyword']);
        $key = $this->normalizeKeyword($data['keyword']);

        if (SeoKeyword::where('keyword_key', $key)->where('id', '<>', $keyword->id)->exists()) {
            return back()->withInput()->withErrors([
                'keyword' => 'این عبارت با رکورد دیگری تکراری است.',
            ]);
        }

        [$payload, $observation] = $this->splitObservation($data);
        $payload['keyword_key'] = $key;
        $payload['volume_source'] = filled($payload['search_volume'] ?? null) ? 'manual' : null;

        DB::transaction(function () use ($keyword, $payload, $observation, $request): void {
            $keyword->update($payload);
            if ($observation['position'] !== null) {
                $this->recordPosition($keyword, $observation, 'manual', $request->user()->id);
            }
        });

        return redirect()->route('owner.seo.keywords.edit', $keyword)
            ->with('success', 'اطلاعات کلمه ذخیره شد.');
    }

    public function storeRanking(Request $request, SeoKeyword $keyword): RedirectResponse
    {
        $this->authorizeSeoSpecialist();
        $data = $request->validate([
            'position' => ['required', 'integer', 'between:1,1000'],
            'observed_on' => ['required', 'date'],
            'search_volume' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'impressions' => ['nullable', 'integer', 'min:0', 'max:9223372036854775807'],
            'clicks' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'ctr' => ['nullable', 'numeric', 'between:0,100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        DB::transaction(function () use ($keyword, $data, $request): void {
            $this->recordPosition($keyword, [
                'position' => (int) $data['position'],
                'observed_on' => $data['observed_on'],
                'search_volume' => $data['search_volume'] ?? null,
                'impressions' => $data['impressions'] ?? null,
                'clicks' => $data['clicks'] ?? null,
                'ctr' => $data['ctr'] ?? null,
                'notes' => $data['notes'] ?? null,
            ], 'manual', $request->user()->id);
        });

        return redirect()->route('owner.seo.keywords.edit', $keyword)
            ->with('success', 'مشاهدهٔ رتبه ثبت شد. این رکورد تاریخچهٔ دستی است و از Google دریافت نشده است.');
    }

    public function destroy(SeoKeyword $keyword): RedirectResponse
    {
        $this->authorizeSeoSpecialist();
        $keyword->delete();

        return redirect()->route('owner.seo.keywords.index')
            ->with('success', 'کلمه و تاریخچهٔ ثبت‌شدهٔ آن حذف شد.');
    }

    public function import(Request $request): RedirectResponse
    {
        $this->authorizeSeoSpecialist();
        $request->validate(['file' => ['required', 'file', 'max:5120']]);
        $file = $request->file('file');

        if (! in_array(Str::lower($file->getClientOriginalExtension()), ['csv', 'txt'], true)) {
            return back()->withErrors(['file' => 'فایل باید CSV یا TXT با جداکنندهٔ ویرگول باشد.']);
        }

        $stream = fopen($file->getRealPath(), 'rb');
        if ($stream === false) {
            return back()->withErrors(['file' => 'فایل برای خواندن باز نشد.']);
        }

        try {
            $headerRow = fgetcsv($stream);
            if (! is_array($headerRow) || $headerRow === []) {
                return back()->withErrors(['file' => 'فایل خالی است یا سطر عنوان ندارد.']);
            }

            $headers = array_map(fn ($header) => $this->normalizeHeader((string) $header), $headerRow);
            if (! in_array('keyword', $headers, true)) {
                return back()->withErrors(['file' => 'ستون اجباری keyword یا «کلمه کلیدی» در فایل پیدا نشد.']);
            }

            $rows = [];
            $invalidRows = 0;
            $line = 1;
            while (($cells = fgetcsv($stream)) !== false) {
                $line++;
                if ($line > 5001) {
                    return back()->withErrors(['file' => 'حداکثر ۵۰۰۰ ردیف داده در هر بار ورود پذیرفته می‌شود. فایل را به چند بخش تقسیم کن.']);
                }

                $cells = array_slice(array_pad($cells, count($headers), ''), 0, count($headers));
                $mapped = array_combine($headers, array_map(fn ($value) => trim((string) $value), $cells));
                $rawKeyword = trim((string) ($mapped['keyword'] ?? ''));
                if ($rawKeyword === '') {
                    continue;
                }
                if (mb_strlen($rawKeyword) > 255) {
                    $invalidRows++;
                    continue;
                }

                $targetUrl = trim((string) ($mapped['target_url'] ?? ''));
                if ($targetUrl !== '' && filter_var($targetUrl, FILTER_VALIDATE_URL) === false) {
                    $invalidRows++;
                    continue;
                }

                $rows[] = [
                    'keyword' => $rawKeyword,
                    'keyword_key' => $this->normalizeKeyword($rawKeyword),
                    'cluster' => $this->nullableText($mapped['cluster'] ?? null, 120),
                    'intent' => $this->normalizeChoice($mapped['intent'] ?? null, $this->intentAliases(), 'informational'),
                    'target_url' => $targetUrl ?: null,
                    'priority' => $this->normalizeChoice($mapped['priority'] ?? null, $this->priorityAliases(), 'medium'),
                    'status' => $this->normalizeChoice($mapped['status'] ?? null, $this->statusAliases(), 'idea'),
                    'search_volume' => $this->nullableUnsignedInt($mapped['search_volume'] ?? null),
                    'volume_source' => filled($mapped['search_volume'] ?? null) ? 'csv_import' : null,
                    'target_position' => $this->nullableBoundedInt($mapped['target_position'] ?? null, 1, 1000),
                    'observed_position' => $this->nullableBoundedInt($mapped['observed_position'] ?? null, 1, 1000),
                    'observed_on' => $this->validDate($mapped['observed_on'] ?? null),
                    'impressions' => $this->nullableUnsignedInt($mapped['impressions'] ?? null),
                    'clicks' => $this->nullableUnsignedInt($mapped['clicks'] ?? null),
                    'ctr' => $this->nullablePercent($mapped['ctr'] ?? null),
                    'notes' => $this->nullableText($mapped['notes'] ?? null, 5000),
                ];
            }
        } finally {
            fclose($stream);
        }

        $existingKeys = [];
        $uniqueKeys = collect($rows)->pluck('keyword_key')->unique()->values();
        foreach ($uniqueKeys->chunk(500) as $keyChunk) {
            foreach (SeoKeyword::whereIn('keyword_key', $keyChunk->all())->pluck('keyword_key') as $existingKey) {
                $existingKeys[$existingKey] = true;
            }
        }

        $added = 0;
        $skipped = 0;
        DB::transaction(function () use ($rows, &$existingKeys, &$added, &$skipped, $request): void {
            foreach ($rows as $row) {
                $key = $row['keyword_key'];
                if (isset($existingKeys[$key])) {
                    $skipped++;
                    continue;
                }

                $observation = [
                    'position' => $row['observed_position'],
                    'observed_on' => $row['observed_on'] ?: now()->toDateString(),
                    'search_volume' => $row['search_volume'],
                    'impressions' => $row['impressions'],
                    'clicks' => $row['clicks'],
                    'ctr' => $row['ctr'],
                    'notes' => 'واردشده از CSV',
                ];
                unset($row['observed_position'], $row['observed_on'], $row['impressions'], $row['clicks'], $row['ctr']);
                $row['created_by'] = $request->user()->id;
                $keyword = SeoKeyword::create($row);
                $existingKeys[$key] = true;
                $added++;

                if ($observation['position'] !== null) {
                    $this->recordPosition($keyword, $observation, 'csv_import', $request->user()->id);
                }
            }
        });

        return redirect()->route('owner.seo.keywords.index')
            ->with('success', "ورود CSV تمام شد: {$added} کلمهٔ جدید اضافه شد و {$skipped} ردیف تکراری و {$invalidRows} ردیف نامعتبر رد شد. رتبه‌های واردشده با منبع CSV علامت‌گذاری شده‌اند.");
    }

    public function template(): StreamedResponse
    {
        $this->authorizeSeoSpecialist();

        return response()->streamDownload(function (): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\\xEF\\xBB\\xBF");
            fputcsv($output, [
                'keyword', 'cluster', 'intent', 'target_url', 'priority', 'status',
                'search_volume', 'target_position', 'observed_position', 'observed_on', 'notes',
            ]);
            fclose($output);
        }, 'sheykhan-seo-keywords-template.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorizeSeoSpecialist();
        $query = $this->filteredQuery($request);

        return response()->streamDownload(function () use ($query): void {
            $output = fopen('php://output', 'wb');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'keyword', 'cluster', 'intent', 'target_url', 'priority', 'status',
                'search_volume', 'volume_source', 'target_position',
                'observed_position', 'observed_on', 'observed_source', 'notes',
            ]);

            $query->orderBy('keyword')->chunk(500, function ($keywords) use ($output): void {
                foreach ($keywords as $keyword) {
                    fputcsv($output, array_map(fn ($cell) => $this->safeCsvCell($cell), [
                        $keyword->keyword,
                        $keyword->cluster,
                        $keyword->intent,
                        $keyword->target_url,
                        $keyword->priority,
                        $keyword->status,
                        $keyword->search_volume,
                        $keyword->volume_source,
                        $keyword->target_position,
                        $keyword->last_observed_position,
                        $keyword->last_observed_at?->toDateString(),
                        $keyword->last_observed_source,
                        $keyword->notes,
                    ]));
                }
            });
            fclose($output);
        }, 'sheykhan-seo-keywords.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-store, private',
        ]);
    }

    private function rules(): array
    {
        return [
            'keyword' => ['required', 'string', 'max:255'],
            'cluster' => ['nullable', 'string', 'max:120'],
            'intent' => ['required', 'in:' . implode(',', self::INTENTS)],
            'target_url' => ['nullable', 'url', 'max:2048'],
            'priority' => ['required', 'in:' . implode(',', self::PRIORITIES)],
            'status' => ['required', 'in:' . implode(',', self::STATUSES)],
            'search_volume' => ['nullable', 'integer', 'min:0', 'max:2147483647'],
            'target_position' => ['nullable', 'integer', 'between:1,1000'],
            'observed_position' => ['nullable', 'integer', 'between:1,1000'],
            'observed_on' => ['nullable', 'date'],
            'observation_notes' => ['nullable', 'string', 'max:500'],
            'notes' => ['nullable', 'string', 'max:5000'],
        ];
    }

    private function splitObservation(array $data): array
    {
        $observation = [
            'position' => isset($data['observed_position']) && $data['observed_position'] !== ''
                ? (int) $data['observed_position']
                : null,
            'observed_on' => ($data['observed_on'] ?? null) ?: now()->toDateString(),
            'notes' => $data['observation_notes'] ?? null,
            'search_volume' => isset($data['search_volume']) && $data['search_volume'] !== ''
                ? (int) $data['search_volume']
                : null,
            'impressions' => null,
            'clicks' => null,
            'ctr' => null,
        ];
        unset($data['observed_position'], $data['observed_on'], $data['observation_notes']);

        return [$data, $observation];
    }

    private function recordPosition(SeoKeyword $keyword, array $data, string $source, int $userId): void
    {
        SeoKeywordRanking::create([
            'seo_keyword_id' => $keyword->id,
            'observed_on' => $data['observed_on'] ?? now()->toDateString(),
            'position' => $data['position'],
            'search_volume' => $data['search_volume'] ?? null,
            'impressions' => $data['impressions'] ?? null,
            'clicks' => $data['clicks'] ?? null,
            'ctr' => $data['ctr'] ?? null,
            'source' => $source,
            'notes' => $data['notes'] ?? null,
            'created_by' => $userId,
        ]);

        $observedOn = $data['observed_on'] ?? now()->toDateString();
        $isLatestObservation = $keyword->last_observed_at === null
            || \Illuminate\Support\Carbon::parse($observedOn)->greaterThanOrEqualTo($keyword->last_observed_at);

        // Keep the headline position tied to the newest observation; an imported
        // historical CSV row must not overwrite a newer manually recorded rank.
        if ($isLatestObservation) {
            $keyword->update([
                'last_observed_position' => $data['position'],
                'last_observed_at' => $observedOn,
                'last_observed_source' => $source,
                ...(isset($data['search_volume']) && $data['search_volume'] !== null
                    ? ['search_volume' => $data['search_volume'], 'volume_source' => $source]
                    : []),
            ]);
        }
    }

    private function filteredQuery(Request $request)
    {
        return SeoKeyword::query()
            ->search($request->query('q'))
            ->when(in_array($request->query('status'), self::STATUSES, true),
                fn ($query) => $query->where('status', $request->query('status')))
            ->when(in_array($request->query('intent'), self::INTENTS, true),
                fn ($query) => $query->where('intent', $request->query('intent')))
            ->when(in_array($request->query('priority'), self::PRIORITIES, true),
                fn ($query) => $query->where('priority', $request->query('priority')))
            ->when(filled($request->query('cluster')),
                fn ($query) => $query->where('cluster', $request->query('cluster')));
    }

    private function authorizeSeoSpecialist(): void
    {
        $user = request()->user();
        abort_unless(
            $user
            && $user->hasPermission('seo.manage')
            && $user->hasRole('seo-specialist')
            && ! $user->hasRole('academy-owner'),
            403
        );
    }

    private function normalizeKeyword(string $keyword): string
    {
        $keyword = str_replace(['ي', 'ك', 'ـ', "‌"], ['ی', 'ک', '', ' '], trim($keyword));
        $keyword = preg_replace('/[\x{064B}-\x{065F}\x{0670}]/u', '', $keyword) ?? $keyword;
        $keyword = preg_replace('/\s+/u', ' ', $keyword) ?? trim($keyword);

        return Str::lower(trim($keyword));
    }

    private function normalizeHeader(string $value): string
    {
        $value = Str::lower(trim(str_replace("\xEF\xBB\xBF", '', $value)));
        $aliases = [
            'کلمه کلیدی' => 'keyword', 'عبارت کلیدی' => 'keyword',
            'خوشه' => 'cluster', 'گروه' => 'cluster',
            'نیت' => 'intent', 'قصد جستجو' => 'intent',
            'آدرس هدف' => 'target_url', 'صفحه هدف' => 'target_url',
            'اولویت' => 'priority', 'وضعیت' => 'status',
            'حجم جستجو' => 'search_volume', 'حجم جستجوی ماهانه' => 'search_volume',
            'رتبه هدف' => 'target_position', 'رتبه مشاهده شده' => 'observed_position',
            'تاریخ مشاهده' => 'observed_on', 'یادداشت' => 'notes',
            'نمایش' => 'impressions', 'کلیک' => 'clicks', 'نرخ کلیک' => 'ctr',
        ];

        return $aliases[$value] ?? $value;
    }

    private function normalizeChoice(?string $value, array $aliases, string $fallback): string
    {
        $value = Str::lower(trim((string) $value));

        return $aliases[$value] ?? (in_array($value, $aliases, true) ? $value : $fallback);
    }

    private function nullableText(mixed $value, int $max): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $max);
    }

    private function nullableUnsignedInt(mixed $value): ?int
    {
        $value = trim((string) $value);
        if ($value === '' || ! ctype_digit($value)) {
            return null;
        }

        $number = (int) $value;

        return $number >= 0 ? min($number, 2147483647) : null;
    }

    private function nullableBoundedInt(mixed $value, int $min, int $max): ?int
    {
        $number = $this->nullableUnsignedInt($value);

        return $number !== null && $number >= $min && $number <= $max ? $number : null;
    }

    private function nullablePercent(mixed $value): ?float
    {
        $value = trim((string) $value);
        if ($value === '' || ! is_numeric($value)) {
            return null;
        }

        $number = (float) $value;

        return $number >= 0 && $number <= 100 ? $number : null;
    }

    private function validDate(mixed $value): ?string
    {
        $value = trim((string) $value);
        if ($value === '') {
            return null;
        }

        try {
            return \Illuminate\Support\Carbon::parse($value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    private function safeCsvCell(mixed $value): string
    {
        $value = (string) ($value ?? '');
        if (preg_match('/^[=+\-@\t\r]/', $value)) {
            return "'" . $value;
        }

        return $value;
    }

    private function intentLabels(): array
    {
        return [
            'informational' => 'اطلاعاتی',
            'commercial' => 'تجاری / مقایسه‌ای',
            'transactional' => 'خرید / اقدام',
            'navigational' => 'جست‌وجوی نام مشخص',
        ];
    }

    private function priorityLabels(): array
    {
        return ['high' => 'زیاد', 'medium' => 'متوسط', 'low' => 'کم'];
    }

    private function statusLabels(): array
    {
        return [
            'idea' => 'ایدهٔ جدید',
            'planned' => 'برنامه‌ریزی‌شده',
            'in_progress' => 'در حال انجام',
            'optimized' => 'بهینه‌شده',
            'tracking' => 'در حال رصد',
            'done' => 'تکمیل‌شده',
        ];
    }

    private function intentAliases(): array
    {
        return [
            'اطلاعاتی' => 'informational', 'اطلاعاتی / آموزشی' => 'informational',
            'تجاری' => 'commercial', 'تجاری / مقایسه‌ای' => 'commercial',
            'خرید' => 'transactional', 'تراکنشی' => 'transactional', 'خرید / اقدام' => 'transactional',
            'ناوبری' => 'navigational', 'جست‌وجوی نام مشخص' => 'navigational',
            'informational' => 'informational', 'commercial' => 'commercial',
            'transactional' => 'transactional', 'navigational' => 'navigational',
        ];
    }

    private function priorityAliases(): array
    {
        return [
            'زیاد' => 'high', 'بالا' => 'high', 'high' => 'high',
            'متوسط' => 'medium', 'medium' => 'medium',
            'کم' => 'low', 'پایین' => 'low', 'low' => 'low',
        ];
    }

    private function statusAliases(): array
    {
        return [
            'ایده' => 'idea', 'ایدهٔ جدید' => 'idea', 'idea' => 'idea',
            'برنامه‌ریزی' => 'planned', 'برنامه‌ریزی‌شده' => 'planned', 'planned' => 'planned',
            'در حال انجام' => 'in_progress', 'in_progress' => 'in_progress',
            'بهینه‌شده' => 'optimized', 'بهینه شده' => 'optimized', 'optimized' => 'optimized',
            'در حال رصد' => 'tracking', 'tracking' => 'tracking',
            'تکمیل' => 'done', 'تکمیل‌شده' => 'done', 'done' => 'done',
        ];
    }
}
