<?php echo $this->Html->script('modules/SalSalesIndex', FALSE); ?> 
<!--<div class="row-fluid">--> <!-- No va porque ya esta dentro del row-fluid del container del template principal-->
<?php echo  $this->BootstrapPaginator->options(array('url' => $this->passedArgs));?>
<!-- ************************************************************************************************************************ -->
<div class="span12"><!-- START CONTAINER FLUID/ROW FLUID/SPAN12 - FORMATO DE #UNICORN -->
<!-- ************************************************************************************************************************ -->
<h3>	<?php echo $this->Html->link('<i class="icon-plus icon-white"></i>', array('action' => 'save_invoice'), array('class'=>'btn btn-primary', 'escape'=>false, 'title'=>'Nuevo')); ?> 
			<?php echo __('NOTAS DE REMISION');?></h3>
<!-- *********************************************** #UNICORN SEARCH WRAP ********************************************-->
		<div class="widget-box">
			<div class="widget-title">
				<span class="icon">
					<i class="icon-search"></i>
				</span>
				<h5>Filtro</h5>
			</div>
			<div class="widget-content nopadding">
			<!-- ////////////////////////////////////////INCIO - FORMULARIO BUSQUEDA////////////////////////////////////////////////-->
			<?php echo $this->BootstrapForm->create('SalSale', array('class' => 'form-search', 'novalidate' => true));?>
			<fieldset>
						<?php
//						echo $this->BootstrapForm->input('doc_code', array(				
//										//'label' => 'Codigo Entrada:',
//										'id'=>'txtCode',
//										'value'=>$doc_code,
//										'placeholder'=>'Codigo'
//										));
						echo $this->BootstrapForm->input('searchDate', array(				
							'id'=>'txtDate',
							'value'=>$searchDate,
							'placeholder'=>'Fecha',
							'class'=>'input-date-type'
						));
						echo "&nbsp;";
						echo $this->BootstrapForm->input('note_code', array(				
								'id'=>'txtNoteCode',
								'value'=>$note_code,
								'placeholder'=>'Nro. de Nota de Remisión',
								'style'=>'text-transform: uppercase;'	
						));
						?>

					<?php
						echo $this->BootstrapForm->submit('<i class="icon-search icon-white"></i>',array('class'=>'btn btn-primary','div'=>false, 'id'=>'btnSearch', 'title'=>'Buscar'));
						echo "&nbsp;";
						echo $this->BootstrapForm->submit('<i class="icon-trash icon-white"></i>',array('class'=>'btn btn-danger','div'=>false, 'id'=>'btnClearSearch', 'title'=>'Limpiar Busqueda'));
					?>
				
					<div class=" pull-right">
						<a href="#" id="btnDebtsReport" class="btn btn-danger btn-large" style="display:inline"> Deudas</a>	
					</div>
				
			</fieldset>
			<?php echo $this->BootstrapForm->end();?>
			<!-- ////////////////////////////////////////FIN - FORMULARIO BUSQUEDA////////////////////////////////////////////////-->		
			</div>
		</div>
		<!-- *********************************************** #UNICORN SEARCH WRAP ********************************************-->	
<!-- *********************************************** #UNICORN TABLE WRAP ********************************************-->
		<div class="widget-box">
			<div class="widget-title">
				<span class="icon">
					<i class="icon-th"></i>
				</span>
				<h5><?php echo $this->BootstrapPaginator->counter(array('format' => __('Página {:page} de {:pages}, mostrando {:current} registros de {:count} total, comenzando en {:start}, terminando en {:end}')));?></h5>
			</div>
			<div class="widget-content nopadding">
		<!-- *********************************************** #UNICORN TABLE WRAP ********************************************-->

			<?php $cont = $this->BootstrapPaginator->counter('{:start}'); ?>
		<table class="table table-striped table-bordered table-hover">
			<tr>
				<th><?php echo '#';?></th>
		<!--		<th><?php echo $this->BootstrapPaginator->sort('doc_code', 'Código');?></th>	-->
				<th><?php echo $this->BootstrapPaginator->sort('date', 'Fecha');?></th>
		<!--	<th><?php echo $this->BootstrapPaginator->sort('doc_code', 'Código Origen');?></th>	-->
				<th><?php echo $this->BootstrapPaginator->sort('note_code','Numero de Nota de Remisión');?></th>
		<!--	<th><?php echo $this->BootstrapPaginator->sort('note_code', 'Código de Proforma');?></th>	-->
				<th><?php echo $this->BootstrapPaginator->sort('SalCustomer.name','Cliente');?></th>
				<th><?php echo $this->BootstrapPaginator->sort('AdmProfile.first_name','Vendedor');?></th>	
		<!--		<th><?php echo $this->BootstrapPaginator->sort('cost_sum','Costo Total (Bs.)');?></th> -->
				<th><?php echo $this->BootstrapPaginator->sort('lc_state', 'Estado Documento');?></th>	
				<th><?php echo $this->BootstrapPaginator->sort('paid', 'Estado Pago');?></th>	
			</tr>
		<?php foreach ($salSales as $salSale): ?>
			<tr>
				<td><?php echo $cont++;?></td>				
		<!--		<td><?php echo h($salSale['SalSale']['doc_code']); ?>&nbsp;</td>		-->
				<td><?php echo date("d/m/Y", strtotime($salSale['SalSale']['date'])); ?>&nbsp;</td>
		<!--	<td><?php echo h($salSale['SalSale']['doc_code']); ?>&nbsp;</td>		-->
				<td><?php echo h($salSale['SalSale']['note_code']); ?>&nbsp;</td>
		<!--	<td><?php echo h($salSale['SalSale']['note_code']); ?>&nbsp;</td>		-->
				<td><?php echo h($salSale['SalCustomer']['name']); ?>&nbsp;</td>
				<td><?php echo h($salSale['AdmProfile']['first_name'].' '.$salSale['AdmProfile']['last_name1']); ?>&nbsp;</td>	
		<!--		<td><?php echo h($salSale[0]['cost_sum']); ?>&nbsp;</td> -->
				<td><?php 
						$documentState = $salSale['SalSale']['lc_state'];
						$documentReserveState = $salSale['SalSale']['reserve'];
