<?php
App::uses('AppModel', 'Model');
/**
 * SalSale Model
 *
 * @property SalEmployee $SalEmployee
 * @property SalTaxNumber $SalTaxNumber
 * @property SalPayment $SalPayment
 * @property SalDetail $SalDetail
 */
class SalSale extends AppModel {

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'sal_employee_id' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'sal_tax_number_id' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
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
//		'doc_code' => array(
//			'notempty' => array(
//				'rule' => array('notempty'),
//				//'message' => 'Your custom message here',
//				//'allowEmpty' => false,
//				//'required' => false,
//				//'last' => false, // Stop validation after this rule
//				//'on' => 'create', // Limit validation to 'create' or 'update' operations
//			),
//		),
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
	);

	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * belongsTo associations
 *
 * @var array
 */
	public $belongsTo = array(
		'SalEmployee' => array(
			'className' => 'SalEmployee',
			'foreignKey' => 'sal_employee_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'SalTaxNumber' => array(
			'className' => 'SalTaxNumber',
			'foreignKey' => 'sal_tax_number_id',
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
		'SalPayment' => array(
			'className' => 'SalPayment',
			'foreignKey' => 'sal_sale_id',
			'dependent' => false,
			'conditions' => '',
			'fields' => '',
			'order' => '',
			'limit' => '',
			'offset' => '',
			'exclusive' => '',
			'finderQuery' => '',
			'counterQuery' => ''
		),
		'SalDetail' => array(
			'className' => 'SalDetail',
			'foreignKey' => 'sal_sale_id',
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
//		,'SalInvoice' => array(
//			'className' => 'SalInvoice',
//			'foreignKey' => 'sal_sale_id',
//			'dependent' => false,
//			'conditions' => '',
//			'fields' => '',
//			'order' => '',
//			'limit' => '',
//			'offset' => '',
//			'exclusive' => '',
//			'finderQuery' => '',
//			'counterQuery' => ''
//		)
	);
	
	public function saveSale($dataSale, $dataSaleDetail, $OPERATION, $ACTION, $STATE, $dataPayDetail, $arraySalePrices, $dataInvoiceDetail, $dataMovement/*, $dataSaleDetailsBatch*/, $saleNoteDocCode, $saleCode, $notDeliveredSum, $backorderSum, $dataSaleDetail1, $arrayForValidate){
		$dataSource = $this->getDataSource();
		$dataSource->begin();
		
//		print_r($dataSale);
//		print_r($dataMovement);
//		die();
		///////////////////////////// Start - Stock validation ///////////////////////////////////////
		$validation = array('error'=>0);// first array field is 0 when there isn't any error

		if($STATE == 'SINVOICE_APPROVED'){
			$validation = $this->_validateItemsStocksOut($arrayForValidate);
			if($validation == 'ERROR'){
				$dataSource->rollback();
				return 'ERROR';
			}
		}
		
		if($validation['error'] > 0){
			$dataSource->rollback();
			return array('VALIDATION', $validation['itemsStocks']);
		}
		///////////////////////////// End - Stock validation ///////////////////////////////////////
//		print_r($dataSaleDetail);
//		die();
		$strItemsApproved = '';
		$strItemsApprovedQuantity = '';
		$strItemsCompletelyApproved = '';
		$strItemsHalfApproved = '';
		$strItemsCompletelyBackordered = '';
		///////////////////////////////////////Start - Save Sale////////////////////////////////////////////
		/*Saving Note*/
		if(!$this->saveAll($dataSale[0])){
			$dataSource->rollback();
			return 'ERROR';
		}else /*if($dataSaleDetail != null)*/{
			$idSale1 = $this->id;
			$dataSaleDetail[0]['SalDetail']['sal_sale_id']=$idSale1;
			if($dataPayDetail != null){
				$dataPayDetail['SalPayment']['sal_sale_id']=$idSale1;
			}
			if($dataInvoiceDetail[0] != 'delete' AND $dataInvoiceDetail[0] != 'empty' ){
				if($dataInvoiceDetail[0] == 'new'){
//					$dataInvoiceDetail['SalInvoice']['sal_sale_id']=$idSale1;
					if(!ClassRegistry::init('SalInvoice')->saveAll($dataInvoiceDetail)){
						$dataSource->rollback();
						return 'error';
					}
				}else{
					$salInvoicesIds = ClassRegistry::init('SalInvoice')->find('list', array(
							'conditions'=>array(
								'SalInvoice.sal_code'=>$dataInvoiceDetail['SalInvoice']['sal_code']
							),
							'fields'=>array('SalInvoice.id', 'SalInvoice.id')
						));
					if($salInvoicesIds == array()){
						if(!ClassRegistry::init('SalInvoice')->saveAll($dataInvoiceDetail)){
							$dataSource->rollback();
							return 'error';
						}
					}else{	
						foreach ($salInvoicesIds as $salInvoicesId) {
							try {
									ClassRegistry::init('SalInvoice')->save(array(
										'id'=>$salInvoicesId, 
										'invoice_number'=>$dataInvoiceDetail['SalInvoice']['invoice_number'],
										'description'=>$dataInvoiceDetail['SalInvoice']['description'])									
									);
							} catch (Exception $e) {
	//								debug($e);
								$dataSource->rollback();
								return 'ERROR';
							}
						}
					}
				}		
			}
		}
		
	if($dataInvoiceDetail[0] == 'delete'){
				$salInvoicesIds = ClassRegistry::init('SalInvoice')->find('list', array(
						'conditions' => array(
							'SalInvoice.sal_code'=>$dataInvoiceDetail['SalInvoice']['sal_code']
						),
						'fields' => array('SalInvoice.id', 'SalInvoice.id')
					));
				if($salInvoicesIds != array()){
					foreach ($salInvoicesIds as $salInvoicesId) {
						try {
							ClassRegistry::init('SalInvoice')->id = $salInvoicesId;	
							ClassRegistry::init('SalInvoice')->delete();
						} catch (Exception $e) {
//								debug($e);
							$dataSource->rollback();
							return 'ERROR';
						}
					}
				}	
			}
//			debug(isset($dataSale[0]['SalSale']['id']));
//			debug(isset($dataSale[1]['SalSale']['id']));
//			die();
		if (($ACTION == 'save_order') || ($ACTION == 'save_invoice' && isset($dataSale[0]['SalSale']['id']) == true && isset($dataSale[1]['SalSale']['id']) == true)){
//		if($ACTION=='save_order'){
//			print_r('si tiene order');
			if(!$this->saveAll($dataSale[1])){
				$dataSource->rollback();
				return 'ERROR';
			}else/* if($dataSaleDetail != null)*/{
				$idSale2 = $this->id;
				$dataSaleDetail[1]['SalDetail']['sal_sale_id']=$idSale2;
//				if($dataInvoiceDetail != 'delete'){
//					if($dataInvoiceDetail[0] == 'new'){
//	//					$dataInvoiceDetail['SalInvoice']['sal_sale_id']=$idSale1;
//						if(!ClassRegistry::init('SalInvoice')->saveAll($dataInvoiceDetail)){
//								$dataSource->rollback();
//								return 'error';
//							}
//					}else{
//						$salInvoicesIds = ClassRegistry::init('SalInvoice')->find('list', array(
//								'conditions'=>array(
//									'SalInvoice.sal_code'=>$dataInvoiceDetail[1]['SalInvoice']['sal_code']
//								),
//								'fields'=>array('SalInvoice.id', 'SalInvoice.id')
//							));
//
//						foreach ($salInvoicesIds as $salInvoicesId) {
//							try {
//									ClassRegistry::init('SalInvoice')->save(array(
//										'id'=>$salInvoicesId, 
//										'invoice_number'=>$dataInvoiceDetail[1]['SalInvoice']['invoice_number'],
//										'description'=>$dataInvoiceDetail[1]['SalInvoice']['description'])									
//									);
//							} catch (Exception $e) {
//	//								debug($e);
//								$dataSource->rollback();
//								return 'ERROR';
//							}
//						}
//					}		
//				}
			}
			
//			if($dataInvoiceDetail == 'delete'){
//				$salInvoicesIds = ClassRegistry::init('SalInvoice')->find('list', array(
//						'conditions' => array(
//							'SalInvoice.sal_sale_id'=>$idSale2
//						),
//						'fields' => array('SalInvoice.id', 'SalInvoice.id')
//					));
//
//					foreach ($salInvoicesIds as $salInvoicesId) {
//						try {
//							ClassRegistry::init('SalInvoice')->id = $salInvoicesId;	
//							ClassRegistry::init('SalInvoice')->delete();
//						} catch (Exception $e) {
////								debug($e);
//							$dataSource->rollback();
//							return 'ERROR';
//						}
//					}	
//			}
		
		}	
		
//		print_r($dataSaleDetail);
//		die();
		
			switch ($OPERATION) {
				case 'ADD':
					if(!$this->SalDetail->saveAll($dataSaleDetail[0])){
						$dataSource->rollback();
						return 'error';
					}
					if (($ACTION == 'save_order') || ($ACTION == 'save_invoice' && isset($dataSale[0]['SalSale']['id']) == true && isset($dataSale[1]['SalSale']['id']) == true)){
//					if($ACTION=='save_order'){
						if(!$this->SalDetail->saveAll($dataSaleDetail[1])){
							$dataSource->rollback();
							return 'ERROR';
						}
					}	
					break;
				case 'ADD_PAY':	
					if($dataPayDetail != null){
						if(!$this->SalPayment->saveAll($dataPayDetail)){
							$dataSource->rollback();
							return 'error';
						}
					}
					break;
				case 'EDIT':							//array fields
//					if($this->SalDetail->updateAll(array(/*'SalDetail.lc_transaction'=>"'MODIFY'",*/ 'SalDetail.sale_price'=>$dataMovementDetail[0]['SalDetail']['sale_price'], 
//															'SalDetail.quantity'=>$dataMovementDetail[0]['SalDetail']['quantity'], 
//															'SalDetail.ex_sale_price'=>$dataMovementDetail[0]['SalDetail']['ex_sale_price']
//															/*'SalDetail.fob_price'=>$dataMovementDetail['SalDetail']['fob_price'],
//															'SalDetail.ex_fob_price'=>$dataMovementDetail['SalDetail']['ex_fob_price'],
//															'SalDetail.cif_price'=>$dataMovementDetail['SalDetail']['cif_price'],
//															'SalDetail.ex_cif_price'=>$dataMovementDetail['SalDetail']['ex_cif_price']*/), 
//								/*array conditions*/array('SalDetail.sal_sale_id'=>$dataMovementDetail[0]['SalDetail']['sal_sale_id'], 
//														'SalDetail.inv_warehouse_id'=>$dataMovementDetail[0]['SalDetail']['inv_warehouse_id'], 
//														'SalDetail.inv_item_id'=>$dataMovementDetail[0]['SalDetail']['inv_item_id']
//													))){
//						$rowsAffected = $this->getAffectedRows();//must do this because updateAll always return true
//					}
//					if($rowsAffected == 0){
//						$dataSource->rollback();
//						return 'error';
//					}
					//--------------------------------------------------------------------------------------------------------------
					$salDetailsIds = $this->SalDetail->find('list', array(
							'conditions'=>array(
								'SalDetail.sal_sale_id'=>$dataSaleDetail[0]['SalDetail']['sal_sale_id'], 
								'SalDetail.inv_warehouse_id'=>$dataSaleDetail[0]['SalDetail']['last_warehouse'], 
								'SalDetail.inv_item_id'=>$dataSaleDetail[0]['SalDetail']['inv_item_id']
							),
							'fields'=>array('SalDetail.id', 'SalDetail.id')
						));
					
					foreach ($salDetailsIds as $salDetailsId) {
						try {
								$this->SalDetail->save(array(
									'id'=>$salDetailsId, 
									'inv_warehouse_id'=>$dataSaleDetail[0]['SalDetail']['inv_warehouse_id'],
									'sale_price'=>$dataSaleDetail[0]['SalDetail']['sale_price'], 
									'quantity'=>$dataSaleDetail[0]['SalDetail']['quantity'], 
									'backorder'=>$dataSaleDetail[0]['SalDetail']['backorder'], 
									'ex_sale_price'=>$dataSaleDetail[0]['SalDetail']['ex_sale_price'])
								);
						} catch (Exception $e) {
//								debug($e);
							$dataSource->rollback();
							return 'ERROR';
						}
					}
					//--------------------------------------------------------------------------------------------------------------
					if (($ACTION == 'save_order') || ($ACTION == 'save_invoice' && isset($dataSale[0]['SalSale']['id']) == true && isset($dataSale[1]['SalSale']['id']) == true)){
//					if($ACTION=='save_order'){
//						if($this->SalDetail->updateAll(array(/*'SalDetail.lc_transaction'=>"'MODIFY'",*/'SalDetail.sale_price'=>$dataMovementDetail[1]['SalDetail']['sale_price'], 
//															'SalDetail.quantity'=>$dataMovementDetail[1]['SalDetail']['quantity'], 
//															'SalDetail.ex_sale_price'=>$dataMovementDetail[1]['SalDetail']['ex_sale_price']				
//															/*'SalDetail.fob_price'=>$dataMovementDetail['SalDetail']['fob_price'],
//															'SalDetail.ex_fob_price'=>$dataMovementDetail['SalDetail']['ex_fob_price'],
//															'SalDetail.cif_price'=>$dataMovementDetail['SalDetail']['cif_price'],
//															'SalDetail.ex_cif_price'=>$dataMovementDetail['SalDetail']['ex_cif_price']*/), 
//								/*array conditions*/array('SalDetail.sal_sale_id'=>$dataMovementDetail[1]['SalDetail']['sal_sale_id'], 
//														'SalDetail.inv_warehouse_id'=>$dataMovementDetail[1]['SalDetail']['inv_warehouse_id'], 
//														'SalDetail.inv_item_id'=>$dataMovementDetail[1]['SalDetail']['inv_item_id']
//													))){
//							$rowsAffected = $this->getAffectedRows();//must do this because updateAll always return true
//						}
//						if($rowsAffected == 0){
//							$dataSource->rollback();
//							return 'error';
//						}
						//--------------------------------------------------------------------------------------------------------------
						$salDetailsIds = $this->SalDetail->find('list', array(
							'conditions'=>array(
								'SalDetail.sal_sale_id'=>$dataSaleDetail[1]['SalDetail']['sal_sale_id'], 
								'SalDetail.inv_warehouse_id'=>$dataSaleDetail[1]['SalDetail']['last_warehouse'], 
								'SalDetail.inv_item_id'=>$dataSaleDetail[1]['SalDetail']['inv_item_id']
							),
							'fields'=>array('SalDetail.id', 'SalDetail.id')
						));

						foreach ($salDetailsIds as $salDetailsId) {
							try {
									$this->SalDetail->save(array(
										'id'=>$salDetailsId, 
										'inv_warehouse_id'=>$dataSaleDetail[1]['SalDetail']['inv_warehouse_id'],
										'sale_price'=>$dataSaleDetail[1]['SalDetail']['sale_price'], 
										'quantity'=>$dataSaleDetail[1]['SalDetail']['quantity'], 
										'backorder'=>$dataSaleDetail[1]['SalDetail']['backorder'], 
										'ex_sale_price'=>$dataSaleDetail[1]['SalDetail']['ex_sale_price'])
									);
							} catch (Exception $e) {
	//								debug($e);
								$dataSource->rollback();
								return 'ERROR';
							}
						}
						//--------------------------------------------------------------------------------------------------------------
					}					
					
					break;
				case 'EDIT_PAY':
//					if($this->SalPayment->updateAll(array(/*'SalPayment.lc_transaction'=>"'MODIFY'",*/ 'SalPayment.amount'=>$dataPayDetail['SalPayment']['amount'], 
//															'SalPayment.description'=>"'".$dataPayDetail['SalPayment']['description']."'", 
//															'SalPayment.ex_amount'=>$dataPayDetail['SalPayment']['ex_amount']),
//								/*array conditions*/array('SalPayment.sal_sale_id'=>$dataPayDetail['SalPayment']['sal_sale_id'], 
//														'SalPayment.sal_payment_type_id'=>$dataPayDetail['SalPayment']['sal_payment_type_id'],
//														'SalPayment.date'=>$dataPayDetail['SalPayment']['date']))){
//						$rowsAffected = $this->getAffectedRows();//must do this because updateAll always return true
//					}
//					if($rowsAffected == 0){
//						$dataSource->rollback();
//						return 'error';
//					}
					//--------------------------------------------------------------------------------------------------------------
					$salPaymentsIds = $this->SalPayment->find('list', array(
						'conditions'=>array(
							'SalPayment.sal_sale_id'=>$dataPayDetail['SalPayment']['sal_sale_id'], 
							'SalPayment.sal_payment_type_id'=>$dataPayDetail['SalPayment']['sal_payment_type_id'],
							'SalPayment.date'=>$dataPayDetail['SalPayment']['date']
						),
						'fields'=>array('SalPayment.id', 'SalPayment.id')
					));
					
					foreach ($salPaymentsIds as $salPaymentsId) {
						try {
								$this->SalPayment->save(array(
									'id'=>$salPaymentsId, 
									'amount'=>$dataPayDetail['SalPayment']['amount'], 
									'description'=>$dataPayDetail['SalPayment']['description'], 
									'ex_amount'=>$dataPayDetail['SalPayment']['ex_amount'])
								);
						} catch (Exception $e) {
//								debug($e);
							$dataSource->rollback();
							return 'ERROR';
						}
					}
					//--------------------------------------------------------------------------------------------------------------
					break;
				case 'DELETE':
//					if(!$this->SalDetail->deleteAll(array('SalDetail.sal_sale_id'=>$dataMovementDetail[0]['SalDetail']['sal_sale_id'],	
//															'SalDetail.inv_warehouse_id'=>$dataMovementDetail[0]['SalDetail']['inv_warehouse_id'], 
//															'SalDetail.inv_item_id'=>$dataMovementDetail[0]['SalDetail']['inv_item_id']))){
//						$dataSource->rollback();
//						return 'error';
//					}
					//--------------------------------------------------------------------------------------------------------------
					$salDetailsIds = $this->SalDetail->find('list', array(
						'conditions' => array(
							'SalDetail.sal_sale_id'=>$dataSaleDetail[0]['SalDetail']['sal_sale_id'],	
							'SalDetail.inv_warehouse_id'=>$dataSaleDetail[0]['SalDetail']['inv_warehouse_id'], 
							'SalDetail.inv_item_id'=>$dataSaleDetail[0]['SalDetail']['inv_item_id']
						),
						'fields' => array('SalDetail.id', 'SalDetail.id')
					));

					foreach ($salDetailsIds as $salDetailsId) {
						try {
							$this->SalDetail->id = $salDetailsId;	
							$this->SalDetail->delete();
						} catch (Exception $e) {
//								debug($e);
							$dataSource->rollback();
							return 'ERROR';
						}
					}	
					//--------------------------------------------------------------------------------------------------------------
					if (($ACTION == 'save_order') || ($ACTION == 'save_invoice' && isset($dataSale[0]['SalSale']['id']) == true && isset($dataSale[1]['SalSale']['id']) == true)){
//					if($ACTION=='save_order'){
//						if(!$this->SalDetail->deleteAll(array('SalDetail.sal_sale_id'=>$dataMovementDetail[1]['SalDetail']['sal_sale_id'],	
//																'SalDetail.inv_warehouse_id'=>$dataMovementDetail[1]['SalDetail']['inv_warehouse_id'], 
//																'SalDetail.inv_item_id'=>$dataMovementDetail[1]['SalDetail']['inv_item_id']))){
//							$dataSource->rollback();
//							return 'error';
//						}
					
						//--------------------------------------------------------------------------------------------------------------
						$salDetailsIds = $this->SalDetail->find('list', array(
							'conditions' => array(
								'SalDetail.sal_sale_id'=>$dataSaleDetail[1]['SalDetail']['sal_sale_id'],	
								'SalDetail.inv_warehouse_id'=>$dataSaleDetail[1]['SalDetail']['inv_warehouse_id'], 
								'SalDetail.inv_item_id'=>$dataSaleDetail[1]['SalDetail']['inv_item_id']
							),
							'fields' => array('SalDetail.id', 'SalDetail.id')
						));

						foreach ($salDetailsIds as $salDetailsId) {
							try {
								$this->SalDetail->id = $salDetailsId;	
								$this->SalDetail->delete();
							} catch (Exception $e) {
//								debug($e);
								$dataSource->rollback();
								return 'ERROR';
							}
						}	
						//--------------------------------------------------------------------------------------------------------------
					}	
					break;
				case 'DELETE_PAY':
//					if(!$this->SalPayment->deleteAll(array('SalPayment.sal_sale_id'=>$dataPayDetail['SalPayment']['sal_sale_id'], 
//															'SalPayment.date'=>$dataPayDetail['SalPayment']['date']))){
//						$dataSource->rollback();
//						return 'error';
//					}
					//--------------------------------------------------------------------------------------------------------------
//					print_r($dataPayDetail);
//					die();
					$salPaymentsIds = $this->SalPayment->find('list', array(
						'conditions' => array(
							'SalPayment.sal_sale_id'=>$dataPayDetail['SalPayment']['sal_sale_id'], 
							'SalPayment.date'=>$dataPayDetail['SalPayment']['date']
						),
						'fields' => array('SalPayment.id', 'SalPayment.id')
					));
					foreach ($salPaymentsIds as $salPaymentsId) {
						try {
							$this->SalPayment->id = $salPaymentsId;	
							$this->SalPayment->delete();
						} catch (Exception $e) {
//								debug($e);
							$dataSource->rollback();
							return 'ERROR';
						}
					}	
					//--------------------------------------------------------------------------------------------------------------	
					break;
				case 'DISTRIB':	
					//--------------------------------------------------------------------------------------------------------------
					$salDetailsIds = $this->SalDetail->find('list', array(
							'conditions'=>array(
								'SalDetail.sal_sale_id'=>$dataSaleDetail[0]['SalDetail']['sal_sale_id'], 
								'SalDetail.inv_warehouse_id'=>$dataSaleDetail[0]['SalDetail']['inv_warehouse_id'], 
								'SalDetail.inv_item_id'=>$dataSaleDetail[0]['SalDetail']['inv_item_id']
							),
							'fields'=>array('SalDetail.id', 'SalDetail.id')
						));
					
					foreach ($salDetailsIds as $salDetailsId) {
						try {
								$this->SalDetail->save(array(
									'id'=>$salDetailsId, 
									'inv_warehouse_id'=>$dataSaleDetail[0]['SalDetail']['inv_warehouse_id'],
//									'sale_price'=>$dataSaleDetail[0]['SalDetail']['sale_price'], 
									'quantity'=>$dataSaleDetail[0]['SalDetail']['quantity']
										,'backorder'=>$dataSaleDetail[0]['SalDetail']['backorder'])
//									'ex_sale_price'=>$dataSaleDetail[0]['SalDetail']['ex_sale_price'])
								);
						} catch (Exception $e) {
//								debug($e);
							$dataSource->rollback();
							return 'ERROR';
						}
					}
					$salDetailsDistribIds = $this->SalDetail->find('list', array(
							'conditions'=>array(
								'SalDetail.sal_sale_id'=>$dataSaleDetail[0]['SalDetail']['sal_sale_id'], 
								'SalDetail.inv_warehouse_id'=>$dataSaleDetail[2]['SalDetail']['inv_warehouse_id'], 
								'SalDetail.inv_item_id'=>$dataSaleDetail[2]['SalDetail']['inv_item_id']
							),
							'fields'=>array(/*'SalDetail.id', 'SalDetail.id',*/ 'SalDetail.quantity', 'SalDetail.id')
						));
						
					if($salDetailsDistribIds != array()){
						$lastQuantity = key($salDetailsDistribIds);
						foreach ($salDetailsDistribIds as $salDetailsDistribId) {
							try {
									$this->SalDetail->save(array(
										'id'=>$salDetailsDistribId, 
										'inv_warehouse_id'=>$dataSaleDetail[2]['SalDetail']['inv_warehouse_id'],
//											'sale_price'=>$dataSaleDetail[2]['SalDetail']['sale_price'], 
										'quantity'=>$dataSaleDetail[2]['SalDetail']['quantity']+$lastQuantity
											,'backorder'=>$dataSaleDetail[2]['SalDetail']['backorder'])
//											'ex_sale_price'=>$dataSaleDetail[2]['SalDetail']['ex_sale_price'])
									);
							} catch (Exception $e) {
	//								debug($e);
								$dataSource->rollback();
								return 'ERROR';
							}
						}
					}else{
						$dataSaleDetail[2]['SalDetail']['sal_sale_id'] = $dataSaleDetail[0]['SalDetail']['sal_sale_id']; 
						if(!$this->SalDetail->saveAll($dataSaleDetail[2])){
							$dataSource->rollback();
							return 'error';
						}
					}
					//--------------------------------------------------------------------------------------------------------------
					if (($ACTION == 'save_order') || ($ACTION == 'save_invoice' && isset($dataSale[0]['SalSale']['id']) == true && isset($dataSale[1]['SalSale']['id']) == true)){
						//--------------------------------------------------------------------------------------------------------------
						$salDetailsIds = $this->SalDetail->find('list', array(
							'conditions'=>array(
								'SalDetail.sal_sale_id'=>$dataSaleDetail[1]['SalDetail']['sal_sale_id'], 
								'SalDetail.inv_warehouse_id'=>$dataSaleDetail[1]['SalDetail']['inv_warehouse_id'], 
								'SalDetail.inv_item_id'=>$dataSaleDetail[1]['SalDetail']['inv_item_id']
							),
							'fields'=>array('SalDetail.id', 'SalDetail.id')
						));

						foreach ($salDetailsIds as $salDetailsId) {
							try {
									$this->SalDetail->save(array(
										'id'=>$salDetailsId, 
										'inv_warehouse_id'=>$dataSaleDetail[1]['SalDetail']['inv_warehouse_id'],
//										'sale_price'=>$dataSaleDetail[1]['SalDetail']['sale_price'], 
										'quantity'=>$dataSaleDetail[1]['SalDetail']['quantity']
											,'backorder'=>$dataSaleDetail[1]['SalDetail']['backorder'])
//										'ex_sale_price'=>$dataSaleDetail[1]['SalDetail']['ex_sale_price'])
									);
							} catch (Exception $e) {
	//								debug($e);
								$dataSource->rollback();
								return 'ERROR';
							}
						}
						
						$salDetailsDistribIds = $this->SalDetail->find('list', array(
							'conditions'=>array(
								'SalDetail.sal_sale_id'=>$dataSaleDetail[1]['SalDetail']['sal_sale_id'], 
								'SalDetail.inv_warehouse_id'=>$dataSaleDetail[3]['SalDetail']['inv_warehouse_id'], 
								'SalDetail.inv_item_id'=>$dataSaleDetail[3]['SalDetail']['inv_item_id']
							),
							'fields'=>array(/*'SalDetail.id', 'SalDetail.id',*/ 'SalDetail.quantity', 'SalDetail.id')
						));
					
						if($salDetailsDistribIds != array()){
							$lastQuantity = key($salDetailsDistribIds);
							foreach ($salDetailsDistribIds as $salDetailsDistribId) {
								try {
										$this->SalDetail->save(array(
											'id'=>$salDetailsDistribId, 
											'inv_warehouse_id'=>$dataSaleDetail[3]['SalDetail']['inv_warehouse_id'],
//												'sale_price'=>$dataSaleDetail[2]['SalDetail']['sale_price'], 
											'quantity'=>$dataSaleDetail[3]['SalDetail']['quantity']+$lastQuantity
												,'backorder'=>$dataSaleDetail[3]['SalDetail']['backorder'])
//												'ex_sale_price'=>$dataSaleDetail[2]['SalDetail']['ex_sale_price'])
										);
								} catch (Exception $e) {
		//								debug($e);
									$dataSource->rollback();
									return 'ERROR';
								}
							}
						}else{
							$dataSaleDetail[3]['SalDetail']['sal_sale_id'] = $dataSaleDetail[1]['SalDetail']['sal_sale_id']; 
							if(!$this->SalDetail->saveAll($dataSaleDetail[3])){
								$dataSource->rollback();
								return 'error';
							}
						}
						//--------------------------------------------------------------------------------------------------------------
					}	
					break;
			}		
//			debug($dataMovement);//null
//			die();
			if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED' && $dataMovement != null){
				foreach($dataMovement AS $row) { 
					if(!ClassRegistry::init('InvMovement')->saveAll($row)){
						$dataSource->rollback();
						return 'error';
					}else{
						$idMovement[] = ClassRegistry::init('InvMovement')->id;
					}
				} 	
				//--------------------------------------------------------------------------------------------------------------
//				print_r($dataSaleDetail[0]);
//				die();
				$salDetailsIds = 
						array_values(
								$this->SalDetail->find('list', array(
						'conditions'=>array(
							'SalDetail.sal_sale_id'=>$dataSaleDetail[0]['SalDetail']['sal_sale_id'], 
							'SalDetail.inv_warehouse_id'=> /*array(1,1,1,2),*/$dataSaleDetail[0]['SalDetail']['inv_warehouse_id'], 
							'SalDetail.inv_item_id'=> /*array(305,294,241,307)*/$dataSaleDetail[0]['SalDetail']['inv_item_id']
						),
						'fields'=>array('SalDetail.id'/*, 'SalDetail.id'*/)
//						'fields'=>'SalDetail.id'/*, 'SalDetail.id'*/
//									'fields'=>array('SalDetail.id', 'SalDetail.inv_item_id', 'SalDetail.inv_warehouse_id')
//									,'order' => 'SalDetail.inv_item_id ASC',
									,'order' => array('SalDetail.inv_item_id ASC', 'SalDetail.inv_warehouse_id ASC')
					))
						)
							;
//					print_r($dataSaleDetail);
//					print_r($salDetailsIds);
//					die();
//				foreach ($salDetailsIds as $salDetailsId) {
//					try {
//						print_r($salDetailsId);
////						print_r($dataSaleDetail[0]['SalDetail']['approved'][]);
////							$this->SalDetail->save(array(
//							$hoho[] = array(
//								'id'=>$salDetailsId, 
//								'approved'=>$dataSaleDetail[0]['SalDetail']['approved'][0]
////									)
//							);
//					} catch (Exception $e) {
//						$dataSource->rollback();
//						return 'ERROR';
//					}
					
//				}
				
				 for ($i = 0; $i < count($salDetailsIds); $i++) {
					 try {
						 $this->SalDetail->save(array(
//						 $hoho1[] = array(
							 'id'=>$salDetailsIds[$i], 
							 'approved'=>$dataSaleDetail[0]['SalDetail']['approved'][$i]
								 ,'inv_item_id'=>$dataSaleDetail[0]['SalDetail']['inv_item_id'][$i]
								 ,'inv_warehouse_id'=>$dataSaleDetail[0]['SalDetail']['inv_warehouse_id'][$i]
								 )
							);
						 } catch (Exception $e) {
						$dataSource->rollback();
						return 'ERROR';
					}
				 }
				//--------------------------------------------------------------------------------------------------------------
//				print_r($dataSaleDetail[0]['SalDetail']['inv_item_id']);
//				 print_r($dataSaleDetail[0]['SalDetail']['inv_warehouse_id']);
//				die();
//				$dataSaleDetail[0]
//				$dataSaleDetail[1]
				//--------------------------------------------------------------------------------------------------------------
				if (isset($dataSale[0]['SalSale']['id']) == true && isset($dataSale[1]['SalSale']['id']) == true){
					$salDetailsIds = array_values($this->SalDetail->find('list', array(
						'conditions'=>array(
							'SalDetail.sal_sale_id'=>$dataSaleDetail[1]['SalDetail']['sal_sale_id'], 
							'SalDetail.inv_warehouse_id'=>$dataSaleDetail[1]['SalDetail']['inv_warehouse_id'], 
							'SalDetail.inv_item_id'=>$dataSaleDetail[1]['SalDetail']['inv_item_id']
						),
						'fields'=>'SalDetail.id'
						,'order' => array('SalDetail.inv_item_id ASC', 'SalDetail.inv_warehouse_id ASC')
					)));

					for ($i = 0; $i < count($salDetailsIds); $i++) {
						 try {
							 $this->SalDetail->save(array(
//							 $hoho2[] = array(
								 'id'=>$salDetailsIds[$i], 
								 'approved'=>$dataSaleDetail[1]['SalDetail']['approved'][$i]
									  ,'inv_item_id'=>$dataSaleDetail[1]['SalDetail']['inv_item_id'][$i]
									,'inv_warehouse_id'=>$dataSaleDetail[1]['SalDetail']['inv_warehouse_id'][$i]
									 )
								);
							 } catch (Exception $e) {
							$dataSource->rollback();
							return 'ERROR';
						}
					 }	
					//--------------------------------------------------------------------------------------------------------------
				}
				/////////////////////////////////////// ESTO VA ACA O EN EL CONTROLLER? //////////////////////////////////////////////////////
//				$appQtyBo = $this->SalDetail->find('first', array(
//					'fields' => array('SUM("SalDetail"."quantity") - SUM("SalDetail"."approved") AS not_delivered',
//										'SUM("SalDetail"."backorder") AS backorder'),
//					'conditions' => array('SalSale.id' => $id)
//				));
				
//				if($notDeliveredSum > 0){
//					if($backorderSum > 0){
//						$STATE = 'SINVOICE_PENDANT';
//					}else{
//						$STATE = 'SINVOICE_APPROVED';
//					}
//				}else{
//					$STATE = 'SINVOICE_PENDANT';
//				}
				/////////////////////////////////////////////////////////////////////////////////////////////

/////////////////////////////////////////////////////////////////////////////////////////////
//				$strItemsApproved = $this->_createStringItemsApproved($dataSaleDetail[0]['SalDetail']['inv_item_id'], $dataSaleDetail[0]['SalDetail']['inv_warehouse_id']);
//$strItemsApproved .= '|'.$this->_createStringItemsApproved($dataSaleDetail[0]);
/////////////////////////////////////////////////////////////////////////////////////////////
//				for($i = 0; $i < count($dataSaleDetail[0]['SalDetail']['inv_item_id']); $i++){
//					$strItemsApproved .= $dataSaleDetail[0]['SalDetail']['inv_item_id'][$i].'w'.$dataSaleDetail[0]['SalDetail']['inv_warehouse_id'][$i].',';
//					$strItemsApprovedQuantity .= $dataSaleDetail[0]['SalDetail']['approved'][$i].',';
//				}
/////////////////////////////////////////////////////////////////////////////////////////////
//			print_r($strItemsApprovedQuantity);
//			die();
//			print_r($dataSaleDetail1[0]);
				for($i = 0; $i < count($dataSaleDetail[0]['SalDetail']['inv_item_id']); $i++){
					$strItemsApproved .= $dataSaleDetail[0]['SalDetail']['inv_item_id'][$i].'w'.$dataSaleDetail[0]['SalDetail']['inv_warehouse_id'][$i].',';
					$strItemsApprovedQuantity .= $dataSaleDetail[0]['SalDetail']['approved'][$i].',';
					
					if($dataSaleDetail[0]['SalDetail']['approved'][$i] == $dataSaleDetail[0]['SalDetail']['quantity'][$i]){
						$strItemsCompletelyApproved .= $dataSaleDetail[0]['SalDetail']['inv_item_id'][$i].'w'.$dataSaleDetail[0]['SalDetail']['inv_warehouse_id'][$i].',';
					}
					else{
						$strItemsHalfApproved .= $dataSaleDetail[0]['SalDetail']['inv_item_id'][$i].'w'.$dataSaleDetail[0]['SalDetail']['inv_warehouse_id'][$i].',';
					}
				}
				
				for($i = 0; $i < count($dataSaleDetail1[0]['SalDetail']['inv_item_id']); $i++){
					$strItemsCompletelyBackordered .= $dataSaleDetail1[0]['SalDetail']['inv_item_id'][$i].'w'.$dataSaleDetail1[0]['SalDetail']['inv_warehouse_id'][$i].',';
				}
//print_r($strItemsCompletelyBackordered);
//die();
//$strItemsCompletelyApproved = $this->_createStringItemsApproved($dataSaleDetail[0]['SalDetail']['inv_item_id'], $dataSaleDetail[0]['SalDetail']['inv_warehouse_id']);
//$strItemsApproved .= '|'.$this->_createStringItemsApproved($dataSaleDetail[0]);
/////////////////////////////////////////////////////////////////////////////////////////////
			}
			
			if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_CANCELLED' && $dataMovement != null){
				
				$invPricesIds = ClassRegistry::init('InvPrice')->find('list', array(
					'conditions' => array(
						'InvPrice.code'=>$dataSale[0]['SalSale']['code_for_prices']
					),
					'fields' => array('InvPrice.id', 'InvPrice.id')
				));

				foreach ($invPricesIds as $invPricesId) {
					try {
						ClassRegistry::init('InvPrice')->id = $invPricesId;	
						ClassRegistry::init('InvPrice')->delete();
					} catch (Exception $e) {
						$dataSource->rollback();
						return 'ERROR';
					}
				}	
				
				foreach($dataMovement AS $row) { 
					if(!ClassRegistry::init('InvMovement')->saveAll($row)){
						$dataSource->rollback();
						return 'error';
					}
//					else{
//						$idMovement[] = ClassRegistry::init('InvMovement')->id;
//					}
				} 	
			}
			
			if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED'){
				if($notDeliveredSum > 0){
					if($backorderSum > 0){
						$STATE = 'SINVOICE_PENDANT';
					}else{
						$STATE = 'SINVOICE_APPROVED';
					}
				}else{
//					$STATE = 'SINVOICE_PENDANT';
					$STATE = 'SINVOICE_APPROVED';
				}
			}
			
