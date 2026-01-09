@extends('layouts.app')

@section('content')
<div class="container">
    <h1>{{ $questionnaire->title }}</h1>
    <p>{{ $questionnaire->description }}</p>
    
    <hr>
    <h3>Questions</h3>
    @foreach($questionnaire->questions as $question)
        <div class="card mb-2">
            <div class="card-body">
                <h5>{{ $question->content }} <small>({{ $question->type }})</small></h5>
                @if($question->options)
                    <ul>
                    @foreach($question->options as $option)
                        <li>{{ $option }}</li>
                    @endforeach
                    </ul>
                @endif
            </div>
        </div>
    @endforeach
</div>
@endsection
