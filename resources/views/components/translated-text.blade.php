@props(['model', 'field', 'default' => ''])

@php
    $value = $model ? $model->getTranslated($field, app()->getLocale(), $default) : $default;
@endphp

{{ $value }}









