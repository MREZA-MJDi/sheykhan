@extends('layouts.teacher')

@section('title', $pageTitle . ' | شیخان')
@section('header-title', $pageTitle)

@section('content')
    @include('components.communication.academy-messages-workspace')
@endsection
