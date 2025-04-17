@props([
    'data' => [],
    'request' => [],
])

@php
    $results = array_map(function($arr){
        return json_decode($arr['result'], true);
    }, $data);

    $mapping_request = [];
    $currection = [];
    foreach ($results as $result) {
        $left = $request['left'];
        $right = $request['right'];
        foreach ($result as $letter => $digit) {
            for ($i=0; $i < count($left); $i++) {
                $left[$i] = str_replace($letter, $digit, $left[$i]);
            }
            $right[0] = str_replace($letter, $digit, $right[0]);
        }
        $mapping_request[] = implode(' + ', $left) . ' = ' . $right[0];
        $currection[] = array_sum($left) == $right[0];
    }
@endphp

@if (count($results) > 0)
    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                @foreach ($results[0] as $k => $v)
                    <th scope="col">{{$k}}</th>
                @endforeach
                <th>Mapping</th>
                <th>Currection</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($results as $k_row => $row)
            <tr>
                <th scope="col">{{$k_row+1}}</th>
                @foreach ($row as $k_column => $column)
                    <td>{{$column}}</td>
                @endforeach
                <td>{{$mapping_request[$k_row]}}</td>
                <td>{{$currection[$k_row] ? "true" : "false"}}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <h1 class="h-100 col-12 text-center">No results found.</h1>
@endif
