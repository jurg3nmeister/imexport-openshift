<?php
						
//		echo $this->BootstrapForm->input('price', array(
//				'id' => 'txtModalPrice',
//				'label' => '* Monto:',
//				'required' => 'required'
//				,'append' => $currency	
//				//'type'=>'number'
//				//'helpInline' => '<span class="label label-important">' . __('Requerido') . '</span>&nbsp;'
//				)
//			);
	
		echo $this->BootstrapForm->input('date', array(			
			'id' => 'txtModalDate',
			'label' => '* Fecha:',
			'required' => 'required',
			'class'=>'input-date-type'
			,'value'=>$date	
			//'helpInline' => '<span class="label label-important">' . __('Requerido') . '</span>&nbsp;'
			)
		);		

		echo $this->BootstrapForm->input('description', array(
			'id' => 'txtModalDescription',
			'label' => 'Descripción:',
			'type'=>'textarea',
			'class'=>'span12'
			,'value'=>$description	
		));		
		
?>