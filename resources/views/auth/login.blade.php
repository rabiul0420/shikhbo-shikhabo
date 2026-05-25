@extends('layouts.app', ['title' => 'Login'])

@push('styles')
    <style>
        .auth-page {
            min-height: calc(100vh - 270px);
            display: grid;
            place-items: center;
            padding: 28px 0;
        }

        .auth-card {
            width: min(100%, 460px);
            padding: 28px;
            border-radius: 8px;
            border-color: #d8dee7;
            box-shadow: 0 14px 38px rgba(31, 45, 61, .10);
        }

        .auth-card .page-head {
            display: block;
            margin-bottom: 22px;
            text-align: center;
        }

        .auth-card h1 {
            font-size: 30px;
            margin-bottom: 8px;
        }

        .auth-card .muted {
            margin-bottom: 0;
        }

        .auth-form {
            gap: 16px;
        }

        .auth-form label {
            color: #2f3d4c;
        }

        .auth-form input[type="email"],
        .auth-form input[type="password"] {
            min-height: 44px;
            border-radius: 6px;
            background: #fbfcfd;
        }

        .auth-form .check-label {
            justify-content: flex-start;
            font-size: 14px;
            color: var(--muted);
        }

        .auth-form button {
            width: 100%;
            min-height: 44px;
            border-radius: 6px;
            margin-top: 2px;
        }

        .auth-register-link {
            margin: 18px 0 0;
            text-align: center;
            font-size: 14px;
        }

        .auth-register-link a {
            color: var(--primary);
        }

        @media (max-width: 460px) {
            .auth-page {
                min-height: auto;
                padding: 14px 0;
            }

            .auth-card {
                padding: 22px 18px;
            }
        }
    </style>
@endpush

@section('content')
    <div class="auth-page">
        <section class="panel auth-card">
            <div class="page-head">
                <div>
                    <h1>Login</h1>
                    <p class="muted">Sign in to take exams.</p>
                </div>
            </div>

            <form class="stack auth-form" method="POST" action="{{ route('login') }}">
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
            <p class="muted auth-register-link">No account yet? <a href="{{ route('register') }}"><strong>Create account</strong></a>.</p>
        </section>
    </div>
@endsection
