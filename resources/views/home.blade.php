@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    <h1>Hello, {{ Auth::user()->name }}</h1>
                    
                    @if (Auth::user()->isMasterExpert())
                        <h2>SELFIE master</h2>
                        <master-expert-view></master-expert-view>
                    @elseif (Auth::user()->isLanguageExpert())
                        <h2>Country SELFIE Expert {{ $language->name }}</h2>
                        <language-expert-view language="et"></language-expert-view>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
