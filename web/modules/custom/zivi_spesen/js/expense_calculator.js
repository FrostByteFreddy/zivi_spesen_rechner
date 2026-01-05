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
        const startVal = $(".expense-date-start").val();
        const endVal = $(".expense-date-end").val();

        if (!startVal || !endVal) return;

        const startDate = parseDate(startVal);
        const endDate = parseDate(endVal);

        if (endDate < startDate) return;

        const serviceStartVal = drupalSettings.zivi_spesen.service_start;
        const serviceEndVal = drupalSettings.zivi_spesen.service_end;

        const serviceStartDate = serviceStartVal
          ? parseDate(serviceStartVal)
          : null;
        const serviceEndDate = serviceEndVal ? parseDate(serviceEndVal) : null;

        // Iterate over each "row"
        $(".expense-quantity", context).each(function () {
          const $qtyInput = $(this);
          const $row = $qtyInput.closest(".grid");
          const $typeInput = $row.find(".expense-type");

          if ($typeInput.length) {
            const type = $typeInput.val(); // e.g., "Morgenessen"
            let allowedDays = 0;

            // Loop through each day in the range and check if the item is allowed
            let current = new Date(startDate);
            while (current <= endDate) {
              let isAllowed = true;

              // Check if day is within service period
              if (serviceStartDate && current < serviceStartDate) {
                isAllowed = false;
              }
              if (serviceEndDate && current > serviceEndDate) {
                isAllowed = false;
              }

              // Special exclusions on first/last day
              if (isAllowed) {
                if (
                  serviceStartDate &&
                  current.getTime() === serviceStartDate.getTime()
                ) {
                  // First day: No breakfast
                  if (type === "Morgenessen") isAllowed = false;
                }

                if (
                  serviceEndDate &&
                  current.getTime() === serviceEndDate.getTime()
                ) {
                  // Last day: No dinner
                  if (type === "Nachtessen") isAllowed = false;
                }
              }

              if (isAllowed) allowedDays++;
              current.setDate(current.getDate() + 1);
            }

            $qtyInput.val(allowedDays);

            // Update Total
            const rate = parseFloat($row.find(".expense-rate").val());
            const total = (allowedDays * rate).toFixed(2);
            $row.find(".expense-total").val(total);
          } else {
            // Custom Row: Just re-calculate total
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
