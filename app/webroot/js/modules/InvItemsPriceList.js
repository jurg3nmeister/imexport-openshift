$(document).ready(function() {
//START SCRIPT
	var arrayItemsAlreadySaved = [];
	var itemsCounter = 0;
	startEventsWhenExistsItems();

	function startEventsWhenExistsItems() {
		var arrayAux = [];
		arrayAux = getItemsDetails();
		if (arrayAux[0] !== 0) {
			for (var i = 0; i < arrayAux.length; i++) {
				arrayItemsAlreadySaved[i] = arrayAux[i]['inv_item_id'];
				createEventClickAddPriceButton(arrayAux[i]['inv_item_id']);
				createEventClickEditPriceButton(arrayAux[i]['inv_item_id']);
				createEventClickDeletePriceButton(arrayAux[i]['inv_item_id']);
				itemsCounter = itemsCounter + 1;  //like this cause iteration something++ apparently not supported by javascript, gave me NaN error
			}
		}
	}
	
	function validatePrice(price, date, result, lastPrice){
		var error = '';
		if(price === ''){error+='<li>El campo "Monto" no puede estar vacio</li>';}else{
			if(lastPrice === price){error+='<li>El nuevo precio no puede ser igual al ultimo precio</li>';}
		}
		if(date === ''){error+='<li>El campo "Fecha" no puede estar vacio</li>';}
		if(result > 0){error+='<li>Ya existe un precio asociado a esta fecha</li>';}
		return error;
	}
	
	function ajax_check_date_duplicity(callback){
		$.ajax({
		    type:"POST",
//		    async: false,	
		    url:urlModuleController + "ajax_check_date_duplicity",			
		    data:{itemId: $('#txtModalItemId').val()
				,date: $('#txtModalDate').val()
				,lastDate: $('#txtModalLastDate').val()
				,priceType: $('#inputPriceType').val()},
		    beforeSend: showProcessing(),
				success: function(data){
					$("#processing").text("");	
					callback(data); 
//					var dataReceived = data.split('|');
//					callback(dataReceived[0],dataReceived[1]); 
				}
		});
	}
	
	function createEventClickAddPriceButton(itemId) {
		$('#btnAddPrice' + itemId).bind("click", function() { //must be binded 'cause loaded live with javascript'
			var objectTableRowSelected = $(this).closest('tr');
			initiateModalAddPrice(objectTableRowSelected);
			return false; //avoid page refresh
		});
	}
	
	function createEventClickEditPriceButton(itemId) {
		$('#btnEditPrice' + itemId).bind("click", function() { //must be binded 'cause loaded live with javascript'
			var objectTableRowSelected = $(this).closest('tr');
			initiateModalEditPrice(objectTableRowSelected);
			return false; //avoid page refresh
		});
	}
	
	function createEventClickDeletePriceButton(itemId) {
		$('#btnDeletePrice' + itemId).bind("click", function(e) { //must be binded 'cause loaded live with javascript'
			var objectTableRowSelected = $(this).closest('tr');
			deletePrice(objectTableRowSelected);
			//return false; //avoid page refresh
			e.preventDefault();
		});
	}
	
	function initiateModalAddPrice(objectTableRowSelected) {
		//	var itemIdForEdit = objectTableRowSelected.find('#txtItemId').val();  
			var itemIdForEditPrice = objectTableRowSelected.find('#txtItemId').val();
			$('#btnModalAddPrice').show();
			$('#btnModalEditPrice').hide();
			$('#boxModalValidatePrice').html('');//clear error message
			$('#txtModalPrice').val('');
			$('#txtModalItemId').val(itemIdForEditPrice);
			$('#txtModalDescription').val('');
			
			var d = new Date();
//			var curr_date = d.getDate();
//			var curr_month = d.getMonth();
//			var curr_year = d.getFullYear();
//			$('#txtModalDate').val(curr_date + "/" + (curr_month + 1) + "/" + curr_year);
			$('#txtModalDate').val(('0' + d.getDate()).slice(-2) + '/' + ('0' + (d.getMonth()+1)).slice(-2) + '/' + d.getFullYear());
			$('#txtModalLastPrice').val(objectTableRowSelected.find('#spaPrice'+itemIdForEditPrice).text());
			$('#txtModalPrice').keypress(function(event){
					if($.browser.mozilla === true){
						if (event.which === 8 || event.keyCode === 37 || event.keyCode === 39 || event.keyCode === 9 || event.keyCode === 16 || event.keyCode === 46){
							return true;
						}
					}
					if ((event.which !== 46 || $(this).val().indexOf('.') !== -1) && (event.which < 48 || event.which > 57)) {
						event.preventDefault();
					}
				});
			fnBittionSetSelectsStyle();
			initiateModal();
			$("#txtModalDate").datepicker({
					showButtonPanel: true
				});
	}
	
	function initiateModalEditPrice(objectTableRowSelected) {
//			var itemIdForEditPrice = objectTableRowSelected.find('#txtItemId').val(); 			
			$('#btnModalAddPrice').hide();
			$('#btnModalEditPrice').show();
			$('#boxModalValidateItem').html('');//clear error message
			ajax_initiate_modal_edit_list_price(objectTableRowSelected);
			fnBittionSetSelectsStyle();
//			initiateModal();
	}
	
	function ajax_initiate_modal_edit_list_price(objectTableRowSelected){
		var itemIdForEditPrice = objectTableRowSelected.find('#txtItemId').val();
		var priceType = $('#cbxPriceType option:selected').val();
		 $.ajax({
			type:"POST",
			url:urlModuleController + "ajax_initiate_modal_edit_list_price",			
			data:{itemIdForEditPrice: itemIdForEditPrice, priceType: priceType},				
			beforeSend: showProcessing(),
			success: function(data){
				$('#boxModalValidatePrice').html(''); 
				$('#processing').text('');
				$('#boxModalPriceDateDescription').html(data);
				$('#txtModalItemId').val(itemIdForEditPrice);
				$('#txtModalPriceId').val(objectTableRowSelected.find('#txtPriceId').val());
				$('#txtModalPrice').val(objectTableRowSelected.find('#spaPrice'+itemIdForEditPrice).text());
				$('#txtModalLastDate').val(objectTableRowSelected.find('#spaDate'+itemIdForEditPrice).text());
				initiateModal();
				fnBittionSetSelectsStyle();
				$("#txtModalDate").datepicker({
					showButtonPanel: true
				});
				////////////////////////////////////////////////////////////////////////////////// till convert this float validation script to function
				$('#txtModalPrice').keypress(function(event){
					if($.browser.mozilla === true){
						if (event.which === 8 || event.keyCode === 37 || event.keyCode === 39 || event.keyCode === 9 || event.keyCode === 16 || event.keyCode === 46){
							return true;
						}
					}
					if ((event.which !== 46 || $(this).val().indexOf('.') !== -1) && (event.which < 48 || event.which > 57)) {
						event.preventDefault();
					}
				});
				////////////////////////////////////////////////////////////////////////////////// till convert this float validation script to function
			},
			error:function(data){
				showGrowlMessage('error', 'Vuelva a intentarlo.');
				$('#processing').text('');
			}
		});
	}
	
	$('#btnModalAddPrice').click(function(e){
//		addPrice();
		ajax_check_date_duplicity(addPrice);
		e.preventDefault();
	});
	
	$('#btnModalEditPrice').click(function(e){
//		editPrice();
		ajax_check_date_duplicity(editPrice);
		e.preventDefault();
	});
	
	function addPrice(result){	
		var lastPrice = $('#txtModalLastPrice').val();
		var price = $('#txtModalPrice').val();
		var date = $('#txtModalDate').val();
		var error = validatePrice(price, date, result, lastPrice); 
		if(error === ''){
			ajax_save_list_price();
		}else{
			$('#boxModalValidatePrice').html('<ul>'+error+'</ul>');
		}
	}
	
	function editPrice(result){	
		var price = $('#txtModalPrice').val();
		var date = $('#txtModalDate').val();
		var error = validatePrice(price, date, result); 
		if(error === ''){
			ajax_save_list_price();
		}else{
			$('#boxModalValidatePrice').html('<ul>'+error+'</ul>');
		}
	}
	
	function deletePrice(objectTableRowSelected) {
		showBittionAlertModal({content: '¿Está seguro de eliminar este precio?'});
		$('#bittionBtnYes').click(function(event) {
//			var objectTableRowSelected = object.closest('tr');
//			var priceId = objectTableRowSelected.find('#txtPriceId').val();
			hideBittionAlertModal();
			ajax_delete_price(objectTableRowSelected);
			event.preventDefault();
		});
		event.preventDefault();
	}
	
	//Save price
	function ajax_save_list_price() {
		$.ajax({
			type: "POST",
			url: urlModuleController + "ajax_save_list_price",
			data: {itemId: $('#txtModalItemId').val(),
				priceId: $('#txtModalPriceId').val(),
				amount: $('#txtModalPrice').val(),
				priceType: $('#inputPriceType').val(),
				date: $('#txtModalDate').val(),
				description: $('#txtModalDescription').val()
			},
			beforeSend: showProcessing(),
			success: function(data) {
				var arrayData = data.split('|');
				if(arrayData[0] === "success"){
					window.location.reload(true);
					$('#modalAddPrice').modal('hide');
//					showGrowlMessage('ok', 'Cambios guardados.');
//					$("#txtIdCustomer").val(arrayData[1]);
				}else{
					showGrowlMessage('error', 'Vuelva a intentarlo.');
				}
				$('#boxProcessing').text('');
			},
			error:function(data){
				showGrowlMessage('error', 'Vuelva a intentarlo.');
				$('#boxProcessing').text('');
			}
		});
	}
	
	function ajax_delete_price(objectTableRowSelected){
		var priceId = objectTableRowSelected.find('#txtPriceId').val();
		var itemId = objectTableRowSelected.find('#txtItemId').val();
		var priceTypeId = $('#inputPriceType').val();
		$.ajax({
            type:"POST",
            url:urlModuleController + "ajax_delete_price",			
            data:{priceId: priceId,
				itemId: itemId,
				priceTypeId: priceTypeId},
//           beforeSend: function(){
//				$('#boxProcessingEmployee').text(" Procesando...");
//			},
			beforeSend: showProcessing(),
            success: function(data){
//				var arrayData = data.split('|');
				if(data === "success"){
					showGrowlMessage('ok', 'Cambios guardados.');
					window.location.reload(true);
				}else{
					showGrowlMessage('error', 'Vuelva a intentarlo.');
				}
				$('#boxProcessing').text('');
			},
			error:function(data){
				showGrowlMessage('error', 'Vuelva a intentarlo.');
				$('#boxProcessing').text('');
			}
        });
	}
	
	function initiateModal() {
		$('#modalAddPrice').modal({
			show: 'true',
			backdrop: 'static'
		});
	}
	
	//get all items for save a movement
	function getItemsDetails() {
		var arrayItemsDetails = [];
		var itemId = '';

		$('#tablaPrecios tr').each(function() {
			itemId = $(this).find('#txtItemId').val();

			arrayItemsDetails.push({'inv_item_id': itemId});

		});

		if (arrayItemsDetails.length === 0) {  //For fix undefined index
			arrayItemsDetails = [0]; //if there isn't any row, the array must have at least one field 0 otherwise it sends null
		}

		return arrayItemsDetails;
	}
	
	//show message of procesing for ajax
	function showProcessing(){
        $('#processing').text("Procesando...");
    }
	
	function showGrowlMessage(type, text, sticky) {
		if (typeof(sticky) === 'undefined')
			sticky = false;

		var title;
		var image;
		switch (type) {
			case 'ok':
				title = 'EXITO!';
				image = urlImg+'check.png';
				break;
			case 'error':
				title = 'OCURRIO UN PROBLEMA!';
				image = urlImg+'error.png';
				break;
			case 'warning':
				title = 'PRECAUCIÓN!';
				image = urlImg+'warning.png';
				break;
		}
		$.gritter.add({
			title: title,
			text: text,
			sticky: sticky,
			image: image
		});
	}

//END SCRIPT	
});

