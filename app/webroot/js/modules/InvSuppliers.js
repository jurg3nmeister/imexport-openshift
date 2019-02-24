$(document).ready(function() {
//START SCRIPT
	
	$("#saveSupplier").click(function(event){
//		alert("saveCustomer");
		event.preventDefault();
		var id = $("#txtIdSupplier").val();
		var name = $("#txtNameSupplier").val();
//		var contactId = $("#txtIdSupplierContact").val();
		var address = $("#txtAddressSupplier").val();
		var area = $("#txtAreaSupplier").val();
		var location = $("#txtLocationSupplier").val();
		var country = $("#cbxCountrySupplier").val();
		var phone = $("#txtPhoneSupplier").val();
		var email = $("#txtEmailSupplier").val();
		var website = $("#txtWebsiteSupplier").val();
		
		var error = validateBeforeSaveSupplier(name/*, employeeName*/);
		if(error === ""){
			ajax_save_supplier(id, name, /*employeeId, employeeName,*/ address, phone, email, website, country, area, location);
			$('#boxMessage').html('');
		}else{
			$('#boxMessage').html('<div class="alert-error"><ul>'+error+'</ul></div>');
		}
		
	});
	
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
	
	function ajax_save_supplier(id, name, /*employeeId, employeeName,*/address, phone, email, website, country, area, location){
		$.ajax({
            type:"POST",
            url:urlModuleController + "ajax_save_supplier",			
            data:{id: id, name: name, /*employeeId:employeeId, employeeName:employeeName,*/ address:address, phone:phone, email:email, website:website, country:country, area:area, location:location},
            beforeSend: function(){
				$('#boxProcessing').text(" Procesando...");
			},
            success: function(data){
				var arrayData = data.split('|');
				if(arrayData[0] === "success"){
					showGrowlMessage('ok', 'Cambios guardados.');
					if(arrayData[1] === "add"){
						$("#txtIdSupplier").val(arrayData[2]);
						window.history.pushState('obj', 'newtitle', urlModuleController+urlAction+'/id:'+arrayData[2]);
//						return false;
//						$("#txtIdTaxNumber").val(arrayData[4]);
//						addRowEmployee(arrayData[3], 'Contacto', '', '');
//						addRowTaxNumber(arrayData[4], 'N/a', 'N/a');
					}
//					if(arrayData[1] === "edit"){
//						$("#txtIdCustomer").val(arrayData[2]);
//					}	
					
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
	
	//Employee
	function ajax_save_contact(id, name, title, phone, email, idSupplier){
		$.ajax({
            type:"POST",
            url:urlModuleController + "ajax_save_contact",			
            data:{id: id, name: name, title:title, phone:phone, email:email, idSupplier:idSupplier},
            beforeSend: function(){
				$('#boxProcessingSupplierContact').text(" Procesando...");
			},
            success: function(data){
				var arrayData = data.split('|');
				if(arrayData[0] === "success"){
					showGrowlMessage('ok', 'Cambios guardados.');
					if(arrayData[2] === "add"){
						addRowContact(arrayData[1], name, title, phone, email);
					}
					if(arrayData[2] === "edit"){
						editContact(arrayData[1], name, title, phone, email);
					}
				}else{
					showGrowlMessage('error', 'Vuelva a intentarlo.');
				}
				$('#boxProcessingSupplierContact').text('');
			},
			error:function(data){
				showGrowlMessage('error', 'Vuelva a intentarlo.');
				$('#boxProcessingSupplierContact').text('');
			}
        });
	}
	
//	function ajax_save_tax_number(id, nit, name, idCustomer){
//		$.ajax({
//            type:"POST",
//            url:urlModuleController + "ajax_save_tax_number",			
//            data:{id: id, nit:nit, name: name, idCustomer:idCustomer},
//            beforeSend: function(){
//				$('#boxProcessingTaxNumber').text(" Procesando...");
//			},
//            success: function(data){
//				var arrayData = data.split('|');
//				if(arrayData[0] === "success"){
//					showGrowlMessage('ok', 'Cambios guardados.');
//					if(arrayData[2] === "add"){
//						addRowTaxNumber(arrayData[1], nit, name);
//					}
//					if(arrayData[2] === "edit"){
//						editTaxNumber(arrayData[1], nit, name);
//					}
//				}else{
//					showGrowlMessage('error', 'Vuelva a intentarlo.');
//				}
//				$('#boxProcessingTaxNumber').text('');
//			},
//			error:function(data){
//				showGrowlMessage('error', 'Vuelva a intentarlo.');
//				$('#boxProcessingTaxNumber').text('');
//			}
//        });
//	}
	
	function addRowContact(id, name, title, phone, email) {
		var pruebaRow = createRowContact(id, name, title, phone, email);
		$('#tblSupplierContacts tbody').append(pruebaRow);
		bindButtonEventsRowContact();
		$('#InvSupplierContactVsaveForm input[type=hidden], #InvSupplierContactVsaveForm input[type=text]').val(""); //clear all after add
	}
	
	function createRowContact(id, name, title, phone, email) {
		var rowCount = $('#tblSupplierContacts tbody tr').length + 1;
		var row = '<tr id="rowContact' + id + '">';
		row += '<td style="text-align:center;"><span class="spaNumber">' + rowCount + '</span><input type="hidden" value="' + id + '" class="spaIdContact"></td>';
		row += '<td><span class="spaNameContact">' + name + '</span></td>';
		row += '<td><span class="spaTitleContact">' + title + '</span></td>';
		row += '<td><span class="spaPhoneContact">' + phone + '</span></td>';
		row += '<td><span class="spaEmailContact">' + email + '</span></td>';
		row += '<td>';
		row += '<a href="#" class="btn btn-primary btnRowEditContact" title="Editar"><i class="icon-pencil icon-white"></i></a>';
		row += ' <a href="#" class="btn btn-danger btnRowDeleteContact" title="Eliminar"><i class="icon-trash icon-white"></i></a>';
		row += '</td>';
		row += '</tr>';
		return row;
	}
	
	function addContact(idSupplier) {
		var id = $("#txtIdSupplierContact").val();
		var name = $("#txtNameSupplierContact").val();
		var title = $("#txtTitleSupplierContact").val();
		var phone = $("#txtPhoneSupplierContact").val();
		var email = $("#txtEmailSupplierContact").val();
//		var idCustomer = 
		var error = validateBeforeAddContact(name);

		if (error === "") {
			ajax_save_contact(id, name, title, phone, email, idSupplier);
			$('#boxMessageSupplierContact').html('');
		} else {
			$('#boxMessageSupplierContact').html('<div class="alert-error"><ul>' + error + '</ul></div>');
		}
	}
	
	$("#btnAddSupplierContact").click(function(event) {
		event.preventDefault();
		var idSupplier = $("#txtIdSupplier").val();
		if(idSupplier !== ""){
			addContact(idSupplier);
		}else{
			alert('Debe "Guardar Cambios" del Proveedor antes de adicionar un Contacto');
		}
	});
	
	$("#btnEditSupplierContact").click(function(event) {
		event.preventDefault();
		var id = $("#txtIdSupplierContact").val();
		var name = $("#txtNameSupplierContact").val();
		var title = $("#txtTitleSupplierContact").val();
		var phone = $("#txtPhoneSupplierContact").val();
		var email = $("#txtEmailSupplierContact").val();
		var idSupplier = $("#txtIdSupplier").val();
//		if(idCustomer !== ""){
//			editEmployee(id, name, phone, email);
		var error = validateBeforeAddContact(name);

		if (error === "") {
			ajax_save_contact(id, name, title, phone, email, idSupplier);
			$('#boxMessageSupplierContact').html('');
		} else {
			$('#boxMessageSupplierContact').html('<div class="alert-error"><ul>' + error + '</ul></div>');
		}
//		}else{
//			alert('Debe "Guardar Cambios" del Cliente antes de editar un Empleado');
//		}
		
	});
	
	function bindButtonEventsRowContact() {
		$('#tblSupplierContacts tbody tr:last .btnRowEditContact').bind("click", function(event) {
			editRowContact($(this), event);
		});

		$('#tblSupplierContacts tbody tr:last .btnRowDeleteContact').bind("click", function(event) {
			deleteRowContact($(this), event);
		});
	}

	////////////EVENTS
			

	function reorderRowNumbers(table){
		var counter = 1;
		$('#'+table+' tbody tr').each(function() {
			$(this).find('.spaNumber').text(counter);
			counter++;
		});
	}
	

	
	function validateBeforeAddContact(name) {
		var error = '';
		if (name === '') {
			error += '<li> El campo "Nombre" de Conacto no puede estar vacio </li>';
		}
		return error;
	}
	
	function validateBeforeSaveSupplier(name/*, employeeName*/) {
		var error = '';
		if (name === '') {
			error += '<li> El campo "Nombre Compañia" del Proveedor no puede estar vacio </li>';
		}
//		if (employeeName === '') {
//			error += '<li> El campo "Responsable" del Cliente no puede estar vacio </li>';
//		}
		return error;
	}
	
	
	$(".btnRowEditContact").click(function(event) {
		editRowContact($(this), event);
	});

	$(".btnRowDeleteContact").click(function(event) {
		deleteRowContact($(this), event);
		
	});
	

	
	$("#btnCancelSupplierContact").click(function(event) {
		$("#btnAddSupplierContact").show();
		$("#btnEditSupplierContact, #btnCancelSupplierContact").hide();
		$('#InvSupplierContactVsaveForm input[type=hidden], #InvSupplierContactVsaveForm input[type=text]').val("");
		$('#InvSupplierContactVsaveForm input[type=text]').removeAttr('style');
		event.preventDefault();
	});
	
	function editContact(id, name, title, phone, email){
		$("#rowContact"+id).find('.spaNameContact').text(name);
		$("#rowContact"+id).find('.spaTitleContact').text(title);
		$("#rowContact"+id).find('.spaPhoneContact').text(phone);
		$("#rowContact"+id).find('.spaEmailContact').text(email);
		$('#InvSupplierContactVsaveForm input[type=hidden], #InvSupplierContactVsaveForm input[type=text]').val("");
		$('#InvSupplierContactVsaveForm input[type=text]').removeAttr('style');
		$("#btnAddSupplierContact").show();
		$("#btnEditSupplierContact, #btnCancelSupplierContact").hide();
//		highlightTemporally(id);
	}
	//not working :( ??
//	function highlightTemporally(id) {
//		$("#tblEmployees #rowEmployee1").fadeIn(4000).css("background-color", "#FFFF66");
//		setTimeout(function() {
//			$("#rowEmployee1").removeAttr('style');
//		}, 4000);
//	}
	
	///////////PAGE FUNCTIONS
	function editRowContact(object, event) {
		event.preventDefault();
		var objectTableRowSelected = object.closest('tr');
		var id = objectTableRowSelected.find('.spaIdContact').val();
		var name = objectTableRowSelected.find('.spaNameContact').text();
		var title = objectTableRowSelected.find('.spaTitleContact').text();
		var phone = objectTableRowSelected.find('.spaPhoneContact').text();
		var email = objectTableRowSelected.find('.spaEmailContact').text();
		$("#txtIdSupplierContact").val(id);
		$("#txtNameSupplierContact").val(name);
		$("#txtTitleSupplierContact").val(title);
		$("#txtPhoneSupplierContact").val(phone);
		$("#txtEmailSupplierContact").val(email);
		$("#btnAddSupplierContact").hide();
		$("#btnEditSupplierContact, #btnCancelSupplierContact").show();
		$('#InvSupplierContactVsaveForm input[type=text]').css("background-color","#FFFF66");
		//alert(valor);
	}

	function deleteRowContact(object, event) {
		showBittionAlertModal({content: '¿Está seguro de eliminar este contacto?'});
		$('#bittionBtnYes').click(function(event) {
			var objectTableRowSelected = object.closest('tr');
			var id = objectTableRowSelected.find('.spaIdContact').val();
			hideBittionAlertModal();
			ajax_delete_contact(id, objectTableRowSelected);
			event.preventDefault();
		});
		
		event.preventDefault();
	}

	
	function ajax_delete_contact(id, objectTableRowSelected){
		$.ajax({
            type:"POST",
            url:urlModuleController + "ajax_delete_contact",			
            data:{id: id},
            beforeSend: function(){
				$('#boxProcessingSupplierContact').text(" Procesando...");
			},
            success: function(data){
//				var arrayData = data.split('|');
				if(data === "success"){
					showGrowlMessage('ok', 'Cambios guardados.');
					objectTableRowSelected.fadeOut("slow", function() {
						$(this).remove();
						reorderRowNumbers('tblSupplierContacts');//must go inside due the fadeout efect
					});
				}else if(data === "headless"){
					alert("No puede dejar al Cliente sin al menos un Contacto, si desea puede eliminar al Cliente y se eliminaran todos sus Contactos!");
				}else if(data === "children"){
					alert("El Contacto tiene Ventas registradas, no se puede eliminar!");
				}else{
					showGrowlMessage('error', 'Vuelva a intentarlo.');
				}
				$('#boxProcessingSupplierContact').text('');
			},
			error:function(data){
				showGrowlMessage('error', 'Vuelva a intentarlo.');
				$('#boxProcessingSupplierContact').text('');
			}
        });
	}

	///////////AJAX FUNCTIONS
	
	//Tax Numbers functions
	
	//Employee
	
	
//	function addRowTaxNumber(id, nit, name) {
//		var pruebaRow = createRowTaxNumber(id, nit, name);
//		$('#tblTaxNumbers tbody').append(pruebaRow);
//		bindButtonEventsRowTaxNumber();
//		$('#SalTaxNumberVsaveForm input[type=hidden], #SalTaxNumberVsaveForm input[type=text]').val(""); //clear all after add
//	}
//	
//	function createRowTaxNumber(id, nit, name) {
//		var rowCount = $('#tblTaxNumbers tbody tr').length + 1;
//		var row = '<tr id="rowTaxNumber' + id + '">';
//		row += '<td style="text-align:center;"><span class="spaNumber">' + rowCount + '</span><input type="hidden" value="' + id + '" class="spaIdTaxNumber"></td>';
//		row += '<td><span class="spaNitTaxNumber">' + nit + '</span></td>';
//		row += '<td><span class="spaNameTaxNumber">' + name + '</span></td>';
//		row += '<td>';
//		row += '<a href="#" class="btn btn-primary btnRowEditTaxNumber" title="Editar"><i class="icon-pencil icon-white"></i></a>';
//		row += ' <a href="#" class="btn btn-danger btnRowDeleteTaxNumber" title="Eliminar"><i class="icon-trash icon-white"></i></a>';
//		row += '</td>';
//		row += '</tr>';
//		return row;
//	}
	
//	function addTaxNumber(idCustomer) {
//		var id = $("#txtIdTaxNumber").val();
//		var nit = $("#txtNitTaxNumber").val();
//		var name = $("#txtNameTaxNumber").val();
//		var error = validateBeforeAddTaxNumber(nit, name);
//		if (error === "") {
//			ajax_save_tax_number(id, nit, name,idCustomer);
//			$('#boxMessageTaxNumber').html('');
//		} else {
//			$('#boxMessageTaxNumber').html('<div class="alert-error"><ul>' + error + '</ul></div>');
//		}
//	}
//	
//	$("#btnAddTaxNumber").click(function(event) {
//		event.preventDefault();
//		var idCustomer = $("#txtIdCustomer").val();
//		if(idCustomer !== ""){
//			addTaxNumber(idCustomer);
//		}else{
//			alert('Debe "Guardar Cambios" del Cliente antes de adicionar un Nit');
//		}
//	});
	
//	$("#btnEditTaxNumber").click(function(event) {
//		event.preventDefault();
//		var id = $("#txtIdTaxNumber").val();
//		var nit = $("#txtNitTaxNumber").val();
//		var name = $("#txtNameTaxNumber").val();
//		var idCustomer = $("#txtIdCustomer").val();
////		if(idCustomer !== ""){
////			editEmployee(id, name, phone, email);
//		var error = validateBeforeAddTaxNumber(nit, name);
//		if (error === "") {
//			ajax_save_tax_number(id, nit, name, idCustomer);
//			$('#boxMessageTaxNumber').html('');
//		} else {
//			$('#boxMessageTaxNumber').html('<div class="alert-error"><ul>' + error + '</ul></div>');
//		}
////		}else{
////			alert('Debe "Guardar Cambios" del Cliente antes de editar un Empleado');
////		}
//		
//	});
	
//	function bindButtonEventsRowTaxNumber() {
//		$('#tblTaxNumbers tbody tr:last .btnRowEditTaxNumber').bind("click", function(event) {
//			editRowTaxNumber($(this), event);
//		});
//
//		$('#tblTaxNumbers tbody tr:last .btnRowDeleteTaxNumber').bind("click", function(event) {
//			deleteRowTaxNumber($(this), event);
//		});
//	}

	////////////EVENTS
			
//	function validateBeforeAddTaxNumber(nit, name) {
//		var error = '';
//		if (nit === '') {
//			error += '<li> El campo "Nit" no puede estar vacio </li>';
//		}
//		if (name === '') {
//			error += '<li> El campo "Nombre" no puede estar vacio </li>';
//		}
//		return error;
//	}
//	
//	$(".btnRowEditTaxNumber").click(function(event) {
//		editRowTaxNumber($(this), event);
//	});
//
//	$(".btnRowDeleteTaxNumber").click(function(event) {
//		deleteRowTaxNumber($(this), event);
//		
//	});
	

	
//	$("#btnCancelTaxNumber").click(function(event) {
//		$("#btnAddTaxNumber").show();
//		$("#btnEditTaxNumber, #btnCancelTaxNumber").hide();
//		$('#SalTaxNumberVsaveForm input[type=hidden], #SalTaxNumberVsaveForm input[type=text]').val("");
//		$('#SalTaxNumberVsaveForm input[type=text]').removeAttr('style');
//		event.preventDefault();
//	});
//	
//	function editTaxNumber(id, nit, name){
//		$("#rowTaxNumber"+id).find('.spaNitTaxNumber').text(nit);
//		$("#rowTaxNumber"+id).find('.spaNameTaxNumber').text(name);
//		$('#SalTaxNumberVsaveForm input[type=hidden], #SalTaxNumberVsaveForm input[type=text]').val("");
//		$('#SalTaxNumberVsaveForm input[type=text]').removeAttr('style');
//		$("#btnAddTaxNumber").show();
//		$("#btnEditTaxNumber, #btnCancelTaxNumber").hide();
//	}

	
	///////////PAGE FUNCTIONS
//	function editRowTaxNumber(object, event) {
//		event.preventDefault();
//		var objectTableRowSelected = object.closest('tr');
//		var id = objectTableRowSelected.find('.spaIdTaxNumber').val();
//		var nit = objectTableRowSelected.find('.spaNitTaxNumber').text();
//		var name = objectTableRowSelected.find('.spaNameTaxNumber').text();
//		
//		$("#txtIdTaxNumber").val(id);
//		$("#txtNitTaxNumber").val(nit);
//		$("#txtNameTaxNumber").val(name);
//		$("#btnAddTaxNumber").hide();
//		$("#btnEditTaxNumber, #btnCancelTaxNumber").show();
//		$('#SalTaxNumberVsaveForm input[type=text]').css("background-color","#FFFF66");
//		//alert(valor);
//	}
//
//	function deleteRowTaxNumber(object, event) {
//		showBittionAlertModal({content: '¿Está seguro de eliminar este nit?'});
//		$('#bittionBtnYes').click(function(event) {
//			var objectTableRowSelected = object.closest('tr');
//			var id = objectTableRowSelected.find('.spaIdTaxNumber').val();
//			hideBittionAlertModal();
//			ajax_delete_tax_number(id, objectTableRowSelected);
//			event.preventDefault();
//		});
//		
//		event.preventDefault();
//	}

	
//	function ajax_delete_tax_number(id, objectTableRowSelected){
//		$.ajax({
//            type:"POST",
//            url:urlModuleController + "ajax_delete_tax_number",			
//            data:{id: id},
//            beforeSend: function(){
//				$('#boxProcessingTaxNumber').text(" Procesando...");
//			},
//            success: function(data){
////				var arrayData = data.split('|');
//				if(data === "success"){
//					showGrowlMessage('ok', 'Cambios guardados.');
//					objectTableRowSelected.fadeOut("slow", function() {
//						$(this).remove();
//						reorderRowNumbers('tblTaxNumbers');//must go inside due the fadeout efect
//					});
//				}else if(data === "children"){
//					alert("El Nit ya fue usado en Ventas, no se puede eliminar!");
//				}else{
//					showGrowlMessage('error', 'Vuelva a intentarlo.');
//				}
//				$('#boxProcessingTaxNumber').text('');
//			},
//			error:function(data){
//				showGrowlMessage('error', 'Vuelva a intentarlo.');
//				$('#boxProcessingTaxNumber').text('');
//			}
//        });
//	}
	
//END SCRIPT	
});

