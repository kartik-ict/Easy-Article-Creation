@extends('backend.layouts.master')

@section('title')
    @lang('admins.create_admin')
@endsection

@section('styles')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/css/select2.min.css" rel="stylesheet" />

    <style>
        .form-check-label {
            text-transform: capitalize;
        }
    </style>
@endsection


@section('admin-content')
    <!-- page title area start -->
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">@lang('admins.create_admin')</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="{{ route('admin.dashboard') }}">@lang('admins.dashboard')</a></li>
                        <li><a href="{{ route('admin.admins.index') }}">@lang('admins.all_admins')</a></li>
                        <li><span>@lang('admins.create_admin')</span></li>
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
                        <h4 class="header-title">@lang('admins.create_new_admin')</h4>
                        @include('backend.layouts.partials.messages')
                        <div id="route-container-warehouse" data-warehouse-search="{{ route('product.warehouseSearch') }}">
                        </div>
                        <div id="route-container-bin-location"
                            data-bin-location-search="{{ route('product.binLocationSearch') }}"></div>

                        <form action="{{ route('admin.admins.store') }}" method="POST">
                            @csrf
                            <div class="form-row d-flex flex-wrap mb-4">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="name">@lang('admins.form.name')</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="@lang('admins.form.placeholder.name')" value="{{ old('name') }}">
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="email">@lang('admins.form.email')</label>
                                    <input type="text" class="form-control" id="email" name="email"
                                        placeholder="@lang('admins.form.placeholder.email')" value="{{ old('email') }}">
                                </div>
                            </div>

                            <div class="form-row d-flex flex-wrap mb-4">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="password">@lang('admins.form.password')</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="@lang('admins.form.placeholder.password')" value="{{ old('password') }}">
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="password_confirmation">@lang('admins.form.confirm_password')</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation" placeholder="@lang('admins.form.placeholder.confirm_password')"
                                        value="{{ old('password_confirmation') }}">
                                </div>
                            </div>

                            <div class="form-row d-flex flex-wrap mb-4">
                                <div class="form-group col-md-6 col-sm-6 px-2">
                                    <label for="roles">@lang('admins.form.assign_roles')</label>
                                    <select name="roles[]" id="roles" class="form-control select2">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ in_array($role->name, old('roles', [])) ? 'selected' : '' }}>
                                                {{ $role->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6 col-sm-6 px-2">
                                    <label for="username">@lang('admins.form.username')</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        placeholder="@lang('admins.form.placeholder.username')" value="{{ old('username') }}" required>
                                </div>
                            </div>
                            <div class="form-row d-flex flex-wrap mb-4">
                                <div class="form-group col-md-6 col-sm-6 px-2">
                                    <label for="warehouse">@lang('product.warehouse')</label>
                                    <select name="warehouse" id="warehouse" class="form-control">
                                    </select>
                                </div>
                                <div class="form-group col-md-6 col-sm-6 px-2">
                                    <label for="binLocation">@lang('product.bin_location')</label>
                                    <select name="bin_location[]" id="binLocation" class="form-control" multiple>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group col-md-6 col-sm-6 px-2">
                                <label for="ip_address">@lang('admins.form.ip_address')</label>
                                <input type="text" class="form-control" id="ip_address" name="ip_address"
                                    value="{{ old('ip_address', $admin->ip_address ?? '') }}"
                                    placeholder="@lang('admins.form.placeholder.ip_address')">
                            </div>
                            <button type="submit" class="btn btn-primary mt-4 pr-4 pl-4">@lang('admins.save_admin')</button>
                        </form>
                    </div>
                </div>
            </div>
            <!-- data table end -->
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-beta.1/dist/js/select2.min.js"></script>
    <script src="{{ asset('backend/assets/js/bin-location.js') }}"></script>
@endsection
