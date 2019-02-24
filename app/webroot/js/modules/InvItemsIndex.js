$(document).ready(function(){
	///Url Paths
//	var path = window.location.pathname;
//	var arr = path.split('/');
//	var moduleController = ('/'+arr[1]+'/'+arr[2]+'/');//Path validation
//
//	//Calendar script
//   $("#txtDate").datepicker({
//	  showButtonPanel: true
//   });
//   
//   $('#txtDate').keydown(function(e){e.preventDefault();});
//   

	fnBittionSetSelectsStyle();

   $("#btnClearSearch").click(function(){
	   window.location.href = urlModuleController + urlAction;
	   event.preventDefault();
   });
	
	
	$('#btnDebtsReport').click(function(){
		ajax_generate_debts_report();
	});
	
	function ajax_generate_debts_report(){ //Report
		$.ajax({
            type:"POST",
			async:false, // the key to open new windows when success
            url:urlModuleController + "ajax_generate_debts_report",
            data:{	},
			beforeSend: function(){
				$('#boxProcessing').text('Procesando...');
			},
            success: function(data){
//				open_in_new_tab(urlModuleController+'vreport_items_utilities');
				open_in_new_tab(urlModuleController+'vreport_debts');
				$('#boxProcessing').text('');
			},
			error:function(data){
				showGrowlMessage('error', 'Vuelva a intentarlo.');
				$('#boxProcessing').text('');
			}
        });
	}
	
	function open_in_new_tab(url)
	{
	  var win=window.open(url, '_blank');
	  win.focus();
	}
	
	
	
	
	
	
//END SCRIPT	
});