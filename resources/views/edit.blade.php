@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Questionnaire</h1>
    <form action="{{ route('questionnaires.update', $questionnaire) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="form-group">
            <label>Title</label>
            <input type="text" name="title" class="form-control" value="{{ $questionnaire->title }}" required>
        </div>
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" class="form-control">{{ $questionnaire->description }}</textarea>
        </div>
        <div class="form-group">
            <label>
                <input type="checkbox" name="is_active" value="1" {{ $questionnaire->is_active ? 'checked' : '' }}> Active
            </label>
        </div>
        <button type="submit" class="btn btn-success mt-2">Update</button>
    </form>
</div>
@endsection
