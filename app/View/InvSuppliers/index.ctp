<?php  echo  $this->BootstrapPaginator->options(array('url' => $this->passedArgs));?>	
<div class="span12">
	<h3>
		<?php echo $this->Html->link('<i class="icon-plus icon-white"></i>', array('action' => 'vsave'), array('class'=>'btn btn-primary', 'escape'=>false, 'title'=>'Nuevo'));?>
		<?php echo __('%s', __('Proveedores'));?>			
	</h3>
	
	<div class="widget-box">
		<div class="widget-title">
			<span class="icon">
				<i class="icon-th"></i>
			</span>
			<h5><?php echo $this->BootstrapPaginator->counter(array('format' => __('Página {:page} de {:pages}, mostrando {:current} de un total de {:count} registros')));?></h5>
		</div>
		<div class="widget-content nopadding">

			<?php $cont = $this->BootstrapPaginator->counter('{:start}');?>
		<table class="table table-striped table-bordered table-hover">
			<tr>
				<th><?php echo ('#');?></th>
				<th><?php echo $this->BootstrapPaginator->sort('Nombre');?></th>
				<th><?php echo $this->BootstrapPaginator->sort('Pais');?></th>
				<th><?php echo $this->BootstrapPaginator->sort('Telefono');?></th>
				<th><?php echo $this->BootstrapPaginator->sort('Correo Electronico');?></th>		
				<th><?php echo $this->BootstrapPaginator->sort('Sitio Web');?></th>		
				<th class="actions"><?php echo __('Acciones');?></th>
			</tr>
		<?php foreach ($invSuppliers as $invSupplier): ?>
			<tr>
				<td><?php echo h($cont++); ?>&nbsp;</td>
				<td><?php echo h($invSupplier['InvSupplier']['name']); ?>&nbsp;</td>
				<td><?php echo h($invSupplier['InvSupplier']['country']); ?>&nbsp;</td>
				<td><?php echo h($invSupplier['InvSupplier']['phone']); ?>&nbsp;</td>		
				<td><?php echo h($invSupplier['InvSupplier']['email']); ?>&nbsp;</td>		
				<td><?php echo h($invSupplier['InvSupplier']['website']); ?>&nbsp;</td>		
				<td class="actions">
					<?php 
							$url = array();
							$parameters = $this->passedArgs;
						
							
							$url['action'] = 'vsave';
							$parameters['id'] = $invSupplier['InvSupplier']['id'];
					   echo $this->Html->link('<i class= "icon-pencil icon-white"></i>',array_merge($url, $parameters),array('class' => 'btn btn-primary', 'escape'=>false, 'title'=>'Editar')); ?>
				<?php echo $this->Form->postLink('<i class= "icon-trash icon-white"></i>', array('action' => 'delete', $invSupplier['InvSupplier']['id']), array('class'=>'btn btn-danger', 'escape'=>false, 'title' => 'Eliminar'), __('Está seguro de eliminar este proveedor y todos sus contactos?', $invSupplier['InvSupplier']['id'])); ?>
				</td>
			</tr>
		<?php endforeach; ?>
		</table>

		<?php echo $this->BootstrapPaginator->pagination(); ?>
		</div>
	</div>
</div>