//			print_r($strItemsApproved);
//			die();
			//MAYBE I COULD PUT THIS BEFORE ^ TO SAVE PRICES EVERYTIME THEY ARE APPROVED
			if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED' && $arraySalePrices != array()){
			
				if(!ClassRegistry::init('InvPrice')->saveAll($arraySalePrices)){
					$dataSource->rollback();
					return 'error';
				}
				
			}		
//			print_r($dataSale);
//			die();
		$dataSource->commit();
		if(!isset($dataSale[0]['SalSale']['paid'])){
			$dataSale[0]['SalSale']['paid'] = '';
		}
//		 return array("json" => $json, "selectedIds" => $selectedIds, "listDatatableIdsSums" => $listDatatableIdsSums); //$data;
		return array('SUCCESS', $STATE.'|'.$idSale1.'|'.$saleNoteDocCode.'|'.$saleCode.'|'.$strItemsApproved.'|'.$strItemsApprovedQuantity.'|'.$strItemsCompletelyApproved.'|'.$strItemsHalfApproved.'|'.$strItemsCompletelyBackordered.'|'.$dataSale[0]['SalSale']['paid']);
//		return array('SUCCESS', $STATE.'|'.$idSale1);
//		return array('SUCCESS', $STATE.'|'.$this->id);
	}
	
	public function updateMovement($dataSale, $dataMovement, $pricesCode){
//            print_r($dataSale);
		$dataSource = $this->getDataSource();
		$dataSource->begin();
		if($dataSale[1] != array()){
			if(!$this->saveAll($dataSale[1])){
				$dataSource->rollback();
				return 'ERROR';
			}else{
				$idSale = $this->id;
			}
		}
                if($dataSale[2] != array()){
			if(!$this->saveAll($dataSale[2])){
				$dataSource->rollback();
				return 'ERROR';
			}else{
				$idSale = $this->id;
			}
		}
                if($dataSale[0] != array()){
			if(!$this->saveAll($dataSale[0])){
				$dataSource->rollback();
				return 'ERROR';
			}else{
				$idSale = $this->id;
			}
		}	
		if($dataMovement != array()){
			if(!ClassRegistry::init('InvMovement')->saveAll($dataMovement)){
				$dataSource->rollback();
				return 'ERROR';
			}else{
				$idMovement = ClassRegistry::init('InvMovement')->id;
			}
		}
                if($pricesCode != ''){
                    $invPricesIds = ClassRegistry::init('InvPrice')->find('list', array(
                            'conditions' => array(
                                    'InvPrice.code'=>$pricesCode
                            ),
                            'fields' => array('InvPrice.id', 'InvPrice.id')
                    ));

                    foreach ($invPricesIds as $invPricesId) {
                            try {
                                    ClassRegistry::init('InvPrice')->id = $invPricesId;	
                                    ClassRegistry::init('InvPrice')->delete();
                            } catch (Exception $e) {
                                    $dataSource->rollback();
                                    return 'ERROR';
                            }
                    }	
                }
		$dataSource->commit();
		if(($dataSale[0] != array() || $dataSale[1] != array()|| $dataSale[2] != array()) && $dataMovement != array()){
			return array('SUCCESS', $idSale);
		}elseif(($dataSale[0] != array() || $dataSale[1] != array()) && $dataMovement != array()){
			return array('SUCCESS', $idSale);
		}elseif($dataSale[0] != array() || $dataSale[1] != array() ){
			return array('SUCCESS', $idSale);
		}elseif ($dataMovement != array()) {
			return array('SUCCESS', $idMovement);
		}
			
	}
	
	public function saveGeneratedMovements(/*$idsToDelete,*/ $data){
		$dataSource = $this->getDataSource();
		$dataSource->begin();
		
//		if(!ClassRegistry::init('InvMovement')->deleteAll($idsToDelete, true)){
//			$dataSource->rollback();
//			return 'error';
//		}
		
		foreach($data AS $row) { 
			if(!ClassRegistry::init('InvMovement')->saveAll($row)){
				$dataSource->rollback();
				return 'error';
			}else{
				$idMovement[] = ClassRegistry::init('InvMovement')->id;
			}
		} 	
		$dataSource->commit();
		return array('SUCCESS', implode("|",$idMovement));
	}	
	
	private function _createStringItemsApproved($arrayItemsIds, $arrayItemsWarehouses){
		////////////////////////////////////////////INICIO-CREAR CADENA ITEMS STOCK ACUTALIZADOS//////////////////////////////
			$strItemsApproved = '';
			/////////////////for new stock method 
//			$items = array();
//			foreach ($arrayItemsDetails as $value) {//get a clean items arrays
//				$items[$value['inv_item_id']] = $value['inv_item_id'];
//			}
//			$stocks = $this->_get_stocks($items, $idWarehouse);//get all the stocks
			///////////////////
			for($i = 0; $i<count($arrayItemsIds); $i++){
				//$updatedStock = $this->_find_stock($arrayItemsDetails[$i]['inv_item_id'], $idWarehouse);
//				$updatedStock = $this->_find_item_stock($stocks, $arrayItemsDetails[$i]['inv_item_id']);
				$strItemsApproved .= $arrayItemsIds[$i].'w'.$arrayItemsWarehouses[$i].',';
			}
			////////////////////////////////////////////FIN-CREAR CADENA ITEMS STOCK ACUTALIZADOS/////////////////////////////////
			return $strItemsApproved;
	}
	//////////////////////////////////////////// START - PRIVATE ///////////////////////////////////////////////
	
	public function _validateItemsStocksOut($arrayItemsDetails){
		$strItemsStockErrorSuccess = '';
		/////////////////for new stock method 
		$items = array();
		foreach ($arrayItemsDetails as $value) {//get a clean items arrays
			$items[$value['inv_item_id']] = $value['inv_item_id'];
		}
		$warehouses = array();
		foreach ($arrayItemsDetails as $value) {//get a clean warehouses arrays
			$warehouses[$value['inv_warehouse_id']] = $value['inv_warehouse_id'];
		}
		$stocksReal = $this->_get_real_stocks_by_warehouse($items, $warehouses);//get all the stocks
		if($stocksReal == 'ERROR'){
			return 'ERROR';
		}
//		print_r($stocksReal);
		$stocksReserved = $this->_get_reserved_stocks_by_warehouse_minus_backorder($items, $warehouses);//get all the stocks
		if($stocksReserved == 'ERROR'){
			return 'ERROR';
		}
//		print_r($stocksReserved);
		///////////////////
		$cont=0;
		for($i = 0; $i<count($arrayItemsDetails); $i++){
			$updatedRealStock = $this->_find_real_item_stock_by_warehouse($stocksReal, $arrayItemsDetails[$i]['inv_item_id'], array($arrayItemsDetails[$i]['inv_warehouse_id']));
			$updatedReservedStock = $this->_find_reserved_item_stock_by_warehouse($stocksReserved, $arrayItemsDetails[$i]['inv_item_id'], array($arrayItemsDetails[$i]['inv_warehouse_id']));
			$updatedVirtualStock = $updatedRealStock - $updatedReservedStock;
			$requiredQuantity = $arrayItemsDetails[$i]['quantity'] - $arrayItemsDetails[$i]['backorder'];
			if(($updatedVirtualStock + $requiredQuantity) < $requiredQuantity){
				$updatedVirtualStock = $updatedVirtualStock + ($arrayItemsDetails[$i]['quantity'] - $arrayItemsDetails[$i]['backorder']);//adding the quantity decreased in the current detail
				$strItemsStockErrorSuccess .= $arrayItemsDetails[$i]['inv_item_id'].'w'.$arrayItemsDetails[$i]['inv_warehouse_id'].'=>error:'.$updatedVirtualStock.','; //error
				$cont++;
			}else{
				$strItemsStockErrorSuccess .= $arrayItemsDetails[$i]['inv_item_id'].'w'.$arrayItemsDetails[$i]['inv_warehouse_id'].'=>success:'.$updatedVirtualStock.',';//success
			}
		}
		return array('error'=>$cont, 'itemsStocks'=>$strItemsStockErrorSuccess);
	}
	
	//gets a list of (inv_item_id, inv_warehouse_id and its stock) for the required items in the required warehouses (inv_movement_details) (so we can get stock by warehouse)
    private function _get_real_stocks_by_warehouse($items, $warehouses, $limitDate = '', $dateOperator = '<=') {
//        $this->loadModel('InvMovement');
        ClassRegistry::init('InvMovement')->InvMovementDetail->unbindModel(array('belongsTo' => array('InvItem')));
        ClassRegistry::init('InvMovement')->InvMovementDetail->bindModel(array(
            'hasOne' => array(
                'InvMovementType' => array(
                    'foreignKey' => false,
                    'conditions' => array('InvMovement.inv_movement_type_id = InvMovementType.id')
                )
            )
        ));
        $dateRanges = array();
        if ($limitDate <> '') {
            $dateRanges = array('InvMovement.date ' . $dateOperator => $limitDate);
        }

		try{
			$movements = ClassRegistry::init('InvMovement')->InvMovementDetail->find('all', array(
				'fields' => array(
					"InvMovementDetail.inv_item_id",
					"InvMovement.inv_warehouse_id",
					"(SUM(CASE WHEN \"InvMovementType\".\"status\" = 'entrada' AND \"InvMovement\".\"lc_state\" = 'APPROVED' THEN \"InvMovementDetail\".\"quantity\" ELSE 0 END))-
					(SUM(CASE WHEN \"InvMovementType\".\"status\" = 'salida' AND \"InvMovement\".\"lc_state\" = 'APPROVED' THEN \"InvMovementDetail\".\"quantity\" ELSE 0 END)) AS stock"
				),
				'conditions' => array(
					'InvMovement.inv_warehouse_id' => $warehouses,
					'InvMovementDetail.inv_item_id' => $items,
					$dateRanges
				),
				'group' => array('InvMovementDetail.inv_item_id', 'InvMovement.inv_warehouse_id'),
				'order' => array('InvMovementDetail.inv_item_id', 'InvMovement.inv_warehouse_id')
			));
			return $movements;
		}catch (Exception $e){
			return 'ERROR';
		}	
    }
	
		//gets the added stock of one item from the list that gets _get_real_stocks_by_warehouse, from one or more warehouses (inv_movement_details)
    private function _find_real_item_stock_by_warehouse($stocks, $item, $warehouses) {
        $stockTotal = 0;
        foreach ($stocks as $stock) {//find required stock inside stocks array
            foreach ($warehouses as $warehouse) {
                if ($item == $stock['InvMovementDetail']['inv_item_id'] && $warehouse == $stock['InvMovement']['inv_warehouse_id']) {
                    $stockTotal = $stockTotal + $stock[0]['stock'];
                }
            }
        }
        //this fixes in case there isn't any item inside movement_details yet with a determinated warehouse
        return $stockTotal;
    }
	
	//gets a list of (inv_item_id, inv_warehouse_id and its (quantity - approved - backorder = reserved quantity)) for the required items in the required warehouses (sal_details)
    private function _get_reserved_stocks_by_warehouse_minus_backorder($items, $warehouses, $limitDate = '', $dateOperator = '<=') {
        $dateRanges = array();
        if ($limitDate <> '') {
            $dateRanges = array('SalSale.date ' . $dateOperator => $limitDate);
        }

		try{
			$sales = $this->SalDetail->find('all', array(
				'fields' => array(
					"SalDetail.inv_item_id",
					"SalDetail.inv_warehouse_id",
	//				"(SUM(CASE WHEN \"SalSale\".\"lc_state\" IN ('DRAFT','SINVOICE_PENDANT') THEN \"SalDetail\".\"quantity\" ELSE 0 END)) AS stock_reserved"
	//              "(SUM(CASE WHEN \"SalSale\".\"lc_state\" IN ('DRAFT','SINVOICE_PENDANT') THEN (\"SalDetail\".\"quantity\" - \"SalDetail\".\"backorder\") ELSE 0 END)) AS stock_reserved"
					"(SUM(CASE WHEN \"SalSale\".\"lc_state\" IN ('DRAFT','SINVOICE_PENDANT') THEN (\"SalDetail\".\"quantity\" - \"SalDetail\".\"approved\" - \"SalDetail\".\"backorder\") ELSE 0 END)) AS stock_reserved"
				),
				'conditions' => array(
					'SalDetail.inv_warehouse_id' => $warehouses,
					'SalDetail.inv_item_id' => $items,
					$dateRanges
				),
				'group' => array('SalDetail.inv_item_id', 'SalDetail.inv_warehouse_id'),
				'order' => array('SalDetail.inv_item_id', 'SalDetail.inv_warehouse_id')
			));
			return $sales;
		}catch (Exception $e){
			return 'ERROR';
		}	
    }
	
	//gets the stock of one item from the list that gets _get_real_stocks (inv_movement_details)
    private function _find_reserved_item_stock_by_warehouse($stocks, $item, $warehouses) {
        $stockTotal = 0;
        foreach ($stocks as $stock) {//find required stock inside stocks array
            foreach ($warehouses as $warehouse) {
                if ($item == $stock['SalDetail']['inv_item_id'] && $warehouse == $stock['SalDetail']['inv_warehouse_id']) {
                    $stockTotal = $stockTotal + $stock[0]['stock_reserved'];
                }
            }
        }
        //this fixes in case there isn't any item inside movement_details yet with a determinated warehouse
        return $stockTotal;
    }
	
	//////////////////////////////////////////// END - PRIVATE ///////////////////////////////////////////////
}
