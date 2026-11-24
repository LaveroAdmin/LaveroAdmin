@php
    $renderAlert = function($data, $defaultTitle = '') {
        $title = $defaultTitle;
        $message = '';

        if ($data instanceof \Illuminate\Support\MessageBag) {
            $title = \Illuminate\Support\Arr::get($data->get('title'), 0, $defaultTitle);
            $message = \Illuminate\Support\Arr::get($data->get('message'), 0, '');
        } elseif (is_array($data)) {
            $title = is_array($data['title'] ?? null) ? ($data['title'][0] ?? $defaultTitle) : ($data['title'] ?? $defaultTitle);
            $msgRaw = $data['message'] ?? (isset($data[0]) ? $data[0] : '');
            $message = is_array($msgRaw) ? ($msgRaw[0] ?? '') : $msgRaw;
        } elseif (is_object($data)) {
            if (method_exists($data, 'get')) {
                $title = \Illuminate\Support\Arr::get($data->get('title'), 0, $defaultTitle);
                $message = \Illuminate\Support\Arr::get($data->get('message'), 0, '');
            } else {
                $title = $data->title ?? $defaultTitle;
                $message = $data->message ?? '';
            }
        } elseif (is_string($data)) {
            $message = $data;
        }

        return ['title' => $title, 'message' => $message];
    };
@endphp

@if($error = session()->get('error'))
    @php $alert = $renderAlert($error); @endphp
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">×</button>
        @if(!empty($alert['title']))<h4><i class="icon icon-ban"></i> {{ $alert['title'] }}</h4>@endif
        <p>{!! $alert['message'] !!}</p>
    </div>
@elseif ($errors = session()->get('errors'))
    @if ($errors instanceof \Illuminate\Support\ViewErrorBag && $errors->hasBag('error'))
        <div class="alert alert-danger alert-dismissable">
            <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">×</button>
            @foreach($errors->getBag("error")->toArray() as $message)
                <p>{!! is_array($message) ? \Illuminate\Support\Arr::get($message, 0) : $message !!}</p>
            @endforeach
        </div>
    @endif
@endif

@if($success = session()->get('success'))
    @php $alert = $renderAlert($success); @endphp
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">×</button>
        @if(!empty($alert['title']))<h4><i class="icon icon-check"></i> {{ $alert['title'] }}</h4>@endif
        <p>{!! $alert['message'] !!}</p>
    </div>
@endif

@if($info = session()->get('info'))
    @php $alert = $renderAlert($info); @endphp
    <div class="alert alert-info alert-dismissable">
        <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">×</button>
        @if(!empty($alert['title']))<h4><i class="icon icon-info"></i> {{ $alert['title'] }}</h4>@endif
        <p>{!! $alert['message'] !!}</p>
    </div>
@endif

@if($warning = session()->get('warning'))
    @php $alert = $renderAlert($warning); @endphp
    <div class="alert alert-warning alert-dismissable">
        <button type="button" class="close" data-bs-dismiss="alert" aria-hidden="true">×</button>
        @if(!empty($alert['title']))<h4><i class="icon icon-exclamation-triangle"></i> {{ $alert['title'] }}</h4>@endif
        <p>{!! $alert['message'] !!}</p>
    </div>
@endif
