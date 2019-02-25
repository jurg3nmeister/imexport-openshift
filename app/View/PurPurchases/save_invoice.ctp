<?php echo $this->Html->script('modules/PurPurchases', FALSE); ?>

<!-- ************************************************************************************************************************ -->
<div class="span12"><!-- START CONTAINER FLUID/ROW FLUID/SPAN12 - FORMATO DE #UNICORN -->
<!-- ************************************************************************************************************************ -->
	<!-- //******************************** START - #UNICORN  WRAP FORM BOX PART 1/2 *************************************** -->
	<?php 
		switch ($documentState){
			case '':
				$documentStateColor = '';
				$documentStateName = 'SIN ESTADO';
				break;
			case 'PINVOICE_PENDANT':
				$documentStateColor = 'label-warning';
				$documentStateName = 'FACTURA PENDIENTE';
				break;
			case 'PINVOICE_APPROVED':
				$documentStateColor = 'label-success';
				$documentStateName = 'FACTURA APROBADA';
				break;
			case 'PINVOICE_CANCELLED':
				$documentStateColor = 'label-important';
				$documentStateName = 'FACTURA CANCELADA';
				break;
		}
	?>
	<!-- //////////////////////////// Start - buttons /////////////////////////////////-->
	<div class="widget-box">
		<div class="widget-content nopadding">
			<?php 
				/////////////////START - SETTINGS BUTTON CANCEL /////////////////
				$url=array('action'=>'index_invoice');
				$parameters = $this->passedArgs;
				if(!isset($parameters['search'])){
//					unset($parameters['document_code']);
					unset($parameters['code']);
				}
				unset($parameters['id']);
				echo $this->Html->link('<i class=" icon-arrow-left"></i> Volver', array_merge($url,$parameters), array('class'=>'btn', 'escape'=>false)).' ';
				//////////////////END - SETTINGS BUTTON CANCEL /////////////////
			?>

			<?php 
				switch ($documentState){
							case '':
								$displayApproved = 'none';
								$displayCancelled = 'none';
								$displayPays = 'none';
								$displayCosts = 'none';
								$displayGenerate = 'none';
                                                                $displayClone = 'none';
								break;
							case 'PINVOICE_PENDANT':
								$displayApproved = 'inline';
								$displayCancelled = 'none';
								if($paid == true){
									$displayPays = 'none';
								}else{
									$displayPays = 'inline';
								}								
								$displayCosts = 'inline';
								$displayGenerate = 'none';
                                                                $displayClone = 'none';
								break;
							case 'PINVOICE_APPROVED':
								$displayApproved = 'none';
								
								if($paid == true){
									$displayPays = 'none';
								}else{
									$displayPays = 'inline';
								}	                                                              
								$displayCosts = 'none';
								$displayGenerate = 'none';
                                                                if($cloneStatus == 'YES'){
                                                                    $displayCancelled = 'none';
                                                                    $displayClone = 'none';
                                                                }else{
                                                                    $displayCancelled = 'inline';
                                                                    $displayClone = 'inline';
                                                                }
								break;
							case 'PINVOICE_CANCELLED':
								$displayApproved = 'none';
								$displayCancelled = 'none';
								$displayPays = 'none';
								$displayCosts = 'none';
								$displayGenerate = 'inline';
                                                                $displayClone = 'none';
								break;
						}
