<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;

final class GetDashboardStatsAction extends BaseAction
{
    public function execute(): JsonResponse
    {
        return $this->executeWithExceptionHandling(
            fn (): JsonResponse => $this->successResponse(
                $this->commanderService->getDashboardStats(),
                'Dashboard statistics loaded successfully',
            ),
            'GetDashboardStats',
        );
    }
}
