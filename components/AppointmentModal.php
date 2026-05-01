<?php
class AppointmentModal
{
    public static function render($controller, $currentUserId = null, $editAppointment = null)
    {
        $services = $controller->getServices();
        $statuses = $controller->getStatuses();
        $providers = $controller->getProviders();

        // Determine if we're in edit mode
        $isEdit = $editAppointment !== null;
        $modalTitle = $isEdit ? 'Edit Appointment' : 'Schedule Provider Visit';
        $submitText = $isEdit ? 'Update Visit' : 'Schedule Visit';
        $actionUrl = $isEdit ? '../api/update-appointment.php' : '../api/create-appointment.php';

        // Get appointment data for edit mode
        $appointmentData = $isEdit ? $editAppointment : null;
?>
        <div id="appointmentModal" class="fixed inset-0 z-50 hidden items-center justify-center" style="background-color: rgba(0,0,0,0.5);">
            <div id="appointmentModalBox" class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4">
                <div class="px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-semibold" style="color:#1a2e25;"><?php echo $modalTitle; ?></h3>
                        <button type="button" onclick="closeAppointmentModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <form id="appointmentForm" method="POST" action="<?php echo $actionUrl; ?>">
                    <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="appointment_id" value="<?php echo $appointmentData['id']; ?>">
                            <?php endif; ?>

                            <!-- Provider Selection -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" style="color:#2c4b3e;">
                                    Healthcare Provider <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="provider_id" id="provider_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]">
                                    <option value="">Select Provider</option>
                                    <?php foreach ($providers as $provider): ?>
                                        <option value="<?php echo $provider['id']; ?>"
                                            <?php echo ($isEdit && $appointmentData['provider_id'] == $provider['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($provider['name'] . ' - ' . $provider['specialization']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Service Type -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" style="color:#2c4b3e;">
                                    Service Type <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="service_id" id="service_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]">
                                    <option value="">Select Service</option>
                                    <?php foreach ($services as $service): ?>
                                        <option value="<?php echo $service['id']; ?>"
                                            <?php echo ($isEdit && $appointmentData['service_id'] == $service['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($service['service_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Status -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" style="color:#2c4b3e;">
                                    Status <span class="text-red-500 ml-1">*</span>
                                </label>
                                <select name="status_id" id="status_id" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]">
                                    <option value="">Select Status</option>
                                    <?php foreach ($statuses as $status): ?>
                                        <option value="<?php echo $status['id']; ?>"
                                            <?php echo ($isEdit && $appointmentData['status_id'] == $status['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($status['status_name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Visit Date -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" style="color:#2c4b3e;">
                                    Visit Date <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="date" name="visit_date" id="visit_date" required
                                    min="<?php echo date('Y-m-d'); ?>"
                                    value="<?php echo $isEdit ? $appointmentData['visit_date'] : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]">
                            </div>

                            <!-- Start Time -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" style="color:#2c4b3e;">
                                    Start Time <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="time" name="start_time" id="start_time" required
                                    value="<?php echo $isEdit ? $appointmentData['start_time'] : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]">
                            </div>

                            <!-- End Time -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" style="color:#2c4b3e;">
                                    End Time <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="time" name="end_time" id="end_time" required
                                    value="<?php echo $isEdit ? $appointmentData['end_time'] : ''; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]">
                            </div>

                            <!-- Maximum Students -->
                            <div>
                                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" style="color:#2c4b3e;">
                                    Maximum Students <span class="text-red-500 ml-1">*</span>
                                </label>
                                <input type="number" name="max_students" id="max_students" required
                                    placeholder="Max students for this visit"
                                    min="1"
                                    max="100"
                                    value="<?php echo $isEdit ? $appointmentData['max_students'] : '20'; ?>"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]">
                                <p class="text-xs text-gray-500 mt-1">Number of students that can sign up</p>
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label class="block text-xs font-semibold uppercase tracking-wide mb-2" style="color:#2c4b3e;">
                                    Special Instructions / Notes
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]"
                                    placeholder="Any special requirements or instructions for this visit..."><?php echo $isEdit ? htmlspecialchars($appointmentData['notes']) : ''; ?></textarea>
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" name="requester_id" value="<?php echo $currentUserId; ?>">
                        </div>
                    </div>

                    <div class="px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50 rounded-b-xl">
                        <button type="button" onclick="closeAppointmentModal()"
                            class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                            Cancel
                        </button>
                        <button type="submit"
                            class="px-4 py-2 text-sm rounded-lg text-white transition bg-[#2d8a6e] hover:bg-[#247a60]">
                            <?php echo $submitText; ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            const _aptModalStyle = document.createElement('style');
            _aptModalStyle.textContent = `
    @keyframes aptSlideDown {
        from { opacity: 0; transform: translateY(-40px) scale(0.98); }
        to   { opacity: 1; transform: translateY(0)      scale(1);   }
    }
    .apt-modal-animate {
        animation: aptSlideDown 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
`;
            document.head.appendChild(_aptModalStyle);
            let isEditMode = <?php echo $isEdit ? 'true' : 'false'; ?>;

            function openAppointmentModal() {
                const modal = document.getElementById('appointmentModal');
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                document.body.style.overflow = 'hidden';

                // Replay slide-down animation every time the modal opens
                const box = document.getElementById('appointmentModalBox');
                if (box) {
                    box.classList.remove('apt-modal-animate');
                    void box.offsetWidth;
                    box.classList.add('apt-modal-animate');
                }

                if (!isEditMode) {
                    if (!document.getElementById('start_time').value) {
                        document.getElementById('start_time').value = '09:00';
                    }
                    if (!document.getElementById('end_time').value) {
                        document.getElementById('end_time').value = '17:00';
                    }
                }
            }

            function openEditModal(appointmentId) {
                fetch(`../api/get-appointment.php?id=${appointmentId}`)
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.getElementById('provider_id').value = data.appointment.provider_id;
                            document.getElementById('service_id').value = data.appointment.service_id;
                            document.getElementById('status_id').value = data.appointment.status_id;
                            document.getElementById('visit_date').value = data.appointment.visit_date;
                            document.getElementById('start_time').value = data.appointment.start_time;
                            document.getElementById('end_time').value = data.appointment.end_time;
                            document.getElementById('max_students').value = data.appointment.max_students;
                            document.getElementById('notes').value = data.appointment.notes;

                            document.getElementById('appointmentForm').action = '../api/update-appointment.php';

                            let idInput = document.querySelector('input[name="appointment_id"]');
                            if (!idInput) {
                                idInput = document.createElement('input');
                                idInput.type = 'hidden';
                                idInput.name = 'appointment_id';
                                document.getElementById('appointmentForm').appendChild(idInput);
                            }
                            idInput.value = appointmentId;

                            isEditMode = true;

                            // Force-close first so the animation always replays cleanly
                            const modal = document.getElementById('appointmentModal');
                            const box = document.getElementById('appointmentModalBox');
                            modal.classList.add('hidden');
                            modal.classList.remove('flex');
                            box.classList.remove('apt-modal-animate');
                            void box.offsetWidth;

                            openAppointmentModal();
                        } else {
                            alert('Error loading appointment data');
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('Error loading appointment data');
                    });
            }

            function closeAppointmentModal() {
                const modal = document.getElementById('appointmentModal');
                modal.classList.add('hidden');
                modal.classList.remove('flex');
                document.body.style.overflow = '';
                document.getElementById('appointmentForm').reset();

                // Reset form action to create
                document.getElementById('appointmentForm').action = 'api/create-appointment.php';
                isEditMode = false;
            }

            // Close modal when clicking outside
            document.getElementById('appointmentModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAppointmentModal();
                }
            });

