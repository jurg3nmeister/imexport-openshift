<?php echo $this->Html->script('modules/InvItemsPriceList', FALSE); ?>
<?php  echo  $this->BootstrapPaginator->options(array('url' => $this->passedArgs));?>	
<div class="span12">
		<h3> <!--  <?php echo $this->Html->link('<i class="icon-plus icon-white"></i>', array('action' => 'add'), array('class'=>'btn btn-primary', 'escape'=>false, 'title'=>'Nuevo'));?>  -->
			<?php echo __('Lista de %s', __('Precios'));?>
		</h3> 
	
	<div class="widget-box">
			<div class="widget-title">
				<span class="icon">
					<i class="icon-search"></i>
				</span>
				<h5>Filtro</h5>
			</div>
			<div class="widget-content nopadding">
			<!-- ////////////////////////////////////////INCIO - FORMULARIO BUSQUEDA////////////////////////////////////////////////-->
			<?php echo $this->BootstrapForm->create('InvItem', array('class' => 'form-search', 'novalidate' => true));?>
			<fieldset>
						<?php
						echo "&nbsp;";
						echo $this->BootstrapForm->input('brand', array(											
										'id'=>'cbxBrand',
										'value'=>$valueBrand,
										'options'=>$brands,
										'type'=>'select',
										'label'=>'Marca:&nbsp;',
										'default'=> 0
										));
						echo "&nbsp;";
						echo "&nbsp;";
						echo $this->BootstrapForm->input('price_type', array(											
										'id'=>'cbxPriceType',
										'value'=>$valuePrice,
										'type'=>'select',
										'options'=>array("1"=>"FOB", "8"=>"CIF", "9"=>"VENTA"),
										'label'=>'Tipo de Precio:&nbsp;',
										'default'=> 9
										));
						echo "&nbsp;";
						echo "&nbsp;";
						echo $this->BootstrapForm->input('code', array(											
										'id'=>'txtCode',
										'value'=>$code,
										'placeholder'=>'Codigo producto'
										));
						?>				
			
					<?php
						echo $this->BootstrapForm->submit('<i class="icon-search icon-white"></i>',array('class'=>'btn btn-primary','div'=>false, 'id'=>'btnSearch', 'title'=>'Buscar'));
					?>
				<div class=" pull-right">
					<?php
	//				$displayPrint = 'none';
	//				if($id <> ''){
						$displayPrint = 'inline';
	//				}
					echo $this->Html->link('<i class="icon-print icon-white"></i> Imprimir', array('action' => 'view_prices_list_pdf', $valueBrand, $valuePrice.'.pdf'), array('class'=>'btn btn-primary','style'=>'display:'.$displayPrint, 'escape'=>false, 'title'=>'Nuevo', 'id'=>'btnPrint', 'target'=>'_blank')); 
					?>
				</div>
					<input type="hidden" value="<?php echo $valuePrice; ?>" id="inputPriceType" >
			</fieldset>
			<?php echo $this->BootstrapForm->end();?>
			<!-- ////////////////////////////////////////FIN - FORMULARIO BUSQUEDA////////////////////////////////////////////////-->		
			</div>
		</div>
		<?php if($valuePrice > 1){$currency='Bs.';}else{$currency='$us.';} ?>
		<div class="widget-box">
		<div class="widget-title">
			<span class="icon">
				<i class="icon-th"></i>
			</span>
			<h5><?php echo $this->BootstrapPaginator->counter(array('format' => __('Página {:page} de {:pages}, mostrando {:current} de un total de {:count} registros')));?></h5>
		</div>
		<div class="widget-content nopadding">
		
		<?php $cont = $this->BootstrapPaginator->counter('{:start}');?>
		<table class="table table-striped table-bordered table-hover" id="tablaPrecios">
			<tr>
				<th><?php echo "#";?></th>
				<th><?php echo 'Código';?></th>
				<th><?php echo 'Descripción';?></th>
				<th><?php echo 'Precio Actual ('.$currency.')';?></th>
				<th><?php echo 'Fecha';?></th>
<!--			<th><?php echo 'Categoría';?></th>
				<th><?php echo 'Descripción';?></th>   
				<th style="width:30%"><?php echo 'Descripción';?></th>
				<th><?php echo 'Stock';?></th>		-->
				<th class="actions"><?php echo __('Acciones');?></th>
			</tr>
		<?php foreach ($invItems as $invItem): ?>
			<tr>
				<td><?php echo $cont++;?></td>
				<td><?php echo $invItem['InvItem']['code'].'<input type="hidden" value="'.$invItem['InvItem']['id'].'" id="txtItemId" >'; ?></td>
				<td><?php echo $invItem['InvItem']['name']; ?></td>
