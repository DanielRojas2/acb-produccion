<a href="{{ route('admin.inicio') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.inicio'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.inicio')])>
    Inicio
</a>

<span class="block px-4 pt-4 pb-1 text-xs uppercase tracking-wider text-slate-500">Festival</span>
<a href="{{ route('admin.configuracion') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.configuracion'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.configuracion')])>
    Configuración
</a>
<a href="{{ route('admin.usuarios.index') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.usuarios.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.usuarios.*')])>
    Usuarios
</a>

<span class="block px-4 pt-4 pb-1 text-xs uppercase tracking-wider text-slate-500">Operación</span>
<a href="{{ route('admin.importaciones.index') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.importaciones.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.importaciones.*')])>
    Importaciones
</a>
<a href="{{ route('admin.participantes.index') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.participantes.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.participantes.*')])>
    Participantes
</a>
<a href="{{ route('admin.inscripciones.index') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.inscripciones.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.inscripciones.*')])>
    Inscripciones
</a>
<a href="{{ route('admin.presentaciones.index') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.presentaciones.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.presentaciones.*')])>
    Presentaciones
</a>
<a href="{{ route('admin.jurados.index') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.jurados.index'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.jurados.index')])>
    Jurados
</a>
<a href="{{ route('admin.jurados.secreto') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.jurados.secreto'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.jurados.secreto')])>
    🔒 Jurado Secreto
</a>

<span class="block px-4 pt-4 pb-1 text-xs uppercase tracking-wider text-slate-500">Resultados</span>
<a href="{{ route('inicio') }}" target="_blank" class="block px-4 py-2 rounded-lg text-slate-300 hover:bg-slate-800 hover:text-white flex items-center justify-between">
    <span>Ranking Público</span>
    <span class="text-[10px] text-amber-300">↗</span>
</a>
<a href="{{ route('admin.reportes.index') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.reportes.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.reportes.*')])>
    Reportes & Actas
</a>
<a href="{{ route('admin.auditoria.index') }}" @class(['block px-4 py-2 rounded-lg', 'bg-slate-800 text-white font-semibold' => request()->routeIs('admin.auditoria.*'), 'text-slate-300 hover:bg-slate-800 hover:text-white' => ! request()->routeIs('admin.auditoria.*')])>
    Auditoría
</a>