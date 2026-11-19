	<!-- Bootstrap JS -->
	<script src="assets/js/bootstrap.bundle.min.js"></script>
	<!--plugins-->
	<script src="assets/js/jquery.min.js"></script>
	<script src="assets/plugins/simplebar/js/simplebar.min.js"></script>
	<script src="assets/plugins/metismenu/js/metisMenu.min.js"></script>
	<script src="assets/plugins/chartjs/chart.min.js"></script>
	<script src="assets/plugins/vectormap/jquery-jvectormap-2.0.2.min.js"></script>
    <script src="assets/plugins/vectormap/jquery-jvectormap-world-mill-en.js"></script>
	<script src="assets/plugins/jquery.easy-pie-chart/jquery.easypiechart.min.js"></script>
	<script src="assets/plugins/sparkline-charts/jquery.sparkline.min.js"></script>
	<script src="assets/plugins/jquery-knob/excanvas.js"></script>
	<script src="assets/plugins/jquery-knob/jquery.knob.js"></script>
	
	  <script>
		  $(function() {
			  $(".knob").knob();
		  });
	  </script>
	<script src="assets/js/index.js"></script>
	<!--app JS-->
	<script src="assets/js/app.js"></script>

	<script src="https://cdn.jsdelivr.net/npm/select2@4.0.13/dist/js/select2.min.js"></script>
	

	<!-- DataTables CSS -->
    <link rel="stylesheet" href="assets/plugins/datatable/css/dataTables.bootstrap5.min2.css">
    
    
    <!-- DataTables JS -->
    <script src="assets/plugins/datatable/js/dataTables.bootstrap5.min2.js"></script>
    
    <!-- Your custom JS to activate DataTables -->
    <script>
    $(document).ready(function() {
        $('#example').DataTable({
            "pageLength": 10,
            "lengthMenu": [10, 25, 50, 100],
            "paging": true,
            "info": true,
            "searching": true
        });
    });
    </script>

	<script>
	$(document).ready(function() {
		$('#example_sort_data').DataTable({
			"pageLength": 10,
			"lengthMenu": [10, 25, 50, 100],
			"paging": true,
			"info": true,
			"searching": true,
			"order": [] // ✅ sort by first column descending
		});
	});
	</script>

</body>

</html>