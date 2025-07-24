<?php

declare(strict_types=1);

namespace LaravelPlus\Commander\Http\Actions;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use LaravelPlus\Commander\Contracts\CommanderServiceInterface;

final class GetActivityAction extends BaseAction
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
            fn (): JsonResponse => $this->getActivity(),
            'GetActivity',
        );
    }

    private function getActivity(): JsonResponse
    {
        $page = (int) $this->request->input('page', 1);
        $perPage = (int) $this->request->input('per_page', 20);
        $status = $this->request->input('status', '');
        $command = $this->request->input('command', '');
        $dateFrom = $this->request->input('dateFrom', '');
        $dateTo = $this->request->input('dateTo', '');

        $activity = $this->commanderService->getActivity($page, $perPage, $status, $command, $dateFrom, $dateTo);

        return $this->successResponse(
            $activity,
            'Activity data loaded successfully',
        );
    }
}
