@extends('layouts.app')

@section('content')
    @component('components.full-page-section')
        @component('components.card')
            @slot('title')
                {{ __('Login') }}
            @endslot

            <form id="login-form" method="POST" action="{{ route('login') }}">
                @csrf

                <div class="field">
                    <label class="label" for="email">{{ __('E-Mail Address') }}</label>
                    <div class="control">
                        <input id="email" type="email" class="input @error('email') is-danger @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" autofocus>
                    </div>
                    @error('email')
                        <p class="help is-danger" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="field">
                    <label class="label" for="password">{{ __('Password') }}</label>
                    <div class="control">
                        <input id="password" type="password" class="input @error('password') is-danger @enderror" name="password" required autocomplete="current-password">
                    </div>
                    @error('password')
                        <p class="help is-danger" role="alert">
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <div class="field">
                    <div class="control">
                        <label class="checkbox">
                            <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            {{ __('Remember Me') }}
                        </label>
                    </div>
                </div>

                <hr>

                <div class="field is-grouped">
                    <div class="control">
                        <button id="login-btn" class="button is-primary">
                            {{ __('Login') }}
                        </button>
                    </div>

                    @if (Route::has('password.request'))
                        <div class="control">
                            <a class="button is-text" href="{{ route('password.request') }}">
                                {{ __('Forgot Your Password?') }}
                            </a>
                        </div>
                    @endif
                </div>
            </form>

            <script>
                function login(event) {
                    event.preventDefault();
                    event.stopPropagation();

                    axios.get('/sanctum/csrf-cookie').then(response => {
                        document.getElementById('login-form').submit();
                    });
                }

                document.getElementById('login-btn').onclick = login;
            </script>
        @endcomponent
    @endcomponent
@endsection
