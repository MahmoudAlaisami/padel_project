/* ============================================================
   Padel Pitch Reservation — Main JavaScript
   ============================================================ */

// ── Navbar mobile toggle ─────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.navbar-toggle');
    const nav    = document.querySelector('.navbar-nav');
    if (toggle && nav) {
        toggle.addEventListener('click', () => nav.classList.toggle('open'));
    }

    // Close nav on outside click
    document.addEventListener('click', (e) => {
        if (nav && !nav.contains(e.target) && toggle && !toggle.contains(e.target)) {
            nav.classList.remove('open');
        }
    });

    initReservationForm();
    initModals();
    initConfirmButtons();
    autoHideAlerts();
});

// ── Reservation price calculator ─────────────────────────────
function initReservationForm() {
    const form = document.getElementById('reservationForm');
    if (!form) return;

    const fields = ['pitch_type_id', 'ball_type_id', 'racket_type_id', 'number_of_balls', 'number_of_rackets', 'start_time', 'end_time'];
    fields.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener('change', updatePrice);
    });

    updatePrice();
}

function updatePrice() {
    const pitchId   = parseInt(document.getElementById('pitch_type_id')?.value) || 0;
    const ballId    = parseInt(document.getElementById('ball_type_id')?.value) || 0;
    const racketId  = parseInt(document.getElementById('racket_type_id')?.value) || 0;
    const numBalls  = parseInt(document.getElementById('number_of_balls')?.value) || 0;
    const numRackets = parseInt(document.getElementById('number_of_rackets')?.value) || 0;
    const startTime = document.getElementById('start_time')?.value;
    const endTime   = document.getElementById('end_time')?.value;
    const summary   = document.getElementById('priceSummary');

    if (!summary) return;

    if (!pitchId || !startTime || !endTime) {
        summary.style.display = 'none';
        return;
    }

    const start = new Date(startTime);
    const end   = new Date(endTime);
    if (end <= start) {
        summary.style.display = 'none';
        return;
    }

    const hours = (end - start) / 3600000;

    // Prices are embedded as data attributes on the select options
    const pitchPrice  = parseFloat(document.getElementById('pitch_type_id')?.selectedOptions[0]?.dataset?.price) || 0;
    const ballPrice   = parseFloat(document.getElementById('ball_type_id')?.selectedOptions[0]?.dataset?.price) || 0;
    const racketPrice = parseFloat(document.getElementById('racket_type_id')?.selectedOptions[0]?.dataset?.price) || 0;

    const pitchTotal  = pitchPrice * hours;
    const ballTotal   = ballPrice * numBalls;
    const racketTotal = racketPrice * numRackets;
    const grand       = pitchTotal + ballTotal + racketTotal;

    document.getElementById('priceHours').textContent    = hours.toFixed(2) + ' hr(s)';
    document.getElementById('pricePitch').textContent    = '$' + pitchTotal.toFixed(2);
    document.getElementById('priceBalls').textContent    = '$' + ballTotal.toFixed(2);
    document.getElementById('priceRackets').textContent  = '$' + racketTotal.toFixed(2);
    document.getElementById('priceTotal').textContent    = '$' + grand.toFixed(2);

    summary.style.display = 'block';
}

// ── Modal helpers ────────────────────────────────────────────
function initModals() {
    document.querySelectorAll('[data-modal-open]').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = document.getElementById(btn.dataset.modalOpen);
            if (target) target.classList.add('active');
        });
    });

    document.querySelectorAll('.modal-close, [data-modal-close]').forEach(btn => {
        btn.addEventListener('click', () => {
            btn.closest('.modal-backdrop')?.classList.remove('active');
        });
    });

    document.querySelectorAll('.modal-backdrop').forEach(backdrop => {
        backdrop.addEventListener('click', (e) => {
            if (e.target === backdrop) backdrop.classList.remove('active');
        });
    });
}

function openModal(id) {
    document.getElementById(id)?.classList.add('active');
}

function closeModal(id) {
    document.getElementById(id)?.classList.remove('active');
}

// ── Confirm buttons (delete / cancel) ───────────────────────
function initConfirmButtons() {
    document.querySelectorAll('[data-confirm]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            if (!confirm(btn.dataset.confirm || 'Are you sure?')) {
                e.preventDefault();
            }
        });
    });
}

// ── Auto-hide alerts after 4 seconds ────────────────────────
function autoHideAlerts() {
    document.querySelectorAll('.alert').forEach(alert => {
        setTimeout(() => {
            alert.style.transition = 'opacity 0.5s ease';
            alert.style.opacity    = '0';
            setTimeout(() => alert.remove(), 500);
        }, 4000);
    });
}

// ── Table search ─────────────────────────────────────────────
function tableSearch(inputId, tableId) {
    const input = document.getElementById(inputId);
    const table = document.getElementById(tableId);
    if (!input || !table) return;

    input.addEventListener('input', () => {
        const term = input.value.toLowerCase();
        table.querySelectorAll('tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(term) ? '' : 'none';
        });
    });
}
