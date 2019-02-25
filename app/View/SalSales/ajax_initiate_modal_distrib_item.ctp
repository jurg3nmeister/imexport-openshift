<?php
		echo $this->BootstrapForm->input('items_id', array(				
		'label' => 'Item:',
		'id'=>'cbxModalItemsDistrib',
		'class'=>'span12'
		));
		echo '<br>';
		echo '<br>';
		echo '<div id="boxModalWarehouseStock">';
			//////////////////////////////////////
			echo $this->BootstrapForm->input('inv_warehouse_id', array(				
			'label' => 'Almacén destino:',
			'id'=>'cbxModalWarehousesDestDistrib',
			'class'=>'span6',
			'options' => $invWarehousesDest	
			));
			echo '<br>';
			echo '<br>';
				echo '<div id="boxModalStock">';
				echo $this->BootstrapForm->input('virtual_stock', array(				
				'label' => 'Stock de almacén destino:',
//				'id'=>'txtModalDestinyStockDistrib',
					'id'=>'txtModalVirtualStockDestDistrib',
				'value'=>$virtualStockDest,
				'disabled'=>'disabled',	
				'style'=>'background-color:#EEEEEE',
				'class'=>'span3',
				'maxlength'=>'15'
				,'append' => 'u.'
				));
				echo '</div>';		
			//////////////////////////////////////
		echo '</div>';
		
//		echo $this->BootstrapForm->input('stockVirtual', array(		
//			'label' => 'Stock Virtual de almacén destino:',
//		'id'=>'txtModalDestinyStockVirtual'
//		,'value'=>$stock2
////		,'type'=>'hidden'
//		));
		
		echo $this->BootstrapForm->input('real_stock', array(	
			'label' => 'Stock Real de almacén destino:',
//			'id'=>'txtModalDestinyStockReal',
		'id'=>'txtModalRealStockDestDistrib'
		,'value'=>$realStockDest
		,'type'=>'hidden'
		));
		
		echo $this->BootstrapForm->input('last_backorder_destiny', array(			
//						'id'=>'txtModalDestinyLastBOQuantityDistrib'
						'id'=>'txtModalLastBOQuantityDestDistrib'
			,'value'=>$backorderDest
						,'type'=>'hidden'
						));	
//		debug($backorderDest);
		
		echo $this->BootstrapForm->input('virtual_stock_origin', array(		
			'label' => 'Stock Virtual de almacén origen:',
//		'id'=>'txtModalOriginStockVirtualDistrib'
			'id'=>'txtModalVirtualStockOrigDistrib'
		,'value'=>$virtualStockOrig
		,'type'=>'hidden'
		));
		
		echo $this->BootstrapForm->input('real_stock_origin', array(		
		'label' => 'Stock Real de almacén origen:',
//					'id'=>'txtModalOriginStockRealDistrib'	
		'id'=>'txtModalRealStockOrigDistrib'
			,'value'=>$realStockOrig
//					,'value'=>$stock2
					,'type'=>'hidden'
		));
		
//		echo $this->BootstrapForm->input('last_warehouse_name', array(			
//		'id'=>'txtModalLastWarehouseName'
//		,'value'=>$lastWarehouse
//		,'type'=>'hidden'
//		));
?>