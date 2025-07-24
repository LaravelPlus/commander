<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;

final class GetFailedCommandsAction extends BaseAction
{
    public function execute(): JsonResponse
    {
        return $this->executeWithExceptionHandling(
            fn (): JsonResponse => $this->directResponse(
                $this->commanderService->getFailedCommands()->toArray(),
            ),
            'GetFailedCommands',
        );
    }
}
