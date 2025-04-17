<?php

namespace App\Services;

use App\Http\Requests\SaveResultRequest;
use App\Repositories\LogRepository;
use App\Repositories\ResultRepository;

class FindDigitsService
{
    protected ResultRepository $resultRepository;
    protected LogRepository $logRepository;

    public function __construct(
        ResultRepository $resultRepository,
        LogRepository $logRepository
    )
    {
        $this->resultRepository = $resultRepository;
        $this->logRepository = $logRepository;
    }

    public function saveLog(array $left, array $right): int
    {
        $leftJson = json_encode($left);
        $rightJson = json_encode($right);
        return $this->logRepository->saveLog($leftJson, $rightJson);
    }

    public function searchLog(array $left, array $right)
    {
        $leftJson = json_encode($left);
        $rightJson = json_encode($right);
        return $this->logRepository->searchLog($leftJson, $rightJson);
    }

    public function updateLog(int $logId, int $cntResult, int $iteration): void
    {
        $this->logRepository->updateLog($logId, $cntResult, $iteration);
    }

    public function saveResults(array $data)
    {
        $this->resultRepository->saveResults(new SaveResultRequest($data));
    }
}
