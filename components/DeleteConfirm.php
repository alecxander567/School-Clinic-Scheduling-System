<?php
class DeleteConfirm
{
    /**
     * Generate a delete confirmation modal
     * 
     * @param string $id Modal ID
     * @param string $itemType Type of item being deleted (e.g., 'student', 'appointment')
     * @param string $deleteUrl URL to send delete request
     * @return string Modal HTML
     */
    public static function render($id = 'deleteConfirmModal', $itemType = 'item', $deleteUrl = '')
    {
        return '
    <style>
        @keyframes aptSlideDown {
            from { opacity: 0; transform: translateY(-40px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0)      scale(1);   }
        }
        .apt-modal-animate {
            animation: aptSlideDown 0.28s cubic-bezier(0.34, 1.56, 0.64, 1);
        }
    </style>

    <div id="' . $id . '" class="modal fixed inset-0 z-50 items-center justify-center" style="display:none; background-color: rgba(0,0,0,0.5);">
        <div class="modal-content bg-white rounded-xl shadow-xl max-w-md w-full mx-4">
            <div class="modal-header px-6 py-4 border-b border-gray-100 bg-red-50 rounded-t-xl">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-red-100 flex items-center justify-center">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900">Confirm Deletion</h3>
                </div>
            </div>
            <div class="modal-body px-6 py-4">
                <p class="text-sm text-gray-600" id="deleteConfirmMessage">
                    Are you sure you want to delete this ' . htmlspecialchars($itemType) . '?
                </p>
                <p class="text-xs text-red-500 mt-2">This action cannot be undone.</p>
            </div>
            <div class="modal-footer px-6 py-4 border-t border-gray-100 flex justify-end gap-3 bg-gray-50 rounded-b-xl">
                <button type="button" onclick="closeModal(\'' . $id . '\')"
                        class="px-4 py-2 text-sm rounded-lg border border-gray-300 hover:bg-gray-50 transition">
                    Cancel
                </button>
                <button type="button" id="confirmDeleteBtn"
                        class="px-4 py-2 text-sm rounded-lg text-white transition bg-red-600 hover:bg-red-700">
                    Delete
                </button>
            </div>
        </div>
    </div>';
    }

    /**
     * Generate a JavaScript function to handle delete confirmation
     * 
     * @return string JavaScript code
     */
    public static function getScript()
    {
        return '
<script>
if (typeof closeModal === "undefined") {
    function closeModal(id) {
        var el = document.getElementById(id);
        if (el) { el.style.display = "none"; document.body.style.overflow = ""; }
    }
}

let deleteCallback = null;

function confirmDeleteWithCallback(itemId, itemName, callback) {
    const modal = document.getElementById("deleteConfirmModal");
    const messageEl = document.getElementById("deleteConfirmMessage");

    if (modal && messageEl) {
        messageEl.innerHTML = `Are you sure you want to delete <strong style="color:#1a2e25;">${escapeHtml(itemName)}</strong>?<br><span style="font-size:0.72rem; color:#c0504d; margin-top:0.25rem; display:block;">This action cannot be undone.</span>`;

        deleteCallback = function() {
            callback(itemId);
            closeModal("deleteConfirmModal");
        };

        modal.style.display = "flex";
        document.body.style.overflow = "hidden";

        const box = modal.querySelector(".modal-content");
        if (box) {
            box.classList.remove("apt-modal-animate");
            void box.offsetWidth;
            box.classList.add("apt-modal-animate");
        }

        const confirmBtn = document.getElementById("confirmDeleteBtn");
        if (confirmBtn) {
            const newBtn = confirmBtn.cloneNode(true);
            confirmBtn.parentNode.replaceChild(newBtn, confirmBtn);
            newBtn.addEventListener("click", function() {
                if (deleteCallback) {
                    deleteCallback();
                    deleteCallback = null;
                }
            });
        }
    }
}

function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}
</script>
    ';
    }
}
