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
        const startVal = $(".expense-date-start", context).val();
        const endVal = $(".expense-date-end", context).val();

        if (!startVal || !endVal) return;

        const startDate = parseDate(startVal);
        const endDate = parseDate(endVal);

        if (endDate < startDate) return;

        const days = calculateDays(startDate, endDate);

        // Update Standard Rows
        // We iterate over rows that have a hidden .expense-type input
        $(".expense-table tr", context).each(function () {
          const $row = $(this);
          const type = $row.find(".expense-type").val();

          if (type) {
            // Standard Row
            let quantity = days;
            if (type === "Nachtessen") {
              quantity = Math.max(0, days - 1);
            }

            $row.find(".expense-quantity").val(quantity);

            const rate = parseFloat($row.find(".expense-rate").val());
            const total = (quantity * rate).toFixed(2);

            $row.find(".expense-total").val(total);
          } else {
            // Custom Row? Check if it has inputs
            const $rateInput = $row.find(".expense-rate-input");
            const $qtyInput = $row.find(".expense-quantity");
            const $totalInput = $row.find(".expense-total");

            if ($rateInput.length && $qtyInput.length) {
              // Calculate total for custom row based on user input
              const rate = parseFloat($rateInput.val()) || 0;
              const qty = parseFloat($qtyInput.val()) || 0;
              const total = (rate * qty).toFixed(2);
              $totalInput.val(total);
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

      // Listen for changes in custom rows to update totals
      $(".expense-rate-input, .expense-quantity", context).on(
        "change keyup",
        function () {
          // Update the specific row total first
          const $row = $(this).closest("tr");
          const rate = parseFloat($row.find(".expense-rate-input").val()) || 0;
          const qty = parseFloat($row.find(".expense-quantity").val()) || 0;
          const total = (rate * qty).toFixed(2);
          $row.find(".expense-total").val(total);

          calculateTotalSum();
        }
      );

      // Run on load
      updateCalculations();
    },
  };
})(jQuery, Drupal);
