@extends('backend.layouts.master')

@section('title')
    @lang('product_logs.product_logs')
@endsection

@section('styles')
<!-- Start datatable css -->
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
@endsection

@section('admin-content')

<!-- page title area start -->
<div class="page-title-area">
    <div class="row align-items-center">
        <div class="col-sm-6">
            <div class="breadcrumbs-area clearfix">
                <h4 class="page-title pull-left">@lang('product_logs.product_logs')</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                    <li><span>@lang('product_logs.product_logs')</span></li>
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
        <!-- data table start -->
        <div class="col-12 mt-5">
            <div class="card">
                <div class="card-body">
                    <h4 class="header-title float-left">@lang('product_logs.product_logs')</h4>
                    <div class="clearfix"></div>
                    
                    <!-- Filters -->
                    <form method="GET" class="mb-4">
                        <div class="row">
                            <div class="col-md-2">
                                <input type="text" name="product_id" class="form-control" placeholder="@lang('product_logs.product_number')" value="{{ request('product_id') }}">
                            </div>
                            <div class="col-md-2">
                                <select name="user_id" class="form-control">
                                    <option value="">@lang('product_logs.all_users')</option>
                                    @foreach(\App\Models\Admin::all() as $admin)
                                        <option value="{{ $admin->id }}" {{ request('user_id') == $admin->id ? 'selected' : '' }}>{{ $admin->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-primary">@lang('product_logs.filter')</button>
                                <a href="{{ route('product-logs.index') }}" class="btn btn-secondary">@lang('product_logs.clear')</a>
                            </div>
                        </div>
                    </form>

                    <div class="data-tables">
                        @include('backend.layouts.partials.messages')
                        <table id="dataTable" class="text-center">
                            <thead class="bg-light text-capitalize">
                                <tr>
                                    <th>@lang('product_logs.product_number')</th>
                                    <th>@lang('product_logs.user')</th>
                                    <th>@lang('product_logs.changes')</th>
                                    <th>@lang('product_logs.date')</th>
                                    <th>@lang('product_logs.action')</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                <tr>
                                    <td>{{ $log->product_number }}</td>
                                    <td>{{ $log->user->name ?? __('product_logs.unknown_user') }}</td>
                                    <td>{{ $log->message }}</td>
                                    <td>{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                                    <td>
                                        <a href="{{ route('product-logs.show', $log->id) }}" class="btn btn-success text-white">@lang('product_logs.view')</a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <!-- data table end -->
    </div>
</div>
@endsection

@section('scripts')
<!-- Start datatable js -->
<script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.10.18/js/dataTables.bootstrap4.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/2.2.3/js/responsive.bootstrap.min.js"></script>

<script>
    /*================================
        datatable active
        ==================================*/
    if ($('#dataTable').length) {
        $('#dataTable').DataTable({
            responsive: true,
            language: {
                url: "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Dutch.json"
            }
        });
    }
</script>
@endsection
