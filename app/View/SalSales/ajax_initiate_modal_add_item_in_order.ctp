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
			'class'=>'span6'
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

				echo '<div id="boxModalStock">';
				echo $this->BootstrapForm->input('stock', array(				
				'label' => 'Stock:',
				'id'=>'txtModalVirtualStock',
//				'value'=>$stock,
				'disabled'=>'disabled',	
				'style'=>'background-color:#EEEEEE',
				'class'=>'span3',
				'maxlength'=>'15'
				,'append' => 'u.'
				));
				echo '</div>';
				
				echo '<div id="boxModalStockTotal">';
				echo $this->BootstrapForm->input('stockTotal', array(				
				'label' => 'Stock Total:',
				'id'=>'txtModalVirtualStockTotal',
//				'value'=>$stockTotal,
				'disabled'=>'disabled',
				'style'=>'background-color:#EEEEEE',
				'class'=>'span3',
				'maxlength'=>'15'
				,'append' => 'u.'	
				));
				echo '</div>';
					
				if($action == 'save_invoice'){
					echo $this->BootstrapForm->input('real_stock', array(	
					'label' => 'Stock Real:',
					'id'=>'txtModalRealStock',
//					'value'=>$realStock		
					'disabled'=>'disabled',
					'style'=>'background-color:#EEEEEE',
					'class'=>'span3',
					'maxlength'=>'15'
					,'append' => 'u.'	
					));
				}	
				
			echo '</div>';	
			//////////////////////////////////////
		echo '</div>';
?>