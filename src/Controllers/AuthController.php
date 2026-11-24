<?php

namespace LaveroAdmin\Admin\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Validator;
use LaveroAdmin\Admin\Auth\TwoFactor\RecoveryCodes;
use LaveroAdmin\Admin\Auth\TwoFactor\TwoFactorAuthenticator;
use LaveroAdmin\Admin\Facades\Admin;
use LaveroAdmin\Admin\Form;
use LaveroAdmin\Admin\Layout\Content;

class AuthController extends Controller
{
    /**
     * @var string
     */
    protected $loginView = 'admin::login';

    /**
     * Show the login page.
     *
     * @return \Illuminate\Contracts\View\Factory|Redirect|\Illuminate\View\View
     */
    public function getLogin()
    {
        if ($this->guard()->check()) {
            return redirect($this->redirectPath());
        }

        return view($this->loginView);
    }

    /**
     * Handle a login request.
     *
     * @param Request $request
     *
     * @return mixed
     */
    public function postLogin(Request $request)
    {
        $this->loginValidator($request->all())->validate();

        $rate_limit_key = 'login-tries-'.Admin::guardName().'|'.sha1($request->input($this->username()).'|'.$request->ip());

        if (config('admin.auth.throttle_logins', true)) {
            $maxAttempts = config('admin.auth.throttle_attempts', 5);
            if (RateLimiter::tooManyAttempts($rate_limit_key, $maxAttempts)) {
                $seconds = RateLimiter::availableIn($rate_limit_key);

                $message = Lang::has('auth.throttle')
                    ? trans('auth.throttle', ['seconds' => $seconds])
                    : (Lang::has('admin.throttle_logins')
                        ? trans('admin.throttle_logins', ['seconds' => $seconds])
                        : "Too many login attempts. Please try again in {$seconds} seconds.");

                return back()->withInput()->withErrors([
                    $this->username() => $message,
                ]);
            }
        }

        $credentials = $request->only([$this->username(), 'password']);
        $remember    = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            RateLimiter::clear($rate_limit_key);

            $user = $this->guard()->user();
            if ($user && method_exists($user, 'hasTwoFactorEnabled') && $user->hasTwoFactorEnabled()) {
                $userId = $user->getAuthIdentifier();
                $this->guard()->logout();
                $request->session()->put('admin.2fa.user_id', $userId);
                $request->session()->put('admin.2fa.remember', $remember);

                return redirect(admin_url('auth/2fa'));
            }

            return $this->sendLoginResponse($request);
        }

        if (config('admin.auth.throttle_logins', true)) {
            $throttle_timeout = config('admin.auth.throttle_timeout', 900);
            RateLimiter::hit($rate_limit_key, $throttle_timeout);
        }

        return back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

    /**
     * Get a validator for an incoming login request.
     *
     * @param array $data
     *
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function loginValidator(array $data)
    {
        return Validator::make($data, [
            $this->username() => 'required',
            'password'        => 'required',
        ]);
    }

    /**
     * User logout.
     *
     * @return Redirect
     */
    public function getLogout(Request $request)
    {
        $this->guard()->logout();

        $request->session()->invalidate();

        return redirect(config('admin.route.prefix'));
    }

    /**
     * User setting page.
     *
     * @param Content $content
     *
     * @return Content
     */
    public function getSetting(Content $content)
    {
        $form = $this->settingForm();
        $form->tools(
            function (Form\Tools $tools) {
                $tools->disableList();
                $tools->disableDelete();
                $tools->disableView();
            }
        );

        return $content
            ->title(trans('admin.user_setting'))
            ->body($form->edit(Admin::user()->id))
            ->body(view('admin::auth.setting-2fa'));
    }

    /**
     * Show 2FA challenge screen.
     *
     * @param Request $request
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\View\View
     */
    public function get2FaChallenge(Request $request)
    {
        if (!$request->session()->has('admin.2fa.user_id')) {
            return redirect(admin_url('auth/login'));
        }

        return view('admin::auth.2fa');
    }

