@props([
    'data' => []
])

@php
    $result = array_map(function($arr){
        return json_decode($arr['result'], true);
    }, $data);
@endphp

@if (count($result) > 0)
    <table class="table">
        <thead>
            <tr>
                <th scope="col">#</th>
                @foreach ($result[0] as $k => $v)
                    <th scope="col">{{$k}}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach ($result as $k_row => $row)
            <tr>
                <th scope="col">{{$k_row+1}}</th>
                @foreach ($row as $k_column => $column)
                    <td>{{$column}}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
@else
    <h1 class="h-100 col-12 text-center">No results found.</h1>
@endif
