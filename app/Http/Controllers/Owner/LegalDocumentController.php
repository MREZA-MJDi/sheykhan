<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\LegalDocument;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

final class LegalDocumentController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()->hasPermission('legal.manage'), 403);

        $documents = LegalDocument::query()
            ->orderBy('code')
            ->orderByDesc('created_at')
            ->paginate(30);

        $validCodes = LegalDocument::query()
            ->whereIn('code', ['purchase-terms', 'copyright'])
            ->where('required_for_purchase', true)
            ->where('is_active', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now())
            ->get(['code', 'content', 'content_hash'])
            ->filter(fn (LegalDocument $document): bool =>
                filled(trim((string) $document->content))
                    && filled($document->content_hash)
                    && hash_equals($document->content_hash, hash('sha256', $document->content))
            )
            ->pluck('code')
            ->unique()
            ->values();

        $purchaseReady = collect(['purchase-terms', 'copyright'])
            ->every(fn (string $code): bool => $validCodes->contains($code));

        return view('owner.legal-documents.index', compact('documents', 'purchaseReady'));
    }

    public function store(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('legal.manage'), 403);

        $data = $request->validate([
            'code' => ['required', Rule::in(['purchase-terms', 'copyright', 'media-release'])],
            'version' => [
                'required', 'string', 'max:32', 'regex:/^[A-Za-z0-9._-]+$/',
                Rule::unique('legal_documents', 'version')->where(fn ($query) => $query->where('code', $request->input('code'))),
            ],
            'title' => ['required', 'string', 'max:255'],
            'content' => ['required', 'string', 'min:100', 'max:100000'],
            'publish' => ['sometimes', 'boolean'],
        ]);

        $required = in_array($data['code'], ['purchase-terms', 'copyright'], true);
        $content = trim($data['content']);
        $hash = hash('sha256', $content);
        $publish = (bool) ($data['publish'] ?? false);

        DB::transaction(function () use ($request, $data, $required, $content, $hash, $publish): void {
            if ($publish) {
                LegalDocument::query()
                    ->where('code', $data['code'])
                    ->where('is_active', true)
                    ->update(['is_active' => false]);
            }

            LegalDocument::query()->create([
                'code' => $data['code'],
                'title' => trim($data['title']),
                'version' => $data['version'],
                'content' => $content,
                'content_hash' => $hash,
                'document_type' => match ($data['code']) {
                    'purchase-terms' => 'terms_purchase',
                    'copyright' => 'copyright',
                    default => 'media_release',
                },
                'required_for_purchase' => $required,
                'is_active' => $publish,
                'published_at' => $publish ? now() : null,
            ]);
        });

        return redirect()
            ->route('owner.legal-documents.index')
            ->with('success', $publish
                ? 'نسخه جدید سند حقوقی منتشر شد. نسخه‌های قبلی غیرفعال شدند و رضایت‌های ثبت‌شده قبلی دست‌نخورده باقی می‌مانند.'
                : 'پیش‌نویس سند ذخیره شد و تا انتشار، در checkout استفاده نمی‌شود.');
    }

    public function deactivate(Request $request, LegalDocument $document): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('legal.manage'), 403);

        $document->forceFill(['is_active' => false])->save();

        return back()->with('success', 'سند غیرفعال شد. سفارش‌های قبلی و سابقه رضایت حذف نمی‌شوند.');
    }

}
