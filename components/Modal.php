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
            <div class="modal-content bg-white rounded-xl shadow-xl ' . $sizeClass . ' w-full mx-4 animate-slide-down">
                <div class="modal-header px-6 py-4 border-b border-gray-100 bg-gray-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-lg font-semibold" style="color:#1a2e25;">' . htmlspecialchars($title) . '</h3>
                        </div>
                        <button type="button" onclick="closeModal(\'' . $id . '\')" 
                                class="text-gray-400 hover:text-gray-600 transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
                <div class="modal-body px-6 py-4 max-h-[70vh] overflow-y-auto">
                    ' . $content . '
                </div>
                <div class="modal-footer px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50 rounded-b-xl">
                    <button type="button" onclick="closeModal(\'' . $id . '\')" 
                            class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                        Cancel
                    </button>
                    <button type="submit" form="' . $id . '_form" 
                            class="px-4 py-2 text-sm rounded-lg text-white transition ' . ($submitClass ?: 'bg-[#2d8a6e] hover:bg-[#247a60]') . '">
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
        $type = $field['type'] ?? 'text';
        $name = $field['name'];
        $label = $field['label'];
        $required = $field['required'] ?? false;
        $placeholder = $field['placeholder'] ?? '';
        $options = $field['options'] ?? [];
        $rows = $field['rows'] ?? 3;

        $requiredStar = $required ? '<span class="text-red-500 ml-1">*</span>' : '';

        $html = '<div>';
        $html .= '<label class="form-label text-xs font-semibold uppercase tracking-wide" style="color:#2c4b3e;">';
        $html .= htmlspecialchars($label) . $requiredStar;
        $html .= '</label>';

        if ($type === 'select') {
            $html .= '<select name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) . '" ' . ($required ? 'required' : '') . ' 
                      class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]">';
            $html .= '<option value="">Select ' . htmlspecialchars($label) . '</option>';
            foreach ($options as $value => $optionLabel) {
                $html .= '<option value="' . htmlspecialchars($value) . '">' . htmlspecialchars($optionLabel) . '</option>';
            }
            $html .= '</select>';
        } elseif ($type === 'textarea') {
            $html .= '<textarea name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) . '" rows="' . $rows . '" 
                      class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]" 
                      placeholder="' . htmlspecialchars($placeholder) . '" ' . ($required ? 'required' : '') . '></textarea>';
        } else {
            $html .= '<input type="' . $type . '" name="' . htmlspecialchars($name) . '" id="' . htmlspecialchars($name) . '" 
                      class="form-input w-full px-3 py-2 border border-gray-300 rounded-lg text-sm focus:outline-none focus:border-[#2d8a6e]" 
                      placeholder="' . htmlspecialchars($placeholder) . '" ' . ($required ? 'required' : '') . '>';
        }

        if (isset($field['help'])) {
            $html .= '<p class="text-xs text-gray-500 mt-1">' . htmlspecialchars($field['help']) . '</p>';
        }

        $html .= '</div>';

        return $html;
    }
}
