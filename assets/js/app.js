/**
 * Campus Marketplace — Client-side JavaScript
 * Toast notifications, AJAX helpers, search, offers
 */

/* ------------------------------------------------------------------
 * Toast notification system
 * ----------------------------------------------------------------*/
function showToast(message, type = 'success') {
    const container = document.getElementById('toastContainer');
    if (!container) return;

    const id = 'toast-' + Date.now();
    const iconMap = {
        success: 'fas fa-check-circle text-success',
        error:   'fas fa-times-circle text-danger',
        info:    'fas fa-info-circle text-primary',
    };

    const html = `
        <div id="${id}" class="toast toast-custom toast-${type} show" role="alert">
            <div class="d-flex align-items-center p-3">
                <i class="${iconMap[type] || iconMap.info} me-2 fs-5"></i>
                <div class="flex-grow-1 small">${escapeHtml(message)}</div>
                <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="toast"></button>
            </div>
        </div>`;
    container.insertAdjacentHTML('beforeend', html);

    const el = document.getElementById(id);
    setTimeout(() => {
        el.classList.add('fade');
        setTimeout(() => el.remove(), 300);
    }, 4000);
}

function escapeHtml(str) {
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

/* ------------------------------------------------------------------
 * AJAX helper
 * ----------------------------------------------------------------*/
async function apiRequest(url, method = 'GET', body = null) {
    const opts = {
        method,
        headers: {
            'X-CSRF-TOKEN': CSRF,
            'Accept': 'application/json',
        },
    };

    if (body && method !== 'GET') {
        if (body instanceof FormData) {
            opts.body = body;
            // Don't set Content-Type; let browser set boundary
        } else {
            opts.headers['Content-Type'] = 'application/json';
            opts.body = JSON.stringify(body);
        }
    }

    try {
        const resp = await fetch(BASE + url, opts);
        const data = await resp.json();
        return { ok: resp.ok, status: resp.status, data };
    } catch (err) {
        return { ok: false, status: 0, data: { success: false, message: 'Network error. Please try again.' } };
    }
}

/* ------------------------------------------------------------------
 * Offer actions (called from product-details & offer pages)
 * ----------------------------------------------------------------*/
async function submitOffer(productId, price, message) {
    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('product_id', productId);
    fd.append('offered_price', price);
    fd.append('message', message);

    const res = await apiRequest('api/offers.php?action=create', 'POST', fd);
    if (res.data.success) {
        showToast(res.data.message, 'success');
        setTimeout(() => location.reload(), 800);
    } else {
        showToast(res.data.message || 'Failed to submit offer.', 'error');
    }
    return res;
}

async function updateOfferStatus(offerId, action) {
    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('offer_id', offerId);
    fd.append('action', action);

    const res = await apiRequest('api/offers.php?action=update_status', 'POST', fd);
    if (res.data.success) {
        showToast(res.data.message, 'success');
        setTimeout(() => location.reload(), 800);
    } else {
        showToast(res.data.message || 'Action failed.', 'error');
    }
    return res;
}

async function submitCounterOffer(parentOfferId, price, message) {
    const fd = new FormData();
    fd.append('csrf_token', CSRF);
    fd.append('parent_offer_id', parentOfferId);
    fd.append('offered_price', price);
    fd.append('message', message);

    const res = await apiRequest('api/counteroffers.php', 'POST', fd);
    if (res.data.success) {
        showToast(res.data.message, 'success');
        setTimeout(() => location.reload(), 800);
    } else {
        showToast(res.data.message || 'Counter offer failed.', 'error');
    }
    return res;
}

/* ------------------------------------------------------------------
 * AJAX search (browse page)
 * ----------------------------------------------------------------*/
let searchTimeout = null;
function initLiveSearch() {
    const searchInput = document.getElementById('searchInput');
    if (!searchInput) return;

    searchInput.addEventListener('input', function () {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            document.getElementById('filterForm')?.submit();
        }, 500);
    });
}

/* ------------------------------------------------------------------
 * Confirm actions
 * ----------------------------------------------------------------*/
function confirmAction(msg, callback) {
    if (confirm(msg)) callback();
}

/* ------------------------------------------------------------------
 * Init on DOMContentLoaded
 * ----------------------------------------------------------------*/
document.addEventListener('DOMContentLoaded', () => {
    initLiveSearch();

    // Auto-dismiss alerts after 5s
    document.querySelectorAll('.alert-dismissible').forEach(alert => {
        setTimeout(() => {
            alert.classList.remove('show');
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
});
