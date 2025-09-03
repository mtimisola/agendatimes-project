@extends('admin.layout.app')

@section('heading', 'Online Polls')

@section('button')
<a href="{{ route('admin_online_poll_create') }}" class="btn btn-primary"><i class="fas fa-plus"></i> Add</a>
@endsection

@section('main_content')
<div class="section-body">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered" id="example1">
                            <thead>
                                <tr>
                                    <th>SL</th>
                                    <th>Title</th>
                                    <th>Questions & Answers</th>
                                    <th>Expiration</th>
                                    <th>Visibility</th>
                                    <th>Voting Limit</th>
                                    <th>Language</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($online_poll_data as $row)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $row->title }}</td>
                                    <td>
                                        @foreach($row->questions as $q)
                                            <div class="mb-2">
                                                <strong>Q: {{ $q->text }}</strong>
                                                <ul>
                                                    @foreach($q->answers as $a)
                                                        <li>{{ $a->text }} (Votes: {{ $a->votes }})</li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endforeach
                                    </td>
                                    <td>{{ $row->expiration }}</td>
                                    <td>{{ ucfirst($row->visibility) }}</td>
                                    <td>{{ $row->voting_limit }}</td>
                                    <td>{{ $row->language_id }}</td>
                                    <td class="pt_10 pb_10">
                                        <a href="{{ route('admin_online_poll_edit',$row->id) }}" class="btn btn-primary">Edit</a>
                                        <a href="{{ route('admin_online_poll_delete',$row->id) }}" class="btn btn-danger" onClick="return confirm('Are you sure?');">Delete</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="row mt-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h6 class="mb-0">Other Active Pools</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-group">
                            @foreach($active_pools as $active)
                                <li class="list-group-item d-flex justify-content-between align-items-center">
                                    <span>{{ $active->question }}</span>
                                    <a href="{{ route('admin_online_poll_show', $active->id) }}" class="btn btn-sm btn-outline-info">View Pool</a>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection