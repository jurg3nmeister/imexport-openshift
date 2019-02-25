<?php
App::uses('AppModel', 'Model');
/**
 * SalCustomer Model
 *
 * @property SalTaxNumber $SalTaxNumber
 * @property SalEmployee $SalEmployee
 */
class SalCustomer extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';


	//The Associations below have been created with all possible keys, those that are not needed can be removed

/**
 * hasMany associations
 *
 * @var array
 */
	public $hasMany = array(
		'SalTaxNumber' => array(
			'className' => 'SalTaxNumber',
			'foreignKey' => 'sal_customer_id',
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
		'SalEmployee' => array(
			'className' => 'SalEmployee',
			'foreignKey' => 'sal_customer_id',
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
	
	public function deleteCustomer($id){
		$dataSource = $this->getDataSource();
		$dataSource->begin();
		
		$taxNumbersIds = $this->SalTaxNumber->find('list', array(
			'conditions' => array(
				'SalTaxNumber.sal_customer_id'=>$id
			),
			'fields' => array('SalTaxNumber.id', 'SalTaxNumber.id')
		));
		foreach ($taxNumbersIds as $taxNumbersId) {
			try {
				$this->SalTaxNumber->id = $taxNumbersId;	
				$this->SalTaxNumber->delete();
			} catch (Exception $e) {	
				$dataSource->rollback();
				return 'error';
			}		
		}
		
		$employeesIds = $this->SalEmployee->find('list', array(
			'conditions' => array(
				'SalEmployee.sal_customer_id'=>$id
			),
			'fields' => array('SalEmployee.id', 'SalEmployee.id')
		));
		foreach ($employeesIds as $employeesId) {
			try {
				$this->SalEmployee->id = $employeesId;	
				$this->SalEmployee->delete();
			} catch (Exception $e) {	
				$dataSource->rollback();
				return 'error';
			}
		}
		
		try {
			$this->delete();
		} catch (Exception $e) {	
			$dataSource->rollback();
			return 'error';
		}
		
		$dataSource->commit();
		return 'success';
	
	}

}
