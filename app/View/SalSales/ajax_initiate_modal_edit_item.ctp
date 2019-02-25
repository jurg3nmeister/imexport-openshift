	<?php
		echo $this->BootstrapForm->input('items_id', array(				
		'label' => 'Item:',
		'id'=>'cbxModalItems',
		'class'=>'span12'
		));
		echo '<br>';
		echo '<br>';
		echo '<div id="boxModalWarehousePriceStock">';
			//////////////////////////////////////
			echo $this->BootstrapForm->input('inv_warehouse_id', array(				
			'label' => 'Almacén:',
			'id'=>'cbxModalWarehouses',
			'class'=>'span6',
			'selected' => $warehouseIdForEdit	
			));
			echo '<br>';
			echo '<br>';
			echo '<div id="boxModalPriceStock">';
//			echo '<div id="boxModalPrice">';
				echo $this->BootstrapForm->input('sale_price', array(				
				'label' => 'Precio Unitario:',
				'id'=>'txtModalPrice',
//				'value'=>$price,
				'class'=>'span3',
				'maxlength'=>'15'
				,'append' => 'Bs.'	
				));
//			echo '</div>';	
				echo '<div id="boxModalStocks">';
					echo '<div id="boxModalStock">';
					echo $this->BootstrapForm->input('virtual_stock', array(				
					'label' => 'Stock:',
					'id'=>'txtModalVirtualStock',
					'value'=>$virtualStock,//VAMOS A HABILIOTAR ESTO TEMPORALMENTE
					'disabled'=>'disabled',	
					'style'=>'background-color:#EEEEEE',
					'class'=>'span3',
					'maxlength'=>'15'
					,'append' => 'u.'
					));
					echo '</div>';	

					echo '<div id="boxModalStockTotal">';
					echo $this->BootstrapForm->input('virtal_stock_total', array(				
					'label' => 'Stock Total:',
					'id'=>'txtModalVirtualStockTotal',
					'value'=>$virtualStockTotal,
					'disabled'=>'disabled',
					'style'=>'background-color:#EEEEEE',
					'class'=>'span3',
					'maxlength'=>'15'
					,'append' => 'u.'	
					));
					echo '</div>';
					
//					echo $this->BootstrapForm->input('stockVirtual', array(	
//		'value'=>$stock2,	
//		'id'=>'txtModalStockVirtual'
////		,'type'=>'hidden'
//		));				
					
//					echo $this->BootstrapForm->input('stockReal', array(	
////		'value'=>$stockReal,	
//		'id'=>'txtModalStockReal'
////		,'type'=>'hidden'
//		));					
				if($action == 'save_invoice'){
					echo $this->BootstrapForm->input('real_stock', array(	
					'label' => 'Stock Real:',
					'id'=>'txtModalRealStock',
					'value'=>$realStock,		
					'disabled'=>'disabled',
					'style'=>'background-color:#EEEEEE',
					'class'=>'span3',
					'maxlength'=>'15'
					,'append' => 'u.'	
					));
				}	
					
				echo '</div>';
			echo '</div>';	
			//////////////////////////////////////
		echo '</div>';
		
		echo $this->BootstrapForm->input('last_warehouse', array(			
		'id'=>'txtModalLastWarehouse'
		,'type'=>'hidden'
		));
		
		echo $this->BootstrapForm->input('last_quantity', array(			
		'id'=>'txtModalLastQuantity'
		,'type'=>'hidden'
		));
//		if($approvedQuantity > 0){
			echo $this->BootstrapForm->input('approved_quantity', array(			
			'id'=>'txtModalApprovedQuantity'
				,'value'=>$approvedQuantity
			,'type'=>'hidden'
			));
//		}
//		echo $this->BootstrapForm->input('last_warehouse_name', array(			
//		'id'=>'txtModalLastWarehouseName'
//		,'value'=>$lastWarehouse
//		,'type'=>'hidden'
//		));
?>