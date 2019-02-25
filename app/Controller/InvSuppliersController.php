<?php
App::uses('AppController', 'Controller');
/**
 * InvSuppliers Controller
 *
 * @property InvSupplier $InvSupplier
 */
class InvSuppliersController extends AppController {

/**
 *  Layout
 *
 * @var string
 */
	public $layout = 'default';

/**
 * Helpers
 *
 * @var array
 */
	//public $helpers = array('TwitterBootstrap.BootstrapHtml', 'TwitterBootstrap.BootstrapForm', 'TwitterBootstrap.BootstrapPaginator');
/**
 * Components
 *
 * @var array
 */
	//public $components = array('Session');
//	public  function isAuthorized($user){
//		return $this->Permission->isAllowed($this->name, $this->action, $this->Session->read('Permission.'.$this->name));
//	}
/**
 * index method
 *
 * @return void
 */
	public function index() {
		$this->InvSupplier->recursive = 0;
		$this->set('invSuppliers', $this->paginate());
	}

/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->InvSupplier->id = $id;
		if (!$this->InvSupplier->exists()) {
			throw new NotFoundException(__('Invalid %s', __('inv supplier')));
		}
		$this->set('invSupplier', $this->InvSupplier->read(null, $id));
	}

/**
 * add method
 *
 * @return void
 */
	public function add() {
		if ($this->request->is('post')) {
			$this->InvSupplier->create();
			if ($this->InvSupplier->save($this->request->data)) {
				$this->Session->setFlash(
					__('The %s has been saved', __('inv supplier')),
					'alert',
					array(
						'plugin' => 'TwitterBootstrap',
						'class' => 'alert-success'
					)
				);
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(
					__('The %s could not be saved. Please, try again.', __('inv supplier')),
					'alert',
					array(
						'plugin' => 'TwitterBootstrap',
						'class' => 'alert-error'
					)
				);
			}
		}
	}

/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		$this->InvSupplier->id = $id;
		if (!$this->InvSupplier->exists()) {
			throw new NotFoundException(__('Invalid %s', __('inv supplier')));
		}
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->request->data['InvSupplier']['lc_transaction'] = 'MODIFY';
			if ($this->InvSupplier->save($this->request->data)) {
				$this->Session->setFlash(
					__('The %s has been saved', __('inv supplier')),
					'alert',
					array(
						'plugin' => 'TwitterBootstrap',
						'class' => 'alert-success'
					)
				);
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(
					__('The %s could not be saved. Please, try again.', __('inv supplier')),
					'alert',
					array(
						'plugin' => 'TwitterBootstrap',
						'class' => 'alert-error'
					)
				);
			}
		} else {
			$this->request->data = $this->InvSupplier->read(null, $id);
		}
	}

