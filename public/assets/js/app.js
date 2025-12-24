// Lightweight app JS for City Complaint System
document.addEventListener('DOMContentLoaded', function () {
    // Confirm logout links
    document.querySelectorAll('a[href$="/logout.php"]').forEach(function (el) {
        el.addEventListener('click', function (ev) {
            if (!confirm('Are you sure you want to logout?')) ev.preventDefault();
        });
    });

    // Handle complaint status update via AJAX if form exists
    var statusForm = document.getElementById('status-form');
    if (statusForm) {
        statusForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var complaintId = statusForm.dataset.complaintId;
            var select = statusForm.querySelector('select[name="current_status_id"]');
            var comment = statusForm.querySelector('textarea[name="comment"]');
            var newStatus = select ? select.value : '';
            if (!newStatus) { alert('Please choose a status.'); return; }

            var payload = { complaint_id: parseInt(complaintId, 10), current_status_id: parseInt(newStatus, 10), comment: comment ? comment.value : '' };

            fetch('/Citizen_Complaint/api/complaints.php', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            }).then(function (r) {
                return r.json();
            }).then(function (data) {
                if (data && data.success) {
                    alert('Status updated');
                    // reload to show new status
                    window.location.reload();
                } else {
                    alert('Update failed: ' + (data && data.error ? data.error : 'unknown'));
                }
            }).catch(function (err) {
                alert('Request failed');
                console.error(err);
            });
        });
    }

    // Handle complaint form AJAX submission
    var compForm = document.getElementById('complaint-form');
    if (compForm) {
        compForm.addEventListener('submit', function (ev) {
            ev.preventDefault();
            var msgEl = document.getElementById('complaint-message');
            msgEl.textContent = '';
            var formData = new FormData(compForm);
            var payload = {
                service_id: parseInt(formData.get('service_id') || 0, 10),
                area_id: parseInt(formData.get('area_id') || 0, 10),
                priority_id: parseInt(formData.get('priority_id') || 0, 10),
                description: (formData.get('description') || '').trim()
            };
            // basic client validation
            if (!payload.service_id || !payload.area_id || !payload.description) {
                msgEl.innerHTML = '<p style="color:red;">Service, area and description are required.</p>';
                return;
            }
            var btn = compForm.querySelector('button[type="submit"]');
            if (btn) btn.disabled = true;
            fetch('/Citizen_Complaint/api/complaints.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload),
                credentials: 'same-origin'
            }).then(function (r) { return r.json(); })
                .then(function (data) {
                    if (btn) btn.disabled = false;
                    if (data && data.success && data.id) {
                        msgEl.innerHTML = '<p style="color:green;">Complaint submitted. Redirecting...</p>';
                        window.location.href = '/Citizen_Complaint/public/complaint-view.php?id=' + encodeURIComponent(data.id);
                    } else {
                        msgEl.innerHTML = '<p style="color:red;">Submission failed: ' + (data && data.error ? data.error : 'Unknown') + '</p>';
                    }
                }).catch(function (err) {
                    if (btn) btn.disabled = false;
                    msgEl.innerHTML = '<p style="color:red;">Request error</p>';
                    console.error(err);
                });
        });
    }

    // Initialize DataTables for tables with class 'datatable' (if jQuery & DataTables present)
    if (typeof $ !== 'undefined' && $.fn.dataTable) {
        $('.datatable').DataTable({
            dom: 'Bfrtip',
            buttons: ['copy', 'csv', 'excel', 'pdf', 'print'],
            responsive: true
        });
    }
});
