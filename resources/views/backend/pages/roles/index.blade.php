@extends('backend.layouts.master')

@section('title')
{{ __('roles.title') }}
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
                <h4 class="page-title pull-left">{{ __('roles.breadcrumbs.all_roles') }}</h4>
                <ul class="breadcrumbs pull-left">
                    <li><a href="{{ route('admin.dashboard') }}">{{ __('roles.breadcrumbs.dashboard') }}</a></li>
                    <li><span>{{ __('roles.breadcrumbs.all_roles') }}</span></li>
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
                    <h4 class="header-title float-left">{{ __('roles.headers.list') }}</h4>
                    <p class="float-right mb-2">
                        @if (Auth::guard('admin')->user()->can('role.create'))
                        <a class="btn btn-primary text-white" href="{{ route('admin.roles.create') }}">{{ __('roles.buttons.create') }}</a>
                        @endif
                    </p>
                    <div class="clearfix"></div>
                    <div class="data-tables">
                        @include('backend.layouts.partials.messages')
                        <table id="dataTable" class="text-center">
                            <thead class="bg-light text-capitalize">
                                <tr>
                                    <th>{{ __('roles.table.sl') }}</th>
                                    <th>{{ __('roles.table.name') }}</th>
                                    <th>{{ __('roles.table.action') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($roles as $role)
                                <tr>
                                    <td>{{ $loop->index+1 }}</td>
                                    <td>{{ $role->name }}</td>
                                    {{-- <td>--}}
                                    {{-- @foreach ($role->permissions as $perm)--}}
                                    {{-- <span class="badge badge-info mr-1">--}}
                                    {{-- {{ $perm->name }}--}}
                                    {{-- </span>--}}
                                    {{-- @endforeach--}}
                                    {{-- </td>--}}
                                    <td>
                                        @if (Auth::guard('admin')->user()->can('role.edit'))
                                        <a class="btn btn-success text-white" href="{{ route('admin.roles.edit', $role->id) }}">{{ __('roles.buttons.edit') }}</a>
                                        @endif

                                        @if (Auth::guard('admin')->user()->can('role.delete'))
                                        <a class="btn btn-danger text-white" href="{{ route('admin.roles.destroy', $role->id) }}"
                                            onclick="event.preventDefault(); document.getElementById('delete-form-{{ $role->id }}').submit();">
                                            {{ __('roles.buttons.delete') }}
                                        </a>

                                        <form id="delete-form-{{ $role->id }}" action="{{ route('admin.roles.destroy', $role->id) }}" method="POST" style="display: none;">
                                            @method('DELETE')
                                            @csrf
                                        </form>
                                        @endif
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