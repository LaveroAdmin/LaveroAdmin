<div class="card mt-4 border-0 shadow-sm">
    <div class="card-header bg-transparent py-3">
        <h5 class="card-title mb-0 d-flex align-items-center">
            <i class="icon-shield-alt text-primary me-2 fs-5"></i>
            {{ __('admin.two_factor_auth') }}
        </h5>
    </div>
    <div class="card-body">
        @php
            $user = Admin::user();
            $enabled = method_exists($user, 'hasTwoFactorEnabled') && $user->hasTwoFactorEnabled();
            $existingCodes = $enabled ? ($user->two_factor_recovery_codes ?? []) : [];
        @endphp

        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge {{ $enabled ? 'bg-success' : 'bg-secondary' }}">
                        {{ $enabled ? __('admin.two_factor_enabled') : __('admin.two_factor_disabled') }}
                    </span>
                    @if($enabled && !empty($user->two_factor_confirmed_at))
                        <span class="text-muted small">
                            ({{ $user->two_factor_confirmed_at->format('d.m.Y H:i') }})
                        </span>
                    @endif
                </div>
                <p class="text-muted small mb-0">
                    {{ __('admin.scan_qr_code') }}
                </p>
            </div>

            <div>
                @if(!$enabled)
                    <button type="button" class="btn btn-primary" id="btn-start-2fa">
                        <i class="icon-key me-1"></i> {{ __('admin.enable_2fa') }}
                    </button>
                @else
                    <button type="button" class="btn btn-outline-danger" id="btn-disable-2fa">
                        <i class="icon-times me-1"></i> {{ __('admin.disable_2fa') }}
                    </button>
                @endif
            </div>
        </div>

        {{-- Existing Recovery Codes Section (When 2FA is already enabled) --}}
        @if($enabled && !empty($existingCodes))
            <div class="mt-4 pt-3 border-top">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                    <div>
                        <span class="fw-semibold small">
                            <i class="icon-shield-alt text-primary me-1"></i> {{ __('admin.recovery_codes') }}
                        </span>
                        <span class="badge bg-light text-dark border ms-1">{{ count($existingCodes) }}</span>
                    </div>
                    <div class="btn-group btn-group-sm">
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-toggle-existing-codes">
                            <i class="icon-eye me-1"></i> <span id="label-toggle-codes">{{ __('admin.show_recovery_codes') }}</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-copy-existing-codes" title="{{ __('admin.copy_codes') }}">
                            <i class="icon-copy me-1"></i> <span id="label-copy-existing-codes">{{ __('admin.copy_codes') }}</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-download-existing-codes" title="{{ __('admin.download_codes') }}">
                            <i class="icon-download me-1"></i> {{ __('admin.download_codes') }}
                        </button>
                    </div>
                </div>
                <div id="container-existing-codes" class="d-none p-3 bg-light rounded font-monospace small row row-cols-2 row-cols-sm-4 g-2">
                    @foreach($existingCodes as $code)
                        <div class="col text-center p-2 bg-white rounded border">{{ $code }}</div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- 2FA Setup Container (Hidden until clicked) --}}
        <div id="container-2fa-setup" class="d-none mt-4 pt-3 border-top">
            <div class="row g-4 align-items-center">
                <div class="col-md-4 text-center">
                    <div class="p-2 border rounded bg-white d-inline-block shadow-sm">
                        <img id="img-2fa-qr" src="" alt="2FA QR Code" width="180" height="180" class="d-block" />
                    </div>
                    <div class="mt-2 font-monospace small text-muted text-break" id="text-2fa-secret"></div>
                </div>
                <div class="col-md-8">
                    <h6 class="fw-bold mb-2">{{ __('admin.scan_qr_code') }}</h6>
                    <p class="text-muted small mb-3">
                        {{ __('admin.recovery_codes_hint') }}
                    </p>

                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <span class="fw-semibold small text-secondary">
                            <i class="icon-shield-alt me-1"></i> {{ __('admin.recovery_codes') }}
                        </span>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-copy-codes" title="{{ __('admin.copy_codes') }}">
                                <i class="icon-copy me-1"></i> <span id="label-copy-codes">{{ __('admin.copy_codes') }}</span>
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary" id="btn-download-codes" title="{{ __('admin.download_codes') }}">
                                <i class="icon-download me-1"></i> {{ __('admin.download_codes') }}
                            </button>
                        </div>
                    </div>

                    <div id="container-recovery-codes" class="p-3 bg-light rounded font-monospace small mb-3 row row-cols-2 row-cols-sm-4 g-2"></div>

                    <div class="input-group mb-3" style="max-width: 320px;">
                        <input type="text" id="input-2fa-verify-code" class="form-control text-center font-monospace" placeholder="000000" maxlength="6" />
                        <button type="button" class="btn btn-primary" id="btn-confirm-2fa">
                            <i class="icon-check me-1"></i> {{ __('admin.submit') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
;(function() {
    let btnStart = document.getElementById('btn-start-2fa');
    let btnDisable = document.getElementById('btn-disable-2fa');
    let setupContainer = document.getElementById('container-2fa-setup');
    let imgQr = document.getElementById('img-2fa-qr');
    let textSecret = document.getElementById('text-2fa-secret');
    let recoveryContainer = document.getElementById('container-recovery-codes');
    let inputCode = document.getElementById('input-2fa-verify-code');
    let btnConfirm = document.getElementById('btn-confirm-2fa');

    let btnCopyCodes = document.getElementById('btn-copy-codes');
    let btnDownloadCodes = document.getElementById('btn-download-codes');
    let labelCopyCodes = document.getElementById('label-copy-codes');

    let currentSecret = '';
    let currentRecoveryCodes = [];

    // Existing codes elements (when 2FA already enabled)
    let existingCodes = @json($existingCodes);
    let btnToggleExisting = document.getElementById('btn-toggle-existing-codes');
    let labelToggleCodes = document.getElementById('label-toggle-codes');
    let containerExisting = document.getElementById('container-existing-codes');
    let btnCopyExisting = document.getElementById('btn-copy-existing-codes');
    let labelCopyExisting = document.getElementById('label-copy-existing-codes');
    let btnDownloadExisting = document.getElementById('btn-download-existing-codes');

    function copyToClipboard(codes, labelEl) {
        if (!codes || !codes.length) return;
        let text = codes.join('\n');
        let done = function() {
            if (labelEl) {
                let original = labelEl.innerText;
                labelEl.innerText = '{{ __('admin.codes_copied') }}';
                setTimeout(function() { labelEl.innerText = original; }, 2000);
            }
        };

        if (navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(text).then(done).catch(function() {
                fallbackCopy(text, done);
            });
        } else {
            fallbackCopy(text, done);
        }
    }

    function fallbackCopy(text, callback) {
        let textarea = document.createElement('textarea');
        textarea.value = text;
        textarea.style.position = 'fixed';
        textarea.style.opacity = '0';
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            if (callback) callback();
        } catch(e) {}
        document.body.removeChild(textarea);
    }

    function downloadAsTextFile(codes, username) {
        if (!codes || !codes.length) return;
        let content = "# {{ config('admin.name', 'Lavero Admin') }} 2FA Recovery Codes\n" +
                      "# User: " + username + "\n" +
                      "# Date: " + new Date().toLocaleString() + "\n" +
                      "# Store these codes safely. Each code can be used only once.\n\n" +
                      codes.join('\n') + "\n";
        let blob = new Blob([content], { type: 'text/plain;charset=utf-8' });
        let url = URL.createObjectURL(blob);
        let a = document.createElement('a');
        a.href = url;
        a.download = '2fa-recovery-codes-' + username + '.txt';
        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
        URL.revokeObjectURL(url);
    }

    if (btnCopyCodes) {
        btnCopyCodes.addEventListener('click', function() {
            copyToClipboard(currentRecoveryCodes, labelCopyCodes);
        });
    }

    if (btnDownloadCodes) {
        btnDownloadCodes.addEventListener('click', function() {
            downloadAsTextFile(currentRecoveryCodes, '{{ Admin::user()->username }}');
        });
    }

    if (btnToggleExisting && containerExisting) {
        btnToggleExisting.addEventListener('click', function() {
            containerExisting.classList.toggle('d-none');
            let isHidden = containerExisting.classList.contains('d-none');
            if (labelToggleCodes) {
                labelToggleCodes.innerText = isHidden ? '{{ __('admin.show_recovery_codes') }}' : '{{ __('admin.hide_recovery_codes') }}';
            }
        });
    }

    if (btnCopyExisting) {
        btnCopyExisting.addEventListener('click', function() {
            copyToClipboard(existingCodes, labelCopyExisting);
        });
    }

    if (btnDownloadExisting) {
        btnDownloadExisting.addEventListener('click', function() {
            downloadAsTextFile(existingCodes, '{{ Admin::user()->username }}');
        });
    }

    if (btnStart) {
        btnStart.addEventListener('click', function() {
            btnStart.disabled = true;
            fetch('{{ admin_url('auth/2fa/setup') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(res => res.json())
            .then(data => {
                btnStart.disabled = false;
                if (data.status) {
                    currentSecret = data.secret;
                    currentRecoveryCodes = data.recovery_codes;
                    imgQr.src = data.qr_url;
                    textSecret.innerText = data.secret;

                    recoveryContainer.innerHTML = '';
                    data.recovery_codes.forEach(code => {
                        let div = document.createElement('div');
                        div.className = 'col text-center p-2 bg-white rounded border';
                        div.innerText = code;
                        recoveryContainer.appendChild(div);
                    });

                    setupContainer.classList.remove('d-none');
                    inputCode.focus();
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('admin.error') }}',
                        text: data.message || 'Error setting up 2FA',
                        confirmButtonColor: '#12161f'
                    });
                }
            })
            .catch(() => {
                btnStart.disabled = false;
            });
        });
    }

    if (btnConfirm) {
        btnConfirm.addEventListener('click', function() {
            let code = inputCode.value.trim();
            if (!code || code.length !== 6) {
                inputCode.classList.add('is-invalid');
                return;
            }
            inputCode.classList.remove('is-invalid');
            btnConfirm.disabled = true;

            fetch('{{ admin_url('auth/2fa/confirm') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    secret: currentSecret,
                    code: code,
                    recovery_codes: currentRecoveryCodes
                })
            })
            .then(res => res.json())
            .then(data => {
                btnConfirm.disabled = false;
                if (data.status) {
                    Swal.fire({
                        icon: 'success',
                        title: '{{ __('admin.two_factor_auth') }}',
                        text: data.message || '2FA enabled successfully!',
                        confirmButtonColor: '#12161f',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(function() {
                        window.location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: '{{ __('admin.error') }}',
                        text: data.message || '{{ __('admin.invalid_code') }}',
                        confirmButtonColor: '#12161f'
                    });
                }
            })
            .catch(() => {
                btnConfirm.disabled = false;
            });
        });
    }

    if (btnDisable) {
        btnDisable.addEventListener('click', function() {
            Swal.fire({
                title: '{{ __('admin.disable_2fa') }}?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: '{{ __('admin.disable_2fa') }}',
                cancelButtonText: '{{ __('admin.cancel') }}'
            }).then(function(result) {
                if (!result.isConfirmed && !result.value) {
                    return;
                }
                btnDisable.disabled = true;

                fetch('{{ admin_url('auth/2fa/disable') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    btnDisable.disabled = false;
                    if (data.status) {
                        Swal.fire({
                            icon: 'success',
                            title: '{{ __('admin.two_factor_auth') }}',
                            text: data.message,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(function() {
                            window.location.reload();
                        });
                    } else {
                        Swal.fire({
                            icon: 'error',
                            title: '{{ __('admin.error') }}',
                            text: data.message || 'Error disabling 2FA',
                            confirmButtonColor: '#12161f'
                        });
                    }
                })
                .catch(() => {
                    btnDisable.disabled = false;
                });
            });
        });
    }
})();
</script>
