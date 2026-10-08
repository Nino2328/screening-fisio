/**
 * public/js/screening.js
 * FootScreen Pro — Global JavaScript
 */

/* ============================================
   TOAST NOTIFICATION
   ============================================ */
function showToast(message, icon = "✅", duration = 3000) {
    const toast = document.getElementById("toast");
    const msgEl = document.getElementById("toast-msg");
    const iconEl = document.getElementById("toast-icon");
    if (!toast) return;
    msgEl.textContent = message;
    iconEl.textContent = icon;
    toast.classList.remove("translate-y-20", "opacity-0");
    toast.classList.add("translate-y-0", "opacity-100");
    setTimeout(() => {
        toast.classList.add("translate-y-20", "opacity-0");
        toast.classList.remove("translate-y-0", "opacity-100");
    }, duration);
}

/* ============================================
   SIDEBAR TOGGLE (mobile)
   ============================================ */
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    if (sidebar) sidebar.classList.toggle("open");
}

/* ============================================
   FILL ATHLETE DATA from dropdown
   ============================================ */
function fillAthleteData(select) {
    const opt = select.options[select.selectedIndex];
    if (!opt.value) return;

    const fields = {
        name: '[name="name"]',
        age: '[name="age"]',
        gender: '[name="gender"]',
        position: '[name="position"]',
        height: '[name="height"]',
        weight: '[name="weight"]',
        phone: '[name="phone"]',
    };

    Object.entries(fields).forEach(([key, selector]) => {
        const el = document.querySelector(selector);
        const val = opt.dataset[key];
        if (!el || !val) return;

        if (el.tagName === "SELECT") {
            const normalizedValue = val.toLowerCase();
            const genderAliases = {
                m: ["m", "putra", "pria"],
                f: ["f", "putri", "wanita"],
            };
            const allowedValues = genderAliases[normalizedValue] || [
                normalizedValue,
            ];
            const matchingOption = Array.from(el.options).find((option) =>
                allowedValues.includes(option.value.toLowerCase()),
            );

            if (matchingOption) el.value = matchingOption.value;
        } else {
            el.value = val;
        }
    });

    showToast(`Data ${opt.dataset.name} berhasil dimuat`, "👤");
}

/* ============================================
   AUTO-SAVE DRAFT to sessionStorage
   ============================================ */
function saveDraft(formId) {
    const form = document.getElementById(formId);
    if (!form) return;
    const data = {};
    new FormData(form).forEach((v, k) => {
        if (k !== "_token") data[k] = v;
    });
    sessionStorage.setItem("draft_" + formId, JSON.stringify(data));
}

function loadDraft(formId) {
    const raw = sessionStorage.getItem("draft_" + formId);
    if (!raw) return;
    const data = JSON.parse(raw);
    Object.entries(data).forEach(([name, value]) => {
        const el = document.querySelector(`[name="${name}"]`);
        if (!el) return;
        if (el.tagName === "SELECT") {
            Array.from(el.options).forEach(
                (o) => (o.selected = o.value === value),
            );
        } else {
            el.value = value;
        }
    });
}

/* ============================================
   FORM VALIDATION HELPERS
   ============================================ */
function validateRequired(formEl) {
    let valid = true;
    formEl.querySelectorAll("[required]").forEach((el) => {
        if (!el.value.trim()) {
            el.classList.add("error");
            valid = false;
        } else {
            el.classList.remove("error");
        }
    });
    if (!valid) showToast("Harap isi semua field wajib", "⚠️");
    return valid;
}

/* ============================================
   CSRF TOKEN for AJAX
   ============================================ */
const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

/* ============================================
   ON DOM READY
   ============================================ */
document.addEventListener("DOMContentLoaded", () => {
    // Flash success auto-dismiss
    const flash = document.querySelector(".flash-success");
    if (flash) setTimeout(() => (flash.style.display = "none"), 4000);

    // Show toast if session success message exists
    const successMsg = document.querySelector("[data-toast]");
    if (successMsg) showToast(successMsg.dataset.toast);

    // Close sidebar on outside click (mobile)
    document.addEventListener("click", (e) => {
        const sidebar = document.getElementById("sidebar");
        const btn = document.querySelector(".mobile-menu-btn");
        if (
            sidebar &&
            sidebar.classList.contains("open") &&
            !sidebar.contains(e.target) &&
            !btn?.contains(e.target)
        ) {
            sidebar.classList.remove("open");
        }
    });
});
