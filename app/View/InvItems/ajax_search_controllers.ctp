<?php
	echo $this->BootstrapForm->input('stock_war', array(											
											'id'=>'cbxStockWarehouse',
											'value'=>$valueWarehouse,
											'options'=>$warehouses,
	//							'empty' => '(choose one)',
											'type'=>'select',
											'label'=>'Almacen:&nbsp;',
	//										'class'=>'span3'
											));
?>