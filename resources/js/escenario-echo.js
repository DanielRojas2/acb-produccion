/**
 * Cliente WebSocket del escenario y evaluación de jurados.
 * Sincroniza en tiempo real los datos de la presentación, el cronómetro principal,
 * la habilitación dinámica de calificación y el aviso cuando se llama al siguiente participante.
 */
export function inicializarEscenario() {
    const raiz = document.querySelector('[data-escenario-raiz], [data-escenario-echo]');

    if (!raiz || !window.Echo) {
        return;
    }

    const activoEl = raiz.querySelector('[data-escenario-activo]');
    const vacioEl = raiz.querySelector('[data-escenario-vacio]');
    const estadoEl = raiz.querySelector('[data-estado]');
    const categoriaFaseEl = raiz.querySelector('[data-categoria-fase]');
    const nombreEl = raiz.querySelector('[data-nombre]');
    const displayEl = raiz.querySelector('[data-cronometro-display]');
    const tiempoMaxDisplayEl = raiz.querySelector('[data-tiempo-max-display]');
    const tiempoMaxTextoEl = raiz.querySelector('[data-tiempo-max-texto]');
    const progressEl = raiz.querySelector('[data-cronometro-progress]');
    const avisoFinalizadoEl = raiz.querySelector('[data-aviso-finalizado]');

    // Elementos del Aviso de Siguiente Participante
    const avisoSiguienteModal = document.getElementById('avisoSiguienteParticipante');
    const avisoSiguienteTexto = document.getElementById('avisoSiguienteTexto');
    const avisoSiguienteContador = document.getElementById('avisoSiguienteContador');

    let presentacionId = parseInt(raiz.dataset.escenarioId || raiz.dataset.escenarioEcho || '0', 10) || null;
    let corriendo = raiz.dataset.cronometroCorriendo === '1';

    // Sincronización precisa con el reloj del servidor (en milisegundos)
    // Permite que todos los dispositivos (jurado presidente, vocales y público)
    // calculen el tiempo sobre la misma fuente de verdad sin desfases por reloj local.
    let serverOffset = 0;
    function sincronizarHoraServidor(serverNowMs) {
        if (serverNowMs && !isNaN(Number(serverNowMs))) {
            serverOffset = Number(serverNowMs) - Date.now();
        }
    }

    if (raiz.dataset.serverNowMs) {
        sincronizarHoraServidor(raiz.dataset.serverNowMs);
    }

    function obtenerHoraServidor() {
        return Date.now() + serverOffset;
    }

    let iniciadoAt = raiz.dataset.cronometroIniciadoAtMs
        ? parseInt(raiz.dataset.cronometroIniciadoAtMs, 10)
        : (raiz.dataset.cronometroIniciadoAt ? new Date(raiz.dataset.cronometroIniciadoAt).getTime() : null);

    let finalizadoAt = raiz.dataset.cronometroFinalizadoAtMs
        ? parseInt(raiz.dataset.cronometroFinalizadoAtMs, 10)
        : (raiz.dataset.cronometroFinalizadoAt ? new Date(raiz.dataset.cronometroFinalizadoAt).getTime() : null);

    let tiempoMaximo = parseInt(raiz.dataset.tiempoMaximo || '10', 10);
    let segundosRestantes = parseInt(raiz.dataset.segundosRestantes || '0', 10);
    let recargaPendiente = false;

    function calcularSegundosRestantes() {
        const totalSegundos = tiempoMaximo * 60;

        if (corriendo && iniciadoAt) {
            const ahora = obtenerHoraServidor();
            const transcurridoSegundos = Math.max(0, (ahora - iniciadoAt) / 1000);
            return Math.max(0, Math.ceil(totalSegundos - transcurridoSegundos));
        }

        if (finalizadoAt && iniciadoAt) {
            const transcurridoSegundos = Math.max(0, (finalizadoAt - iniciadoAt) / 1000);
            return Math.max(0, Math.ceil(totalSegundos - transcurridoSegundos));
        }

        return Math.max(0, segundosRestantes);
    }

    // Inicializar cálculo sincronizado
    segundosRestantes = calcularSegundosRestantes();

    function formatear(segundos) {
        const seguros = Math.max(0, Math.ceil(segundos));
        const horas = Math.floor(seguros / 3600);
        const minutos = Math.floor((seguros % 3600) / 60);
        const segs = seguros % 60;
        const mmss = `${String(minutos).padStart(2, '0')}:${String(segs).padStart(2, '0')}`;

        return horas > 0 ? `${String(horas).padStart(2, '0')}:${mmss}` : mmss;
    }

    function renderizar() {
        if (displayEl) {
            displayEl.textContent = formatear(segundosRestantes);
            if (corriendo && segundosRestantes <= 30) {
                displayEl.classList.remove('text-[#00ff88]');
                displayEl.classList.add('text-rose-400');
            } else {
                displayEl.classList.remove('text-rose-400');
                displayEl.classList.add('text-[#00ff88]');
            }
        }

        if (progressEl) {
            const totalSeg = Math.max(1, tiempoMaximo * 60);
            const porcentaje = Math.max(0, Math.min(100, (segundosRestantes / totalSeg) * 100));
            progressEl.style.width = `${porcentaje}%`;
        }
    }

    function actualizarEstilosEstado(estado) {
        if (!estadoEl) return;
        
        const dot = estadoEl.querySelector('[data-estado-dot]');

        if (estado === 'PARTICIPANDO') {
            estadoEl.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase bg-emerald-950/70 text-emerald-400 border border-emerald-500/40 shadow-[0_0_12px_rgba(16,185,129,0.25)]';
            if (dot) dot.className = 'w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399] animate-pulse';
        } else if (estado === 'EN_ESCENARIO') {
            estadoEl.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase bg-emerald-950/70 text-emerald-400 border border-emerald-500/30 shadow-[0_0_12px_rgba(16,185,129,0.2)]';
            if (dot) dot.className = 'w-2 h-2 rounded-full bg-emerald-400 shadow-[0_0_8px_#34d399] animate-pulse';
        } else if (estado === 'FINALIZADO') {
            estadoEl.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase bg-slate-900/80 text-slate-300 border border-slate-700 shadow-sm';
            if (dot) dot.className = 'w-2 h-2 rounded-full bg-amber-400 shadow-[0_0_8px_#fbbf24]';
        } else {
            estadoEl.className = 'inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold tracking-wider uppercase bg-slate-900/80 text-slate-300 border border-slate-700 shadow-sm';
            if (dot) dot.className = 'w-2 h-2 rounded-full bg-slate-400';
        }
    }

    const btnCalificar = document.getElementById('btnConfirmarCalificacion');
    const btnCalificarTexto = document.getElementById('btnCalificarTexto');

    function habilitarBotonCalificar() {
        if (!btnCalificar) return;
        btnCalificar.disabled = false;
        btnCalificar.classList.remove('bg-slate-800', 'border', 'border-slate-700', 'text-slate-400', 'cursor-not-allowed', 'opacity-60');
        btnCalificar.classList.add('bg-gradient-to-r', 'from-[#5c0618]', 'to-rose-700', 'hover:from-[#720a20]', 'hover:to-rose-600', 'text-white', 'cursor-pointer', 'shadow-lg');
        if (btnCalificarTexto) {
            btnCalificarTexto.textContent = 'Registrar calificación oficial';
        }
        const spanIcono = btnCalificar.querySelector('span:first-child');
        if (spanIcono) spanIcono.textContent = '✓';
    }

    function deshabilitarBotonCalificar() {
        if (!btnCalificar) return;
        btnCalificar.disabled = true;
        btnCalificar.classList.remove('bg-gradient-to-r', 'from-[#5c0618]', 'to-rose-700', 'hover:from-[#720a20]', 'hover:to-rose-600', 'text-white', 'cursor-pointer', 'shadow-lg');
        btnCalificar.classList.add('bg-slate-800', 'border', 'border-slate-700', 'text-slate-400', 'cursor-not-allowed', 'opacity-60');
        if (btnCalificarTexto) {
            btnCalificarTexto.textContent = 'Esperando inicio de presentación para calificar';
        }
        const spanIcono = btnCalificar.querySelector('span:first-child');
        if (spanIcono) spanIcono.textContent = '⏳';
    }

    function actualizarControlTiempoUI() {
        const controlCaja = document.querySelector('[data-control-tiempo-caja]');
        if (!controlCaja) return;

        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
        const urlIniciar = controlCaja.dataset.urlIniciar || '';
        const urlDetener = controlCaja.dataset.urlDetener || '';

        const targetElement = controlCaja.querySelector('form, [data-control-estado-texto]');
        if (!targetElement) return;

        if (corriendo) {
            targetElement.outerHTML = `
                <form method="POST" action="${urlDetener}">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-500 text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-rose-950/40 border border-rose-400/30 transition flex items-center gap-2 cursor-pointer">
                        <span>⏹</span>
                        <span>Detener cronómetro</span>
                    </button>
                </form>
            `;
        } else if (finalizadoAt || (estadoEl && estadoEl.textContent.toLowerCase().includes('finaliz'))) {
            targetElement.outerHTML = '<span data-control-estado-texto class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400 font-semibold">Cronómetro finalizado</span>';
        } else if (!iniciadoAt) {
            targetElement.outerHTML = `
                <form method="POST" action="${urlIniciar}">
                    <input type="hidden" name="_token" value="${csrfToken}">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-extrabold text-xs uppercase tracking-wider shadow-lg shadow-emerald-950/40 border border-emerald-400/30 transition flex items-center gap-2 cursor-pointer">
                        <span>▶</span>
                        <span>Iniciar cronómetro</span>
                    </button>
                </form>
            `;
        } else {
            targetElement.outerHTML = '<span data-control-estado-texto class="px-4 py-2 rounded-xl bg-slate-900 border border-slate-800 text-xs text-slate-400 font-semibold">Cronómetro finalizado</span>';
        }
    }

    function mostrarModoFinalizado(estadoLabel) {
        corriendo = false;
        if (estadoEl) {
            const text = estadoEl.querySelector('[data-estado-texto]');
            if (text) {
                text.textContent = estadoLabel || 'Finalizado';
            } else {
                estadoEl.textContent = estadoLabel || 'Finalizado';
            }
            actualizarEstilosEstado('FINALIZADO');
        }
        if (avisoFinalizadoEl) {
            avisoFinalizadoEl.classList.remove('hidden');
            avisoFinalizadoEl.classList.add('flex');
        }
        habilitarBotonCalificar();

        // Mostrar botón de corrección de calificación en jurados que ya emitieron su voto
        const contenedoresCorregir = document.querySelectorAll('[data-contenedor-corregir]');
        contenedoresCorregir.forEach(el => {
            el.classList.remove('hidden');
            el.classList.add('block', 'pt-1');
        });

        actualizarControlTiempoUI();
    }

    function aplicarCronometro(datos) {
        if (!datos) return;

        if (datos.server_now_ms) {
            sincronizarHoraServidor(datos.server_now_ms);
        } else if (datos.server_now) {
            sincronizarHoraServidor(datos.server_now);
        }

        if (typeof datos.corriendo === 'boolean') {
            corriendo = datos.corriendo;
            if (corriendo) {
                habilitarBotonCalificar();
                if (avisoFinalizadoEl) {
                    avisoFinalizadoEl.classList.add('hidden');
                    avisoFinalizadoEl.classList.remove('flex', 'block');
                }
            }
        }

        if (datos.cronometro_iniciado_at_ms) {
            iniciadoAt = Number(datos.cronometro_iniciado_at_ms);
        } else if (datos.cronometro_iniciado_at) {
            iniciadoAt = new Date(datos.cronometro_iniciado_at).getTime();
        } else if (datos.estado === 'EN_ESCENARIO') {
            iniciadoAt = null;
        }

        if (datos.cronometro_finalizado_at_ms) {
            finalizadoAt = Number(datos.cronometro_finalizado_at_ms);
        } else if (datos.cronometro_finalizado_at) {
            finalizadoAt = new Date(datos.cronometro_finalizado_at).getTime();
        } else if (corriendo) {
            finalizadoAt = null;
        }

        if (typeof datos.tiempo_maximo === 'number') {
            tiempoMaximo = datos.tiempo_maximo;
            if (tiempoMaxDisplayEl) {
                tiempoMaxDisplayEl.textContent = `/ ${tiempoMaximo}:00`;
            }
            if (tiempoMaxTextoEl) {
                tiempoMaxTextoEl.textContent = `Tiempo máximo de presentación: ${tiempoMaximo} minuto(s)`;
            }
        }

        if (typeof datos.segundos_restantes === 'number') {
            segundosRestantes = datos.segundos_restantes;
        }

        // Si el cronómetro fue detenido o finalizado
        if (!corriendo && (datos.cronometro_finalizado_at || datos.estado === 'FINALIZADO')) {
            mostrarModoFinalizado(datos.estado_label);
        } else if (!corriendo && datos.estado === 'EN_ESCENARIO') {
            deshabilitarBotonCalificar();
            if (estadoEl) {
                estadoEl.textContent = datos.estado_label ?? 'En escenario';
                actualizarEstilosEstado('EN_ESCENARIO');
            }
        } else if (corriendo && datos.estado === 'PARTICIPANDO') {
            if (estadoEl) {
                estadoEl.textContent = datos.estado_label ?? 'Participando';
                actualizarEstilosEstado('PARTICIPANDO');
            }
        }

        actualizarControlTiempoUI();
        segundosRestantes = calcularSegundosRestantes();
        renderizar();
    }

    function notificarSiguienteParticipante(datos) {
        if (recargaPendiente) return;
        recargaPendiente = true;

        if (avisoSiguienteModal) {
            if (avisoSiguienteTexto && datos.nombre_presentacion) {
                avisoSiguienteTexto.textContent = `El administrador ha llamado a "${datos.nombre_presentacion}" al escenario.`;
            }
            avisoSiguienteModal.classList.remove('hidden');

            let segundosEspera = 4;
            const intervaloAviso = window.setInterval(() => {
                segundosEspera -= 1;
                if (avisoSiguienteContador) {
                    avisoSiguienteContador.textContent = `Cargando en ${segundosEspera}s...`;
                }
                if (segundosEspera <= 0) {
                    window.clearInterval(intervaloAviso);
                    window.location.reload();
                }
            }, 1000);
        } else {
            window.location.reload();
        }
    }

    function aplicarEstadoCambiado(datos) {
        if (!datos || !datos.presentacion_id) return;

        const nuevoId = parseInt(datos.presentacion_id, 10);

        // Si cambió la presentación en el escenario, avisar al jurado para que verifique su nota
        // y recargar en 4 segundos
        if (presentacionId && nuevoId !== presentacionId) {
            notificarSiguienteParticipante(datos);
            return;
        }

        presentacionId = nuevoId;

        // Mostrar bloque activo
        if (datos.estado === 'EN_ESCENARIO' || datos.estado === 'PARTICIPANDO' || datos.estado === 'FINALIZADO') {
            if (activoEl) activoEl.classList.remove('hidden');
            if (vacioEl) vacioEl.classList.add('hidden');
        }

        if (estadoEl) {
            const text = estadoEl.querySelector('[data-estado-texto]');
            if (text) {
                text.textContent = datos.estado_label ?? datos.estado ?? '';
            } else {
                estadoEl.textContent = datos.estado_label ?? datos.estado ?? '';
            }
            actualizarEstilosEstado(datos.estado);
        }

        if (nombreEl && datos.nombre_presentacion) {
            const partes = datos.nombre_presentacion.trim().split(/\s+/);
            const primero = partes.shift() || '';
            const resto = partes.join(' ');
            if (resto) {
                nombreEl.innerHTML = `<span class="text-white drop-shadow-[0_2px_10px_rgba(255,255,255,0.2)]">${primero}</span> <span class="gold-gradient-text drop-shadow-[0_0_20px_rgba(229,192,91,0.5)] font-black">${resto}</span>`;
            } else {
                nombreEl.innerHTML = `<span class="gold-gradient-text drop-shadow-[0_0_20px_rgba(229,192,91,0.5)] font-black">${primero}</span>`;
            }
        }

        if (categoriaFaseEl) {
            const cat = datos.categoria || '';
            const fase = datos.fase_label || datos.fase || '';
            const depto = datos.departamento ? ` · ${datos.departamento}` : '';
            categoriaFaseEl.textContent = cat && fase ? `${cat} · ${fase}${depto}` : (cat || fase || '');
        }

        if (typeof datos.tiempo_maximo === 'number') {
            tiempoMaximo = datos.tiempo_maximo;
            if (tiempoMaxDisplayEl) {
                tiempoMaxDisplayEl.textContent = `/ ${tiempoMaximo}:00`;
            }
            if (tiempoMaxTextoEl) {
                tiempoMaxTextoEl.textContent = `Tiempo máximo de presentación: ${tiempoMaximo} minuto(s)`;
            }
        }

        if (datos.server_now_ms) {
            sincronizarHoraServidor(datos.server_now_ms);
        } else if (datos.server_now) {
            sincronizarHoraServidor(datos.server_now);
        }

        if (datos.cronometro_iniciado_at_ms) {
            iniciadoAt = Number(datos.cronometro_iniciado_at_ms);
        } else if (datos.cronometro_iniciado_at) {
            iniciadoAt = new Date(datos.cronometro_iniciado_at).getTime();
        } else if (datos.estado === 'EN_ESCENARIO') {
            iniciadoAt = null;
        }

        if (datos.cronometro_finalizado_at_ms) {
            finalizadoAt = Number(datos.cronometro_finalizado_at_ms);
        } else if (datos.cronometro_finalizado_at) {
            finalizadoAt = new Date(datos.cronometro_finalizado_at).getTime();
        } else if (datos.estado === 'PARTICIPANDO') {
            finalizadoAt = null;
        }

        // Gestión del estado
        if (datos.estado === 'FINALIZADO') {
            mostrarModoFinalizado(datos.estado_label);
        } else if (datos.estado === 'PARTICIPANDO') {
            corriendo = true;
            if (avisoFinalizadoEl) {
                avisoFinalizadoEl.classList.add('hidden');
                avisoFinalizadoEl.classList.remove('flex', 'block');
            }
            const contenedoresCorregir = document.querySelectorAll('[data-contenedor-corregir]');
            contenedoresCorregir.forEach(el => el.classList.add('hidden'));
            habilitarBotonCalificar();
        } else if (datos.estado === 'EN_ESCENARIO') {
            corriendo = false;
            segundosRestantes = tiempoMaximo * 60;
            if (avisoFinalizadoEl) {
                avisoFinalizadoEl.classList.add('hidden');
                avisoFinalizadoEl.classList.remove('flex', 'block');
            }
            const contenedoresCorregir = document.querySelectorAll('[data-contenedor-corregir]');
            contenedoresCorregir.forEach(el => el.classList.add('hidden'));
            deshabilitarBotonCalificar();
        }

        if (typeof datos.segundos_restantes === 'number') {
            segundosRestantes = datos.segundos_restantes;
        }

        actualizarControlTiempoUI();
        segundosRestantes = calcularSegundosRestantes();
        renderizar();
    }

    // Interceptar envío de formularios de control de cronómetro (Iniciar / Detener)
    // El jurado presidente envía la orden vía AJAX sin recargar la página,
    // garantizando que todos los jurados inicien al mismo segundo sin desfase
    if (!window.__cronometroControlAjaxRegistrado) {
        window.__cronometroControlAjaxRegistrado = true;
        document.addEventListener('submit', function (e) {
            const form = e.target;
            if (!form || !form.closest('[data-control-tiempo-caja]')) {
                return;
            }

            e.preventDefault();
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            }

            const actionUrl = form.action;
            const formData = new FormData(form);

            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(res => {
                if (!res.ok) {
                    throw new Error(`HTTP ${res.status}`);
                }
                return res.json();
            })
            .then(data => {
                if (data && data.cronometro) {
                    aplicarCronometro(data.cronometro);
                }
            })
            .catch(err => {
                console.error('Error al controlar cronómetro vía AJAX:', err);
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('opacity-60', 'cursor-not-allowed');
                }
            });
        });
    }

    // Intervalo de renderizado y sincronización en alta frecuencia (100ms)
    // Calcula contra la marca de tiempo oficial del servidor para evitar desfases o retrasos acumulativos entre clientes
    window.setInterval(() => {
        if (corriendo) {
            const nuevosSegundos = calcularSegundosRestantes();
            if (nuevosSegundos !== segundosRestantes) {
                segundosRestantes = nuevosSegundos;
                if (segundosRestantes <= 0) {
                    segundosRestantes = 0;
                    mostrarModoFinalizado();
                }
                renderizar();
            }
        }
    }, 100);

    // Escuchar el canal de presentaciones (privado y público para máxima compatibilidad)
    const canales = [
        window.Echo.private('presentaciones'),
        window.Echo.channel('presentaciones'),
    ];

    canales.forEach((ch) => {
        if (!ch) return;
        ch.listen('.presentacion.estado.cambiado', (datos) => {
            aplicarEstadoCambiado(datos);
        })
        .listen('.cronometro.actualizado', (datos) => {
            if (presentacionId && parseInt(datos.presentacion_id, 10) !== presentacionId) {
                return;
            }
            aplicarCronometro(datos);
            if (datos.estado_label && estadoEl) {
                estadoEl.textContent = datos.estado_label;
                actualizarEstilosEstado(datos.estado);
            }
            if (datos.nombre_presentacion && nombreEl) {
                nombreEl.textContent = datos.nombre_presentacion;
            }
        })
        .listen('.calificacion.registrada', (datos) => {
            if (presentacionId && parseInt(datos.presentacion_id, 10) === presentacionId) {
                const juradoItem = document.querySelector(`[data-jurado-usuario-id="${datos.jurado_id}"]`);
                if (juradoItem) {
                    const badge = juradoItem.querySelector('[data-jurado-estado-badge]');
                    if (badge) {
                        badge.textContent = 'Voto emitido';
                        badge.className = 'px-2 py-0.5 rounded text-[10px] font-bold bg-emerald-500/20 text-emerald-300';
                    }
                }
            }
        });
    });

    renderizar();
}