            // Validate end time is after start time
            document.getElementById('end_time').addEventListener('change', function() {
                const startTime = document.getElementById('start_time').value;
                const endTime = this.value;

                if (startTime && endTime && endTime <= startTime) {
                    alert('End time must be after start time');
                    this.value = '';
                }
            });

            document.getElementById('appointmentForm').addEventListener('submit', async function(e) {
                e.preventDefault();

                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent.trim();
                submitBtn.textContent = isEditMode ? 'Updating...' : 'Scheduling...';
                submitBtn.disabled = true;

                const formData = new FormData(this);

                try {
                    const response = await fetch(this.action, {
                        method: 'POST',
                        body: formData
                    });

                    const result = await response.json();

                    if (result.success) {
                        closeAppointmentModal();
                        // Store flash message in sessionStorage so it survives the reload
                        sessionStorage.setItem('flash_type', isEditMode ? 'success' : 'success');
                        sessionStorage.setItem('flash_message', isEditMode ?
                            'Appointment updated successfully!' :
                            'Provider visit scheduled successfully!');
                        location.reload();
                    } else {
                        closeAppointmentModal();
                        sessionStorage.setItem('flash_type', 'error');
                        sessionStorage.setItem('flash_message', result.message || 'Something went wrong. Please try again.');
                        location.reload();
                    }
                } catch (error) {
                    console.error('Error:', error);
                    closeAppointmentModal();
                    sessionStorage.setItem('flash_type', 'error');
                    sessionStorage.setItem('flash_message', 'An unexpected error occurred. Please try again.');
                    location.reload();
                } finally {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                }
            });

