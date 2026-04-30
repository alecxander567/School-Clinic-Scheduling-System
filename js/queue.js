/**
 * Queue Registration — queue.js
 * Handles the print button click (used in the success state).
 */
document.addEventListener("DOMContentLoaded", function () {
  var btnPrint = document.querySelector(".btn-print");
  if (btnPrint) {
    btnPrint.addEventListener("click", function () {
      window.print();
    });
  }
});
