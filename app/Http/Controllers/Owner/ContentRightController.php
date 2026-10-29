<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\ContentRight;
use App\Models\Media;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

final class ContentRightController extends Controller
{
    private const SOURCE_TYPES = [
        'owned',
        'licensed',
        'commissioned',
        'public_domain',
        'third_party',
    ];

    private const PERMITTED_USES = [
        'streaming',
        'paid_distribution',
        'download',
        'promotion',
        'excerpt',
    ];

    public function index(Request $request): View
    {
        $this->authorizeManage($request);

        return view('owner.content-rights.index', $this->viewData($request));
    }

    public function edit(Request $request, ContentRight $contentRight): View
    {
        $this->authorizeManage($request);
        abort_unless($contentRight->status === 'draft', 409, 'فقط پیش‌نویس قابل ویرایش است.');

        $contentRight->load('media');

        return view('owner.content-rights.index', $this->viewData($request, $contentRight));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManage($request);
        $data = $request->validate($this->rules());
        $evidencePath = $request->file('evidence')?->store('private/content-rights', 'local');

        unset($data['evidence']);
        $data['permitted_uses'] = array_values($data['permitted_uses']);
        $data['created_by'] = $request->user()->id;
        $data['evidence_path'] = $evidencePath;
        $data['status'] = 'draft';

        try {
            DB::transaction(function () use ($data): void {
                Media::query()
                    ->whereKey($data['media_id'])
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->firstOrFail();

                ContentRight::query()->create($data);
            });
        } catch (Throwable $exception) {
            if ($evidencePath) {
                Storage::disk('local')->delete($evidencePath);
            }

            throw $exception;
        }

        return redirect()
            ->route('owner.content-rights.index')
            ->with('success', 'پرونده حقوق نشر به‌صورت پیش‌نویس ثبت شد؛ پس از بررسی مدرک، آن را فعال کنید.');
    }

    public function update(Request $request, ContentRight $contentRight): RedirectResponse
    {
        $this->authorizeManage($request);
        abort_unless($contentRight->status === 'draft', 409, 'پرونده منتشرشده قابل ویرایش نیست؛ برای تغییر، نسخه جدید ثبت کنید.');

        $data = $request->validate($this->rules());
        $oldEvidencePath = $contentRight->evidence_path;
        $newEvidencePath = $request->file('evidence')?->store('private/content-rights', 'local');

        unset($data['evidence']);
        $data['permitted_uses'] = array_values($data['permitted_uses']);
        if ($newEvidencePath) {
            $data['evidence_path'] = $newEvidencePath;
        }

        try {
            DB::transaction(function () use ($data, $contentRight): void {
                Media::query()
                    ->whereKey($data['media_id'])
                    ->where('status', 'active')
                    ->lockForUpdate()
                    ->firstOrFail();

                $locked = ContentRight::query()->whereKey($contentRight->id)->lockForUpdate()->firstOrFail();
                abort_unless($locked->status === 'draft', 409, 'وضعیت پرونده تغییر کرده است؛ صفحه را تازه‌سازی کنید.');
                $locked->fill($data)->save();
            });
        } catch (Throwable $exception) {
            if ($newEvidencePath) {
                Storage::disk('local')->delete($newEvidencePath);
            }

            throw $exception;
        }

        if ($newEvidencePath && $oldEvidencePath) {
            Storage::disk('local')->delete($oldEvidencePath);
        }

        return redirect()
            ->route('owner.content-rights.index')
            ->with('success', 'پیش‌نویس حقوق نشر به‌روزرسانی شد.');
    }

    public function activate(Request $request, ContentRight $contentRight): RedirectResponse
    {
        $this->authorizeManage($request);

        DB::transaction(function () use ($contentRight): void {
            $right = ContentRight::query()->whereKey($contentRight->id)->lockForUpdate()->firstOrFail();
            abort_unless($right->status === 'draft', 409, 'فقط پیش‌نویس قابل فعال‌سازی است.');
            abort_if($right->valid_from?->isFuture(), 422, 'تاریخ شروع مجوز هنوز نرسیده است.');
            abort_if($right->valid_until?->isPast(), 422, 'اعتبار این مجوز منقضی شده است.');

            Media::query()
                ->whereKey($right->media_id)
                ->where('status', 'active')
                ->lockForUpdate()
                ->firstOrFail();

            ContentRight::query()
                ->where('media_id', $right->media_id)
                ->where('status', 'active')
                ->where('id', '!=', $right->id)
                ->update(['status' => 'superseded', 'updated_at' => now()]);

            $right->forceFill(['status' => 'active'])->save();
        });

        return redirect()
            ->route('owner.content-rights.index')
            ->with('success', 'مجوز فعال شد؛ مجوز فعال قبلی همین فایل برای حفظ سابقه به حالت جایگزین‌شده رفت.');
    }

