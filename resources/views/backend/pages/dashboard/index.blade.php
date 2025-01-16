@extends('backend.layouts.master')

@section('title')
    {{ trans('custom.title') }}
@endsection


@section('admin-content')

    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">{{ __('custom.breadcrumbs.dashboard') }}</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="/admin">{{ __('custom.breadcrumbs.home') }}</a></li>
                        <li><span>{{ __('custom.breadcrumbs.dashboard') }}</span></li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-6 clearfix">
                @include('backend.layouts.partials.logout')
            </div>
        </div>
    </div>
    <!-- page title area end -->

    <div class="main-content-inner">
        <div class="row">
            <div class="col-lg-8">
                <div class="row">
                    @if (Auth::guard('admin')->user()->can('role.view'))
                        <div class="col-md-6 mt-5 mb-3">
                            <div class="card">
                                <div class="seo-fact sbg1">
                                    <a href="{{ route('admin.roles.index') }}">
                                        <div class="p-4 d-flex justify-content-between align-items-center">
                                            <div class="seofct-icon"><i class="fa fa-users"></i>{{ trans('custom.dashboardPage.role') }}</div>
                                            <h2>{{ $total_roles }}</h2>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                    @if (Auth::guard('admin')->user()->can('admin.view'))
                        <div class="col-md-6 mt-md-5 mb-3">
                            <div class="card">
                                <div class="seo-fact sbg2">
                                    <a href="{{ route('admin.admins.index') }}">
                                        <div class="p-4 d-flex justify-content-between align-items-center">
                                            <div class="seofct-icon"><i class="fa fa-user"></i>{{ trans('custom.dashboardPage.admin') }}</div>
                                            <h2>{{ $total_admins }}</h2>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection