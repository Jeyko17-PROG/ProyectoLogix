@extends('layouts.spikia')

@section('content')
<style>
    .main-wrapper { padding: 30px; background-color: #0b0b0b; min-height: 100vh; color: white; font-family: 'Segoe UI', sans-serif; }
    .brand-header { display: flex; justify-content: center; margin-bottom: 40px; }
    .logo-main { height: 80px; animation: pulsePurple 3s infinite ease-in-out; }
    @keyframes pulsePurple {
        0% { filter: drop-shadow(0 0 5px rgba(124, 58, 237, 0.3)); transform: scale(1); }
        50% { filter: drop-shadow(0 0 25px rgba(124, 58, 237, 0.7)); transform: scale(1.03); }
        100% { filter: drop-shadow(0 0 5px rgba(124, 58, 237, 0.3)); transform: scale(1); }
    }
    .btn-back { text-decoration: none; display: flex; align-items: center; gap: 8px; color: #7c3aed; font-weight: bold; font-size: 11px; text-transform: uppercase; margin-bottom: 20px; }
    .table-container { background: #121212; border-radius: 12px; border: 1px solid #222; overflow: hidden; }
    .spikia-table { width: 100%; border-collapse: collapse; }
    .spikia-table th { background: #1a1a1a; color: #777; padding: 15px; text-align: left; font-size: 11px; text-transform: uppercase; }
    .spikia-table td { padding: 20px 15px; border-top: 1px solid #222; vertical-align: middle; }
    .btn-group-row { display: flex; gap: 8px; margin-top: 10px; }
    .btn-link { padding: 8px 12px; border-radius: 6px; text-decoration: none; font-weight: bold; color: white; font-size: 10px; text-transform: uppercase; min-width: 80px; text-align: center; }
    .btn-blue { background: #2563eb; } .btn-purple { background: #7c3aed; } .btn-pink { background: #db2777; }
    .copy-section { margin-top: 12px; display: flex; flex-direction: column; gap: 6px; }
    .copy-box { display: flex; align-items: center; background: #000; border: 1px solid #333; border-radius: 4px; padding: 3px 8px; width: fit-content; }
    .copy-tag { font-size: 8px; color: #7c3aed; font-weight: bold; width: 45px; text-transform: uppercase; }
    .copy-url { background: transparent; border: none; color: #555; font-size: 9px; width: 230px; outline: none; }
    .btn-copy { background: none; border: none; color: #7c3aed; cursor: pointer; font-size: 12px; }
    .qr-frame { background: white; padding: 5px; border-radius: 8px; width: 85px; height: 85px; }
    .qr-img { width: 100%; height: 100%; }
    .mgmt-stack { display: flex; flex-direction: column; gap: 8px; align-items: flex-end; }
    .btn-action { width: 140px; padding: 9px; border-radius: 6px; font-weight: bold; font-size: 10px; text-transform: uppercase; cursor: pointer; text-align: center; border: 1px solid transparent; }
    .btn-add-sesion { background: #7c3aed; color: white; }
    .btn-config-sesion { border-color: #7c3aed; color: #7c3aed; background: transparent; }
    .btn-delete-sesion { border-color: #ff4d4d; color: #ff4d4d; background: transparent; }
    .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.9); backdrop-filter: blur(10px); }
    .modal-content { background: #121212; border: 1px solid #333; margin: 2vh auto; border-radius: 15px; width: 90%; max-width: 650px; max-height: 95vh; display: flex; flex-direction: column; overflow: hidden; }
    .modal-body { padding: 30px; overflow-y: auto; }
    .form-input { background:#000; border:1px solid #222; color:white; padding:12px; width:100%; border-radius:8px; margin-bottom: 15px; }
    .selection-box { background:#080808; padding:15px; border-radius:10px; border: 1px solid #222; margin-bottom:15px; }
    .box-title { font-size:10px; color:#7c3aed; font-weight:bold; margin:0 0 10px; text-transform: uppercase; }
</style>

<div class="main-wrapper">
    <div class="brand-header"><img src="{{ asset('images/spikia-25.png') }}" class="logo-main"></div>
    <a href="{{ route('dashboard') }}" class="btn-back">
        <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path d="M10 19l-7-7m0 0l7-7m-7 7h18" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
        Volver al Dashboard
    </a>

    <div class="table-container">
        <table class="spikia-table">
            <thead>
                <tr>
                    <th style="padding-left: 20px;">QR MÓVIL</th>
                    <th>DETALLES Y ACCESOS</th>
                    <th>PROGRAMACIÓN</th>
                    <th style="text-align: right; padding-right: 20px;">GESTIÓN</th>
                </tr>
            </thead>
            <tbody>
                @forelse($sesiones as $sesion)
                @php 
                    $uMv = route('sesion.reunion', $sesion->slug);
                    $uMs = route('sesion.master', $sesion->slug);
                    $uVi = route('sesion.transmision', $sesion->slug);
                    $qr = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($uMv);
                @endphp
                <tr>
                    <td style="padding-left: 20px;"><div class="qr-frame"><img src="{{ $qr }}" class="qr-img"></div></td>
                    <td>
                        <h2 style="margin:0; color:#fff;">{{ $sesion->titulo }}</h2>
                        <div class="btn-group-row">
                            <a href="{{ $uMv }}" target="_blank" class="btn-link btn-blue">Móviles</a>
                            <a href="{{ $uMs }}" target="_blank" class="btn-link btn-purple">Master</a>
                            <a href="{{ $uVi }}" target="_blank" class="btn-link btn-pink">Vivo</a>
                        </div>
                        <div class="copy-section">
                            <div class="copy-box"><span class="copy-tag">Móvil</span><input type="text" value="{{ $uMv }}" id="m{{$sesion->id}}" class="copy-url" readonly><button class="btn-copy" onclick="copy('m{{$sesion->id}}')">📋</button></div>
                            <div class="copy-box"><span class="copy-tag">Master</span><input type="text" value="{{ $uMs }}" id="ma{{$sesion->id}}" class="copy-url" readonly><button class="btn-copy" onclick="copy('ma{{$sesion->id}}')">📋</button></div>
                            <div class="copy-box"><span class="copy-tag">Vivo</span><input type="text" value="{{ $uVi }}" id="v{{$sesion->id}}" class="copy-url" readonly><button class="btn-copy" onclick="copy('v{{$sesion->id}}')">📋</button></div>
                        </div>
                    </td>
                    <td><span style="color:#888;">{{ $sesion->fecha_inicio }}</span><br><strong style="color:#7c3aed;">{{ $sesion->hora_inicio }}</strong></td>
                    <td style="text-align: right; padding-right: 20px;">
                        <div class="mgmt-stack">
                            <button onclick="openCreateModal()" class="btn-action btn-add-sesion">+ AGREGAR SESIÓN</button>
                            <button onclick='editSesion(@json($sesion))' class="btn-action btn-config-sesion">CONFIGURAR</button>
                            <form action="{{ route('sesiones.destroy', $sesion->id) }}" method="POST">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-action btn-delete-sesion">ELIMINAR</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="4" style="text-align:center; padding:50px;"><button onclick="openCreateModal()" class="btn-action btn-add-sesion">+ AGREGAR PRIMERA SESIÓN</button></td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div id="modalSesion" class="modal">
    <div class="modal-content">
        <div style="padding:20px 30px; border-bottom:1px solid #222;"><h2 id="modalTitle" style="color:#7c3aed; margin:0;">Sesión</h2></div>
        <div class="modal-body">
            <form id="sesionForm" method="POST">
                @csrf <div id="methodContainer"></div>
                <input type="text" name="titulo" id="f_titulo" placeholder="TÍTULO" required class="form-input">
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:15px;">
                    <input type="text" name="presentador" id="f_presentador" placeholder="PRESENTADOR" class="form-input">
                    <input type="text" name="cuenta" id="f_cuenta" placeholder="CUENTA / ID" class="form-input">
                    <input type="date" name="fecha_inicio" id="f_fecha" class="form-input">
                    <input type="time" name="hora_inicio" id="f_hora" class="form-input">
                </div>
                <div class="selection-box">
                    <p class="box-title">Idiomas de Audio</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        @foreach(['en-US'=>'Inglés','es-LATAM'=>'Español LatAm','es-ES'=>'Español ES','pt-PT'=>'Portugués','it-IT'=>'Italiano','fr-FR'=>'Francés'] as $v => $n)
                            <label style="font-size:11px;"><input type="checkbox" name="idiomas[]" value="{{$v}}" class="check-idioma"> {{$n}}</label>
                        @endforeach
                    </div>
                </div>
                <div class="selection-box">
                    <p class="box-title">Subtítulos Disponibles</p>
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:8px;">
                        @foreach(['sub-en'=>'Inglés (Subs)','sub-es'=>'Español (Subs)','sub-pt'=>'Portugués (Subs)','sub-it'=>'Italiano (Subs)'] as $vs => $ns)
                            <label style="font-size:11px;"><input type="checkbox" name="subtitulos[]" value="{{$vs}}" class="check-sub"> {{$ns}}</label>
                        @endforeach
                    </div>
                </div>
                <input type="text" name="zoom_link" id="f_zoom" placeholder="LINK ZOOM" class="form-input">
            </form>
        </div>
        <div style="padding:20px 30px; border-top:1px solid #222; text-align:right; background:#0f0f0f;">
            <button type="button" onclick="closeModal()" style="background:none; border:none; color:#777; cursor:pointer; margin-right:20px; font-weight:bold;">CANCELAR</button>
            <button type="submit" form="sesionForm" style="background:#7c3aed; color:white; border:none; padding:12px 30px; border-radius:8px; font-weight:bold; cursor:pointer;">GUARDAR SESIÓN</button>
        </div>
    </div>
</div>

<script>
    function copy(id) {
        var t = document.getElementById(id);
        navigator.clipboard.writeText(t.value);
        alert("Enlace copiado");
    }

    function openCreateModal() {
        document.getElementById('modalTitle').innerText = "Nueva Sesión";
        document.getElementById('sesionForm').reset();
        document.getElementById('sesionForm').action = "{{ route('sesiones.store') }}";
        document.getElementById('methodContainer').innerHTML = '';
        document.querySelectorAll('.check-idioma, .check-sub').forEach(cb => cb.checked = false);
        document.getElementById('modalSesion').style.display = 'block';
    }

    function editSesion(s) {
        document.getElementById('modalTitle').innerText = "Editar Sesión";
        document.getElementById('sesionForm').action = "/sesiones/" + s.id;
        document.getElementById('methodContainer').innerHTML = '<input type="hidden" name="_method" value="PUT">';
        
        document.getElementById('f_titulo').value = s.titulo || '';
        document.getElementById('f_presentador').value = s.presentador || '';
        document.getElementById('f_cuenta').value = s.cuenta || '';
        document.getElementById('f_fecha').value = s.fecha_inicio || '';
        document.getElementById('f_hora').value = s.hora_inicio || '';
        document.getElementById('f_zoom').value = s.zoom_link || '';

        document.querySelectorAll('.check-idioma, .check-sub').forEach(cb => cb.checked = false);

        // Parsear idiomas guardados
        let idiomas = s.idiomas;
        if(typeof idiomas === 'string') idiomas = JSON.parse(idiomas);
        if(Array.isArray(idiomas)) {
            idiomas.forEach(v => {
                let cb = document.querySelector(`.check-idioma[value="${v}"]`);
                if(cb) cb.checked = true;
            });
        }

        // Parsear subtitulos guardados
        let subs = s.subtitulos;
        if(typeof subs === 'string') subs = JSON.parse(subs);
        if(Array.isArray(subs)) {
            subs.forEach(v => {
                let cb = document.querySelector(`.check-sub[value="${v}"]`);
                if(cb) cb.checked = true;
            });
        }

        document.getElementById('modalSesion').style.display = 'block';
    }

    function closeModal() { document.getElementById('modalSesion').style.display = 'none'; }
</script>
@endsection