<?php
App::uses('AppModel', 'Model');
/**
 * InvMovement Model
 *
 * @property InvMovementType $InvMovementType
 * @property InvMovementDetail $InvMovementDetail
 */
class InvMovement extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'code';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'code' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'date' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'inv_movement_type_id' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'InvMovementType' => array(
			'className' => 'InvMovementType',
			'foreignKey' => 'inv_movement_type_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'InvWarehouse' => array(
			'className' => 'InvWarehouse',
			'foreignKey' => 'inv_warehouse_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		)
	);

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'InvMovementDetail' => array(
			'className' => 'InvMovementDetail',
			'foreignKey' => 'inv_movement_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		)
	);

	
	public function fnLogicDelete($invMovementIds) {
		$dataSource = $this->getDataSource();
		$dataSource->begin();
		//////////////////////////////////////////////////////
		foreach ($invMovementIds as $invMovementId) {
			try {
				$this->save(array('id' => $invMovementId, 'lc_state' => 'LOGIC_DELETED'));
			} catch (Exception $e) {
//				debug($e);
				$dataSource->rollback();
				return false;
			}
		}
		//////////////////////////////////////////////////////
		$dataSource->commit();
		return true;
	}
	

	public function saveMovement($dataMovement, $dataMovementDetail, $OPERATION, $ACTION, $arrayForValidate, $code){
		$dataSource = $this->getDataSource();
		$dataSource->begin();

/**/	if($OPERATION != 'UPDATEHEAD' && $OPERATION != 'DELETEHEAD'){
			///////////////////////////////////////////Start - variables declarations/////////////////////////////////////////		
			$STATE = $dataMovement['InvMovement']['lc_state'];
			$warehouseId = $dataMovement['InvMovement']['inv_warehouse_id'];	
/**/	}
		$validation = array('error'=>0);// first array field is 0 when there isn't any error
		$token = '';//for inser purchase or sale
		$strItemsStock = '';//if approved or cancelled, stocks are updated in block
		///////////////////////////////////////////End - variables declarations/////////////////////////////////////////

/**/	if($OPERATION != 'UPDATEHEAD' && $OPERATION != 'DELETEHEAD'){
			///////////////////////////// Start - Stock validation ///////////////////////////////////////
			if(($STATE == 'APPROVED') AND ($ACTION == 'save_out' OR $ACTION == 'save_sale_out')){
//				print_r($arrayForValidate);
				$validation=$this->_validateItemsStocksOut($arrayForValidate, $warehouseId);
				if($validation == 'ERROR'){
					$dataSource->rollback();
					return 'ERROR';
				}
			}
			if(($STATE == 'CANCELLED') AND ($ACTION == 'save_in' OR $ACTION == 'save_purchase_in')){
				$validation=$this->_validateItemsStocksOut($arrayForValidate, $warehouseId);
				if($validation == 'ERROR'){
					$dataSource->rollback();
					return 'ERROR';
				}
			}
/**/	}	
		if($validation['error'] > 0){
			$dataSource->rollback();
			return array('VALIDATION', $validation['itemsStocks']);
		}
			///////////////////////////// End - Stock validation ///////////////////////////////////////

			////////////////////////////////Start - Checking for purchase or sale if must insert//////////////////////////////////////////
//**********************************************************************************************
/**/		if($OPERATION == 'DELETEHEAD'){
//**********************************************************************************************
/**/			if(!$this->deleteAll(array($dataMovement/*'InvMovement.id'=>array(153,154)*/))){
/**/				$dataSource->rollback();
/**/				return 'error';
/**/			}
//**********************************************************************************************
/**/			$idMovement = 0;
//**********************************************************************************************
/**/		}else{	
//**********************************************************************************************
			if($ACTION == 'save_purchase_in' || $ACTION == 'save_sale_out'){
				if(!isset($dataMovement['InvMovement']['id'])){
					$token = 'INSERT';
				}
			}
			////////////////////////////////End - Checking for purchase or sale if must insert//////////////////////////////////////////
		
			///////////////////////////////////////Start - Save Movement////////////////////////////////////////////
			if(!$this->saveAll($dataMovement)){
				$dataSource->rollback();
				return 'ERROR';
			}else{
				$idMovement = $this->id;
				if($token <> 'INSERT'){
					$dataMovementDetail['InvMovementDetail']['inv_movement_id']=$idMovement;
				}
			}
			///////////////////////////////////////End - Save Movement////////////////////////////////////////////
		
		
			///////////////////////////////////////Start - Save MovementDetail////////////////////////////////////////////
			if($token == 'INSERT'){//Create for purchase or sale
				for($i=0;$i<count($dataMovementDetail['InvMovementDetail']);$i++){
					$dataMovementDetail['InvMovementDetail'][$i]['inv_movement_id'] = $idMovement;
				}
				for($i=0;$i<count($dataMovementDetail['InvMovementDetail']);$i++){
					$this->InvMovementDetail->create();
					if(!$this->InvMovementDetail->save($dataMovementDetail['InvMovementDetail'][$i])){
						$dataSource->rollback();
						return 'ERROR';
					}
				}
			}else{
				switch ($OPERATION) {
					case 'ADD':
						if(!$this->InvMovementDetail->saveAll($dataMovementDetail)){
							$dataSource->rollback();
							return 'ERROR';
						}
						break;
					case 'EDIT':
						$invMovementDetailsIds = $this->InvMovementDetail->find('list', array(
							'conditions'=>array(
								'InvMovementDetail.inv_movement_id'=>$dataMovementDetail['InvMovementDetail']['inv_movement_id'],
								'InvMovementDetail.inv_item_id'=>$dataMovementDetail['InvMovementDetail']['inv_item_id']
							),
							'fields'=>array('InvMovementDetail.id', 'InvMovementDetail.id')
						));
						
						foreach ($invMovementDetailsIds as $invMovementDetailsId) {
							try {
									$this->InvMovementDetail->save(array(
										'id'=>$invMovementDetailsId, 
										'quantity'=>$dataMovementDetail['InvMovementDetail']['quantity'])
									);
							} catch (Exception $e) {
//								debug($e);
								$dataSource->rollback();
								return 'ERROR';
							}
						}
						break;
					case 'DELETE':
						$invMovementDetailsIds = $this->InvMovementDetail->find('list', array(
							'conditions' => array(
								'InvMovementDetail.inv_movement_id' => $dataMovementDetail['InvMovementDetail']['inv_movement_id'],
								'InvMovementDetail.inv_item_id' => $dataMovementDetail['InvMovementDetail']['inv_item_id']
							),
							'fields' => array('InvMovementDetail.id', 'InvMovementDetail.id')
						));
						
						foreach ($invMovementDetailsIds as $invMovementDetailsId) {
							try {
								$this->InvMovementDetail->id = $invMovementDetailsId;	
								$this->InvMovementDetail->delete();
							} catch (Exception $e) {
//								debug($e);
								$dataSource->rollback();
								return 'ERROR';
							}
						}
						break;
				}
			}
			///////////////////////////////////////End - Save MovementDetail////////////////////////////////////////////
		
	/**/	if($OPERATION != 'UPDATEHEAD' && $OPERATION != 'DELETEHEAD'){
				////////////////////////////////////////////////// start- approved or cancelled update stocks //////////////////////////////////////////
				if($STATE == 'APPROVED' OR $STATE == 'CANCELLED'){
					$strItemsStock = $this->_createStringItemsStocksUpdated($arrayForValidate, $warehouseId);//BORRAR ESTA FUNCION YA QUE AHORA NO SE ACTUALIZA LOS ITEMS
				}
				//////////////////////////////////////////////// end - approved or cancelled update stocks ////////////////////////////////////
	/**/	}
		
		//**********************************************************************************************			
		}
		//**********************************************************************************************
		$dataSource->commit();
/**/	if($OPERATION != 'UPDATEHEAD' && $OPERATION != 'DELETEHEAD'){
			return array('SUCCESS', $STATE.'|'.$idMovement.'|'.$code.'|'.$strItemsStock);//BORRAR $strItemsStock
/**/	}else{
/**/		return array('SUCCESS', $idMovement.'|'.$code.'|'.$strItemsStock);
/**/	}	
	}

	
	
	
	public function saveMovementTransfer($dataMovement, $OPERATION, $tokenTransfer, $arrayForValidate, $code, $arrayForValidateOrig){
		$dataSource = $this->getDataSource();
		$dataSource->begin();
//		print_r($dataMovement);
                
		///////////////////////////////////////////Start - variables declarations/////////////////////////////////////////
		$STATE = $dataMovement[0]['InvMovement']['lc_state'];
		$warehouseId = $dataMovement[0]['InvMovement']['inv_warehouse_id'];//destination/in
		$warehouseId2 = $dataMovement[1]['InvMovement']['inv_warehouse_id'];//Source/out
		$validation = array('error'=>0);// first array field is 0 when there isn't any error
		$strItemsStock = '';//if approved or cancelled, stocks are updated in block
		///////////////////////////////////////////End - variables declarations/////////////////////////////////////////
                
		///////////////////////////// Start - Stock validation ///////////////////////////////////////
		if(($STATE == 'APPROVED')){
                    if(isset($dataMovement[3]) && $dataMovement[4]){
//                        print_r($dataMovement[3]['InvMovement']['inv_warehouse_id']); //Souce/out
//                        print_r($warehouseId2);
//                        print_r($dataMovement[4]['InvMovement']['inv_warehouse_id']); //Destiny/in
//                        print_r($warehouseId);
//                        die();
                        if($dataMovement[3]['InvMovement']['inv_warehouse_id'] == $warehouseId2 && $dataMovement[4]['InvMovement']['inv_warehouse_id'] == $warehouseId){
                            //mismo warehouse origen y destino                                          //source            //destiny
                            $validation=$this->_validateItemsStocksOutClone($arrayForValidate, array(0 => $warehouseId2,1 => $warehouseId), $arrayForValidateOrig);
                            if($validation == 'ERROR'){
                                $dataSource->rollback();
                                return 'ERROR';
                            }
                        }else if($dataMovement[3]['InvMovement']['inv_warehouse_id'] != $warehouseId2 && $dataMovement[4]['InvMovement']['inv_warehouse_id'] == $warehouseId){
                            //diferente warehouse origen y mismo destino                                  //destiny
                            $validation1=$this->_validateItemsStocksOutClone($arrayForValidate, array(1 => $warehouseId), $arrayForValidateOrig);
                            if($validation == 'ERROR'){
                                $dataSource->rollback();
                                return 'ERROR';
                            }
                                                                                            //new source
                            $validation2=$this->_validateItemsStocksOut($arrayForValidate, $warehouseId2);
                            if($validation == 'ERROR'){
                                $dataSource->rollback();
                                return 'ERROR';
                            }
                            
                            $validation = array('error' => $validation1['error'] + $validation2['error'], 'itemsStocks' => $validation1['itemsStocks'].$validation2['itemsStocks']);
                        }else if($dataMovement[3]['InvMovement']['inv_warehouse_id'] == $warehouseId2 && $dataMovement[4]['InvMovement']['inv_warehouse_id'] != $warehouseId){
                            //mismo warehouse origen y diferente destino                                //source
                            $validation1=$this->_validateItemsStocksOutClone($arrayForValidate, array(0 => $warehouseId2), $arrayForValidateOrig);
                            if($validation == 'ERROR'){
                                $dataSource->rollback();
                                return 'ERROR';
                            }
                            
                            foreach ($arrayForValidateOrig as $key => $value) {
                                $arrayForValidateX[] = array('inv_item_id' => $key, 'quantity' => $value);
                            }
                                                                        //$arrayForValidateOrig         //old destiny
                            $validation2=$this->_validateItemsStocksOut($arrayForValidateX, $dataMovement[4]['InvMovement']['inv_warehouse_id']);
                            if($validation == 'ERROR'){
                                $dataSource->rollback();
                                return 'ERROR';
                            }
                            
                            $validation = array('error' => $validation1['error'] + $validation2['error'], 'itemsStocks' => $validation1['itemsStocks'].$validation2['itemsStocks']);
                        }else{
                            //diferente warehouse origen y destino                          //new source
                            $validation1=$this->_validateItemsStocksOut($arrayForValidate, $warehouseId2);
                            if($validation == 'ERROR'){
                                $dataSource->rollback();
                                return 'ERROR';
                            }
                            
                            foreach ($arrayForValidateOrig as $key => $value) {
                                $arrayForValidateX[] = array('inv_item_id' => $key, 'quantity' => $value);
                            }
                                                                        //$arrayForValidateOrig         //old destiny
                            $validation2=$this->_validateItemsStocksOut($arrayForValidateX, $dataMovement[4]['InvMovement']['inv_warehouse_id']);
                            if($validation == 'ERROR'){
                                $dataSource->rollback();
                                return 'ERROR';
                            }
                            $validation = array('error' => $validation1['error'] + $validation2['error'], 'itemsStocks' => $validation1['itemsStocks'].$validation2['itemsStocks']);
                        }
                    }else{
                        $validation=$this->_validateItemsStocksOut($arrayForValidate, $warehouseId2);
                        if($validation == 'ERROR'){
                            $dataSource->rollback();
                            return 'ERROR';
                        }
                    }                    
		}
		if($STATE == 'CANCELLED'){
                    $validation=$this->_validateItemsStocksOut($arrayForValidate, $warehouseId);
                    if($validation == 'ERROR'){//if stocks throws error
                            $dataSource->rollback();
                            return 'ERROR';
                    }
		}
		if($validation['error'] > 0){
                    $dataSource->rollback();
                    return array('VALIDATION', $validation['itemsStocks']/*.'|'.$strItemsStock*/.'|'.$STATE);
		}
		
		
		///////////////////////////// End - Stock validation ///////////////////////////////////////
		if($tokenTransfer == 'INSERT'){
			if(!$this->saveAll($dataMovement, array('deep' => true))){
				$dataSource->rollback();
				return 'ERROR';
			}
		}else{
			if($OPERATION <> 'DELETE'){
				//debug($dataMovement);
				if(!$this->save($dataMovement[0])){//graba primera cabecera
					$dataSource->rollback();
					return 'ERROR';
				}
				if(!$this->save($dataMovement[1])){//graba segunda cabecera
					$dataSource->rollback();
					return 'ERROR';
				}
                                if(isset($dataMovement[3]) AND isset($dataMovement[4])){
                                    if(!$this->save($dataMovement[3])){//graba primera cabecera
					$dataSource->rollback();
					return 'ERROR';
                                    }
                                    if(!$this->save($dataMovement[4])){//graba segunda cabecera
                                        $dataSource->rollback();
                                        return 'ERROR';
                                    }
                                }
				if($OPERATION == 'EDIT'){
					///////////////////////////////////////////////////
					$invMovementDetailsIds = $this->InvMovementDetail->find('list', array(
						'conditions' => array(
							'InvMovementDetail.inv_movement_id' => array($dataMovement[0]['InvMovement']['id'], $dataMovement[1]['InvMovement']['id']),
							'InvMovementDetail.inv_item_id' => $dataMovement[2]['InvMovementDetail']['inv_item_id']
						),
						'fields' => array('InvMovementDetail.id', 'InvMovementDetail.id')
					));

					foreach ($invMovementDetailsIds as $invMovementDetailsId) {
						try {
							$this->InvMovementDetail->save(array(
								'id' => $invMovementDetailsId,
								'quantity' => $dataMovement[2]['InvMovementDetail']['quantity'])
							);
						} catch (Exception $e) {
//								debug($e);
							$dataSource->rollback();
							return 'ERROR';
						}
					}
					////////////////////////////////////////////////
					
				}
				if($OPERATION == 'ADD'){
					$this->InvMovementDetail->create();//without this doesn't clean and update (in the beginning just in case)
					if(!$this->InvMovementDetail->save(array('InvMovementDetail'=>array('inv_movement_id'=>$dataMovement[0]['InvMovement']['id'], 'inv_item_id'=>$dataMovement[2]['InvMovementDetail']['inv_item_id'],'quantity'=>$dataMovement[2]['InvMovementDetail']['quantity'])))){
						$dataSource->rollback();
						return 'ERROR';
					}
					$this->InvMovementDetail->create();//without this doesn't clean and update
					if(!$this->InvMovementDetail->save(array('InvMovementDetail'=>array('inv_movement_id'=>$dataMovement[1]['InvMovement']['id'], 'inv_item_id'=>$dataMovement[2]['InvMovementDetail']['inv_item_id'],'quantity'=>$dataMovement[2]['InvMovementDetail']['quantity'])))){
						$dataSource->rollback();
						return 'ERROR';
					}
				}	
			}else{
				///////////////////////////////////////////////////////////
				$invMovementDetailsIds = $this->InvMovementDetail->find('list', array(
					'conditions' => array(
						'InvMovementDetail.inv_movement_id' => array($dataMovement[0]['InvMovement']['id'], $dataMovement[1]['InvMovement']['id']),
						'InvMovementDetail.inv_item_id' => $dataMovement[2]['InvMovementDetail']['inv_item_id']
					),
					'fields' => array('InvMovementDetail.id', 'InvMovementDetail.id')
				));

				foreach ($invMovementDetailsIds as $invMovementDetailsId) {
					try {
						$this->InvMovementDetail->id = $invMovementDetailsId;
						$this->InvMovementDetail->delete();
					} catch (Exception $e) {
//								debug($e);
						$dataSource->rollback();
						return 'ERROR';
					}
				}
				///////////////////////////////////////////////////////////
			}
		}
		
		////////////////////////////////////////////////// start- approved or cancelled update stocks //////////////////////////////////////////
//		if($STATE == 'APPROVED' OR $STATE == 'CANCELLED'){
//			$strItemsStock = $this->_createStringItemsStocksUpdated($arrayForValidate, $warehouseId2);
//			$strItemsStock .= '|'.$this->_createStringItemsStocksUpdated($arrayForValidate, $warehouseId);
//		}
		//////////////////////////////////////////////// end - approved or cancelled update stocks ////////////////////////////////////
		
		
		$dataSource->commit();
		//$dataSource->rollback();
		return array('SUCCESS', $STATE.'|'.$this->id.'|'.$code);//.'|'.$strItemsStock);
	}
	
	
	public function updateMovement($dataMovement){
            $dataSource = $this->getDataSource();
            $dataSource->begin();
            
            if($dataMovement[0] != array()){
                if(!ClassRegistry::init('InvMovement')->saveAll($dataMovement[0])){
                    $dataSource->rollback();
                    return 'ERROR';
                }else{
                    $idMovement = ClassRegistry::init('InvMovement')->id;
                }
            }	
            
            if($dataMovement[1] != array()){
                if(!ClassRegistry::init('InvMovement')->saveAll($dataMovement[1])){
                    $dataSource->rollback();
                    return 'ERROR';
                }else{
                    $idMovement = ClassRegistry::init('InvMovement')->id;
                }
            }	
            
            if($dataMovement[2] != array()){
                if(!ClassRegistry::init('InvMovement')->saveAll($dataMovement[2])){
                    $dataSource->rollback();
                    return 'ERROR';
                }else{
                    $idMovement = ClassRegistry::init('InvMovement')->id;
                }
            }
            
            if($dataMovement[3] != array()){
                if(!ClassRegistry::init('InvMovement')->saveAll($dataMovement[3])){
                    $dataSource->rollback();
                    return 'ERROR';
                }else{
                    $idMovement = ClassRegistry::init('InvMovement')->id;
                }
            }            
            
            $dataSource->commit();

            if ($dataMovement != array()) {
                return array('SUCCESS', $idMovement);
            }
	}
	
	
