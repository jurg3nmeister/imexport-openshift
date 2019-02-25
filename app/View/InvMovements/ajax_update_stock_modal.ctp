<?php
		if($transfer == 'warehouses_transfer'){
			$labelStock = 'Saldo Actual Origen (Egresa):';
		}else{
			$labelStock = 'Saldo Actual:';
		}	
		echo $this->BootstrapForm->input('stock', array(				
		'label' => $labelStock,
		'id'=>'txtModalStock',
		'value'=>$stock,
		'style'=>'background-color:#EEEEEE',
		'class'=>'input-small',
		'maxlength'=>'15'
		));
		
		if($transfer == 'warehouses_transfer'){
			echo $this->BootstrapForm->input('stock2', array(				
			'label' => 'Saldo Actual Destino (Ingresa):',
			'id'=>'txtModalStock2',
			'value'=>$stock2,
			'style'=>'background-color:#EEEEEE',
			'class'=>'input-small',
			'maxlength'=>'15'
			));
		}
?>
