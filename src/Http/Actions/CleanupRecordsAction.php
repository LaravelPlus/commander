<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;

final class CleanupRecordsAction extends BaseAction
{
    public function __construct(
        CommanderServiceInterface $commanderService,
        private Request $request,
    ) {
        parent::__construct($commanderService);
    }

    public function execute(): JsonResponse
    {
        return $this->executeWithExceptionHandling(
            fn (): JsonResponse => $this->cleanupRecords(),
            'CleanupRecords',
        );
    }

    private function cleanupRecords(): JsonResponse
    {
        $days = $this->request->input('days', 90);

        $deletedCount = $this->commanderService->cleanupOldRecords($days);

        return $this->successResponse(
            ['deleted_count' => $deletedCount],
            "Successfully cleaned up {$deletedCount} old records",
        );
    }
}
