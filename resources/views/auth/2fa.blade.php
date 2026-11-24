<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{ config('admin.title') }} | {{ __('admin.two_factor_auth') }}</title>
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

    @if(!is_null($favicon = Admin::favicon()))
        <link rel="shortcut icon" href="{{ $favicon }}">
    @endif

    <link rel="stylesheet" href="{{ Admin::asset("lavero-admin/css/styles.css") }}">
    <script src="{{ Admin::asset("bootstrap5/bootstrap.bundle.min.js") }}"></script>
</head>
<body class="bg-light" @if(config('admin.login_background_image')) style="background: url({{ config('admin.login_background_image') }}) no-repeat; background-size: cover;" @endif>
    <div class="d-flex justify-content-center align-items-center h-100" style="min-height: 100vh;">
        <div class="container m-4" style="max-width: 420px;">
            <h1 class="text-center mb-3 h2">
                <a class="text-decoration-none text-dark" href="{{ admin_url('/') }}">{{ config('admin.name') }}</a>
            </h1>
            <div class="bg-body p-4 shadow-sm rounded-3">
                <div class="text-center mb-3">
                    <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle mb-2" style="width: 56px; height: 56px; font-size: 1.5rem;">
                        <i class="icon-shield-alt"></i>
                    </div>
                    <h4 class="h5 fw-bold mb-1">{{ __('admin.two_factor_auth') }}</h4>
                    <p class="text-muted small mb-0">{{ __('admin.enter_totp_code') }}</p>
                </div>

                @if($errors->any())
                    <div class="alert alert-danger py-2 px-3 small mb-3">
                        {{ $errors->first() }}
                    </div>
                @endif

                <form action="{{ admin_url('auth/2fa') }}" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">

                    <div class="mb-3">
                        <label for="2fa_code" class="form-label visually-hidden">{{ __('admin.enter_totp_code') }}</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="icon-key"></i></span>
                            <input type="text"
                                   class="form-control form-control-lg text-center fw-bold letter-spacing-1"
                                   placeholder="000000"
                                   maxlength="11"
                                   name="code"
                                   id="2fa_code"
                                   autocomplete="one-time-code"
                                   autofocus
                                   required>
                        </div>
                    </div>

                    <div class="d-grid mb-3">
                        <button type="submit" class="btn btn-primary py-2 fw-semibold">
                            <i class="icon-check me-1"></i> {{ __('admin.submit') }}
                        </button>
                    </div>

                    <div class="text-center">
                        <a href="{{ admin_url('auth/logout') }}" class="text-muted small text-decoration-none">
                            <i class="icon-arrow-left me-1"></i> {{ __('admin.login') }}
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
