<?php
class ViewAppointmentModal
{
    public static function render()
    {
?>
        <div id="viewAppointmentModal" class="fixed inset-0 z-50 hidden items-center justify-center"
            style="background-color: rgba(0,0,0,0.48);">
            <div class="vam-box apt-modal-animate">

                <!-- Header -->
                <div class="vam-header">
                    <div style="display:flex;align-items:center;gap:12px;flex:1;">
                        <div class="vam-header-icon">
                            <svg width="18" height="18" fill="none" stroke="#2d8a6e" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round"
                                    d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <div>
                            <p class="vam-title">Appointment Details</p>
                            <p id="vamAppointmentId" class="vam-subtitle"></p>
                        </div>
                    </div>
                    <button type="button" onclick="closeViewAppointmentModal()" class="vam-close-btn">
                        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Body -->
                <div class="vam-body">
                    <div id="viewAppointmentContent">
                        <div class="vam-loading">
                            <div class="vam-spinner"></div>
                            <p>Loading appointment details…</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <div class="vam-footer">
                    <button type="button" onclick="closeViewAppointmentModal()" class="vam-btn-close">
                        Close
                    </button>
                </div>
            </div>
        </div>
        </div>

        <style>
            @keyframes aptSlideDown {
                from {
                    opacity: 0;
                    transform: translateY(-40px) scale(0.98);
                }

                to {
                    opacity: 1;
                    transform: translateY(0) scale(1);
                }
            }

            .apt-modal-animate {
                animation: aptSlideDown 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
            }

            /* Box */
            .vam-box {
                background: #fff;
                border-radius: 14px;
                box-shadow: 0 8px 32px rgba(0, 0, 0, 0.14);
                max-width: 540px;
                width: calc(100% - 32px);
                max-height: 90vh;
                overflow-y: auto;
                margin: 16px;

                /* Hide scrollbar — Firefox */
                scrollbar-width: none;
                /* Hide scrollbar — IE/Edge */
                -ms-overflow-style: none;
            }

            /* Hide scrollbar — Chrome/Safari */
            .vam-box::-webkit-scrollbar {
                display: none;
            }

            /* Header */
            .vam-header {
                display: flex;
                align-items: center;
                gap: 12px;
                padding: 14px 18px;
                border-bottom: 1px solid #eef0ec;
                background: #f8faf8;
                border-radius: 14px 14px 0 0;
                position: sticky;
                top: 0;
                z-index: 2;
            }

            .vam-header-icon {
                width: 36px;
                height: 36px;
                border-radius: 9px;
                background: #e1f5ee;
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
            }

            .vam-title {
                font-size: 14px;
                font-weight: 600;
                color: #1a2e25;
                margin: 0 0 2px;
            }

            .vam-subtitle {
                font-size: 11px;
                color: #8aa898;
                margin: 0;
            }

            .vam-close-btn {
                margin-left: auto;
                background: none;
                border: none;
                cursor: pointer;
                padding: 6px;
                border-radius: 7px;
                color: #8aa898;
                transition: background 0.15s, color 0.15s;
            }

            .vam-close-btn:hover {
                background: #eef0ec;
                color: #1a2e25;
            }

            /* Body */
            .vam-body {
                padding: 16px 18px;
            }

            /* Loading */
            .vam-loading {
                text-align: center;
                padding: 40px 0;
                color: #8aa898;
                font-size: 13px;
            }

            .vam-spinner {
                display: inline-block;
                width: 28px;
                height: 28px;
                border: 2.5px solid #d1ece3;
                border-top-color: #2d8a6e;
                border-radius: 50%;
                animation: spin 0.7s linear infinite;
                margin-bottom: 10px;
            }

            @keyframes spin {
                to {
                    transform: rotate(360deg);
                }
            }

            /* Service / status row */
            .vam-service-row {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                padding-bottom: 14px;
                border-bottom: 1px solid #eef0ec;
                margin-bottom: 4px;
            }

            .vam-service-name {
                font-size: 15px;
                font-weight: 600;
                color: #1a2e25;
                margin: 0 0 3px;
            }

            .vam-apt-ref {
                font-size: 11px;
                color: #8aa898;
            }

            /* Detail rows */
            .vam-detail-row {
                display: flex;
                gap: 14px;
                padding: 11px 0;
                border-bottom: 1px solid #f0f3f0;
            }

            .vam-detail-row:last-child {
                border-bottom: none;
            }

            .vam-detail-icon {
                flex-shrink: 0;
                width: 36px;
                height: 36px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f0f8f4;
                border-radius: 9px;
                color: #2d8a6e;
            }

            .vam-detail-icon svg {
                width: 17px;
                height: 17px;
            }

            .vam-detail-content {
                flex: 1;
                min-width: 0;
            }

            .vam-detail-label {
                font-size: 10px;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 0.06em;
                color: #7aaa96;
                display: block;
                margin-bottom: 3px;
            }

            .vam-detail-value {
                font-size: 13.5px;
                color: #1a2e25;
                font-weight: 500;
            }

            /* Cap bar */
            .vam-cap-bar {
                height: 5px;
                background: #e5e7eb;
                border-radius: 3px;
                overflow: hidden;
                margin-top: 6px;
            }

            .vam-cap-fill {
                height: 100%;
                background: #2d8a6e;
                border-radius: 3px;
                transition: width 0.4s ease;
            }

            .vam-cap-fill--done {
                background: #64b99a;
            }

            /* Note box */
            .vam-note-box {
                background: #f8faf8;
                border-left: 3px solid #a8d5c2;
                border-radius: 0 8px 8px 0;
                padding: 9px 12px;
                font-size: 12.5px;
                color: #4a6b5a;
                line-height: 1.65;
                margin-top: 5px;
            }

            /* Meta footer */
            .vam-meta-row {
                padding-top: 12px;
                border-top: 1px solid #eef0ec;
                margin-top: 4px;
                font-size: 11px;
                color: #aab8b0;
            }

            /* Modal footer */
            .vam-footer {
                padding: 13px 18px;
                border-top: 1px solid #eef0ec;
                background: #f8faf8;
                border-radius: 0 0 14px 14px;
                display: flex;
                justify-content: flex-end;
                gap: 10px;
            }

            .vam-btn-close {
                padding: 7px 18px;
                font-size: 12.5px;
                border-radius: 8px;
                border: 1px solid #d4ddd8;
                background: #fff;
                color: #4a6b5a;
                cursor: pointer;
                transition: background 0.15s;
            }

            .vam-btn-close:hover {
                background: #eef0ec;
            }

            .vam-btn-edit {
                padding: 7px 18px;
                font-size: 12.5px;
                border-radius: 8px;
                border: none;
                background: #2d8a6e;
                color: #fff;
                font-weight: 600;
                cursor: pointer;
                transition: background 0.15s;
            }

            .vam-btn-edit:hover {
                background: #247a60;
            }
        </style>

        <script>
            let currentViewAppointmentId = null;

            function viewAppointmentDetails(id) {
                currentViewAppointmentId = id;
                const modal = document.getElementById('viewAppointmentModal');
                const content = document.getElementById('viewAppointmentContent');
                const box = modal.querySelector('.vam-box');

                document.getElementById('vamAppointmentId').textContent = '';
                content.innerHTML = `
                    <div class="vam-loading">
                        <div class="vam-spinner"></div>
                        <p>Loading appointment details…</p>
                    </div>`;

                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';

                // Re-trigger animation
                box.classList.remove('apt-modal-animate');
                void box.offsetWidth;
                box.classList.add('apt-modal-animate');

                fetch(`../api/get-appointment.php?id=${id}`)
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            displayAppointmentDetails(data.appointment);
                        } else {
                            content.innerHTML = vamErrorHtml(data.message || 'Unknown error');
                        }
                    })
                    .catch(() => {
                        content.innerHTML = vamErrorHtml('Failed to load appointment details.');
                    });
            }

            function vamErrorHtml(msg) {
                return `<div class="vam-loading" style="color:#c0504d;">
                    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:8px;">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p>${escapeHtml(msg)}</p>
                </div>`;
            }

            function displayAppointmentDetails(apt) {
                const content = document.getElementById('viewAppointmentContent');
                document.getElementById('vamAppointmentId').textContent = 'ID #APT-' + apt.id;

                const filled = parseInt(apt.registered_students || 0);
                const max = parseInt(apt.max_students || 1);
                const pct = Math.min(100, Math.round(filled / max * 100));
                const sname = apt.status_name || 'Scheduled';
                const isDone = /complet|done/i.test(sname);

                content.innerHTML = `
                <div>
                    <div class="vam-service-row">
                        <div>
                            <p class="vam-service-name">${escapeHtml(apt.service_name)}</p>
                            <p class="vam-apt-ref">Appointment #${apt.id}</p>
                        </div>
                        <span class="apt-badge ${getStatusBadgeClass(sname)}">${escapeHtml(sname)}</span>
                    </div>

                    <div class="vam-detail-row">
                        <div class="vam-detail-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </div>
                        <div class="vam-detail-content">
                            <span class="vam-detail-label">Healthcare Provider</span>
                            <span class="vam-detail-value">${escapeHtml(apt.provider_name || 'Not assigned')}</span>
                        </div>
                    </div>

                    <div class="vam-detail-row">
                        <div class="vam-detail-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div class="vam-detail-content">
                            <span class="vam-detail-label">Visit Date</span>
                            <span class="vam-detail-value">${formatDate(apt.visit_date)}</span>
                        </div>
                    </div>

                    <div class="vam-detail-row">
                        <div class="vam-detail-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div class="vam-detail-content">
                            <span class="vam-detail-label">Time Slot</span>
                            <span class="vam-detail-value">${formatTime(apt.start_time)} – ${formatTime(apt.end_time)}</span>
                        </div>
                    </div>

                    <div class="vam-detail-row">
                        <div class="vam-detail-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <div class="vam-detail-content">
                            <span class="vam-detail-label">Student Capacity</span>
                            <span class="vam-detail-value">${filled} / ${max} students</span>
                            <div class="vam-cap-bar">
                                <div class="vam-cap-fill ${isDone ? 'vam-cap-fill--done' : ''}" style="width:${pct}%"></div>
                            </div>
                        </div>
                    </div>

                    ${apt.notes ? `
                    <div class="vam-detail-row">
                        <div class="vam-detail-icon">
                            <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                        </div>
                        <div class="vam-detail-content">
                            <span class="vam-detail-label">Notes</span>
                            <div class="vam-note-box">${escapeHtml(apt.notes)}</div>
                        </div>
                    </div>` : ''}

                    <div class="vam-meta-row">Created on ${formatDateTime(apt.created_at)}</div>
                </div>`;
            }

            function closeViewAppointmentModal() {
                const modal = document.getElementById('viewAppointmentModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
                currentViewAppointmentId = null;
            }

            function getStatusBadgeClass(s) {
                s = (s || '').toLowerCase();
                if (s.includes('progress')) return 'apt-badge--progress';
                if (s.includes('cancel')) return 'apt-badge--cancelled';
                if (s.includes('complet') || s.includes('done')) return 'apt-badge--completed';
                if (s.includes('confirm')) return 'apt-badge--confirmed';
                return 'apt-badge--scheduled';
            }

            function formatDate(d) {
                if (!d) return 'N/A';
                return new Date(d).toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric'
                });
            }

            function formatTime(t) {
                if (!t) return 'N/A';
                const [h, m] = t.split(':');
                const d = new Date();
                d.setHours(+h, +m);
                return d.toLocaleTimeString('en-US', {
                    hour: 'numeric',
                    minute: '2-digit',
                    hour12: true
                });
            }

            function formatDateTime(dt) {
                if (!dt) return 'N/A';
                return new Date(dt).toLocaleString('en-US', {
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function escapeHtml(text) {
                if (!text) return '';
                const d = document.createElement('div');
                d.textContent = text;
                return d.innerHTML;
            }

            document.getElementById('viewAppointmentModal')?.addEventListener('click', function(e) {
                if (e.target === this) closeViewAppointmentModal();
            });
        </script>
<?php
    }
}
?>