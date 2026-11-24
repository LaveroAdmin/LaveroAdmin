@if(Session::has('toastr'))
    @php
        $toastr  = Session::get('toastr');
        $type    = 'success';
        $message = '';
        $options = [];

        if ($toastr instanceof \Illuminate\Support\MessageBag) {
            $type    = \Illuminate\Support\Arr::get($toastr->get('type'), 0, 'success');
            $message = \Illuminate\Support\Arr::get($toastr->get('message') ?: $toastr->get('content'), 0, '');
            $options = $toastr->get('options', []);
        } elseif (is_array($toastr)) {
            $type    = is_array($toastr['type'] ?? null) ? ($toastr['type'][0] ?? 'success') : ($toastr['type'] ?? 'success');
            $msgRaw  = $toastr['message'] ?? $toastr['content'] ?? '';
            $message = is_array($msgRaw) ? ($msgRaw[0] ?? '') : $msgRaw;
            $options = $toastr['options'] ?? [];
        } elseif (is_object($toastr)) {
            if (method_exists($toastr, 'get')) {
                $type    = \Illuminate\Support\Arr::get($toastr->get('type'), 0, 'success');
                $message = \Illuminate\Support\Arr::get($toastr->get('message') ?: $toastr->get('content'), 0, '');
                $options = $toastr->get('options', []);
            } else {
                $type    = $toastr->type ?? 'success';
                $message = $toastr->message ?? $toastr->content ?? '';
                $options = $toastr->options ?? [];
            }
        } elseif (is_string($toastr)) {
            $message = $toastr;
        }

        $optionsJson = json_encode($options ?: []);
        $messageJson = json_encode((string) $message);
    @endphp
    @if(!empty($message))
    <script>
        (function () {
            admin.toastr.{{ $type }}({!! $messageJson !!}, {!! $optionsJson !!});
        }());
    </script>
    @endif
@endif
