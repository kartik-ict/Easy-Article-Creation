@extends('backend.layouts.master')

@section('title')
    Fabrikant
@endsection

@section('styles')
    <!-- Start datatable css -->
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.18/css/dataTables.bootstrap4.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.bootstrap.min.css">
    <link rel="stylesheet" type="text/css"
          href="https://cdn.datatables.net/responsive/2.2.3/css/responsive.jqueryui.min.css">
@endsection


@section('admin-content')

    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Fabrikant</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li><span>Alle Fabrikant</span></li>
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
                        <h4 class="header-title float-left">Fabrikant Lijst</h4>
                        <p class="float-right mb-2">
                            @if (Auth::guard('admin')->user()->can('manufacture.create'))
                                <a class="btn btn-primary text-white" href="{{ route('admin.manufacturers.create') }}">Nieuwe
                                    fabrikant creëren</a>
                            @endif
                        </p>
                        <div class="clearfix"></div>
                        <div class="data-tables">
                            @include('backend.layouts.partials.messages')
                            <table id="manufacturersTable" class="table table-striped">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Actions</th>
                                </tr>
                                </thead>
                                <tbody>
                                <!-- Data will be populated dynamically via AJAX -->
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
    <link rel="stylesheet" href="https://cdn.datatables.net/1.10.19/css/jquery.dataTables.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.js"></script>

    <script>
        // Pass routes and permissions to JavaScript
        const routes = {
            edit: "{{ route('admin.manufacturers.edit', ':id') }}",
            delete: "{{ route('admin.manufacturers.delete', ':id') }}",
        };

        const userPermissions = {
            canEdit: {{ Auth::guard('admin')->user()->can('manufacture.edit') ? 'true' : 'false' }},
            canDelete: {{ Auth::guard('admin')->user()->can('manufacture.delete') ? 'true' : 'false' }},
        };

        $(document).ready(function () {
            $('#manufacturersTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                responsive: true,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.19/i18n/Dutch.json"
                },
                ajax: {
                    url: '{{ route("admin.manufacturers.getData") }}',
                    type: 'GET',
                    data: function (d) {
                        // This function will send the necessary data to the backend for pagination.
                        d.page = d.start / d.length + 1; // Page number (start is the first row on the page)
                        d.limit = d.length; // Records per page
                    }
                },
                columns: [
                    {data: 'id', name: 'id'},
                    {data: 'name', name: 'name'},
                    {
                        data: 'id',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        render: function (data, type, row) {
                            let actions = '';
                            if (userPermissions.canEdit) {
                                const editUrl = routes.edit.replace(':id', row.id);
                                actions += `<a href="${editUrl}" class="btn btn-primary btn-sm">Edit</a> `;
                            }
                            if (userPermissions.canDelete) {
                                const deleteUrl = routes.delete.replace(':id', row.id);
                                actions += `
                                    <form action="${deleteUrl}" method="POST" style="display:inline;">
                                        @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" onclick="return confirm('Weet je zeker dat je Fabrikant moet verwijderen?')">Delete</button>
                            </form>
`;
                            }
                            return actions;
                        }
                    }
                ]
            });
        });

    </script>
@endsection