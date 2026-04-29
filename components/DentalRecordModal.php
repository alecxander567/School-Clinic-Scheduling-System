<?php
class DentalRecordModal
{
    // Render Add Modal
    public static function renderAddModal($modalId, $students, $commonProcedures, $size = 'lg')
    {
        $sizeClass = $size === 'lg' ? 'hr-modal--lg' : 'hr-modal--md';
        ob_start();
?>
        <div id="<?php echo $modalId; ?>" class="hr-modal-backdrop" style="display:none;" role="dialog" aria-modal="true">
            <div class="hr-modal <?php echo $sizeClass; ?> animate-modal">
                <form id="addDentalRecordForm" method="POST" action="">
                    <input type="hidden" name="_action" value="add">

                    <div class="hr-modal-header">
                        <div class="hr-modal-header-icon">
                            <svg class="hr-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.5a7.5 7.5 0 00-7.5 7.5c0 3.5 2.5 6.5 6 7.2V21h3v-1.8c3.5-0.7 6-3.7 6-7.2 0-4.15-3.35-7.5-7.5-7.5z M12 9a3 3 0 100 6 3 3 0 000-6z M9 20.5v-2.5h6v2.5H9z" />
                            </svg>
                        </div>
                        <div class="hr-modal-header-text">
                            <h3 class="hr-modal-title">Add Dental Record</h3>
                            <p class="hr-modal-subtitle">Record dental visit and procedures</p>
                        </div>
                        <button type="button" onclick="closeModal('<?php echo $modalId; ?>')" class="hr-modal-close" aria-label="Close">
                            <svg class="hr-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="hr-modal-divider"></div>

                    <div class="hr-modal-body">
                        <div class="hr-form-stack">

                            <div class="hr-form-group">
                                <label class="hr-form-label">Student <span class="hr-required">*</span></label>
                                <select name="student_id" required class="hr-form-select">
                                    <option value="">Select Student</option>
                                    <?php foreach ($students as $student): ?>
                                        <option value="<?php echo $student['id']; ?>">
                                            <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                            (<?php echo htmlspecialchars($student['student_number']); ?>)
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="hr-form-group">
                                <label class="hr-form-label">Visit Date <span class="hr-required">*</span></label>
                                <input type="datetime-local" name="visit_date" required
                                    value="<?php echo date('Y-m-d\TH:i'); ?>"
                                    class="hr-form-input">
                            </div>

                            <div class="hr-form-group">
                                <label class="hr-form-label">Diagnosis</label>
                                <textarea name="diagnosis" rows="3" class="hr-form-textarea"
                                    placeholder="e.g., Dental caries, Gingivitis, Tooth fracture..."></textarea>
                            </div>

                            <div class="hr-form-group">
                                <label class="hr-form-label">Treatment</label>
                                <textarea name="treatment" rows="3" class="hr-form-textarea"
                                    placeholder="e.g., Filling, Extraction, Root canal treatment..."></textarea>
                            </div>

                            <div class="hr-form-group">
                                <label class="hr-form-label">Additional Dental Procedures</label>
                                <div id="procedures-container">
                                    <div class="hr-procedure-row">
                                        <select name="procedures[0][name]" class="hr-form-select">
                                            <option value="">Select Procedure</option>
                                            <?php foreach ($commonProcedures as $procedure): ?>
                                                <option value="<?php echo htmlspecialchars($procedure); ?>">
                                                    <?php echo htmlspecialchars($procedure); ?>
                                                </option>
                                            <?php endforeach; ?>
                                            <option value="other">Other (specify below)</option>
                                        </select>
                                        <input type="text" name="procedures[0][description]"
                                            placeholder="Description / Notes"
                                            class="hr-form-input">
                                        <button type="button" onclick="removeProcedure(this)" class="hr-remove-btn" aria-label="Remove">&times;</button>
                                    </div>
                                </div>
                                <button type="button" onclick="addProcedure()" class="hr-add-procedure-btn">
                                    + Add Another Procedure
                                </button>
                            </div>

                        </div>
                    </div>

                    <div class="hr-modal-divider"></div>

                    <div class="hr-modal-footer">
                        <button type="button" onclick="closeModal('<?php echo $modalId; ?>')" class="hr-btn hr-btn-ghost">Cancel</button>
                        <button type="submit" class="hr-btn hr-btn-primary">Save Dental Record</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            var procedureCount = 1;
            var addCommonProcedures = <?php echo json_encode($commonProcedures); ?>;

            function buildProcedureOptions(selectedName) {
                var opts = '<option value="">Select Procedure</option>';
                addCommonProcedures.forEach(function(p) {
                    var sel = (p === selectedName) ? ' selected' : '';
                    opts += '<option value="' + p.replace(/"/g, '&quot;') + '"' + sel + '>' + p.replace(/</g, '&lt;') + '</option>';
                });
                opts += '<option value="other"' + (selectedName === 'other' ? ' selected' : '') + '>Other (specify below)</option>';
                return opts;
            }

            function addProcedure() {
                var container = document.getElementById('procedures-container');
                var row = document.createElement('div');
                row.className = 'hr-procedure-row';
                row.innerHTML =
                    '<select name="procedures[' + procedureCount + '][name]" class="hr-form-select">' +
                    buildProcedureOptions('') +
                    '</select>' +
                    '<input type="text" name="procedures[' + procedureCount + '][description]" placeholder="Description / Notes" class="hr-form-input">' +
                    '<button type="button" onclick="removeProcedure(this)" class="hr-remove-btn" aria-label="Remove">&times;</button>';
                container.appendChild(row);
                procedureCount++;
            }

            function removeProcedure(btn) {
                btn.closest('.hr-procedure-row').remove();
            }
        </script>
    <?php
        return ob_get_clean();
    }

    // Render Edit Modal
    public static function renderEditModal($modalId, $commonProcedures, $size = 'lg')
    {
        $sizeClass = $size === 'lg' ? 'hr-modal--lg' : 'hr-modal--md';
        ob_start();
    ?>
        <div id="<?php echo $modalId; ?>" class="hr-modal-backdrop" style="display:none;" role="dialog" aria-modal="true">
            <div class="hr-modal <?php echo $sizeClass; ?> animate-modal">
                <form id="editDentalRecordForm" method="POST" action="">
                    <input type="hidden" name="_action" value="edit">
                    <input type="hidden" name="visit_id" id="edit_visit_id">

                    <div class="hr-modal-header">
                        <div class="hr-modal-header-icon">
                            <svg class="hr-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z" />
                            </svg>
                        </div>
                        <div class="hr-modal-header-text">
                            <h3 class="hr-modal-title">Edit Dental Record</h3>
                            <p class="hr-modal-subtitle" id="edit-subtitle">Update dental visit information</p>
                        </div>
                        <button type="button" onclick="closeModal('<?php echo $modalId; ?>')" class="hr-modal-close" aria-label="Close">
                            <svg class="hr-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="hr-modal-divider"></div>

                    <div class="hr-modal-body">
                        <div class="hr-form-stack">

                            <div class="hr-form-group">
                                <label class="hr-form-label">Visit Date <span class="hr-required">*</span></label>
                                <input type="datetime-local" name="visit_date" id="edit_visit_date" required class="hr-form-input">
                            </div>

                            <div class="hr-form-group">
                                <label class="hr-form-label">Diagnosis</label>
                                <textarea name="diagnosis" id="edit_diagnosis" rows="3" class="hr-form-textarea"></textarea>
                            </div>

                            <div class="hr-form-group">
                                <label class="hr-form-label">Treatment</label>
                                <textarea name="treatment" id="edit_treatment" rows="3" class="hr-form-textarea"></textarea>
                            </div>

                            <div class="hr-form-group">
                                <label class="hr-form-label">Additional Dental Procedures</label>
                                <div id="edit-procedures-container"></div>
                                <button type="button" onclick="addEditProcedure()" class="hr-add-procedure-btn">
                                    + Add Another Procedure
                                </button>
                            </div>

                        </div>
                    </div>

                    <div class="hr-modal-divider"></div>

                    <div class="hr-modal-footer">
                        <button type="button" onclick="closeModal('<?php echo $modalId; ?>')" class="hr-btn hr-btn-ghost">Cancel</button>
                        <button type="submit" class="hr-btn hr-btn-primary">Update Record</button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            var editProcedureCount = 0;
            var editCommonProcedures = <?php echo json_encode($commonProcedures); ?>;

            function buildEditProcedureOptions(selectedName) {
                var opts = '<option value="">Select Procedure</option>';
                editCommonProcedures.forEach(function(p) {
                    var sel = (p === selectedName) ? ' selected' : '';
                    opts += '<option value="' + p.replace(/"/g, '&quot;') + '"' + sel + '>' + p.replace(/</g, '&lt;') + '</option>';
                });
                opts += '<option value="other"' + (selectedName === 'other' ? ' selected' : '') + '>Other (specify below)</option>';
                return opts;
            }

            function addEditProcedure(name, description) {
                name = name || '';
                description = description || '';
                var container = document.getElementById('edit-procedures-container');
                var row = document.createElement('div');
                row.className = 'hr-procedure-row';
                row.innerHTML =
                    '<select name="procedures[' + editProcedureCount + '][name]" class="hr-form-select">' +
                    buildEditProcedureOptions(name) +
                    '</select>' +
                    '<input type="text" name="procedures[' + editProcedureCount + '][description]"' +
                    ' value="' + description.replace(/"/g, '&quot;') + '"' +
                    ' placeholder="Description / Notes" class="hr-form-input">' +
                    '<button type="button" onclick="removeEditProcedure(this)" class="hr-remove-btn" aria-label="Remove">&times;</button>';
                container.appendChild(row);
                editProcedureCount++;
            }

            function removeEditProcedure(btn) {
                btn.closest('.hr-procedure-row').remove();
            }

            function openEditModal(record) {
                document.getElementById('edit_visit_id').value = record.visit_id;
                document.getElementById('edit_visit_date').value = (record.visit_date || '').replace(' ', 'T');
                document.getElementById('edit_diagnosis').value = record.diagnosis || '';
                document.getElementById('edit_treatment').value = record.treatment || '';

                var subtitle = document.getElementById('edit-subtitle');
                if (subtitle) subtitle.textContent = 'Editing record for ' + (record.first_name || '') + ' ' + (record.last_name || '');

                var container = document.getElementById('edit-procedures-container');
                container.innerHTML = '';
                editProcedureCount = 0;

                if (record.procedure_names) {
                    var names = record.procedure_names.split('||');
                    var descs = record.procedure_descriptions ? record.procedure_descriptions.split('||') : [];
                    names.forEach(function(name, i) {
                        if (name) addEditProcedure(name, descs[i] || '');
                    });
                }

                openModal('editDentalRecordModal');
            }

            function openViewModal(record) {
                var modal = document.getElementById('viewDentalRecordModal');
                if (!modal) return;

                var first = record.first_name || '';
                var last = record.last_name || '';

                document.getElementById('view-name').textContent = first + ' ' + last;
                document.getElementById('view-avatar').textContent = (first.charAt(0) + last.charAt(0)).toUpperCase();
                document.getElementById('view-meta').textContent =
                    (record.student_number || '') + ' \u2022 ' + (record.course || 'N/A') + ' \u2022 Year ' + (record.year_level || 'N/A');

                document.getElementById('view-visit-date').textContent = formatDate(record.visit_date);
                document.getElementById('view-diagnosis').textContent = record.diagnosis || 'No diagnosis recorded';
                document.getElementById('view-treatment').textContent = record.treatment || 'No treatment recorded';
                document.getElementById('view-procedures').textContent = record.procedures || 'No additional procedures recorded';

                openModal('viewDentalRecordModal');
            }

            function formatDate(dateString) {
                if (!dateString) return 'N/A';
                var date = new Date(dateString);
                return date.toLocaleDateString('en-US', {
                    year: 'numeric',
                    month: 'short',
                    day: 'numeric',
                    hour: '2-digit',
                    minute: '2-digit'
                });
            }

            function deleteRecord(id, studentName) {
                if (confirm('Are you sure you want to delete the dental record for ' + studentName + '?')) {
                    window.location.href = 'dental-records.php?action=delete&id=' + id;
                }
            }
        </script>
<?php
        return ob_get_clean();
    }

    // All inline styles moved to dental-records.css — this now returns empty string.
    // Kept for backward compatibility so existing echo calls don't break.
    public static function getModalStyles()
    {
        return '';
    }

    // openModal / closeModal now live in dental-records.js.
    // Kept for backward compatibility.
    public static function getModalScript()
    {
        return '';
    }
}
?>