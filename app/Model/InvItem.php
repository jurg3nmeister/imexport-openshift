<?php
App::uses('AppModel', 'Model');
/**
 * InvItem Model
 *
 * @property InvBrand $InvBrand
 * @property InvCategory $InvCategory
 * @property InvPrice $InvPrice
 * @property InvMovement $InvMovement
 * @property InvItemsSupplier $invItemsSupplier
 */
class InvItem extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $virtualFields = array("full_name"=>"CONCAT('[ ',InvItem.code, ' ] ' ,InvItem.name)");
	public $displayField = 'full_name';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
		'inv_brand_id' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'inv_category_id' => array(
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
				//'message' => 'No puede estar vacio',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'name' => array(
			'notempty' => array(
				'rule' => array('notempty'),
				//'message' => 'Your custom message here',
				//'allowEmpty' => false,
				//'required' => false,
				//'last' => false, // Stop validation after this rule
				//'on' => 'create', // Limit validation to 'create' or 'update' operations
			),
		),
		'description' => array(
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
		'InvBrand' => array(
			'className' => 'InvBrand',
			'foreignKey' => 'inv_brand_id',
			'conditions' => '',
			'fields' => '',
			'order' => ''
		),
		'InvCategory' => array(
			'className' => 'InvCategory',
			'foreignKey' => 'inv_category_id',
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
		'InvPrice' => array(
			'className' => 'InvPrice',
			'foreignKey' => 'inv_item_id',
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
		'InvMovementDetail' => array(
			'className' => 'InvMovementDetail',
			'foreignKey' => 'inv_item_id',
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
		'InvItemsSupplier' => array(
			'className' => 'InvItemsSupplier',
			'foreignKey' => 'inv_item_id',
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

	
	public function updateItem($invItem, $invItemsSupplier){
		$dataSource = $this->getDataSource();
		$dataSource->begin();
		
		
		if($invItem != array()){
			if(!$this->saveAll($invItem)){
				$dataSource->rollback();
				return 'ERROR';
			}else{
				$idItem = $this->id;
			}
		}	
		$idItemSupplier = '';
		if($invItemsSupplier['id'] == '' ){
			if($invItemsSupplier['inv_supplier_id'] != 0){//ADD
				if($invItemsSupplier != array()){
					if(!ClassRegistry::init('InvItemsSupplier')->saveAll($invItemsSupplier)){
						$dataSource->rollback();
						return 'ERROR';
					}else{
						$idItemSupplier = ClassRegistry::init('InvItemsSupplier')->id;
					}
				}
			}			
		}elseif($invItemsSupplier['id'] != ''){//EDIT OR DELETE		
			if( $invItemsSupplier['inv_supplier_id'] != 0){//EDIT
				if($invItemsSupplier != array()){
					if(!ClassRegistry::init('InvItemsSupplier')->saveAll($invItemsSupplier)){
						$dataSource->rollback();
						return 'ERROR';
					}else{
						$idItemSupplier = ClassRegistry::init('InvItemsSupplier')->id;
					}
				}
			}else{//DELETE
				try {
					ClassRegistry::init('InvItemsSupplier')->id = $invItemsSupplier['id'];
					ClassRegistry::init('InvItemsSupplier')->delete();
				} catch (Exception $e) {
					$dataSource->rollback();
					return 'ERROR';
				}
			}
		}
		$dataSource->commit();
//		if(($dataSale[0] != array() || $dataSale[1] != array()) && $dataMovement != array()){
			return array('SUCCESS', $idItem, $idItemSupplier);
//		}elseif($dataSale[0] != array() || $dataSale[1] != array() ){
//			return array('SUCCESS', $idSale);
//		}elseif ($dataMovement != array()) {
//			return array('SUCCESS', $idMovement);
//		}
			
	}
	
}