/**
 * delete method
 *
 * @param string $id
 * @return void
 */
	public function delete($id = null) {
		if (!$this->request->is('post')) {
			throw new MethodNotAllowedException();
		}
		$this->InvSupplier->id = $id;
		if (!$this->InvSupplier->exists()) {
			throw new NotFoundException(__('Invalid %s', __('inv supplier')));
		}
		
//		$contacts = $this->InvSupplier->InvSupplierContact->find('count', array(
//			'conditions'=>array('InvSupplierContact.inv_supplier_id'=>$id)
//		));
		
		$itemsSuppliers = $this->InvSupplier->InvItemsSupplier->find('count', array(
			'conditions'=>array('InvItemsSupplier.inv_supplier_id'=>$id)
		));
//		print_r($itemsSuppliers);
//		die();
		$purDetails = $this->InvSupplier->PurDetail->find('count', array(
			'conditions'=>array('PurDetail.inv_supplier_id'=>$id)
		));
		
		if($itemsSuppliers == 0 && $purDetails == 0){
			$contactsIds = $this->InvSupplier->InvSupplierContact->find('list', array(
				'conditions' => array(
					'InvSupplierContact.inv_supplier_id'=>$id
				),
				'fields' => array('InvSupplierContact.id', 'InvSupplierContact.id')
			));
			foreach ($contactsIds as $contactsId) {
				try {
					$this->InvSupplier->InvSupplierContact->id = $contactsId;	
					$this->InvSupplier->InvSupplierContact->delete();
				} catch (Exception $e) {
						$this->Session->setFlash(
							__('No se puede eliminar el Contacto porque tiene tablas ligadas'),
							'alert',
							array(
								'plugin' => 'TwitterBootstrap',
								'class' => 'alert-error'
							)
						);
						$this->redirect(array('action' => 'index'));
				}
			}
			if ($this->InvSupplier->delete()) {
				$this->Session->setFlash(
					__('Se elimino el proveedor con exito'),
					'alert',
					array(
						'plugin' => 'TwitterBootstrap',
						'class' => 'alert-success'
					)
				);
				$this->redirect(array('action' => 'index'));
			}
		}
		
		$this->Session->setFlash(
			__('No se puede eliminar el proveedor por que tiene notas de compra y/o productos ligados!', __('inv supplier')),
			'alert',
			array(
				'plugin' => 'TwitterBootstrap',
				'class' => 'alert-error'
			)
		);
		$this->redirect(array('action' => 'index'));
		
//		print_r($purDetails);
//		die();
		
//		if($contacts > 0 || $itemsSuppliers > 0 || $purDetails > 0){
//			$contactsIds = $this->InvSupplier->InvSupplierContact->find('list', array(
//				'conditions' => array(
//					'InvSupplierContact.inv_supplier_id'=>$id
//				),
//				'fields' => array('InvSupplierContact.id', 'InvSupplierContact.id')
//			));
//			foreach ($contactsIds as $contactsId) {
//				try {
//					$this->InvSupplier->InvSupplierContact->id = $contactsId;	
//					$this->InvSupplier->InvSupplierContact->delete();
//				} catch (Exception $e) {
//						$this->Session->setFlash(
//							__('No se puede eliminar el Contacto porque tiene tablas ligadas'),
//							'alert',
//							array(
//								'plugin' => 'TwitterBootstrap',
//								'class' => 'alert-error'
//							)
//						);
//						$this->redirect(array('action' => 'index'));
//				}
//			}
//			////////////////////////////////////////////////////////////////////////////
//			$itemsSuppliersIds = $this->InvSupplier->InvItemsSupplier->find('list', array(
//				'conditions' => array(
//					'InvItemsSupplier.inv_supplier_id'=>$id
//				),
//				'fields' => array('InvItemsSupplier.id', 'InvItemsSupplier.id')
//			));
////			print_r($itemsSuppliersIds);
////			die();
//			foreach ($itemsSuppliersIds as $itemsSuppliersId) {
//				try {
//					$this->InvSupplier->InvItemsSupplier->id = $itemsSuppliersId;	
//					$this->InvSupplier->InvItemsSupplier->delete();
//				} catch (Exception $e) {	
////					print_r('catch');
////					die();
//						$this->Session->setFlash(
//							__('No se puede eliminar el Proveedor porque tiene productos ligados'),
//							'alert',
//							array(
//								'plugin' => 'TwitterBootstrap',
//								'class' => 'alert-error'
//							)
//						);
//						$this->redirect(array('action' => 'index'));
//				}
//			}
//			/////////////////////////////////////////////////////////////////////////
//			$purDetailsIds = $this->InvSupplier->PurDetail->find('list', array(
//				'conditions' => array(
//					'PurDetail.inv_supplier_id'=>$id
//				),
//				'fields' => array('PurDetail.id', 'PurDetail.id')
//			));
//			foreach ($purDetailsIds as $purDetailsId) {
//				try {
//					$this->InvSupplier->InvSupplierContact->id = $purDetailsId;	
//					$this->InvSupplier->InvSupplierContact->delete();
//				} catch (Exception $e) {	
////					print_r('catch');
////					die();
//						$this->Session->setFlash(
//							__('No se puede eliminar el Cliente porque tiene notas de compra ligadas'),
//							'alert',
//							array(
//								'plugin' => 'TwitterBootstrap',
//								'class' => 'alert-error'
//							)
//						);
//						$this->redirect(array('action' => 'index'));
//				}
//			}
//		}		
		
	
		
	}
	
	public function vsave(){
		
//		debug($this->passedArgs);
		$idSupplier = '';
		$supplier[0]['InvSupplier']['name'] = '';
		$supplier[0]['InvSupplier']['address'] = '';
		$supplier[0]['InvSupplier']['area'] = '';
		$supplier[0]['InvSupplier']['location'] = '';
		$supplier[0]['InvSupplier']['country'] = '';
		$supplier[0]['InvSupplier']['phone'] = '';
		$supplier[0]['InvSupplier']['email'] = '';
		$supplier[0]['InvSupplier']['website'] = '';
		if(isset($this->passedArgs['id'])){
			$idSupplier = $this->passedArgs['id'];
			
			$supplier = $this->InvSupplier->find('all', array(
				'conditions' => array(
					'InvSupplier.id' => $idSupplier
				),
				'fields'=>array('InvSupplier.name', 'InvSupplier.phone', 'InvSupplier.address', 'InvSupplier.email', 'InvSupplier.website', 'InvSupplier.country', 'InvSupplier.area', 'InvSupplier.location'),
				'recursive' => -1
			));
			
//			$taxNumbers = $this->SalCustomer->SalTaxNumber->find('all', array(
//				"conditions" => array(
//					'SalTaxNumber.sal_customer_id' => $idCostumer
//				),
//				'fields'=>array('SalTaxNumber.id', 'SalTaxNumber.name', 'SalTaxNumber.nit'),
//				'recursive' => -1,
//				'order' => array('SalTaxNumber.id' => 'asc')
//				, 'limit' => 1
//			));	
		}	
		
		$contacts = $this->InvSupplier->InvSupplierContact->find('all', array(
			"conditions"=>array(
				'InvSupplierContact.inv_supplier_id'=>$idSupplier
			),
			'fields'=>array('InvSupplierContact.id', 'InvSupplierContact.name', 'InvSupplierContact.job_title', 'InvSupplierContact.phone', 'InvSupplierContact.email'),
			'recursive' => -1,
			'order'=>array('InvSupplierContact.id'=>'asc')
//			, 'limit' => 1
		));

//		debug($customer);
//		debug($employees);
//		debug($taxNumbers);
		
		$this->set(compact('idSupplier', 'supplier', 'contacts'));
		
	}
	
	public function ajax_save_supplier(){
		if($this->RequestHandler->isAjax()){
			$data = array();
			if(isset($this->request->data['id']) && $this->request->data['id'] <> ""){
				$action = "edit";
				$data["InvSupplier"]["id"] = $this->request->data['id'];//modify
			}else{
				$action = "add";
				$this->InvSupplier->create();
			}
			$data["InvSupplier"]["name"] = $this->request->data['name'];
//			$data["SalEmployee"]["name"] = $this->request->data['employeeName'];
//			$data["SalTaxNumber"]["nit"] = $this->request->data['nit'];
			$data["InvSupplier"]["address"] = $this->request->data['address'];
			$data["InvSupplier"]["phone"] = $this->request->data['phone'];
			$data["InvSupplier"]["email"] = $this->request->data['email'];
			$data["InvSupplier"]["website"] = $this->request->data['website'];
			$data["InvSupplier"]["country"] = $this->request->data['country'];
			$data["InvSupplier"]["area"] = $this->request->data['area'];
			$data["InvSupplier"]["location"] = $this->request->data['location'];
			//debug($data);
			
			if(isset($this->request->data['id']) && $this->request->data['id'] <> ""){//EDIT
				if($this->InvSupplier->save($data)){
//					$invSupplierId = $this->InvSupplier->id;
						
//					$this->SalCustomer->SalTaxNumber->create();
//					$data["SalTaxNumber"]["id"] = $this->request->data['nitId'];
//					$data["SalTaxNumber"]["sal_customer_id"] = $salCustomerId;
//					if($this->request->data['nitName'] == ''){
//						$data["SalTaxNumber"]["name"] = 'N/A';
//					}else{
//						$data["SalTaxNumber"]["name"] = $this->request->data['nitName'];
//					}
//					if($this->request->data['nit'] == ''){
//						$data["SalTaxNumber"]["nit"] = 'N/A';
//					}else{
//						$data["SalTaxNumber"]["nit"] = $this->request->data['nit'];
//					}
////					$data["SalTaxNumber"]["name"] = 'N/a';
//					$this->SalCustomer->SalTaxNumber->save($data);
					
					echo "success|".$action."|".$this->InvSupplier->id;//."|".$this->InvSupplier->InvSupplierContact->id;
				}
			}else{//ADD
//				print_r($data);
//				die();
				if($this->InvSupplier->save($data)){
					$invSupplierId = $this->InvSupplier->id;
			
//					$this->InvSupplier->InvSupplierContact->create();
//					$data["InvSupplierContact"]["sal_customer_id"] = $invSupplierId;
//					$data["InvSupplierContact"]["name"] = 'Contacto';//$this->request->data['employeeName'];
//					$data["InvSupplierContact"]["phone"] = '';
//					$data["InvSupplierContact"]["email"] = '';
//					$this->InvSupplier->SalEmployee->save($data);
					
					echo "success|".$action."|".$invSupplierId;//."|".$this->InvSupplier->InvSupplierContact->id;
				}
			}
		}
	}
	
	public function ajax_save_contact(){
		if($this->RequestHandler->isAjax()){
			$data = array();
			$action = "add";
			if(isset($this->request->data['id']) && $this->request->data['id'] <> ""){
				$data["InvSupplierContact"]["id"] = $this->request->data['id'];
				$action = "edit";
			}else{
				$this->InvSupplier->InvSupplierContact->create();
			}
			$data["InvSupplierContact"]["inv_supplier_id"] = $this->request->data['idSupplier'];
			$data["InvSupplierContact"]["name"] = $this->request->data['name'];
			$data["InvSupplierContact"]["job_title"] = $this->request->data['title'];
			$data["InvSupplierContact"]["phone"] = $this->request->data['phone'];
			$data["InvSupplierContact"]["email"] = $this->request->data['email'];
			
			//debug($data);
			
			if($this->InvSupplier->InvSupplierContact->save($data)){
				echo "success|".$this->InvSupplier->InvSupplierContact->id."|".$action;
			}
		}
	}
	
	public function ajax_delete_contact(){
		if($this->RequestHandler->isAjax()){
			$id = $this->request->data['id'];
//			$contacts = $this->InvSupplier->InvSupplierContact->find('count', array(
//				'conditions'=>array('InvSupplierContact.inv_supplier_id'=>$id)
//			));
//			$children = $this->InvSupplier->SalEmployee->SalSale->find("count", array("conditions"=>array("SalSale.sal_employee_id"=>$id)));
//			if($employees > 1){
//				if($children == 0){
					$this->InvSupplier->InvSupplierContact->id = $id;
					if($this->InvSupplier->InvSupplierContact->delete()){
						echo "success";
					}else{
						echo "error";
					}
//				}else{
//					echo "children";
//				}
//			}else{
//				echo "headless";
//			}	
		}
	}
	
}
