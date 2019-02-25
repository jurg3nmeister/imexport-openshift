<?php echo $this->Html->script('jquery.flot.min', FALSE); ?>
<?php echo $this->Html->script('jquery.flot.pie.min', FALSE); ?>
<?php echo $this->Html->script('jquery.flot.resize.min', FALSE); ?>
<?php echo $this->Html->script('unicorn', FALSE); ?>
<?php echo $this->Html->script('jquery.dataTables.min.js', FALSE); ?>
<?php echo $this->Html->script('jquery.uniform.js', FALSE); ?>
<?php echo $this->Html->script('modules/InvGraphics', FALSE); ?>


<!-- ************************************************************************************************************************ -->
<div class="span12"><!-- START CONTAINER FLUID/ROW FLUID/SPAN12 - FROM MAIN TEMPLATE #UNICORN -->
<!-- ************************************************************************************************************************ -->
	<!-- //////////////////////////// Start - buttons /////////////////////////////////-->
	<div class="widget-box">
		<div class="widget-content nopadding">
			<a href="#" id="btnGenerateReportHistoricalCosts" class="btn btn-primary noPrint "><i class="icon-cog icon-white"></i> Generar Reporte</a>
			<div id="boxMessage"></div>
			<div id="boxProcessing" align="center"></div>
		</div>
	</div>
	<!-- //////////////////////////// End - buttons /////////////////////////////////-->
	
	<!-- //////////////////////////// Start - filters /////////////////////////////////-->
	<div class="widget-box">
		<div class="widget-title">
			<span class="icon">
				<i class=" icon-search"></i>
			</span>
			<h5>Histórico de Costos</h5>
		</div>
		<div class="widget-content nopadding">
			<?php echo $this->BootstrapForm->create('InvMovement', array('class' => 'form-horizontal', 'novalidate' => true));?>
			<?php
			echo $this->BootstrapForm->input('start_date', array(
				'label' => '* Fecha Inicio:',
				'id'=>'txtReportStartDate',
				'class'=>'input-date-type' 
			));

			echo $this->BootstrapForm->input('finish_date', array(
				'label' => '* Fecha Fin:',
				'id'=>'txtReportFinishDate',
				'class'=>'input-date-type' 
			));
				  
				  
//			echo $this->BootstrapForm->input('brand', array(
//				'label' => '* Marca:',
//				'id'=>'cbxBrands',
//				'type'=>'select',
//				'options'=>$brands,
//				'class'=>'span4'  
//			));
//			
//			echo $this->BootstrapForm->input('price_type', array(
//				'label' => 'Tipo de precio:',
//				'id'=>'cbxPriceType',
//				'type'=>'select',
//				'options'=>array("1"=>"FOB", "8"=>"CIF", "9"=>"VENTA"),
//				'class'=>'span3'  
//			));
				  
			echo $this->BootstrapForm->input('currency', array(
				'label' => '* Moneda:',
				'id'=>'cbxReportCurrency',
				'type'=>'select',
				'options'=>array('BOLIVIANOS'=>'BOLIVIANOS', 'DOLARES'=>'DOLARES'),
				'class'=>'span4'  
			));
			
			echo "<br>";
			
			?>
			
			<?php echo $this->BootstrapForm->end();?>
			
		</div>
	</div>
	<!-- //////////////////////////// End - filters /////////////////////////////////-->
	

<!-- ************************************************************************************************************************ -->
</div><!-- END CONTAINER FLUID/ROW FLUID/SPAN12 - FROM MAIN TEMPLATE #UNICORN
<!-- ************************************************************************************************************************ -->