//				if (current($movementState) === 'CANCELLED' AND $documentState != 'PINVOICE_CANCELLED'){
//					$displayGMovement = 'none';
//					$displayEMovement = 'none';
//					$displayGeMovement = 'inline';
//				}elseif (current($movementState) === 'CANCELLED'){
//					$displayGMovement = 'inline';
//					$displayEMovement = 'none';
//					$displayGeMovement = 'none';	
//				}elseif (current($movementState) === 'LOGIC_DELETED'){
//					$displayGMovement = 'none';
//					$displayEMovement = 'inline';
//					$displayGeMovement = 'none';
//				}elseif (current($movementState) !== 'LOGIC_DELETED') {
//					$displayGMovement = 'inline';
//					$displayEMovement = 'none';
//					$displayGeMovement = 'none';
//				}
			?>
			<?php
			if($documentState == 'PINVOICE_PENDANT' OR $documentState == ''){
				echo $this->BootstrapForm->submit('Guardar Cambios',array('class'=>'btn btn-primary','div'=>false, 'id'=>'btnSaveAll'));	
			}
			?>
			<a href="#" id="btnApproveState" class="btn btn-success" style="display:<?php echo $displayApproved;?>"> Aprobar Factura de Compra</a>
			<a href="#" id="btnLogicDeleteState" class="btn btn-danger" style="display:<?php echo $displayApproved;?>"><i class=" icon-trash icon-white"></i> Eliminar</a>
			<a href="#" id="btnCancellState" class="btn btn-danger" style="display:<?php echo $displayCancelled;?>"> Cancelar Factura de Compra</a>
			<a href="#" id="btnGenerateClonePendant" class="btn btn-info" style="display:<?php echo $displayClone;?>"> Clonar esta nota para modificarla</a>
			
			
			<?php
				$displayPrint = 'none';
				if($id <> ''){
					$displayPrint = 'inline';
				}
				echo $this->Html->link('<i class="icon-print icon-white"></i> Imprimir', array('action' => 'view_document_movement_pdf', $id.'.pdf'), array('class'=>'btn btn-primary','style'=>'display:'.$displayPrint, 'escape'=>false, 'title'=>'Nuevo', 'id'=>'btnPrint', 'target'=>'_blank')); 

			?>
			
		<!-- 	<a href="#" id="btnGenerateDocs" class="btn btn-inverse" style="display:<?php echo $displayGenerate;?>"> Generar Documento en Estado Pendiente</a>
			
			<a href="#" id="btnGoMovements" class="btn btn-inverse" style="display:<?php echo $displayApproved;?>"> Ver Movimientos</a>
			
			<div class=" pull-right">		
					<a href="#" id="btnGoMovements" class="btn" style="display:<?php echo $displayGMovement;?>"> Movimiento(s)</a>
					<a href="#" id="btnEnableMovements" class="btn" style="display:<?php echo $displayEMovement;?>"> Generar Movimiento(s)</a>
					<a href="#" id="btnGenerateMovements" class="btn" style="display:<?php echo $displayGeMovement;?>"> Generar Movimiento(s)</a>
			</div>	-->
			
		</div>
	</div>
	<!-- //////////////////////////// End - buttons /////////////////////////////////-->
	
	<div class="widget-box">
		<div class="widget-title">
			<span class="icon">
				<i class="icon-edit"></i>								
			</span>
			<h5>Factura de Compra</h5>																							
			<span id="documentState" class="label <?php echo $documentStateColor;?>"><?php echo $documentStateName;?></span><input type="hidden" value="<?php echo $documentState; ?>" id="inputDocumentState" >
		</div>
		<div class="widget-content nopadding">
			
	<!-- //******************************** END - #UNICORN  WRAP FORM BOX PART 1/2 *************************************** -->
	
	<!-- ////////////////////////////////// START - FORM STARTS ///////////////////////////////////// -->
		<?php echo $this->BootstrapForm->create('PurPurchase', array('class' => 'form-horizontal'));?>
		<fieldset>
	<!-- ////////////////////////////////// END - FORM ENDS /////////////////////////////////////// -->			
					
				
				<!-- ////////////////////////////////// START FORM INVOICE FIELDS /////////////////////////////////////// -->
				<?php
				//////////////////////////////////START - block when APPROVED or CANCELLED///////////////////////////////////////////////////
				$disable = 'disabled';
				$disableDiscType = 'disabled';
