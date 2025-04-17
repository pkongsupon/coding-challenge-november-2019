<?php

namespace App\Repositories;

use App\Models\Log;

class LogRepository
{
    protected Log $log;

    public function __construct(Log $log)
    {
        $this->log = $log;
    }

    public function saveLog(string $left, string $right): int
    {
        return $this->log->insertGetId([
            'left' => $left,
            'right' => $right,
            'created_at' => now(),
            'updated_at' => now()
        ]);
    }

    public function searchLog(string $left, string $right)
    {
        return $this->log->with('result')
                ->where([
                'left' => $left,
                'right' => $right
            ])
            ->orderBy('id', 'desc')
            ->limit(1)
            ->get();
    }

    public function updateLog(int $logId, $cntResult, $iteration)
    {
        $this->log
            ->where('id', $logId)
            ->update([
                'cnt_result' => $cntResult,
                'iteration' => $iteration,
                'updated_at' => now()
            ]);
    }
}