            // On page load, check sessionStorage for a pending flash and render it
            // using the same PHP Alert markup pattern
            (function() {
                const type = sessionStorage.getItem('flash_type');
                const message = sessionStorage.getItem('flash_message');
                if (!type || !message) return;
                sessionStorage.removeItem('flash_type');
                sessionStorage.removeItem('flash_message');

                const configs = {
                    success: {
                        bg: '#f0faf5',
                        border: '#a8dcc0',
                        accent: '#2d8a6e',
                        textTitle: '#1a4a38',
                        textBody: '#2c5a48',
                        label: 'Success',
                        iconPath: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
                    },
                    error: {
                        bg: '#fff5f5',
                        border: '#f5b8b8',
                        accent: '#c62828',
                        textTitle: '#7a1515',
                        textBody: '#a83232',
                        label: 'Error',
                        iconPath: 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z',
                    },
                    warning: {
                        bg: '#fffbf0',
                        border: '#f5d87a',
                        accent: '#c88a00',
                        textTitle: '#6b4a00',
                        textBody: '#8a6200',
                        label: 'Warning',
                        iconPath: 'M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    },
                    info: {
                        bg: '#f0f6ff',
                        border: '#b3cef5',
                        accent: '#2563eb',
                        textTitle: '#1a3a7a',
                        textBody: '#2c4fa8',
                        label: 'Info',
                        iconPath: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
                    },
                };

                const c = configs[type] || configs.info;
                const uid = 'alert-js-' + Math.random().toString(36).slice(2, 9);

                const el = document.createElement('div');
                el.id = uid;
                el.className = 'clinic-alert';
                el.style.cssText = [
                    'display:flex',
                    'align-items:flex-start',
                    'justify-content:space-between',
                    'gap:0.75rem',
                    'margin-bottom:1rem',
                    'padding:0.875rem 1rem',
                    'border-radius:0.625rem',
                    'border:1px solid ' + c.border,
                    'border-left:3.5px solid ' + c.accent,
                    'background:' + c.bg,
                    'box-shadow:0 2px 8px rgba(0,0,0,0.04),0 1px 2px rgba(0,0,0,0.04)',
                    "font-family:'DM Sans',system-ui,sans-serif",
                    'opacity:0',
                    'transform:translateY(-6px)',
                    'transition:opacity 0.25s ease,transform 0.25s ease',
                ].join(';');

                el.innerHTML = `
        <div style="display:flex;align-items:flex-start;gap:0.75rem;flex:1;min-width:0;">
            <div style="flex-shrink:0;width:2rem;height:2rem;border-radius:0.5rem;background:${c.accent}1a;display:flex;align-items:center;justify-content:center;margin-top:0.05rem;">
                <svg style="width:16px;height:16px;" fill="none" stroke="${c.accent}" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="${c.iconPath}"/>
                </svg>
            </div>
            <div style="flex:1;min-width:0;">
                <p style="font-size:0.72rem;font-weight:700;letter-spacing:0.05em;text-transform:uppercase;color:${c.textTitle};margin:0 0 0.2rem 0;line-height:1;">${c.label}</p>
                <p style="font-size:0.82rem;color:${c.textBody};margin:0;line-height:1.5;">${message}</p>
            </div>
        </div>
        <button onclick="(function(el){el.style.opacity='0';el.style.transform='translateY(-4px)';setTimeout(function(){el.remove()},250)})(this.closest('.clinic-alert'))"
            style="flex-shrink:0;margin-left:0.75rem;padding:0.25rem;border-radius:0.375rem;color:${c.accent};background:transparent;border:none;cursor:pointer;opacity:0.6;transition:opacity 0.15s ease,background 0.15s ease;"
            onmouseover="this.style.opacity='1';this.style.background='rgba(0,0,0,0.06)';"
            onmouseout="this.style.opacity='0.6';this.style.background='transparent';"
            aria-label="Dismiss">
            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
            </svg>
        </button>`;

                // Insert into the same spot Alert::displayFlash() renders — look for
                // the first <main> tag and prepend there, just like the PHP flash does.
                const main = document.querySelector('main');
                if (main) {
                    main.insertBefore(el, main.firstChild);
                } else {
                    document.body.prepend(el);
                }

                // Animate in (mirrors the PHP component's double-rAF trick)
                requestAnimationFrame(() => requestAnimationFrame(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }));

                // Auto-dismiss after 5s
                setTimeout(() => {
                    el.style.opacity = '0';
                    el.style.transform = 'translateY(-4px)';
                    setTimeout(() => el.remove(), 250);
                }, 5000);
            })();
        </script>
<?php
    }
}
?>