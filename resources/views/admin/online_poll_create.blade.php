@extends('admin.layout.app')

@section('heading', 'Add Online Poll')

@section('button')
<a href="{{ route('admin_online_poll_show') }}" class="btn btn-primary"><i class="fas fa-eye"></i> View</a>
@endsection

@section('main_content')
<div class="section-body">
    <form action="{{ route('admin_online_poll_store') }}" method="post">
        @csrf
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow mb-4">
                    <div class="card-header bg-primary text-white">
                        <h5 class="mb-0">Create New Poll</h5>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-12">
                                <label class="form-label">Poll Title</label>
                                <input type="text" name="title" class="form-control" value="{{ old('title') }}" required>
                            </div>
                        </div>
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <div id="question-wrapper">
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Poll Question</label>
                                    <input type="text" name="questions[0][text]" class="form-control" placeholder="Enter question" value="{{ old('questions.0.text') }}" required>
                                </div>
                            </div>
                            <div class="row mb-3">
                                <div class="col-md-12">
                                    <label class="form-label">Answers</label>
                                    <div class="answers-wrapper">
                                        <input type="text" name="questions[0][answers][]" class="form-control mb-2" placeholder="Answer option">
                                        <input type="text" name="questions[0][answers][]" class="form-control mb-2" placeholder="Answer option">
                                        <input type="text" name="questions[0][answers][]" class="form-control mb-2" placeholder="Answer option">
                                        <input type="text" name="questions[0][answers][]" class="form-control mb-2" placeholder="Answer option">
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Expiration Date</label>
                                <input type="date" name="expiration" class="form-control" value="{{ old('expiration') }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Visibility</label>
                                <select name="visibility" class="form-control">
                                    <option value="public" {{ old('visibility') == 'public' ? 'selected' : '' }}>Public</option>
                                    <option value="private" {{ old('visibility') == 'private' ? 'selected' : '' }}>Private</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Voting Limit (per user)</label>
                                <input type="number" name="voting_limit" class="form-control" min="1" value="{{ old('voting_limit', 1) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Language</label>
                                <select name="language_id" class="form-control">
                                    @foreach($global_language_data as $row)
                                        <option value="{{ $row->id }}" {{ old('language_id') == $row->id ? 'selected' : '' }}>{{ $row->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-group mt-3">
                            <button type="submit" class="btn btn-primary w-100">Create Poll</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('add-answer')) {
            const answersWrapper = document.querySelector('#question-wrapper .answers-wrapper');
            const input = document.createElement('input');
            input.type = 'text';
            input.name = 'questions[0][answers][]';
            input.className = 'form-control mb-2';
            input.placeholder = 'Answer option';
            input.required = true;
            answersWrapper.appendChild(input);
        }
    });
</script>
@endpush
@endsection