    /**
     * Verify 2FA challenge.
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Http\Response
     */
    public function post2FaChallenge(Request $request)
    {
        if (!$request->session()->has('admin.2fa.user_id')) {
            return redirect(admin_url('auth/login'));
        }

        $request->validate([
            'code' => 'required|string',
        ]);

        $userId = $request->session()->get('admin.2fa.user_id');
        $userModel = config('admin.database.users_model');
        $user = $userModel::find($userId);

        if (!$user) {
            return redirect(admin_url('auth/login'));
        }

        $code = trim($request->input('code'));
        $totp = new TwoFactorAuthenticator();
        $valid = false;

        if (strlen($code) === 6 && ctype_digit($code)) {
            $valid = $totp->verifyKey($user->two_factor_secret, $code);
        }

        if (!$valid && !empty($user->two_factor_recovery_codes)) {
            $remaining = RecoveryCodes::verifyAndConsume($user->two_factor_recovery_codes, $code);
            if ($remaining !== false) {
                $user->two_factor_recovery_codes = $remaining;
                $user->save();
                $valid = true;
            }
        }

        if ($valid) {
            $remember = $request->session()->get('admin.2fa.remember', false);
            $this->guard()->loginUsingId($userId, $remember);
            $request->session()->forget(['admin.2fa.user_id', 'admin.2fa.remember']);

            return $this->sendLoginResponse($request);
        }

        return back()->withErrors([
            'code' => trans('admin.two_factor_invalid_code_or_recovery'),
        ]);
    }

    /**
     * Setup 2FA: generate secret and QR code.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function setup2Fa()
    {
        $user = Admin::user();
        $totp = new TwoFactorAuthenticator();
        $secret = $totp->generateSecretKey();
        $company = config('admin.name', 'Lavero Admin');
        $qrUrl = $totp->getQrCodeUrl($company, $user->username, $secret);
        $recoveryCodes = RecoveryCodes::generate(8);

        return response()->json([
            'status'         => true,
            'secret'         => $secret,
            'qr_url'         => $qrUrl,
            'recovery_codes' => $recoveryCodes,
        ]);
    }

    /**
     * Confirm 2FA setup.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function confirm2Fa(Request $request)
    {
        $request->validate([
            'secret'         => 'required|string',
            'code'           => 'required|string',
            'recovery_codes' => 'required|array',
        ]);

        $totp = new TwoFactorAuthenticator();
        $secret = $request->input('secret');
        $code = $request->input('code');

        if (!$totp->verifyKey($secret, $code)) {
            return response()->json([
                'status'  => false,
                'message' => trans('admin.two_factor_invalid_code_check_time'),
            ], 422);
        }

        $user = Admin::user();
        if (method_exists($user, 'confirmTwoFactor')) {
            $user->confirmTwoFactor($secret, $request->input('recovery_codes'));
        }

        return response()->json([
            'status'  => true,
            'message' => trans('admin.two_factor_enabled_success'),
        ]);
    }

    /**
     * Disable 2FA.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function disable2Fa()
    {
        $user = Admin::user();
        if (method_exists($user, 'disableTwoFactor')) {
            $user->disableTwoFactor();
        }

        return response()->json([
            'status'  => true,
            'message' => trans('admin.two_factor_disabled_success'),
        ]);
    }

    /**
     * Update user setting.
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function putSetting()
    {
        return $this->settingForm()->update(Admin::user()->id);
    }

    /**
     * Model-form for user setting.
     *
     * @return Form
     */
    protected function settingForm()
    {
        $class = config('admin.database.users_model');

        $form = new Form(new $class());

        $form->display('username', trans('admin.username'));
        $form->text('name', trans('admin.name'))->rules('required');
        $form->image('avatar', trans('admin.avatar'));
        $form->password('password', trans('admin.password'))->rules('confirmed|required');
        $form->password('password_confirmation', trans('admin.password_confirmation'))->rules('required')
            ->default(function ($form) {
                return $form->model()->password;
            });

        $form->setAction(admin_url('auth/setting'));

        $form->ignore(['password_confirmation']);

        $form->saving(function (Form $form) {
            if ($form->password && $form->model()->password != $form->password) {
                $form->password = Hash::make($form->password);
            }
        });

        $form->saved(function () {
            admin_toastr(trans('admin.update_succeeded'));

            return redirect(admin_url('auth/setting'));
        });

        return $form;
    }

    /**
     * @return string|\Symfony\Component\Translation\TranslatorInterface
     */
    protected function getFailedLoginMessage()
    {
        return Lang::has('auth.failed')
            ? trans('auth.failed')
            : 'These credentials do not match our records.';
    }

    /**
     * Get the post login redirect path.
     *
     * @return string
     */
    protected function redirectPath()
    {
        if (method_exists($this, 'redirectTo')) {
            return $this->redirectTo();
        }

        return property_exists($this, 'redirectTo') ? $this->redirectTo : config('admin.route.prefix');
    }

    /**
     * Send the response after the user was authenticated.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    protected function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();

        return redirect()->intended($this->redirectPath());
    }

    /**
     * Get the login username to be used by the controller.
     *
     * @return string
     */
    protected function username()
    {
        return 'username';
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected function guard()
    {
        return Admin::guard();
    }
}