//				$disable2 = 'disabled';
//				
				if($documentState == 'PINVOICE_PENDANT' OR $documentState == ''){
//				if(($documentState == 'PINVOICE_PENDANT' OR $documentState == '') AND current($movementState) !== 'APPROVED' AND current($movementState) !== 'CANCELLED' AND current($movementState) !== 'LOGIC_DELETED' ){
					$disable = 'enabled';
					$disableDiscType = '';
				}
				
				//////////////////////////////////END - block when APPROVED or CANCELLED///////////////////////////////////////////////////
				///////////////////////////////
				if($discountType == 2){
					$displayDiscount = 'inline';
					$append = '%';
				}else if($discountType == 3){
					$displayDiscount = 'inline';
					$append = '$us.';
				}else{
					$displayDiscount = 'none';
					$append = '*';
				}
				//////////////////////////////// 
				
				echo $this->BootstrapForm->input('purchase_hidden', array(
					'id'=>'txtPurchaseIdHidden',
					'value'=>$id,
					'type'=>'hidden'
				));
							
				echo $this->BootstrapForm->input('doc_code', array(
					'id'=>'txtCode',
					'type'=>'hidden'
//					'label'=>'Código:',
//					'style'=>'background-color:#EEEEEE',
//					'disabled'=>$disable2,
//					'placeholder'=>'El sistema generará el código'
				));
				
                                echo $this->BootstrapForm->input('clone', array(
					'id'=>'txtClone',
					'type'=>'hidden',
                                        'value'=>$cloneStatus
				));
                                
//				echo $this->BootstrapForm->input('origin_code', array(
//					'id'=>'txtOriginCode',
//					'label'=>'Documento Origen:',
//					'style'=>'background-color:#EEEEEE',
////					'disabled'=>$disable2,
//					'disabled'=>$disable,
//					'value'=>$originCode,
//				));
				
				echo $this->BootstrapForm->input('generic_code', array(
					'id'=>'txtGenericCode',
					'value'=>$genericCode,
					'type'=>'hidden'
				
				));
				
				echo $this->BootstrapForm->input('note_code', array(
					'id'=>'txtNoteCode',
					'label' => 'No. Factura de Compra:',
					'disabled'=>$disable,
					'style'=>'text-transform: uppercase;'	
				));
				
				echo $this->BootstrapForm->input('date_in', array(
					'label' => 'Fecha:',
					'id'=>'txtDate',
					'value'=>$date,
					'disabled'=>$disable,
					'maxlength'=>'0',
					'class'=>'input-date-type'
				));
				
				echo $this->BootstrapForm->input('inv_warehouse_id', array(
					'label' => 'Almacén:',
					'id'=>'cbxWarehouses',
					'class'=>'span4',
					'disabled'=>$disable
				));
				
