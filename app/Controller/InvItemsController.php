<?php
App::uses('AppController', 'Controller');
/**
 * InvItems Controller
 *
 * @property InvItem $InvItem
 */
class InvItemsController extends AppController {

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
	//////////////////////////////////////////// START - PDF ///////////////////////////////////////////////
	public function view_prices_list_pdf($valueBrand, $valuePrice) {
//debug($valueBrand);
//debug($valuePrice);
//die();
		
//        $this->InvMovement->id = $id;
//
//        if (!$this->InvMovement->exists()) {
//            throw new NotFoundException(__('Invalid post'));
//        }
        // increase memory limit in PHP 
        ini_set('memory_limit', '512M');
//        $movement = $this->InvMovement->read(null, $id);
//
//        if ($movement['InvMovement']['inv_movement_type_id'] == 4) {
//            $this->redirect(array('action' => 'index_warehouses_transfer'));
//        }
//
//        if ($movement['InvMovement']['inv_movement_type_id'] == 3) {
//
//            $movementIdOut = $this->InvMovement->find('all', array(
//                'conditions' => array(
//                    'InvMovement.document_code' => $movement['InvMovement']['document_code'],
//                    'InvMovement.inv_movement_type_id =' => 4
//            ))); //Out Origin
//            $movement['Transfer']['code'] = $movementIdOut[0]['InvMovement']['code'];
//            $movement['Transfer']['warehouseName'] = $movementIdOut[0]['InvWarehouse']['name'];
//        }
		$filters = array();
		
		if($valueBrand > 0){
			$filters['InvItem.inv_brand_id'] = $valueBrand;
		}
		$this->InvItem->unbindModel(array('hasMany' => array('InvMovementDetail','InvItemsSupplier','InvPrice')));
//		$this->InvItem->bindModel(array(
//			'hasOne'=>array(
//				'InvPrice'=>array(
//					'foreignKey'=>false,
//					'conditions'=> array('InvItem.id = InvPrice.inv_item_id')
//				)
//				
//			)
//		));
		$itemsByBrand = $this->InvItem->find('all', array(
			'fields' => array('InvItem.id', 'InvItem.code', 'InvItem.name'),
			'conditions' => $filters,
			'recursive' => 1,
			'order' => array('InvItem.code' => 'asc')
//			,'group'=>array('InvItem.id','InvItem.code')
		));
//		debug($itemsByBrand);
//		die();
		
		$items = array();
		foreach($itemsByBrand as $val){
			$items[$val['InvItem']['id']] = $val['InvItem']['id'];
		}
//		debug($items);
//		die();
//		$valuePrice = 9;
		$currentAbbr = "";
//		if(isset($this->passedArgs['priceType'])){
			if($valuePrice == 1){
				$currentAbbr = "ex_";
//				$valuePrice = 1;
			}else if($valuePrice == 8){
				$currentAbbr = "";
//				$valuePrice = 8;
			}else if($valuePrice == 9){
				$currentAbbr = "";
			}
//		}
//		debug($valuePrice);
		foreach($items as $item){
			$prices[] = $this->InvItem->InvPrice->find('first', array(
				'fields' => array(
//					'InvPrice.inv_item_id',
					'"InvPrice"."'.$currentAbbr.'price"',
					'InvPrice.id'
				   ),
				'conditions' => array(
					'InvPrice.inv_item_id' =>$item,
					'InvPrice.inv_price_type_id' => $valuePrice,//$this->passedArgs['priceType'] == 1,
				),
	            'order' => array('InvPrice.date' => 'desc'),
				'limit' => 1
			));
		}
		
		
		
		foreach($itemsByBrand as $key => $val){
			if($prices[$key] != array()){
				$itemsByBrand[$key]['InvItem']['lastPrice'] = $prices[$key]['InvPrice'][$currentAbbr.'price'];
				$itemsByBrand[$key]['InvItem']['priceId'] = $prices[$key]['InvPrice']['id'];
			}else{
				$itemsByBrand[$key]['InvItem']['lastPrice'] = 'n/a';
				$itemsByBrand[$key]['InvItem']['priceId'] = '';
			}	
		}
		
		switch ($valuePrice){
			case 1: 
				$valuePrice = 'FOB';
				break;
			case 8:
				$valuePrice = 'CIF';
				break;
			case 9:
				$valuePrice = 'VENTA';
				break;
		}
		if($valueBrand == 0){
			$valueBrand = 'TODAS';
		}else{
			$valueBrand = current($this->InvItem->InvBrand->find('list',array(
				'conditions' => array('InvBrand.id' => $valueBrand)
			)));
		}
//		debug($itemsByBrand);
//		die();

//        $details = $this->_get_movements_details_without_stock($id);
//        $this->set('movement', $movement);
//        $this->set('details', $details);
		$this->set('valueBrand', $valueBrand);
		$this->set('valuePrice', $valuePrice);
		$this->set('itemsByBrand', $itemsByBrand);
    }
	//////////////////////////////////////////// END - PDF /////////////////////////////////////////////////
	public function index() {
		$filters = array();
		$code = '';
		$name = '';
		////////////////////////////START - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
		
		if($this->request->is("post")) {
			
			$url = array('action'=>'index');
			$parameters = array();
//			$empty=0;
			//aca creamos la variable $parameters que contendra los parametros a ser pasados mediante url ej /code:1014
			if(isset($this->request->data['InvItem']['code']) && $this->request->data['InvItem']['code']){
				$parameters['code'] = trim(strip_tags($this->request->data['InvItem']['code']));
			}else{
//				$empty++;
			}

			if(isset($this->request->data['InvItem']['name']) && $this->request->data['InvItem']['name']){
				$parameters['name'] = trim(strip_tags($this->request->data['InvItem']['name']));
			}else{
//				$empty++;
			}
			
//			if(isset($this->request->data['InvItem']['stock']) && $this->request->data['InvItem']['stock']){
			//aca creamos la variable $parameters que contendra los parametros a ser pasados mediante url ej /:
				$parameters['stock'] = $this->request->data['InvItem']['stock']; //this case is different (witout validation because is select and always has a value)
//			}else{
//				$empty++;
//			}

//			if($empty == 2){
//				$parameters['search']='empty';
//			}else{
//				$parameters['search']='yes';
//			}
			//esta funcion redirect() es la q se encarga de redirigir la pagina a la misma pero con los parametros adheridos
			$this->redirect(array_merge($url,$parameters));
		}
		////////////////////////////END - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
		
		////////////////////////////START - SETTING URL FILTERS//////////////////////////////////////
//		debug($this->passedArgs);
		//$this->passedArgs contiene los parametros pasados mediante url y aca se usan para crear un filtro
		if(isset($this->passedArgs['code'])){
			$filters['InvItem.code LIKE'] = '%'.strtoupper($this->passedArgs['code']).'%';
			$code = $this->passedArgs['code'];
		}

		if(isset($this->passedArgs['name'])){
			$filters['InvItem.name LIKE'] = '%'.strtoupper($this->passedArgs['name']).'%';
			$name = $this->passedArgs['name'];
		}
		////////////////////////////END - SETTING URL FILTERS//////////////////////////////////////
		
		////////////////////////////START - SETTING PAGINATING VARIABLES//////////////////////////////////////	
		//used to limit and order the pagination
		$this->paginate = array(
			'conditions' => array($filters),
			'order' => array('InvItem.code' => 'asc'),
			'limit' => 15
		);
		////////////////////////////END - SETTING PAGINATING VARIABLES//////////////////////////////////////
		$this->InvItem->recursive = 0;
		
		////////////////////////START - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
		
		/////////////////Start - Stocks
		$pagination = $this->paginate('InvItem');
		//XXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXxx
		//se crea la variable $items que contiene los ids de los items desplegados (ya filtrados) ej. (int) 307 => (int) 307,
		$items = array();
		foreach($pagination as $val){
			$items[$val['InvItem']['id']] = $val['InvItem']['id'];
		}
		
		$valueWarehouse = 0;
		//si esq se pasa la variable stock por el url...
		if(isset($this->passedArgs['stock'])){
			if($this->passedArgs['stock'] == 0){//TODOS
				$stocks = $this->_get_stocks($items);//saca los stocks de todos los items de la lista $items
			}else{
				$stocks = $this->_get_stocks($items, $this->passedArgs['stock']);//saca los stocks de la lista $items pero solo de un almacen $this->passedArgs['stock']
				$valueWarehouse = $this->passedArgs['stock'];
			}
		}else{
			$stocks = $this->_get_stocks($items);
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
		//debug($stocks);
		//adhiere cada stock de cada item a cada item
		foreach($pagination as $key => $val){
				$pagination[$key]['InvItem']['stock'] = $this->_find_item_stock($stocks, $val['InvItem']['id']);
				//debug( $this->_find_item_stock($stocks, $val['InvItem']['id']));
		}
		
		$this->loadModel("InvWarehouse");
		$warehouses = $this->InvWarehouse->find("list");
		$warehouses[0] = "Todos los almacenes";
		//debug($pagination);
		////////////////End - Stocks		
//				debug($warehouses);
		$this->set("valueWarehouse", $valueWarehouse);
		$this->set("warehouses",$warehouses);		
		$this->set('invItems', $pagination);
		$this->set('name', $name);
		$this->set('code', $code);
		////////////////////////END - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
	}

	
	
	
	
/**
 * view method
 *
 * @param string $id
 * @return void
 */
	public function view($id = null) {
		$this->InvItem->id = $id;
		if (!$this->InvItem->exists()) {
			throw new NotFoundException(__('Invalid %s', __('inv item')));
		}
		$this->set('invItem', $this->InvItem->read(null, $id));
	}
	
/**
 * save_item method
 *
 * @return void
 */	
	public function save_item($id = null){
		$id = '';
		if(isset($this->passedArgs['id'])){
			$id = $this->passedArgs['id'];
		}
		
		$invSuppliers = $this->InvItem->InvItemsSupplier->InvSupplier->find('list', array('order' => 'InvSupplier.name'));
		if(count($invSuppliers) == 0){
			$invSuppliers[""] = '--- Vacio ---';
		}else{
			$invSuppliers = array(0 => '-- Seleccione un Proveedor --') + $invSuppliers;
		}
		
		$invBrands = $this->InvItem->InvBrand->find('list', array('order' => 'InvBrand.name'));
		if(count($invBrands) == 0){
			$invBrands[""] = '--- Vacio ---';
		}else{
			$invBrands = array(0 => '-- Seleccione una Marca --') + $invBrands;
		}
		
		$invCategories = $this->InvItem->InvCategory->find('list', array('order' => 'InvCategory.name'));
		if(count($invCategories) == 0){
			$invCategories[""] = '--- Vacio ---';
		}else{
			$invCategories = array(0 => '-- Seleccione una Categoria --') + $invCategories;
		}	
		$invPrices = array();
		
		$this->InvItem->recursive = -1;
		$this->request->data = $this->InvItem->read(null,$id);		
		$supplier = 1;
		if($id <> null){
			$invPrices = $this->_get_prices($id);
			$supplier = $this->InvItem->InvItemsSupplier->find('first', array(
				'conditions'=>array(
					'InvItemsSupplier.inv_item_id'=>$id
				)
			));
//			print_r($supplier);
//			die();
		}
		$this->set(compact('id','invBrands', 'invCategories', 'invPrices', 'invSuppliers', 'supplier'));	
	}

	/**
 * add method
 *
 * @return void
 */
	public function add() {
		
		//Section where the controls of the page are loaded		
		$invSuppliers = $this->InvItem->InvItemsSupplier->InvSupplier->find('list', array('order' => 'InvSupplier.name'));
		if(count($invSuppliers) == 0)
		{
			$invSuppliers[""] = '--- Vacio ---';
		}else{
			$invSuppliers = array(0 => '-- Seleccione un Proveedor --') + $invSuppliers;
		}
		$invBrands = $this->InvItem->InvBrand->find('list', array('order' => 'InvBrand.name'));
		if(count($invBrands) == 0)
		{
			$invBrands[""] = '--- Vacio ---';
		}else{
			$invBrands = array(0 => '-- Seleccione una Marca --') + $invBrands;
		}
		
		$invCategories = $this->InvItem->InvCategory->find('list', array('order' => 'InvCategory.name'));
		if(count($invCategories) == 0)
		{
			$invCategories[""] = '--- Vacio ---';
		}else{
			$invCategories = array(0 => '-- Seleccione una Categoria --') + $invCategories;
		}
		
		$this->set(compact('invBrands', 'invCategories', 'invSuppliers'));	
		
		
		//Section where information is saved into the database
		if ($this->request->is('post')) {			
			
			$data = $this->request->data;
//			debug($data);
			
			$this->loadModel("AdmExchangeRate");
			$existexRate = $this->AdmExchangeRate->find("first", array(
				"conditions"=>array("AdmExchangeRate.date <="=>$data["InvPrice"][0]["date"]),
				"fields"=>array("AdmExchangeRate.value")
			));
			$exRate = 0;
//			debug($existexRate);
			$error=0;
			if(count($existexRate) == 0){
				$this->Session->setFlash('No hay un "Tipo de Cambio" registrado para la fecha elegida','alert',array('plugin' => 'TwitterBootstrap','class' => 'alert-error'));
				//$this->redirect(array('action' => 'add'));
				$error++;
				
			}else{
				$exRate = $existexRate["AdmExchangeRate"]["value"];
			}
			
			
			
			if($error == 0){
				$data["InvPrice"][0]["ex_rate"] = $exRate;
				$data["InvPrice"][0]["inv_price_type_id"] = 1;
				$data["InvPrice"][0]["description"] = "Precio FOB Inicial";
				$data["InvPrice"][0]["location"] = "La Paz";
				$data["InvPrice"][0]["code"] = "NO";
				if($data["Aux"]["priceType"] == "BOLIVIANOS"){
					$data["InvPrice"][0]["price"] = $data["Aux"]["neutralPrice"];
					$data["InvPrice"][0]["ex_price"] = $data["Aux"]["neutralPrice"] / $exRate;
				}elseif($data["Aux"]["priceType"] == "DOLARES"){
					$data["InvPrice"][0]["price"] = $data["Aux"]["neutralPrice"] * $exRate;
					$data["InvPrice"][0]["ex_price"] = $data["Aux"]["neutralPrice"];
				}
				
				//converting to UPPER for code
				$data["InvItem"]["code"] = strtoupper($data["InvItem"]["code"]);
			
	//			debug($data);
				//$data["InvItemsSupplier"]
				$this->InvItem->create();			

				if ($this->InvItem->saveAll($data, array("deep"=>true))) {
					$this->Session->setFlash(
						__('El Producto se guardo satisfactoriamente'),
						'alert',
						array(
							'plugin' => 'TwitterBootstrap',
							'class' => 'alert-success'
						)
					);
					$this->redirect(array('action' => 'add'));
				} else {
					$this->Session->setFlash(
						__('El Producto no se pudo guardar, por favor intente de nuevo'),
						'alert',
						array(
							'plugin' => 'TwitterBootstrap',
							'class' => 'alert-error'
						)
					);
				}
			}//end if error
		}
		
		
	}	
	/**
 * edit method
 *
 * @param string $id
 * @return void
 */
	public function edit($id = null) {
		//Section where the controls of the page are loaded		
		$invBrands = $this->InvItem->InvBrand->find('list', array('order' => 'InvBrand.name'));
		if(count($invBrands) == 0)
		{
			$invBrands[""] = '--- Vacio ---';
		}
		
		$invCategories = $this->InvItem->InvCategory->find('list', array('order' => 'InvCategory.name'));
		if(count($invCategories) == 0)
		{
			$invCategories[""] = '--- Vacio ---';
		}		
		$this->set(compact('invBrands', 'invCategories'));	
		$this->InvItem->id = $id;
		if (!$this->InvItem->exists()) {
			throw new NotFoundException(__('Invalid %s', __('inv item')));
		}
		//$this->_view_Prices(1);
		if ($this->request->is('post') || $this->request->is('put')) {
			$this->request->data['InvItem']['lc_transaction']='MODIFY';
			if ($this->InvItem->save($this->request->data)) {
				$this->Session->setFlash(
					__('El item fue modificado'),
					'alert',
					array(
						'plugin' => 'TwitterBootstrap',
						'class' => 'alert-success'
					)
				);
				$this->redirect(array('action' => 'index'));
			} else {
				$this->Session->setFlash(
					__('The %s could not be saved. Please, try again.', __('inv item')),
					'alert',
					array(
						'plugin' => 'TwitterBootstrap',
						'class' => 'alert-error'
					)
				);
			}
		} else {
			$this->request->data = $this->InvItem->read(null, $id);
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
		$this->InvItem->id = $id;
		if (!$this->InvItem->exists()) {
			throw new NotFoundException(__('Invalid %s', __('inv item')));
		}
		try {
			$this->InvItem->delete();
		} catch (Exception $e) {
			$this->Session->setFlash(
				__('El Producto no se pudo Eliminado'),
				'alert',
				array(
					'plugin' => 'TwitterBootstrap',
					'class' => 'alert-error'
				)
			);
			$this->redirect(array('action' => 'index'));
		}
		$this->Session->setFlash(
			__('El Producto fue Eliminado'),
			'alert',
			array(
				'plugin' => 'TwitterBootstrap',
				'class' => 'alert-success'
			)
		);
		$this->redirect(array('action' => 'index'));
	}
		
	public function price_list() {
		$filters = array();
		$code = '';
		////////////////////////////START - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
		
		if($this->request->is("post")) {
			
			$url = array('action'=>'price_list');
			$parameters = array();
			if(isset($this->request->data['InvItem']['code']) && $this->request->data['InvItem']['code']){
				$parameters['code'] = trim(strip_tags($this->request->data['InvItem']['code']));
			}
			
			$parameters['brand'] = $this->request->data['InvItem']['brand'];

			$parameters['priceType'] = $this->request->data['InvItem']['price_type'];
				
			$this->redirect(array_merge($url,$parameters));
		}
		////////////////////////////END - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
		
		////////////////////////////START - SETTING URL FILTERS//////////////////////////////////////
//		$this->InvItem->bindModel(array('hasOne' => array('InvPrice' => array('foreignKey' => false, 'conditions' => array('InvItem.id = InvPrice.inv_item_id'), 'order' => array('InvPrice.date' => 'desc'), 'limit' => 1))));
//		debug($this->passedArgs);
		if(isset($this->passedArgs['code'])){
			$filters['InvItem.code LIKE'] = '%'.strtoupper($this->passedArgs['code']).'%';
			$code = $this->passedArgs['code'];
		}		
		$valueBrand = 0;
		if(isset($this->passedArgs['brand'])){
			if($this->passedArgs['brand'] > 0){
				$filters['InvItem.inv_brand_id'] = strtoupper($this->passedArgs['brand']);
				$valueBrand = $this->passedArgs['brand'];
			}
		}		
		////////////////////////////END - SETTING URL FILTERS//////////////////////////////////////
		
		////////////////////////////START - SETTING PAGINATING VARIABLES//////////////////////////////////////	
		$this->paginate = array(
			'conditions' => array($filters),
			'order' => array('InvItem.code' => 'asc'),
			'limit' => 15
		);
		////////////////////////////END - SETTING PAGINATING VARIABLES//////////////////////////////////////
		$this->InvItem->recursive = 0;
		
		////////////////////////START - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
		
		/////////////////Start - Stocks
		$pagination = $this->paginate('InvItem');
//		debug($pagination);
//		die();
		$items = array();
		foreach($pagination as $val){
			$items[$val['InvItem']['id']] = $val['InvItem']['id'];
		}
//		debug($items);
//		die();
		$valuePrice = 9;
		$currentAbbr = "";
		if(isset($this->passedArgs['priceType'])){
			if($this->passedArgs['priceType'] == 1){
				$currentAbbr = "ex_";
				$valuePrice = 1;
			}else if($this->passedArgs['priceType'] == 8){
				$currentAbbr = "";
				$valuePrice = 8;
			}else if($this->passedArgs['priceType'] == 9){
				$currentAbbr = "";
			}
		}
//		else{
////			$price = 'v';
//		}
		
		foreach($items as $item){
			$prices[] = $this->InvItem->InvPrice->find('first', array(
				'fields' => array(
//					'InvPrice.inv_item_id',
					'"InvPrice"."'.$currentAbbr.'price"',
					'InvPrice.date',
					'InvPrice.id'
				   ),
				'conditions' => array(
					'InvPrice.inv_item_id' =>$item,
					'InvPrice.inv_price_type_id' => $valuePrice,//$this->passedArgs['priceType'] == 1,
				),
	            'order' => array('InvPrice.date' => 'desc'),
				'limit' => 1
			));
		}

		foreach($pagination as $key => $val){
			if($prices[$key] != array()){
				$pagination[$key]['InvItem']['lastPrice'] = $prices[$key]['InvPrice'][$currentAbbr.'price'];
				$pagination[$key]['InvItem']['priceId'] = $prices[$key]['InvPrice']['id'];
				$pagination[$key]['InvItem']['date'] = $prices[$key]['InvPrice']['date'];
			}else{
				$pagination[$key]['InvItem']['lastPrice'] = 'n/a';
				$pagination[$key]['InvItem']['priceId'] = '';
				$pagination[$key]['InvItem']['date'] = 'n/a';
			}	
		}
		
		$this->loadModel("InvWarehouse");
		$warehouses = $this->InvWarehouse->find("list");
		$warehouses[0] = "Todos los almacenes";
		
		$this->loadModel("InvBrand");
		$brands = $this->InvBrand->find("list");
		$brands[0] = "Todas las marcas";
//		debug($pagination);
		////////////////End - Stocks		
//				debug($warehouses);
		$this->set("valueBrand", $valueBrand);
		$this->set("valuePrice", $valuePrice);
		$this->set("warehouses",$warehouses);	
		$this->set("brands",$brands);	
		$this->set('invItems', $pagination);
		$this->set('code', $code);
//		$this->set('priceType', $priceType);
		////////////////////////END - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
	}
	
		public function stock() {
		$filters = array();
		$code = '';
		////////////////////////////START - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
		
		if($this->request->is("post")) {
			
			$url = array('action'=>'stock');
			$parameters = array();
			if(isset($this->request->data['InvItem']['code']) && $this->request->data['InvItem']['code']){
				$parameters['code'] = trim(strip_tags($this->request->data['InvItem']['code']));
			}
			
			$parameters['stock_loc'] = $this->request->data['InvItem']['stock_loc']; //this case is different (witout validation because is select and always has a value)
			
//			$parameters['stock_war'] = $this->request->data['InvItem']['stock_war']; //this case is different (witout validation because is select and always has a value)
			
			$this->redirect(array_merge($url,$parameters));
		}
		////////////////////////////END - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
		
		////////////////////////////START - SETTING URL FILTERS//////////////////////////////////////
//		debug($this->passedArgs);
		if(isset($this->passedArgs['code'])){
			$filters['InvItem.code LIKE'] = '%'.strtoupper($this->passedArgs['code']).'%';
			$code = $this->passedArgs['code'];
		}		
		////////////////////////////END - SETTING URL FILTERS//////////////////////////////////////
		
		////////////////////////////START - SETTING PAGINATING VARIABLES//////////////////////////////////////	
		
		$this->paginate = array(
			'conditions' => array($filters),
			'order' => array('InvItem.code' => 'asc'),
			'limit' => 15
		);
		////////////////////////////END - SETTING PAGINATING VARIABLES//////////////////////////////////////
		$this->InvItem->recursive = 0;
		
		////////////////////////START - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
		
		/////////////////Start - Stocks
		$pagination = $this->paginate('InvItem');
		$items = array();
		foreach($pagination as $val){
			$items[$val['InvItem']['id']] = $val['InvItem']['id'];
		}
		
//		$valueWarehouse = 0;
		$valueLocation = 'todas';
		if(isset($this->passedArgs['stock_loc'])/* AND isset($this->passedArgs['stock_war'])*/){
			if(is_numeric($this->passedArgs['stock_loc'])){
				$stocks = $this->_get_stocks($items, $this->passedArgs['stock_loc']);
				$valueLocation = $this->passedArgs['stock_loc'];
			}else if($this->passedArgs['stock_loc'] == 'todas'){
				$stocks = $this->_get_stocks($items);
			}else{
				$this->loadModel("InvWarehouse");
				$warehouseIds = $this->InvWarehouse->find("list", array(
					'fields'=>array('InvWarehouse.id')
					,'conditions' => array('InvWarehouse.location' => $this->passedArgs['stock_loc'])
				));
				$stocks = $this->_get_stocks($items, $warehouseIds);
				$valueLocation = $this->passedArgs['stock_loc'];
			}
			
			
//			if($this->passedArgs['stock_loc'] == 'todas'/* AND $this->passedArgs['stock_war'] == 0*/){
//				$stocks = $this->_get_stocks($items);
//			}elseif($this->passedArgs['stock_loc'] != 'todas' AND $this->passedArgs['stock_war'] == 0){
//				$this->loadModel("InvWarehouse");
//				$warehouseIds = $this->InvWarehouse->find("list", array(
//					'fields'=>array('InvWarehouse.id')
//					,'conditions' => array('InvWarehouse.location' => $this->passedArgs['stock_loc'])
//				));
////				debug($warehouseIds);
//				$stocks = $this->_get_stocks($items, $warehouseIds);
//				$valueLocation = $this->passedArgs['stock_loc'];
//			}else
////				if($this->passedArgs['stock_loc'] != 'todas' AND $this->passedArgs['stock_war'] != 0)
//				 {
//				$stocks = $this->_get_stocks($items, $this->passedArgs['stock_war']);
////				$valueWarehouse = $this->passedArgs['stock_war'];
//				$valueLocation = $this->passedArgs['stock_loc'];
//			}
		}else{
			$stocks = $this->_get_stocks($items);
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
		//debug($stocks);
		foreach($pagination as $key => $val){
				$pagination[$key]['InvItem']['stock'] = $this->_find_item_stock($stocks, $val['InvItem']['id']);
				//debug( $this->_find_item_stock($stocks, $val['InvItem']['id']));
		}
		
		
		
		$this->loadModel("InvWarehouse");
		$locations = $this->InvWarehouse->find("all", array(
			'fields'=>array('DISTINCT InvWarehouse.location')
			,'recursive' => -1
		));
		$locationList['todas'] = "Todas las Regiones";
		foreach ($locations as $location) {
//			$locationList[strtolower(str_replace(' ', '', $location['InvWarehouse']['location']))] = $location['InvWarehouse']['location'];
			$locationList[$location['InvWarehouse']['location']] = $location['InvWarehouse']['location'];
			
			$warehouses = $this->InvWarehouse->find('list',array('conditions' => array('InvWarehouse.location' => $location['InvWarehouse']['location'])));
			foreach ($warehouses as $key => $warehouse) {
				$locationList[$key] = '----> '.$warehouse;
			}
		}
//		debug($locationList);
//		die();
//		$warehouses = $this->InvWarehouse->find("list");
//		debug($locationList);
//		$warehouses = array(
//   'Group 1' => array(
//      'Value 1' => 'Label 1',
//      'Value 2' => 'Label 2'
//   ),
//   'Group 2' => array(
//      'Value 3' => 'Label 3'
//   )
//); 
//		$warehouses[0] = "Todos los almacenes";
		//debug($pagination);
		////////////////End - Stocks		
//				debug($warehouses);
//		$this->set("valueWarehouse", $valueWarehouse);
//		$this->set("warehouses",$warehouses);
		$this->set("valueLocation", $valueLocation);
		$this->set("locationList",$locationList);
		$this->set('invItems', $pagination);
		$this->set('code', $code);
		////////////////////////END - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
	}
	//////////////////////////////////////////// START - AJAX ///////////////////////////////////////////////
	
	public function ajax_initiate_modal_add_price(){
		if($this->RequestHandler->isAjax()){
						
//			$pricesAlreadySaved = $this->request->data['pricesAlreadySaved'];
			
//			$warehouse = $this->request->data['warehouse']; //if it's warehouse_transfer is OUT
//			$warehouse2 = $this->request->data['warehouse2'];//if it's warehouse_transfer is IN
//			$transfer = $this->request->data['transfer'];
			
			$invPriceTypes = $this->InvItem->InvPrice->InvPriceType->find('list', array(
				//'recursive'=>-1,
				//'fields'=>array('InvItem.id', 'CONCAT(InvItem.code, '-', InvItem.name)')
				//"order"=>array("InvPriceType.name"),
				"conditions"=>array("InvPriceType.name"=>array("FOB", "CIF", "VENTA"))
			));	
			
			$this->set(compact('invPriceTypes'));
		}
	}
	
	public function ajax_initiate_modal_edit_list_price(){
		if($this->RequestHandler->isAjax()){
			
			$itemId = $this->request->data['itemIdForEditPrice'];
			$priceType = $this->request->data['priceType'];
			if($priceType > 1){$currentAbbr='';}else{$currentAbbr='ex_';}
			$price = $this->InvItem->InvPrice->find('first', array(
				'fields' => array(
//					'InvPrice.inv_item_id',
					'"InvPrice"."'.$currentAbbr.'price"',
					'InvPrice.date',
					'InvPrice.description',
					'InvPrice.id'
				   ),
				'conditions' => array(
					'InvPrice.inv_item_id' =>$itemId,
					'InvPrice.inv_price_type_id' => $priceType,
				),
	            'order' => array('InvPrice.date' => 'desc'),
				'limit' => 1
			));
//			$price['InvPrice'][$currentAbbr.'price']
//			print_r($price['InvPrice'][$currentAbbr.'price']);
		
			$date = date("d/m/Y", strtotime($price['InvPrice']['date']));
			$description = $price['InvPrice']['description'];//.'|'.$price['InvPrice'][$currentAbbr.'price'];		//get rid of the price here
			
			$this->set(compact('date','description'));
		}
	}
	
	public function ajax_save_price(){
		if($this->RequestHandler->isAjax()){
			
			$itemId = $this->request->data['itemId'];
			$priceTypeId = $this->request->data['priceTypeId'];		
			$priceDate = $this->request->data['priceDate'];
			$priceAmount = $this->request->data['priceAmount'];			
			$priceDescription = $this->request->data['priceDescription'];			
			$currencyType = $this->request->data['currencyType'];			
			
			
			$this->loadModel("AdmExchangeRate");
			$existexRate = $this->AdmExchangeRate->find("first", array(
				"conditions"=>array("AdmExchangeRate.date <="=>$priceDate),
				"fields"=>array("AdmExchangeRate.value")
			));
			
			
			
			
			
			if(count($existexRate) > 0){
				$exRate = $existexRate["AdmExchangeRate"]["value"];
				
				if($currencyType == "BOLIVIANOS"){
					$price = $priceAmount;
					$exPrice = $priceAmount / $exRate;

				}elseif($currencyType == "DOLARES"){
					$price = $priceAmount * $exRate;
					$exPrice = $priceAmount;
				}
				
				
				$arrayPrice = array('inv_item_id'=>$itemId, 'inv_price_type_id'=>$priceTypeId, 'date'=> $priceDate, 'price'=>$price, 'ex_price'=>$exPrice, 'ex_rate'=>$exRate, 'description'=>$priceDescription);
				$data = array('InvPrice'=>$arrayPrice);

				if($this->InvItem->InvPrice->saveAssociated($data)){
//						$priceIdInserted = $this->InvItem->InvPrice->id;
//							echo 'insertado|'.$priceIdInserted;
						echo "success";
					}
			}else{
				echo "noExRate";
			}
			
			
		}
	}
	
	public function ajax_save_list_price(){
		if($this->RequestHandler->isAjax()){
			
			$data = array();
			if(isset($this->request->data['priceId']) && $this->request->data['priceId'] <> ""){
//				$action = "edit";
				$data["InvPrice"]["id"] = $this->request->data['priceId'];//modify
			}else{
//				$action = "add";
				$this->InvItem->InvPrice->create();
			}
			
			/////////////////////////////////////////////////////////////////////
			$this->loadModel('AdmParameter');
			$currency = $this->AdmParameter->AdmParameterDetail->find('first', array(
				'conditions' => array(
					'AdmParameter.name' => 'Moneda',
					'AdmParameterDetail.par_char1' => 'Dolares'
				)
			));
			$currencyId = $currency['AdmParameterDetail']['id'];			
			////////////////////////////to find the previous last currency value
			$this->loadModel('AdmExchangeRate');
			$rateDirty = $this->AdmExchangeRate->find('first', array(
				'fields' => array('AdmExchangeRate.value'),
				'order' => array('AdmExchangeRate.date' => 'desc'),
				'conditions' => array(
					'AdmExchangeRate.currency' => $currencyId,
					'AdmExchangeRate.date <=' => $this->request->data['date']
				),
				'recursive' => -1
			));
			if ($rateDirty == array() || $rateDirty['AdmExchangeRate']['value'] == null) {
				$exRate = ''; //ESTO TIENE Q SER ''
			} else {
				$exRate = $rateDirty['AdmExchangeRate']['value'];
			}
			////////////////////////////to find the previous last currency value
			
			$data["InvPrice"]["inv_item_id"] = $this->request->data['itemId'];
			$data["InvPrice"]["inv_price_type_id"] = $this->request->data['priceType'];
			$data["InvPrice"]["ex_rate"] = $exRate;
			if($this->request->data['priceType'] > 1){
				$data["InvPrice"]["price"] = $this->request->data['amount'];
				$data["InvPrice"]["ex_price"] = $this->request->data['amount'] / $exRate;
			}else{
				$data["InvPrice"]["ex_price"] = $this->request->data['amount'];
				$data["InvPrice"]["price"] = $this->request->data['amount'] * $exRate;
			}			
			$data["InvPrice"]["date"] = $this->request->data['date'];
			$data["InvPrice"]["description"] = $this->request->data['description'];
			
			if($this->InvItem->InvPrice->save($data)){
				echo "success|".$this->InvItem->InvPrice->id;
			}
		}
	}
	
	public function ajax_delete_price(){
		if($this->RequestHandler->isAjax()){		
			
			$priceId = $this->request->data['priceId'];			
			$itemId = $this->request->data['itemId'];			
			$priceTypeId = $this->request->data['priceTypeId'];
			
			$exists = $this->InvItem->InvPrice->find("count", array("conditions"=>array("InvPrice.inv_item_id"=>$itemId, "InvPrice.inv_price_type_id"=>$priceTypeId)));
			
			//debug()
			
			if($exists > 1){
//				$arrayPrice = array('inv_price_id'=>$priceId);
				//$data = array('InvPrice'=>$arrayPrice);
//				$this->InvItem->InvPrice->deleteAll(array('InvPrice.id' => $arrayPrice));
				try {
					$this->InvItem->InvPrice->id = $priceId;
					$this->InvItem->InvPrice->delete();
					echo "success";
				} catch (Exception $e) {
					echo "error";
				}
			}else{
				echo "mustExistOne";
			}
		}
	}
	
	public function ajax_save_item(){
		if($this->RequestHandler->isAjax()){
			
			$itemId = $this->request->data['itemId'];
			$itemSupplier = $this->request->data['itemSupplier'];
			$itemSupplierId = $this->request->data['itemSupplierId'];
			$itemCode = strtoupper($this->request->data['itemCode']);
			$itemBrand = $this->request->data['itemBrand'];
			$itemCategory = $this->request->data['itemCategory'];
			$itemName = $this->request->data['itemName'];
			$itemDescription = $this->request->data['itemDescription'];
//			$itemMin = $this->request->data['itemMin'];
//			$itemPic = $this->request->data['itemPic'];

			$invItem = array('id' => $itemId, 'inv_brand_id' => $itemBrand, 'inv_category_id' => $itemCategory, 
				   'code' => $itemCode,'name' => $itemName, 'description' => $itemDescription
					/*'picture' => $itemPic, 'min_quantity' => $itemMin,'lc_transaction' => 'MODIFY'*/);
				$invItemsSupplier = array('id' => $itemSupplierId, 'inv_supplier_id' => $itemSupplier, 'inv_item_id' => $itemId);
		
			$res = $this->InvItem->updateItem($invItem, $invItemsSupplier);

			switch ($res[0]) {
			   case 'SUCCESS':
				   echo 'success|'.$res[2];
				   break;
			   case 'EXCEPTION':
				   echo 'exception';
				   break;
			   case 'ERROR':
				   echo 'ERROR|onSaving';
				   break;
            }
		}	
	}
	
	public function ajax_check_date_duplicity(){
		if($this->RequestHandler->isAjax()){
			$itemId = $this->request->data['itemId']; 
			$date = $this->request->data['date']; 
			$lastDate = $this->request->data['lastDate'];
			$priceType = $this->request->data['priceType'];
			
			$result = $this->InvItem->InvPrice->find('count', array(
				'fields'=>array('InvPrice.date'),
				'conditions'=>array(
						'InvPrice.inv_item_id'=>$itemId,
						'InvPrice.inv_price_type_id'=>$priceType,
						'InvPrice.date'=>$date,
						'InvPrice.date !='=>$lastDate
					)
			));
			echo $result;
		}
	}
	
	 public function ajax_search_controllers() {
        if ($this->RequestHandler->isAjax()) {
//            $customer = $this->request->data['customer']; //???????????????????
			$stockLocation = $this->request->data['stockLocation'];

			$this->loadModel('InvWarehouse');
			$warehouses = $this->InvWarehouse->find('list',array('conditions' => array('InvWarehouse.location' => $stockLocation)));
			$warehouses[0] = "Todos los almacenes";
			
			$valueWarehouse = 0; 
		
            $this->set(compact('warehouses', 'valueWarehouse'/* 'admControllers','admActions' */));
        } else {
            $this->redirect($this->Auth->logout());
        }
    }
	//////////////////////////////////////////// END - AJAX /////////////////////////////////////////////////
	
	//////////////////////////////////////////// START - PRIVATE ///////////////////////////////////////////////
	private function _get_prices($idPrice){
		$invPrices = $this->InvItem->InvPrice->find('all',array(
			'conditions' => array('InvPrice.inv_item_id' => $idPrice),
			'fields' => array('InvPrice.id','InvPriceType.name','InvPrice.date','InvPrice.price', 'InvPrice.ex_price','InvPrice.description', 'InvPriceType.id'),
			'order' => array('InvPrice.date' => 'desc'),
		));
		
		$formatedPrices = array();
		foreach ($invPrices as $key => $value){
			$formatedPrices[$key] = array(
				'itemId' => $idPrice,
				'priceId' => $value['InvPrice']['id'],
				'priceType' => $value['InvPriceType']['name'],
				'priceTypeId' => $value['InvPriceType']['id'],
				'date' => $value['InvPrice']['date'],
				'price' => $value['InvPrice']['price'],
				'ex_price' => $value['InvPrice']['ex_price'],
				'description' => $value['InvPrice']['description']
			);
		}
		
		return $formatedPrices;
	}
	
	private function _get_stocks($items, $warehouse='', $limitDate = '', $dateOperator = '<='){
		$this->loadModel('InvMovement');
		$this->InvMovement->InvMovementDetail->unbindModel(array('belongsTo' => array('InvItem')));
		$this->InvMovement->InvMovementDetail->bindModel(array(
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
		
		//variation added for InvItems
		$contionWarehouse = array();
		if($warehouse <> ''){
			$contionWarehouse = array('InvMovement.inv_warehouse_id'=>$warehouse);  
		}
		//////////////////////
		
		$movements = $this->InvMovement->InvMovementDetail->find('all', array(
			'fields'=>array(
				"InvMovementDetail.inv_item_id", 
				"(SUM(CASE WHEN \"InvMovementType\".\"status\" = 'entrada' AND \"InvMovement\".\"lc_state\" = 'APPROVED' THEN \"InvMovementDetail\".\"quantity\" ELSE 0 END))-
				(SUM(CASE WHEN \"InvMovementType\".\"status\" = 'salida' AND \"InvMovement\".\"lc_state\" = 'APPROVED' THEN \"InvMovementDetail\".\"quantity\" ELSE 0 END)) AS stock"
				),
			'conditions'=>array(
				'InvMovementDetail.inv_item_id'=>$items,
				$contionWarehouse,
				$dateRanges
				),
			'group'=>array('InvMovementDetail.inv_item_id'),
			'order'=>array('InvMovementDetail.inv_item_id')
		));
		return $movements;
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
	
	//////////////////////////////////////////// END - PRIVATE /////////////////////////////////////////////////
}
