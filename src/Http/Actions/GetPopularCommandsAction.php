<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;

final class GetPopularCommandsAction extends BaseAction
{
    public function __construct(
        CommanderServiceInterface $commanderService,
        private int $limit = 5,
    ) {
        parent::__construct($commanderService);
    }

    public function execute(): JsonResponse
    {
        return $this->executeWithExceptionHandling(
            fn (): JsonResponse => $this->directResponse(
                $this->commanderService->getPopularCommands($this->limit)->toArray(),
            ),
            'GetPopularCommands',
        );
    }
}
