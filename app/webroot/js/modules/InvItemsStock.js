$(document).ready(function(){
	
	//accion al seleccionar un cliente
	$('#cbxStockLocation').change(function(){
        ajax_search_controllers();		
    });
	
	function ajax_search_controllers(){
        $.ajax({
            type:"POST",
            url:urlModuleController + "ajax_search_controllers",			
            data:{stockLocation: $("#cbxStockLocation").val()},
            beforeSend: showProcessing(),
			success:function(data){
				$("#processing").text("");
		        $("#boxControllers").html(data);
				fnBittionSetSelectsStyle();
//				$('#cbxEmployees').select2();
//				$('#cbxTaxNumbers').select2();
			}
        });
    }
	
	function showProcessing(){
        $('#processing').text("Procesando...");
    }

//END SCRIPT	
});