//				echo $this->BootstrapForm->input('inv_supplier_id', array(
//					'label' => 'Proveedor:',
//					'id'=>'cbxSuppliers',
//					'disabled'=>$disable2
//				));
				
				echo $this->BootstrapForm->input('description', array(
					'rows' => 2,
					'label' => 'Descripción:',
					'disabled'=>$disable,
					'id'=>'txtDescription'
				));
						
				echo $this->BootstrapForm->input('discount_type', array(
					'label' => 'Tipo de Descuento:',						
					'id'=>'rdDiscount',
					'type'=>'radio',
					'name' => 'radio',
					'disabled'=>$disableDiscType,
//					'class'=>'inline',
//					'class'=>'control-label',
					'style'=>'margin-left:10px; margin-right:10px; margin-top:2px; margin-bottom:5px; ',
					'options' => array(
						'1' => 'Ninguno',
						'2' => 'Porcentage',
						'3' => 'Monto'
					),
					'value'=> $discountType,
					'default'=> 1
				));
				
				echo '<div id="boxDiscount" style="display:'.$displayDiscount.'">';
				echo $this->BootstrapForm->input('discount', array(
					'label' => 'Descuento:',
					'disabled'=>$disable,
					'id'=>'txtDiscount',
					'value'=>$discount,
					'type'=>'text'
					,'append' => $append
				));
				echo '</div>';
				
				echo '<div id="boxExRate">';
					echo $this->BootstrapForm->input('ex_rate', array(
						'label' => 'Tipo de Cambio:',
						'value'=>$exRate,
						'disabled'=>'disabled',
						'id'=>'txtExRate',
						'type'=>'text'
						,'append' => 'Bs.'
					));
				echo '</div>';
				?>
				<!-- ////////////////////////////////// END FORM INVOICE FIELDS /////////////////////////////////////// -->
				
					<!-- ////////////////////////////////// START MESSAGES /////////////////////////////////////// -->
					<div id="boxMessage"></div>
					<div id="processing"></div>
					<!-- ////////////////////////////////// END MESSAGES /////////////////////////////////////// -->

	<!-- ////////////////////////////////// START - END FORM ///////////////////////////////////// -->		
	</fieldset>
	<?php echo $this->BootstrapForm->end();?>
	<!-- ////////////////////////////////// END - END FORM ///////////////////////////////////// -->
	
	<!-- //******************************** START - #UNICORN  WRAP FORM BOX PART 2/2 *************************************** -->
		</div> <!-- Belongs to: <div class="widget-content nopadding"> -->
	</div> <!-- Belongs to: <div class="widget-box"> -->
	<!-- //******************************** END - #UNICORN  WRAP FORM BOX PART 2/2 *************************************** -->
	
	<!-- ////////////////////////////////// START - INVOICE DETAILS /////////////////////////////////////// -->
	
	<div class="widget-box">
		<div class="widget-title">
			<ul class="nav nav-tabs">
				<li class="active"><a data-toggle="tab" href="#tab1">Productos</a></li>
				<li><a data-toggle="tab" href="#tab2">Costos</a></li>
                <li><a data-toggle="tab" href="#tab3">Pagos</a></li>
			</ul>
		</div>
		<div class="widget-content tab-content">
			<div id="tab1" class="tab-pane active">
				<!-- ////////////////////////////////// START - INVOICE ITEMS DETAILS /////////////////////////////////////// -->		
				<?php if(($documentState == 'PINVOICE_PENDANT' OR $documentState == '')/* AND current($movementState) !== 'APPROVED' AND current($movementState) !== 'CANCELLED' */){ ?>
					<a class="btn btn-primary" href='#' id="btnAddItem" title="Adicionar Item"><i class="icon-plus icon-white"></i></a>
				<?php } ?>
						<?php $limit = count($purDetails); $counter = $limit;?>
						<table class="table table-bordered table-hover data-table" id="tablaItems">
							<thead>
								<tr>
									<th>Productos ( <span id="countItems"><?php echo $limit;?> </span> )</th>
									<th>Proveedor</th>
									<th>Cantidad (u.)</th>
									<th>Precio Unitario ($us.)</th>
									<th>Subtotal ($us.)</th>
									<?php if(($documentState == 'PINVOICE_PENDANT' OR $documentState == '')/* AND current($movementState) !== 'APPROVED' AND current($movementState) !== 'CANCELLED' */){ ?>
									<th class="columnItemsButtons">Acciones</th>
									<?php }?>
								</tr>
							</thead>
							<tbody>
								<?php
								$total = '0.00';
								$totalQuantity = '0';
								for($i=0; $i<$limit; $i++){
//									$subtotal = ($purDetails[$i]['cantidad'])*($purDetails[$i]['exFobPrice']);
									echo '<tr id="itemRow'.$purDetails[$i]['itemId'].'s'.$purDetails[$i]['supplierId'].'">';				//type="hidden"
										echo '<td><span id="spaItemName'.$purDetails[$i]['itemId'].'">'.$purDetails[$i]['item'].'</span><input type="hidden" value="'.$purDetails[$i]['itemId'].'" id="txtItemId" ></td>';
										echo '<td><span id="spaSupplier'.$purDetails[$i]['itemId'].'">'.$purDetails[$i]['supplier'].'</span><input type="hidden" value="'.$purDetails[$i]['supplierId'].'" id="txtSupplierId'.$purDetails[$i]['itemId'].'" ></td>';
										echo '<td><span id="spaQuantity'.$purDetails[$i]['itemId'].'s'.$purDetails[$i]['supplierId'].'">'.$purDetails[$i]['cantidad'].'</span></td>';
										echo '<td><span id="spaExFobPrice'.$purDetails[$i]['itemId'].'s'.$purDetails[$i]['supplierId'].'">'.$purDetails[$i]['exFobPrice'].'</span></td>';
//										echo '<td><span id="spaSubtotal'.$purDetails[$i]['itemId'].'s'.$purDetails[$i]['supplierId'].'">'.number_format($subtotal, 2, '.', '').'</span></td>';
										echo '<td><span id="spaExSubtotal'.$purDetails[$i]['itemId'].'s'.$purDetails[$i]['supplierId'].'">'.$purDetails[$i]['exSubtotal'].'</span></td>';
										
										if(($documentState == 'PINVOICE_PENDANT' OR $documentState == '')/* AND current($movementState) !== 'APPROVED' AND current($movementState) !== 'CANCELLED' */){
											echo '<td class="columnItemsButtons">';
											echo '<a class="btn btn-primary" href="#" id="btnEditItem'.$purDetails[$i]['itemId'].'s'.$purDetails[$i]['supplierId'].'" title="Editar"><i class="icon-pencil icon-white"></i></a>

												<a class="btn btn-danger" href="#" id="btnDeleteItem'.$purDetails[$i]['itemId'].'s'.$purDetails[$i]['supplierId'].'" title="Eliminar"><i class="icon-trash icon-white"></i></a>';
											echo '</td>';
										}
									echo '</tr>';
									$totalQuantity += $purDetails[$i]['cantidad'];
									$total += $purDetails[$i]['exSubtotal'];
								}?>
							</tbody>
							<tfoot>
								<tr>
									<td></td>
									<td></td>
									<td><h4 id="totQnty" ><?php echo $totalQuantity.' u.'; ?></h4></td>
									<?php if($discount > 0){ ?>
									<td><h6 id="totalLabel">Monto sin </br> descuento:</h6></td>
									<?php }else{ ?>
									<td><h4 id="totalLabel">Total:</h4></td>
									<?php }?>
									<td><h4 id="total" ><?php echo number_format($total, 2, '.', '').' $us.'; ?></h4></td>
									<?php if($documentState == 'PINVOICE_PENDANT' OR $documentState == ''){ ?>
									<td class="columnItemsButtons"></td>
									<?php }?>
								</tr>
								<?php if($discount > 0 || $discountType != 1){ 
										if($discountType == 2){
											$discountedAmount = ($discount * $total) / 100;
										}else if($discountType == 3){
											$discountedAmount = $discount;
										}		
//										$discountedAmount = ($discount * $total) / 100;
										$totalDiscounted = $total - $discountedAmount ?>
									<tr id="discountTr" >
										<td></td>
										<td></td>
										<td></td>
										<td><h4>Descuento:</h4></td>
										<td><h4 id="discAmnt" ><?php echo number_format($discountedAmount, 2, '.', '').' $us.'; ?></h4></td>
										<?php if($documentState == 'PINVOICE_PENDANT' OR $documentState == ''){ ?>
											<td class="columnItemsButtons"></td>
										<?php }?>
									</tr>
									<tr id="totalTr" >
										<td></td>
										<td></td>
										<td></td>
										<td><h4>Total:</h4></td>
										<td><h4 id="totalDisc" ><?php echo number_format($totalDiscounted, 2, '.', '').' $us.'; ?></h4></td>
										<?php if($documentState == 'PINVOICE_PENDANT' OR $documentState == ''){ ?>
											<td class="columnItemsButtons"></td>
										<?php }?>
									</tr>	
								<?php }?>	
							</tfoot>	
						</table>
				<!-- ////////////////////////////////// END INVOICE ITEMS DETAILS /////////////////////////////////////// -->	
			</div>
			<div id="tab2" class="tab-pane">
				<!-- ////////////////////////////////// START - INVOICE COST DETAILS /////////////////////////////////////// -->
					<a class="btn btn-primary" href='#' id="btnAddCost" title="Adicionar Costo" style="display:<?php echo $displayCosts;?>"><i class="icon-plus icon-white"></i></a>
						<?php $limit3 = count($purPrices); $counter3 = $limit3;?>
						<table class="table table-bordered table-striped table-hover" id="tablaCosts">
							<thead>
								<tr>
									<th>Costos Adicionales de Importación</th>
									<th>Monto</th>
									<?php if($documentState == 'PINVOICE_PENDANT' OR $documentState == ''){ ?>
									<th class="columnCostsButtons"></th>
									<?php }?>
								</tr>
							</thead>
							<tbody>
								<?php
								$total3 = '0.00';
								for($i=0; $i<$limit3; $i++){
									echo '<tr id="payRow'.$purPrices[$i]['costId'].'" >';
										echo '<td><span id="spaCostName'.$purPrices[$i]['costId'].'">'.$purPrices[$i]['costCodeName'].'</span><input type="hidden" value="'.$purPrices[$i]['costId'].'" id="txtCostId" ></td>';
										echo '<td><span id="spaCostExAmount'.$purPrices[$i]['costId'].'">'.$purPrices[$i]['costExAmount'].'</span></td>';
										
										if($documentState == 'PINVOICE_PENDANT' OR $documentState == ''){
											echo '<td class="columnCostsButtons">';
											echo '<a class="btn btn-primary" href="#" id="btnEditCost'.$purPrices[$i]['costId'].'" title="Editar"><i class="icon-pencil icon-white"></i></a>
												
												<a class="btn btn-danger" href="#" id="btnDeleteCost'.$purPrices[$i]['costId'].'" title="Eliminar"><i class="icon-trash icon-white"></i></a>';
											echo '</td>';
										}
									echo '</tr>';	
									$total3 += $purPrices[$i]['costExAmount'];
								}?>
							</tbody>
							<tfoot>
								<tr>
									<td><h4>Total:</h4></td>
									<td><h4 id="total3" ><?php echo number_format($total3, 2, '.', '').' $us.'; ?></h4></td>
									<?php if($documentState == 'PINVOICE_PENDANT' OR $documentState == ''){ ?>
										<td class="columnCostsButtons"></td>
									<?php }?>
								</tr>	
							</tfoot>	
						</table>
				<!-- ////////////////////////////////// END INVOICE COST DETAILS /////////////////////////////////////// -->
			</div>
            <div id="tab3" class="tab-pane">
				<!-- ////////////////////////////////// START - INVOICE PAY DETAILS /////////////////////////////////////// -->
					<a class="btn btn-primary" href='#' id="btnAddPay" title="Adicionar Pago" style="display:<?php echo $displayPays;?>"><i class="icon-plus icon-white"></i></a>
						<?php $limit2 = count($purPayments); $counter2 = $limit2;?>
						<table class="table table-bordered table-hover data-table" id="tablaPays">
							<thead>
								<tr>
									<th>Fecha Pago</th>
									<th>Monto</th>
									<th>Descripcion</th>
									<?php if($documentState == 'PINVOICE_PENDANT' OR $documentState == 'PINVOICE_APPROVED' OR $documentState == ''){ ?>
									<th class="columnPaysButtons"></th>
									<?php }?>
								</tr>
							</thead>
							<tbody>
								<?php
								$total2 = '0.00';
								for($i=0; $i<$limit2; $i++){
									echo '<tr id="payRow'.$purPayments[$i]['dateId'].'" >';
										echo '<td><span id="spaPayDate'.$purPayments[$i]['dateId'].'">'.$purPayments[$i]['payDate'].'</span><input type="hidden" value="'.$purPayments[$i]['dateId'].'" id="txtPayDate" ></td>';
										echo '<td><span id="spaPayAmount'.$purPayments[$i]['dateId'].'">'.$purPayments[$i]['payAmount'].'</span></td>';
										echo '<td><span id="spaPayDescription'.$purPayments[$i]['dateId'].'">'.$purPayments[$i]['payDescription'].'</span></td>';
										
										if($documentState == 'PINVOICE_PENDANT' OR $documentState == 'PINVOICE_APPROVED' OR $documentState == ''){
											echo '<td class="columnPaysButtons">';
											echo '<a class="btn btn-primary" href="#" id="btnEditPay'.$purPayments[$i]['dateId'].'" title="Editar"><i class="icon-pencil icon-white"></i></a>
												
												<a class="btn btn-danger" href="#" id="btnDeletePay'.$purPayments[$i]['dateId'].'" title="Eliminar"><i class="icon-trash icon-white"></i></a>';
											echo '</td>';
										}
									echo '</tr>';	
									$total2 += $purPayments[$i]['payAmount'];
								}?>
							</tbody>	
							<tfoot>
								<tr>
									<td><h4>Total:</h4></td>
									<td><h4 id="total2" ><?php echo number_format($total2, 2, '.', '').' $us.'; ?></h4></td>
									<td><h4 id="totalDebt" ><?php 
											if($discount > 0 || $discountType != 1){ 
												echo 'de '.number_format($totalDiscounted, 2, '.', '').' $us.';
											}else{
												echo 'de '.number_format($total, 2, '.', '').' $us.'; 
											}
											?>
										</h4></td>
									<?php if($documentState == 'PINVOICE_PENDANT' OR $documentState == 'PINVOICE_APPROVED' OR  $documentState == ''){ ?>
										<td class="columnPaysButtons"></td>
									<?php }?>
								</tr>	
							</tfoot>	
						</table>
					
				<!-- ////////////////////////////////// END INVOICE PAY DETAILS /////////////////////////////////////// -->
			</div>
		</div>                            
	</div>
								
	<!-- ////////////////////////////////// END INVOICE DETAILS /////////////////////////////////////// -->
		
