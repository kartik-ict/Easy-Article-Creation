@extends('backend.layouts.master')

@section('title')
    Fabrikant maken
@endsection

@section('admin-content')
    <div class="page-title-area">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <div class="breadcrumbs-area clearfix">
                    <h4 class="page-title pull-left">Fabrikant maken</h4>
                    <ul class="breadcrumbs pull-left">
                        <li><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
                        <li><span>Fabrikant maken</span></li>
                    </ul>
                </div>
            </div>
            <div class="col-sm-6 clearfix">
                @include('backend.layouts.partials.logout')
            </div>
        </div>
    </div>

    <div class="main-content-inner">
        <div class="row">
            <div class="col-lg-12 mt-5">
                <div class="card">
                    <div class="card-body">
                        <h4 class="header-title">Fabrikant maken</h4>
                        @include('backend.layouts.partials.messages')
                        <form action="{{ route('admin.manufacturers.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="name">Naam <span class="text-danger">*</span></label>
                                <input type="text" name="name" id="name" class="form-control" placeholder="Naam fabrikant invoeren" required>
                            </div>
                            <div class="form-group">
                                <label for="link">Link</label>
                                <input type="url" name="link" id="link" class="form-control" placeholder="Link naar fabrikant invoeren">
                            </div>
                            <div class="form-group">
                                <label for="description">Beschrijving</label>
                                <textarea name="description" id="description" class="form-control" rows="4" placeholder="Beschrijving invoeren"></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary mt-3">Fabrikant maken</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
