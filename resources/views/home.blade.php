@extends('layouts.app')

@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12">
            <div class="card">
                <div class="card-body">
                    @if(Auth::user()->isLanguageExpert() || Auth::user()->isMasterExpert() || Auth::user()->isAdministrator())
                        <app-sync :is-active="appSyncActive" :connection-state="connectionState"></app-sync>
                    @endif

                    <h1>Hello, {{ Auth::user()->name }}</h1>

                    @if (Auth::user()->isMasterExpert() || Auth::user()->isAdministrator())
                        <h2>SELFIE master</h2>
                        <master-expert-view></master-expert-view>
                    @elseif (Auth::user()->isLanguageExpert())
                        <h2>Country SELFIE Expert for {{ Auth::user()->language->name }}</h2>
                        <language-expert-view language="{{ Auth::user()->language->code }}"></language-expert-view>
                    @else
                        <div class="alert alert-warning text-center mt-4" role="alert">
                            You do not have sufficient role to use the application. Please contact an administrator to have a role assigned.
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
