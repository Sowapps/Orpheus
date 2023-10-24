window.addEventListener('DOMContentLoaded', () => {
	// Simple-DataTables
	// https://github.com/fiduswriter/Simple-DataTables/wiki
	
	const datatablesSimple = document.getElementById('mySimpleDatatable');
	if( datatablesSimple ) {
		new simpleDatatables.DataTable(datatablesSimple);
	}
});
