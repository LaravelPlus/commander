<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;

final class GetRecentExecutionsAction extends BaseAction
{
    public function __construct(
        CommanderServiceInterface $commanderService,
        private int $limit = 10,
    ) {
        parent::__construct($commanderService);
    }

    public function execute(): JsonResponse
    {
        return $this->executeWithExceptionHandling(
            fn (): JsonResponse => $this->directResponse(
                $this->commanderService->getRecentExecutions($this->limit)->toArray(),
            ),
            'GetRecentExecutions',
        );
    }
}
