<?php
class Modal
{
    /**
     * Generate a modal HTML
     * 
     * @param string $id Modal ID
     * @param string $title Modal title
     * @param string $content Modal content (HTML)
     * @param string $size Modal size (sm, md, lg, xl)
     * @param string $submitText Text for submit button
     * @param string $submitClass CSS class for submit button
     * @return string Modal HTML
     */
    public static function render($id, $title, $content, $size = 'md', $submitText = 'Save Changes', $submitClass = '')
    {
        $sizeClasses = [
            'sm' => 'max-w-md',
            'md' => 'max-w-lg',
            'lg' => 'max-w-2xl',
            'xl' => 'max-w-4xl'
        ];

        $sizeClass = $sizeClasses[$size] ?? 'max-w-lg';

        $html = '
        <div id="' . $id . '" class="modal fixed inset-0 z-50 hidden items-center justify-center" style="background-color: rgba(0,0,0,0.5);">
            <div class="modal-content rounded-xl shadow-xl ' . $sizeClass . ' w-full mx-4 animate-slide-down" style="background:#ffffff; border:1px solid #d4e6f1;">

                <!-- Modal Header -->
                <div class="modal-header px-6 py-4 rounded-t-xl flex items-center justify-between" style="background:#e8f1f8; border-bottom:1px solid #d4e6f1;">
                    <div class="flex items-center gap-2">
                        <div style="width:1.75rem; height:1.75rem; border-radius:0.5rem; background:#e6f1fb; border:1px solid #b5d4f4; display:flex; align-items:center; justify-content:center; color:#1e5ba8; flex-shrink:0;">
                            <svg style="width:14px;height:14px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                        </div>
                        <h3 style="font-size:0.875rem; font-weight:700; letter-spacing:0.04em; text-transform:uppercase; color:#004b87; font-family:\'DM Sans\',system-ui,sans-serif;">
                            ' . htmlspecialchars($title) . '
                        </h3>
                    </div>
                    <button type="button" onclick="closeModal(\'' . $id . '\')"
                            style="color:#6b8fa7; background:none; border:none; cursor:pointer; padding:0.25rem; border-radius:0.375rem; transition:color 0.15s;"
                            onmouseover="this.style.color=\'#1e5ba8\';"
                            onmouseout="this.style.color=\'#6b8fa7\';">
                        <svg style="width:18px;height:18px;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <!-- Modal Body -->
                <div class="modal-body px-6 py-4 max-h-[70vh] overflow-y-auto">
                    ' . $content . '
                </div>

                <!-- Modal Footer -->
                <div class="modal-footer px-6 py-4 rounded-b-xl flex justify-end gap-3" style="background:#e8f1f8; border-top:1px solid #d4e6f1;">
                    <button type="button" onclick="closeModal(\'' . $id . '\')"
                            style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.575rem 1.1rem; border-radius:0.5rem; font-size:0.8rem; font-weight:600; font-family:\'DM Sans\',system-ui,sans-serif; background:#ffffff; color:#1a2e25; border:1px solid #d4e6f1; cursor:pointer; transition:all 0.18s ease;"
                            onmouseover="this.style.background=\'#e6f1fb\'; this.style.borderColor=\'#b5d4f4\';"
                            onmouseout="this.style.background=\'#ffffff\'; this.style.borderColor=\'#d4e6f1\';">
                        Cancel
                    </button>
                    <button type="submit" form="' . $id . '_form"
                            style="display:inline-flex; align-items:center; gap:0.4rem; padding:0.575rem 1.25rem; border-radius:0.5rem; font-size:0.8rem; font-weight:600; font-family:\'DM Sans\',system-ui,sans-serif; ' . ($submitClass ? '' : 'background:#1e5ba8;') . ' color:#ffffff; border:none; cursor:pointer; transition:all 0.18s ease; ' . ($submitClass ? 'class="' . $submitClass . '"' : '') . '"
                            onmouseover="this.style.background=\'#004b87\'; this.style.boxShadow=\'0 4px 14px rgba(30,91,168,0.3)\';"
                            onmouseout="this.style.background=\'#1e5ba8\'; this.style.boxShadow=\'none\';">
                        ' . htmlspecialchars($submitText) . '
                    </button>
                </div>
            </div>
        </div>';

        return $html;
    }

