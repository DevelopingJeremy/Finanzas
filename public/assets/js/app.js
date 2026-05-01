/**
 * app.js - Finanzas App JavaScript
 * Lógica de cliente: distribuciones dinámicas, validaciones, UI helpers
 */

/* =============================================
   DISTRIBUCIONES DINÁMICAS (Subcuentas / Bolsillos)
   ============================================= */

let distIndex = 0;

function addDistRow() {
    const container = document.getElementById('dist-container');
    if (!container) return;

    const subcuentas = window.SUBCUENTAS || [];
    if (subcuentas.length === 0) {
        alert('Primero debes seleccionar una cuenta con subcuentas.');
        return;
    }

    let options = subcuentas.map(s =>
        `<option value="${s.id}">${s.nombre} (Saldo: ₡${formatNum(s.saldo)})</option>`
    ).join('');

    const row = document.createElement('div');
    row.className = 'distribution-row';
    row.dataset.idx = distIndex;
    row.innerHTML = `
        <div>
            <label class="form-label">Subcuenta</label>
            <select name="dist_subcuenta[]" class="form-control">${options}</select>
        </div>
        <div>
            <label class="form-label">Monto (₡)</label>
            <input type="number" name="dist_monto[]" class="form-control dist-monto"
                   step="0.01" min="0" placeholder="0.00" oninput="updateDistTotal()">
        </div>
        <div>
            <label class="form-label">&nbsp;</label>
            <button type="button" class="btn btn-danger btn-icon" onclick="removeDistRow(this)">✕</button>
        </div>
    `;
    container.appendChild(row);
    distIndex++;
    updateDistTotal();
}

function removeDistRow(btn) {
    btn.closest('.distribution-row').remove();
    updateDistTotal();
}

function updateDistTotal() {
    const totalMonto = parseFloat(document.getElementById('monto')?.value || 0);
    const rows = document.querySelectorAll('.dist-monto');
    let sum = 0;
    rows.forEach(r => sum += parseFloat(r.value || 0));

    const indicator = document.getElementById('dist-total-indicator');
    if (!indicator) return;

    const remaining = totalMonto - sum;
    indicator.textContent = `Distribuido: ₡${formatNum(sum)} | Restante: ₡${formatNum(remaining)}`;
    indicator.className = remaining < 0 ? 'over' : (remaining === 0 && sum > 0 ? 'ok' : '');
}

function formatNum(n) {
    return parseFloat(n || 0).toLocaleString('es-CR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/* =============================================
   CARGAR SUBCUENTAS AL CAMBIAR CUENTA
   ============================================= */
function onCuentaChange(select) {
    const cuentaId = select.value;
    if (!cuentaId) return;

    fetch(`${BASE_URL}/?c=subcuentas&a=getByAccount&cuenta_id=${cuentaId}`)
        .then(r => r.json())
        .then(data => {
            window.SUBCUENTAS = data;
            // Limpiar distribuciones existentes
            const container = document.getElementById('dist-container');
            if (container) container.innerHTML = '';
        })
        .catch(err => console.error('Error cargando subcuentas:', err));
}

/* =============================================
   MOSTRAR/OCULTAR CUENTA DESTINO EN TRANSFERENCIAS
   ============================================= */
function onTipoChange(select) {
    const destino = document.getElementById('cuenta-destino-wrap');
    const distribucion = document.getElementById('distribucion-section');

    if (!destino) return;

    if (select.value === 'transferencia') {
        destino.style.display = 'block';
        if (distribucion) distribucion.style.display = 'none';
    } else {
        destino.style.display = 'none';
        if (distribucion) distribucion.style.display = 'block';
    }
}

/* =============================================
   FLASH MESSAGE AUTO-HIDE
   ============================================= */
document.addEventListener('DOMContentLoaded', () => {
    // Auto-ocultar alertas después de 5 segundos
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(a => {
        setTimeout(() => {
            a.style.transition = 'opacity 0.5s ease';
            a.style.opacity = '0';
            setTimeout(() => a.remove(), 500);
        }, 5000);
    });

    // Inicializar tipo en formulario de transacciones
    const tipoSelect = document.getElementById('tipo');
    if (tipoSelect) onTipoChange(tipoSelect);
});

/* =============================================
   CONFIRMAR ELIMINACIÓN
   ============================================= */
function confirmDelete(url, name = 'este registro') {
    if (confirm(`¿Estás seguro que deseas eliminar "${name}"? Esta acción no se puede deshacer.`)) {
        window.location.href = url;
    }
}

/* =============================================
   BASE URL para fetch
   ============================================= */
const BASE_URL = '/public';
