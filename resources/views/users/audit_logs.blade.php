@extends('layouts.app')
@section('title', 'Audit Logs')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header card-header-primary card-header-icon">
                        <div class="card-icon">
                            <i class="material-icons">list</i>
                        </div>
                        <h4 class="card-title">Audit Logs</h4>
                    </div>
                    <div class="card-body">

                        @include('layouts.common.success')
                        @include('layouts.common.warnings')
                        <div id="successView" class="alert alert-success" style="display:none;">
                            <button class="close" data-dismiss="alert">&times;</button>
                            <strong>Success!</strong><span id="successData"></span>
                        </div>
                        <div class="material-datatables">
                            <table id="logs-dt" class="table table-striped table-no-bordered table-hover" cellspacing="0" width="100%" style="width:100%">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Action</th>
                                        <th>Created By</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                @foreach($auditLogs as $auditLog)
                                    <tr>
                                        <td>
                                            {{$auditLog->id}}
                                        </td>
                                        <td>
                                            {{$auditLog->action}}
                                        </td>
                                        <td>
                                            {{optional($auditLog->creator)->name}}
                                        </td>
                                        <td>
                                            {{\Carbon\Carbon::parse($auditLog->created_at)->isoFormat('MMM Do YYYY H:m:s')}}
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>

                                <tfoot>
                                    <tr>
                                        <th>ID</th>
                                        <th>Action</th>
                                        <th>Created By</th>
                                        <th>Date</th>
                                    </tr>
                                </tfoot>
                            </table>
                            {{$auditLogs->links()}}
                        </div>
                    </div>
                    <!-- end content-->
                </div>
                <!--  end card  -->
            </div>
            <!-- end col-md-12 -->
        </div>
        <!-- end row -->
    </div>

@endsection
