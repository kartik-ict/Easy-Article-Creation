@extends('backend.layouts.master')

@section('title')
    {{ __('admins.edit') }} - {{ __('admins.admin_panel') }}
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
                    <h4 class="page-title pull-left">{{ __('admins.edit_admin') }}</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="{{ route('admin.dashboard') }}">{{ __('admins.dashboard') }}</a></li>
                        <li><a href="{{ route('admin.admins.index') }}">{{ __('admins.all_admins') }}</a></li>
                        <li><span>{{ __('admins.edit_admin') }} - {{ $admin->name }}</span></li>
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
                        <h4 class="header-title">{{ __('admins.edit_admin') }} - {{ $admin->name }}</h4>
                        @include('backend.layouts.partials.messages')
                        <div id="route-container-warehouse" data-warehouse-search="{{ route('product.warehouseSearch') }}">
                        </div>
                        <div id="route-container-bin-location"
                            data-bin-location-search="{{ route('product.binLocationSearch') }}"></div>

                        <form action="{{ route('admin.admins.update', $admin->id) }}" method="POST">
                            @method('PUT')
                            @csrf
                            <div class="form-row d-flex flex-wrap mb-4">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="name">{{ __('admins.form.name') }}</label>
                                    <input type="text" class="form-control" id="name" name="name"
                                        placeholder="{{ __('admins.form.placeholder.name') }}"
                                        value="{{ $admin->name }}">
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="email">{{ __('admins.form.email') }}</label>
                                    <input type="text" class="form-control" id="email" name="email"
                                        placeholder="{{ __('admins.form.placeholder.email') }}"
                                        value="{{ $admin->email }}">
                                </div>
                            </div>

                            <div class="form-row d-flex flex-wrap mb-4">
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="password">{{ __('admins.form.password') }}</label>
                                    <input type="password" class="form-control" id="password" name="password"
                                        placeholder="{{ __('admins.form.placeholder.password') }}">
                                </div>
                                <div class="form-group col-md-6 col-sm-12 px-2">
                                    <label for="password_confirmation">{{ __('admins.form.confirm_password') }}</label>
                                    <input type="password" class="form-control" id="password_confirmation"
                                        name="password_confirmation"
                                        placeholder="{{ __('admins.form.placeholder.password') }}">
                                </div>
                            </div>

                            <div class="form-row d-flex flex-wrap mb-4">
                                <div class="form-group col-md-6 col-sm-6 px-2">
                                    <label for="password">{{ __('admins.form.assign_roles') }}</label>
                                    <select name="roles[]" id="roles" class="form-control select2">
                                        @foreach ($roles as $role)
                                            <option value="{{ $role->name }}"
                                                {{ $admin->hasRole($role->name) ? 'selected' : '' }}>{{ $role->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="form-group col-md-6 col-sm-6 px-2">
                                    <label for="username">{{ __('admins.form.username') }}</label>
                                    <input type="text" class="form-control" id="username" name="username"
                                        placeholder="{{ __('admins.form.placeholder.username') }}" required
                                        value="{{ $admin->username }}">
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
                            <button type="submit"
                                class="btn btn-primary mt-4 pr-4 pl-4">{{ __('admins.save_admin') }}</button>
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
    <script>
        window.selectedWarehouseId = "{{ $admin->warehouse_id }}";
        window.selectedBinLocationIds = {!! json_encode($admin->bin_location_ids) !!};
    </script>

    <script src="{{ asset('backend/assets/js/bin-location.js') }}"></script>
@endsection
