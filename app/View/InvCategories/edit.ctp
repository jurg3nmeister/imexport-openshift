<?php echo $this->Html->css('http://ajax.googleapis.com/ajax/libs/jqueryui/1.11.1/themes/ui-lightness/jquery-ui.css'); ?>
<?php echo $this->Html->css('evol.colorpicker'); ?>
<?php echo $this->Html->script('evol.colorpicker.min', FALSE); ?>
<?php echo $this->Html->script('myColorPicker', FALSE); ?>
<div class="span12">
	<div class="widget-box">
	<div class="widget-title">
		<span class="icon">
			<i class="icon-edit"></i>								
		</span>
		<h5>Editar Categoría</h5>			
	</div>
	<div class="widget-content nopadding">
	<?php echo $this->BootstrapForm->create('InvCategory', array('class' => 'form-horizontal'));?>
		<fieldset>			
			<?php
			echo $this->BootstrapForm->input('name', array(
				'rows' => 3,		
				'style'=>'width:400px',
				'label'=>'* Nombre',
				'required' => 'required'
//				'helpInline' => '<span class="label label-important">' . __('Requerido') . '</span>&nbsp;'
			));
			// change descripcion -> description
			echo $this->BootstrapForm->input('description', array(
				'rows' => 5,
				'style'=>'width:400px',
				'label'=>'* Descripción',
				'required' => 'required'
//				'helpInline' => '<span class="label label-important">' . __('Requerido') . '</span>&nbsp;'
			));
            echo $this->BootstrapForm->input('color', array(
                'label' => '* Color',
                'style'=>'width:200px',
                'required' => 'required',
//                'helpInline' => '<span class="label label-important">' . __('Requerido') . '</span>&nbsp;',
                'id'=>'mycolor'
                //'options' => $colors,
                //'empty' => array('name' => 'Elija un color', 'value' => '', 'disabled' => TRUE, 'selected' => TRUE),
                //'class'=>'span3'
            ));
			echo $this->BootstrapForm->hidden('id');
			?>

		<div class="row-fluid">
			<div class="span2"></div>
			<div class="span6">
			<div class="btn-toolbar">
			<?php echo $this->BootstrapForm->submit('Guardar', array('id'=>'saveButton', 'class' => 'btn btn-primary', 'div' => false));
				   echo $this->Html->link('Cancelar', array('action' => 'index'), array('class'=>'btn') );
			?>
			</div>				
			</div>
			<div class="span4"></div>
		</div>	
		</fieldset>
	<?php echo $this->BootstrapForm->end();?>
	</div>
	</div>
</div>