<!-- ************************************************************************************************************************ -->
</div><!-- END CONTAINER FLUID/ROW FLUID/SPAN12 - MAIN Template #UNICORN -->
<!-- ************************************************************************************************************************ --> 
	




<!-- ////////////////////////////////// START MODAL (Esta fuera del span9 pero sigue pertenciendo al template principal CONTAINER FLUID/ROW FLUID) ////////////////////////////// -->
			<div id="modalAddItem" class="modal hide fade ">
				  
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
					<h3 id="myModalLabel">Cantidad Item</h3>
				  </div>
				  
				  <div class="modal-body">
					<!--<p>One fine body…</p>-->
					<?php
					echo '<div id="boxModalInitiateSupplierItemPrice">';
						//////////////////////////////////////
						echo $this->BootstrapForm->input('suppliers_id', array(
						'label' => 'Proveedor:',
						'id'=>'cbxModalSuppliers',
						'class'=>'span6'
						));
						echo '<br>';
						echo '<br>';
//						echo '<div style="margin-bottom:45px"></div>'; //fix space otherwise won't work 
						echo '<div id="boxModalItemPrice">';
							//////////////////////////////////////
							echo $this->BootstrapForm->input('items_id', array(				
							'label' => 'Item:',
							'id'=>'cbxModalItems',
							'class'=>'span12'
							));
							echo '<br>';
							echo '<br>';
							echo $this->BootstrapForm->input('quantity', array(				
							'label' => 'Cantidad:',
							'id'=>'txtModalQuantity',
							'class'=>'span3',
							'maxlength'=>'10'
							,'append' => 'u.'
							));
							
							echo $this->BootstrapForm->input('ex_subtotal', array(				
							'label' => 'Subtotal:',
							'id'=>'txtModalExSubtotal',
							'class'=>'span3',
							'maxlength'=>'10'
							,'append' => '$us.'
							));
							
