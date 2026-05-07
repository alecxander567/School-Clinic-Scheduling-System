<?php
class AppointmentModal
{
    public static function render($controller, $currentUserId = null, $editAppointment = null)
    {
        $services  = $controller->getServices();
        $statuses  = $controller->getStatuses();
        $providers = $controller->getProviders();

        // Determine if we're in edit mode
        $isEdit      = $editAppointment !== null;
        $modalTitle  = $isEdit ? 'Edit Appointment' : 'Schedule Provider Visit';
        $submitText  = $isEdit ? 'Update Visit' : 'Schedule Visit';
        $actionUrl   = $isEdit ? '../api/update-appointment.php' : '../api/create-appointment.php';

        // Get appointment data for edit mode
        $appointmentData = $isEdit ? $editAppointment : null;
?>
        <div id="appointmentModal" class="fixed inset-0 z-50 hidden items-center justify-center" style="background-color: rgba(0,0,0,0.5);">
            <div id="appointmentModalBox" class="rounded-xl shadow-xl max-w-2xl w-full mx-4" style="background:#ffffff; border:1px solid #d4e6f1;">

                <!-- Modal header -->
                <div class="px-6 py-4 rounded-t-xl flex items-center justify-between" style="background:#e8f1f8; border-bottom:1px solid #d4e6f1;">
                    <div class="flex items-center gap-2">
                        <div style="width:1.75rem; height:1.75rem; border-radius:0.5rem; background:#e6f1fb; border:1px solid #b5d4f4; display:flex; align-items:center; justify-content:center; color:#1e5ba8; flex-shrink:0;">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <h3 style="font-size:0.875rem; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#004b87; font-family:'DM Sans',system-ui,sans-serif;">
                            <?php echo $modalTitle; ?>
                        </h3>
                    </div>
                    <button type="button" onclick="closeAppointmentModal()" style="color:#6b8fa7; background:none; border:none; cursor:pointer; padding:0.25rem; border-radius:0.375rem; transition:color 0.15s;" onmouseover="this.style.color='#1e5ba8';" onmouseout="this.style.color='#6b8fa7';">
                        <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <form id="appointmentForm" method="POST" action="<?php echo $actionUrl; ?>">
                    <div class="px-6 py-4 max-h-[70vh] overflow-y-auto">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <?php if ($isEdit): ?>
                                <input type="hidden" name="appointment_id" value="<?php echo $appointmentData['id']; ?>">
                            <?php endif; ?>

                            <!-- Provider Selection -->
                            <div class="md:col-span-2">
                                <label style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#1a2e25; margin-bottom:0.4rem;">
                                    Healthcare Provider <span style="color:#a32d2d; margin-left:0.2rem;">*</span>
                                </label>
                                <select name="provider_id" id="provider_id" required
                                    style="width:100%; padding:0.575rem 0.875rem; border:1.5px solid #d4e6f1; border-radius:0.5rem; font-size:0.875rem; font-family:'DM Sans',system-ui,sans-serif; color:#1a2e25; background:#ffffff; appearance:none; -webkit-appearance:none; background-image:url(\" data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%231e5ba8' stroke-width='2' %3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:0.9rem; padding-right:2.25rem; cursor:pointer;"
                                    onfocus="this.style.borderColor='#1e5ba8'; this.style.boxShadow='0 0 0 3px rgba(30,91,168,0.12)';"
                                    onblur="this.style.borderColor='#d4e6f1'; this.style.boxShadow='none';">
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
                                <label style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#1a2e25; margin-bottom:0.4rem;">
                                    Service Type <span style="color:#a32d2d; margin-left:0.2rem;">*</span>
                                </label>
                                <select name="service_id" id="service_id" required
                                    style="width:100%; padding:0.575rem 0.875rem; border:1.5px solid #d4e6f1; border-radius:0.5rem; font-size:0.875rem; font-family:'DM Sans',system-ui,sans-serif; color:#1a2e25; background:#ffffff; appearance:none; -webkit-appearance:none; background-image:url(\" data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%231e5ba8' stroke-width='2' %3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:0.9rem; padding-right:2.25rem; cursor:pointer;"
                                    onfocus="this.style.borderColor='#1e5ba8'; this.style.boxShadow='0 0 0 3px rgba(30,91,168,0.12)';"
                                    onblur="this.style.borderColor='#d4e6f1'; this.style.boxShadow='none';">
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
                                <label style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#1a2e25; margin-bottom:0.4rem;">
                                    Status <span style="color:#a32d2d; margin-left:0.2rem;">*</span>
                                </label>
                                <select name="status_id" id="status_id" required
                                    style="width:100%; padding:0.575rem 0.875rem; border:1.5px solid #d4e6f1; border-radius:0.5rem; font-size:0.875rem; font-family:'DM Sans',system-ui,sans-serif; color:#1a2e25; background:#ffffff; appearance:none; -webkit-appearance:none; background-image:url(\" data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 24 24' stroke='%231e5ba8' stroke-width='2' %3E%3Cpath stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7' /%3E%3C/svg%3E\"); background-repeat:no-repeat; background-position:right 0.75rem center; background-size:0.9rem; padding-right:2.25rem; cursor:pointer;"
                                    onfocus="this.style.borderColor='#1e5ba8'; this.style.boxShadow='0 0 0 3px rgba(30,91,168,0.12)';"
                                    onblur="this.style.borderColor='#d4e6f1'; this.style.boxShadow='none';">
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
                                <label style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#1a2e25; margin-bottom:0.4rem;">
                                    Visit Date <span style="color:#a32d2d; margin-left:0.2rem;">*</span>
                                </label>
                                <input type="date" name="visit_date" id="visit_date" required
                                    min="<?php echo date('Y-m-d'); ?>"
                                    value="<?php echo $isEdit ? $appointmentData['visit_date'] : ''; ?>"
                                    style="width:100%; padding:0.575rem 0.875rem; border:1.5px solid #d4e6f1; border-radius:0.5rem; font-size:0.875rem; font-family:'DM Sans',system-ui,sans-serif; color:#1a2e25; background:#ffffff;"
                                    onfocus="this.style.borderColor='#1e5ba8'; this.style.boxShadow='0 0 0 3px rgba(30,91,168,0.12)';"
                                    onblur="this.style.borderColor='#d4e6f1'; this.style.boxShadow='none';">
                            </div>

                            <!-- Start Time -->
                            <div>
                                <label style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#1a2e25; margin-bottom:0.4rem;">
                                    Start Time <span style="color:#a32d2d; margin-left:0.2rem;">*</span>
                                </label>
                                <input type="time" name="start_time" id="start_time" required
                                    value="<?php echo $isEdit ? $appointmentData['start_time'] : ''; ?>"
                                    style="width:100%; padding:0.575rem 0.875rem; border:1.5px solid #d4e6f1; border-radius:0.5rem; font-size:0.875rem; font-family:'DM Sans',system-ui,sans-serif; color:#1a2e25; background:#ffffff;"
                                    onfocus="this.style.borderColor='#1e5ba8'; this.style.boxShadow='0 0 0 3px rgba(30,91,168,0.12)';"
                                    onblur="this.style.borderColor='#d4e6f1'; this.style.boxShadow='none';">
                            </div>

                            <!-- End Time -->
                            <div>
                                <label style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#1a2e25; margin-bottom:0.4rem;">
                                    End Time <span style="color:#a32d2d; margin-left:0.2rem;">*</span>
                                </label>
                                <input type="time" name="end_time" id="end_time" required
                                    value="<?php echo $isEdit ? $appointmentData['end_time'] : ''; ?>"
                                    style="width:100%; padding:0.575rem 0.875rem; border:1.5px solid #d4e6f1; border-radius:0.5rem; font-size:0.875rem; font-family:'DM Sans',system-ui,sans-serif; color:#1a2e25; background:#ffffff;"
                                    onfocus="this.style.borderColor='#1e5ba8'; this.style.boxShadow='0 0 0 3px rgba(30,91,168,0.12)';"
                                    onblur="this.style.borderColor='#d4e6f1'; this.style.boxShadow='none';">
                            </div>

                            <!-- Maximum Students -->
                            <div>
                                <label style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#1a2e25; margin-bottom:0.4rem;">
                                    Maximum Students <span style="color:#a32d2d; margin-left:0.2rem;">*</span>
                                </label>
                                <input type="number" name="max_students" id="max_students" required
                                    placeholder="Max students for this visit"
                                    min="1" max="100"
                                    value="<?php echo $isEdit ? $appointmentData['max_students'] : '20'; ?>"
                                    style="width:100%; padding:0.575rem 0.875rem; border:1.5px solid #d4e6f1; border-radius:0.5rem; font-size:0.875rem; font-family:'DM Sans',system-ui,sans-serif; color:#1a2e25; background:#ffffff;"
                                    onfocus="this.style.borderColor='#1e5ba8'; this.style.boxShadow='0 0 0 3px rgba(30,91,168,0.12)';"
                                    onblur="this.style.borderColor='#d4e6f1'; this.style.boxShadow='none';">
                                <p style="font-size:0.68rem; color:#8fa6ba; margin-top:0.3rem;">Number of students that can sign up</p>
                            </div>

                            <!-- Notes -->
                            <div class="md:col-span-2">
                                <label style="display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#1a2e25; margin-bottom:0.4rem;">
                                    Special Instructions / Notes
                                </label>
                                <textarea name="notes" id="notes" rows="3"
                                    placeholder="Any special requirements or instructions for this visit..."
                                    style="width:100%; padding:0.575rem 0.875rem; border:1.5px solid #d4e6f1; border-radius:0.5rem; font-size:0.875rem; font-family:'DM Sans',system-ui,sans-serif; color:#1a2e25; background:#ffffff; resize:vertical;"
                                    onfocus="this.style.borderColor='#1e5ba8'; this.style.boxShadow='0 0 0 3px rgba(30,91,168,0.12)';"
                                    onblur="this.style.borderColor='#d4e6f1'; this.style.boxShadow='none';"><?php echo $isEdit ? htmlspecialchars($appointmentData['notes']) : ''; ?></textarea>
                            </div>

                            <!-- Hidden fields -->
                            <input type="hidden" name="requester_id" value="<?php echo $currentUserId; ?>">
                        </div>
                    </div>

                    <!-- Modal footer -->
                    <div class="px-6 py-4 rounded-b-xl flex justify-end gap-3" style="background:#e8f1f8; border-top:1px solid #d4e6f1;">
                        <button type="button" onclick="closeAppointmentModal()"
                            style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.575rem 1.1rem; border-radius:0.5rem; font-size:0.8rem; font-weight:600; font-family:'DM Sans',system-ui,sans-serif; background:#ffffff; color:#1a2e25; border:1px solid #d4e6f1; cursor:pointer; transition:all 0.18s ease;"
                            onmouseover="this.style.background='#e6f1fb'; this.style.borderColor='#b5d4f4';"
                            onmouseout="this.style.background='#ffffff'; this.style.borderColor='#d4e6f1';">
                            Cancel
                        </button>
                        <button type="submit"
                            style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.575rem 1.25rem; border-radius:0.5rem; font-size:0.8rem; font-weight:600; font-family:'DM Sans',system-ui,sans-serif; background:#1e5ba8; color:#ffffff; border:none; cursor:pointer; transition:all 0.18s ease;"
                            onmouseover="this.style.background='#004b87'; this.style.boxShadow='0 4px 14px rgba(30,91,168,0.3)';"
                            onmouseout="this.style.background='#1e5ba8'; this.style.boxShadow='none';">
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

                document.getElementById('appointmentForm').action = 'api/create-appointment.php';
                isEditMode = false;
            }

            document.getElementById('appointmentModal').addEventListener('click', function(e) {
                if (e.target === this) {
                    closeAppointmentModal();
                }
            });

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
                        sessionStorage.setItem('flash_type', 'success');
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

            // On page load, check sessionStorage for a pending flash
            (function() {
                const type = sessionStorage.getItem('flash_type');
                const message = sessionStorage.getItem('flash_message');
                if (!type || !message) return;
                sessionStorage.removeItem('flash_type');
                sessionStorage.removeItem('flash_message');

                const configs = {
                    success: {
                        bg: '#f0f6ff',
                        border: '#b5d4f4',
                        accent: '#1e5ba8',
                        textTitle: '#0c447c',
                        textBody: '#185fa5',
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
                        accent: '#1e5ba8',
                        textTitle: '#0c447c',
                        textBody: '#185fa5',
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

                const main = document.querySelector('main');
                if (main) {
                    main.insertBefore(el, main.firstChild);
                } else {
                    document.body.prepend(el);
                }

                requestAnimationFrame(() => requestAnimationFrame(() => {
                    el.style.opacity = '1';
                    el.style.transform = 'translateY(0)';
                }));

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