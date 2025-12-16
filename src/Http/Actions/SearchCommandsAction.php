<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;

final class SearchCommandsAction extends BaseAction
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
            fn (): JsonResponse => $this->searchCommands(),
            'SearchCommands',
        );
    }

    private function searchCommands(): JsonResponse
    {
        $query = $this->request->input('query', '');

        $commands = $this->commanderService->searchCommands($query);

        return $this->successResponse(
            $commands,
            'Commands search completed successfully',
        );
    }
}