//							echo '<div id="boxModalPrice">';
//								$price='';
//								echo $this->BootstrapForm->input('ex_fob_price', array(				
//								'label' => 'Precio Unitario:',
//								'id'=>'txtModalPrice',
//								'value'=>$price,
//								'class'=>'span3',
//								'maxlength'=>'15',
//								'disabled'=>'disabled'
//								));
//							echo '</div>';		
							//////////////////////////////////////
						echo '</div>';		
						//////////////////////////////////////
					echo '</div>';

					
					?>
					  <div id="boxModalValidateItem" class="alert-error"></div> 
				  </div>
				  
				  <div class="modal-footer">
					 <!-- Ztep 0 Save button from modal triggers btnModalAddItem -->
					<a href='#' class="btn btn-primary" id="btnModalAddItem">Guardar</a>
					<a href='#' class="btn btn-primary" id="btnModalEditItem">Guardar</a>
					<button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
					
				  </div>
					
			</div>
<!-- ////////////////////////////////// FIN MODAL (Esta fuera del span9 pero sigue pertenciendo al template principal CONTAINER FLUID/ROW FLUID) ////////////////////////////// -->




<!-- ////////////////////////////////// INICIO MODAL COSTS (Esta fuera del span9 pero sigue pertenciendo al template principal CONTAINER FLUID/ROW FLUID) ////////////////////////////// -->
			<div id="modalAddCost" class="modal hide fade ">
				  
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
					<h3 id="myModalLabel">Montos</h3>
				  </div>
				  
				  <div class="modal-body">
					<!--<p>One fine body…</p>-->
					<?php
					echo '<div id="boxModalInitiateCost">';
						echo $this->BootstrapForm->input('costs_id', array(				
						'label' => 'Costo:',
						'id'=>'cbxModalCosts',
						'class'=>'span12',
						));
					echo '</div>';
					echo $this->BootstrapForm->input('amount', array(				
							'label' => 'Monto:',
							'id'=>'txtModalCostExAmount',
							'class'=>'span3',
							'maxlength'=>'15'
							,'append' => '$us'
						));
					?>
					  <div id="boxModalValidateCost" class="alert-error"></div> 
				  </div>
				  
				  <div class="modal-footer">
					 <!-- Ztep 0 Save button from modal triggers btnModalAddItem -->
					<a href='#' class="btn btn-primary" id="btnModalAddCost">Guardar</a>
					<a href='#' class="btn btn-primary" id="btnModalEditCost">Guardar</a>
					<button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
					
				  </div>
					
			</div>
