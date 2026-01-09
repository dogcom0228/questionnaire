@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Questionnaires</h1>
    <a href="{{ route('questionnaires.create') }}" class="btn btn-primary">Create New</a>
    
    <table class="table mt-4">
        <thead>
            <tr>
                <th>Title</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($questionnaires as $questionnaire)
            <tr>
                <td>{{ $questionnaire->title }}</td>
                <td>{{ $questionnaire->is_active ? 'Active' : 'Inactive' }}</td>
                <td>
                    <a href="{{ route('questionnaires.edit', $questionnaire) }}">Edit</a>
                    <a href="{{ route('questionnaires.show', $questionnaire) }}">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
