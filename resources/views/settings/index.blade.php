@extends('layouts.app')

@section('content')
@php
    $colors = json_decode(request()->cookie('theme_colors'), true) ?? [
        'sidebar_bg' => '#16A34A',
        'sidebar_text' => '#ECF0F1',
        'topbar_bg' => '#E5E5E5',
        'menu_hover' => '#D0D0D0',
        'menu_active' => '#FFFFFF',
    ];
@endphp

<div class="container-fluid">
    <h2 class="mb-4">⚙️ Pengaturan Warna</h2>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">🎨 Kustomisasi Warna Tema</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.colors') }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-bold">Background Sidebar</label>
                            <input type="color" class="form-control form-control-color" name="sidebar_bg" 
                                   value="{{ $colors['sidebar_bg'] ?? '#2C3E50' }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Teks Sidebar</label>
                            <input type="color" class="form-control form-control-color" name="sidebar_text" 
                                   value="{{ $colors['sidebar_text'] ?? '#ECF0F1' }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Background Topbar</label>
                            <input type="color" class="form-control form-control-color" name="topbar_bg" 
                                   value="{{ $colors['topbar_bg'] ?? '#E5E5E5' }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Menu Hover</label>
                            <input type="color" class="form-control form-control-color" name="menu_hover" 
                                   value="{{ $colors['menu_hover'] ?? '#D0D0D0' }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Menu Aktif</label>
                            <input type="color" class="form-control form-control-color" name="menu_active" 
                                   value="{{ $colors['menu_active'] ?? '#FFFFFF' }}">
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">💾 Simpan Pengaturan</button>
                            <button type="button" class="btn btn-secondary" onclick="resetColors()">🔄 Reset Default</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-6">
            <div class="card shadow-sm">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">👁️ Preview</h5>
                </div>
                <div class="card-body">
                    <div id="preview" style="border: 2px solid #ddd; border-radius: 8px; overflow: hidden;">
                        <div id="preview-sidebar" style="background: {{ $colors['sidebar_bg'] ?? '#2C3E50' }}; padding: 20px; color: {{ $colors['sidebar_text'] ?? '#ECF0F1' }};">
                            <h6>SMART WAREHOUSE</h6>
                            <div id="preview-menu-hover" style="background: {{ $colors['menu_hover'] ?? '#D0D0D0' }}; padding: 10px; margin: 5px 0; border-radius: 4px; color: #333;">
                                📊 Menu Hover
                            </div>
                            <div id="preview-menu-active" style="background: {{ $colors['menu_active'] ?? '#FFFFFF' }}; padding: 10px; margin: 5px 0; border-radius: 4px; color: #333;">
                                📊 Menu Aktif
                            </div>
                        </div>
                        <div id="preview-topbar" style="background: {{ $colors['topbar_bg'] ?? '#E5E5E5' }}; padding: 15px;">
                            <strong>Topbar Preview</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function resetColors() {
    document.querySelector('input[name="sidebar_bg"]').value = '#2C3E50';
    document.querySelector('input[name="sidebar_text"]').value = '#ECF0F1';
    document.querySelector('input[name="topbar_bg"]').value = '#E5E5E5';
    document.querySelector('input[name="menu_hover"]').value = '#D0D0D0';
    document.querySelector('input[name="menu_active"]').value = '#FFFFFF';
    updatePreview();
}

function updatePreview() {
    const sidebarBg = document.querySelector('input[name="sidebar_bg"]').value;
    const sidebarText = document.querySelector('input[name="sidebar_text"]').value;
    const topbarBg = document.querySelector('input[name="topbar_bg"]').value;
    const menuHover = document.querySelector('input[name="menu_hover"]').value;
    const menuActive = document.querySelector('input[name="menu_active"]').value;

    document.getElementById('preview-sidebar').style.background = sidebarBg;
    document.getElementById('preview-sidebar').style.color = sidebarText;
    document.getElementById('preview-topbar').style.background = topbarBg;
    document.getElementById('preview-menu-hover').style.background = menuHover;
    document.getElementById('preview-menu-active').style.background = menuActive;
}

document.querySelectorAll('input[type="color"]').forEach(input => {
    input.addEventListener('input', updatePreview);
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
@endsection
