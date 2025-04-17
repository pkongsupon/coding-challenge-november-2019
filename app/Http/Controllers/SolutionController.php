<?php

namespace App\Http\Controllers;

use App\Http\Requests\findDigitsRequest;
use App\Services\FindDigitsService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SolutionController extends Controller
{
    protected $findDigitsService;

    protected $left;
    protected $right;
    protected $params;
    protected $paramCount;
    protected $maxLength;
    protected $flag;
    protected $noZeroLeading;
    protected $iterations;
    protected $cntResult;
    protected $assignedLetter;
    protected $logId;
    protected $resultArr;

    public function __construct(FindDigitsService $findDigitsService)
    {
        $this->findDigitsService = $findDigitsService;
    }

    public function findDigits(findDigitsRequest $request)
    {
        try {
            DB::beginTransaction();

            $this->left = array_filter($request->get('left'));
            $this->right = $request->get('right');
            $log = $this->findDigitsService->searchLog($this->left, $this->right);
            if(!$log->isEmpty()) {
                $resultsComponent = view('components.results', ["data" => $log->toArray()[0]['result']])->render();
                return response()->json(["component" => $resultsComponent]);
            }
            $this->logId = $this->findDigitsService->saveLog($this->left, $this->right);
            $this->setupParams();

            $all_letters = array_unique(array_merge(...$this->params));
            if (count($all_letters) > 10) return response()->json(["message" => "It has more than ten characters."], 404);

            $this->checkEquation();
            $this->findDigitsService->saveResults($this->resultArr);
            $this->findDigitsService->updateLog($this->logId, $this->cntResult, $this->iterations);

            DB::commit();

            $resultsComponent = view('components.results', ["data" => $this->resultArr])->render();
            return response()->json(["component" => $resultsComponent]);
        } catch(\Exception $exp) {
            DB::rollBack();

            $error_message = 'Error: ' . $exp->getMessage();

            Log::error($error_message);
            throw new \Exception($error_message);
        }

    }

    private function setupParams() {
        $params = array_merge($this->left, $this->right);

        $this->noZeroLeading = array_map(function($s){
            return $s[0];
        }, $params);
        $this->paramCount = count($params);
        $this->flag = array_fill(0, $this->paramCount, 1);
        $this->flag[$this->paramCount-1] = -1; // Right side of the equation
        $this->left = array_map(function($s) {
            return str_split(strrev($s));
        }, $this->left);
        $this->right[0] = str_split(strrev($this->right[0]));

        // reduce equation
        $this->maxLength = max(array_map('strlen', $params));
        for ($column=0; $column < $this->maxLength; $column++) {
            for ($row=0; $row < count($this->left); $row++) {
                if (!isset($this->left[$row][$column])) continue;
                if ($this->left[$row][$column] == $this->right[0][$column]){
                    unset($this->right[0][$column]);
                    unset($this->left[$row][$column]);
                    break;
                }
            }
        }
        $this->params = array_merge($this->left, $this->right);
        $this->iterations = 0;
        $this->cntResult = 0;
        $this->assignedLetter = [];
        $this->resultArr = [];
    }

    private function checkEquation(int $row = 0, int $column = 0, int $addition = 0)
    {
        $this->iterations++;
        if ($column >= $this->maxLength) {
            if ($addition == 0) {
                // prepare result into database
                $this->resultArr[] = [
                    'log_id' => $this->logId,
                    'order' => ++$this->cntResult,
                    'result' => $this->assignedLetter,
                    'attempt' => $this->iterations
                ];
            }
            return 0;
        }
        if ($row >= $this->paramCount){
            if ($addition % 10 != 0) {
                return 0;
            }
            return $this->checkEquation(0, $column+1, floor($addition/10));
        }
        if (!isset($this->params[$row][$column])) {
            return $this->checkEquation($row+1, $column, $addition);
        }
        $char = $this->params[$row][$column];

        if (isset($this->assignedLetter[$char])) {
            return $this->checkEquation($row+1, $column, $addition + ($this->assignedLetter[$char] * $this->flag[$row]));
        } else {
            if ($this->isLastLetterInColumn($row, $column)) {
                $matchInt = abs(($addition % 10) - 10) % 10;
                if (in_array($matchInt, $this->assignedLetter)) return false;
                if ($matchInt == 0 && in_array($char, $this->noZeroLeading)) return false;

                $this->assignedLetter[$char] = $matchInt;
                $this->checkEquation(0, $column+1, floor(($addition + $matchInt) / 10));
                unset($this->assignedLetter[$char]);
                return 0;
            } else {
                foreach (array_diff(range(0,9), $this->assignedLetter)  as $key => $num) {

                    if ($num == 0 && in_array($char, $this->noZeroLeading)) return false;
                    $this->assignedLetter[$char] = $num;
                    $this->checkEquation($row+1, $column, $addition + ($num * $this->flag[$row]));
                    unset($this->assignedLetter[$char]);
                }
            }

        }
        return 0;
    }

    private function isLastLetterInColumn(int $row, int $column) {
        while($row+1 <= $this->paramCount) {
            if (isset($this->params[$row+1][$column])) return false;
            $row++;
        }
        return true;
    }
}
