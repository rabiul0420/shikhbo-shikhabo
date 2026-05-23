@extends('layouts.app', ['title' => 'My Profile'])

@section('content')
    <div class="page-head">
        <div>
            <h1>My Profile</h1>
            <p class="muted">Your account information for Shikhbo Shikhabo.</p>
        </div>
    </div>

    <section class="panel content-panel">
        <dl class="profile-list">
            <div>
                <dt>Name</dt>
                <dd>{{ auth()->user()->name }}</dd>
            </div>
            <div>
                <dt>Email</dt>
                <dd>{{ auth()->user()->email }}</dd>
            </div>
            <div>
                <dt>Account type</dt>
                <dd>{{ auth()->user()->is_admin ? 'Admin' : 'Student' }}</dd>
            </div>
            <div>
                <dt>Joined</dt>
                <dd>{{ optional(auth()->user()->created_at)->format('M d, Y') }}</dd>
            </div>
        </dl>
    </section>
@endsection
