<?php
class HealthRecordModal
{
    public static function renderAddModal($modalId = 'addHealthRecordModal', $students = [], $size = 'lg')
    {
        $sizeClass = self::getSizeClass($size);
        ob_start();
?>
        <div id="<?php echo $modalId; ?>"
            class="hr-modal-backdrop"
            style="display:none;"
            role="dialog"
            aria-modal="true"
            aria-labelledby="<?php echo $modalId; ?>-title">

            <div class="hr-modal <?php echo $sizeClass; ?> animate-modal">

                <!-- Header -->
                <div class="hr-modal-header">
                    <div class="hr-modal-header-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="hr-modal-title" id="<?php echo $modalId; ?>-title">Add Health Record</h3>
                        <p class="hr-modal-subtitle">Fill in the student's medical information below</p>
                    </div>
                    <button onclick="closeModal('<?php echo $modalId; ?>')" class="hr-modal-close" aria-label="Close modal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="hr-modal-divider"></div>

                <form method="POST" action="" id="healthRecordForm" class="hr-modal-body">

                    <div class="hr-field-group">
                        <label class="hr-modal-label" for="student_id">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            Student <span class="hr-required">*</span>
                        </label>
                        <div class="hr-select-wrapper">
                            <select name="student_id" id="student_id" class="hr-modal-select" required>
                                <option value="">Select a student…</option>
                                <?php foreach ($students as $student): ?>
                                    <option value="<?php echo $student['id']; ?>">
                                        <?php echo htmlspecialchars(
                                            $student['first_name'] . ' ' . $student['last_name'] .
                                                ' — ' . $student['student_number']
                                        ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <span class="hr-select-arrow">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                </svg>
                            </span>
                        </div>
                        <p class="hr-field-hint">Only students without an existing record are listed</p>
                    </div>

                    <div class="hr-textarea-grid">

                        <div class="hr-field-group">
                            <label class="hr-modal-label" for="allergies">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Allergies
                            </label>
                            <div class="hr-textarea-wrapper">
                                <textarea name="allergies" id="allergies" rows="4"
                                    class="hr-modal-textarea"
                                    placeholder="e.g. Penicillin, peanuts, latex…"></textarea>
                                <span class="hr-textarea-badge">Optional</span>
                            </div>
                            <p class="hr-field-hint">Food, medication, or environmental triggers</p>
                        </div>

                        <div class="hr-field-group">
                            <label class="hr-modal-label" for="medical_history">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Medical History
                            </label>
                            <div class="hr-textarea-wrapper">
                                <textarea name="medical_history" id="medical_history" rows="4"
                                    class="hr-modal-textarea"
                                    placeholder="e.g. Asthma diagnosed 2018, appendectomy 2020…"></textarea>
                                <span class="hr-textarea-badge">Optional</span>
                            </div>
                            <p class="hr-field-hint">Past illnesses, surgeries, chronic conditions</p>
                        </div>

                    </div>

                </form>

                <div class="hr-modal-divider"></div>

                <div class="hr-modal-footer">
                    <button type="button" onclick="closeModal('<?php echo $modalId; ?>')" class="hr-btn hr-btn-ghost">
                        Cancel
                    </button>
                    <button type="submit" form="healthRecordForm" class="hr-btn hr-btn-primary">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Record
                    </button>
                </div>

            </div>
        </div>
    <?php
        return ob_get_clean();
    }

    public static function renderEditModal($modalId = 'editHealthRecordModal', $size = 'lg')
    {
        $sizeClass = self::getSizeClass($size);
        ob_start();
    ?>
        <div id="<?php echo $modalId; ?>"
            class="hr-modal-backdrop"
            style="display:none;"
            role="dialog"
            aria-modal="true"
            aria-labelledby="<?php echo $modalId; ?>-title">

            <div class="hr-modal <?php echo $sizeClass; ?> animate-modal">

                <div class="hr-modal-header">
                    <div class="hr-modal-header-icon">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                        </svg>
                    </div>
                    <div class="min-w-0 flex-1">
                        <h3 class="hr-modal-title" id="<?php echo $modalId; ?>-title">Edit Health Record</h3>
                        <p class="hr-modal-subtitle">Update the student's medical information</p>
                    </div>
                    <button onclick="closeModal('<?php echo $modalId; ?>')" class="hr-modal-close" aria-label="Close modal">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="hr-modal-divider"></div>

                <form method="POST" action="" id="editHealthRecordForm" class="hr-modal-body">
                    <input type="hidden" name="_action" value="edit">
                    <input type="hidden" name="record_id" id="edit_record_id">
                    <input type="hidden" name="student_id" id="edit_student_id">

                    <div class="hr-identity-strip">
                        <div class="hr-identity-avatar" id="edit-avatar"></div>
                        <div class="min-w-0">
                            <p class="hr-identity-name" id="edit-student-name"></p>
                            <div class="hr-identity-meta" id="edit-student-meta"></div>
                        </div>
                    </div>

                    <div class="hr-textarea-grid">
                        <div class="hr-field-group">
                            <label class="hr-modal-label" for="edit_allergies">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Allergies
                            </label>
                            <div class="hr-textarea-wrapper">
                                <textarea name="allergies" id="edit_allergies" rows="4"
                                    class="hr-modal-textarea"
                                    placeholder="e.g. Penicillin, peanuts, latex…"></textarea>
                                <span class="hr-textarea-badge">Optional</span>
                            </div>
                            <p class="hr-field-hint">Food, medication, or environmental triggers</p>
                        </div>

                        <div class="hr-field-group">
                            <label class="hr-modal-label" for="edit_medical_history">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Medical History
                            </label>
                            <div class="hr-textarea-wrapper">
                                <textarea name="medical_history" id="edit_medical_history" rows="4"
                                    class="hr-modal-textarea"
                                    placeholder="e.g. Asthma diagnosed 2018, appendectomy 2020…"></textarea>
                                <span class="hr-textarea-badge">Optional</span>
                            </div>
                            <p class="hr-field-hint">Past illnesses, surgeries, chronic conditions</p>
                        </div>
                    </div>
                </form>

                <div class="hr-modal-divider"></div>

                <div class="hr-modal-footer">
                    <button type="button" onclick="closeModal('<?php echo $modalId; ?>')" class="hr-btn hr-btn-ghost">
                        Cancel
                    </button>
                    <button type="submit" form="editHealthRecordForm" class="hr-btn hr-btn-primary">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                        </svg>
                        Save Changes
                    </button>
                </div>

            </div>
        </div>
<?php
        return ob_get_clean();
    }

    public static function getModalStyles()
    {
        return '
        <style>
        .hr-modal-backdrop {
            position: fixed; inset: 0;
            background: rgba(15,23,18,0.55);
            backdrop-filter: blur(3px);
            -webkit-backdrop-filter: blur(3px);
            z-index: 1000;
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
        }
        .hr-modal {
            background: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 24px 64px rgba(0,0,0,0.14), 0 4px 16px rgba(0,0,0,0.08), 0 0 0 1px rgba(45,138,110,0.08);
            width: 100%; max-height: 90vh; overflow-y: auto;
            display: flex; flex-direction: column;
        }
        @keyframes hrModalIn {
            from { opacity:0; transform: translateY(-14px) scale(0.97); }
            to   { opacity:1; transform: translateY(0)      scale(1);   }
        }
        .animate-modal { animation: hrModalIn 0.25s cubic-bezier(0.34,1.56,0.64,1); }
        .max-w-md  { max-width: 28rem; }
        .max-w-lg  { max-width: 32rem; }
        .max-w-2xl { max-width: 42rem; }
        .max-w-4xl { max-width: 56rem; }
        .hr-modal-header {
            display: flex; align-items: flex-start; gap: 0.75rem;
            padding: 1.25rem 1.5rem;
        }
        .hr-modal-header-icon {
            flex-shrink: 0; width: 2.25rem; height: 2.25rem;
            border-radius: 0.6rem; background: #eaf5f0; border: 1px solid #c4e5d9;
            display: flex; align-items: center; justify-content: center; color: #2d8a6e;
        }
        .hr-modal-title   { font-size: 0.95rem; font-weight: 600; color: #1a2e25; margin: 0; line-height: 1.3; }
        .hr-modal-subtitle { font-size: 0.72rem; color: #7a9b8a; margin: 0.15rem 0 0; }
        .hr-modal-close {
            flex-shrink: 0; margin-left: auto; width: 2rem; height: 2rem;
            border-radius: 0.4rem; border: 1px solid #e4ede7; background: #f6faf7; color: #627a6e;
            display: flex; align-items: center; justify-content: center; cursor: pointer;
            transition: background 0.15s, color 0.15s, border-color 0.15s;
        }
        .hr-modal-close:hover { background: #fce8e8; border-color: #f0b8b8; color: #c0504d; }
        .hr-modal-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, #e4ede7 20%, #e4ede7 80%, transparent);
            flex-shrink: 0;
        }
        .hr-modal-body   { padding: 1.25rem 1.5rem; display: flex; flex-direction: column; gap: 1rem; flex: 1; }
        .hr-modal-footer { padding: 1rem 1.5rem; display: flex; justify-content: flex-end; gap: 0.5rem; }
        .hr-field-group  { display: flex; flex-direction: column; gap: 0.35rem; }
        .hr-modal-label  {
            display: inline-flex; align-items: center; gap: 0.3rem;
            font-size: 0.67rem; font-weight: 700; color: #2c4b3e;
            text-transform: uppercase; letter-spacing: 0.07em;
        }
        .hr-required { color: #c0504d; }
        .hr-field-hint { font-size: 0.68rem; color: #9ab8ab; margin: 0; }
        .hr-select-wrapper { position: relative; }
        .hr-modal-select {
            width: 100%; appearance: none; -webkit-appearance: none;
            padding: 0.55rem 2.5rem 0.55rem 0.75rem;
            border: 1.5px solid #e2ebe6; border-radius: 0.6rem;
            font-size: 0.82rem; font-family: "DM Sans", system-ui, sans-serif;
            color: #1f2e26; background: #f9fbf9; cursor: pointer;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .hr-modal-select:focus { outline:none; border-color:#2d8a6e; background:#fff; box-shadow:0 0 0 3px rgba(45,138,110,0.12); }
        .hr-select-arrow { position:absolute; right:0.75rem; top:50%; transform:translateY(-50%); color:#7a9b8a; pointer-events:none; }
        .hr-textarea-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        @media (max-width: 540px) { .hr-textarea-grid { grid-template-columns: 1fr; } }
        .hr-textarea-wrapper { position: relative; }
        .hr-modal-textarea {
            width: 100%; padding: 0.55rem 0.75rem;
            border: 1.5px solid #e2ebe6; border-radius: 0.6rem;
            font-size: 0.82rem; font-family: "DM Sans", system-ui, sans-serif;
            color: #1f2e26; background: #f9fbf9; resize: vertical; min-height: 6rem; line-height: 1.6;
            transition: border-color 0.2s, box-shadow 0.2s, background 0.2s;
        }
        .hr-modal-textarea:focus { outline:none; border-color:#2d8a6e; background:#fff; box-shadow:0 0 0 3px rgba(45,138,110,0.12); }
        .hr-modal-textarea::placeholder { color: #afc5bb; }
        .hr-textarea-badge {
            position:absolute; top:0.45rem; right:0.5rem;
            font-size:0.6rem; font-weight:600; letter-spacing:0.05em; text-transform:uppercase;
            background:#f0f7f3; color:#6baa8c; border:1px solid #c8dfd4; border-radius:9999px;
            padding:0.1rem 0.45rem; pointer-events:none;
        }
        .hr-identity-strip {
            display:flex; align-items:center; gap:0.875rem;
            background:#f3f9f6; border:1px solid #daeee5; border-radius:0.75rem; padding:0.875rem 1rem;
        }
        .hr-identity-avatar {
            flex-shrink:0; width:2.75rem; height:2.75rem; border-radius:0.75rem;
            background:#2d8a6e; color:#fff; font-size:0.85rem; font-weight:600;
            display:flex; align-items:center; justify-content:center; letter-spacing:0.02em;
        }
        .hr-identity-name { font-size:0.88rem; font-weight:600; color:#1a2e25; margin:0 0 0.3rem; }
        .hr-identity-meta { display:flex; flex-wrap:wrap; gap:0.35rem; }
        .hr-meta-pill {
            font-size:0.67rem; font-weight:600; color:#2d6b52;
            background:#e6f5ee; border:1px solid #b8ddd0; border-radius:9999px; padding:0.1rem 0.5rem;
        }
        .hr-view-section { border:1px solid #e8f0eb; border-radius:0.75rem; overflow:hidden; }
        .hr-view-section-header {
            display:flex; align-items:center; gap:0.4rem; padding:0.55rem 0.875rem;
            background:#f3f9f6; border-bottom:1px solid #e8f0eb;
            font-size:0.67rem; font-weight:700; text-transform:uppercase; letter-spacing:0.07em; color:#2d8a6e;
        }
        .hr-view-content {
            padding:0.75rem 0.875rem; font-size:0.82rem; color:#2c4b3e;
            line-height:1.65; min-height:3rem; white-space:pre-wrap;
        }
        .hr-view-content--empty { color:#9ab8ab; font-style:italic; }
        .hr-view-timestamp { font-size:0.68rem; color:#9ab8ab; display:flex; align-items:center; gap:0.3rem; margin:0; }
        .hr-btn {
            display:inline-flex; align-items:center; gap:0.35rem;
            padding:0.5rem 1.1rem; border-radius:0.55rem;
            font-size:0.8rem; font-weight:600; font-family:"DM Sans",system-ui,sans-serif;
            cursor:pointer; transition:all 0.18s ease; border:1.5px solid transparent; white-space:nowrap;
        }
        .hr-btn-primary { background:#2d8a6e; color:#fff; border-color:#2d8a6e; }
        .hr-btn-primary:hover { background:#236b56; border-color:#236b56; transform:translateY(-1px); box-shadow:0 4px 14px rgba(45,138,110,0.3); }
        .hr-btn-primary:active { transform:translateY(0); box-shadow:none; }
        .hr-btn-ghost { background:#f4f8f5; color:#456b5b; border-color:#d4e6dd; }
        .hr-btn-ghost:hover { background:#e8f2ec; border-color:#b8d8ca; }
        .hr-edit-btn {
            flex-shrink:0; width:1.75rem; height:1.75rem; border-radius:0.4rem;
            border:1px solid #c8dfd4; background:#f0f7f3; color:#2d8a6e;
            display:flex; align-items:center; justify-content:center; cursor:pointer;
            transition:background 0.15s, border-color 0.15s, color 0.15s;
        }
        .hr-edit-btn:hover { background:#d4eddf; border-color:#2d8a6e; }
        </style>';
    }

    public static function getModalScript()
    {
        return '
        <script>
        function openModal(modalId) {
            var modal = document.getElementById(modalId);
            if (!modal) return;
            modal.style.display = "flex";
            document.body.style.overflow = "hidden";
            var box = modal.querySelector(".hr-modal");
            if (box) { box.classList.remove("animate-modal"); void box.offsetWidth; box.classList.add("animate-modal"); }
        }
        function closeModal(modalId) {
            var modal = document.getElementById(modalId);
            if (!modal) return;
            modal.style.display = "none";
            document.body.style.overflow = "";
        }
        document.addEventListener("click", function(e) {
            if (e.target.classList.contains("hr-modal-backdrop")) {
                e.target.style.display = "none";
                document.body.style.overflow = "";
            }
        });
        document.addEventListener("keydown", function(e) {
            if (e.key !== "Escape") return;
            document.querySelectorAll(".hr-modal-backdrop").forEach(function(m) {
                if (m.style.display !== "none") { m.style.display = "none"; document.body.style.overflow = ""; }
            });
        });
        </script>';
    }

    private static function getSizeClass($size)
    {
        switch ($size) {
            case 'sm':
                return 'max-w-md';
            case 'lg':
                return 'max-w-2xl';
            case 'xl':
                return 'max-w-4xl';
            default:
                return 'max-w-lg';
        }
    }
}
?>