    /**
     * Generate a form modal for editing
     * 
     * @param string $id Modal ID
     * @param string $title Modal title
     * @param array $fields Array of form fields
     * @param string $action Form action URL
     * @param string $size Modal size
     * @return string Modal HTML
     */
    public static function renderFormModal($id, $title, $fields, $action = '', $size = 'lg')
    {
        $formContent = '<form id="' . $id . '_form" method="POST" action="' . htmlspecialchars($action) . '">';
        $formContent .= '<input type="hidden" name="action" value="update">';
        $formContent .= '<input type="hidden" name="id" id="' . $id . '_id">';

        $formContent .= '<div class="grid grid-cols-1 md:grid-cols-2 gap-4">';

        foreach ($fields as $field) {
            $formContent .= self::renderFormField($field);
        }

        $formContent .= '</div></form>';

        return self::render($id, $title, $formContent, $size);
    }

    /**
     * Render a single form field
     * 
     * @param array $field Field configuration
     * @return string HTML for form field
     */
    private static function renderFormField($field)
    {
        $type        = $field['type'] ?? 'text';
        $name        = $field['name'];
        $label       = $field['label'];
        $required    = $field['required'] ?? false;
        $placeholder = $field['placeholder'] ?? '';
        $options     = $field['options'] ?? [];
        $rows        = $field['rows'] ?? 3;

        $requiredStar = $required
            ? '<span style="color:#a32d2d; margin-left:0.2rem;">*</span>'
            : '';

        $labelStyle  = 'display:block; font-size:0.68rem; font-weight:700; text-transform:uppercase; letter-spacing:0.06em; color:#1a2e25; margin-bottom:0.4rem;';
        $inputBase   = 'width:100%; padding:0.575rem 0.875rem; border:1.5px solid #d4e6f1; border-radius:0.5rem; font-size:0.875rem; font-family:\'DM Sans\',system-ui,sans-serif; color:#1a2e25; background:#ffffff;';
        $focusOn     = "this.style.borderColor='#1e5ba8'; this.style.boxShadow='0 0 0 3px rgba(30,91,168,0.12)';";
        $focusOff    = "this.style.borderColor='#d4e6f1'; this.style.boxShadow='none';";

        $html  = '<div>';
        $html .= '<label style="' . $labelStyle . '">';
        $html .= htmlspecialchars($label) . $requiredStar;
        $html .= '</label>';

        if ($type === 'select') {
            $selectStyle = $inputBase . ' appearance:none; -webkit-appearance:none;'
                . ' background-image:url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'%231e5ba8\' stroke-width=\'2\'%3E%3Cpath stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M19 9l-7 7-7-7\'/%3E%3C/svg%3E");'
                . ' background-repeat:no-repeat; background-position:right 0.75rem center; background-size:0.9rem; padding-right:2.25rem; cursor:pointer;';

            $html .= '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) . '" '
                . ($required ? 'required' : '') . ' style="' . $selectStyle . '"'
                . ' onfocus="' . $focusOn . '" onblur="' . $focusOff . '">';
            $html .= '<option value="">Select ' . htmlspecialchars($label) . '</option>';
            foreach ($options as $value => $optionLabel) {
                $html .= '<option value="' . htmlspecialchars($value) . '">' . htmlspecialchars($optionLabel) . '</option>';
            }
            $html .= '</select>';
        } elseif ($type === 'textarea') {
            $html .= '<textarea name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) . '" rows="' . $rows . '"'
                . ' style="' . $inputBase . ' resize:vertical;"'
                . ' placeholder="' . htmlspecialchars($placeholder) . '"'
                . ($required ? ' required' : '')
                . ' onfocus="' . $focusOn . '" onblur="' . $focusOff . '"></textarea>';
        } else {
            $html .= '<input type="' . $type . '" name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) . '"'
                . ' style="' . $inputBase . '"'
                . ' placeholder="' . htmlspecialchars($placeholder) . '"'
                . ($required ? ' required' : '')
                . ' onfocus="' . $focusOn . '" onblur="' . $focusOff . '">';
        }

        if (isset($field['help'])) {
            $html .= '<p style="font-size:0.68rem; color:#8fa6ba; margin-top:0.3rem;">' . htmlspecialchars($field['help']) . '</p>';
        }

        $html .= '</div>';

        return $html;
    }
}