<!-- ////////////////////////////////// FIN MODAL COSTS(Esta fuera del span9 pero sigue pertenciendo al template principal CONTAINER FLUID/ROW FLUID) ////////////////////////////// -->

<!-- ////////////////////////////////// INICIO MODAL PAYS(Esta fuera del span9 pero sigue pertenciendo al template principal CONTAINER FLUID/ROW FLUID) ////////////////////////////// -->
			<div id="modalAddPay" class="modal hide fade ">
				  
				  <div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
					<h3 id="myModalLabel">Pagos</h3>
				  </div>
				  
				  <div class="modal-body">
					<!-- class="control-group"--> 
					<?php
					echo '<div id="boxModalInitiatePay">';
						$datePay = '';
						echo $this->BootstrapForm->input('date', array(	
								'label' => 'Fecha:',
								'id'=>'txtModalDate',
								'value'=>$datePay,
								'class'=>'span3',
								'maxlength'=>'15',
								'class'=>'input-date-type'
								));
//					
						$payDebt = '';
						echo $this->BootstrapForm->input('amount', array(				
								'label' => 'Monto a Pagar:',
								'id'=>'txtModalPaidAmount',
								'value'=>$payDebt,
								'class'=>'span3',
								'maxlength'=>'15'
								));
						
						echo $this->BootstrapForm->input('debt', array(	
								'value'=>$payDebt,
								'id'=>'txtModalDebtAmount'
								,'type'=>'hidden'
								));
					echo '</div>';
					
					echo $this->BootstrapForm->input('description', array(				
							'label' => 'Descripcion:',
							'id'=>'txtModalDescription',
							'class'=>'span9',
							'rows' => 2
							));
					
//					echo $this->BootstrapForm->input('amount_hidden', array(				
//							'id'=>'txtModalAmountHidden',
//							'type'=>'hidden'
//							));

					?>
					  <div id="boxModalValidatePay" class="alert-error"></div> 
				  </div>
				  
				  <div class="modal-footer">
					 <!-- Ztep 0 Save button from modal triggers btnModalAddItem -->
					<a href='#' class="btn btn-primary" id="btnModalAddPay">Guardar</a>
					<a href='#' class="btn btn-primary" id="btnModalEditPay">Guardar</a>
					<button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>
					
				  </div>
					
			</div>
<!-- ////////////////////////////////// FIN MODAL PAYS (Esta fuera del span9 pero sigue pertenciendo al template principal CONTAINER FLUID/ROW FLUID) ////////////////////////////// -->
