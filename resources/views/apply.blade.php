@extends('layouts.app')

@section('content')
<section class="section" style="margin-top: 30px;">
  <div class="container" style="max-width:760px; margin:auto;">
    <h2 style="color:var(--primary); text-align:center; margin-bottom:18px;">
      {{ __('messages.Application Form') }}
    </h2>

    <div style="background:#fff; padding:22px; border-radius:10px; box-shadow:0 6px 18px rgba(0,0,0,0.06);">
      @if ($errors->any())
        <div style="color:#c00; margin-bottom:12px;">
          <ul style="margin:0; padding-left:18px;">
            @foreach ($errors->all() as $err)
              <li>{{ $err }}</li>
            @endforeach
          </ul>
        </div>
      @endif

      <form action="{{ route('applications.store') }}" method="POST">
        @csrf

        <input type="hidden" name="program_type" value="{{ $program_type }}">
        <input type="hidden" name="program_id" value="{{ $program_id }}">
        <input type="hidden" name="program_title" value="{{ $program_title }}">

        <div style="margin-bottom:12px;">
          <label style="display:block; font-weight:600; margin-bottom:6px;">{{ __('messages.Program') }}</label>
          <div style="padding:10px; background:#f7f7f7; border-radius:6px;">{{ $program_title }}</div>
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-weight:600; margin-bottom:6px;">{{ __('messages.Your Name') }} <span style="color:#c00">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ddd;">
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-weight:600; margin-bottom:6px;">{{ __('messages.Phone') }} <span style="color:#c00">*</span></label>
          <input type="text" name="phone" value="{{ old('phone') }}" required style="width:100%; padding:10px; border-radius:6px; border:1px solid #ddd;">
        </div>

        <div style="margin-bottom:12px;">
          <label style="display:block; font-weight:600; margin-bottom:6px;">{{ __('messages.Email') }} ({{ __('messages.optional') }})</label>
          <input type="email" name="email" value="{{ old('email') }}" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ddd;">
        </div>

        <div style="margin-bottom:16px;">
          <label style="display:block; font-weight:600; margin-bottom:6px;">{{ __('messages.Notes') }} ({{ __('messages.optional') }})</label>
          <textarea name="notes" rows="4" style="width:100%; padding:10px; border-radius:6px; border:1px solid #ddd;">{{ old('notes') }}</textarea>
        </div>

        <div style="display:flex; gap:10px; align-items:center;">
          <button type="submit" class="btn-primary" style="padding:10px 18px;">
            {{ __('messages.Send via WhatsApp') }}
          </button>

          <a href="{{ url()->previous() }}" class="btn-secondary" style="padding:10px 14px; text-decoration:none;">
            {{ __('messages.Cancel') }}
          </a>
        </div>
      </form>

    </div>
  </div>
</section>
@endsection
