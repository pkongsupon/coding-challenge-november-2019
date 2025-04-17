@props([
    'input_value' => '',
])

<div class="input-group mb-3">
    <span class="input-group-text" id="basic-addon1">+</span>
    <input class="form-control" name="left[]" value="{{$input_value}}">
</div>
