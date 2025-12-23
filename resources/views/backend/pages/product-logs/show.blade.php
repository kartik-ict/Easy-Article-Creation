@extends('backend.layouts.master')

@section('title')
    @lang('product_logs.product_log_details')
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">@lang('product_logs.product_log_details')</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><a href="{{ route('product-logs.index') }}">@lang('product_logs.product_logs')</a></li>
                    <li><span>@lang('product_logs.product_log_details')</span></li>
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
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title float-left">@lang('product_logs.product_log_details')</h4>
                    <p class="float-right mb-2">
                        <a class="btn btn-secondary text-white" href="{{ route('product-logs.index') }}">@lang('product_logs.back_to_logs')</a>
                    </p>
                    <div class="clearfix"></div>

                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-bordered">
                                <tr>
                                    <th>@lang('product_logs.product_number')</th>
                                    <td>{{ $log->product_number }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('product_logs.user')</th>
                                    <td>{{ $log->user->name ?? __('product_logs.unknown_user') }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('product_logs.type')</th>
                                    <td>@lang('product_logs.' . ($log->new_values['type'] ?? 'update'))</td>
                                </tr>
                                <tr>
                                    <th>@lang('product_logs.date')</th>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                </tr>
                                <tr>
                                    <th>@lang('product_logs.ip_address')</th>
                                    <td>{{ $log->ip_address }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-12">
                            @if($log->old_values && $log->new_values)
                            <h5>Stock Changes</h5>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered">
                                    <thead class="bg-light text-capitalize">
                                        <tr>
                                            <th>Old Stock</th>
                                            <th>New Stock</th>
                                            <th>Bin Location</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>{{ $log->old_values['stock'] ?? 'N/A' }}</td>
                                            <td>{{ $log->new_values['stock'] ?? 'N/A' }}</td>
                                            <td>{{ $log->new_values['bin_location_name'] ?? 'N/A' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
