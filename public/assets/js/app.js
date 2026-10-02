/**
 * app.js - Finanzas App JavaScript
 * Lógica de cliente: transferencias entre bolsillos/cuentas, distribuciones dinámicas, UI helpers
 */

const BASE_URL = '/public';
let distIndex = 0;

/* =============================================
   DISTRIBUCIONES DINÁMICAS (Subcuentas / Bolsillos)
   ============================================= */
function addDistRow() {
    const container = document.getElementById('dist-container');
    if (!container) return;

    const subcuentas = window.SUBCUENTAS || [];
    if (subcuentas.length === 0) {
        alert('Primero debes seleccionar una cuenta que tenga bolsillos.');
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
            <label class="form-label">Bolsillo</label>
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
    if (rows.length === 0) {
        indicator.textContent = '';
        indicator.className = '';
        return;
    }
    indicator.textContent = `Distribuido: ₡${formatNum(sum)} | Restante: ₡${formatNum(remaining)}`;
    indicator.className = remaining < 0 ? 'over' : (remaining === 0 && sum > 0 ? 'ok' : '');
}

function formatNum(n) {
    return parseFloat(n || 0).toLocaleString('es-CR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

/* =============================================
   CARGAR Y POBLAR SUBCUENTAS
   ============================================= */
function populateSubcuentasSelect(selectElem, subcuentas, selectedVal = '', defaultLabel = '— Saldo Principal (Cuenta Normal) —') {
    if (!selectElem) return;
    let html = `<option value="">${defaultLabel}</option>`;
    subcuentas.forEach(s => {
        const isSel = String(s.id) === String(selectedVal) ? 'selected' : '';
        html += `<option value="${s.id}" ${isSel}>👛 ${s.nombre} (Saldo: ₡${formatNum(s.saldo)})</option>`;
    });
    selectElem.innerHTML = html;
}

function fetchSubcuentas(cuentaId, callback) {
    if (!cuentaId) {
        callback([]);
        return;
    }
    fetch(`${BASE_URL}/?c=subcuentas&a=getByAccount&cuenta_id=${cuentaId}`)
        .then(r => r.json())
        .then(data => callback(data || []))
        .catch(err => {
            console.error('Error cargando subcuentas:', err);
            callback([]);
        });
}

function onCuentaChange(select) {
    const cuentaId = select.value;
    const scSelect = document.getElementById('subcuenta_id');

    fetchSubcuentas(cuentaId, (subcuentas) => {
        window.SUBCUENTAS = subcuentas;
        const preSel = window.PRE_ORIGEN_SUBCUENTA || '';
        window.PRE_ORIGEN_SUBCUENTA = ''; // Consumir
        populateSubcuentasSelect(scSelect, subcuentas, preSel);

        // Limpiar distribuciones múltiples existentes
        const container = document.getElementById('dist-container');
        if (container) container.innerHTML = '';
        updateDistTotal();
        updateTransferSummary();
    });
}

function onCuentaDestinoChange(select) {
    const cuentaId = select.value;
    const scDestSelect = document.getElementById('subcuenta_destino_id');

    fetchSubcuentas(cuentaId, (subcuentas) => {
        window.SUBCUENTAS_DESTINO = subcuentas;
        const preSel = window.PRE_DESTINO_SUBCUENTA || '';
        window.PRE_DESTINO_SUBCUENTA = ''; // Consumir
        populateSubcuentasSelect(scDestSelect, subcuentas, preSel);
        updateTransferSummary();
    });
}

/* =============================================
   MANEJO DE TIPO DE TRANSACCIÓN
   ============================================= */
function onTipoChange(select) {
    const tipo = select.value;
    const destinoBlock = document.getElementById('transferencia-destino-block');
    const distribucion = document.getElementById('distribucion-section');
    const categoriaWrap = document.getElementById('categoria-wrap');
    const lblCuentaOrigen = document.getElementById('lbl_cuenta_origen');
    const lblSubcuentaOrigen = document.getElementById('lbl_subcuenta_origen');
    const cuentaDestinoSelect = document.getElementById('cuenta_destino_id');

    if (tipo === 'transferencia') {
        if (destinoBlock) destinoBlock.style.display = 'block';
        if (distribucion) distribucion.style.display = 'none';
        if (lblCuentaOrigen) lblCuentaOrigen.textContent = 'Cuenta Origen *';
        if (lblSubcuentaOrigen) lblSubcuentaOrigen.textContent = 'Bolsillo Origen';
        if (cuentaDestinoSelect) cuentaDestinoSelect.required = true;
    } else {
        if (destinoBlock) destinoBlock.style.display = 'none';
        if (distribucion) distribucion.style.display = 'block';
        if (lblCuentaOrigen) lblCuentaOrigen.textContent = 'Cuenta *';
        if (lblSubcuentaOrigen) lblSubcuentaOrigen.textContent = 'Bolsillo (Opcional)';
        if (cuentaDestinoSelect) cuentaDestinoSelect.required = false;
    }

    updateTransferSummary();
}

function updateTransferSummary() {
    const tipo = document.getElementById('tipo')?.value;
    const note = document.getElementById('transfer-summary-note');
    if (!note || tipo !== 'transferencia') return;

    const cuentaOrig = document.getElementById('cuenta_id');
    const cuentaDest = document.getElementById('cuenta_destino_id');
    const scOrig = document.getElementById('subcuenta_id');
    const scDest = document.getElementById('subcuenta_destino_id');
    const monto = parseFloat(document.getElementById('monto')?.value || 0);

    const cuentaOrigText = cuentaOrig && cuentaOrig.selectedIndex > 0 ? cuentaOrig.options[cuentaOrig.selectedIndex].text.split('(')[0].trim() : '';
    const cuentaDestText = cuentaDest && cuentaDest.selectedIndex > 0 ? cuentaDest.options[cuentaDest.selectedIndex].text.split('(')[0].trim() : '';

    const scOrigText = scOrig && scOrig.value ? scOrig.options[scOrig.selectedIndex].text.split('(')[0].trim() : 'Saldo Normal';
    const scDestText = scDest && scDest.value ? scDest.options[scDest.selectedIndex].text.split('(')[0].trim() : 'Saldo Normal';

    if (!cuentaOrigText || !cuentaDestText) {
        note.innerHTML = 'Selecciona origen y destino.';
        return;
    }

    let summary = '';
    if (cuentaOrig.value === cuentaDest.value) {
        if (scOrig.value === scDest.value) {
            summary = '<span style="color:var(--red);">⚠️ El origen y destino no pueden ser exactamente el mismo.</span>';
        } else {
            summary = `💡 <strong>Transferencia interna en ${cuentaOrigText}:</strong> de <code>${scOrigText}</code> ➔ <code>${scDestText}</code> (el saldo total de la cuenta no varía).`;
        }
    } else {
        summary = `💡 <strong>Transferencia entre cuentas:</strong> de <code>${cuentaOrigText} (${scOrigText})</code> ➔ <code>${cuentaDestText} (${scDestText})</code>.`;
    }

    if (monto > 0) {
        summary += ` Por un monto de <strong>₡${formatNum(monto)}</strong>.`;
    }
    note.innerHTML = summary;
}

/* =============================================
   INICIALIZACIÓN
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

    // Inicializar selectores en formulario de transacciones
    const tipoSelect = document.getElementById('tipo');
    if (tipoSelect) {
        onTipoChange(tipoSelect);

        const cuentaOrigen = document.getElementById('cuenta_id');
        if (cuentaOrigen && cuentaOrigen.value) {
            onCuentaChange(cuentaOrigen);
        }

        const cuentaDestino = document.getElementById('cuenta_destino_id');
        if (cuentaDestino && cuentaDestino.value) {
            onCuentaDestinoChange(cuentaDestino);
        }
    }
});

/* =============================================
   CONFIRMAR ELIMINACIÓN
   ============================================= */
function confirmDelete(url, name = 'este registro') {
    if (confirm(`¿Estás seguro que deseas eliminar "${name}"? Esta acción no se puede deshacer.`)) {
        window.location.href = url;
    }
}

