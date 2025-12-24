<?php
// Footer: include Bootstrap and DataTables scripts + app JS
?>
<footer class="mt-5">
  <hr>
  <div class="container"><p class="text-muted">&copy; <?php echo date('Y'); ?> City Complaint System</p></div>
</footer>
</div> <!-- /.container -->

<!-- Scripts: jQuery, Bootstrap, DataTables, Buttons -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-..." crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.print.min.js"></script>

<script src="/Citizen_Complaint/public/assets/js/app.js"></script>

<script>
// initialize datatables with export buttons for any table.datatable
document.addEventListener('DOMContentLoaded', function () {
  if (typeof $ !== 'undefined' && $.fn.dataTable) {
    $('.datatable').each(function () {
      var table = $(this).DataTable({
        dom: 'Bfrtip',
        buttons: [ 'copy', 'csv', 'excel', 'pdf', 'print' ],
        responsive: true
      });
    });
  }
});
</script>

</body>
</html>
