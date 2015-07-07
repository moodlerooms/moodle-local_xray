/**
 * jQuery scripting for list reports
 * 
 * @author Pablo Pagnone
 */
$(document).ready(function() {
	
	$('#reportslist').dataTable( {
        "bProcessing": true,
        "bServerSide": true,
	    "paging": false,
	    "searching": false,
	    "retrieve": true,
	    "oLanguage": {
	      "sProcessing": "Fetching Data, Please wait..."
	    },
	    "sAjaxSource": 'view.php?controller="reports"&action="jsonlist"'
	} );
	
} );