<!--				<td><?php echo $invItem['InvBrand']['name']; ?></td>
				<td><?php echo h($invItem['InvCategory']['name']); ?>&nbsp;</td>
				<td><?php echo h($invItem['InvItem']['description']); ?>&nbsp;</td>		-->
				<td style="text-align: center;"><span id="spaPrice<?php echo $invItem['InvItem']['id']; ?>" ><?php echo h($invItem['InvItem']['lastPrice']).'<input type="hidden" value="'.$invItem['InvItem']['priceId'].'" id="txtPriceId" >'; ?></span></td>
				<td style="text-align: center;"><span id="spaDate<?php echo $invItem['InvItem']['id']; ?>" ><?php 
					if($invItem['InvItem']['date'] != 'n/a'){
						echo date("d/m/Y", strtotime($invItem['InvItem']['date'])).'<input type="hidden" value="'.$invItem['InvItem']['priceId'].'" id="txtPriceId" >';
					}else{
						echo h($invItem['InvItem']['date']).'<input type="hidden" value="'.$invItem['InvItem']['priceId'].'" id="txtPriceId" >';
					}
					?></span></td>
				<td class="actions" style="width: 130px">
					<?php	echo '<a class="btn btn-success" href="#" id="btnAddPrice'.$invItem['InvItem']['id'].'" title="Adicionar"><i class="icon-plus icon-white"></i></a>'; ?>
					<?php	if($invItem['InvItem']['priceId'] != ''){
								echo '<a class="btn btn-primary" href="#" id="btnEditPrice'.$invItem['InvItem']['id'].'" title="Editar"><i class="icon-pencil icon-white"></i></a>&nbsp;'; 
								echo '<a class="btn btn-danger" href="#" id="btnDeletePrice'.$invItem['InvItem']['id'].'" title="Borrar"><i class="icon-trash icon-white"></i></a>'; 
							} ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</table>
		</div>
		</div>

		<?php echo $this->BootstrapPaginator->pagination(); ?>
	
		<!-- Prices Modal -->
<div id="modalAddPrice" class="modal hide fade">
				  
	<div class="modal-header">
	  <button type="button" class="close" data-dismiss="modal" aria-hidden="true">x</button>
	  <h3 id="myModalLabel">Precios</h3>
	</div>

	<div class="modal-body ">
	  
	  <?php
		echo $this->BootstrapForm->input('item_id_hidden', array(	
			'id'=>'txtModalItemId'
			,'type'=>'hidden'
			));	  
		
		echo $this->BootstrapForm->input('price_id_hidden', array(	
			'id'=>'txtModalPriceId'
			,'type'=>'hidden'
			));	  
		
		echo $this->BootstrapForm->input('last_price_hidden', array(	
			'id'=>'txtModalLastPrice'
			,'type'=>'hidden'
			));	
		
		echo $this->BootstrapForm->input('last_date_hidden', array(	
			'id'=>'txtModalLastDate'
			,'type'=>'hidden'
			));	
		
		echo $this->BootstrapForm->input('price', array(
			'id' => 'txtModalPrice',
			'label' => '* Monto:',
			'required' => 'required'
			,'append' => $currency	
			//'type'=>'number'
			//'helpInline' => '<span class="label label-important">' . __('Requerido') . '</span>&nbsp;'
			)
		);
		
		echo '<div id="boxModalPriceDateDescription">';	
			echo $this->BootstrapForm->input('date', array(			
				'id' => 'txtModalDate',
				'label' => '* Fecha:',
				'required' => 'required',
				'class'=>'input-date-type'
				//'helpInline' => '<span class="label label-important">' . __('Requerido') . '</span>&nbsp;'
				)
			);		

			echo $this->BootstrapForm->input('description', array(
				'id' => 'txtModalDescription',
				'label' => 'Descripción:',
				'type'=>'textarea',
				'class'=>'span12'
			));		
		echo '</div>';
	  ?>
		<div id="boxModalValidatePrice" class="alert-error"></div> 
	</div>

	<div class="modal-footer">
	  <a href='#' class="btn btn-primary" id="btnModalAddPrice">Guardar</a>
	  <a href='#' class="btn btn-primary" id="btnModalEditPrice">Guardar</a>
	  <button class="btn" data-dismiss="modal" aria-hidden="true">Cancelar</button>

	</div>
					
</div>
</div>
