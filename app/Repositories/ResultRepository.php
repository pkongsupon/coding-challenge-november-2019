<?php

namespace App\Repositories;

use App\Http\Requests\SaveResultRequest;
use App\Models\Result;

class ResultRepository
{
    protected Result $result;

    public function __construct(Result $result)
    {
        $this->result = $result;
    }

    public function saveResults(SaveResultRequest $request)
    {
        $this->result::insert($request->all());
    }
}
