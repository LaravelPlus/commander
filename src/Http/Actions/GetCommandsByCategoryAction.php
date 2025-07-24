<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;

final class GetCommandsByCategoryAction extends BaseAction
{
    public function __construct(
        CommanderServiceInterface $commanderService,
        private string $category,
    ) {
        parent::__construct($commanderService);
    }

    public function execute(): JsonResponse
    {
        return $this->executeWithExceptionHandling(
            fn (): JsonResponse => $this->successResponse(
                $this->commanderService->getCommandsByCategory($this->category),
                'Commands by category loaded successfully',
            ),
            'GetCommandsByCategory',
        );
    }
}
