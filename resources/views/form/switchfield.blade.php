@include("admin::form._header")

    <div class="form-check form-switch">
        <input type="hidden" name="{{$name}}" id="{{$id}}" value="{{ old($column, $value ?? ($checked ? ($states['on']['value'] ?? 1) : ($states['off']['value'] ?? 0))) }}" />
        <input class="form-check-input {{$class}}" name="{{$name}}_cb" type="checkbox" id="{{$name}}_cb" {{ !empty($checked) ? 'checked' : '' }} {!! $attributes !!} onchange="
            var val = this.checked ? '{{ $states['on']['value'] ?? 1 }}' : '{{ $states['off']['value'] ?? 0 }}';
            document.querySelector('#{{$id}}').value = val;
            var lbl = document.querySelector('#{{$id}}_label');
            if (lbl) {
                lbl.textContent = this.checked ? '{{ $states['on']['text'] ?? '' }}' : '{{ $states['off']['text'] ?? '' }}';
            }
        " />
        @if(!empty($states['on']['text']) || !empty($states['off']['text']))
            <label class="form-check-label ms-1" for="{{$name}}_cb" id="{{$id}}_label">
                {{ !empty($checked) ? ($states['on']['text'] ?? '') : ($states['off']['text'] ?? '') }}
            </label>
        @endif
    </div>

@include("admin::form._footer")
