<?php // echo $this->set('aqui va el tityulo');   ?>
<!--<div style="text-align: center">-->
<div style=" font-size: 28px; font-weight: bold; ">
	SISTEMA IMEXPORT
</div>
<br>
<!--</div>-->
<!--<div class="row-fluid">
	<div class="span9">
		<h3>INICIO SISTEMA IMEXPORT</h3>
	</div>
</div>-->

<div class="alert alert-info">
	Usted inició sesión como <strong><?php echo $this->Session->read('Profile.fullname'); ?></strong>, todos los cambios en el sistema quedaran registrados con esta identidad.
	<a href="#" data-dismiss="alert" class="close">×</a>
</div>
<?php echo $this->Session->flash('flash_change_user_restriction');?>

<?php echo $this->Html->image('logo-imexport.png', array('alt' => 'Imexport', 'width'=>'700px')); ?>