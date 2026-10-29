<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Models\Academy;
use App\Models\ProtectedFileView;
use App\Models\ProtectedLearningMediaView;
use App\Models\LearningResource;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\Request;
use Illuminate\View\View;

final class ProtectedFileViewController extends Controller
{
    public function index(Academy $academy, Request $request, OwnerWorkspaceService $workspace): View
    {
        abort_unless($request->user()->hasPermission('orders.view'), 403);
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 404);

        $data = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'event' => ['nullable', 'in:open,page'],
        ]);

        $search = trim((string) ($data['q'] ?? ''));

        $query = ProtectedFileView::query()
            ->whereHas('productFile.product', fn ($product) => $product->where('academy_id', $academy->id))
            ->with([
                'user:id,name,email',
                'order:id,order_number,buyer_id',
                'productFile.product:id,title,academy_id',
                'productFile.media:id,original_name',
            ])
            ->when(isset($data['event']), fn ($builder) => $builder->where('event_type', $data['event']))
            ->when($search !== '', function ($builder) use ($search): void {
                $builder->where(function ($filter) use ($search): void {
                    $filter
                        ->whereHas('user', fn ($user) => $user->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%'))
                        ->orWhereHas('order', fn ($order) => $order->where('order_number', 'like', '%' . $search . '%'))
                        ->orWhereHas('productFile.product', fn ($product) => $product->where('title', 'like', '%' . $search . '%'));
                });
            })
            ->orderByDesc('viewed_at')
            ->orderByDesc('id');

        $views = $query->paginate(30)->withQueryString();

        $learningQuery = ProtectedLearningMediaView::query()
            ->whereIn('learning_resource_id', LearningResource::query()
                ->where('academy_id', $academy->id)
                ->select('id'))
            ->with(['user:id,name,email', 'media:id,original_name'])
            ->when(isset($data['event']), fn ($builder) => $builder->where('event_type', $data['event']))
            ->when($search !== '', function ($builder) use ($search, $academy): void {
                $builder->where(function ($filter) use ($search, $academy): void {
                    $filter
                        ->whereHas('user', fn ($user) => $user->where('name', 'like', '%' . $search . '%')->orWhere('email', 'like', '%' . $search . '%'))
                        ->orWhereHas('media', fn ($media) => $media->where('original_name', 'like', '%' . $search . '%'));

                    if (ctype_digit($search)) {
                        $filter->orWhere('learning_resource_id', (int) $search)
                            ->orWhere('child_id', (int) $search);
                    }

                    $filter->orWhereIn('learning_resource_id', LearningResource::query()
                        ->where('academy_id', $academy->id)
                        ->where('title', 'like', '%' . $search . '%')
                        ->select('id'));
                });
            })
            ->orderByDesc('viewed_at')
            ->orderByDesc('id');

        $learningViews = $learningQuery->paginate(30, ['*'], 'learning_page')->withQueryString();

        return view('owner.protected-file-views.index', compact('academy', 'views', 'learningViews', 'search'));
    }
}
