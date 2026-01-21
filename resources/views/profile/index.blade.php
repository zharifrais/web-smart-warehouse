@extends('layouts.app')

@section('content')

<div class="row">
    <div class="col-md-8 mx-auto">
        <h4 class="mb-4">Profil Pengguna</h4>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        {{-- Profile Info Card --}}
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="text-center mb-4">
                    <div class="avatar-placeholder bg-primary text-white rounded-circle mx-auto d-flex align-items-center justify-content-center" style="width: 80px; height: 80px; font-size: 36px;">
                        👤
                    </div>
                </div>

                <form action="{{ route('profile.update') }}" method="POST">
                    @csrf
                    @method('PUT')

                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama Lengkap</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" id="name" 
                               name="name" value="{{ old('name', $user->name) }}" required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Username --}}
                    <div class="mb-3">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" class="form-control @error('username') is-invalid @enderror" id="username" 
                               name="username" value="{{ old('username', $user->username) }}" required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" id="email" 
                               name="email" value="{{ old('email', $user->email) }}" required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password --}}
                    <div class="mb-3">
                        <label for="password" class="form-label">Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" id="password" 
                               name="password">
                        <small class="form-text text-muted">Password minimal 6 karakter</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Password Confirmation --}}
                    <div class="mb-3">
                        <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                        <input type="password" class="form-control" id="password_confirmation" name="password_confirmation">
                    </div>

                    {{-- Member Since --}}
                    <div class="mb-4">
                        <label class="form-label">Bergabung Sejak</label>
                        <p class="form-control-plaintext">
                            @if($user->created_at)
                                {{ $user->created_at->translatedFormat('d F Y H:i') }}
                            @else
                                -
                            @endif
                        </p>
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            💾 Simpan Perubahan
                        </button>
                        <a href="{{ route('profile.index') }}" class="btn btn-secondary">
                            ↩️ Batal
                        </a>
                    </div>
                </form>
            </div>
        </div>

        {{-- Logout Card --}}
        <div class="card shadow border-danger">
            <div class="card-body">
                <h6 class="card-title text-danger">Keluar dari Akun</h6>
                <p class="card-text text-muted">Klik tombol di bawah untuk keluar dari aplikasi.</p>
                
                <form action="{{ route('profile.logout') }}" method="POST" style="display: inline;" class="logout-form">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        🚪 Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection
