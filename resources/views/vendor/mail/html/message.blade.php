@component('mail::layout')
    {{-- Header --}}
    @slot('header')
        @component('mail::header', ['url' => config('app.url')])
            <img src="{{ url('/images/ticksift.svg') }}"></img>
        @endcomponent
    @endslot

    {{-- Body --}}
    {{ $slot }}

    {{-- Subcopy --}}
    @if (isset($subcopy))
        @slot('subcopy')
            @component('mail::subcopy')
                {{ $subcopy }}
            @endcomponent
        @endslot
    @endif

    {{-- Footer --}}
    @slot('footer')
        @component('mail::footer')
            &copy; {{ date('Y') }} Midnight Mechanism, LLC. All rights reserved.
        @endcomponent
    @endslot
@endcomponent
