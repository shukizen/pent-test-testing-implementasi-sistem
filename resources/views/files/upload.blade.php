@extends('layouts.app')
@section('title', 'Upload File')
@section('content')
<h2>Upload & Tools</h2>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Upload File</div>
            <div class="card-body">
                {{-- VULNERABLE A08: No file type restriction --}}
                <form id="uploadForm" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <input type="file" name="file" class="form-control" required>
                        <small class="text-muted">Semua tipe file diterima</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Upload</button>
                </form>
                <div id="uploadResult" class="mt-3"></div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">File Info (Command)</div>
            <div class="card-body">
                {{-- VULNERABLE A03: Command injection --}}
                <form id="convertForm">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="filename" class="form-control" placeholder="Nama file...">
                    </div>
                    <button type="submit" class="btn btn-warning">Get File Info</button>
                </form>
                <pre id="convertResult" class="mt-3 bg-light p-2"></pre>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-header">Fetch URL (SSRF Demo)</div>
            <div class="card-body">
                {{-- VULNERABLE A10: SSRF --}}
                <form id="fetchForm">
                    @csrf
                    <div class="mb-3">
                        <input type="url" name="url" class="form-control" placeholder="https://example.com">
                    </div>
                    <button type="submit" class="btn btn-info">Fetch</button>
                </form>
                <pre id="fetchResult" class="mt-3 bg-light p-2" style="max-height:300px;overflow:auto;"></pre>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-header">Import Data (Deserialization)</div>
            <div class="card-body">
                {{-- VULNERABLE A08: Insecure deserialization --}}
                <form id="importForm">
                    @csrf
                    <div class="mb-3">
                        <textarea name="data" class="form-control" rows="3" placeholder="Base64 encoded serialized data..."></textarea>
                    </div>
                    <button type="submit" class="btn btn-danger">Import</button>
                </form>
                <pre id="importResult" class="mt-3 bg-light p-2"></pre>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

$('#uploadForm').on('submit', function(e) {
    e.preventDefault();
    var formData = new FormData(this);
    $.ajax({ url: '/files/upload', type: 'POST', data: formData, processData: false, contentType: false,
        success: function(r) { $('#uploadResult').html('<div class="alert alert-success">'+JSON.stringify(r)+'</div>'); },
        error: function(r) { $('#uploadResult').html('<div class="alert alert-danger">'+r.responseText+'</div>'); }
    });
});

$('#fetchForm').on('submit', function(e) {
    e.preventDefault();
    $.post('/files/fetch-url', { url: $('[name=url]').val() }, function(r) {
        $('#fetchResult').text(JSON.stringify(r, null, 2));
    }).fail(function(r) { $('#fetchResult').text(r.responseText); });
});

$('#convertForm').on('submit', function(e) {
    e.preventDefault();
    $.post('/files/convert', { filename: $('[name=filename]').val() }, function(r) {
        $('#convertResult').text(JSON.stringify(r, null, 2));
    }).fail(function(r) { $('#convertResult').text(r.responseText); });
});

$('#importForm').on('submit', function(e) {
    e.preventDefault();
    $.post('/files/import', { data: $('[name=data]').val() }, function(r) {
        $('#importResult').text(JSON.stringify(r, null, 2));
    }).fail(function(r) { $('#importResult').text(r.responseText); });
});
</script>
@endsection
