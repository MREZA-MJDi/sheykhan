@extends('layouts.owner')

@section('title', $pageTitle . ' | شیخان')
@section('header-title', $pageTitle)

@section('content')
    @include('owner.communications.partials.messages-workspace')
@endsection
