<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;

final class GetCommandStatsAction extends BaseAction
{
    public function __construct(
        CommanderServiceInterface $commanderService,
        private string $commandName,
    ) {
        parent::__construct($commanderService);
    }

    public function execute(): JsonResponse
    {
        return $this->executeWithExceptionHandling(
            fn (): JsonResponse => $this->successResponse(
                $this->commanderService->getCommandStats($this->commandName),
                'Command statistics loaded successfully',
            ),
            'GetCommandStats',
        );
    }
}
