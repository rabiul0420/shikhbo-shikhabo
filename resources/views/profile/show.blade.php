@extends('layouts.app', ['title' => 'My Profile'])

@section('content')
    <div class="profile-page">
        <div class="page-head">
            <div>
                <h1>My Profile</h1>
                <p class="muted">Your account information for Shikhbo Shikhabo.</p>
            </div>
        </div>

        <section class="panel content-panel profile-card">
            <dl class="profile-list">
                <div>
                    <dt>Profile Picture</dt>
                    <dd>
                        @if (auth()->user()->profile_photo_path)
                            <img
                                class="profile-photo-preview"
                                src="{{ asset(auth()->user()->profile_photo_path) }}"
                                alt="{{ auth()->user()->name }} profile picture"
                            >
                        @else
                            -
                        @endif
                    </dd>
                </div>
                <div>
                    <dt>Name</dt>
                    <dd>{{ auth()->user()->name }}</dd>
                </div>
                <div>
                    <dt>Mobile</dt>
                    <dd>{{ auth()->user()->phone ?? '-' }}</dd>
                </div>
                <div>
                    <dt>School</dt>
                    <dd>{{ auth()->user()->school->title ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Account type</dt>
                    <dd>{{ auth()->user()->is_admin ? 'Admin' : 'Student' }}</dd>
                </div>
                <div>
                    <dt>Class</dt>
                    <dd>{{ auth()->user()->academicClass->name ?? '-' }}</dd>
                </div>
                <div>
                    <dt>Joined</dt>
                    <dd>{{ optional(auth()->user()->created_at)->format('M d, Y') }}</dd>
                </div>
            </dl>

            <div class="row" style="margin-top: 18px;">
                <a class="button" href="{{ route('profile.edit') }}">Update Profile</a>
            </div>
        </section>
    </div>
@endsection
