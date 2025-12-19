/**
 * @file
 * Calculator logic for Zivi Spesen.
 */

(function ($, Drupal) {
  Drupal.behaviors.ziviSpesenCalculator = {
    attach: function (context, settings) {
      // Helper to parse date (YYYY-MM-DD).
      function parseDate(dateString) {
        if (!dateString) return null;
        const parts = dateString.split("-");
        return new Date(parts[0], parts[1] - 1, parts[2]);
      }

      // Helper to calculate days between two dates (inclusive).
      function calculateDays(start, end) {
        const oneDay = 24 * 60 * 60 * 1000;
        return Math.round(Math.abs((end - start) / oneDay)) + 1;
      }

      // Main update function.
      function updateCalculations() {
        // Date fields are in the header, so we must search globally, not just in context
        // because context might be an AJAX fragment (new row).
        const startVal = $(".expense-date-start").val();
        const endVal = $(".expense-date-end").val();

        if (!startVal || !endVal) return;

        const startDate = parseDate(startVal);
        const endDate = parseDate(endVal);

        if (endDate < startDate) return;

        const days = calculateDays(startDate, endDate);

        // Iterate over each "row" (which is now a grid container)
        // We can identify rows by looking for the quantity input
        $(".expense-quantity", context).each(function () {
          const $qtyInput = $(this);
          const $row = $qtyInput.closest(".grid"); // Tailwind grid container

          // Check if it's a standard row (has hidden type input)
          const $typeInput = $row.find(".expense-type");

          if ($typeInput.length) {
            // Standard Row: Update Quantity automatically
            // User requested: "always set the quantity to the length of the month"
            // So we set it to 'days' for ALL standard items.
            $qtyInput.val(days);

            // Update Total
            const rate = parseFloat($row.find(".expense-rate").val());
            const total = (days * rate).toFixed(2);
            $row.find(".expense-total").val(total);
          } else {
            // Custom Row: Just re-calculate total based on current inputs (don't auto-set qty)
            // Unless we want to? "Can we always set the quntity to the length of the month?"
            // Usually custom rows are for specific things like "Train Ticket" (qty 1).
            // So we should probably ONLY auto-update standard rows.

            const $rateInput = $row.find(".expense-rate-input");
            if ($rateInput.length) {
              const rate = parseFloat($rateInput.val()) || 0;
              const qty = parseFloat($qtyInput.val()) || 0;
              const total = (rate * qty).toFixed(2);
              $row.find(".expense-total").val(total);
            }
          }
        });

        calculateTotalSum();
      }

      function calculateTotalSum() {
        let globalTotal = 0;
        $(".expense-total", context).each(function () {
          const val = parseFloat($(this).val());
          if (!isNaN(val)) {
            globalTotal += val;
          }
        });
        $(".global-total-sum", context).val(globalTotal.toFixed(2));
      }

      // Attach listeners.
      $(".expense-date-start, .expense-date-end", context).on(
        "change",
        updateCalculations
      );

      // Listen for changes in inputs to update totals
      // Use event delegation or direct attach if elements exist
      $(context).on(
        "change keyup",
        ".expense-rate-input, .expense-quantity",
        function () {
          const $input = $(this);
          const $row = $input.closest(".grid");

          const rateInput = $row.find(".expense-rate-input");
          // Only for custom rows (standard rows are read-only/auto mostly, but if user edits qty manually?)
          // If it's a standard row, we have .expense-rate hidden.

          let rate = 0;
          if (rateInput.length) {
            rate = parseFloat(rateInput.val()) || 0;
          } else {
            rate = parseFloat($row.find(".expense-rate").val()) || 0;
          }

          const qty = parseFloat($row.find(".expense-quantity").val()) || 0;
          const total = (rate * qty).toFixed(2);
          $row.find(".expense-total").val(total);

          calculateTotalSum();
        }
      );

      // Run on load
      // We might need a slight delay or check if values are present
      if ($(".expense-date-start").val() && $(".expense-date-end").val()) {
        updateCalculations();
      }
    },
  };
})(jQuery, Drupal);
