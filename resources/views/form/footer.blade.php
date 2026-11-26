<footer class="navbar form-footer navbar-light bg-white py-3 px-3 px-md-4 @if (!empty($fixedFooter))shadow fixed-bottom @endif">
    <div class="row g-0 w-100 align-items-center">
        {{ csrf_field() }}

        <div class="col-md-{{$width['label']}} d-none d-md-block"></div>

        <div class="col-12 col-md-{{$width['field']}} d-flex flex-wrap align-items-center justify-content-between gap-2">
            @if(in_array('reset', $buttons))
            <div class="me-auto">
                <button type="reset" class="btn btn-warning">{{ trans('admin.reset') }}</button>
            </div>
            @endif

            @if(in_array('submit', $buttons))
            <div class="d-flex align-items-center gap-3 ms-auto">
                <div class="form-footer-redirects d-none d-md-flex align-items-center flex-wrap gap-3">
                    @foreach($submit_redirects as $value => $redirect)
                        @if(in_array($redirect, $checkboxes))
                        <div class="form-check form-check-inline m-0">
                            <input type="checkbox" class="form-check-input after-submit" id="after-save-{{$redirect}}" name="after-save" value="{{ $value }}" {{ ($default_check == $redirect) ? 'checked' : '' }}>
                            <label class="form-check-label" for="after-save-{{$redirect}}">{{ trans("admin.{$redirect}") }}</label>
                        </div>
                        @endif
                    @endforeach
                </div>

                <button type="submit" class="btn btn-primary px-4">{{ trans('admin.submit') }}</button>
            </div>
            @endif
        </div>
    </div>
</footer>