    public function revoke(Request $request, ContentRight $contentRight): RedirectResponse
    {
        $this->authorizeManage($request);
        abort_unless($contentRight->status === 'active', 409, 'فقط مجوز فعال قابل لغو است.');

        $contentRight->forceFill(['status' => 'revoked'])->save();

        return redirect()
            ->route('owner.content-rights.index')
            ->with('success', 'مجوز لغو شد و سابقه آن حفظ شد.');
    }

    public function evidence(Request $request, ContentRight $contentRight)
    {
        $this->authorizeManage($request);
        abort_if(blank($contentRight->evidence_path), 404);
        abort_unless(Storage::disk('local')->exists($contentRight->evidence_path), 404);

        return Storage::disk('local')->download(
            $contentRight->evidence_path,
            'content-right-' . $contentRight->id . '.' . pathinfo($contentRight->evidence_path, PATHINFO_EXTENSION)
        );
    }

    private function authorizeManage(Request $request): void
    {
        abort_unless($request->user()?->hasPermission('legal.manage'), 403);
    }

    private function rules(): array
    {
        return [
            'media_id' => [
                'required',
                'integer',
                Rule::exists('media', 'id')->where(fn ($query) => $query->where('status', 'active')),
            ],
            'title' => ['required', 'string', 'max:255'],
            'rights_holder' => ['required', 'string', 'max:255'],
            'source_type' => ['required', Rule::in(self::SOURCE_TYPES)],
            'license_name' => ['required', 'string', 'max:150'],
            'license_reference' => ['nullable', 'string', 'max:190'],
            'source_url' => ['nullable', 'url', 'max:2048'],
            'territory' => ['nullable', 'string', 'max:120'],
            'valid_from' => ['nullable', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'permitted_uses' => ['required', 'array', 'min:1'],
            'permitted_uses.*' => [Rule::in(self::PERMITTED_USES)],
            'attribution' => ['nullable', 'string', 'max:5000'],
            'evidence' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ];
    }

    private function viewData(Request $request, ?ContentRight $editingRight = null): array
    {
        $search = mb_substr(trim($request->query('q', '')), 0, 100);
        $mediaSearch = mb_substr(trim($request->query('media_q', '')), 0, 100);
        $status = in_array($request->query('status', 'all'), ['all', 'draft', 'active', 'revoked', 'superseded'], true)
            ? $request->query('status', 'all')
            : 'all';

        $rightsQuery = ContentRight::query()
            ->with(['media:id,original_name,collection,mime_type,size', 'creator:id,name'])
            ->when($status !== 'all', fn ($query) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($nested) use ($search): void {
                    $nested->where('title', 'like', '%' . $search . '%')
                        ->orWhere('rights_holder', 'like', '%' . $search . '%')
                        ->orWhere('license_reference', 'like', '%' . $search . '%')
                        ->orWhereHas('media', fn ($media) => $media->where('original_name', 'like', '%' . $search . '%'));
                });
            });

        $mediaOptions = Media::query()
            ->where('status', 'active')
            ->when($mediaSearch !== '', function ($query) use ($mediaSearch): void {
                $query->where(function ($nested) use ($mediaSearch): void {
                    $nested->where('original_name', 'like', '%' . $mediaSearch . '%')
                        ->orWhere('collection', 'like', '%' . $mediaSearch . '%');
                });
            })
            ->orderByDesc('id')
            ->limit(100)
            ->get(['id', 'original_name', 'collection', 'mime_type', 'size']);

        if ($editingRight && $editingRight->media) {
            $mediaOptions->prepend($editingRight->media);
            $mediaOptions = $mediaOptions->unique('id')->values();
        }

        $now = now();
        $activeCount = ContentRight::query()
            ->where('status', 'active')
            ->where(fn ($query) => $query->whereNull('valid_from')->orWhere('valid_from', '<=', $now))
            ->where(fn ($query) => $query->whereNull('valid_until')->orWhere('valid_until', '>=', $now))
            ->count();

        return [
            'rights' => $rightsQuery->orderByDesc('created_at')->paginate(15)->withQueryString(),
            'mediaOptions' => $mediaOptions,
            'editingRight' => $editingRight ?? new ContentRight(),
            'search' => $search,
            'mediaSearch' => $mediaSearch,
            'status' => $status,
            'activeCount' => $activeCount,
            'draftCount' => ContentRight::query()->where('status', 'draft')->count(),
            'expiredCount' => ContentRight::query()->where('status', 'active')->whereNotNull('valid_until')->where('valid_until', '<', $now)->count(),
            'sourceLabels' => [
                'owned' => 'مالکیت مستقیم',
                'licensed' => 'دارای مجوز',
                'commissioned' => 'تولید سفارشی',
                'public_domain' => 'مالکیت عمومی / حوزه عمومی',
                'third_party' => 'محتوای شخص ثالث',
            ],
            'useLabels' => [
                'streaming' => 'پخش آنلاین',
                'paid_distribution' => 'فروش یا توزیع پولی',
                'download' => 'دانلود فایل',
                'promotion' => 'استفاده تبلیغاتی',
                'excerpt' => 'نمایش بخشی از اثر',
            ],
        ];
    }
}
