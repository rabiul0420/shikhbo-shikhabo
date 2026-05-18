@extends('layouts.app', ['title' => 'Login'])

@section('content')
    <div class="page-head">
        <div>
            <h1>Login</h1>
            <p class="muted">Sign in to take exams or manage the admin panel.</p>
        </div>
    </div>

    <section class="panel" style="max-width:520px;">
        <form class="stack" method="POST" action="{{ route('login') }}">
            @csrf
            <label>
                Email
                <input type="email" name="email" value="{{ old('email') }}" autofocus>
            </label>
            <label>
                Password
                <input type="password" name="password">
            </label>
            <label class="check-label">
                <input type="checkbox" name="remember" value="1">
                Remember me
            </label>
            <button type="submit">Login</button>
        </form>
        <p class="muted">No account yet? <a href="{{ route('register') }}"><strong>Create one</strong></a>.</p>
        <p class="muted">Default admin: admin@example.com / password</p>
    </section>
@endsection
