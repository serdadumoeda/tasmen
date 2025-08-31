@extends('layouts.app')

@section('content')
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Pengaturan Umum</h3>
                    </div>
                    <div class="card-body">
                        @if (session('success'))
                            <div class="alert alert-success" role="alert">
                                {{ session('success') }}
                            </div>
                        @endif

                        <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="row">
                                <div class="col-md-6">
                                    <h4>Kop Surat</h4>
                                    <hr>
                                    <div class="form-group mb-3">
                                        <label for="letterhead_line_1">Baris 1</label>
                                        <input type="text" class="form-control" id="letterhead_line_1" name="letterhead_line_1" value="{{ old('letterhead_line_1', $settings['letterhead_line_1'] ?? '') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="letterhead_line_2">Baris 2</label>
                                        <input type="text" class="form-control" id="letterhead_line_2" name="letterhead_line_2" value="{{ old('letterhead_line_2', $settings['letterhead_line_2'] ?? '') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="letterhead_line_3">Baris 3</label>
                                        <input type="text" class="form-control" id="letterhead_line_3" name="letterhead_line_3" value="{{ old('letterhead_line_3', $settings['letterhead_line_3'] ?? '') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="letterhead_line_4">Baris 4</label>
                                        <input type="text" class="form-control" id="letterhead_line_4" name="letterhead_line_4" value="{{ old('letterhead_line_4', $settings['letterhead_line_4'] ?? '') }}">
                                    </div>
                                     <div class="form-group mb-3">
                                        <label for="letterhead_line_5">Baris 5</label>
                                        <input type="text" class="form-control" id="letterhead_line_5" name="letterhead_line_5" value="{{ old('letterhead_line_5', $settings['letterhead_line_5'] ?? '') }}">
                                    </div>

                                    <div class="form-group mb-3">
                                        <label for="logo_path">Logo</label>
                                        <input type="file" class="form-control" id="logo_path" name="logo_path">
                                        @if(isset($settings['logo_path']) && $settings['logo_path'])
                                            <div class="mt-2">
                                                <img src="{{ asset('storage/' . $settings['logo_path']) }}" alt="Current Logo" style="max-height: 100px;">
                                            </div>
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h4>Blok Penandatangan</h4>
                                    <hr>
                                     <div class="form-group mb-3">
                                        <label for="signer_block_line_1">Baris 1</label>
                                        <input type="text" class="form-control" id="signer_block_line_1" name="signer_block_line_1" value="{{ old('signer_block_line_1', $settings['signer_block_line_1'] ?? '') }}">
                                    </div>
                                     <div class="form-group mb-3">
                                        <label for="signer_block_line_2">Baris 2</label>
                                        <input type="text" class="form-control" id="signer_block_line_2" name="signer_block_line_2" value="{{ old('signer_block_line_2', $settings['signer_block_line_2'] ?? '') }}">
                                    </div>
                                     <div class="form-group mb-3">
                                        <label for="signer_block_line_3">Baris 3</label>
                                        <input type="text" class="form-control" id="signer_block_line_3" name="signer_block_line_3" value="{{ old('signer_block_line_3', $settings['signer_block_line_3'] ?? '') }}">
                                    </div>
                                    <div class="form-group mb-3">
                                        <label for="signer_block_line_4">Baris 4</label>
                                        <input type="text" class="form-control" id="signer_block_line_4" name="signer_block_line_4" value="{{ old('signer_block_line_4', $settings['signer_block_line_4'] ?? '') }}">
                                    </div>
                                </div>
                            </div>


                            <button type="submit" class="btn btn-primary mt-4">Simpan Pengaturan</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
