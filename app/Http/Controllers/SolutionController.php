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
    protected $all_letters;

    public function __construct(FindDigitsService $findDigitsService)
    {
        $this->findDigitsService = $findDigitsService;
    }

    public function isSolvable($words, $result) {
        $words[] = $result;
        $R = count($words);
        $C = 0;
        foreach ($words as $word) {
            $C = max($C, strlen($word));
        }

        $assigned = [];
        $assigned_inv = array_fill(0, 10, null);

        $search = function ($column, $row, $bal) use (&$search, $words, $R, $C, &$assigned, &$assigned_inv) {
            if ($column >= $C) {
                return $bal === 0;
            }
            if ($row === $R) {
                return $bal % 10 === 0 && $search($column + 1, 0, intdiv($bal, 10));
            }

            $word = $words[$row];
            if ($column >= strlen($word)) {
                return $search($column, $row + 1, $bal);
            }

            $letter = $word[strlen($word) - 1 - $column];
            $sign = $row < $R - 1 ? 1 : -1;

            if (isset($assigned[$letter])) {
                return $search($column, $row + 1, $bal + $sign * $assigned[$letter]);
            } else {
                for ($d = 0; $d <= 9; $d++) {
                    if ($assigned_inv[$d] === null && ($d !== 0 || strlen($word) - 1 !== $column)) {
                        $assigned_inv[$d] = $letter;
                        $assigned[$letter] = $d;
                        if ($search($column, $row + 1, $bal + $sign * $d)) {
                            return true;
                        }
                        $assigned_inv[$d] = null;
                        unset($assigned[$letter]);
                    }
                }
                return false;
            }
        };

        return $search(0, 0, 0);
    }


    public function findDigits(findDigitsRequest $request)
    {
        // $a = $this->isSolvable(["HIER","GIBT","ES"], "NEUES");
        // dd($a);
        try {
            DB::beginTransaction();

            $this->left = array_filter($request->get('left'));
            $this->right = $request->get('right');


            // $log = $this->findDigitsService->searchLog($this->left, $this->right);
            // if(!$log->isEmpty()) {
            //     $resultsComponent = view('components.results', ["data" => $log->toArray()[0]['result']])->render();
            //     return response()->json(["component" => $resultsComponent]);
            // }
            $this->logId = $this->findDigitsService->saveLog($this->left, $this->right);
            $this->setupParams();
            if (count($this->all_letters) > 10) return response()->json(["message" => "It has more than ten characters."], 404);

            $this->checkEquation();

            $this->resultArr = array_map(function($arr) {
                $arr['result'] = json_encode($arr['result']);
                return $arr;
            }, $this->resultArr);
            $this->findDigitsService->saveResults($this->resultArr);
            $this->findDigitsService->updateLog($this->logId, $this->cntResult, $this->iterations);

            DB::commit();

            $resultsComponent = view('components.results', ["data" => $this->resultArr, "request" => $request->all()])->render();
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

        $this->left = array_map(function($s) {
            return str_split(strrev($s));
        }, $this->left);
        $this->right[0] = str_split(strrev($this->right[0]));
        $params = array_merge($this->left, $this->right);
        $this->all_letters = array_unique(array_merge(...$params));
        $this->paramCount = count($params);
        $this->flag = array_fill(0, $this->paramCount, 1);
        $this->flag[$this->paramCount-1] = -1; // Right side of the equation

        // reduce equation
        $this->maxLength = max(array_map('count', $params));
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
                $otherLetters = array_values(array_diff($this->all_letters,array_keys($this->assignedLetter)));
                $otherDigits = array_values(array_diff(range(0,9), $this->assignedLetter));
                if(count($otherLetters) == 0) {
                    $this->resultArr[] = [
                        'log_id' => $this->logId,
                        'order' => ++$this->cntResult,
                        'result' => $this->assignedLetter,
                        'attempt' => $this->iterations
                    ];
                } else {
                    for ($i=0; $i < count($otherLetters); $i++) {
                        $assignedOther = [];
                        for ($j=0; $j < count($otherDigits); $j++) {
                            $num = $otherDigits[$j];
                            $char = $otherLetters[($i+$j)%count($otherLetters)];
                            if ($num == 0 && in_array($char, $this->noZeroLeading)) continue;
                            $assignedOther[$char] = $num;
                        }
                        $this->resultArr[] = [
                            'log_id' => $this->logId,
                            'order' => ++$this->cntResult,
                            'result' => array_merge($this->assignedLetter, $assignedOther),
                            'attempt' => $this->iterations
                        ];
                    }
                }
            }
            return 0;
        }
        if ($row >= $this->paramCount){
            if ($addition % 10 != 0) {
                return 0;
            }
            return $this->checkEquation(0, $column + 1, floor($addition / 10));
        }
        if (!isset($this->params[$row][$column])) {
            return $this->checkEquation($row + 1, $column, $addition);
        }
        $char = $this->params[$row][$column];

        if (isset($this->assignedLetter[$char])) {
            return $this->checkEquation($row + 1, $column, $addition + ($this->assignedLetter[$char] * $this->flag[$row]));
        } else {
            if ($this->isLastLetterInColumn($row, $column)) {
                $matchInt = abs(($addition % 10) - (10 * $this->flag[$row])) % 10;
                if (in_array($matchInt, $this->assignedLetter)) return 0;
                if ($matchInt == 0 && in_array($char, $this->noZeroLeading)) return 0;
                $this->assignedLetter[$char] = $matchInt;
                $this->checkEquation(0, $column + 1, floor(($addition + ($matchInt * $this->flag[$row])) / 10));
                unset($this->assignedLetter[$char]);
                return 0;
            } else {
                foreach (array_diff(range(0, 9), $this->assignedLetter) as $num) {
                    if ($num == 0 && in_array($char, $this->noZeroLeading)) continue;
                    $this->assignedLetter[$char] = $num;
                    $this->checkEquation($row + 1, $column, $addition + ($num * $this->flag[$row]));
                    unset($this->assignedLetter[$char]);
                }
            }
        }
        return 0; // Indicate that the current branch has been explored
    }

    private function isLastLetterInColumn(int $row, int $column) {
        while($row+1 <= $this->paramCount) {
            if (isset($this->params[$row+1][$column])) return false;
            $row++;
        }
        return true;
    }
}
