<?php

namespace App\Http\Controllers\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\PeopleDirectoryRequest;
use App\Http\Resources\OwnerPersonResource;
use App\Models\Academy;
use App\Services\OwnerWorkspaceService;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;

final class PeopleDirectoryController extends Controller
{
    public function __invoke(
        PeopleDirectoryRequest $request,
        Academy $academy,
        string $role,
        OwnerWorkspaceService $workspace,
    ): JsonResponse {
        abort_unless($workspace->canManageAcademy($request->user(), $academy), 403);

        $filters = $request->validated();
        $paginator = $workspace->peopleDirectory($request->user(), $academy, $role, $filters);

        $response = OwnerPersonResource::collection($paginator)
            ->additional([
                'directory' => [
                    'role' => $role,
                    'q' => $filters['q'] ?? '',
                    'sort' => $filters['sort'] ?? 'name',
                    'direction' => $filters['direction'] ?? 'asc',
                    'per_page' => $filters['per_page'] ?? 25,
                    'pagination_mode' => $paginator instanceof CursorPaginator ? 'cursor' : 'page',
                ],
            ])
            ->response();

        $response->headers->set('Cache-Control', 'private, no-store');

        return $response;
    }
}
