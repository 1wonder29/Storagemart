// public/assets/js/ticket/fetch_ticket_history.js
// Requires: jQuery, DataTables
// Must be loaded AFTER jQuery and DataTables JS and AFTER `const base = "...";` is defined.

(function () {
  // small helper to avoid inserting raw HTML
  function escapeHtml(text) {
    if (text === null || text === undefined) return "";
    return String(text)
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
  }

  $(document).ready(function () {
    // IT My Tickets page manages its own DataTable init (it-my-tickets.js)
    var $itMyTicketsTable = $(".it-ticket-page #ticketsTable");
    if (!$itMyTicketsTable.length) {
      try {
        if (!$.fn.DataTable.isDataTable("#ticketsTable")) {
          $("#ticketsTable").DataTable({
            responsive: true,
            pageLength: 10,
          });
        }
      } catch (err) {
        console.error("DataTable init error:", err);
      }
    }

    // Detect which module we're in to construct the correct fetch URL
    let fetchUrl = (window.BASE_URL || (typeof base !== 'undefined' ? base : '')).replace(/\/$/, '') + "/employee/tickets/history/fetch"; // default
    if (window.location.pathname.includes("/it/")) {
      fetchUrl = (window.BASE_URL || base).replace(/\/$/, '') + "/it/tickets/history/fetch";
    } else if (window.location.pathname.includes("/head/")) {
      fetchUrl = (window.BASE_URL || base).replace(/\/$/, '') + "/head/tickets/history/fetch";
    } else if (window.location.pathname.includes("/hr/")) {
      fetchUrl = (window.BASE_URL || base).replace(/\/$/, '') + "/hr/tickets/history/fetch";
    } else if (window.location.pathname.includes("/admin/")) {
      fetchUrl = (window.BASE_URL || base).replace(/\/$/, '') + "/admin/tickets/history";
    }

    // Attach click handler for "View" buttons (delegated in case rows are replaced)
    $(document).on("click", ".viewBtn", function () {
      const id = $(this).data("ticketid");

      $("#ticket_number").val($(this).data("ticketnum") || "");
      $("#employee").val($(this).data("employee") || "");
      $("#priority").val($(this).data("priority") || "");
      $("#status").val($(this).data("status") || "");

      const detailBase = (window.BASE_URL || (typeof base !== "undefined" ? base : "")).replace(/\/$/, "");
      $("#viewFullDetailLink").attr("href", detailBase + "/it/tickets/view?id=" + id);

      // CLEAR history table (not the main tickets table)
      $("#ticketHistoryTable tbody").empty();

      // fetch history JSON (expects JSON array)
      $.getJSON(fetchUrl, { ticket_id: id })
        .done(function (data) {
          if (Array.isArray(data) && data.length > 0) {
            data.forEach((row) => {
              $("#ticketHistoryTable tbody").append(`
                                <tr>
                                    <td>${escapeHtml(row.action_details)}</td>
                                    <td>${escapeHtml(row.performed_by)}</td>
                                    <td>${escapeHtml(row.old_status || "")}</td>
                                    <td>${escapeHtml(row.new_status || "")}</td>
                                    <td>${escapeHtml(row.date_logged || "")}</td>
                                </tr>
                            `);
            });
          } else {
            $("#ticketHistoryTable tbody").append(
              `<tr><td colspan="5" class="text-center">No history found.</td></tr>`,
            );
          }
        })
        .fail(function (jqxhr, textStatus, error) {
          console.error("Failed to fetch ticket history:", textStatus, error);
          $("#ticketHistoryTable tbody").append(
            `<tr><td colspan="5" class="text-center text-danger">Failed to load history.</td></tr>`,
          );
        });

      if (window.TicketComments) {
        TicketComments.load("#viewTicketModal .ticket-comments-section", id);
      }

      $("#viewTicketModal").modal("show");
    });
  });
})();
// ==============================
// Notification click → load modal
// ==============================
document.querySelectorAll(".notification-item").forEach((item) => {
  item.addEventListener("click", function (e) {
    e.preventDefault();

    const url = this.href;
    const notifId = this.dataset.id;

    // 1️⃣ Mark notification as read
    fetch("/StoragemartTMS/notifications/read", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: "id=" + notifId,
    }).then(() => {
      // 2️⃣ Update UI instantly
      this.classList.remove("notification-unread");
      this.classList.add("notification-read");

      // 3️⃣ Resolved ticket notifications → ticket detail page
      const targetUrl = url.includes("/tickets/rate")
        ? url.replace("/tickets/rate", "/tickets/view")
        : url;
      window.location.href = targetUrl;
    });
  });
});

// ==============================
// AJAX submit for rating form
// ==============================
$(document).on("submit", "#rateTicketForm", function (e) {
  e.preventDefault(); // prevent full page reload
  var $form = $(this);

  $.post($form.attr("action"), $form.serialize())
    .done(function (response) {
      // Optionally: show a success toast or message
      alert("Thank you! Your rating has been submitted.");
      $("#rateTicketModal").modal("hide");

      // Reload page or update ticket UI if needed
      location.reload();
    })
    .fail(function () {
      alert("Oops! Something went wrong. Please try again.");
    });
});
