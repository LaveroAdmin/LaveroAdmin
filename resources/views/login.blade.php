<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<title>{{ config('admin.title') }} | {{ __('admin.login') }}</title>
		<meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">

		@if(!is_null($favicon = Admin::favicon()))
		<link rel="shortcut icon" href="{{ $favicon }}">
		@endif

		<link rel="stylesheet" href="{{ Admin::asset("lavero-admin/css/styles.css") }}">
		<script src="{{ Admin::asset("bootstrap5/bootstrap.bundle.min.js") }}"></script>
		<style>
			html, body {
				min-height: 100%;
				height: 100%;
			}
			.lavero-login-page {
				min-height: 100vh;
				display: flex;
				align-items: center;
				justify-content: center;
				padding: 1.5rem 1rem;
				background-color: #020617;
				background-image:
					radial-gradient(circle at 18% 18%, rgba(190, 242, 100, 0.12) 0%, transparent 45%),
					radial-gradient(circle at 82% 82%, rgba(30, 41, 59, 0.75) 0%, transparent 50%);
			}
			.lavero-login-page.has-bg-image {
				background-position: center;
				background-repeat: no-repeat;
				background-size: cover;
				position: relative;
			}
			.lavero-login-page.has-bg-image::before {
				content: '';
				position: fixed;
				inset: 0;
				background: rgba(2, 6, 23, 0.65);
				backdrop-filter: blur(4px);
			}
			.lavero-login-container {
				position: relative;
				z-index: 1;
				width: 100%;
				max-width: 420px;
			}
			.lavero-login-card {
				background: #ffffff;
				border: 1px solid rgba(255, 255, 255, 0.08);
				border-radius: 16px;
				overflow: hidden;
				box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.55);
			}
			.lavero-login-header {
				background: #0f172a;
				color: #ffffff;
				padding: 1.75rem 2rem;
				text-align: center;
				border-bottom: 3px solid #bef264;
			}
			.lavero-login-badge {
				width: 48px;
				height: 48px;
				border-radius: 12px;
				background: #bef264;
				color: #0f172a;
				display: inline-flex;
				align-items: center;
				justify-content: center;
				font-size: 1.25rem;
				font-weight: 700;
				margin-bottom: 0.75rem;
				box-shadow: 0 6px 16px rgba(190, 242, 100, 0.25);
			}
			.lavero-login-brand {
				display: block;
				font-size: 1.35rem;
				font-weight: 700;
				color: #ffffff;
				text-decoration: none;
				letter-spacing: -0.01em;
			}
			.lavero-login-brand:hover {
				color: #bef264;
			}
			.lavero-login-subtitle {
				color: #94a3b8;
				font-size: 0.85rem;
				margin-top: 0.25rem;
			}
			.lavero-login-body {
				padding: 2rem;
			}
			.lavero-login-body .form-label {
				font-size: 0.85rem;
				font-weight: 600;
				color: #334155;
				margin-bottom: 0.4rem;
			}
			.lavero-login-body .input-group-text {
				background-color: #f8fafc;
				border-color: #cbd5e1;
				color: #64748b;
			}
			.lavero-login-body .form-control {
				border-color: #cbd5e1;
				padding-top: 0.6rem;
				padding-bottom: 0.6rem;
				font-size: 0.9375rem;
			}
			.lavero-login-body .form-control:focus {
				border-color: #84cc16;
				box-shadow: 0 0 0 0.2rem rgba(190, 242, 100, 0.3);
			}
			.lavero-login-submit {
				width: 100%;
				padding: 0.65rem 1.25rem;
				font-size: 0.95rem;
				font-weight: 600;
				border-radius: 10px;
				transition: transform 0.12s ease, box-shadow 0.15s ease;
			}
			.lavero-login-submit:hover {
				transform: translateY(-1px);
				box-shadow: 0 6px 18px rgba(132, 204, 22, 0.3);
			}
			.lavero-toggle-password {
				border-color: #cbd5e1;
				background: #f8fafc;
				color: #64748b;
			}
			.lavero-toggle-password:hover {
				background: #f1f5f9;
				color: #0f172a;
			}
			@media (max-width: 575.98px) {
				.lavero-login-header {
					padding: 1.4rem 1.25rem;
				}
				.lavero-login-body {
					padding: 1.5rem 1.25rem;
				}
			}
		</style>
	</head>
	<body class="lavero-login-page @if(config('admin.login_background_image')) has-bg-image @endif" @if(config('admin.login_background_image'))style="background-image: url({{ config('admin.login_background_image') }});"@endif>
		<div class="lavero-login-container">
			<div class="lavero-login-card">
				<div class="lavero-login-header">
					<div class="lavero-login-badge">
						<i class="icon-shield-alt"></i>
					</div>
					<a class="lavero-login-brand" href="{{ admin_url('/') }}">{{ config('admin.name') }}</a>
					<div class="lavero-login-subtitle">{{ config('admin.title') }}</div>
				</div>

				<div class="lavero-login-body">
					@if($errors->has('attempts'))
						<div class="alert alert-danger m-0 text-center small">{{ $errors->first('attempts') }}</div>
					@else
						<form action="{{ admin_url('auth/login') }}" method="post">
							<input type="hidden" name="_token" value="{{ csrf_token() }}">

							<div class="mb-3">
								@if($errors->has('username'))
									<div class="alert alert-danger py-2 px-3 small mb-2">{{ $errors->first('username') }}</div>
								@endif

								<label for="username" class="form-label">{{ __('admin.username') }}</label>
								<div class="input-group">
									<span class="input-group-text"><i class="icon-user"></i></span>
									<input type="text" class="form-control" placeholder="{{ __('admin.username') }}" name="username" id="username" value="{{ old('username') }}" autocomplete="username" autofocus required>
								</div>
							</div>

							<div class="mb-3">
								<label for="password" class="form-label">{{ __('admin.password') }}</label>
								<div class="input-group">
									<span class="input-group-text"><i class="icon-lock"></i></span>
									<input type="password" class="form-control" placeholder="{{ __('admin.password') }}" name="password" id="password" autocomplete="current-password" required>
									<button type="button" class="btn lavero-toggle-password" id="toggle-login-password" aria-label="Toggle password visibility">
										<i class="icon-eye"></i>
									</button>
								</div>

								@if($errors->has('password'))
									<div class="alert alert-danger py-2 px-3 small mt-2 mb-0">{{ $errors->first('password') }}</div>
								@endif
							</div>

							@if(config('admin.auth.remember'))
							<div class="mb-4 form-check">
								<input type="checkbox" class="form-check-input" name="remember" id="remember" value="1" {{ old('remember') ? 'checked="checked"' : '' }}>
								<label class="form-check-label small text-secondary" for="remember">{{ __('admin.remember_me') }}</label>
							</div>
							@endif

							<button type="submit" class="btn btn-primary lavero-login-submit">
								{{ __('admin.login') }} <i class="icon-arrow-right ms-1"></i>
							</button>
						</form>
					@endif
				</div>
			</div>
		</div>

		<script>
			(function () {
				var toggleBtn = document.getElementById('toggle-login-password');
				var pwdInput = document.getElementById('password');
				if (toggleBtn && pwdInput) {
					toggleBtn.addEventListener('click', function () {
						var isPwd = pwdInput.type === 'password';
						pwdInput.type = isPwd ? 'text' : 'password';
						var icon = this.querySelector('i');
						if (icon) {
							icon.className = isPwd ? 'icon-eye-slash' : 'icon-eye';
						}
					});
				}
			})();
		</script>
	</body>
</html>
