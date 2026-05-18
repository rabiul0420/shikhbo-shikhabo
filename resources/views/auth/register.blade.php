@extends('layouts.app', ['title' => 'Register'])

@section('content')
    <div class="page-head">
        <div>
            <h1>Create Account</h1>
            <p class="muted">Register as a student user. Admin access is assigned from the database.</p>
        </div>
    </div>

    <section class="panel" style="max-width:520px;">
        <form class="stack" method="POST" action="{{ route('register') }}">
            @csrf
            <label>
                Name
                <input name="name" value="{{ old('name') }}" autofocus>
            </label>
            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}">
            </label>
            <label>
                Password
                <input type="password" name="password">
            </label>
            <label>
                Confirm Password
                <input type="password" name="password_confirmation">
            </label>
            <button type="submit">Create account</button>
        </form>
        <p class="muted">Already registered? <a href="{{ route('login') }}"><strong>Login</strong></a>.</p>
    </section>
@endsection
