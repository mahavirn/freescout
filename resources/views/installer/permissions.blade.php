@extends('installer.layouts.master')

@section('template_title')
    {{ trans('installer_messages.permissions.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-key fa-fw" aria-hidden="true"></i>
    {{ trans('installer_messages.permissions.title') }}
@endsection

@section('container')

    These folders must be writable by web server user: <strong>{{ get_current_user() }}</strong>
    <br/>Recommended permissions: <strong>775</strong>

    <ul class="list">
        @foreach ($permissions as $folder => $writable)
        <li class="list__item list__item--permissions {{ $writable ? 'success' : 'error' }}">
            {{ $folder }}
            <span>
                <i class="fa fa-fw fa-{{ $writable ? 'check-circle-o' : 'exclamation-circle' }}"></i>
                @if (!$writable)
                    <small>Not writable</small>
                @endif
            </span>
        </li>
        @endforeach
    </ul>

    @if ($can_continue)
        <div class="buttons">
            <a href="{{ route('installer.environment', [], false) }}" class="button">
                {{ trans('installer_messages.permissions.next') }}
                <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
            </a>
        </div>
    @endif

@endsection
