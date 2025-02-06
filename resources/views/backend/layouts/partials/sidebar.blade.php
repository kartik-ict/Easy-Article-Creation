<!-- sidebar menu area start -->
@php $usr = Auth::guard('admin')->user(); @endphp
<div class="sidebar-menu">
    <div class="sidebar-header">
        <div class="logo">
            <a href="{{ route('admin.dashboard') }}">
                <h2 class="text-white">{{ trans('custom.side_bar_title') }}</h2>
            </a>
        </div>
    </div>
    <div class="main-menu">
        <div class="menu-inner">
            <nav>
                <ul class="metismenu" id="menu">

                    @if ($usr->can('dashboard.view'))
                        <li class="active">
                            <a href="javascript:void(0)" aria-expanded="true"><i class="ti-dashboard"></i><span>{{ trans('custom.dashboard') }}</span></a>
                            <ul class="collapse">
                                <li class="{{ Route::is('admin.dashboard') ? 'active' : '' }}"><a
                                            href="{{ route('admin.dashboard') }}">{{ trans('custom.dashboard') }}</a></li>
                            </ul>
                        </li>
                    @endif

                    @if ($usr->can('role.create') || $usr->can('role.view') ||  $usr->can('role.edit') ||  $usr->can('role.delete'))
                        <li>
                            <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-tasks"></i><span>
                            {{ trans('custom.roles_permissions') }}
                        </span></a>
                            <ul class="collapse {{ Route::is('admin.roles.create') || Route::is('admin.roles.index') || Route::is('admin.roles.edit') || Route::is('admin.roles.show') ? 'in' : '' }}">
                                @if ($usr->can('role.view'))
                                    <li class="{{ Route::is('admin.roles.index')  || Route::is('admin.roles.edit') ? 'active' : '' }}">
                                        <a href="{{ route('admin.roles.index') }}">{{ trans('custom.all_roles') }}</a></li>
                                @endif
                                @if ($usr->can('role.create'))
                                    <li class="{{ Route::is('admin.roles.create')  ? 'active' : '' }}"><a
                                                href="{{ route('admin.roles.create') }}">{{ trans('custom.create_role') }}</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if ($usr->can('admin.create') || $usr->can('admin.view') ||  $usr->can('admin.edit') ||  $usr->can('admin.delete'))
                        <li>
                            <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-user"></i><span>
                            {{ trans('custom.admins') }}
                        </span></a>
                            <ul class="collapse {{ Route::is('admin.admins.create') || Route::is('admin.admins.index') || Route::is('admin.admins.edit') || Route::is('admin.admins.show') ? 'in' : '' }}">

                                @if ($usr->can('admin.view'))
                                    <li class="{{ Route::is('admin.admins.index')  || Route::is('admin.admins.edit') ? 'active' : '' }}">
                                        <a href="{{ route('admin.admins.index') }}">{{ trans('custom.all_admins') }}</a></li>
                                @endif

                                @if ($usr->can('admin.create'))
                                    <li class="{{ Route::is('admin.admins.create')  ? 'active' : '' }}"><a
                                                href="{{ route('admin.admins.create') }}">{{ trans('custom.create_admin') }}</a></li>
                                @endif
                            </ul>
                        </li>
                    @endif

                    @if ($usr->can('catalogue'))
                        <li>
                            <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-product-hunt"></i><span>{{ trans('custom.catalog') }}</span></a>
                                <ul class="collapse {{ Route::is('admin.product.index') || Route::is('product.create') }}">
                                    <li class="{{ Route::is('admin.product.index')  ? 'active' : '' }}">
                                        <a href="{{ route('admin.product.index') }}">{{ trans('custom.products') }}</a>
                                    </li>
                                </ul>
                        </li>
                        @endif

                        @if ($usr->can('manufacture.view'))
                            <li>
                                <a href="javascript:void(0)" aria-expanded="true"><i class="fa fa-compass"></i><span>{{ trans('custom.manufacturers') }}</span></a>
                                    <ul class="collapse {{ Route::is('admin.manufacturers.create') || Route::is('admin.manufacturers.index') || Route::is('admin.manufacturers.edit') || Route::is('admin.manufacturers.show') ? 'in' : '' }}">
                                        <li class="{{ Route::is('admin.manufacturers.index')  ? 'active' : '' }}">
                                            <a href="{{ route('admin.manufacturers.index') }}">{{ trans('custom.manufacturers') }}</a>
                                        </li>
                                    </ul>
                            </li>
                        @endif
                </ul>
            </nav>
        </div>
    </div>
</div>
<!-- sidebar menu area end -->
