@extends('installer.layouts.master')

@section('template_title')
    {{ trans('installer_messages.requirements.templateTitle') }}
@endsection

@section('title')
    <i class="fa fa-check fa-fw" aria-hidden="true"></i>
    {{ trans('installer_messages.requirements.title') }}
@endsection

@section('container')

    <ul class="list">
        <li class="list__item list__title {{ $php_supported ? 'success' : 'error' }}">
            <strong>PHP</strong>
            <strong>
                <small>({{ $min_php_version }}+)</small>
            </strong>
            <span class="float-right">
                <strong>{{ PHP_VERSION }}</strong>
                <i class="fa fa-fw fa-{{ $php_supported ? 'check-circle-o' : 'exclamation-circle' }} row-icon" aria-hidden="true"></i>
            </span>
        </li>
        @foreach ($extensions as $extension => $enabled)
            <li class="list__item {{ $enabled ? 'success' : 'error' }}">
                {{ $extension }}
                <i class="fa fa-fw fa-{{ $enabled ? 'check-circle-o' : 'exclamation-circle' }} row-icon" aria-hidden="true"></i>
            </li>
        @endforeach
    </ul>

    @if ($can_continue)
        <div class="buttons">
            <a class="button" href="{{ route('installer.permissions', [], false) }}">
                {{ trans('installer_messages.requirements.next') }}
                <i class="fa fa-angle-right fa-fw" aria-hidden="true"></i>
            </a>
        </div>
    @endif

@endsection