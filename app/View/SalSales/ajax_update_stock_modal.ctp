<?php
//		echo $this->BootstrapForm->input('stock', array(				
//			'label' => 'Stock:',
//			'id'=>'txtModalStock',
//			'value'=>$stock,
//			'disabled'=>'disabled',
//			'style'=>'background-color:#EEEEEE',
//			'class'=>'span3',
//			'maxlength'=>'15'
//			,'append' => 'u.'
//			));
			echo '<div id="boxModalStock">';
			echo $this->BootstrapForm->input('virtual_stock', array(				
			'label' => 'Stock:',
			'id'=>'txtModalVirtualStock',
			'value'=>$virtualStock,
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
			
//			echo $this->BootstrapForm->input('stockVirtual', array(	
//		'value'=>$stock2,	
//		'id'=>'txtModalStockVirtual'
////		,'type'=>'hidden'
//		));			
			
			if($action == 'save_invoice'){
				echo $this->BootstrapForm->input('real_stock', array(	
				'label' => 'Stock Real:',
				'id'=>'txtModalRealStock',
				'value'=>$realStock		
				,'disabled'=>'disabled',
				'style'=>'background-color:#EEEEEE',
				'class'=>'span3',
				'maxlength'=>'15'
				,'append' => 'u.'	
				));
			}	
?>