//						if($documentReserveState === null){
//							$documentReserveState = true;
//						}
						switch ($documentState){
							case 'SINVOICE_PENDANT':
								if($documentReserveState === true){
									$stateColor = 'btn-warning';
									$stateName = 'Nota Pendiente';
								}elseif($documentReserveState === false){
									$stateColor = 'btn-info';
									$stateName = 'Nota Reservada';
								}
								break;
							case 'SINVOICE_APPROVED':
								$stateColor = 'btn-success';
								$stateName = 'Nota Aprobada';
								break;
							case 'SINVOICE_CANCELLED':
								$stateColor = 'btn-danger';
								$stateName = 'Nota Cancelada';
								break;						
						}
						///////////START - SETTING URL AND PARAMETERS/////////////
					$url = array();
					$parameters = $this->passedArgs;
						$url['action'] = 'save_invoice';
						$parameters['id']=$salSale['SalSale']['id'];
						
						////////////END - SETTING URL AND PARAMETERS//////////////
					if($documentReserveState === true){	
						echo $this->Html->link('<i class="icon-pencil icon-white"></i>'.__(' '.$stateName),  array_merge($url,$parameters), array('class'=>'btn '.$stateColor, 'escape'=>false, 'title'=>'Editar')); 
					}elseif($documentReserveState === false){
						echo $this->Html->link('<i class="icon-eye-open icon-white"></i>'.__(' '.$stateName),  array_merge($url,$parameters), array('class'=>'btn '.$stateColor, 'escape'=>false, 'title'=>'Editar')); 
					}	
					?>&nbsp;
				</td>
				<td><?php //echo h($salSale['SalSale']['paid']); 
					$payState = $salSale['SalSale']['paid'];
					switch ($payState){
						case '1':
                                                        if($documentState != 'SINVOICE_CANCELLED'){
                                                            $payColor = 'btn-success';
                                                            $payName = 'Nota Pagada';
                                                            echo $this->Html->link('<i class="icon-pencil icon-white"></i>'.__(' '.$payName),  array_merge($url,$parameters), array('class'=>'btn '.$payColor, 'escape'=>false, 'title'=>'Editar'));
                                                        }
                                                        break;
						case '':
                                                        if($documentState != 'SINVOICE_CANCELLED'){
                                                            $payColor = 'btn-danger';
                                                            $payName = 'Nota No Pagada';
                                                            echo $this->Html->link('<i class="icon-pencil icon-white"></i>'.__(' '.$payName),  array_merge($url,$parameters), array('class'=>'btn '.$payColor, 'escape'=>false, 'title'=>'Editar'));
                                                        }
							break;					
					}
					//echo $this->Html->link('<i class="icon-pencil icon-white"></i>'.__(' '.$payName),  array_merge($url,$parameters), array('class'=>'btn '.$payColor, 'escape'=>false, 'title'=>'Editar')); 
				
				?>&nbsp;</td>
			</tr>
		<?php endforeach; ?>
		</table>
<!-- *********************************************** #UNICORN TABLE WRAP ********************************************-->
		</div>
	</div>
	<!-- *********************************************** #UNICORN TABLE WRAP ********************************************-->
		<?php echo $this->BootstrapPaginator->pagination(); ?>
<!-- ************************************************************************************************************************ -->
</div><!-- FIN CONTAINER FLUID/ROW FLUID/SPAN12 - Del Template Principal #UNICORN
<!-- ************************************************************************************************************************ -->
<!--</div>--><!-- No va porque ya esta dentro del row-fluid del container del template principal-->