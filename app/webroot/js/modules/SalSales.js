$(document).ready(function(){
//START SCRIPT

//	var globalPeriod = $('#globalPeriod').text(); // this value is obtained from the main template AND from MainBittion.css
	
//	var arrayItemsAlreadySaved = []; 
	var arrayItemsWarehousesAlreadySaved = []; 
	var itemsCounter = 0;
//	var arrayWarehouseItemsAlreadySaved = []; 
	startEventsWhenExistsItems();
	
	var arrayPaysAlreadySaved = []; 
	startEventsWhenExistsPays();
	
//	var payDebt = 0;
//	startEventsWhenExistsDebts();
	
	//When exist items of warehouses, it starts its events and fills arrayItemsAlreadySaved
	function startEventsWhenExistsItems() {
		var arrayAux = [];
		arrayAux = getItemsDetails();
//		if(urlAction === 'save_invoice'){
			if (arrayAux[0] !== 0) {
				for (var i = 0; i < arrayAux.length; i++) {
					arrayItemsWarehousesAlreadySaved[i] = arrayAux[i]['inv_item_id']+'w'+arrayAux[i]['inv_warehouse_id'];
					createEventClickEditItemButton(arrayAux[i]['inv_item_id'],arrayAux[i]['inv_warehouse_id']);
					createEventClickDistribItemButton(arrayAux[i]['inv_item_id'],arrayAux[i]['inv_warehouse_id']);
					createEventClickDeleteItemButton(arrayAux[i]['inv_item_id'],arrayAux[i]['inv_warehouse_id']);	
					itemsCounter = itemsCounter + 1;  //like this cause iteration something++ apparently not supported by javascript, gave me NaN error
				}
			}
//		}else{
//			if (arrayAux[0] !== 0) {
//				for (var i = 0; i < arrayAux.length; i++) {
//	//				arrayItemsAlreadySaved[i] = arrayAux[i]['inv_item_id'];
//	//				arrayWarehouseItemsAlreadySaved[i] = arrayAux[i]['inv_warehouse_id'];
//					arrayItemsWarehousesAlreadySaved[i] = arrayAux[i]['inv_item_id']+'w'+arrayAux[i]['inv_warehouse_id'];
//					createEventClickEditItemButton(arrayAux[i]['inv_item_id'],arrayAux[i]['inv_warehouse_id']);
//					createEventClickDeleteItemButton(arrayAux[i]['inv_item_id'],arrayAux[i]['inv_warehouse_id']);	
//					itemsCounter = itemsCounter + 1;  //like this cause iteration something++ apparently not supported by javascript, gave me NaN error
//				}
//			}
//		}
		
	}
	
//	function startEventsWhenExistsDebts(){		
//		payDebt =0;
//		var discount = $('#txtDiscount').val();
//		var	payPaid = getTotalPay();
//		var payTotal = getTotal();
//		var payTotalPlusDisc = Number(payTotal) - ((Number(payTotal) * Number(discount))/100);
//		var payDebtDirt = Number(payTotalPlusDisc) - Number(payPaid);
//		payDebt = parseFloat(payDebtDirt).toFixed(2);
//		return payDebt;
//	}
	
	//gets a list of the item ids in the document details
//	function itemsListWhenExistsItems(){
//		var arrayAux = [];
//		arrayItemsAlreadySaved = [];
//		arrayAux = getItemsDetails();
//		if(arrayAux[0] !== 0){
//			for(var i=0; i< arrayAux.length; i++){
//				 arrayItemsAlreadySaved[i] = arrayAux[i]['inv_item_id'];
//			}
//		}
//		if(arrayItemsAlreadySaved.length === 0){  //For fix undefined index
//			arrayItemsAlreadySaved = [0]; //if there isn't any row, the array must have at least one field 0 otherwise it sends null
//		}
//		
//		return arrayItemsAlreadySaved; //NOT SURE TO PUT THIS LINE	
//	}
	
	//gets a list of the warehouse ids in the document details
//	function warehouseListWhenExistsItems(){
//		var arrayAux = [];
//		arrayWarehouseItemsAlreadySaved = [];
//		arrayAux = getItemsDetails();
//		if(arrayAux[0] !== 0){
//			for(var i=0; i< arrayAux.length; i++){
//				 arrayWarehouseItemsAlreadySaved[i] = arrayAux[i]['inv_warehouse_id'];
//			}
//		}
//		if(arrayWarehouseItemsAlreadySaved.length === 0){  //For fix undefined index
//			arrayWarehouseItemsAlreadySaved = [0]; //if there isn't any row, the array must have at least one field 0 otherwise it sends null
//		}
//		
//		return arrayWarehouseItemsAlreadySaved; //NOT SURE TO PUT THIS LINE	
//	}
	
//	//When exist items, it starts its events and fills arrayItemsAlreadySaved
//	function startEventsWhenExistsItems(){
//		var arrayAux = [];
//		arrayAux = getItemsDetails();
//		if(arrayAux[0] !== 0){
//			for(var i=0; i< arrayAux.length; i++){
//				arrayItemsAlreadySaved[i] = arrayAux[i]['inv_item_id'];
//				arrayWarehouseItemsAlreadySaved[i] = arrayAux[i]['inv_warehouse_id'];
//				createEventClickEditItemButton(arrayAux[i]['inv_item_id'],arrayAux[i]['inv_warehouse_id']);
//				createEventClickDeleteItemButton(arrayAux[i]['inv_item_id'],arrayAux[i]['inv_warehouse_id']);	
//				itemsCounter = itemsCounter + 1;  //like this cause iteration something++ apparently not supported by javascript, gave me NaN error							 
//			}
//		}
//	}
	
	//When exist pays, it starts its events and fills arrayPaysAlreadySaved
	function startEventsWhenExistsPays(){		/*STANDBY*/
		var arrayAux = [];
		arrayAux = getPaysDetails();
		if(arrayAux[0] !== 0){
			for(var i=0; i< arrayAux.length; i++){
				 arrayPaysAlreadySaved[i] = arrayAux[i]['date'];
				 createEventClickEditPayButton(arrayAux[i]['date']);
				 createEventClickDeletePayButton(arrayAux[i]['date']);			 
			}
		}
	}
	//validates before add item warehouse price and quantity
	function validateItem(warehouse, item, salePrice, quantity, backorder, lastBackorder, virtualStock, lastQuantity, approvedQuantity){
		var error = '';
		if(warehouse === ''){error+='<li>El campo "Almacen" no puede estar vacio</li>';}
		if(item === ''){error+='<li>El campo "Item" no puede estar vacio</li>';}
		if(quantity === ''){
			error+='<li>El campo "Cantidad" no puede estar vacio</li>'; 
		}else{
			if(parseInt(quantity, 10) == 0){//CREO Q SE DEBE USAR Number()
				error+='<li>El campo "Cantidad" no puede ser cero</li>'; 
			}
		}
		if(salePrice === ''){
			error+='<li>El campo "Precio Unitario" no puede estar vacio</li>'; 
		}
//		if(backorder !== lastBackorder){
//			if(Number(backorder) > Number(quantity)){
//				error+='<li>El campo "Backorder" no puede ser mayor a cantidad</li>'; 
//			}
//			if(Number(backorder) < (Number(lastBackorder) - Number(stockVirtual))){
//				error+='<li>El campo "Backorder" no puede ser menor a backorder</li>'; 
//			}
//		}
		
		
		
		 if((quantity !== lastQuantity) && (backorder !== lastBackorder)){//CAMBIO DE cantidad y backorder
			var rest = Number(lastQuantity) - Number(lastBackorder); 	
			var rest2 = Number(quantity) - Number(rest) - Number(virtualStock); 	
			if(Number(backorder) > Number(quantity) || Number(backorder) < Number(rest2)){
				error+='<li>El campo "Backorder" no puede ser menor a rest2 ni mayor a la cantidad</li>'; 
			}
		}
//		else if(quantity !== lastQuantity){//CAMBIO DE cantidad 
//			
//		}
		else if(backorder !== lastBackorder){//CAMBIO DE backorder
			if(Number(backorder) > Number(quantity)){
				error+='<li>El campo "Backorder" no puede ser mayor a cantidad</li>'; 
			}
//			if(Number(backorder) < (Number(lastBackorder) - Number(virtualStock))){
//			var foo = (Number(lastBackorder) - Number(virtualStock) + Number(lastQuantity) - Number(lastBackorder));
			if(Number(backorder) < Number(lastBackorder) - Number(virtualStock) + Number(lastQuantity) - Number(lastBackorder)){
				error+='<li>El campo "Backorder" no puede ser menor a backorder</li>'; 
			}
		}
//		else{ //o si puede ser cero el precio?	
//			if(parseFloat(salePrice).toFixed(2) === 0.00){
//				error+='<li>El campo "Precio Unitario" no puede ser cero</li>'; 
//			}	
//		}
		
		
		
		if (Number(approvedQuantity) > 0){
			if(Number(lastQuantity) !== Number(quantity)){
				if(Number(quantity) < Number(approvedQuantity)){
					error+='<li>El campo "Cantidad" no puede ser menor a lo aprovado</li>'; 
				}
			}else{
				if(Number(backorder) > (Number(quantity) - Number(approvedQuantity))){
					error+='<li>El campo "Backorder" no puede ser mayor ....</li>'; 
				}
			}		
		}

		return error;
	}
	
	//validates before add item warehouse price and quantity
	function validateItemDistrib(warehouseDest, item, quantity, quantityToDistrib){
		var error = '';
		if(warehouseDest === ''){error+='<li>El campo "Almacen" no puede estar vacio</li>';}
		if(item === ''){error+='<li>El campo "Item" no puede estar vacio</li>';}
		if(quantity === ''){
			error+='<li>El campo "Cantidad" no puede estar vacio</li>'; 
		}else{
			if(parseInt(quantity, 10) == 0){//CREO Q SE DEBE USAR Number()
				error+='<li>El campo "Cantidad" no puede ser cero</li>'; 
			}
		}
		if(quantityToDistrib === ''){
			error+='<li>El campo "Cantidad a Pasar" no puede estar vacio</li>'; 
		}else{
			if(parseInt(quantityToDistrib, 10) == 0){//CREO Q SE DEBE USAR Number()
				error+='<li>El campo "Cantidad a Pasar" no puede ser cero</li>'; 
			}
			if(parseInt(quantityToDistrib, 10) >= parseInt(quantity, 10)){
				error+='<li>El campo "Cantidad a Pasar" no puede mayor o igual a "Cantidad"</li>'; 
			}
		}
		return error;
	}
	
	function validateAddPay(payDate, payAmount, payDebt, documentState){
		var error = '';
		if(payDate === ''){
			error+='<li>El campo "Fecha" no puede estar vacio</li>'; 
		}else{
			var arrayAux = [];
			var myDate = payDate.split('/');
			var dateId = myDate[2]+"-"+myDate[1]+"-"+myDate[0];
			arrayAux = getPaysDetails();
			if(arrayAux[0] !== 0){
				for(var i=0; i< arrayAux.length; i++){
					if(dateId === (arrayAux[i]['date'])){
						error+='<li>La "Fecha" ya existe</li>'; 
					}  
				}
			}
		}
		
		if(payAmount === ''){
			error+='<li>El campo "Monto a Pagar" no puede estar vacio</li>'; 
		}else{
			if(Number(payAmount) > Number(payDebt) && (documentState === 'NOTE_APPROVED' || documentState === 'SINVOICE_APPROVED')){
				error+='<li>El campo "Monto a Pagar" no puede ser mayor a la deuda</li>'; 
			}
			if(parseFloat(payAmount).toFixed(2) == 0){//CREO Q SE DEBE USAR Number()
				error+='<li>El campo "Monto a Pagar" no puede ser cero</li>'; 
			}
		}
		return error;
	}
	
	function validateEditPay(payDate, payAmount, payHiddenAmount, payDebt, documentState){
		var error = '';
		if(payDate === ''){
			error+='<li>El campo "Fecha" no puede estar vacio</li>'; 
		}		
		if(payAmount === ''){
			error+='<li>El campo "Monto a Pagar" no puede estar vacio</li>'; 
		}else{
			var payDebt2 = Number(payDebt) + Number(payHiddenAmount);
			if(parseFloat(payAmount).toFixed(2) == 0){//CREO Q SE DEBE USAR Number()
				error+='<li>El campo "Monto a Pagar" no puede ser cero</li>'; 
			}else if (payAmount > payDebt2  && (documentState === 'NOTE_APPROVED' || documentState === 'SINVOICE_APPROVED')){
				error+='<li>El campo "Monto a Pagar" no puede ser mayor a la deuda</li>'; 
			}
		}
		return error;
	}
	
	function validateBeforeSaveAll(arrayItemsDetails, result){
		var error = '';
		var noteCode = $('#txtNoteCode').val();
		var date = $('#txtDate').val();
		var dateYear = date.split('/');
		var clients = $('#cbxCustomers').text();
		var clientVal = $('#cbxCustomers option:selected').val();
		var employees = $('#cbxEmployees').text();
		var taxNumbers = $('#cbxTaxNumbers').text();
		var salesmen = $('#cbxSalesman').text();
		var salesmenVal = $('#cbxSalesman option:selected').val();
//		var discountType=$('input[name=radio]:checked, #rdDiscount').val();//1.Ninguno 2.Porcentage 3.Monto 
		var discount = $('#txtDiscount').val();
		var exRate = $('#txtExRate').val();
		
		if(noteCode === ''){	error+='<li> El campo "No. Nota de Remisión" no puede estar vacio </li>'; }
		if(date === ''){	error+='<li> El campo "Fecha" no puede estar vacio </li>'; }
		if(dateYear[2] !== globalPeriod){	error+='<li> El año '+dateYear[2]+' de la fecha del documento no es valida, ya que se encuentra en la gestión '+ globalPeriod +'.</li>'; }
		if(clientVal === '0'){	error+='<li> Selecione un "Cliente" </li>'; }
		if(clients === ''){	error+='<li> El campo "Cliente" no puede estar vacio </li>'; }
		if(employees === ''){	error+='<li> El campo "Encargado" no puede estar vacio </li>'; }
		if(taxNumbers === ''){	error+='<li> El campo "NIT - Nombre" no puede estar vacio </li>'; }
		if(salesmenVal === '0'){	error+='<li> Selecione un "Vendedor" </li>'; }
		if(salesmen === ''){	error+='<li> El campo "Vendedor" no puede estar vacio </li>'; }
		if(arrayItemsDetails[0] === 0){error+='<li> Debe existir al menos 1 "Item" </li>';}
		if(discount === ''){	error+='<li> El campo "Descuento" no puede estar vacio </li>'; }
//		if(discountType !== 1){
//			if(discount === '0'){	error+='<li> El campo "Descuento" no puede ser 0 </li>'; }
//		}
		if(exRate === ''){	error+='<li> El campo "Tipo de Cambio" no puede estar vacio </li>'; }
		var itemZero = findIfOneItemHasQuantityZero(arrayItemsDetails);
		if(itemZero > 0){error+='<li> Se encontraron '+ itemZero +' "Items" con "Cantidad" 0, no puede existir ninguno </li>';}
//		if(typeof(result)==='undefined') result = 'undefined';
//		if(result !== 'undefined'){
			if(result > 0){error+='<li> El "No. Nota de Remision" no puede repetirse </li>'; }
//		}
		return error;
	}
	
	function validateBeforeSaveAll3(payDebt){
		var error = '';
		if(payDebt < 0){error+='<li> Debe corregir los pagos antes de poder APROBAR </li>';}

		return error;
	}
	
	function ajax_check_code_duplicity(callback, param){
//		 var jqXHR = 
		$.ajax({
		    type:"POST",
//		    async: false,	
		    url:urlModuleController + "ajax_check_code_duplicity",			
		    data:{noteCode: $('#txtNoteCode').val()
				,genericCode: $('#txtGenericCode').val()},
		    beforeSend: showProcessing(),
				success: function(data){
					$("#processing").text("");
					callback(data, param); 
				}
		});
//		return jqXHR.responseText;
	}
	
	function ajax_check_code_duplicity_pays_coherency(callback){
//		 var jqXHR = 
		$.ajax({
		    type:"POST",
//		    async: false,	
		    url:urlModuleController + "ajax_check_code_duplicity_pays_coherency",			
		    data:{noteCode: $('#txtNoteCode').val()
				,genericCode: $('#txtGenericCode').val()
				,docCode: $('#txtCode').val()},
		    beforeSend: showProcessing(),
				success: function(data){
					$("#processing").text("");	
					var dataReceived = data.split('|');
					callback(dataReceived[0],dataReceived[1],dataReceived[2]); 
				}
		});
//		return jqXHR.responseText;
	}
	
	function ajax_check_movements_state(callback){
//		 var jqXHR = 
		$.ajax({
		    type:"POST",
//		    async: false,	
		    url:urlModuleController + "ajax_check_movements_state",			
		    data:{//purchaseId: $('#txtPurchaseIdHidden').val(),
				genericCode: $('#txtGenericCode').val() },
		    beforeSend: showProcessing(),
				success: function(data){
					$("#processing").text("");	
					callback(data); 
				}
		});
//		return jqXHR.responseText;
	}
	
	function findIfOneItemHasQuantityZero(arrayItemsDetails){
		var cont = 0;
		for(var i = 0; i < arrayItemsDetails.length; i++){
			if(parseInt(arrayItemsDetails[i]['quantity'],10) === 0){
				cont++;
			}
		}
		return cont;
	}
	
	function changeLabelDocumentState(state){
		switch(state)
		{
			case 'NOTE_PENDANT':
				$('#documentState').addClass('label-warning');
				$('#documentState').text('NOTA PENDIENTE');
				$('#inputDocumentState').text('NOTE_PENDANT');
				break;
			case 'NOTE_APPROVED':
				$('#documentState').removeClass('label-warning').addClass('label-success');
				$('#documentState').text('NOTA APROBADA');
				$('#inputDocumentState').text('NOTE_APPROVED');
				break;
			case 'NOTE_CANCELLED':
				$('#documentState').removeClass('label-success').addClass('label-important');
				$('#documentState').text('NOTA CANCELADA');
				$('#inputDocumentState').text('NOTE_CANCELLED');
				break;
				
			case 'NOTE_RESERVED':
				$('#documentState').removeClass('label-warning').addClass('label-info');
				$('#documentState').text('NOTA RESERVADA');
				$('#inputDocumentState').text('NOTE_RESERVED');
				break;	
				
			case 'SINVOICE_PENDANT':
				$('#documentState').addClass('label-warning');
				$('#documentState').text('FACTURA PENDIENTE');
				$('#inputDocumentState').text('SINVOICE_PENDANT');
				break;
			case 'SINVOICE_APPROVED':
				$('#documentState').removeClass('label-warning').addClass('label-success');
				$('#documentState').text('FACTURA APROBADA');
				$('#inputDocumentState').text('SINVOICE_APPROVED');
				break;
			case 'SINVOICE_CANCELLED':
				$('#documentState').removeClass('label-success').addClass('label-important');
				$('#documentState').text('FACTURA CANCELADA');
				$('#inputDocumentState').text('SINVOICE_CANCELLED');
				break;
		}
	}
	
	function initiateModal(){
		$('#modalAddItem').modal({
					show: 'true',
					backdrop:'static'
		});
	}
	
//	function initiateModalEdit(){
//		$('#modalEditItem').modal({
//					show: 'true',
//					backdrop:'static'
//		});
//	}
	
	function initiateModalDistrib(){
		$('#modalDistribItem').modal({
					show: 'true',
					backdrop:'static'
		});
	}
	
	function initiateModalPay(){
		$('#modalAddPay').modal({
					show: 'true',
					backdrop:'static'
		});
	}
	
		function validateOnlyIntegers(event){
		// Allow only backspace and delete
		if (event.keyCode === 8 || event.keyCode === 9 || event.keyCode === 46 || (event.keyCode > 34 && event.keyCode < 41)/*event.keyCode === 35 || event.keyCode === 36 || event.keyCode === 37 || event.keyCode === 38 || event.keyCode === 39 || event.keyCode === 40*/) {
			// let it happen, don't do anything
		}
		else {
			// Ensure that it is a number and stop the keypress
			if ( (event.keyCode < 96 || event.keyCode > 105) ) { //habilita keypad
				if ( (event.keyCode < 48 || event.keyCode > 57) ) {
					event.preventDefault(); 
				}
			}   
		}
	}
	
	function validateOnlyFloatNumbers(event){
		// Allow backspace,	tab, decimal point
		if (event.keyCode === 8 || event.keyCode === 9 || event.keyCode === 110 || event.keyCode === 190) {
			// let it happen, don't do anything
		}
		else {
			// Ensure that it is a number and stop the keypress
			if ( (event.keyCode < 96 || event.keyCode > 105) ) { //habilita keypad
				if ((event.keyCode < 48 || event.keyCode > 57 ) ) {
					
						event.preventDefault(); 					
					
				}
			}   
		}
	}
	
	function validateBeforeMoveOut(arrayItemsStocksErrors/*, controlName*/) {
		var error = '';
		var arrItemsStatusStock = [];
		var arrItemWarehouse = [];
		var arrStatusVirtStock = [];
		var itemId = '';
		var warehouseId = '';
		var status = '';
		var stockVirt = '';
		for (var i = 0; i < arrayItemsStocksErrors.length; i++) {
			arrItemsStatusStock = arrayItemsStocksErrors[i].split('=>');//  itemwwarehouse=>status:stock
			arrItemWarehouse = arrItemsStatusStock[0].split('w');//itemwwarehouse
			itemId = arrItemWarehouse[0];
			warehouseId = arrItemWarehouse[1];
			if (itemId !== '') {//if exist itemId in the array splited because a,b,'' because last field is empty
//				warehouseId = $('#txtWarehouseId' + itemId).val();
				arrStatusVirtStock = arrItemsStatusStock[1].split(':');//status:stock
				status = arrStatusVirtStock[0];
//				if(arrStatusVirtStock[1] < 0){
//					stockVirt = 0;
//				}else{
					stockVirt = arrStatusVirtStock[1];
//				}	
				if (status === 'error') {
					error += '<li>' + $('#spaItemName' + itemId).text() + ': El "Stock = ' + stockVirt + '" no es suficiente para la "Cantidad = ' + $('#spaAvaQuantity' + itemId + 'w' + warehouseId).text() + '" requerida.</li>';
				}else if (status === 'errorD') {
					error += '<li>' + $('#spaItemName' + itemId).text() + ': El "Stock = ' + stockVirt + '" del almacen Destino, no es suficiente para eliminar la "Cantidad = ' + $('#spaAvaQuantity' + itemId + 'w' + warehouseId).text() + '" requerida.</li>';
				}else if (status === 'errorS') {
					error += '<li>' + $('#spaItemName' + itemId).text() + ': El "Stock = ' + stockVirt + '" del almacen Origen, no es suficiente para aprobar la "Cantidad = ' + $('#spaAvaQuantity' + itemId + 'w' + warehouseId).text() + '" requerida.</li>';
				}
                                
//				$('#' + controlName + itemId).text(stock);
			}
		}
		return error;
	}

	function initiateModalAddItemSO(result){
		var error = validateBeforeSaveAll([{0:0}], result);//I send [{0:0}] 'cause it doesn't care to validate if arrayItemsDetails is empty or not
		if( error === ''){
			if(arrayItemsWarehousesAlreadySaved.length === 0){  //For fix undefined index
				arrayItemsWarehousesAlreadySaved = [0+'w'+0]; //if there isn't any row, the array must have at least one field 0 otherwise it sends null
			}
			$('#btnModalAddItem').show();
			$('#btnModalEditItem').hide();
			$('#boxModalValidateItem').html('');//clear error message
			ajax_initiate_modal_add_item_in_order(arrayItemsWarehousesAlreadySaved);
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}
	}
	
	function initiateModalAddItem(result){
		var error = validateBeforeSaveAll([{0:0}], result);//I send [{0:0}] 'cause it doesn't care to validate if arrayItemsDetails is empty or not
		if( error === ''){
			if(arrayItemsWarehousesAlreadySaved.length === 0){  //For fix undefined index
				arrayItemsWarehousesAlreadySaved = [0+'w'+0]; //if there isn't any row, the array must have at least one field 0 otherwise it sends null
//				arrayWarehouseItemsAlreadySaved = [0];
			}
			$('#btnModalAddItem').show();
			$('#btnModalEditItem').hide();
			$('#boxModalValidateItem').html('');//clear error message
			ajax_initiate_modal_add_item_in(arrayItemsWarehousesAlreadySaved);
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}
	}
	
	function initiateModalEditItem(result, objectTableRowSelected){
		var error = validateBeforeSaveAll([{0:0}], result);//I send [{0:0}] 'cause it doesn't care to validate if arrayItemsDetails is empty or not
		if( error === ''){
//			var itemIdForEdit = objectTableRowSelected.find('#txtItemId').val();
//			var warehouseIdForEdit = objectTableRowSelected.find('#txtWarehouseId'+itemIdForEdit).val();
			$('#btnModalAddItem').hide();
			$('#btnModalEditItem').show();
			$('#boxModalValidateItem').html('');//clear error message
//			$('#cbxModalWarehouses').empty();
//			$('#cbxModalWarehouses').append('<option value="'+warehouseIdForEdit+'">'+objectTableRowSelected.find('#spaWarehouse'+itemIdForEdit).text()+'</option>');
//			$('#cbxModalItems').empty();
//			$('#cbxModalItems').append('<option value="'+itemIdForEdit+'">'+objectTableRowSelected.find('td:first').text()+'</option>');
//			$('#txtModalPrice').val(objectTableRowSelected.find('#spaSalePrice'+itemIdForEdit+'w'+warehouseIdForEdit).text());
//			$('#txtModalStock').val(objectTableRowSelected.find('#spaStock'+itemIdForEdit).text());
//			$('#txtModalQuantity').val(objectTableRowSelected.find('#spaQuantity'+itemIdForEdit+'w'+warehouseIdForEdit).text());
//			$('#txtModalStock').keypress(function() {
//				return false;
//			});
//			fnBittionSetSelectsStyle();
//			initiateModal();
			ajax_initiate_modal_edit_item(arrayItemsWarehousesAlreadySaved, objectTableRowSelected /*,itemIdForEdit, warehouseIdForEdit*/);
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}
	}
	
	function initiateModalAddPay(result){
		var error = validateBeforeSaveAll([{0:0}], result);//I send [{0:0}] 'cause it doesn't care to validate if arrayItemsDetails is empty or not
		if( error === ''){
//			if(arrayPaysAlreadySaved.length === 0){  //For fix undefined index
//				arrayPaysAlreadySaved = [0]; //if there isn't any row, the array must have at least one field 0 otherwise it sends null
//			}
			$('#btnModalAddPay').show();
			$('#btnModalEditPay').hide();
			$('#boxModalValidatePay').html('');//clear error message
			ajax_initiate_modal_add_pay(/*arrayPaysAlreadySaved/*,parseFloat(payDebt).toFixed(2)*/);
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}
	}
	
	function initiateModalEditPay(result, objectTableRowSelected){
		var error = validateBeforeSaveAll([{0:0}], result);//I send [{0:0}] 'cause it doesn't care to validate if arrayItemsDetails is empty or not
		if( error === ''){
//			var payIdForEdit = objectTableRowSelected.find('#txtPayDate').val();  //
			$('#btnModalAddPay').hide();
			$('#btnModalEditPay').show();
			$('#boxModalValidatePay').html('');//clear error message
//			$('#txtModalDate').val(objectTableRowSelected.find('#spaPayDate'+payIdForEdit).text());
//			$('#txtModalPaidAmount').val(objectTableRowSelected.find('#spaPayAmount'+payIdForEdit).text());
//			$('#txtModalDescription').val(objectTableRowSelected.find('#spaPayDescription'+payIdForEdit).text());
//			$('#txtModalAmountHidden').val(objectTableRowSelected.find('#spaPayAmount'+payIdForEdit).text());
//			$('#txtModalDebtAmount').val(objectTableRowSelected.find('#spaPayAmount'+payIdForEdit).text());
//			initiateModalPay();
			ajax_initiate_modal_edit_pay(objectTableRowSelected/*arrayPaysAlreadySaved/*,parseFloat(payDebt).toFixed(2)*/);
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}	
	}
	
	function initiateModalDistribItem(result, objectTableRowSelected){
		var error = validateBeforeSaveAll([{0:0}], result);//I send [{0:0}] 'cause it doesn't care to validate if arrayItemsDetails is empty or not
		if( error === ''){
//			$('#btnModalAddItem').hide();
//			$('#btnModalEditItem').hide();
			$('#btnModalDistribItem').show();
			$('#boxModalValidateItemDistrib').html('');//clear error message
			ajax_initiate_modal_distrib_item(/*arrayItemsWarehousesAlreadySaved,*/ objectTableRowSelected /*,itemIdForEdit, warehouseIdForEdit*/);
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}
	}
	
	function createEventClickEditItemButton(itemId,warehouseId){
			$('#btnEditItem'+itemId+'w'+warehouseId).bind("click",function(){ //must be binded 'cause loaded live with javascript'
					var objectTableRowSelected = $(this).closest('tr');
//					initiateModalEditItem(objectTableRowSelected);
					ajax_check_code_duplicity(initiateModalEditItem, objectTableRowSelected);//passing callback as a parameter into another function
					return false; //avoid page refresh
			});
	}
	
	function createEventClickDistribItemButton(itemId,warehouseId){
			$('#btnDistribItem'+itemId+'w'+warehouseId).bind("click",function(){ //must be binded 'cause loaded live with javascript'
					var objectTableRowSelected = $(this).closest('tr');
					ajax_check_code_duplicity(initiateModalDistribItem, objectTableRowSelected);
					return false; //avoid page refresh
			});
	}
	
	function createEventClickDeleteItemButton(itemId,warehouseId){
		$('#btnDeleteItem'+itemId+'w'+warehouseId).bind("click",function(e){ //must be binded 'cause loaded live with javascript'
					var objectTableRowSelected = $(this).closest('tr');
//					deleteItem(objectTableRowSelected);
					ajax_check_code_duplicity(deleteItem, objectTableRowSelected);//passing callback as a parameter into another function
					//return false; //avoid page refresh
					e.preventDefault();
		});
	}
	
	function deleteItem(result, objectTableRowSelected){
		var arrayItemsDetails = getItemsDetails();
		var error = validateBeforeSaveAll([{0:0}], result);//Send [{0:0}] 'cause I won't use arrayItemsDetails classic validation, I will use it differently for this case (as done below)
		if(arrayItemsDetails.length === 1){error+='<li> Debe existir al menos 1 "Item" </li>';}
		if( error === ''){
			showBittionAlertModal({content:'¿Está seguro de eliminar este item?'});
			$('#bittionBtnYes').click(function(){
				if(urlAction === 'save_order'){
					ajax_save_movement('DELETE', 'NOTE_PENDANT', objectTableRowSelected, []);
				}
				if(urlAction === 'save_invoice'){
					ajax_save_movement('DELETE', 'SINVOICE_PENDANT', objectTableRowSelected, []);
				}
				return false; //avoid page refresh
			});
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}
	}
	
	function deletePay(result, objectTableRowSelected){
		//var arrayPaysDetails = getPaysDetails();
		var error = validateBeforeSaveAll([{0:0}], result);//Send [{0:0}] 'cause I won't use arrayItemsDetails classic validation, I will use it differently for this case (as done below)
		if( error === ''){
			showBittionAlertModal({content:'¿Está seguro de eliminar este pago?'});
			$('#bittionBtnYes').click(function(){
				if(urlAction === 'save_order'){
					ajax_save_movement('DELETE_PAY', 'NOTE_PAY', objectTableRowSelected, []);
				}
				if(urlAction === 'save_invoice'){
					ajax_save_movement('DELETE_PAY', 'SINVOICE_PAY', objectTableRowSelected, []);
				}
				return false;
			});
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}
	}
	
	function createEventClickEditPayButton(dateId){
			$('#btnEditPay'+dateId).bind("click",function(){ //must be binded 'cause loaded live with javascript'
					var objectTableRowSelected = $(this).closest('tr');
//					startEventsWhenExistsDebts();
//					initiateModalEditPay(objectTableRowSelected);
					ajax_check_code_duplicity(initiateModalEditPay, objectTableRowSelected);//passing callback as a parameter into another function
					return false; //avoid page refresh
			});
	}
	
	function createEventClickDeletePayButton(dateId){
		$('#btnDeletePay'+dateId).bind("click",function(){ //must be binded 'cause loaded live with javascript'
					var objectTableRowSelected = $(this).closest('tr');
//					deletePay(objectTableRowSelected);
					ajax_check_code_duplicity(deletePay, objectTableRowSelected);//passing callback as a parameter into another function
					return false; //avoid page refresh
		});
	}
	
	// (GC Ztep 3) function to fill Items list when saved in modal triggered by addItem() //type="hidden"
	function createRowItemTable(itemId, itemCodeName, salePrice, quantity, backorder, warehouse, warehouseId, /*stockVirtual, stockReal,*/ subtotal){
		var row = '<tr id="itemRow'+itemId+'w'+warehouseId+'" >';
		row +='<td><span id="spaItemName'+itemId+'">'+itemCodeName+'</span><input type="hidden" value="'+itemId+'" id="txtItemId" ></td>';
		row +='<td><span id="spaSalePrice'+itemId+'w'+warehouseId+'">'+salePrice+'</span></td>';
		row +='<td><span id="spaAvaQuantity'+itemId+'w'+warehouseId+'">'+(quantity-backorder)+'</span></td>';
		if(backorder > 0){
			row +='<td><span id="spaBOQuantity'+itemId+'w'+warehouseId+'" style="color:red">'+backorder+'</span></td>';
		}else{
			row +='<td><span id="spaBOQuantity'+itemId+'w'+warehouseId+'">'+backorder+'</span></td>';
		}	
		row +='<td><span id="spaQuantity'+itemId+'w'+warehouseId+'">'+quantity+'</span></td>';
		row +='<td><span id="spaSubtotal'+itemId+'w'+warehouseId+'">'+subtotal+'</span></td>';
		row +='<td><span id="spaWarehouse'+itemId+'w'+warehouseId+'">'+warehouse+'</span><input type="hidden" value="'+warehouseId+'" id="txtWarehouseId'+itemId+'" ></td>';
//		if(stockVirtual > 0){	
//			row +='<td><span id="spaVirtualStock'+itemId+'w'+warehouseId+'">'+stockVirtual+'</span></td>';
//		}else{
//			row +='<td><span id="spaVirtualStock'+itemId+'w'+warehouseId+'" style="color:red">'+stockVirtual+'</span></td>';
//		}	
//		row +='<td><span id="spaStock'+itemId+'w'+warehouseId+'">'+stockReal+'</span></td>';
		row +='<td class="columnItemsButtons">';
		row +='<a class="btn btn-primary" href="#" id="btnEditItem'+itemId+'w'+warehouseId+'" title="Editar"><i class="icon-pencil icon-white"></i></a> ';
//		if(urlAction === 'save_invoice'){
			row +='<a class="btn btn-info" href="#" id="btnDistribItem'+itemId+'w'+warehouseId+'" title="Distribuir"><i class="icon-resize-full icon-white"></i></a> ';
//		}	
		row +='<a class="btn btn-danger" href="#" id="btnDeleteItem'+itemId+'w'+warehouseId+'" title="Eliminar"><i class="icon-trash icon-white"></i></a>';
		row +='</td>';
		row +='</tr>';
		$('#tablaItems tbody').prepend(row);
	}
	
	function createRowTotalTable(trId, label, h4Id, amount, color){
		color = typeof color !== 'undefined' ? color : 'inherit';
		var row = '<tr id="'+trId+'" style="color:'+color+'">';
		row +='<td></td>';
		row +='<td></td>';
			row +='<td colspan="3"><h4>'+label+':</h4></td>';
			row +='<td><h4 id="'+h4Id+'">'+amount+' Bs.</td>';		
		row +='<td></td>';
		row +='<td></td>';
//		row +='<td></td>';
//		row +='<td></td>';
		row +='</tr>';
		$('#tablaItems tfoot').append(row);
	}
	//row +='<td><span id="spaBOQuantity'+itemId+'w'+warehouseId+'" style="color:red">'+backorder+'</span></td>';
	
	function createRowPayTable(dateId, payDate, payAmount, payDescription){
		var row = '<tr id="payRow'+dateId+'" >';
		row +='<td><span id="spaPayDate'+dateId+'">'+payDate+'</span><input type="hidden" value="'+dateId+'" id="txtPayDate" ></td>';
		row +='<td><span id="spaPayAmount'+dateId+'">'+payAmount+'</span></td>';
		row +='<td><span id="spaPayDescription'+dateId+'">'+payDescription+'</span></td>';
		row +='<td class="columnPaysButtons">';
		row +='<a class="btn btn-primary" href="#" id="btnEditPay'+dateId+'" title="Editar"><i class="icon-pencil icon-white"></i></a> ';
		row +='<a class="btn btn-danger" href="#" id="btnDeletePay'+dateId+'" title="Eliminar"><i class="icon-trash icon-white"></i></a>';
		row +='</td>';
		row +='</tr>';
		$('#tablaPays > tbody:last').append(row);
	}
	
	
	
//	function updateMultipleStocks(arrayItemsStocks, controlName){
//		var auxItemsStocks = [];
//		for(var i=0; i<arrayItemsStocks.length; i++){
//			auxItemsStocks = arrayItemsStocks[i].split('=>');//  item5=>9stock
//			$('#'+controlName+auxItemsStocks[0]).text(auxItemsStocks[1]);  //update only if quantities are APPROVED
//		}
//	}
	
	// Triggered when Guardar Modal button is pressed
	function addItem(){	
		var warehouse = $('#cbxModalWarehouses option:selected').text();
		var itemCodeName = $('#cbxModalItems option:selected').text();
		var salePrice = $('#txtModalPrice').val();
		var quantity = $('#txtModalQuantity').val();
		var error = validateItem(warehouse, itemCodeName, salePrice, quantity); 
		if(error === ''){
			if(urlAction === 'save_order'){
				ajax_save_movement('ADD', 'NOTE_PENDANT', '', []);
			}
			if(urlAction === 'save_invoice'){
				ajax_save_movement('ADD', 'SINVOICE_PENDANT', '', []);
			}
		}else{
			$('#boxModalValidateItem').html('<ul>'+error+'</ul>');
		}
	}
	
	function editItem(){
		var warehouse = $('#cbxModalWarehouses option:selected').text();
		var itemCodeName = $('#cbxModalItems option:selected').text();	
		var salePrice = $('#txtModalPrice').val();
		var quantity = $('#txtModalQuantity').val();	
//		var cifPrice = $('#txtCifPrice').val();
//		var exCifPrice = $('#txtCifExPrice').val();	
		var backorder = $('#txtModalBOQuantity').val();
		var lastBackorder = $('#txtModalLastBOQuantity').val();
		var virtualStock = $('#txtModalVirtualStock').val();
		var lastQuantity = $('#txtModalLastQuantity').val();
		var approvedQuantity = $('#txtModalApprovedQuantity').val();
		
		var error = validateItem(warehouse, itemCodeName, salePrice, quantity, backorder, lastBackorder, virtualStock, lastQuantity, approvedQuantity); 
		if(error === ''){
			if(urlAction === 'save_order'){
				ajax_save_movement('EDIT', 'NOTE_PENDANT', '', []);
			}
			if(urlAction === 'save_invoice'){
				ajax_save_movement('EDIT', 'SINVOICE_PENDANT', '', []);
			}	
		}else{
			$('#boxModalValidateItem').html('<ul>'+error+'</ul>');
		}
	}
	
	function distribItem(){
		var warehouseDest = $('#cbxModalWarehousesDestDistrib option:selected').text();
		var itemCodeName = $('#cbxModalItemsDistrib option:selected').text();	
		var quantity = $('#txtModalQuantityDistrib').val();
		var quantityToDistrib = $('#txtModalQuantityToDistrib').val();	
		var error = validateItemDistrib(warehouseDest, itemCodeName, quantity, quantityToDistrib); 
		if(error === ''){
			if(urlAction === 'save_order'){
				ajax_save_movement('DISTRIB', 'NOTE_PENDANT', '', []);
			}
			if(urlAction === 'save_invoice'){
				ajax_save_movement('DISTRIB', 'SINVOICE_PENDANT', '', []);
			}
		}else{
			$('#boxModalValidateItemDistrib').html('<ul>'+error+'</ul>');
		}
	}
	
	function addPay(){
		var payDate = $('#txtModalDate').val();
		var payAmount = $('#txtModalPaidAmount').val();
		var payDebt = $('#txtModalDebtAmount').val();
		var documentState = $('#inputDocumentState').val();
		var error = validateAddPay(payDate, parseFloat(payAmount).toFixed(2), parseFloat(payDebt).toFixed(2), documentState);  
		if(error === ''){
			if(urlAction === 'save_order'){
				ajax_save_movement('ADD_PAY', 'NOTE_PAY', '', []);
			}
			if(urlAction === 'save_invoice'){
				ajax_save_movement('ADD_PAY', 'SINVOICE_PAY', '', []);
			}
		}else{
			$('#boxModalValidatePay').html('<ul>'+error+'</ul>');
		}
	}
	
	function editPay(){
		var payDate = $('#txtModalDate').val();
		var payAmount = $('#txtModalPaidAmount').val();
		var payHiddenAmount = $('#txtModalAmountHidden').val();
		var payDebt = $('#txtModalDebtAmount').val();
		var documentState = $('#inputDocumentState').val();
		var error = validateEditPay(payDate, parseFloat(payAmount).toFixed(2), parseFloat(payHiddenAmount).toFixed(2), parseFloat(payDebt).toFixed(2), documentState);  
		if(error === ''){
			if(urlAction === 'save_order'){
				ajax_save_movement('EDIT_PAY', 'NOTE_PAY', '', []);
			}
			if(urlAction === 'save_invoice'){
				ajax_save_movement('EDIT_PAY', 'SINVOICE_PAY', '', []);
			}
		}else{
			$('#boxModalValidatePay').html('<ul>'+error+'</ul>');
		}
	}
	//esto suma todos los subtotales y retorna el total	
	function getTotal(){
		var arrayAux = [];
		var total = 0;
//		var discount = $('#txtDiscount').val();
		arrayAux = getItemsDetails();
		if(arrayAux[0] !== 0){
			for(var i=0; i< arrayAux.length; i++){
				 var salePrice = (arrayAux[i]['sale_price']);
				 var quantity = (arrayAux[i]['quantity']);
				 total = total + (salePrice*quantity);
			}
		}
		
//		if(discount !== 0){
//			total = total-(total*(discount/100));
//		}
		
		return parseFloat(total).toFixed(2); 	
	}
	
	function getTotalDebt(){
		var arrayAux = [];
		var total = 0;
		var discount = $('#txtDiscount').val();
		arrayAux = getItemsDetails();
		if(arrayAux[0] !== 0){
			for(var i=0; i< arrayAux.length; i++){
				 var salePrice = (arrayAux[i]['sale_price']);
				 var quantity = (arrayAux[i]['quantity']);
				 total = total + (salePrice*quantity);
			}
		}
		
		if(discount !== 0){
			total = total-(total*(discount/100));
		}
		
		return parseFloat(total).toFixed(2); 	
	}
	//esto sume todos los pagos y devuelve el total
	function getTotalPay(){
		var arrayAux = [];
		var total = 0;
		arrayAux = getPaysDetails();
		if(arrayAux[0] !== 0){
			for(var i=0; i< arrayAux.length; i++){
				 var amount = (arrayAux[i]['amount']);
//				 var quantity = (arrayAux[i]['quantity']);
				 total = total + Number(amount);
			}
		}
		return parseFloat(total).toFixed(2); 	
	}
	
	//get all items for save a purchase
	function getItemsDetails(){		
		var arrayItemsDetails = [];
		var itemId = '';
		var itemSalePrice = '';
		var itemQuantity = '';
		var itemWarehouseId = '';
		var itemBackorder = '';
//		var itemCifPrice = '';
//		var itemExCifPrice = '';
		var exRate = $('#txtExRate').val();
	
		var itemExSalePrice = '';	//??????????????????????
		
		$('#tablaItems tbody tr').each(function(){		
			itemId = $(this).find('#txtItemId').val();
			itemWarehouseId = $(this).find('#txtWarehouseId'+itemId).val();
			itemSalePrice = $(this).find('#spaSalePrice'+itemId+'w'+itemWarehouseId).text();
			itemQuantity = $(this).find('#spaQuantity'+itemId+'w'+itemWarehouseId).text();
			itemBackorder = $(this).find('#spaBOQuantity'+itemId+'w'+itemWarehouseId).text();
			
//			itemCifPrice = $(this).find('#txtCifPrice').val();
//			itemExCifPrice = $(this).find('#txtCifExPrice').val();
			itemExSalePrice = itemSalePrice / exRate;//?????????????????????????
			arrayItemsDetails.push({'inv_item_id':itemId, 'sale_price':itemSalePrice, 'quantity':itemQuantity, 'inv_warehouse_id':itemWarehouseId, 'ex_sale_price':parseFloat(itemExSalePrice).toFixed(2), 'backorder':itemBackorder});
			
		});
		
		if(arrayItemsDetails.length === 0){  //For fix undefined index
			arrayItemsDetails = [0]; //if there isn't any row, the array must have at least one field 0 otherwise it sends null
		}
		
		return arrayItemsDetails; 		
	}
	
	function getPaysDetails(){		
		var arrayPaysDetails = [];
		var dateId = '';
		var payDate = '';
		var payAmount = '';
		var payDescription = '';
		
		$('#tablaPays tbody tr').each(function(){		
			dateId = $(this).find('#txtPayDate').val();
			payDate = $(this).find('#spaPayDate'+dateId).text();
			payAmount = $(this).find('#spaPayAmount'+dateId).text();
			payDescription = $(this).find('#spaPayDescription'+dateId).text();
			
			arrayPaysDetails.push({'date':dateId, 'amount':payAmount,'description':payDescription});
		});
		
		if(arrayPaysDetails.length === 0){  //For fix undefined index
			arrayPaysDetails = [0]; //if there isn't any row, the array must have at least one field 0 otherwise it sends null
		}
		
		return arrayPaysDetails; 		
	}
	
	//show message of procesing for ajax
	function showProcessing(){
        $('#processing').text("Procesando...");
    }
	
	function showGrowlMessage(type, text, sticky){
		if(typeof(sticky)==='undefined') sticky = false;
		
		var title;
		var image;
		switch(type){
			case 'ok':
				title = 'EXITO!';
				image= urlImg+'check.png';
				break;
			case 'error':
				title = 'OCURRIO UN PROBLEMA!';
				image= urlImg+'error.png';
				break;
			case 'warning':
				title = 'PRECAUCIÓN!';
				image= urlImg+'warning.png';
				break;
		}
		$.gritter.add({
			title:	title,
			text: text,
			sticky: sticky,
			image: image
		});	
	}
	
	function saveAll(result){
		var arrayItemsDetails = [];
		arrayItemsDetails = getItemsDetails();
//		var arrayCostsDetails = [];
//		arrayCostsDetails = getCostsDetails();
//		var arrayPaysDetails = [];
//		arrayPaysDetails = getPaysDetails();
		var error = validateBeforeSaveAll(arrayItemsDetails, result);
		if( error === ''){
			if(urlAction === 'save_order'){
				ajax_save_movement('DEFAULT', 'NOTE_PENDANT', '', []);
			}
			if(urlAction === 'save_invoice'){
				ajax_save_movement('DEFAULT', 'SINVOICE_PENDANT', '', []);
			}
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}
	}
	
	// (AEA Ztep 2) action when button Aprobar Entrada Almacen is pressed
//	function changeStateApproved(result){
//		showBittionAlertModal({content:'Al APROBAR este documento ya no se podrá hacer más modificaciones. ¿Está seguro?'});
//		$('#bittionBtnYes').click(function(){
//			var arrayForValidate = [];
//			arrayForValidate = getItemsDetails();
//			var error = validateBeforeSaveAll(arrayForValidate);
//			if( error === ''){
//				if(urlAction === 'save_order'){
//					ajax_save_movement('DEFAULT', 'NOTE_APPROVED', '', arrayForValidate);
//				}
//				if(urlAction === 'save_invoice'){
//					ajax_save_movement('DEFAULT', 'SINVOICE_APPROVED', '', arrayForValidate);
//				}
//			}else{
//				$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
//			}
//			hideBittionAlertModal();
//		});
//	}
	
	function changeStateApproved(result, payDebt, backorderSum){
		var arrayForValidate = [];
		arrayForValidate = getItemsDetails();
		var error = validateBeforeSaveAll(arrayForValidate, result);
		if(Number(backorderSum) === 0){
			error += validateBeforeSaveAll3(payDebt);
		}	
		if( error === ''){
//			if(urlAction === 'save_order'){
//				showBittionAlertModal({content:'Al APROBAR este documento ya no se podrá hacer más modificaciones. ¿Está seguro?'});
//				$('#bittionBtnYes').click(function(){
//					ajax_save_movement('DEFAULT', 'NOTE_APPROVED', ''/*, arrayForValidate*/);
//					hideBittionAlertModal();
//				});	
//			}
//			if(urlAction === 'save_invoice'){
//				startEventsWhenExistsDebts();
//				if(result === 'approve'){
//					if(payDebt == 0){
								showBittionAlertModal({content:'Al APROBAR este documento ya no se podrá hacer más modificaciones. ¿Está seguro?'});
								$('#bittionBtnYes').click(function(){
									ajax_save_movement('DEFAULT', 'SINVOICE_APPROVED', '', arrayForValidate);
								hideBittionAlertModal();
								});		
//					}else{
//						showBittionAlertModal({content:'No puede aprobar esta factura de venta. <br><br>Primero debe cancelar todos los pagos pendientes.', btnYes:'Aceptar', btnNo:''});
//						$('#bittionBtnYes').click(function(){
//							hideBittionAlertModal();
//						});
//					}				
//				}else{
//					showBittionAlertModal({content:'No puede aprobar esta factura de venta. <br><br>Primero deben aprobar el/los movimiento(s) relacionados a esta factura de venta.', btnYes:'Aceptar', btnNo:''});
//					$('#bittionBtnYes').click(function(){
//						hideBittionAlertModal();
//					});
//				}	
//			}
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}	
	}
	
	function reserveNote(result){
		var arrayForValidate = [];
		arrayForValidate = getItemsDetails();
		var error = validateBeforeSaveAll(arrayForValidate, result);
		if( error === ''){
			if(urlAction === 'save_order'){
//				alert('fuck!');
				showBittionAlertModal({content:'Reservar. ¿Está seguro?'});
//				alert('you!');
				$('#bittionBtnYes').click(function(){
//					alert('asshole!');
					ajax_save_movement('DEFAULT', 'NOTE_RESERVED', '',[]/*, arrayForValidate*/);
//					alert('aca se caga doto');
//						if($('#bittionAlertModal').hasClass('in')){
//							$('#bittionAlertModal').modal('hide');
//						}
//						if($('#bittionAlertModal').is('no se puede seguir editando bla bla bla')){
//							$('#bittionAlertModal').modal('hide');
//						}
					hideBittionAlertModal();
				});	
			}
//			if(urlAction === 'save_invoice'){
//				startEventsWhenExistsDebts();
//				if(result === 'approve'){
//					if(payDebt == 0){
//								showBittionAlertModal({content:'Al APROBAR este documento ya no se podrá hacer más modificaciones. ¿Está seguro?'});
//								$('#bittionBtnYes').click(function(){
//									ajax_save_movement('DEFAULT', 'SINVOICE_APPROVED', ''/*, arrayForValidate*/);
//								hideBittionAlertModal();
//								});		
//					}else{
//						showBittionAlertModal({content:'No puede aprobar esta factura de venta. <br><br>Primero debe cancelar todos los pagos pendientes.', btnYes:'Aceptar', btnNo:''});
//						$('#bittionBtnYes').click(function(){
//							hideBittionAlertModal();
//						});
//					}				
//				}else{
//					showBittionAlertModal({content:'No puede aprobar esta factura de venta. <br><br>Primero deben aprobar el/los movimiento(s) relacionados a esta factura de venta.', btnYes:'Aceptar', btnNo:''});
//					$('#bittionBtnYes').click(function(){
//						hideBittionAlertModal();
//					});
//				}	
//			}
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}	
	}
	// (CEA Ztep 2) action when button Cancelar Entrada Almacen is pressed
//	function changeStateCancelled(){
//		showBittionAlertModal({content:'Al CANCELAR este documento ya no será válido y no habrá marcha atrás. ¿Está seguro?'});
//		$('#bittionBtnYes').click(function(){
////			var arrayItemsDetails = [];
////			arrayItemsDetails = getItemsDetails();
////			var arrayPaysDetails = [];
////			arrayPaysDetails = getPaysDetails();
//			var arrayForValidate = [];
//			arrayForValidate = getItemsDetails();
//			if(urlAction === 'save_order'){
//				ajax_save_movement('DEFAULT', 'NOTE_CANCELLED', '', arrayForValidate);
//			}
//			if(urlAction === 'save_invoice'){
//				ajax_save_movement('DEFAULT', 'SINVOICE_CANCELLED', '', arrayForValidate);
//			}
//			hideBittionAlertModal();
//		});
//	}
	
	function changeStateCancelled(/*result*/){
		if(urlAction === 'save_order'){
//			if(result === 'proceed'){
				showBittionAlertModal({content:'Al CANCELAR este documento ya no será válido y no habrá marcha atrás. ¿Está seguro?'});
				$('#bittionBtnYes').click(function(){
					ajax_save_movement('DEFAULT', 'NOTE_CANCELLED', '', []/*, arrayForValidate*/);
					hideBittionAlertModal();
				});
//			}else{
//				showBittionAlertModal({content:'No puede cancelar esta orden de compra. <br><br>Primero debe eliminar/cancelar la factura y movimiento(s) relacionados a esta orden de compra.', btnYes:'Aceptar', btnNo:''});
//				$('#bittionBtnYes').click(function(){
//					hideBittionAlertModal();
//				});
//			}

		}
		if(urlAction === 'save_invoice'){
//			if(result === 'cancell'){
				showBittionAlertModal({content:'Al CANCELAR este documento ya no será válido y no habrá marcha atrás. ¿Está seguro?'});
				$('#bittionBtnYes').click(function(){
					ajax_save_movement('DEFAULT', 'SINVOICE_CANCELLED', '', []/*, arrayForValidate*/);
					hideBittionAlertModal();
				});
//			}else{
//				showBittionAlertModal({content:'No puede cancelar esta factura de venta. <br><br>Primero debe cancelar el/los movimiento(s) relacionados a esta factura de venta.', btnYes:'Aceptar', btnNo:''});
//				$('#bittionBtnYes').click(function(){
//					hideBittionAlertModal();
//				});
//			}
		}
	}
	
	function changeStateLogicDeleted(){
		if($('#inputDelivered').val() !== '1'){
			showBittionAlertModal({content:'¿Está seguro de eliminar este documento en estado Pendiente?'});
			$('#bittionBtnYes').click(function(){
				var purchaseId = $('#txtPurchaseIdHidden').val();
				var genCode = $('#txtGenericCode').val();
				var delivered = $('#inputDelivered').val();
	//			var purchaseId2=0;
				var type;
	//			var type2=0;
				var index;
				switch(urlAction){
					case 'save_order':
						index = 'index_order';
						type = 'NOTE_LOGIC_DELETED';
						break;	
					case 'save_invoice':
						index = 'index_invoice';
						type = 'SINVOICE_LOGIC_DELETED';
						break;	
				}
				ajax_logic_delete(purchaseId, type, index, genCode, delivered);
				hideBittionAlertModal();
			});
		}else if($('#inputDelivered').val() === '1'){
			showBittionAlertModal({content:'¿Está seguro de cancelar este documento en estado Entregado?'});
			$('#bittionBtnYes').click(function(){
				var purchaseId = $('#txtPurchaseIdHidden').val();
				var genCode = $('#txtGenericCode').val();
				var delivered = $('#inputDelivered').val();
	//			var purchaseId2=0;
				var type;
	//			var type2=0;
				var index;
				switch(urlAction){
					case 'save_order':
						index = 'index_order';
						type = 'NOTE_LOGIC_DELETED';
						break;	
					case 'save_invoice':
						index = 'index_invoice';
						type = 'SINVOICE_LOGIC_DELETED';
						break;	
				}
				ajax_logic_delete(purchaseId, type, index, genCode, delivered);
				hideBittionAlertModal();
			});
		}
	}
	
	function changeStateReserved(){ 
		showBittionAlertModal({content:'¿Está seguro de editar este documento en estado Reservado?'});
		$('#bittionBtnYes').click(function(){
			var saleId = $('#txtPurchaseIdHidden').val();
			var genCode = $('#txtGenericCode').val();
			var reserve;
			var action;
			switch(urlAction){
				case 'save_order':
					reserve = false;
					action = 'save_order';
					break;	
				case 'save_invoice':
					reserve = true;
					action = 'save_invoice';
					break;	
			}
			ajax_change_reserved(saleId, reserve, genCode, action/*, index, genCode*/);
			hideBittionAlertModal();
		});
	}
	//************************************************************************//
	//////////////////////////////////END-FUNCTIONS//////////////////////
	//************************************************************************//
	
	
	
	
	//************************************************************************//
	//////////////////////////////////BEGIN-CONTROLS EVENTS/////////////////////
	//************************************************************************//
//	$('#txtModalPrice').keydown(function(event) {
//			validateOnlyFloatNumbers(event);			
//	});
	$('#txtModalQuantity').keydown(function(event) {
			validateOnlyIntegers(event);			
	});
//	$('#txtDiscount').keydown(function(event) {
//			validateOnlyIntegers(event);			
//	});
	$('#txtDiscount').keypress(function(event){
		if($.browser.mozilla === true){
			if (event.which === 8 || event.keyCode === 37 || event.keyCode === 39 || event.keyCode === 9 || event.keyCode === 16 || event.keyCode === 46){
				return true;
			}
		}
		if ((event.which !== 46 || $(this).val().indexOf('.') !== -1) && (event.which < 48 || event.which > 57)) {
			event.preventDefault();
		}
	});
//	$('#txtModalPaidAmount').keydown(function(event) {
//			validateOnlyFloatNumbers(event);			
//	});
	//Calendar script
	$("#txtDate").datepicker({
	  showButtonPanel: true
	});
	
	$('#txtDate').focusout(function() {
			ajax_update_ex_rate();			
	});
	
	$('#txtModalQuantityToDistrib').keydown(function(event) {
			validateOnlyIntegers(event);			
	});
	
	$('#txtInvoicePercent').keydown(function(event) {
			validateOnlyIntegers(event);			
	});
	
	function ajax_update_ex_rate(){
		$.ajax({
		    type:"POST",
		    url:urlModuleController + "ajax_update_ex_rate",			
		    data:{date: $("#txtDate").val()},
		    beforeSend: showProcessing(),
				success:function(data){
					$("#processing").text("");
					$("#boxExRate").html(data);
				}
		});
    }
	
//	$("#txtModalDate").datepicker({
//	  showButtonPanel: true
//	});
	
//	$("#txtModalDueDate").datepicker({
//	  showButtonPanel: true
//	});
	
	$('#btnAddItemSO').click(function(){
		ajax_check_code_duplicity(initiateModalAddItemSO);
		return false; //avoid page refresh
	});
	
	//Call modal
	$('#btnAddItem').click(function(){
//		itemsListWhenExistsItems();			//NEEDS TO BE RUN BEFORE MODAL TO UPDATE ITEMS LIST BY WAREHOUSE
//		warehouseListWhenExistsItems();	//NEEDS TO BE RUN BEFORE MODAL TO UPDATE ITEMS LIST BY WAREHOUSE
//		initiateModalAddItem();
		ajax_check_code_duplicity(initiateModalAddItem);//passing callback as a parameter into another function
		return false; //avoid page refresh
	});
	
	//function when button Guardar on the modal is pressed
	$('#btnModalAddItem').click(function(){
		addItem();
		return false; //avoid page refresh
	});
	
	//edit an existing item quantity
	$('#btnModalEditItem').click(function(){
		editItem();
		return false; //avoid page refresh
	});
	
	//edit an existing item quantity
	$('#btnModalDistribItem').click(function(){
		distribItem();
		return false; //avoid page refresh
	});
	
	//saves all order
	$('#btnSaveAll').click(function(){
//		saveAll();
		ajax_check_code_duplicity(saveAll);//passing callback as a parameter into another function
		return false; //avoid page refresh
	});
	
	//function triggered when PAYS plus icon is clicked
	$('#btnAddPay').click(function(){
//		startEventsWhenExistsDebts();
//		initiateModalAddPay();
		ajax_check_code_duplicity(initiateModalAddPay);//passing callback as a parameter into another function
		return false; //avoid page refresh
	});
	
	$('#btnModalAddPay').click(function(){
		addPay();
		return false; //avoid page refresh
	});
	
	//edit an existing item quantity
	$('#btnModalEditPay').click(function(){
		editPay();
		return false; //avoid page refresh
	});
	////////////////
	
	// action when button Aprobar Entrada is pressed
	$('#btnApproveState').click(function(){
//		changeStateApproved();
//		ajax_check_document_state(changeStateApproved);
//		ajax_check_code_duplicity(changeStateApproved);
		ajax_check_code_duplicity_pays_coherency(changeStateApproved);
		return false;
	});
	// (CEA Ztep 1) action when button Cancelar Entrada Almacen is pressed
	$('#btnCancellState').click(function(){
		//alert('Se cancela entrada');
		changeStateCancelled();
//		ajax_check_document_state1(changeStateCancelled);//no es necesario pq cuando esta aprobado no se puede cambiar el nro de factura
		return false;
	});
	
	$('#btnLogicDeleteState').click(function(){
		changeStateLogicDeleted();
		return false;
	});
	
	$('#btnEditReservedNote').click(function(){
		changeStateReserved();
		return false;
	});
	
	$('#btnGoMovements').click(function(){
		window.location = '../../inv_movements/index_sale_out/note_code:'+ $('#txtNoteCode').val() +'/search:yes';
		return false;
	});
	
	
	$('#btnReserveNote').click(function(){
		ajax_check_code_duplicity(reserveNote);
		return false;
	});

    $('#btnGenerateClonePendant').click(function(){
        generateClonePendant();
        return false;
    });

//	fnBittionSetSelectsStyle();
	
//	$('#cbxSuppliers').data('pre', $(this).val());
//	$('#cbxSuppliers').change(function(){
//	var supplier = $(this).data('pre');
//		deleteList(supplier);
//	$(this).data('pre', $(this).val());
//		return false; //avoid page refresh
//	});
  
	//accion al seleccionar un cliente
	$('#cbxCustomers').change(function(){
        ajax_list_controllers_inside();		
    });
	
	$('#txtDate').keydown(function(e){e.preventDefault();});
	$('#txtModalDate').keypress(function(){return false;});
	$('#txtCode').keydown(function(e){e.preventDefault();});
//	$('#txtOriginCode').keydown(function(e){e.preventDefault();});
	
	
	$("#chkInv").change(function() {
		if(this.checked) {
			$('#boxInvoice').show();
			$('#tab3li').show();
//			$('#tab1li').removeClass('active');
//			$('#tab1').removeClass('active');
//			$('#tab2li').removeClass('active');
//			$('#tab2').removeClass('active');
//			$('#tab3li').addClass('active');
//			$('#tab3').addClass('active');
		}else{
			$('#boxInvoice').hide();
			$('#tab3li').hide();
			$('#tab2li').removeClass('active');
			$('#tab3li').removeClass('active');
			$('#tab1li').addClass('active');
			$('#tab2').removeClass('active');
			$('#tab3').removeClass('active');
			$('#tab1').addClass('active');
		}
	});
	
	$("input[type=radio][name='radio']").change(function() {
		if(this.value === '1') {
			$('#boxDiscount').hide();
			$('#txtDiscount').val('0.00');
		}else if(this.value === '2') {
			$('#boxDiscount').show();
			$('#boxDiscount span').text('%');
		}else if(this.value === '3') {
			$('#boxDiscount').show();
			$('#boxDiscount span').text('Bs.');
		}
	});
	
	$('#appBtnInvoice').click(function(){
		if($('#totalDisc').length){
			var total = parseFloat($('#totalDisc').text());
		}else{
			var total = parseFloat($('#total').text());
		}
		var tax = $('#txtInvoicePercent').val();
		var taxAmnt = (total*tax)/100;
		
		if($('#taxAmnt').length || $('#totalTax').length){//EDIT
			$('#taxAmnt').text(parseFloat(taxAmnt).toFixed(2)+' Bs.');
			$('#totalTax').text(parseFloat(total - taxAmnt).toFixed(2)+' Bs.');	
		}else{//ADD
			createRowTotalTable('taxTr', 'Impuesto', 'taxAmnt', parseFloat(taxAmnt).toFixed(2), 'gray');
			createRowTotalTable('totalTaxTr', 'Total', 'totalTax', parseFloat(total - taxAmnt).toFixed(2), 'gray');
		}
	
//		alert(total);
//		var total = getTotal();
//		if(discount > 0 && discountType !== '1'){
//				if(discountType === '2'){//%
//					var discAmnt = (total*discount)/100;
//				}else if(discountType === '3'){//$us.
//					var discAmnt = discount;
//				}
//				if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
//					$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' $us.');
//					$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' $us.');					
//				}else{//ADD
//					$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
//					createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
//					createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
//				}	
//		}else{//REMOVE if 0
//			if( $('#discAmnt').length || $('#totalDisc').length ) {
//				$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//				$('#discountTr').remove();
//				$('#totalTr').remove();
//			}
//		}
	return false;
	});
	
	function ajax_list_controllers_inside(){
        $.ajax({
            type:"POST",
            url:urlModuleController + "ajax_list_controllers_inside",			
            data:{customer: $("#cbxCustomers").val()},
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
	
	function ajax_check_document_state(callback){
		$.ajax({
		    type:"POST",
		    url:urlModuleController + "ajax_check_document_state",			
		    data:{action: urlAction,
				purchaseId: $('#txtPurchaseIdHidden').val()
				,genericCode: $('#txtGenericCode').val()},
		    beforeSend: showProcessing(),
				success: function(data){
					$("#processing").text("");					
					callback(data); 
				}
		});
	}
	
	function ajax_check_document_state1(callback){
		$.ajax({
		    type:"POST",
		    url:urlModuleController + "ajax_check_document_state1",			
		    data:{action: urlAction,
				purchaseId: $('#txtPurchaseIdHidden').val()
				,genericCode: $('#txtGenericCode').val()},
		    beforeSend: showProcessing(),
				success: function(data){
					$("#processing").text("");					
					callback(data); 
				}
		});
	}

//	$('#txtDate').keypress(function(e){e.preventDefault();});
//	$('#txtModalDate').keypress(function(){return false;});
//	$('#txtModalDueDate').keypress(function(){return false;});
//	$('#txtCode').keydown(function(e){e.preventDefault();});
//	$('#txtOriginCode').keydown(function(e){e.preventDefault();});
	//************************************************************************//
	//////////////////////////////////END-CONTROLS EVENTS//////////////////////
	//************************************************************************//
	
	
	
	
	//************************************************************************//
	//////////////////////////////////BEGIN-AJAX FUNCTIONS//////////////////////
	////************************************************************************//
	
	
	//*****************************************************************************************************************************//
	function setOnData(ACTION, OPERATION, STATE, objectTableRowSelected, arrayForValidate){
//		alert(ACTION);
//		alert(OPERATION);
//		alert(STATE);
		var DATA = [];
		//constants
		var purchaseId=$('#txtPurchaseIdHidden').val();
		var movementDocCode = $('#txtCode').val();
		var movementCode = $('#txtGenericCode').val();
		var noteCode=$('#txtNoteCode').val().toUpperCase();
		var date=$('#txtDate').val();
		var employee=$('#cbxEmployees').val();
		var taxNumber=$('#cbxTaxNumbers').val();
		var salesman=$('#cbxSalesman').val();
		var description=$('#txtDescription').val();
		var exRate=$('#txtExRate').val();
		var discountType=$('input[name=radio]:checked, #rdDiscount').val();//1.Ninguno 2.Porcentage 3.Monto 
		var discount=$('#txtDiscount').val();
		var invoiced=$('#chkInv').is(':checked');
		
		var invoiceNumber=$('#tabInvoiceNumber').val();
		var invoiceDescription=$('#tabDescription').val();
		//variables
		var warehouseId = 0;	//destiny warehouse (DISTRIB)
		var itemId = 0;
		var salePrice = 0.00;
		var warehouseLastOrigId = 0;	//used for EDIT and DISTRIB meaning last warehouse id and warehouse origin id respectively
		var quantityLastToDistrib = 0;	//used for EDIT and DISTRIB meaning last(previous) quantity and quantity to pass respectively 
		var quantity = 0;
		var backorder = 0;
		var backorderLastOrig = 0;	//used for EDIT and DISTRIB meaning last(previous) backorder and origin backorder respectively 
		var quantityApproved = 0;//txtModalApprovedQuantity
		var subtotal = 0.00;
		var dateId = '';
		var payDate = '';
		var payAmount = 0;
		var payDescription = '';
		//only used for ADD
		var warehouse = '';
		var itemCodeName = '';
		var stockVirtual = 0;//virtual stock (inv stock - sal stock)
//		var stockReal = 0;//real stock (inv stock)
//		var stockRealDest = 0;
		var stockVirtualOrig = 0;
		
		var arrayItemsDetails = [0];

		if(ACTION === 'save_invoice' && STATE === 'SINVOICE_APPROVED'){
			arrayItemsDetails = getItemsDetails();
		}
		//SaleDetails(Products) setup variables
		if(OPERATION === 'ADD' || OPERATION === 'EDIT' || OPERATION === 'ADD_PAY' || OPERATION === 'EDIT_PAY'){
			warehouseId = $('#cbxModalWarehouses').val();		
			itemId = $('#cbxModalItems').val();
			salePrice = $('#txtModalPrice').val();
			quantity = $('#txtModalQuantity').val();

			if(OPERATION === 'ADD_PAY' || OPERATION === 'EDIT_PAY'){
				payDate = $('#txtModalDate').val();
				var myDate = payDate.split('/');
				dateId = myDate[2]+"-"+myDate[1]+"-"+myDate[0];
				payAmount = $('#txtModalPaidAmount').val();
				payDescription = $('#txtModalDescription').val();
			}
			if(OPERATION === 'ADD'){				
				warehouse = $('#cbxModalWarehouses option:selected').text();
				itemCodeName = $('#cbxModalItems option:selected').text();
				stockVirtual = $('#txtModalVirtualStock').val();
//				stockReal = $('#txtModalRealStock').val();
				
				subtotal = Number(quantity) * Number(salePrice);
//				backorder = $('#txtModalBOQuantity').val();
//				if (backorder === 0){
//					var stockOp = 0;
//					if(stock > 0){
//						stockOp = stock;
//					}				
//					var backorderOp = Number(stockOp) - Number(quantity);
//					if(backorderOp >= 0){
//						backorder = 0;
//					}else{
//						backorder = Math.abs(backorderOp);
//					}
							      //virtual stock
					backorder = Number(stockVirtual) - Number(quantity);
					if(backorder < 0){
						backorder = Math.abs(backorder);
					}else{
						backorder = 0;
					}
					
//				}	
			}
			if(OPERATION === 'EDIT'){
				warehouseLastOrigId = $('#txtModalLastWarehouse').val();
				warehouse = $('#cbxModalWarehouses option:selected').text();
				quantityLastToDistrib = $('#txtModalLastQuantity').val();
//				stockReal = $('#txtModalRealStock').val();
				stockVirtual = $('#txtModalVirtualStock').val();
				backorder = $('#txtModalBOQuantity').val();
				backorderLastOrig = $('#txtModalLastBOQuantity').val();
				quantityApproved = $('#txtModalApprovedQuantity').val();
				
				if((warehouseId !== warehouseLastOrigId)){//CAMBIO DE WAREHOUSE
					backorder = Number(stockVirtual) - Number(quantity);
					if(backorder < 0){
						backorder = Math.abs(backorder);
					}else{
						backorder = 0;
					}
					
				}else if((quantity !== quantityLastToDistrib) && (backorder !== backorderLastOrig)){//CAMBIO DE cantidad y backorder
					//creo q no necesita nada				
				}else if(quantity !== quantityLastToDistrib){//CAMBIO DE cantidad						
//					var backorder = ((Number(quantityLastToDistrib) - Number(backorder)) - Number(quantity)) + Number(stockVirtual);
					var backorder = Number(stockVirtual) - Number(quantity);
//					backorder = Number(rest) + Number(stockVirtual/*Virtual*/);
					if(backorder < 0){
						backorder = Math.abs(backorder);
					}else{
						backorder = 0;
					}
				}else if(backorder !== backorderLastOrig){//CAMBIO DE backorder
					//creo q no necesita nada
				}
//				backorderLastOrig = Number(backorderLastOrig);
//				if(backorder === backorderLastOrig) {
//					if((quantity !== quantityLastToDistrib)/* || (warehouse !== warehouseLastOrigId)*/){//CAMBIO DE CANTIDAD
//						var rest = (Number(quantityLastToDistrib) - Number(backorder)) - Number(quantity);
//	//					backorder = /*Math.abs(*/Number(rest) + Number(stockVirtual)/*)*/;
////alert('2');
//						backorder = Number(rest) + Number(stockVirtual);
//						if(backorder < 0){
//							backorder = Math.abs(backorder);
//						}else{
//							backorder = 0;
//						}
//	//					stockVirtual = Number(rest) + Number(stockVirtual);
//					}
//				}
				
			}
		}
		
		if(OPERATION === 'DISTRIB'){
			warehouseId = $('#cbxModalWarehousesDestDistrib').val();				//warehouse destino
			warehouse = $('#cbxModalWarehousesDestDistrib option:selected').text();
			itemId = $('#cbxModalItemsDistrib').val();
			itemCodeName = $('#cbxModalItemsDistrib option:selected').text();
			salePrice = $('#txtModalPriceDistrib').val();
			quantity = $('#txtModalQuantityDistrib').val();						//actual quantity
			quantityLastToDistrib = $('#txtModalQuantityToDistrib').val();			//quantity to pass
			warehouseLastOrigId = $('#txtModalWarehouseOrigDistrib').val();		//warehouse origen
			
			stockVirtualOrig = $('#txtModalVirtualStockOrigDistrib').val();
			stockVirtual = $('#txtModalVirtualStockDestDistrib').val();				//virtual stock destiny
//			/*stockNew*/stockReal = $('#txtModalRealStockOrigDistrib').val();
//			stockRealDest = $('#txtModalRealStockDestDistrib').val();
			
//			stockOrigin = $('#txtModalStockOrigDistrib').val();
//			stockVirtual = $('#txtModalDestinyStockVirtual').val();//por adicionar stockVirtual		
			backorderLastOrig = $('#txtModalLastBOQuantityOrigDistrib').val();
			backorder = $('#txtModalLastBOQuantityDestDistrib').val();
			
			//////////////////////////////////////////EDIT DE ESTE ALMACEN ORIGEN///////////////////////////////////////////////////
					         //ultima cantidad		//backorder				//nueva cantidad
//			alert(quantity);				
//			alert(backorderLastOrig);
//			alert(quantity);
//			alert(quantityLastToDistrib);
//			var rest = (Number(quantity) - Number(backorderLastOrig)) - (Number(quantity)-Number(quantityLastToDistrib));
////			alert(stockVirtualOrig);
//					backorderLastOrig = Number(rest) + Number(stockVirtualOrig);
//					if(backorderLastOrig < 0){
//						backorderLastOrig = Math.abs(backorderLastOrig);
//					}else{
//						backorderLastOrig = 0;
//					}
			var backorderLastOrig = ((Number(quantity) - Number(backorderLastOrig)) - (Number(quantity) - Number(quantityLastToDistrib))) + Number(stockVirtualOrig);
			if(backorderLastOrig < 0){
				backorderLastOrig = Math.abs(backorderLastOrig);
			}else{
				backorderLastOrig = 0;
			}		
			//////////////////////////////////////////EDIT DE ESTE ALMACEN ORIGEN///////////////////////////////////////////////////
			
			//////////////////////////////////////////ADD O EDIT DE EL OTRO ALMACEN DESTINO///////////////////////////////////////////////////
			var otherQuantity = $('#spaQuantity'+itemId+'w'+warehouseId).text();
			if (otherQuantity !== ''){//EDIT OTHER
//				alert(otherQuantity);
//				alert(backorder);
//				alert(otherQuantity);
//				alert(quantityLastToDistrib);
//				var rest2 = (Number(otherQuantity) - Number(backorder)) - (Number(otherQuantity)+Number(quantityLastToDistrib));
////				alert(rest2);
//				backorder = Number(rest2) + Number(stockVirtual);
//				if(backorder < 0){
//					backorder = Math.abs(backorder);
//				}else{
//					backorder = 0;
//				}	
				var backorder = ((Number(otherQuantity) - Number(backorder)) - (Number(otherQuantity)+Number(quantityLastToDistrib))) + Number(stockVirtual);
				if(backorder < 0){
					backorder = Math.abs(backorder);
				}else{
					backorder = 0;
				}
			}else{//ADD OTHER
//				var stockop = 0;
//				if(stockVirtual > 0){
//					stockop = stockVirtual;
//				}		
//				var backorderop = Number(stockop) - Number(quantityLastToDistrib);
//				if(backorderop >= 0){
//					backorder = 0;
//				}else{
//					backorder = Math.abs(backorderop);
//				}				//virtual stock
//				var backorderOp = Number(stockVirtual) - Number(quantityLastToDistrib);
//				if(backorderOp >= 0){
//					backorder = 0;
//				}else{
//					backorder = Math.abs(backorderOp);
//				}
				      //virtual stock
				backorder = Number(stockVirtual) - Number(quantityLastToDistrib);
				if(backorder < 0){
					backorder = Math.abs(backorder);
				}else{
					backorder = 0;
				}
			}
			//////////////////////////////////////////ADD O EDIT DE EL OTRO ALMACEN DESTINO///////////////////////////////////////////////////
					
			////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
		}																				//remaining quantity = quantity - quantityLastToDistrib (warehouseLastOrigId)
																						//other quantity = quantityLastToDistrib --- if its new (warehouseId)
		if(OPERATION === 'DELETE'){															//other quantity = quantityLastToDistrib + other quantity --- if its edit (warehouseId)
			itemId = objectTableRowSelected.find('#txtItemId').val();
			warehouseId = objectTableRowSelected.find('#txtWarehouseId'+itemId).val();
		}
		
		if(OPERATION === 'DELETE_PAY'){
			payDate = objectTableRowSelected.find('#txtPayDate').val();
		}
		//setting data
		DATA ={	'purchaseId':purchaseId
				,'movementDocCode':movementDocCode
				,'movementCode':movementCode
				,'noteCode':noteCode
				,'date':date
				,'employee':employee
				,'taxNumber':taxNumber
				,'salesman':salesman
				,'description':description	
				,'exRate':exRate
				,'discountType':discountType
				,'discount':discount
				,'invoiced':invoiced
				,'invoiceNumber':invoiceNumber
				,'invoiceDescription':invoiceDescription

				,'warehouseId':warehouseId
				,'warehouse':warehouse
				,'itemId':itemId
				,'salePrice':salePrice
				,'warehouseLastOrigId':warehouseLastOrigId
				,'quantityLastToDistrib':quantityLastToDistrib
				,'quantity':quantity	
				,'backorder':backorder	
				,'backorderLastOrig':backorderLastOrig
				,'quantityApproved':quantityApproved
//				,'backorderOrig':backorderOrig	
//				,'cifPrice':cifPrice
//				,'exCifPrice':exCifPrice
				,'subtotal':subtotal
			//	,'total':total
				
//				,'quantityToDistrib':quantityToDistrib
//				,'warehouseOriginId':warehouseOriginId
				
				,'dateId':dateId
				,'payDate':payDate
				,'payAmount':payAmount
				,'payDescription':payDescription
		
				,arrayItemsDetails:arrayItemsDetails
		
				,'ACTION':ACTION
				,'OPERATION':OPERATION
				,'STATE':STATE

				,'itemCodeName':itemCodeName
				,'stockVirtual':stockVirtual
//				,'stockReal':stockReal
				,'stockVirtualOrig':stockVirtualOrig
//				,'stockRealDest':stockRealDest
//				,'stockNew':stockNew
				,arrayForValidate:arrayForValidate
			  };
		  
		return DATA;
	}
	
	function highlightTemporally(id){
		//$('#itemRow'+dataSent['itemId']).delay(8000).removeAttr('style');
			$(id).fadeIn(4000).css("background-color","#FFFF66");
			setTimeout(function() {
				$(id).removeAttr('style');
				//$('#itemRow'+itemId).animate({ background: '#fed900'}, "slow");
				 //$('#itemRow'+itemId).fadeOut(400);
				 //$('#itemRow'+itemId).fadeIn(4000).css("background-color","red");
				 //$('#itemRow'+itemId).animate({ backgroundColor: "#f6f6f6" }, 'slow');
			}, 4000);
	}
	
	function fixPrintButtonUrlWhenNewDocument(id) {
		var a_href = $('#btnPrint').attr("href");
		var new_href = a_href.replace(a_href.substr(a_href.lastIndexOf('/') + 1), id + ".pdf");
		$('#btnPrint').attr("href", new_href);
	}
	
	function setOnPendant(DATA, ACTION, OPERATION, STATE, objectTableRowSelected, warehouseId, warehouse, itemId, itemCodeName, salePrice,/* stockVirtual, stockReal, stockVirtualOrig, stockRealDest,*/ warehouseLastOrigId, quantityLastToDistrib, quantity, backorder, backorderLastOrig, quantityApproved,/*backorderOrig,*/ subtotal, discountType, discount/* warehouseOriginId, quantityToDistrib, dateId, payDate, payAmount, payDescription*/){
		if($('#txtPurchaseIdHidden').val() === ''){
			$('#txtCode').val(DATA[2]);
			$('#txtGenericCode').val(DATA[3]);
			window.history.replaceState('','',urlPathName+'/id:'+DATA[1]);
                        if(ACTION === 'save_invoice'){
				$('#btnApproveState').show();
			}
			$(/*'#btnApproveState*/'#btnReserveNote, #btnPrint, #btnLogicDeleteState, #btnAddPay').show();
			$('#txtPurchaseIdHidden').val(DATA[1]);
			changeLabelDocumentState(STATE); //#UNICORN
		}
		/////////////************************************////////////////////////
		//Item's table setup
		if(OPERATION === 'ADD'){
//			if((stockVirtual - quantity) < 0){stockVirtual = 0;}else{stockVirtual = stockVirtual - quantity;}
			createRowItemTable(itemId, itemCodeName, /*parseFloat(*/salePrice/*).toFixed(2)*/, /*parseInt(*/quantity/*,10)*/,backorder , warehouse, warehouseId, /*stockVirtual*//*(stockVirtual - quantity)*//*, stockReal,*/ parseFloat(subtotal).toFixed(2));
			createEventClickEditItemButton(itemId, warehouseId);
//			if(urlAction === 'save_invoice'){
				createEventClickDistribItemButton(itemId, warehouseId);
//			}
			createEventClickDeleteItemButton(itemId, warehouseId);
//			arrayItemsAlreadySaved.push(itemId);  //push into array of the added item
//			arrayWarehouseItemsAlreadySaved.push(warehouseId);  //push into array of the added warehouses	
			arrayItemsWarehousesAlreadySaved.push(itemId+'w'+warehouseId);  //push into array of the added items+warehouses
			///////////////////
			itemsCounter = itemsCounter + 1;
			//////////////////
			$('#countItems').text(itemsCounter);
			//$('#countItems').text(arrayItemsAlreadySaved.length);
			var total = getTotal();
			$('#total').text(/*parseFloat(*/total/*).toFixed(2)*/+' Bs.');
			$('#totalDebt').text(/*parseFloat(*/total/*).toFixed(2)*/+' Bs.');
			if(discount > 0 && discountType !== '1'){
					if(discountType === '2'){//%
						var discAmnt = (total*discount)/100;
					}else if(discountType === '3'){//Bs.
						var discAmnt = discount;
					}
					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
					}else{//ADD
						if($('#taxTr').length || $('#totalTaxTr').length){
							$('#txtInvoicePercent').val('');
							$('#taxTr').remove();
							$('#totalTaxTr').remove();
						}
						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
					}	
					$('#totalDebt').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');
			}else{//REMOVE if 0
				if( $('#discAmnt').length || $('#totalDisc').length ) {
					$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
					$('#discountTr').remove();
					$('#totalTr').remove();
				}
				$('#totalDebt').text(parseFloat(total).toFixed(2)+' Bs.');
			}	
			
//			if(discountType === '2'){//%
//				if(discount > 0){
//					var discAmnt = (total*discount)/100;
//					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
//						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
//						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
//					}else{//ADD
//						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
//						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
//						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
//					}	
//				}else{//REMOVE if 0
//					if( $('#discAmnt').length || $('#totalDisc').length ) {
//						$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//						$('#discountTr').remove();
//						$('#totalTr').remove();
//					}
//				}	
//			}else if(discountType === '3'){//Bs.
//				if(discount > 0){
//					var discAmnt = discount;
//					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
//						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
//						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
//					}else{//ADD
//						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
//						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
//						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
//					}		
//				}else{//REMOVE if 0
//					if( $('#discAmnt').length || $('#totalDisc').length ) {
//						$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//						$('#discountTr').remove();
//						$('#totalTr').remove();
//					}
//				}		
//			}else{//REMOVE
//				if( $('#discAmnt').length || $('#totalDisc').length ) {
//					$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//					$('#discountTr').remove();
//					$('#totalTr').remove();
//				}
//			}
			$('#modalAddItem').modal('hide');
			highlightTemporally('#itemRow'+itemId+'w'+warehouseId);
		}	
		
		if(OPERATION === 'EDIT'){
			if(warehouseLastOrigId !== warehouseId){
				arrayItemsWarehousesAlreadySaved = jQuery.grep(arrayItemsWarehousesAlreadySaved, function(value) {
					return value !== itemId+'w'+warehouseLastOrigId;
				});
				arrayItemsWarehousesAlreadySaved.push(itemId+'w'+warehouseId);  //push into array of the added items+warehouses
				$('#itemRow'+itemId+'w'+warehouseLastOrigId).attr('id', 'itemRow'+itemId+'w'+warehouseId);
				$('#spaSalePrice'+itemId+'w'+warehouseLastOrigId).attr('id', 'spaSalePrice'+itemId+'w'+warehouseId);
				$('#spaAvaQuantity'+itemId+'w'+warehouseLastOrigId).attr('id', 'spaAvaQuantity'+itemId+'w'+warehouseId);
				$('#spaBOQuantity'+itemId+'w'+warehouseLastOrigId).attr('id', 'spaBOQuantity'+itemId+'w'+warehouseId);
				$('#spaQuantity'+itemId+'w'+warehouseLastOrigId).attr('id', 'spaQuantity'+itemId+'w'+warehouseId);
				$('#spaWarehouse'+itemId+'w'+warehouseLastOrigId).attr('id', 'spaWarehouse'+itemId+'w'+warehouseId);
				$('#spaWarehouse'+itemId+'w'+warehouseId).text(warehouse);
				$('#txtWarehouseId'+itemId).val(warehouseId);
//				$('input[id="txtWarehouseId'+itemId+'"][value="'+warehouseLastOrigId+'"]').val(warehouseId); //by using val you don't affect the DOM element's value
				$('input[id="txtWarehouseId'+itemId+'"][value="'+warehouseLastOrigId+'"]').attr('value', warehouseId); //by using setAttribute or jQuery's attr you will also affect the DOM element's value
//				$('span:contains("'+warehouseLastOrigId+'")').text(); //recupera el text de todos los spans q contains()
//				$('#spaVirtualStock'+itemId+'w'+warehouseLastOrigId).attr('id', 'spaVirtualStock'+itemId+'w'+warehouseId);
//				$('#spaStock'+itemId+'w'+warehouseLastOrigId).attr('id', 'spaStock'+itemId+'w'+warehouseId);
				$('#spaSubtotal'+itemId+'w'+warehouseLastOrigId).attr('id', 'spaSubtotal'+itemId+'w'+warehouseId);
				$('#btnEditItem'+itemId+'w'+warehouseLastOrigId).attr('id', 'btnEditItem'+itemId+'w'+warehouseId);
				$('#btnDeleteItem'+itemId+'w'+warehouseLastOrigId).attr('id', 'btnDeleteItem'+itemId+'w'+warehouseId);
			}
			$('#spaQuantity'+itemId+'w'+warehouseId).text(parseInt(quantity,10));
//			var virtualStock = 0;
			if(warehouseLastOrigId !== warehouseId){
//				if((Number(stockVirtual) - Number(quantity)) < 0){virtualStock = 0;}else{virtualStock = Number(stockVirtual) - Number(quantity);}
//				virtualStock = Number(stockVirtual) - Number(quantity);
														//backorderLastOrig
			}else if((quantity !== quantityLastToDistrib) && (backorder !== backorderLastOrig)){//CAMBIO DE cantidad y backorder	
													//backorderLastOrig
//				virtualStock = ((Number(quantityLastToDistrib) - Number(backorderLastOrig)) - Number(quantity)) + Number(backorder) + Number(stockVirtual);
//				if(virtualStock < 0){virtualStock = 0;}
			}else if(quantity !== quantityLastToDistrib){//CAMBIO DE cantidad		
													//backorderLastOrig
//				virtualStock = ((Number(quantityLastToDistrib) - Number(backorderLastOrig)) - Number(quantity)) + Number(stockVirtual);
//				if(virtualStock < 0){virtualStock = 0;}
//				var diff = 0;
//				if( quantityLastToDistrib < quantity ){
//					diff = Number(quantity) - Number(quantityLastToDistrib);
//					virtualStock = Number(stockVirtual) - Number(diff);
//
//				}else if( quantityLastToDistrib > quantity){
//					diff = Number(quantityLastToDistrib) - Number(quantity);
//					virtualStock = Number(stockVirtual) + Number(diff);
//				}else{
//					diff = 0;
//					virtualStock = Number(stockVirtual);
//				}	
//				alert(backorder);
							//backorderLastOrig
			}else if(backorder !== backorderLastOrig){//CAMBIO DE backorder
														//backorderLastOrig
//				virtualStock = ((Number(quantityLastToDistrib) - Number(backorderLastOrig)) - Number(quantity)) + Number(backorder) + Number(stockVirtual);
//				if(virtualStock < 0){virtualStock = 0;}
			}
			$('#spaBOQuantity'+itemId+'w'+warehouseId).text(backorder);
			if(backorder > 0){
				$('#spaBOQuantity'+itemId+'w'+warehouseId).attr('style', 'color:red');
			}else{
				$('#spaBOQuantity'+itemId+'w'+warehouseId).attr('style', 'color:black');
			}
//			alert();
			if((quantityApproved > 0) && (Number(quantity) - Number(backorder) - Number(quantityApproved) > 0)){
				$('#spaAvaQuantity'+itemId+'w'+warehouseId).text(Number(quantityApproved)+' + '+(Number(quantity) - Number(backorder) - Number(quantityApproved)));
			}else{
				$('#spaAvaQuantity'+itemId+'w'+warehouseId).text(Number(quantity) - Number(backorder));
			}
				
//			$('#spaStock'+itemId+'w'+warehouseId).text(stockReal);	
//			$('#spaVirtualStock'+itemId+'w'+warehouseId).text(virtualStock);
//			if(virtualStock <= 0){
//				$('#spaVirtualStock'+itemId+'w'+warehouseId).attr('style', 'color:red');
//			}else{
//				$('#spaVirtualStock'+itemId+'w'+warehouseId).attr('style', 'color:black');
//			}
			$('#spaSalePrice'+itemId+'w'+warehouseId).text(parseFloat(salePrice).toFixed(2));	
			$('#spaSubtotal'+itemId+'w'+warehouseId).text(parseFloat(Number(quantity) * Number(salePrice)).toFixed(2));
			var total = getTotal();			
			$('#total').text(/*parseFloat(*/total/*).toFixed(2)*/+' Bs.');
			$('#totalDebt').text(/*parseFloat(*/total/*).toFixed(2)*/+' Bs.');
			if(discount > 0 && discountType !== '1'){
					if(discountType === '2'){//%
						var discAmnt = (total*discount)/100;
					}else if(discountType === '3'){//Bs.
						var discAmnt = discount;
					}
					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
					}else{//ADD
						if($('#taxTr').length || $('#totalTaxTr').length){
							$('#txtInvoicePercent').val('');
							$('#taxTr').remove();
							$('#totalTaxTr').remove();
						}
						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
					}	
					$('#totalDebt').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');
			}else{//REMOVE if 0
				if( $('#discAmnt').length || $('#totalDisc').length ) {
					$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
					$('#discountTr').remove();
					$('#totalTr').remove();
				}
				$('#totalDebt').text(parseFloat(total).toFixed(2)+' Bs.');
			}	
			
//			if(discountType === '2'){//%
//				if(discount > 0){
//					var discAmnt = (total*discount)/100;
//					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
//						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
//						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
//					}else{//ADD
//						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
//						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
//						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
//					}	
//				}else{//REMOVE if 0
//					if( $('#discAmnt').length || $('#totalDisc').length ) {
//						$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//						$('#discountTr').remove();
//						$('#totalTr').remove();
//					}
//				}	
//			}else if(discountType === '3'){//Bs.
//				if(discount > 0){
//					var discAmnt = discount;
//					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
//						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
//						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
//					}else{//ADD
//						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
//						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
//						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
//					}	
//				}else{//REMOVE if 0
//					if( $('#discAmnt').length || $('#totalDisc').length ) {
//						$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//						$('#discountTr').remove();
//						$('#totalTr').remove();
//					}
//				}		
//			}else{//REMOVE
//				if( $('#discAmnt').length || $('#totalDisc').length ) {
//					$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//					$('#discountTr').remove();
//					$('#totalTr').remove();
//				}
//			}
			
			$('#modalAddItem').modal('hide');
			highlightTemporally('#itemRow'+itemId+'w'+warehouseId);
		}	
		
		if(OPERATION === 'DELETE'){	
			arrayItemsWarehousesAlreadySaved = jQuery.grep(arrayItemsWarehousesAlreadySaved, function(value) {
				return value !== itemId+'w'+warehouseId;
			});
//			var itemIdForDelete = objectTableRowSelected.find('#txtItemId').val();
			var subtotal = $('#spaSubtotal'+itemId/*ForDelete*/+'w'+warehouseId).text();		
			hideBittionAlertModal();
			
			objectTableRowSelected.fadeOut("slow", function() {
				$(this).remove();
			});
			///////////////////
			itemsCounter = itemsCounter - 1;
			//////////////////
			$('#countItems').text(itemsCounter);
			//$('#countItems').text(arrayItemsAlreadySaved.length-1);	//because arrayItemsAlreadySaved updates after all is done
			var total = getTotal() - subtotal;
			$('#total').text(parseFloat(total).toFixed(2)+' Bs.');
			$('#totalDebt').text(/*parseFloat(*/total/*).toFixed(2)*/+' Bs.');
			if(discount > 0 && discountType !== '1'){
					if(discountType === '2'){//%
						var discAmnt = (total*discount)/100;
					}else if(discountType === '3'){//Bs.
						var discAmnt = discount;
					}
					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
					}else{//ADD
						if($('#taxTr').length || $('#totalTaxTr').length){
							$('#txtInvoicePercent').val('');
							$('#taxTr').remove();
							$('#totalTaxTr').remove();
						}
						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
					}	
					$('#totalDebt').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');
			}else{//REMOVE if 0
				if( $('#discAmnt').length || $('#totalDisc').length ) {
					$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
					$('#discountTr').remove();
					$('#totalTr').remove();
				}
				$('#totalDebt').text(parseFloat(total).toFixed(2)+' Bs.');
			}	
			
//			if(discountType === '2'){//%
//				if(discount > 0){
//					var discAmnt = (total*discount)/100;
//					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
//						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
//						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
//					}else{//ADD
//						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
//						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
//						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
//					}	
//				}else{//REMOVE if 0
//					if( $('#discAmnt').length || $('#totalDisc').length ) {
//						$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//						$('#discountTr').remove();
//						$('#totalTr').remove();
//					}
//				}			
//			}else if(discountType === '3'){//Bs.
//				if(discount > 0){
//					var discAmnt = discount;
//					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
//						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
//						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
//					}else{//ADD
//						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
//						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
//						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
//					}	
//				}else{//REMOVE if 0
//					if( $('#discAmnt').length || $('#totalDisc').length ) {
//						$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//						$('#discountTr').remove();
//						$('#totalTr').remove();
//					}
//				}	
//			}else{//REMOVE
//				if( $('#discAmnt').length || $('#totalDisc').length ) {
//					$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//					$('#discountTr').remove();
//					$('#totalTr').remove();
//				}
//			}
			
		}
		
		if(OPERATION === 'DEFAULT'){
			var total = getTotal();
			
			if(discount > 0 && discountType !== '1'){
					if(discountType === '2'){//%
						var discAmnt = (total*discount)/100;
					}else if(discountType === '3'){//Bs.
						var discAmnt = discount;
					}
					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
					}else{//ADD
						if($('#taxTr').length || $('#totalTaxTr').length){
							$('#txtInvoicePercent').val('');
							$('#taxTr').remove();
							$('#totalTaxTr').remove();
						}
						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
					}	
					$('#totalDebt').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');
			}else{//REMOVE if 0
				if( $('#discAmnt').length || $('#totalDisc').length ) {
					$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
					$('#discountTr').remove();
					$('#totalTr').remove();
				}
				$('#totalDebt').text(parseFloat(total).toFixed(2)+' Bs.');
			}	
			
//			if(discountType === '2'){//%
//				if(discount > 0){
//					var discAmnt = (total*discount)/100;
//					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
//						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
//						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
//					}else{//ADD
//						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
//						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
//						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
//					}	
//				}else{//REMOVE if 0
//					if( $('#discAmnt').length || $('#totalDisc').length ) {
//						$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//						$('#discountTr').remove();
//						$('#totalTr').remove();
//					}
//				}	
//			}else if(discountType === '3'){//Bs.
//				if(discount > 0){
//					var discAmnt = discount;
//					if( $('#discAmnt').length || $('#totalDisc').length ) {//EDIT
//						$('#discAmnt').text(parseFloat(discAmnt).toFixed(2)+' Bs.');
//						$('#totalDisc').text(parseFloat(total - discAmnt).toFixed(2)+' Bs.');					
//					}else{//ADD
//						$('#totalLabel').html('<h6 id="totalLabel">Monto sin </br> descuento:</h6>');
//						createRowTotalTable('discountTr', 'Descuento', 'discAmnt', parseFloat(discAmnt).toFixed(2));
//						createRowTotalTable('totalTr', 'Total', 'totalDisc', parseFloat(total - discAmnt).toFixed(2));
//					}	
//				}else{//REMOVE if 0
//					if( $('#discAmnt').length || $('#totalDisc').length ) {
//						$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//						$('#discountTr').remove();
//						$('#totalTr').remove();
//					}
//				}		
//			}else{//REMOVE
//				if( $('#discAmnt').length || $('#totalDisc').length ) {
//					$('#totalLabel').html('<h4 id="totalLabel">Total:</h4>');
//					$('#discountTr').remove();
//					$('#totalTr').remove();
//				}
//			}
			
			if(STATE === 'NOTE_RESERVED'){
				$('#btnSaveAll, #btnLogicDeleteState ,#btnReserveNote').hide();
				$('#btnEditReservedNote').show();
				
				$('.columnItemsButtons').hide();
				$('#txtNoteCode, #txtDate, #txtDescription, #txtExRate, #txtDiscount').attr('disabled','disabled');
				$('#tabInvoiceNumber, #tabDescription').attr('disabled','disabled');
				$('#cbxCustomers').select2('disable', true); //change to function on BittionMain ??????
				$('#cbxEmployees').select2('disable', true); //change to function on BittionMain ??????
				$('#cbxSalesman').select2('disable', true); //change to function on BittionMain ??????
				$('#cbxTaxNumbers').select2('disable', true); //change to function on BittionMain ??????
				$('#chkInv').attr("disabled", true);
				if ($('#btnAddItemSO').length > 0){//existe
					$('#btnAddItemSO').hide();
				}
				changeLabelDocumentState('NOTE_RESERVED');
			}
			
			if(STATE === 'SINVOICE_APPROVED'){
				var arrayItemsApproved = DATA[4].split(',');
				if(arrayItemsApproved.length > 1){
					if ($('#btnAddItemSO').length > 0){//existe
						$('#btnAddItemSO').hide();
					}

					var arrayItemsApprovedQuantity = DATA[5].split(',');
					for (var i = 0; i < arrayItemsApproved.length; i++) {
		//				var auxItemsApproved = arrayItemsApproved[i].split(',');//  item5=>9stock
		//				$('#spaAvaQuantity' + auxItemsApproved).attr('style', 'color:red');
						$('#spaAvaQuantity' + arrayItemsApproved[i].split(',')).text(arrayItemsApprovedQuantity[i].split(','));
						$('#spaAvaQuantity' + arrayItemsApproved[i].split(',')).attr('style', 'color:blue');
		//				alert(auxItemsApproved);
		//				$('#' + controlName + arrayItemsApproved[0]).text(arrayItemsApproved[1]);  //update only if quantities are APPROVED
					}

		//			btnDistribItem
		//			btnDeleteItem
					var arrayItemsCompletelyApproved = DATA[6].split(',');
					for (var i = 0; i < arrayItemsCompletelyApproved.length; i++) {
		//				var auxItemsApproved = arrayItemsApproved[i].split(',');//  item5=>9stock
		//				$('#spaAvaQuantity' + auxItemsApproved).attr('style', 'color:red');
						$('#btnEditItem' + arrayItemsCompletelyApproved[i].split(',')).attr('style', 'display:none');
						$('#btnDistribItem' + arrayItemsCompletelyApproved[i].split(',')).attr('style', 'display:none');
						$('#btnDeleteItem' + arrayItemsCompletelyApproved[i].split(',')).attr('style', 'display:none');
		//				alert(auxItemsApproved);
		//				$('#' + controlName + arrayItemsApproved[0]).text(arrayItemsApproved[1]);  //update only if quantities are APPROVED
					}
					var arrayItemsHalfApproved = DATA[7].split(',');
					for (var i = 0; i < arrayItemsHalfApproved.length; i++) {
		//				var auxItemsApproved = arrayItemsApproved[i].split(',');//  item5=>9stock
		//				$('#spaAvaQuantity' + auxItemsApproved).attr('style', 'color:red');
		//				$('#btnEditItem' + arrayItemsHalfApproved[i].split(',')).attr('style', 'display:none');
						$('#btnDistribItem' + arrayItemsHalfApproved[i].split(',')).attr('style', 'display:none');
						$('#btnDeleteItem' + arrayItemsHalfApproved[i].split(',')).attr('style', 'display:none');
		//				alert(auxItemsApproved);
		//				$('#' + controlName + arrayItemsApproved[0]).text(arrayItemsApproved[1]);  //update only if quantities are APPROVED
					}
					var arrayItemsCompletelyBackordered = DATA[8].split(',');
					for (var i = 0; i < arrayItemsCompletelyBackordered.length; i++) {
		//				var auxItemsApproved = arrayItemsApproved[i].split(',');//  item5=>9stock
		//				$('#spaAvaQuantity' + auxItemsApproved).attr('style', 'color:red');
		//				$('#btnEditItem' + arrayItemsCompletelyBackordered[i].split(',')).attr('style', 'display:none');
						$('#btnDistribItem' + arrayItemsCompletelyBackordered[i].split(',')).attr('style', 'display:none');
		//				$('#btnDeleteItem' + arrayItemsCompletelyBackordered[i].split(',')).attr('style', 'display:none');
		//				alert(auxItemsApproved);
		//				$('#' + controlName + arrayItemsApproved[0]).text(arrayItemsApproved[1]);  //update only if quantities are APPROVED
					}
//					if(arrayItemsApproved.length > 1){
					$('#inputDelivered').val(1);
//					}
				}
			}	
//			alert(auxItemsApproved);
//			row +='<td><span id="spaBOQuantity'+itemId+'w'+warehouseId+'" style="color:red">'+backorder+'</span></td>';
//			$('#spaAvaQuantity307w2').attr('style', 'color:red');
//			$('#spaAvaQuantity307w1').attr('style', 'color:red');
//			$('#spaAvaQuantity305w2').attr('style', 'color:red');
//			alert('DODODODODOD');
		}
		
				
		if(OPERATION === 'DISTRIB'){
			var tQuantity= Number(quantity) - Number(quantityLastToDistrib);
			$('#spaQuantity'+itemId+'w'+warehouseLastOrigId).text(tQuantity);
//			var virtualStock = 0;
//				var diff = 0;
//				if( quantity < tQuantity ){
//					diff = Number(tQuantity) - Number(quantity);
//					stockReserved = Number(stockVirtual) - Number(diff);
//
//				}else if( quantity > tQuantity){
//					diff = Number(quantity) - Number(tQuantity);
//					alert(stockVirtualOrig);
//					alert(diff);
//					virtualStock = Number(stockVirtualOrig) + Number(diff);
//				}else{
//					diff = 0;
//					stockReserved = Number(stockVirtual);
//				}	
//			alert(stockReserved);
//			virtualStock = ((Number(quantity) - Number(backorderLastOrig)) - Number(tQuantity)) + Number(stockVirtualOrig);
//			if(virtualStock < 0){virtualStock = 0;}

			$('#spaBOQuantity'+itemId+'w'+warehouseLastOrigId).text(backorderLastOrig);
			if(backorderLastOrig > 0){
				$('#spaBOQuantity'+itemId+'w'+warehouseLastOrigId).attr('style', 'color:red');
			}else{
				$('#spaBOQuantity'+itemId+'w'+warehouseLastOrigId).attr('style', 'color:black');
			}
			$('#spaAvaQuantity'+itemId+'w'+warehouseLastOrigId).text(Number(tQuantity) - Number(backorderLastOrig));
			
			$('#spaSubtotal'+itemId+'w'+warehouseLastOrigId).text(parseFloat(Number(tQuantity) * Number(salePrice)).toFixed(2));
			
			var otherQuantity = $('#spaQuantity'+itemId+'w'+warehouseId).text();
			if (otherQuantity !== ''){//EDIT OTHER
//				var otherLastBackorder = $('#spaBOQuantity'+itemId+'w'+warehouseId).text();
				var otherTQuantity = Number(otherQuantity) + Number(quantityLastToDistrib);
//				var otherVirtualStock = 0;
//				otherVirtualStock = ((Number(otherQuantity) - Number(otherLastBackorder)) - Number(otherTQuantity)) + Number(stockVirtual);
//				if(otherVirtualStock < 0){otherVirtualStock = 0;}
				$('#spaAvaQuantity'+itemId+'w'+warehouseId).text(Number(otherTQuantity) - Number(backorder));////////////////////REVISAR pq no estaba?
				$('#spaQuantity'+itemId+'w'+warehouseId).text(otherTQuantity);
				$('#spaSubtotal'+itemId+'w'+warehouseId).text(parseFloat(Number(otherTQuantity) * Number(salePrice)).toFixed(2));
//				$('#spaVirtualStock'+itemId+'w'+warehouseId).text(Number(stockVirtual)-Number(quantityLastToDistrib));
//				$('#spaVirtualStock'+itemId+'w'+warehouseId).text(otherVirtualStock);
				$('#spaBOQuantity'+itemId+'w'+warehouseId).text(backorder);
				if(backorder > 0){
					$('#spaBOQuantity'+itemId+'w'+warehouseId).attr('style', 'color:red');
				}else{
					$('#spaBOQuantity'+itemId+'w'+warehouseId).attr('style', 'color:black');
				}
			}else{//ADD OTHER
				var otherSubtotal = Number(quantityLastToDistrib) * Number(salePrice);
//				createRowItemTable(itemId, itemCodeName, salePrice, quantity, backorder, warehouse, warehouseId, (stockVirtual - quantity), stockReal, parseFloat(subtotal).toFixed(2));
				//createRowItemTable(itemId, itemCodeName, salePrice, quantity, backorder , warehouse, warehouseId, (stockVirtual - quantity), stockReal, parseFloat(subtotal).toFixed(2));
//				if((stockVirtual - quantity) < 0){stockVirtual = 0;}else{stockVirtual = stockVirtual - quantity;}
				createRowItemTable(itemId, itemCodeName, salePrice, quantityLastToDistrib, backorder, warehouse, warehouseId,/* stockVirtual,*//*(stockVirtual - quantityLastToDistrib),*//* stockRealDest,*/ parseFloat(otherSubtotal).toFixed(2));
				createEventClickEditItemButton(itemId, warehouseId);
//				if(urlAction === 'save_invoice'){
					createEventClickDistribItemButton(itemId, warehouseId);
//				}
				createEventClickDeleteItemButton(itemId, warehouseId);
				arrayItemsWarehousesAlreadySaved.push(itemId+'w'+warehouseId);  //push into array of the added items+warehouses
				///////////////////
				itemsCounter = itemsCounter + 1;
				//////////////////
				$('#countItems').text(itemsCounter);
			}
////			$('#spaVirtualStock'+itemId+'w'+warehouseLastOrigId).text(Number(stockVirtualOrig)+Number(quantityLastToDistrib));
//			$('#spaVirtualStock'+itemId+'w'+warehouseLastOrigId).text(virtualStock);
//			if(virtualStock <= 0){
////				$('#spaVirtualStock'+itemId+'w'+warehouseId).text(0);
//				$('#spaVirtualStock'+itemId+'w'+warehouseLastOrigId).attr('style', 'color:red');
//			}else{
////				$('#spaVirtualStock'+itemId+'w'+warehouseId).text(stockReserved);
//				$('#spaVirtualStock'+itemId+'w'+warehouseLastOrigId).attr('style', 'color:black');
//			}
			var total = getTotal();
			$('#total').text(/*parseFloat(*/total/*).toFixed(2)*/+' Bs.');
			$('#totalDebt').text(/*parseFloat(*/total/*).toFixed(2)*/+' Bs.');
			$('#modalDistribItem').modal('hide');
			highlightTemporally('#itemRow'+itemId+'w'+warehouseLastOrigId);
			highlightTemporally('#itemRow'+itemId+'w'+warehouseId);
//			highlightTemporally('#payRow'+dateId);
		}		
		showGrowlMessage('ok', 'Cambios guardados.');
	}
	
	function setOnApproved(DATA, STATE, ACTION){
		$('#txtCode').val(DATA[2]);
		$('#txtGenericCode').val(DATA[3]);
		$('#btnApproveState, #btnLogicDeleteState, #btnSaveAll, #btnAddItemSO, .columnItemsButtons').hide();
		$('#btnCancellState, #btnGenerateClonePendant').show();
		$('#txtCode, #txtNoteCode, #txtDate, #txtDescription, #txtExRate, #txtDiscount').attr('disabled','disabled');
		$('#RdDiscount1, #RdDiscount2, #RdDiscount3').attr('disabled','disabled');
		$('#chkInv').attr('disabled','disabled');
		$('#cbxCustomers, #cbxEmployees, #cbxTaxNumbers, #cbxSalesman').select2('disable', true); //change to function on BittionMain ??????
		if ($('#btnAddItem').length > 0){//existe
			$('#btnAddItem').hide();
		}
		
		var paid = DATA[9];
		if(paid === '1'){//TRUE
			if ($('#btnAddPay').length > 0){//existe
				$('#btnAddPay').hide();
			}
		}else{
			if ($('#btnAddPay').length > 0){//no existe
				$('#btnAddPay').show();
			}
		}
		
		var arrayItemsApproved = DATA[4].split(',');
		var arrayItemsApprovedQuantity = DATA[5].split(',');
		for (var i = 0; i < arrayItemsApproved.length; i++) {
			$('#spaAvaQuantity' + arrayItemsApproved[i].split(',')).text(arrayItemsApprovedQuantity[i].split(','));
			$('#spaAvaQuantity' + arrayItemsApproved[i].split(',')).attr('style', 'color:blue');
		}
		
		$('#inputDelivered').val(1);
				
		changeLabelDocumentState(STATE); //#UNICORN
		showGrowlMessage('ok', 'Aprobado.');
	}
	
	function setOnCancelled(STATE){
		$('#btnCancellState, #btnGenerateClonePendant').hide();
		changeLabelDocumentState(STATE); //#UNICORN
		showGrowlMessage('ok', 'Cancelado.');
	}
	
	function setOnPay(DATA, OPERATION, objectTableRowSelected, dateId, payDate, payAmount, payDescription){
		if(OPERATION === 'ADD_PAY'){
			var paid = DATA[9];
			if(paid === '1'){//TRUE
				if ($('#btnAddPay').length > 0){//existe
					$('#btnAddPay').hide();
				}
			}else{
				if ($('#btnAddPay').length > 0){//no existe
					$('#btnAddPay').show();
				}
			}
			createRowPayTable(dateId, payDate, parseFloat(payAmount).toFixed(2), payDescription);
			createEventClickEditPayButton(dateId);
			createEventClickDeletePayButton(dateId);
			arrayPaysAlreadySaved.push(dateId);  //push into array of the added date
			var totalPay = getTotalPay();
			$('#total2').text(/*parseFloat(*/totalPay/*).toFixed(2)*/+' Bs.');
			$('#modalAddPay').modal('hide');
			highlightTemporally('#payRow'+dateId);
		}
		if(OPERATION === 'EDIT_PAY'){	
			var paid = DATA[9];
			if(paid === '1'){//TRUE
				if ($('#btnAddPay').length > 0){//existe
					$('#btnAddPay').hide();
				}
			}else{
				if ($('#btnAddPay').length > 0){//no existe
					$('#btnAddPay').show();
				}
			}
			$('#spaPayDate'+dateId).text(payDate);
			$('#spaPayAmount'+dateId).text(parseFloat(payAmount).toFixed(2));
			$('#spaPayDescription'+dateId).text(payDescription);
//			$('#total2').text(parseFloat(getTotalPay()).toFixed(2)+' Bs.');	
			var totalPay = getTotalPay();
			$('#total2').text(/*parseFloat(*/totalPay/*).toFixed(2)*/+' Bs.');
			$('#modalAddPay').modal('hide');
			highlightTemporally('#payRow'+dateId);
		}
		if(OPERATION === 'DELETE_PAY'){	
			var paid = DATA[9];
			if(paid === '1'){//TRUE
				if ($('#btnAddPay').length > 0){//existe
					$('#btnAddPay').hide();
				}
			}else{
				if ($('#btnAddPay').length > 0){//no existe
					$('#btnAddPay').show();
				}
			}
			arrayPaysAlreadySaved = jQuery.grep(arrayPaysAlreadySaved, function(value){
				return value !== payDate;
			});
			var subtotal = $('#spaPayAmount'+payDate).text();			
			hideBittionAlertModal();
			objectTableRowSelected.fadeOut("slow", function() {
				$(this).remove();
			});
			$('#total2').text(parseFloat(getTotalPay()-subtotal).toFixed(2)+' Bs.');
		}
		showGrowlMessage('ok', 'Cambios guardados.');
	}
	
	function setOnValidation(DATA/*, ACTION*/) {
		var arrayItemsStocks = DATA[1].split(',');
		var validation = '';

//		if (ACTION === 'save_warehouses_transfer') {
//			if (DATA[3] === 'APPROVED') {
//				validation = validateBeforeMoveOut(arrayItemsStocks, 'spaStock');
//				var arrayItemsStocksDestination = DATA[2].split(',');
//				updateMultipleStocks(arrayItemsStocksDestination, 'spaStock2-');
//			} else {
//				validation = validateBeforeMoveOut(arrayItemsStocks, 'spaStock2-');
//				var arrayItemsStocksDestination = DATA[2].split(',');
//				updateMultipleStocks(arrayItemsStocksDestination, 'spaStock');
//			}
//		} else {
			validation = validateBeforeMoveOut(arrayItemsStocks/*, 'spaStock'*/);
//		}
		$('#boxMessage').html('<div class="alert alert-error">\n\
		<button type="button" class="close" data-dismiss="alert">&times;</button>\n\
		<p>No se pudo realizar la operación debido a falta de STOCK:</p><ul>' + validation + '</ul><div>');
	}
	
	function setOnError(){
		showGrowlMessage('error', 'Vuelva a intentarlo.');
	}
	
	function setOnBlock(){
		
			if($('#modalAddItem').hasClass('in')){
				$('#modalAddItem').modal('hide');
			}
//			if(STATE === 'NOTE_RESERVED'){
	//			alert('hide');
				hideBittionAlertModal();
//			}
	//		$('#modalAddItem').modal('hide');
			showBittionAlertModal({content:'No puede continuar editando el documento, debe solicitar edición', btnYes:'Aceptar', btnNo:''});
			$('#bittionBtnYes').click(function(){
				if(urlAction === 'save_order'){//EN VEZ DE IR A INDEX DEBERIAN IR ACTUALIZAR LA PAGINA(DOCUMENTO)
					window.location = urlModuleController + 'index_order';
				}	
				if(urlAction === 'save_invoice'){
					window.location = urlModuleController + 'index_invoice';
				}
			});
	//		showGrowlMessage('error', 'asdasdasdasd.');
	}
	
	function ajax_save_movement(OPERATION, STATE, objectTableRowSelected, arrayForValidate){//SAVE_IN/ADD/PENDANT
		var ACTION = urlAction;
		var dataSent = setOnData(ACTION, OPERATION, STATE, objectTableRowSelected, arrayForValidate);
		//Ajax Interaction	
		$.ajax({
            type:"POST",
            url:urlModuleController + "ajax_save_movement",//saveSale			
			async:false, 
			data:dataSent,
            beforeSend: showProcessing(),
            success: function(data){
				$('#boxMessage').html('');//this for order goes here
				$('#processing').text('');//this must go at the begining not at the end, otherwise, it won't work when validation is send
				var dataReceived = data.split('|');
				//////////////////////////////////////////
//				if(dataReceived[0] === 'NOTE_APPROVED' || dataReceived[0] === 'NOTE_CANCELLED'){
//						var arrayItemsStocks = dataReceived[3].split(',');
//						updateMultipleStocks(arrayItemsStocks, 'spaStock');//What is this for???????????
//				}
				switch(dataReceived[0]){
					case 'NOTE_PENDANT':
						setOnPendant(dataReceived, ACTION, OPERATION, STATE, objectTableRowSelected, dataSent['warehouseId'], dataSent['warehouse'], dataSent['itemId'], dataSent['itemCodeName'], dataSent['salePrice'],/* dataSent['stockVirtual'], dataSent['stockReal'], dataSent['stockVirtualOrig'],*//* dataSent['stockRealDest'],*/ dataSent['warehouseLastOrigId'], dataSent['quantityLastToDistrib'], dataSent['quantity'], dataSent['backorder'], dataSent['backorderLastOrig'], dataSent['quantityApproved'],/*dataSent['backorderOrig'],*/ dataSent['subtotal'], dataSent['discountType'], dataSent['discount']/* dataSent['warehouseOriginId'], dataSent['quantityToDistrib'], dataSent['dateId'], dataSent['payDate'], dataSent['payAmount'], dataSent['payDescription']*/);
						break;
					case 'NOTE_APPROVED':
						setOnApproved(dataReceived, STATE, ACTION);
						break;
					case 'NOTE_CANCELLED':
						setOnCancelled(STATE);
						break;
					case 'NOTE_PAY':
						setOnPay(dataReceived, OPERATION, objectTableRowSelected, dataSent['dateId'], dataSent['payDate'], dataSent['payAmount'], dataSent['payDescription']);
						break;	
					case 'SINVOICE_PENDANT':
						setOnPendant(dataReceived, ACTION, OPERATION, STATE, objectTableRowSelected, dataSent['warehouseId'], dataSent['warehouse'], dataSent['itemId'], dataSent['itemCodeName'], dataSent['salePrice'],/* dataSent['stockVirtual'], dataSent['stockReal'], dataSent['stockVirtualOrig'],*//* dataSent['stockRealDest'],*/ dataSent['warehouseLastOrigId'], dataSent['quantityLastToDistrib'], dataSent['quantity'], dataSent['backorder'], dataSent['backorderLastOrig'], dataSent['quantityApproved'],/*dataSent['backorderOrig'],*/ dataSent['subtotal'], dataSent['discountType'], dataSent['discount']/* dataSent['warehouseOriginId'], dataSent['quantityToDistrib'], dataSent['dateId'], dataSent['payDate'], dataSent['payAmount'], dataSent['payDescription']*/);
						break;
					case 'SINVOICE_APPROVED':
						setOnApproved(dataReceived, STATE, ACTION);
						break;
					case 'SINVOICE_CANCELLED':
						setOnCancelled(STATE);
						break;
					case 'SINVOICE_PAY':
						setOnPay(dataReceived, OPERATION, objectTableRowSelected, dataSent['dateId'], dataSent['payDate'], dataSent['payAmount'], dataSent['payDescription']);
						break;	
					case 'VALIDATION':
						setOnValidation(dataReceived/*, ACTION*/);
						break;
					case 'BLOCK':
						setOnBlock();//caso guardar esta bien......caso reservar tiene q mandar algo
						break;		
					case 'ERROR':
						setOnError();
						break;
				}
			},
			error:function(data){
				$('#boxMessage').html(''); 
				$('#processing').text(''); 
				setOnError();
			}
        });
	}
	
	//*************************************************************************************************************************//
	
	function ajax_logic_delete(purchaseId,/* purchaseId2, */type, /*type2,*/ index, genCode, delivered){
		$.ajax({
			type:"POST",
			url:urlModuleController + "ajax_logic_delete",			
			data:{purchaseId: purchaseId
			//	,purchaseId2: purchaseId2
				,type: type
			//	,type2: type2
				,genCode: genCode
				,delivered: delivered
			},
			success: function(data){
				if(data === 'success'){
					showBittionAlertModal({content:'Se eliminó el documento en estado Pendiente', btnYes:'Aceptar', btnNo:''});
					$('#bittionBtnYes').click(function(){
						window.location = urlModuleController + index;
					});

				}else if(data === 'BLOCK'){
					setOnBlock();
				}else{	
					showGrowlMessage('error', 'Vuelva a intentarlo.');
				}
			},
			error:function(data){
				showGrowlMessage('error', 'Vuelva a intentarlo.');
			}
		});
	}
		
	function ajax_change_reserved(saleId, reserve, genCode, action){
		$.ajax({
				type:"POST",
				url:urlModuleController + "ajax_change_reserved",			
				data:{saleId: saleId
					,reserve: reserve
					,genCode:genCode
					,action:action
				},
				success: function(data){
					var dataReceived = data.split('|');
					if(dataReceived[0] === 'success'){
						showBittionAlertModal({content:'Se cambio el documento en estado Reservado', btnYes:'Aceptar', btnNo:''});
						$('#bittionBtnYes').click(function(){
//							window.location = urlModuleController + 'save_order/' + 'id:' +dataReceived[1];
							window.location = urlModuleController + urlAction + '/id:' +dataReceived[1];
//							location.reload();// NO DEJA VER EL MENSAJE DE EXITO
						});
//					$('#btnSetToPendant').hide();
//					$('#cbxWarehouses').select2('enable', true); 
//					$('#btnApproveState, #btnPrint, #btnLogicDeleteState, #btnAddItem, .columnItemsButtons').show();
//					$('#btnAddItem').show();
//					$('.columnItemsButtons').show();
//					$('#txtCode, #txtNoteCode, #txtDate, #txtDescription, #txtExRate, #txtDiscount').removeAttr('disabled');
//					changeLabelDocumentState('ORDER_PENDANT'); //#UNICORN
					}else if(dataReceived[0] === 'BLOCK'){
						setOnBlock();
					}else{
						showGrowlMessage('error', 'Vuelva a intentarlo.');
					}
				},
				error:function(data){
					showGrowlMessage('error', 'Vuelva a intentarlo.');
				}
			});
		}	
		
	//Get prices and stock for the fist item when modal inititates
	function ajax_initiate_modal_add_item_in_order(itemsWarehousesAlreadySaved){
		 $.ajax({
			type:"POST",
			url:urlModuleController + "ajax_initiate_modal_add_item_in_order",			
			data:{itemsWarehousesAlreadySaved: itemsWarehousesAlreadySaved, action: urlAction},				
			beforeSend: showProcessing(),
			success: function(data){
				$('#processing').text('');
				$('#boxModalInitiateIWPS').html(data);
				$('#txtModalQuantity').val('');  
				$('#boxModalBOQuantity').attr('style', 'display:none');
				initiateModal();
				fnBittionSetSelectsStyle();
				$('#cbxModalItems').bind("change",function(){ //must be binded 'cause dropbox is loaded by a previous ajax'
					//updates price and stock in modal
					ajax_update_warehouse_price_stock_modal_order(itemsWarehousesAlreadySaved);
				});							
				$('#txtModalVirtualStock').keypress(function(){return false;});//find out why this is necessary
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
	
	//Get prices and stock for the fist item when modal inititates
	function ajax_initiate_modal_add_item_in(itemsWarehousesAlreadySaved){ 
		 $.ajax({
			type:"POST",
			url:urlModuleController + "ajax_initiate_modal_add_item_in",			
			data:{itemsWarehousesAlreadySaved: itemsWarehousesAlreadySaved/*, date: $('#txtDate').val()*/},				
			beforeSend: showProcessing(),
			success: function(data){
				$('#processing').text('');
				$('#boxModalInitiateIWPS').html(data);
				$('#txtModalQuantity').val('');  
				initiateModal();
				fnBittionSetSelectsStyle();
				$('#cbxModalItems').bind("change",function(){ //must be binded 'cause dropbox is loaded by a previous ajax'
					//updates price and stock in modal
					ajax_update_warehouse_price_stock_modal(itemsWarehousesAlreadySaved);
				});
//				$('#cbxModalWarehouses').bind("change",function(){ //must be binded 'cause dropbox is loaded by a previous ajax'
//					//updates items, price and stock in modal
//					ajax_update_items_price_stock_modal();
//				});								
				$('#txtModalVirtualStock').keypress(function(){return false;});//find out why this is necessary
				
//				$('#txtModalPrice').keydown(function(event) {
//					validateOnlyFloatNumbers(event);			
//				});
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
	
	//Get available warehouses for the item selected to be edited$('#spaWarehouse'+itemId)
	function ajax_initiate_modal_edit_item(itemsWarehousesAlreadySaved, objectTableRowSelected /*,itemIdForEdit, warehouseIdForEdit*/){
		var itemIdForEdit = objectTableRowSelected.find('#txtItemId').val();
		var warehouseIdForEdit = objectTableRowSelected.find('#txtWarehouseId'+itemIdForEdit).val();
		var genCode = $('#txtGenericCode').val();
//		var warehouseName = objectTableRowSelected.find('#spaWarehouse'+itemIdForEdit+'w'+warehouseIdForEdit).text();
		 $.ajax({
			type:"POST",
			url:urlModuleController + "ajax_initiate_modal_edit_item",			
			data:{itemsWarehousesAlreadySaved: itemsWarehousesAlreadySaved, itemIdForEdit: itemIdForEdit, warehouseIdForEdit: warehouseIdForEdit/*, date: $('#txtDate').val()*/, action: urlAction, genCode: genCode},				
			beforeSend: showProcessing(),
			success: function(data){
				$('#processing').text('');
				$('#boxModalInitiateIWPS').html(data);
				$('#boxModalBOQuantity').attr('style', 'display:inline');
//				$('#txtModalQuantity').val(objectTableRowSelected.find('#spaQuantity'+itemIdForEdit).text());
				$('#cbxModalItems').empty();
				$('#cbxModalItems').append('<option value="'+itemIdForEdit+'">'+objectTableRowSelected.find('td:first').text()+'</option>');
				$('#txtModalPrice').val(objectTableRowSelected.find('#spaSalePrice'+itemIdForEdit+'w'+warehouseIdForEdit).text());	
				////////////////////////////////////////ver si es necesario q jalen de la bd, no por ahora
//				$('#txtModalVirtualStock').val(objectTableRowSelected.find('#spaVirtualStock'+itemIdForEdit+'w'+warehouseIdForEdit).text());//VAMOS A BLOKEAR ESTO TEMPORALMENTE //MEJOR Q JALE ESTO DE LA BASE
//				$('#txtModalRealStock').val(objectTableRowSelected.find('#spaStock'+itemIdForEdit+'w'+warehouseIdForEdit).text());
				////////////////////////////////////////
				$('#txtModalLastWarehouse').val(warehouseIdForEdit);
				$('#txtModalBOQuantity').val(objectTableRowSelected.find('#spaBOQuantity'+itemIdForEdit+'w'+warehouseIdForEdit).text());
				$('#txtModalLastBOQuantity').val(objectTableRowSelected.find('#spaBOQuantity'+itemIdForEdit+'w'+warehouseIdForEdit).text());
//				$('#txtModalLastWarehouseName').val(warehouseName);
				$('#txtModalQuantity').val(objectTableRowSelected.find('#spaQuantity'+itemIdForEdit+'w'+warehouseIdForEdit).text());
				$('#txtModalLastQuantity').val(objectTableRowSelected.find('#spaQuantity'+itemIdForEdit+'w'+warehouseIdForEdit).text());
				initiateModal();
				fnBittionSetSelectsStyle();
				$('#cbxModalWarehouses').bind("change",function(){ //must be binded 'cause dropbox is loaded by a previous ajax'
					//updates items, price and stock in modal
					ajax_update_stock_modal();
//					$('#boxModalBOQuantity').attr('style', 'display:none');	
				});
				
//				$('#cbxModalItems').bind("change",function(){ //must be binded 'cause dropbox is loaded by a previous ajax'
//					//updates price and stock in modal
//					ajax_update_price_stock_modal();
//				});
//				$('#txtModalQuantity').val(objectTableRowSelected.find('#spaQuantity'+itemIdForEdit).text());	
			
//				$('#txtModalPrice').keydown(function(event) {
//					validateOnlyFloatNumbers(event);			
//				});
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
				$('#txtModalVirtualStock').keypress(function(){return false;});//find out why this is necessary
			},
			error:function(data){
				showGrowlMessage('error', 'Vuelva a intentarlo.');
				$('#processing').text('');
			}
		});
	}
	
	function ajax_initiate_modal_distrib_item(/*itemsWarehousesAlreadySaved,*/ objectTableRowSelected){
		var itemIdForDistrib = objectTableRowSelected.find('#txtItemId').val();
		var warehouseIdOrigForDistrib = objectTableRowSelected.find('#txtWarehouseId'+itemIdForDistrib).val();
		var saleDocCode = $('#txtCode').val();
		 $.ajax({
			type:"POST",
			url:urlModuleController + "ajax_initiate_modal_distrib_item",			
			data:{saleDocCode:saleDocCode, /*itemsWarehousesAlreadySaved: itemsWarehousesAlreadySaved,*/ itemIdForDistrib: itemIdForDistrib, warehouseIdOrigForDistrib: warehouseIdOrigForDistrib},				
			beforeSend: showProcessing(),
			success: function(data){
				$('#processing').text('');
				$('#boxModalInitiateIWS').html(data);
				$('#txtModalQuantityToDistrib').val('');  
//				$('#txtModalQuantityDistrib').val(objectTableRowSelected.find('#spaQuantity'+itemIdForEdit).text());
				$('#cbxModalItemsDistrib').empty();
				$('#cbxModalItemsDistrib').append('<option value="'+itemIdForDistrib+'">'+objectTableRowSelected.find('td:first').text()+'</option>');
				$('#cbxModalWarehousesDestDistrib').bind("change",function(){ //must be binded 'cause dropbox is loaded by a previous ajax'
					//updates items, price and stock in modal
					ajax_update_stock_modal();
				});
				$('#txtModalQuantityDistrib').val(objectTableRowSelected.find('#spaQuantity'+itemIdForDistrib+'w'+warehouseIdOrigForDistrib).text());
				$('#txtModalQuantityDistrib').keypress(function(){return false;});//find out why this is necessary
				$('#txtModalWarehouseOrigDistrib').val(objectTableRowSelected.find('#txtWarehouseId'+itemIdForDistrib).val());
				$('#txtModalPriceDistrib').val(objectTableRowSelected.find('#spaSalePrice'+itemIdForDistrib+'w'+warehouseIdOrigForDistrib).text());
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
//				$('#txtModalVirtualStockOrigDistrib').val(objectTableRowSelected.find('#spaVirtualStock'+itemIdForDistrib+'w'+warehouseIdOrigForDistrib).text());//MEJOR Q JALE ESTO DE LA BASE
//				$('#txtModalRealStockOrigDistrib').val(objectTableRowSelected.find('#spaStock'+itemIdForDistrib+'w'+warehouseIdOrigForDistrib).text());
				///////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
				$('#txtModalLastBOQuantityOrigDistrib').val(objectTableRowSelected.find('#spaBOQuantity'+itemIdForDistrib+'w'+warehouseIdOrigForDistrib).text());
				fnBittionSetSelectsStyle();
				initiateModalDistrib();
			},
			error:function(data){
				showGrowlMessage('error', 'Vuelva a intentarlo.');
				$('#processing').text('');
			}
		});
	}
	
	function ajax_update_stock_modal(){ 
		$.ajax({
            type:"POST",
            url:urlModuleController + "ajax_update_stock_modal",			
            data:{genCode: $('#txtGenericCode').val(),
				item: $('#cbxModalItems').val(),/*itemsWarehousesAlreadySaved: itemsWarehousesAlreadySaved,*/
			warehouse: $('#cbxModalWarehouses').val(),
			lastWarehouse : $('#txtModalLastWarehouse').val()
			,action: urlAction},
            beforeSend: showProcessing(),
            success: function(data){
				$('#processing').text("");
				$('#boxModalStocks').html(data);
				if($('#cbxModalWarehouses').val() === $('#txtModalLastWarehouse').val()){
					$('#boxModalBOQuantity').attr('style', 'display:inline');	
				}else{
					$('#boxModalBOQuantity').attr('style', 'display:none');	
				}
//				fnBittionSetSelectsStyle();
//				$('#cbxModalItems').bind("change",function(){ //must be binded 'cause dropbox is loaded by a previous ajax'
////					//updates price and stock in modal
//					ajax_update_price_stock_modal();
//				});
				
//				$('#txtModalPrice').keydown(function(event) {
//					validateOnlyFloatNumbers(event);			
//				});
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
			},
			error:function(data){
				$('#boxMessage').html('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>Ocurrio un problema, vuelva a intentarlo<div>');
				$('#processing').text('');
			}
        });
	}
	
	function ajax_update_price_stock_modal(){ 
		$.ajax({
            type:"POST",
            url:urlModuleController + "ajax_update_price_stock_modal",			
            data:{item: $('#cbxModalItems').val(),/*itemsWarehousesAlreadySaved: itemsWarehousesAlreadySaved,*/
			warehouse: $('#cbxModalWarehouses').val()
			,date: $('#txtDate').val()
		,action: urlAction},
            beforeSend: showProcessing(),
            success: function(data){
				$('#processing').text("");
				$('#boxModalPriceStock').html(data);
//				fnBittionSetSelectsStyle();
//				$('#cbxModalItems').bind("change",function(){ //must be binded 'cause dropbox is loaded by a previous ajax'
////					//updates price and stock in modal
//					ajax_update_price_stock_modal();
//				});
				
//				$('#txtModalPrice').keydown(function(event) {
//					validateOnlyFloatNumbers(event);			
//				});
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
			},
			error:function(data){
				$('#boxMessage').html('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>Ocurrio un problema, vuelva a intentarlo<div>');
				$('#processing').text('');
			}
		 });
	}
	
	function ajax_initiate_modal_add_pay(/*paysAlreadySaved/*,payDebt*/){
		 $.ajax({
            type:"POST",
            url:urlModuleController + "ajax_initiate_modal_add_pay",			
		    data:{
//				paysAlreadySaved: paysAlreadySaved,
//					,payDebt: payDebt
//				,discount: $('#txtDiscount').val()
				date:$('#txtDate').val()
				,docCode:$('#txtCode').val()
			},
            beforeSend: showProcessing(),
            success: function(data){
				$('#processing').text('');
				$('#boxModalInitiatePay').html(data); 
				$('#txtModalDescription').val('');  
				initiateModalPay();
				fnBittionSetTypeDate();
				$("#txtModalDate").datepicker({
					showButtonPanel: true
				});
//				$('#txtModalPaidAmount').keydown(function(event) {
//					validateOnlyFloatNumbers(event);			
//				});
				$('#txtModalPaidAmount').keypress(function(event){
					if($.browser.mozilla === true){
						if (event.which === 8 || event.keyCode === 37 || event.keyCode === 39 || event.keyCode === 9 || event.keyCode === 16 || event.keyCode === 46){
							return true;
						}
					}
					if ((event.which !== 46 || $(this).val().indexOf('.') !== -1) && (event.which < 48 || event.which > 57)) {
						event.preventDefault();
					}
				});
			},
			error:function(data){
				$('#boxMessage').html('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>Ocurrio un problema, vuelva a intentarlo<div>');
				$('#processing').text('');
			}
        });
	}
	
	function ajax_initiate_modal_edit_pay(objectTableRowSelected/*paysAlreadySaved/*,payDebt*/){
		var payIdForEdit = objectTableRowSelected.find('#txtPayDate').val();
		 $.ajax({
            type:"POST",
            url:urlModuleController + "ajax_initiate_modal_edit_pay",			
		    data:{
//				paysAlreadySaved: paysAlreadySaved,
//					,payDebt: payDebt
//				,discount: $('#txtDiscount').val()
//			date:$('#txtDate').val(),
			docCode:$('#txtCode').val()
		},
            beforeSend: showProcessing(),
            success: function(data){
				$('#processing').text('');
				$('#boxModalInitiatePay').html(data); 
//				$('#txtModalDescription').val(''); 
				$('#txtModalDate').val(objectTableRowSelected.find('#spaPayDate'+payIdForEdit).text());
				$('#txtModalPaidAmount').val(objectTableRowSelected.find('#spaPayAmount'+payIdForEdit).text());
				$('#txtModalDescription').val(objectTableRowSelected.find('#spaPayDescription'+payIdForEdit).text());
				$('#txtModalAmountHidden').val(objectTableRowSelected.find('#spaPayAmount'+payIdForEdit).text());
				initiateModalPay();
				fnBittionSetTypeDate();
				$("#txtModalDate").datepicker({
					showButtonPanel: true
				});
//				$('#txtModalPaidAmount').keydown(function(event) {
//					validateOnlyFloatNumbers(event);			
//				});
				$('#txtModalPaidAmount').keypress(function(event){
					if($.browser.mozilla === true){
						if (event.which === 8 || event.keyCode === 37 || event.keyCode === 39 || event.keyCode === 9 || event.keyCode === 16 || event.keyCode === 46){
							return true;
						}
					}
					if ((event.which !== 46 || $(this).val().indexOf('.') !== -1) && (event.which < 48 || event.which > 57)) {
						event.preventDefault();
					}
				});
			},
			error:function(data){
				$('#boxMessage').html('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>Ocurrio un problema, vuelva a intentarlo<div>');
				$('#processing').text('');
			}
        });
	}
	
	//Update price
	function ajax_update_warehouse_price_stock_modal_order(itemsWarehousesAlreadySaved){
		$.ajax({
			 type:"POST",
			url:urlModuleController + "ajax_update_warehouse_price_stock_modal_order",			
			data:{itemsWarehousesAlreadySaved: itemsWarehousesAlreadySaved, item: $('#cbxModalItems').val() /*,warehouse: $('#cbxModalWarehouses').val()*/ ,date: $('#txtDate').val(), action: urlAction},
			beforeSend: showProcessing(),
			success: function(data){
				$('#processing').text("");
				$('#boxModalWarehousePriceStock').html(data);
				fnBittionSetSelectsStyle();
				$('#cbxModalWarehouses').bind("change",function(){ //must be binded 'cause dropbox is loaded by a previous ajax'
					//updates items, price and stock in modal
					ajax_update_price_stock_modal();
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
				$('#txtModalVirtualStock').bind("keypress",function(){ //must be binded 'cause input is re-loaded by a previous ajax'
					return false;	//find out why this is necessary
				});
			},
			error:function(data){
				$('#boxMessage').html('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>Ocurrio un problema, vuelva a intentarlo<div>');
				$('#processing').text('');
			}
        });
	}
	
	//Update price
	function ajax_update_warehouse_price_stock_modal(itemsWarehousesAlreadySaved){
		$.ajax({
			 type:"POST",
			url:urlModuleController + "ajax_update_warehouse_price_stock_modal",			
			data:{itemsWarehousesAlreadySaved: itemsWarehousesAlreadySaved, item: $('#cbxModalItems').val() /*,warehouse: $('#cbxModalWarehouses').val()*/ ,date: $('#txtDate').val()},
			beforeSend: showProcessing(),
			success: function(data){
				$('#processing').text("");
				$('#boxModalWarehousePriceStock').html(data);
				fnBittionSetSelectsStyle();
				$('#cbxModalWarehouses').bind("change",function(){ //must be binded 'cause dropbox is loaded by a previous ajax'
					//updates items, price and stock in modal
					ajax_update_price_stock_modal();
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
				$('#txtModalVirtualStock').bind("keypress",function(){ //must be binded 'cause input is re-loaded by a previous ajax'
					return false;	//find out why this is necessary
				});
			},
			error:function(data){
				$('#boxMessage').html('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>Ocurrio un problema, vuelva a intentarlo<div>');
				$('#processing').text('');
			}
        });
	}
	//Update stock
//	function ajax_update_stock_modal_1(){ 
//		$.ajax({
//            type:"POST",
//            url:urlModuleController + "ajax_update_stock_modal_1",			
//            data:{item: $('#cbxModalItems').val(),
//				warehouse: $('#cbxModalWarehouses').val()},
//            beforeSend: showProcessing(),
//            success: function(data){
//				$('#processing').text("");
//				$('#boxModalStock').html(data);
//				$('#txtModalStock').bind("keypress",function(){ //must be binded 'cause input is re-loaded by a previous ajax'
//					return false;	//find out why this is necessary
//				});
//			},
//			error:function(data){
//				$('#boxMessage').html('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>Ocurrio un problema, vuelva a intentarlo<div>');
//				$('#processing').text('');
//			}
//        });
//	}
	
	
	//************************************************************************//
	//////////////////////////////////END-AJAX FUNCTIONS////////////////////////btnGenerateMovements
	//************************************************************************//
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	$('#btnCancellAll').click(function(){
		//alert('Se cancela entrada');
		changeStateCancelledAll();
		return false;
	});
	
	function changeStateCancelledAll(){
		showBittionAlertModal({content:'¿Está seguro de eliminar este documento y su factura y movimientos?'});
		$('#bittionBtnYes').click(function(){
			var purchaseId = $('#txtPurchaseIdHidden').val();
			var genCode = $('#txtGenericCode').val();
//			var purchaseId2=0;
			var type;
//			var type2=0;
			var index;
			switch(urlAction){
				case 'save_order':
					index = 'index_order';
					type = 'NOTE_CANCELLED';
					break;	
				case 'save_invoice':
					index = 'index_invoice';
					type = 'SINVOICE_LOGIC_DELETED';
					break;	
			}
			ajax_cancell_all(purchaseId, type, index, genCode);
			hideBittionAlertModal();
		});
	}
	
	function ajax_cancell_all(purchaseId,/* purchaseId2, */type, /*type2,*/ index, genCode){
		$.ajax({
            type:"POST",
            url:urlModuleController + "ajax_cancell_all",			
            data:{purchaseId: purchaseId
			//	,purchaseId2: purchaseId2
				,type: type
			//	,type2: type2
				,genCode: genCode
			},
            success: function(data){
				if(data === 'success'){
					showBittionAlertModal({content:'Se eliminó el documento su factura y movimientos', btnYes:'Aceptar', btnNo:''});
					$('#bittionBtnYes').click(function(){
						window.location = urlModuleController + index;
					});
					
				}else{
					showGrowlMessage('error', 'Vuelva a intentarlo.');
				}
			},
			error:function(data){
				showGrowlMessage('error', 'Vuelva a intentarlo.');
			}
        });
	}
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	$('#btnGenerateMovements').click(function(){
		generateMovements();
		return false;
	});
	
	function generateMovements(){
		showBittionAlertModal({content:'¿Esta seguro de crear los movimientos correspondientes?'});
		$('#bittionBtnYes').click(function(){
			var arrayItemsDetails = [];
			arrayItemsDetails = getItemsDetails();
			var error = validateBeforeSaveAll(arrayItemsDetails);
			if( error === ''){
				if(urlAction === 'save_invoice'){
					ajax_generate_movements(arrayItemsDetails);
				}
			}else{
				$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
			}
			hideBittionAlertModal();
		});
	}
	
	function ajax_generate_movements(arrayItemsDetails){
		$.ajax({
            type:"POST",
            url:urlModuleController + "ajax_generate_movements",			
            data:{arrayItemsDetails: arrayItemsDetails 
//				  ,purchaseId:$('#txtPurchaseIdHidden').val()
				  ,date:$('#txtDate').val()
//				  ,customer:$('#cbxCustomers').val()
//				  ,employee:$('#cbxEmployees').val()
//				  ,taxNumber:$('#cbxTaxNumbers').val()
//				  ,salesman:$('#cbxSalesman').val()	
				  ,description:$('#txtDescription').val()
//				  ,exRate:$('#txtExRate').val()
//				  ,note_code:$('#txtNoteCode').val()
				  ,genericCode:$('#txtGenericCode').val()
//				  ,originCode:$('#txtOriginCode').val()
			  },
            beforeSend: showProcessing(),
            success: function(data){			
				var arrayCatch = data.split('|');
				if(arrayCatch[0] === 'creado'){


//		$('#btnApproveState, #btnLogicDeleteState, #btnSaveAll, .columnItemsButtons, #btnApproveStateFull').hide();
//		$('#btnCancellState').show();
//		$('#txtCode, #txtNoteCode, #txtDate, #cbxCustomers, #cbxEmployees, #cbxTaxNumbers, #cbxSalesman, #txtDescription, #txtExRate').attr('disabled','disabled');
//		if ($('#btnAddItem').length > 0){//existe
//			$('#btnAddItem').hide();
//		}
//		changeLabelDocumentState('NOTE_APPROVED'); //#UNICORN
					showBittionAlertModal({content:'Se crearon los movimientos correspondientes', btnYes:'Aceptar', btnNo:''});
						$('#bittionBtnYes').click(function(){
							window.location = '../../inv_movements/index_sale_out/document_code:'+ $('#txtGenericCode').val() +'/search:yes';
						});
							

//					$('#boxMessage').html('');
//					showGrowlMessage('ok', 'Movimientos creados.');
				}
				$('#processing').text('');
			},
			error:function(data){
				//$('#boxMessage').html('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>Ocurrio un problema, vuelva a intentarlo<div>');
				showGrowlMessage('error', 'Vuelva a intentarlo.');
				$('#processing').text('');
			}
        });
	}

    function generateClonePendant(){
        showBittionAlertModal({content:'¿Está seguro de CANCELAR esta venta para generar una copia en estado PENDIENTE?, los pagos de la VENTA no sera copiados.'});
        $('#bittionBtnYes').click(function(){
            var arrayItemsDetails = [];
            arrayItemsDetails = getItemsDetails();

            var error = validateBeforeSaveAll(arrayItemsDetails);
            if( error === ''){
                ajax_generate_clone(arrayItemsDetails);
            }else{
                $('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
            }
            hideBittionAlertModal();
        });
    }

    function ajax_generate_clone(arrayItemsDetails){
        $.ajax({
            type:"POST",
            url:urlModuleController + "ajax_generate_clone",
            data:{saleId:$('#txtPurchaseIdHidden').val()
                ,noteCode:$('#txtNoteCode').val()
                ,code:$('#txtGenericCode').val()
                ,date:$('#txtDate').val()
                ,employeeId:$('#cbxEmployees').val()
                ,taxNumberId:$('#cbxTaxNumbers').val()
                ,salesmanId:$('#cbxSalesman').val()
                ,description:$('#txtDescription').val()
                ,discountType:$('input[name=radio]:checked, #rdDiscount').val()
                ,discount:$('#txtDiscount').val()
                ,invoice:$('input[name=checkbox]').is(":checked")
                ,exRate:$('#txtExRate').val()
                ,arrayItemsDetails: arrayItemsDetails
            },
            beforeSend: showProcessing(),
            success: function(data){
                $('#boxMessage').html('');//this for order goes here
                $('#processing').text('');//this must go at the begining not at the end, otherwise, it won't work when validation is send
                var dataReceived = data.split('|');
                if(dataReceived[0] === 'success'){
                    window.location = urlModuleController + urlAction + '/id:' +dataReceived[1];
                }
            },
            error:function(data){
                $('#boxMessage').html('');
                $('#processing').text('');
                setOnError();
            }
        });
    }

	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	$('#btnApproveStateFull').click(function(){
		changeStateApprovedFull();
		return false;
	});
	
	function changeStateApprovedFull(){
		showBittionAlertModal({content:'Al APROBAR este documento ya no se podrá hacer más modificaciones y se crearan MOVs y FACs. ¿Está seguro?'});
		$('#bittionBtnYes').click(function(){
			var arrayItemsDetails = [];
			arrayItemsDetails = getItemsDetails();
			var error = validateBeforeSaveAll(arrayItemsDetails);
			if( error === ''){
				if(urlAction === 'save_order'){
					ajax_change_state_approved_movement_in_full(arrayItemsDetails);
				}
			}else{
				$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
			}
			hideBittionAlertModal();
		});
	}
	
	function ajax_change_state_approved_movement_in_full(arrayItemsDetails){
		$.ajax({
            type:"POST",
            url:urlModuleController + "ajax_change_state_approved_movement_in_full",			
            data:{arrayItemsDetails: arrayItemsDetails 
				  ,purchaseId:$('#txtPurchaseIdHidden').val()
				  ,date:$('#txtDate').val()
				  ,customer:$('#cbxCustomers').val()
				  ,employee:$('#cbxEmployees').val()
				  ,taxNumber:$('#cbxTaxNumbers').val()
				  ,salesman:$('#cbxSalesman').val()	
				  ,description:$('#txtDescription').val()
				  ,exRate:$('#txtExRate').val()
				   ,discount:$('#txtDiscount').val()
				  ,note_code:$('#txtNoteCode').val()
				  ,genericCode:$('#txtGenericCode').val()
			  },
            beforeSend: showProcessing(),
            success: function(data){			
				var arrayCatch = data.split('|');
				if(arrayCatch[0] === 'aprobado'){


//		$('#txtCode').val(DATA[2]);
//		$('#txtGenericCode').val(DATA[3]);
		$('#btnApproveState, #btnLogicDeleteState, #btnSaveAll, .columnItemsButtons, #btnApproveStateFull').hide();
		$('#btnCancellState').show();
		$('#txtCode, #txtNoteCode, #txtDate, #cbxCustomers, #cbxEmployees, #cbxTaxNumbers, #cbxSalesman, #txtDescription, #txtExRate, #txtDiscount').attr('disabled','disabled');
		if ($('#btnAddItem').length > 0){//existe
			$('#btnAddItem').hide();
		}
		changeLabelDocumentState('NOTE_APPROVED'); //#UNICORN
		


					$('#boxMessage').html('');
					showGrowlMessage('ok', 'Entrada aprobada.');
				}
				$('#processing').text('');
			},
			error:function(data){
				//$('#boxMessage').html('<div class="alert alert-error"><button type="button" class="close" data-dismiss="alert">&times;</button>Ocurrio un problema, vuelva a intentarlo<div>');
				showGrowlMessage('error', 'Vuelva a intentarlo.');
				$('#processing').text('');
			}
        });
	}
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
	
//END SCRIPT	
});