//	public function reduceCredits($id, $amount) { 
//                if($this->updateAll( 
//                                array( 
//                                        'Manager.credit' => "Manager.credit-{$amount}" ,
//										'lc_transaction'=>"'MODIFY'"  // doubt, Manager?
//                                         ), 
//                                array( 
//                                        'Manager.id' => $id, 
//                                        'Manager.credit >= ' => $amount 
//                                        ) 
//                                ) 
//                        )  { 
//                        return $this->getAffectedRows(); 
//                } 
//                return false; 
//	} 
	
	///////////////////////////////////////////////////////////////////////////////////////////////////////////////
	public function _validateItemsStocksOut($arrayItemsDetails, $warehouse){
		$strItemsStockErrorSuccess = '';
		/////////////////for new stock method 
		$items = array();
		foreach ($arrayItemsDetails as $value) {//get a clean items arrays
			$items[$value['inv_item_id']] = $value['inv_item_id'];
		}
		$stocks = $this->_get_stocks($items, $warehouse);//get all the stocks
		
		if($stocks == 'ERROR'){
			return 'ERROR';
		}
		
		///////////////////
		$cont=0;
		for($i = 0; $i<count($arrayItemsDetails); $i++){
				$updatedStock = $this->_find_item_stock($stocks, $arrayItemsDetails[$i]['inv_item_id']);
				if($updatedStock < $arrayItemsDetails[$i]['quantity']){
					$strItemsStockErrorSuccess .= $arrayItemsDetails[$i]['inv_item_id'].'=>error:'.$updatedStock.','; //error
					$cont++;
				}else{
					$strItemsStockErrorSuccess .= $arrayItemsDetails[$i]['inv_item_id'].'=>success:'.$updatedStock.',';//success
				}
		}
		return array('error'=>$cont, 'itemsStocks'=>$strItemsStockErrorSuccess);
	}
	
	public function _validateItemsStocksOutClone($arrayItemsDetails, $warehouse, $arrayItemsDetailsOrig ){
//            print_r($arrayItemsDetails);//array de productos y cantidades nuevas
//            print_r($arrayItemsDetailsOrig);//array de productos y cantidades originales/antiguas
//            print_r($warehouse);
//            die();
		$strItemsStockErrorSuccess = '';
		/////////////////for new stock method 
		$items = array();
		foreach ($arrayItemsDetails as $value) {//get a clean items arrays
			$items[$value['inv_item_id']] = $value['inv_item_id'];
		}
                
                if(is_array($warehouse)){   
                    if(isset($warehouse[0])){
                        $stocks2 = $this->_get_stocks($items, $warehouse[0]);//get all the stocks from Source warehouse
                        if($stocks2 == 'ERROR'){
                            return 'ERROR';
                        }
                    }
                    if(isset($warehouse[1])){
                        $stocks = $this->_get_stocks($items, $warehouse[1]);//get all the stocks from Destiny warehouse
                        if($stocks == 'ERROR'){
                            return 'ERROR';
                        }
                    }
                }else{
                    $stocks = $this->_get_stocks($items, $warehouse);//get all the stocks
                    if($stocks == 'ERROR'){
                        return 'ERROR';
                    }
                }
//		die();
		
                
		///////////////////
		$cont=0;
                for($i = 0; $i<count($arrayItemsDetails); $i++){
                    if(isset($warehouse[1]) || !is_array($warehouse)){
                        /////////////////////////////////////////////////////////
                        $new=0;
                        $updatedStock = $this->_find_item_stock($stocks, $arrayItemsDetails[$i]['inv_item_id']);
                        foreach ($arrayItemsDetailsOrig as $key => $val){
                            if($arrayItemsDetails[$i]['inv_item_id'] == $key){
                                $subtraction = $val - $arrayItemsDetails[$i]['quantity'];
                                $new++;
//                              print_r('['.$updatedStock.' < ('.$val.' - '.$arrayItemsDetails[$i]['quantity'].')]    ');
                                break;
                            }
                        }
                        if($new != 0){
                            if($updatedStock < $subtraction){
                                    $strItemsStockErrorSuccess .= $arrayItemsDetails[$i]['inv_item_id'].'=>errorD:'.$updatedStock.','; //error destiny
                                    $cont++;
                            }else{
                                    $strItemsStockErrorSuccess .= $arrayItemsDetails[$i]['inv_item_id'].'=>success:'.$updatedStock.',';//success
                            }
                        }
                        /////////////////////////////////////////////////////////
                    }
                    if(isset($warehouse[0]) && is_array($warehouse)){
                        /////////////////////////////////////////////////////////
                        $new2=0;
                        $updatedStock2 = $this->_find_item_stock($stocks2, $arrayItemsDetails[$i]['inv_item_id']);
                        foreach ($arrayItemsDetailsOrig as $key => $val){
                            if($arrayItemsDetails[$i]['inv_item_id'] == $key){
                                $subtraction = $arrayItemsDetails[$i]['quantity'] - $val;
                                $new2++;
//                              print_r('[('.$arrayItemsDetails[$i]['quantity'].' - '.$val.') >'.$updatedStock2.']     ');
                                break;
                            }
                        }
                        if($new2 != 0){
                            if($subtraction > $updatedStock2){
                                    $strItemsStockErrorSuccess .= $arrayItemsDetails[$i]['inv_item_id'].'=>errorS:'.$updatedStock2.','; //error source
                                    $cont++;
                            }else{
                                    $strItemsStockErrorSuccess .= $arrayItemsDetails[$i]['inv_item_id'].'=>success:'.$updatedStock2.',';//success
                            }
                        }
                        /////////////////////////////////////////////////////////
                    }
                }
		return array('error'=>$cont, 'itemsStocks'=>$strItemsStockErrorSuccess);
	}
	
	private function _get_stocks($items, $warehouse, $limitDate = '', $dateOperator = '<='){
		$this->InvMovementDetail->unbindModel(array('belongsTo' => array('InvItem')));
		$this->InvMovementDetail->bindModel(array(
			'hasOne'=>array(
				'InvMovementType'=>array(
					'foreignKey'=>false,
					'conditions'=> array('InvMovement.inv_movement_type_id = InvMovementType.id')
				)
				
			)
		));
		$dateRanges = array();
		if($limitDate <> ''){
			$dateRanges = array('InvMovement.date '.$dateOperator => $limitDate);
		}
		
		try{
			$movements = $this->InvMovementDetail->find('all', array(
			'fields'=>array(
				"InvMovementDetail.inv_item_id", 
				"(SUM(CASE WHEN \"InvMovementType\".\"status\" = 'entrada' AND \"InvMovement\".\"lc_state\" = 'APPROVED' THEN \"InvMovementDetail\".\"quantity\" ELSE 0 END))-
				(SUM(CASE WHEN \"InvMovementType\".\"status\" = 'salida' AND \"InvMovement\".\"lc_state\" = 'APPROVED' THEN \"InvMovementDetail\".\"quantity\" ELSE 0 END)) AS stock"
				),
			'conditions'=>array(
				'InvMovement.inv_warehouse_id'=>$warehouse,
				'InvMovementDetail.inv_item_id'=>$items,
				$dateRanges
				),
			'group'=>array('InvMovementDetail.inv_item_id'),
			'order'=>array('InvMovementDetail.inv_item_id')
			));
			return $movements;
		}catch (Exception $e){
			return 'ERROR';
		}
		
		
		//the array format is like this:
		/*
		array(
			(int) 0 => array(
				'InvMovementDetail' => array(
					'inv_item_id' => (int) 9
				),
				(int) 0 => array(
					'stock' => '20'
				)
			),...etc,etc
		)	*/
		
	}
	
	private function _find_item_stock($stocks, $item){
		foreach($stocks as $stock){//find required stock inside stocks array 
			if($item == $stock['InvMovementDetail']['inv_item_id']){
				return $stock[0]['stock'];
			}
		}
		//this fixes in case there isn't any item inside movement_details yet with a determinated warehouse
		return 0;
	}
	
	private function _createStringItemsStocksUpdated($arrayItemsDetails, $idWarehouse){
		////////////////////////////////////////////INICIO-CREAR CADENA ITEMS STOCK ACUTALIZADOS//////////////////////////////
			$strItemsStock = '';
			/////////////////for new stock method 
			$items = array();
			foreach ($arrayItemsDetails as $value) {//get a clean items arrays
				$items[$value['inv_item_id']] = $value['inv_item_id'];
			}
			$stocks = $this->_get_stocks($items, $idWarehouse);//get all the stocks
			///////////////////
			for($i = 0; $i<count($arrayItemsDetails); $i++){
				//$updatedStock = $this->_find_stock($arrayItemsDetails[$i]['inv_item_id'], $idWarehouse);
				$updatedStock = $this->_find_item_stock($stocks, $arrayItemsDetails[$i]['inv_item_id']);
				$strItemsStock .= $arrayItemsDetails[$i]['inv_item_id'].'=>'.$updatedStock.',';
			}
			////////////////////////////////////////////FIN-CREAR CADENA ITEMS STOCK ACUTALIZADOS/////////////////////////////////
			return $strItemsStock;
	}
	
//END MODEL
}
