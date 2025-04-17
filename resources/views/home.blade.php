@extends("layouts.default")
@section('title', 'Home')
@section("content")
    <div class="row mt-5 mb-5">
        <h1 class="col col-6 text-center">
            Left Equation
        </h1>
        <h1 class="col col-6 text-center">
            Right Equation
        </h1>
    </div>
    <form id="equation_form">
        <div class="row mb-3">
            <div class="col col-6" id="left_equation">
                <input class="form-control mb-3" name="left[]" value="HIER">
                <x-left-equation :input_value="'GIBT'"/>
                <x-left-equation :input_value="'ES'"/>
            </div>
            <div class="col col-6" id="right_equation">
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">=</span>
                    <input class="form-control" name="right[]" value="NEUES">
                </div>
            </div>
        </div>
    </form>
    <div class="row mb-3">
        <div class="col col-6 text-center">
            <button class="btn btn-secondary" onclick="add_left_equation()">Add Left Equation</button>
        </div>
        <div class="col col-6 text-center">
            <button class="btn btn-primary" onclick="calculate()">Calculate</button>
        </div>
    </div>
    <div class="row mt-5 mb-3" id="result_div">
    </div>
@endsection

@section("script")
<script>
    function add_left_equation() {
        $("#left_equation").append(`<x-left-equation/>`);
    }

    function calculate() {
        $('#result_div').html('');
        const form_data = $("#equation_form").serialize();
        $.ajax({
            type: "POST",
            url: "{{env('APP_URL')}}/api/find_digits",
            data: form_data,
            success: function(data) {
                console.log(data)
                $('#result_div').html(data.component)
            },
            error: function(error) {
                console.error('Error:', error);
            }
        });
    }
</script>
@endsection
