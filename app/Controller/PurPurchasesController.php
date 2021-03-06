<?php
App::uses('AppController', 'Controller');
/**
 * PurPurchases Controller
 *
 * @property PurPurchase $PurPurchase
 */
class PurPurchasesController extends AppController {

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
	//*******************************************************************************************************//
	///////////////////////////////////////// START - FUNCTIONS ///////////////////////////////////////////////
	//*******************************************************************************************************//
	
	//////////////////////////////////////////// START - PDF ///////////////////////////////////////////////
	public function view_document_movement_pdf($id = null) {
		
		$this->PurPurchase->id = $id;
		
		if (!$this->PurPurchase->exists()) {
			throw new NotFoundException(__('Invalid post'));
		}
		// increase memory limit in PHP 
		ini_set('memory_limit', '512M');
		$movement = $this->PurPurchase->read(null, $id);		
		
		$details=$this->_get_movements_details($id);
		$costDetails=$this->_get_costs_details($id);
		$payDetails=$this->_get_pays_details($id);
		
		$this->set('movement', $movement);
		$this->set('details', $details);
		$this->set('costDetails', $costDetails);
		$this->set('payDetails', $payDetails);
	}
	//////////////////////////////////////////// END - PDF /////////////////////////////////////////////////
	
	//////////////////////////////////////////// START - REPORT ////////////////////////////////////////////////
	public function vreport_generator(){
		$this->loadModel("InvWarehouse");
		$warehouse = $this->InvWarehouse->find('list');
		$item = $this->_find_items();
		$this->set(compact("warehouse", "item"));
	}
	
	private function _find_items($type = 'none', $selected = array()){
		$conditions = array();
		$order = array('InvItem.code');
		
		switch ($type){
			case 'category':
				$conditions = array('InvItem.inv_category_id'=>$selected);
				//$order = array('InvCategory.name');
				break;
			case 'brand':
				$conditions = array('InvItem.inv_brand_id'=>$selected);
				//$order = array('InvBrand.name');
				break;
		}
			
		$this->loadModel("InvItem");
		$this->InvItem->unbindModel(array('hasMany' => array('InvPrice', 'InvCategory', 'InvMovementDetail', 'InvItemsSupplier')));
		return $this->InvItem->find("all", array(
					"fields"=>array('InvItem.code', 'InvItem.name', 'InvCategory.name', 'InvBrand.name', 'InvItem.id'),
					"conditions"=>$conditions,
					"order"=>$order
				));
	}
	
	public function ajax_get_group_items_and_filters(){
		if($this->RequestHandler->isAjax()){
			$type = $this->request->data['type'];
			$group = array();
			switch ($type) {
				case 'category':
					$this->loadModel("InvCategory");
					$group = $this->InvCategory->find("list", array("order"=>array("InvCategory.name")));
					$this->set('group', $group);
					break;
				case 'brand':
					$this->loadModel("InvBrand");
					$group = $this->InvBrand->find("list", array("order"=>array("InvBrand.name")));
					$this->set('group', $group);
					break;
			}
//			$item = $this->_find_items($type, array_keys($group));
			$item = $this->_find_items($type, array_keys(array()));
			$this->set(compact("item"));
		}
	}
	
	public function ajax_get_group_items(){
		if($this->RequestHandler->isAjax()){
			$type = $this->request->data['type'];
			if(isset($this->request->data['selected'])){
				$selected = $this->request->data['selected'];
			}else{
				$selected = array(); 
			}
			$item = $this->_find_items($type, $selected);
			$this->set(compact("item"));
		}
	}

	
	public function ajax_generate_report(){
		if($this->RequestHandler->isAjax()){
			//SETTING DATA
			$this->Session->write('ReportMovement.startDate', $this->request->data['startDate']);
			$this->Session->write('ReportMovement.finishDate', $this->request->data['finishDate']);
			$this->Session->write('ReportMovement.movementType', $this->request->data['movementType']);
			$this->Session->write('ReportMovement.movementTypeName', $this->request->data['movementTypeName']);
			$this->Session->write('ReportMovement.warehouse', $this->request->data['warehouse']);
			$this->Session->write('ReportMovement.warehouseName', $this->request->data['warehouseName']);
			$this->Session->write('ReportMovement.currency', $this->request->data['currency']);
			
			//for transfer
			$this->Session->write('ReportMovement.warehouse2', $this->request->data['warehouse2']);
			$this->Session->write('ReportMovement.warehouseName2', $this->request->data['warehouseName2']);
			//array items
			$this->Session->write('ReportMovement.items', $this->request->data['items']);
			
			//to send data response to ajax success so it can choose the report view
			echo $this->request->data['movementType']; 
		///END AJAX
		}
	}
	
	public function vreport_ins_or_outs(){
		$this->_generate_report();
	}
	
	public function vreport_ins_and_outs(){
		$this->_generate_report();
	}
	
	public function vreport_transfers(){
		$this->_generate_report(); 
	}
	
	private function _generate_report(){
		//special ctp template for printing due DOMPdf colapses generating too many pages
		$this->layout = 'print';
		
		//Check if session variables are set otherwise redirect
		if(!$this->Session->check('ReportMovement')){
			$this->redirect(array('action' => 'vreport_generator'));
		}
		
		//put session data sent data into variables
		$initialData = $this->Session->read('ReportMovement');
		
		//debug($initialData);

		$settings = $this->_generate_report_settings($initialData);
		
		//debug($settings);
		
		$movements=$this->_generate_report_movements($settings['values'], $settings['conditions'], $settings['fields']);
		//debug($movements);
		
		$currencyFieldPrefix = '';
		$currencyAbbreviation = '(BS)';
		if(trim($initialData['currency']) == 'DOLARES AMERICANOS'){
			$currencyFieldPrefix = 'ex_';
			$currencyAbbreviation = '($US)';
		}
		
		
		$itemsComplete = $this->_generate_report_items_complete($initialData['items']);
		//debug($itemsComplete);
		$itemsMovements = $this->_generate_report_items_movements($itemsComplete, $movements, $currencyFieldPrefix);
		//debug($itemsMovements);
		
		$initialData['currencyAbbreviation']=$currencyAbbreviation;//setting currency abbreviation before send
		$initialData['items']='';//cleaning items ids 'cause won't be needed begore send
		//debug($initialData);
		$this->set('initialData', $initialData);
		$this->set('itemsMovements', $itemsMovements);
		//debug($settings['initialStocks']);
		$this->set('initialStocks', $settings['initialStocks']);
		$this->Session->delete('ReportMovement');
	//END FUNCTION	
	}
	
	
	
	private function _generate_report_items_movements($itemsComplete, $movements, $currencyFieldPrefix){
		//I'll not calculate totals 'cause will be easier in the view and specially cleaner due the variation of calculation in every report
		$auxArray=array();
		foreach($itemsComplete as $item){
			$fobQuantityTotal = 0;
			$cifQuantityTotal = 0;
			$saleQuantityTotal = 0;
			$counter = 0;
			
			$forPricesSubQuery = 0; //before 'InvMovementDetail'
			
			//movements
			foreach($movements as $movement){
				if($item['InvItem']['id'] == $movement['InvMovementDetail']['inv_item_id']){
					$fobQuantity = $movement['InvMovementDetail']['quantity'] * $movement[$forPricesSubQuery][$currencyFieldPrefix.'fob_price'];
					$cifQuantity = $movement['InvMovementDetail']['quantity'] * $movement[$forPricesSubQuery][$currencyFieldPrefix.'cif_price'];
					$saleQuantity = $movement['InvMovementDetail']['quantity'] * $movement[$forPricesSubQuery][$currencyFieldPrefix.'sale_price'];
					$fobQuantityTotal = $fobQuantityTotal + $fobQuantity;
					$cifQuantityTotal = $cifQuantityTotal + $cifQuantity;
					$saleQuantityTotal = $saleQuantityTotal + $saleQuantity;
					$auxArray[$item['InvItem']['id']]['Movements'][$counter] = array(
						'code'=>$movement['InvMovement']['code'],
						'document_code'=>$movement['InvMovement']['document_code'],
						'quantity'=> $movement['InvMovementDetail']['quantity'],
						'date'=>date("d/m/Y", strtotime($movement['InvMovement']['date'])),
						'fob'=> $movement[$forPricesSubQuery][$currencyFieldPrefix.'fob_price'],
						'cif'=> $movement[$forPricesSubQuery][$currencyFieldPrefix.'cif_price'],
						'sale'=> $movement[$forPricesSubQuery][$currencyFieldPrefix.'sale_price'],
						'fobQuantity'=>$fobQuantity,
						'cifQuantity'=>$cifQuantity,
						'saleQuantity'=>$saleQuantity,
						'warehouse'=>$movement['InvMovement']['inv_warehouse_id']
					);
					if(isset($movement['InvMovementType']['status'])){
						$auxArray[$item['InvItem']['id']]['Movements'][$counter]['status']=$movement['InvMovementType']['status'];
					}
					$counter++;
				}
			}
			//Items
			$auxArray[ $item['InvItem']['id'] ]['Item']['codeName']='[ '.$item['InvItem']['code'].' ] '.$item['InvItem']['name'];
			$auxArray[ $item['InvItem']['id'] ]['Item']['brand']=$item['InvBrand']['name'];
			$auxArray[ $item['InvItem']['id'] ]['Item']['category']=$item['InvCategory']['name'];
			$auxArray[ $item['InvItem']['id'] ]['Item']['id']=$item['InvItem']['id'];
			//Totals
			$auxArray[ $item['InvItem']['id'] ]['TotalMovements']['fobQuantityTotal'] = $fobQuantityTotal;
			$auxArray[ $item['InvItem']['id'] ]['TotalMovements']['cifQuantityTotal'] = $cifQuantityTotal;
			$auxArray[ $item['InvItem']['id'] ]['TotalMovements']['saleQuantityTotal'] = $saleQuantityTotal;
			////I don't calculate total quantity here 'cause could vary in every report, it will be done in the report views
		}
		return $auxArray;
	}
	
	private function _generate_report_settings($initialData){
		///////////////////VALUES, FIELDS, CONDITIONS////////////////////////
		$values = array();
		$conditions = array();
		$fields = array();
		$initialStocks=array();
				
		
		$values['startDate']=$initialData['startDate'];
		$values['finishDate']=$initialData['finishDate'];
		$warehouses = array(0=>$initialData['warehouse']);
		
		switch ($initialData['movementType']) {
			case 998://TODAS LAS ENTRADAS
				$conditions['InvMovement.inv_movement_type_id']=array(1,4,5,6);
				break;
			case 999://TODAS LAS SALIDAS
				$conditions['InvMovement.inv_movement_type_id']=array(2,3,7);
				break;
			case 1000://ENTRADAS Y SALIDAS
				$values['bindMovementType'] = 1;
				$initialStocks = $this->_get_stocks($initialData['items'], $initialData['warehouse'], $initialData['startDate'], '<');//before starDate, 'cause it will be added or substracted with movements quantities
				break;
			case 1001://TRASPASOS ENTRE ALMACENES
				$values['bindMovementType'] = 1;
				$conditions['InvMovement.inv_movement_type_id']=array(3,4);
				$warehouses[1]=$initialData['warehouse2'];
				break;
			default:
				$conditions['InvMovement.inv_movement_type_id']=$initialData['movementType'];
				break;
		}
		$conditions['InvMovement.inv_warehouse_id']=$warehouses;//necessary to be here
		$values['items']=$initialData['items'];//just for order
		switch($initialData['currency']){
			case 'BOLIVIANOS':
				//$fields = array('InvMovementDetail.fob_price', 'InvMovementDetail.cif_price', 'InvMovementDetail.sale_price');
				$fields[]='(SELECT price FROM inv_prices where inv_item_id = "InvMovementDetail"."inv_item_id" AND date <= "InvMovement"."date" AND inv_price_type_id=1 order by date DESC, date_created DESC LIMIT 1) AS "fob_price"';
				$fields[]='(SELECT price FROM inv_prices where inv_item_id = "InvMovementDetail"."inv_item_id" AND date <= "InvMovement"."date" AND inv_price_type_id=8 order by date DESC, date_created DESC LIMIT 1) AS "cif_price"';
				$fields[]='(SELECT price FROM inv_prices where inv_item_id = "InvMovementDetail"."inv_item_id" AND date <= "InvMovement"."date" AND inv_price_type_id=9 order by date DESC, date_created DESC LIMIT 1) AS "sale_price"';
				break;
			case 'DOLARES AMERICANOS':
				//$fields = array('InvMovementDetail.ex_fob_price', 'InvMovementDetail.ex_cif_price', 'InvMovementDetail.ex_sale_price');
				$fields[]='(SELECT ex_price FROM inv_prices where inv_item_id = "InvMovementDetail"."inv_item_id" AND date <= "InvMovement"."date" AND inv_price_type_id=1 order by date DESC, date_created DESC LIMIT 1) AS "ex_fob_price"';
				$fields[]='(SELECT ex_price FROM inv_prices where inv_item_id = "InvMovementDetail"."inv_item_id" AND date <= "InvMovement"."date" AND inv_price_type_id=8 order by date DESC, date_created DESC LIMIT 1) AS "ex_cif_price"';
				$fields[]='(SELECT ex_price FROM inv_prices where inv_item_id = "InvMovementDetail"."inv_item_id" AND date <= "InvMovement"."date" AND inv_price_type_id=9 order by date DESC, date_created DESC LIMIT 1) AS "ex_sale_price"';
				break;
		}
		
		return array('values'=>$values,'conditions'=>$conditions, 'fields'=>$fields, 'initialStocks'=>$initialStocks);
	}
	
	
	private function _generate_report_movements($values, $conditions, $fields){
		$staticFields = array(
			'InvMovement.id',
			'InvMovement.code',
			'InvMovement.document_code',
			'InvMovement.date',
			'InvMovement.inv_warehouse_id',
			'InvMovementDetail.inv_item_id',
			'InvMovementDetail.quantity'
			);
		if(isset($values['bindMovementType']) AND $values['bindMovementType'] == 1){
			$this->InvMovement->InvMovementDetail->bindModel(array(
				'hasOne'=>array(
					'InvMovementType'=>array(
						'foreignKey'=>false,
						'conditions'=> array('InvMovement.inv_movement_type_id = InvMovementType.id')
					)
				)
			));
			$fields[] = 'InvMovementType.status'; 
		}
		$this->InvMovement->InvMovementDetail->unbindModel(array('belongsTo' => array('InvItem')));
		return $this->InvMovement->InvMovementDetail->find('all', array(
					'conditions'=>array(
						'InvMovementDetail.inv_item_id'=>$values['items'],
						'InvMovement.lc_state'=>'APPROVED',
						'InvMovement.date BETWEEN ? AND ?' => array($values['startDate'], $values['finishDate']),
						$conditions
					),
					'fields'=>  array_merge($staticFields, $fields),
					'order'=>array('InvMovement.date', 'InvMovementDetail.id')
				));
	}
	
	
	private function _generate_report_items_complete($items){
		$this->loadModel('InvItem');
		$this->InvItem->unbindModel(array('hasMany' => array('InvMovementDetail', 'PurDetail', 'SalDetail', 'InvItemsSupplier', 'InvPrice')));
		return $this->InvItem->find('all', array(
			'fields'=>array('InvItem.id', 'InvItem.code', 'InvItem.name', 'InvBrand.name', 'InvCategory.name'),
			'conditions'=>array('InvItem.id'=>$items),
			'order'=>array('InvItem.code')
		));
	}
	
	//////////////////////////////////////////// END - REPORT /////////////////////////////////////////////////
	
	public function vreport_historical_costs_generator() {
        $this->loadModel("AdmPeriod");
        $years = $this->AdmPeriod->find("list", array(
            "order" => array("name" => "desc"),
            "fields" => array("name", "name")
                )
        );
		
		$this->loadModel('InvBrand');
		$brands = $this->InvBrand->find('list', array(
            "order" => array("name"),
            "fields" => array("name")
                )
        );
		 
        $this->set(compact("years", "brands"));
    }
	
	public function ajax_generate_report_historical_costs() {
        if ($this->RequestHandler->isAjax()) {
            $this->Session->write('ReportHistoricalCosts.startDate', $this->request->data['startDate']);
            $this->Session->write('ReportHistoricalCosts.finishDate', $this->request->data['finishDate']);
//          $this->Session->write('ReportHistoricalCosts.brand', $this->request->data['brand']);
//			$this->Session->write('ReportHistoricalCosts.brandName', $this->request->data['brandName']);
//			$this->Session->write('ReportHistoricalCosts.priceType', $this->request->data['priceType']);
//			$this->Session->write('ReportHistoricalCosts.priceTypeName', $this->request->data['priceTypeName']);
			$this->Session->write('ReportHistoricalCosts.currency', $this->request->data['currency']);
        }
    }
	
	 public function vreport_historical_costs() {
        $this->layout = 'print';

        //Check if session variables are set otherwise redirect
        if (!$this->Session->check('ReportHistoricalCosts')) {
            $this->redirect(array('action' => 'vreport_historical_costs_generator'));
        }

        //put session data sent data into variables
        $initialData = $this->Session->read('ReportHistoricalCosts');
		
//		$conditionBrand = null;
//        if ($initialData["brand"] > 0) {
//            $conditionBrand = array("InvItem.inv_brand_id" => $initialData["brand"]);
//        }
//		
//		$conditionPriceType = null;
//        if ($initialData["priceType"] > 0) {
//            $conditionPriceType = array("InvPrice.inv_price_type_id" => $initialData["priceType"]);
//        }
		
		$currencyAbbr = "";
        if ($initialData["currency"] == "DOLARES") {
            $currencyAbbr = "ex_";
        }
		
		$this->loadModel("InvPriceType");
		$costs = $this->InvPriceType->PurPrice->find("all", array(
            "conditions" => array(
                 'PurPrice.date BETWEEN ? AND ?' => array($initialData['startDate'], $initialData['finishDate'])
//                , $conditionBrand
//                , $conditionPriceType
            )
            , "fields" => array(
				"InvPriceType.id"
//                , "InvItem.code"
                , "InvPriceType.name"
				, '"PurPrice"."'.$currencyAbbr.'amount"'
				, "PurPrice.date"
			)
            , "group" => array(
                "InvPriceType.id"
//                , "InvPriceType.code"
                , "InvPriceType.name"
				, '"PurPrice"."'.$currencyAbbr.'amount"'
				, "PurPrice.date"
            )
			, "order" => array( 'InvPriceType.id', 'PurPrice.date' )
        ));
//------------------------------------------------------------------------------------------		
		
		if($costs != array()){
			foreach ($costs as $cost) {
				$datesUnsorted[] = $cost['PurPrice']['date'];
			}
			asort($datesUnsorted);
			$dates = array_unique($datesUnsorted);
	//		die();
			$pricesByCost = null;
			foreach ($costs as $cost) {
				if($pricesByCost != null){
					$size = count($pricesByCost);
	//				$size = count($dates);
					$count = 0;
					for ($i = 0; $i < $size; $i++) {
						$count = $count + 1;
						if($cost['InvPriceType']['id'] == $pricesByCost[$i]['InvPriceType']['id']){//edit
							$lastPriceData = end($pricesByCost[$i]['prices']);
							$lastPrice = $lastPriceData['amount'];
							$increment = number_format((( $cost['PurPrice'][$currencyAbbr.'amount'] / $lastPrice ) - 1) * 100, 2, '.', '');
							$pricex = array('date'=>$cost['PurPrice']['date'],'amount'=>$cost['PurPrice'][$currencyAbbr.'amount'],'increment'=>$increment);
							$pricesByCost[$i]['prices'][] = $pricex;
							break;
						}					
						if($count == $size){//new
							$cost['prices'][] = array('date'=>$cost['PurPrice']['date'],'amount'=>$cost['PurPrice'][$currencyAbbr.'amount'],'increment'=>0);
							unset($cost['PurPrice']);
							$pricesByCost[] = $cost;					
						}
					}
				}else{//first
					$cost['prices'][] = array('date'=>$cost['PurPrice']['date'],'amount'=>$cost['PurPrice'][$currencyAbbr.'amount'],'increment'=>0);
					unset($cost['PurPrice']);
					$pricesByCost[] = $cost;
				}	
			}	


			$this->set("data", $initialData);
			$this->set("dates", array_values($dates));
			$this->set("pricesByCost", $pricesByCost);
			$this->Session->delete('ReportHistoricalCosts');	
		}else{
			$pricesByCost = null;
			$this->set("data", $initialData);
			$this->set("pricesByCost", $pricesByCost);
			$this->Session->delete('ReportHistoricalCosts');
		}
    }
	
	//////////////////////////////////////////START-GRAPHICS//////////////////////////////////////////
	
        ////////////////////////////////////////////NEW GRAPHICS - START//////////////////////////////////////////
    public function graphics_purchases_products() {
        ////////////////////////////////////// new feature 2015 ///////////////////
        if(count($this->passedArgs) > 0){
            $this->request->data = array('PurPurchase'=>$this->passedArgs);
//            debug($this->passedArgs);
        }
        ////////////////////////////////////// new feature 2015 ///////////////////

        $this->loadModel("AdmPeriod");
        $years = $this->AdmPeriod->find("list", array(
            "order" => array("name" => "desc"),
            "fields" => array("name", "name")
                )
        );
        $months = array("01" => "Ene", "02" => "Feb", "03" => "Mar", "04" => "Abr", "05" => "May", "06" => "Jun", "07" => "Jul", "08" => "Ago", "09" => "Sep", "10" => "Oct", "11" => "Nov", "12" => "Dic");

        ////////////////////////////////////// new feature 2015 ///////////////////
        $monthsSelected = array();
        if(isset($this->passedArgs['month'])){ //item
            $this->passedArgs['month'] = split('-',$this->passedArgs['month']);
            foreach($this->passedArgs['month'] as $index => $value){
                $monthsSelected[$index] = $value;
            }
        }else{ //clean start from categories or brands
            $monthsSelected = array("01","02","03","04","05","06","07","08","09","10","11","12");
        }
        $this->set("monthsSelected", $monthsSelected);
        ////////////////////////////////////// new feature 2015 ///////////////////

        $departamentsClean = $this->PurPurchase->find("list", array(
            "fields" => array("PurPurchase.location", "PurPurchase.location"),
            "group" => array("PurPurchase.location"),
        ));
        $departaments = array_merge(array("TODOS" => "TODOS"), $departamentsClean);
        ////////////////////////////
        $currencies = array("BOB" => "BOLIVIANOS", "USD" => "DOLARES");
        $priceTypes = array("fob" => "FOB", "cif" => "CIF");
        $showBy = array("money" => "DINERO", "quantity" => "CANTIDAD");
        ///////////////////////////
        $groups = array();
        if (!isset($this->passedArgs['groupId'])) {
            $groups = array('brand' => 'Marca', 'category' => 'Categoria');
        }
        $this->set(compact("years", "months", "departaments", "currencies", "priceTypes", "showBy", "groups"));
        ///////////////////////////////////////TEST BEFORE AJAX //////////////////////////////////
////        debug($this->_get_pie_sales_by_groups("2013", array("02", "03", "04", "05"), "La Paz", "USD", "sale", "money", "InvCategory", "0", "include", null));
//        $year = "2013";
//        $month = array("02", "03", "04", "05");
//        $location = "La Paz";
//        $currency = "USD";
//        $priceType = "sale";
//        $showBy = "money";
//        $model = "InvCategory";
//        $selectedIds = "0";
//        $rule = "include";
//        $productsCondition = null;
//
//        //PIE (CORE) and also included data
//        $pieDataCompleteIncluded = $this->_get_pie_sales_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $selectedIds, $rule, $productsCondition);
////        debug($pieDataCompleteIncluded);
//        $pieDataFormatedIncluded = $this->_formatDataPieToJson($pieDataCompleteIncluded, $model); //Here divides in two = [json, selectedIds]
//        debug($pieDataFormatedIncluded);
//        $json["Pie"] = $pieDataFormatedIncluded["json"]; //$pieDataDivided["selectedIds"]
//        $listIncludedSums = $pieDataFormatedIncluded["listDatatableIdsSums"];
//        debug($listIncludedSums);
//        $listIncludedSelectedIds = $pieDataFormatedIncluded["selectedIds"]; //always will work because capture selected checkboxes ids or limit 5 order DESC (never nulls)
//        if ($selectedIds[0] > 0) {//selected checkedbox value from datatable
//            $listIncludedSelectedIds = $selectedIds;
//        }
//
//        //LINES BARS
//        $linesBarsDataComplete = $this->_get_bars_lines_sales_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $pieDataFormatedIncluded["selectedIds"], $productsCondition);
//        $linesBarsDataFormated = $this->_formatDataLinesBarsToJson($linesBarsDataComplete, $model, $pieDataFormatedIncluded["selectedIds"]);
//        $json["LinesBars"] = $linesBarsDataFormated;
//
//        //excluded data
//        $pieDataCompleteExcluded = $this->_get_pie_sales_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $selectedIds, "exclude", $productsCondition);
//        $pieDataFormatedExcluded = $this->_formatDataPieToJson($pieDataCompleteExcluded, $model);
//        $listExcludedSums = $pieDataFormatedExcluded["listDatatableIdsSums"];
//        $listExcludedSelectedIds = $this->_getExcludedIds($model, $listIncludedSelectedIds, $productsCondition); //must overload this function to get excluded
//        //COMPLETE LIST for datatable = with checkboxes, colors, label and quantities. Union, ordered and 0 quanties
//        $listGroups = $this->_getList($model, $productsCondition);
//        $json["DataTable"] = $this->_get_list_groups_graphics_datatable($listIncludedSums, $listExcludedSums, $listIncludedSelectedIds, $listExcludedSelectedIds, $listGroups, $showBy);
////        debug($json);
////        $json["LastSelectedGroup"] = $group;
////        return new CakeResponse(array('body' => json_encode($json)));  //convert to json format and send
    }

    ///////////////////////////////////

    private function _getExcludedIds($model, $excludes, $productsCondition) {
        $this->loadModel($model);
        $data = $this->$model->find("list", array(
            "fields" => array($model . ".id", $model . ".id")
            , "conditions" => array(
                "NOT" => array($model . ".id" => $excludes)
                , $productsCondition
            )
            , "order" => array($model . ".name" => "ASC")
        ));
        return $data;
    }

    private function _getList($model, $productsCondition) {
        $this->loadModel($model);
        $data = $this->$model->find("list", array(
            "conditions" => array($productsCondition)
        ));
        return $data;
    }

    private function _get_list_groups_graphics_datatable($listIncludedSums, $listExcludedSums, $listIncludedSelectedIds, $listExcludedSelectedIds, $listGroups, $showBy) {
        //****$listIncludedSums, $listExcludedSums  have => ids, label, data  
        //put labels to selected ids
        $listIncludedSelectedIdsPlusLabels = $this->_set_labels_to_selected_ids($listIncludedSelectedIds, $listGroups);
        $listExcludedSelectedIdsPlusLabels = $this->_set_labels_to_selected_ids($listExcludedSelectedIds, $listGroups);

        //put zero values to selected ids when null
        $listIncludedPlusZeros = $this->_add_zero_to_selected_ids($listIncludedSelectedIdsPlusLabels, $listIncludedSums, "included", $showBy);
        $listExcludedPlusZeros = $this->_add_zero_to_selected_ids($listExcludedSelectedIdsPlusLabels, $listExcludedSums, "excluded", $showBy);

        //merges included and excluded 
        return array_merge($listIncludedPlusZeros, $listExcludedPlusZeros);
//        return array_merge($listIncludedSelectedIdsPlusLabels, $listExcludedSelectedIdsPlusLabels);
    }

    private function _set_labels_to_selected_ids($selectedIds, $completeList) {
        $data = array();
        $counter = 0;
//        debug($selectedIds);
//        debug($completeList);
//        for ($i = 0; $i < count($selectedIds); $i++) {
        foreach ($selectedIds as $keySelectedId => $varSelectedId) {
            foreach ($completeList as $id => $label) { //id=>label
                if ($varSelectedId == $id) {
                    $data[$counter]["id"] = $id;
                    $data[$counter]["label"] = $label;
                    unset($completeList[$id]);
                }
            }
            $counter++;
        }
        return $data;
    }

    private function _add_zero_to_selected_ids($listSelectedIdsPlusLabels, $listSums, $type, $showBy) {
        //this function will add zero value if null 
        $listIncludedPlusZeros = array();
        $tempSortBySumArray = array(); //to order multidimension array by sum value
        $tempSortByLabelArray = array();
        $round = 0;
        if($showBy == "money"){
            $round = 2;
        }
        
        for ($i = 0; $i < count($listSelectedIdsPlusLabels); $i++) {

            $listIncludedPlusZeros[$i]["id"] = $listSelectedIdsPlusLabels[$i]["id"];
            $listIncludedPlusZeros[$i]["checked"] = false;
            if ($type == "included") {
                $listIncludedPlusZeros[$i]["checked"] = true;
            }
            $listIncludedPlusZeros[$i]["label"] = $listSelectedIdsPlusLabels[$i]["label"];

            $sumValue = 0;
            for ($j = 0; $j < count($listSums); $j++) {
                if ($listSelectedIdsPlusLabels[$i]["id"] == $listSums[$j]["id"]) {
                    $sumValue = $listSums[$j]["data"];
//                    unset($listSums[$j]["id"]);//unset not working 'cause nested array moves index and counter does not match anymore
                }
            }
            $listIncludedPlusZeros[$i]["data"] = number_format((float)$sumValue, $round, '.', '');//$sumValue;
            $tempSortBySumArray[$i] = $sumValue;
            $tempSortByLabelArray[$i] = $listSelectedIdsPlusLabels[$i]["label"];
        }

        //sort by sum DESC, label ASC
        $res = array_multisort($tempSortBySumArray, SORT_DESC, $tempSortByLabelArray, SORT_ASC, $listIncludedPlusZeros); //Important array_multisort return true or false, must send the original sorted array anyway

        return $listIncludedPlusZeros;
    }

    public function ajax_get_graphics_purchases_products() {
        if ($this->RequestHandler->isAjax()) {
            $year = $this->request->data['year'];
            $month = $this->request->data['month'];
            $location = $this->request->data['location'];
            $currency = $this->request->data['currency'];
            $priceType = $this->request->data['priceType'];
            $showBy = $this->request->data['showBy'];
            $group = $this->request->data['group']; //category or brand
            $selectedIds = $this->request->data['selectedIds'];
            $groupId = $this->request->data['groupId'];
            $json = array();
            /////////////////SWITCH BETWEEN GROUPS OR PRODUCTS BY GROUP           
            $model = "InvBrand";
            $productsCondition = null;
            if ($group == "category") {
                $model = "InvCategory";
            }
            if ($groupId > 0) { //For items
                $model = "InvItem";
                if ($group == "category") {
                    $productsCondition = array($model . ".inv_category_id" => $groupId);
                } else {
                    $productsCondition = array($model . ".inv_brand_id" => $groupId);
                }
            }else{//For Brands and Categories
                //FIND COLORS
                $this->loadModel($model);
                $json['colors']=$this->$model->find('list', array('fields'=>array($model.'.id', $model.'.color')));
            }
            ////////////////////////////////////////EXEC - START///////////////////////////////

            //PIE (CORE) and also included data
            $pieDataCompleteIncluded = $this->_get_pie_purchases_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $selectedIds, "include", $productsCondition, $groupId);
			$pieDataFormatedIncluded = $this->_formatDataPieToJson($pieDataCompleteIncluded, $model, $groupId); //Here divides in two = [json, selectedIds]
			$json["Pie"] = $pieDataFormatedIncluded["json"]; //$pieDataDivided["selectedIds"]
            $listIncludedSums = $pieDataFormatedIncluded["listDatatableIdsSums"];
            $listIncludedSelectedIds = $pieDataFormatedIncluded["selectedIds"]; //always will work because capture selected checkboxes ids or limit 5 order DESC (never nulls)
            if ($selectedIds[0] > 0) {//selected checkedbox value from datatable
                $listIncludedSelectedIds = $selectedIds;
            }
            //LINES BARS
            $linesBarsDataComplete = $this->_get_bars_lines_purchases_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $pieDataFormatedIncluded["selectedIds"], $productsCondition, $groupId);
            $linesBarsDataFormated = $this->_formatDataLinesBarsToJson($linesBarsDataComplete, $model, $pieDataFormatedIncluded["selectedIds"], $groupId);
            $json["LinesBars"] = $linesBarsDataFormated;

            //excluded data
            $pieDataCompleteExcluded = $this->_get_pie_purchases_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $selectedIds, "exclude", $productsCondition, $groupId);
            $pieDataFormatedExcluded = $this->_formatDataPieToJson($pieDataCompleteExcluded, $model, $groupId);
            $listExcludedSums = $pieDataFormatedExcluded["listDatatableIdsSums"];
            $listExcludedSelectedIds = $this->_getExcludedIds($model, $listIncludedSelectedIds, $productsCondition); //must overload this function to get excluded
            //COMPLETE LIST for datatable = with checkboxes, colors, label and quantities. Union, ordered and 0 quanties
            $listGroups = $this->_getList($model, $productsCondition);
            $json["DataTable"] = $this->_get_list_groups_graphics_datatable($listIncludedSums, $listExcludedSums, $listIncludedSelectedIds, $listExcludedSelectedIds, $listGroups, $showBy);
            $json["LastSelectedGroup"] = $group;
            $json["groupId"] = $groupId;
        return new CakeResponse(array('body' => json_encode($json)));  //convert to json format and send
            ////////////////////////////////////////EXEC - END///////////////////////////////
        } else {
            $this->redirect($this->Auth->logout()); //only accesible through ajax otherwise logout
        }
    }

    ///////////////////////////////////
    private function _get_pie_purchases_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $selectedIds, $rule, $productsCondition, $groupId) {
        if ($location == "TODOS") {
            $location = null;
        } else {
            $location = array("PurPurchase.location" => $location);
        }
        $fieldName = $model . ".name";
        $fieldId = $model . ".id";

        $limit = null;
        $offset = null;
        if ($selectedIds == 0) {
            $selectedIds = null;
            if ($rule == "include") {//include
                $limit = 5;
            }else{//exclude
                $offset = 5;
            }
        } else {
            if ($rule == "include") {//include
                $selectedIds = array($model . ".id" => $selectedIds);
            } else {//exclude
                $selectedIds = array("NOT" => array($model . ".id" => $selectedIds));
            }
        }
        ////////////// Model Bindings
        $exceptionBind = array();
        if ($model == "InvBrand") {
            $exceptionBind[$model] = array('foreignKey' => false, 'conditions' => array('InvItem.inv_brand_id = InvBrand.id'));
        } elseif ($model == "InvCategory") {
            $exceptionBind[$model] = array('foreignKey' => false, 'conditions' => array('InvItem.inv_category_id = InvCategory.id'));
        }
        $genericBind = array(
//            "InvMovementType" => array('foreignKey' => false, 'conditions' => array("InvMovement.inv_movement_type_id = InvMovementType.id")),
//            "InvWarehouse" => array('foreignKey' => false, 'conditions' => array("InvMovement.inv_warehouse_id = InvWarehouse.id"))
        );
        $this->PurPurchase->PurDetail->bindModel(array(
            "hasOne" => array_merge($genericBind, $exceptionBind)
        ));

        ///////////// Sales rules
        $sumField = '"PurDetail"."quantity"';
        $round = 0;
        if ($showBy == "money") {
            $round = 2;
            $sumField = '"PurDetail"."quantity" * "PurDetail"."' . $priceType . '_price"';
            if ($currency == "USD") {
                $sumField = '"PurDetail"."quantity" * "PurDetail"."ex_' . $priceType . '_price"';
            }
            if ($priceType == "fob") {//only fob has discounts
                $sumField = $this->_createSubQueryDiscounts($sumField, $currency);
            }
        }
        /////////
        $fields = array($fieldId, $fieldName, $model.".color", "ROUND(COALESCE(SUM(" . $sumField . "),0)," . $round . ") as sum");
        $group = array($fieldId, $fieldName, $model.".color");
        if($groupId > 0){ //for items without color field
            //New 2015
                $fields = array($fieldId, $fieldName, $model.".code", "ROUND(COALESCE(SUM(" . $sumField . "),0)," . $round . ") as sum");
                $group = array($fieldId, $fieldName, $model.".code");
            }
        //Query
        $data = $this->PurPurchase->PurDetail->find('all', array(
            "fields" => $fields,
            "group" => $group,
            "conditions" => array(
                "PurPurchase.lc_state" => "PINVOICE_APPROVED",
                "to_char(PurPurchase.date,'YYYY')" => $year,
                "to_char(PurPurchase.date,'mm')" => $month,
                $location,
                $selectedIds,
                $productsCondition
            ),
            "order" => array("sum" => "DESC"),
            "limit" => $limit,
            "offset" => $offset
        ));
        return $data;
    }

    private function _formatDataPieToJson($data, $model, $groupId) {
        //Format Data to Json (data[i] = { label: "Name", data: number })
        $json = array();
        $selectedIds = array();
        $listDatatableIdsSums = array(); //to fill graphics datatable
        for ($i = 0; $i < count($data); $i++) {
            $json[$i]["label"] = $data[$i][$model]["name"];
            $json[$i]["data"] = (float)$data[$i][0]["sum"];//(float) $data[$i][0]["sum"]; //Convert to int, otherwise plotchart.js won't recognize
            if($groupId == 0) { //brand or category
                $json[$i]["color"] = $data[$i][$model]["color"];
            }else{ //items
                $json[$i]["label"] = '[ '.$data[$i][$model]["code"] .' ] '.$data[$i][$model]["name"];
            }
            $selectedIds[$i] = $data[$i][$model]["id"];
            $listDatatableIdsSums[$i]["id"] = $data[$i][$model]["id"];
            $listDatatableIdsSums[$i]["label"] = $data[$i][$model]["id"];
            $listDatatableIdsSums[$i]["data"] = (float) $data[$i][0]["sum"];
        }
        return array("json" => $json, "selectedIds" => $selectedIds, "listDatatableIdsSums" => $listDatatableIdsSums); //$data;
        //////////////////////////////////////////////////////////////////////////////////
    }

    private function _get_bars_lines_purchases_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $selectedIds, $productsCondition, $groupId) {
        if ($location == "TODOS") {
            $location = null;
        } else {
            $location = array("PurPurchase.location" => $location);
        }
        $fieldName = $model . ".name";
        $fieldId = $model . ".id";

//        $limit = null;
//        if ($selectedIds == 0) {
//            $selectedIds = null;
//            $limit = 5;
//        } else {
//            if ($rule == "include") {//include
//                $selectedIds = array($model . ".id" => $selectedIds);
//            } else {//exclude
//                $selectedIds = array("NOT" => array($model . ".id" => $selectedIds));
//            }
//        }
        ////////////// Model Bindings
        $exceptionBind = array();
        if ($model == "InvBrand") {
            $exceptionBind[$model] = array('foreignKey' => false, 'conditions' => array('InvItem.inv_brand_id = InvBrand.id'));
        } elseif ($model == "InvCategory") {
            $exceptionBind[$model] = array('foreignKey' => false, 'conditions' => array('InvItem.inv_category_id = InvCategory.id'));
        }
        $genericBind = array(
//            "InvMovementType" => array('foreignKey' => false, 'conditions' => array("InvMovement.inv_movement_type_id = InvMovementType.id")),
//            "InvWarehouse" => array('foreignKey' => false, 'conditions' => array("InvMovement.inv_warehouse_id = InvWarehouse.id"))
        );
        $this->PurPurchase->PurDetail->bindModel(array(
            "hasOne" => array_merge($genericBind, $exceptionBind)
        ));

        ///////////// Sales rules
        $sumField = '"PurDetail"."quantity"';
        $round = 0;
        if ($showBy == "money") {
            $round = 2;
            $sumField = '"PurDetail"."quantity" * "PurDetail"."' . $priceType . '_price"';
            if ($currency == "USD") {
                $sumField = '"PurDetail"."quantity" * "PurDetail"."ex_' . $priceType . '_price"';
            }
            if ($priceType == "fob") {//only sale has discounts
                $sumField = $this->_createSubQueryDiscounts($sumField, $currency);
            }
        }
        /////
        $fields = array($fieldId, $fieldName, $model.".color", "to_char(\"PurPurchase\".\"date\",'mm') AS month", "ROUND(COALESCE(SUM(" . $sumField . "),0)," . $round . ") as sum");
        $group = array($fieldId, $fieldName, $model.".color", "month");
        if($groupId > 0){ //for items without color field
            //New 2015
            $fields = array($fieldId, $fieldName, $model.".code", "to_char(\"PurPurchase\".\"date\",'mm') AS month", "ROUND(COALESCE(SUM(" . $sumField . "),0)," . $round . ") as sum");
            $group = array($fieldId, $fieldName, $model.".code", "month");
        }
        //Query
        $data = $this->PurPurchase->PurDetail->find('all', array(
            "fields" => $fields,
            "group" => $group,
            "conditions" => array(
                "PurPurchase.lc_state" => "PINVOICE_APPROVED",
                "to_char(PurPurchase.date,'YYYY')" => $year,
                "to_char(PurPurchase.date,'mm')" => $month,
                $location,
                $model . ".id" => $selectedIds,
                $productsCondition
            ),
            "order" => array("month" => "ASC")
//            "limit" => $limit
        ));
        return $data;
    }

    private function _formatDataLinesBarsToJson($data, $model, $selectedIds, $groupId) {
        //Format Data to Json (data[i] = { label: "Name", data: number })
        $dataGrouped = array();
        $json = array();
        $counter = 0;

        for ($i = 0; $i < count($data); $i++) {
            /////////////new 2015///////
            $label = $data[$i][$model]["name"];
            //////////////////////////////
            $id = $data[$i][$model]["id"]; //
            $month = (int) $data[$i][0]["month"];
            $quantity = (float) $data[$i][0]["sum"];
            ///////////////////////////////
            if($groupId == 0){ //category or brand
                $color = $data[$i][$model]["color"];
                //new 2015
                $dataGrouped[$id . "%-&" . $label . "%-&" . $color][$month] = array($month, $quantity); //Ej: 'Accesorios' => array('01'=>array(1,888), '02'=>array(2,543)) | 'Aceites' => array('01'=>array(1,78))
            }else{//items without color
                //new 2015
                $label = '[ '.$data[$i][$model]["code"].' ] ' . $data[$i][$model]["name"];
                $dataGrouped[$id . "%-&" . $label][$month] = array($month, $quantity); //Ej: 'Accesorios' => array('01'=>array(1,888), '02'=>array(2,543)) | 'Aceites' => array('01'=>array(1,78))
                // $dataGrouped[$label][$month] = array($month,$quantity); //Ej: 'Accesorios' => array('01'=>array(1,888), '02'=>array(2,543)) | 'Aceites' => array('01'=>array(1,78))
            }
            ////////////////////////////////
        }///End FOR
        foreach ($selectedIds as $valueSelectedIds) {//order elements as pie chart DESC values
            foreach ($dataGrouped as $keyDataGrouped => $valueDataGrouped) {
                ////////////////////////////////////
                if($groupId == 0){ //category or brand
                    list($id, $label, $color) = split("%-&", $keyDataGrouped); //new 2015
                }else{//Item without color
                    list($id, $label) = split("%-&", $keyDataGrouped); //new 2015
                }
                ////////////////////////////////////
                if ($valueSelectedIds == $id) {
                    $json[$counter]["label"] = $label;
                    $json[$counter]["data"] = array_values($valueDataGrouped); //use array_values to reset keys. Ej: "04" to 0, "08" to 1 in sequencial order. For fit plotchart format
                    if($groupId == 0) { //brand or category
                        $json[$counter]["color"] = $color;
                    }
                    unset($dataGrouped[$keyDataGrouped]); //if matches remove the element from array for better perfomance
                }
            }
            $counter++;
        }

        return $json;
    }


    private function _createSubQueryDiscounts($sumField, $currency) {
        //PERCENT
        $discountPercent = '(' . $sumField . ')-((' . $sumField . ')*("PurPurchase"."discount")/100)';

        //BOB
        $discountBOB = ' CASE WHEN "PurPurchase"."discount_type" = \' ' . $currency . ' \' ';
        $discountBOB .= ' THEN';
        $discountBOB .= ' (' . $sumField . ')-("PurPurchase"."discount")';
        $discountBOB .= ' ELSE';
        $discountBOB .= ' (' . $sumField . ')-(("PurPurchase"."discount") / "PurPurchase"."ex_rate")';
        $discountBOB .= ' END';

        //USD
        $discountUSD = ' CASE WHEN "PurPurchase"."discount_type" = \' ' . $currency . ' \' ';
        $discountUSD .= ' THEN'; //(discount)USD = (sumField)USD*
        $discountUSD .= ' (' . $sumField . ')-("PurPurchase"."discount")'; //res en USD 
        $discountUSD .= ' ELSE'; //(discount)USD = (sumField)BOB*
        $discountUSD .= ' (' . $sumField . ')-(("PurPurchase"."discount") * "PurPurchase"."ex_rate")'; //res en BOB
        $discountUSD .= ' END';
        ///////////////////////////////

        $discountOperators = ' CASE WHEN';
        $discountOperators .= ' "PurPurchase"."discount_type" != \'NONE\' ';
        $discountOperators .= ' THEN';
        $discountOperators .= ' (CASE WHEN "PurPurchase"."discount_type" = \'BOB\' THEN ( ' . $discountBOB . ' ) ';
        $discountOperators .= ' WHEN "PurPurchase"."discount_type" = \'USD\' THEN ( ' . $discountUSD . ' ) ';
        $discountOperators .= ' WHEN "PurPurchase"."discount_type" = \'PERCENT\' THEN ( ' . $discountPercent . ' )  END) ';
        $discountOperators .= ' ELSE';
        $discountOperators .= $sumField;
        $discountOperators .= ' END';
        return $discountOperators;
    }

////////////////////////////////////////////NEW GRAPHICS - END//////////////////////////////////////////
        
        
	////////////OLD GRAPHICS
	public function vgraphics(){
		$this->loadModel("AdmPeriod");
		$years = $this->AdmPeriod->find("list", array(
			"order"=>array("name"=>"desc"),
			"fields"=>array("name", "name")
			)
		);
		/*
		$this->loadModel("InvItem");
		$itemsClean = $this->InvItem->find("list", array('order'=>array('InvItem.code')));
		$items[0]="TODOS";
		foreach ($itemsClean as $key => $value) {
			$items[$key] = $value;
		}
		*/
		$item = $this->_find_items();
		//$this->loadModel("InvPriceType");
		//$priceTypes = $this->InvPriceType->find("list", array("conditions"=>array("name"=>array("FOB", "CIF"))));
		
		$this->set(compact("years", "item"));
		//debug($this->_get_bars_sales_and_time("2013", "0"));
	}
	
	public function ajax_get_graphics_data(){
		if($this->RequestHandler->isAjax()){
			$year = $this->request->data['year'];
			//$month = $this->request->data['month'];
			$currency = $this->request->data['currency'];
			$item = $this->request->data['item'];
			$priceType = $this->request->data['priceType'];;
			$string = $this->_get_bars_purchases_and_time($year, $item, $currency, $priceType);
			echo $string;
		}
//		$string .= '30|54|12|114|64|100|98|80|10|50|169|222';
	}
	
	private function _get_bars_purchases_and_time($year, $item, $currency, $priceType){
		$conditionItem = null;
		$dataString = "";
		//$conditionMonth= null;
		if($item > 0){
			$conditionItem = array("PurDetail.inv_item_id" => $item);
		}
		
		$currencyField = "";
		if($currency == "dolares"){
			$currencyField = "ex_";
		}
		
		$priceTypeField = "fob_price";
		if($priceType == "CIF"){
			$priceTypeField = "cif_price";
		}
		/*
		if($month > 0){
			if(count($month) == 1){
				$conditionMonth = array("to_char(PurPurchase.date,'mm')" => "0".$month);
			}else{
				$conditionMonth = array("to_char(PurPurchase.date,'mm')" => $month);
			}
			
		}
		*/
		//*****************************************************************************//
		$this->PurPurchase->PurDetail->unbindModel(array('belongsTo' => array('InvSupplier')));
		$data = $this->PurPurchase->PurDetail->find('all', array(
			"fields"=>array(
				"to_char(\"PurPurchase\".\"date\",'mm') AS month",
				//'SUM("PurDetail"."quantity" * (SELECT '.$currencyType.'  FROM inv_prices where inv_item_id = "PurDetail"."inv_item_id" AND date <= "PurPurchase"."date" AND inv_price_type_id='.$priceType.' order by date DESC, date_created DESC LIMIT 1))'
				'SUM("PurDetail"."quantity" * "PurDetail"."'.$currencyField.$priceTypeField.'")'
			),
			"conditions"=>array(
				"to_char(PurPurchase.date,'YYYY')"=>$year,
				"PurPurchase.lc_state"=>"PINVOICE_APPROVED",
				$conditionItem,
				//$conditionMonth
				
			),
			'group'=>array("to_char(PurPurchase.date,'mm')")
		));
		//*****************************************************************************//
		
		
		//format data on string to response ajax request
		$months = array(1,2,3,4,5,6,7,8,9,10,11,12);
		
		foreach ($months as $month) {
			$exist = 0;
			foreach ($data as $value) {
				if($month == (int)$value[0]['month']){
					$dataString .= $value[0]['sum']."|";
					//debug($dataString);
					$exist++;
				}
			}
			if($exist == 0){
				$dataString .= "0|";
			}
		}
		
		return substr($dataString, 0, -1);
	}
	
	
	
	//////////////////////////////////////////END-GRAPHICS//////////////////////////////////////////
	
	//////////////////////////////////////////// START - INDEX ///////////////////////////////////////////////
	
	public function index_order() {	
		
		///////////////////////////////////////START - CREATING VARIABLES//////////////////////////////////////
		$filters = array();
		$doc_code = '';
		$note_code = '';
		$searchDate = '';
		$period = $this->Session->read('Period.name');
		///////////////////////////////////////END - CREATING VARIABLES////////////////////////////////////////
		
		////////////////////////////START - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
		if($this->request->is("post")) {
			$url = array('action'=>'index_order');
			$parameters = array();
			$empty=0;
			if(isset($this->request->data['PurPurchase']['doc_code']) && $this->request->data['PurPurchase']['doc_code']){
				$parameters['doc_code'] = trim(strip_tags($this->request->data['PurPurchase']['doc_code']));
			}else{
				$empty++;
			}
			if(isset($this->request->data['PurPurchase']['note_code']) && $this->request->data['PurPurchase']['note_code']){
				$parameters['note_code'] = trim(strip_tags($this->request->data['PurPurchase']['note_code']));
			}else{
				$empty++;
			}
			if(isset($this->request->data['PurPurchase']['searchDate']) && $this->request->data['PurPurchase']['searchDate']){
				$parameters['searchDate'] = trim(strip_tags(str_replace("/", "", $this->request->data['PurPurchase']['searchDate'])));
			}else{
				$empty++;
			}
			if($empty == 3){
				$parameters['search']='empty';
			}else{
				$parameters['search']='yes';
			}
			$this->redirect(array_merge($url,$parameters));
		}
		////////////////////////////END - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////

		////////////////////////////START - SETTING URL FILTERS//////////////////////////////////////
		if(isset($this->passedArgs['doc_code'])){
			$filters['PurPurchase.doc_code LIKE'] = '%'.strtoupper($this->passedArgs['doc_code']).'%';
			$doc_code = $this->passedArgs['doc_code'];
		}
		if(isset($this->passedArgs['note_code'])){
			$filters['PurPurchase.note_code LIKE'] = '%'.strtoupper($this->passedArgs['note_code']).'%';
			$note_code = $this->passedArgs['note_code'];
		}
		if(isset($this->passedArgs['searchDate'])){
			$catchDate = $this->passedArgs['searchDate'];
			$finalDate = substr($catchDate, 0, 2)."/".substr($catchDate, 2, 2)."/".substr($catchDate, 4, 4);		
			$filters['PurPurchase.date'] = $finalDate;
			$searchDate = $finalDate;
		}
		////////////////////////////END - SETTING URL FILTERS//////////////////////////////////////
		
		////////////////////////////START - SETTING PAGINATING VARIABLES//////////////////////////////////////
		$this->paginate = array(
			"conditions"=>array(
				"PurPurchase.lc_state !="=>"ORDER_LOGIC_DELETED",
				'PurPurchase.lc_state LIKE'=> '%ORDER%',
				"to_char(PurPurchase.date,'YYYY')"=> $period,
				$filters
			 ),
			"recursive"=>0,
			"fields"=>array("PurPurchase.id", "PurPurchase.code", "PurPurchase.doc_code", "PurPurchase.date", "PurPurchase.note_code", "PurPurchase.inv_warehouse_id", "InvWarehouse.name", "PurPurchase.lc_state"),
			"order"=> array("PurPurchase.id"=>"desc"),
			"limit" => 15,
		);
		////////////////////////////END - SETTING PAGINATING VARIABLES//////////////////////////////////////
		
		////////////////////////START - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
		$this->set('purPurchases', $this->paginate('PurPurchase'));
		$this->set('doc_code', $doc_code);
		$this->set('note_code', $note_code);
		$this->set('searchDate', $searchDate);		
		////////////////////////END - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
		
//		$this->paginate = array(
//			'conditions' => array(
//				'PurPurchase.lc_state !='=>'ORDER_LOGIC_DELETED'
//				,'PurPurchase.lc_state LIKE'=> '%ORDER%'
//			),
//			'order' => array('PurPurchase.id' => 'desc'),
//			'limit' => 15
//		);
//		$this->PurPurchase->recursive = 0;
//		$this->set('purPurchases', $this->paginate());
	}
	
	public function index_invoice(){
		///////////////////////////////////////START - CREATING VARIABLES//////////////////////////////////////
		$filters = array();
		$doc_code = '';
		$note_code = '';
		$searchDate = '';
		$period = $this->Session->read('Period.name');
		///////////////////////////////////////END - CREATING VARIABLES////////////////////////////////////////
		
		////////////////////////////START - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
		if($this->request->is("post")) {
			$url = array('action'=>'index_invoice');
			$parameters = array();
			$empty=0;
//			if(isset($this->request->data['PurPurchase']['doc_code']) && $this->request->data['PurPurchase']['doc_code']){
//				$parameters['doc_code'] = trim(strip_tags($this->request->data['PurPurchase']['doc_code']));
//			}else{
//				$empty++;
//			}
			if(isset($this->request->data['PurPurchase']['note_code']) && $this->request->data['PurPurchase']['note_code']){
				$parameters['note_code'] = trim(strip_tags($this->request->data['PurPurchase']['note_code']));
			}else{
				$empty++;
			}
			if(isset($this->request->data['PurPurchase']['searchDate']) && $this->request->data['PurPurchase']['searchDate']){
				$parameters['searchDate'] = trim(strip_tags(str_replace("/", "", $this->request->data['PurPurchase']['searchDate'])));
			}else{
				$empty++;
			}
			if($empty == 3){
				$parameters['search']='empty';
			}else{
				$parameters['search']='yes';
			}
			$this->redirect(array_merge($url,$parameters));
		}
		////////////////////////////END - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////

		////////////////////////////START - SETTING URL FILTERS//////////////////////////////////////
//		if(isset($this->passedArgs['doc_code'])){
//			$filters['PurPurchase.code LIKE'] = '%'.strtoupper($this->passedArgs['doc_code']).'%';
//			$doc_code = $this->passedArgs['doc_code'];
//		}
		if(isset($this->passedArgs['note_code'])){
			$filters['PurPurchase.note_code LIKE'] = '%'.strtoupper($this->passedArgs['note_code']).'%';
			$note_code = $this->passedArgs['note_code'];
		}
		if(isset($this->passedArgs['searchDate'])){
			$catchDate = $this->passedArgs['searchDate'];
			$finalDate = substr($catchDate, 0, 2)."/".substr($catchDate, 2, 2)."/".substr($catchDate, 4, 4);		
			$filters['PurPurchase.date'] = $finalDate;
			$searchDate = $finalDate;
		}
		////////////////////////////END - SETTING URL FILTERS//////////////////////////////////////
		
		////////////////////////////START - SETTING PAGINATING VARIABLES//////////////////////////////////////
		$this->paginate = array(
			"conditions"=>array(
				"PurPurchase.lc_state !="=>"PINVOICE_LOGIC_DELETED",
				'PurPurchase.lc_state LIKE'=> '%PINVOICE%',
				"to_char(PurPurchase.date,'YYYY')"=> $period,
			//	"InvMovementType.status"=> "entrada",
				$filters
			 ),
			"recursive"=>0,
			"fields"=>array("PurPurchase.id", "PurPurchase.code", "PurPurchase.doc_code", "PurPurchase.date", "PurPurchase.note_code", "PurPurchase.inv_warehouse_id", "InvWarehouse.name", "PurPurchase.lc_state"),
			"order"=> array("PurPurchase.id"=>"desc"),
			"limit" => 15,
		);
		////////////////////////////END - SETTING PAGINATING VARIABLES//////////////////////////////////////
		
		////////////////////////START - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
		$this->set('purPurchases', $this->paginate('PurPurchase'));
		$this->set('doc_code', $doc_code);
		$this->set('note_code', $note_code);
		$this->set('searchDate', $searchDate);		
		////////////////////////END - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
		
		
//		$this->paginate = array(
//			'conditions' => array(
//				'PurPurchase.lc_state !='=>'INVOICE_LOGIC_DELETED',
//				'PurPurchase.lc_state LIKE'=> '%INVOICE%',
//			),
//			'order' => array('PurPurchase.id' => 'desc'),
//			'limit' => 15
//		);
//		$this->PurPurchase->recursive = 0;
//		$this->set('purPurchases', $this->paginate());
	}
	
	///////////////////////////////////////////// END - INDEX ////////////////////////////////////////////////
	
	//////////////////////////////////////////// START - SAVE ///////////////////////////////////////////////
	
	public function save_order(){
		$id = '';
		if(isset($this->passedArgs['id'])){
			$id = $this->passedArgs['id'];
		}
		$this->loadModel('InvWarehouse');
		$invWarehouses = $this->InvWarehouse->find('list');
		
		$this->PurPurchase->recursive = -1;
		$this->request->data = $this->PurPurchase->read(null, $id);
		$genericCode ='';
		$purDetails = array();
		$documentState = '';
		$date=date('d/m/Y');
		$discount  = 0;
		////////////////////////////to find the previous last currency value
		$this->loadModel('AdmParameter');
		$currency = $this->AdmParameter->AdmParameterDetail->find('first', array(
				'conditions'=>array(
					'AdmParameter.name'=>'Moneda',
					'AdmParameterDetail.par_char1'=>'Dolares'
				)
			)); 
		$currencyId = $currency['AdmParameterDetail']['id'];
		
		$this->loadModel('AdmExchangeRate');
		$rateDirty = $this->AdmExchangeRate->find('first', array(
				'fields'=>array('AdmExchangeRate.value'),
				'order' => array('AdmExchangeRate.date' => 'desc'),
				'conditions'=>array(
					'AdmExchangeRate.currency'=>$currencyId,
					'AdmExchangeRate.date <='=>$date
				),
				'recursive'=>-1
			)); 		
		if($rateDirty == array() || $rateDirty['AdmExchangeRate']['value'] == null){
			$exRate = '';
		}else{		
			$exRate = $rateDirty['AdmExchangeRate']['value'];	
		}
		////////////////////////////to find the previous last currency value
		$invoiceState = array();
		$movementState = array();
		if($id <> null){
			$date = date("d/m/Y", strtotime($this->request->data['PurPurchase']['date']));//$this->request->data['InvMovement']['date'];
			$purDetails = $this->_get_movements_details($id);
			$documentState =$this->request->data['PurPurchase']['lc_state'];
			$genericCode = $this->request->data['PurPurchase']['code'];			
			$exRate = $this->request->data['PurPurchase']['ex_rate'];
			$discount = $this->request->data['PurPurchase']['discount'];
			////////////////////////////
			$invoiceId = $this->_get_doc_id($id, $genericCode, null/*, null*/);//gets MOVEMENT id type 1(hay stock)
			$movementId = $this->_get_doc_id(null, $genericCode, 1/*, 2*/);//EL ALTO 2 MANUALMENTE VER COMO ELEGIR ESTO
			
			$invoiceState = $this->PurPurchase->find('list', array(
						'fields'=>array(
							'PurPurchase.lc_state'
							),
						'conditions'=>array(
								'PurPurchase.id'=>$invoiceId
							)
				));
			
			$this->loadModel('InvMovement');
			$movementState = $this->InvMovement->find('list', array(
						'fields'=>array(
							'InvMovement.lc_state'
							),
						'conditions'=>array(
								'InvMovement.id'=>$movementId
							)
				));
//				debug($invoiceState);
//				debug($movementState);
			////////////////////////////
//			debug($purDetails);	
		}
		$this->set(compact('id', 'invWarehouses', 'date', 'purDetails', 'documentState', 'genericCode', 'exRate', 'discount', 'invoiceState', 'movementState'));
	}
	
	public function save_invoice(){
		$id = '';
		if(isset($this->passedArgs['id'])){
			$id = $this->passedArgs['id'];
		}
		$this->loadModel('AdmParameter');
        $currency = $this->AdmParameter->AdmParameterDetail->find('first', array(
            'conditions' => array(
                'AdmParameter.name' => 'Moneda',
                'AdmParameterDetail.par_char1' => 'Dolares'
            )
        ));
        $currencyId = $currency['AdmParameterDetail']['id'];
		$this->loadModel('InvWarehouse');
		$invWarehouses = $this->InvWarehouse->find('list');
						
		$this->PurPurchase->recursive = -1;
		$this->request->data = $this->PurPurchase->read(null, $id);
		
		$documentState = '';
		$genericCode ='';
                $cloneStatus ='';
//		$originCode = '';
		$date = date('d/m/Y');
		$discountType = 1;
        $discount = 0;
		$purDetails = array();
		$purPrices = array();
		$purPayments = array();
		
		////////////////////////////to find the previous last currency value
//		$this->loadModel('AdmParameter');
//		$currency = $this->AdmParameter->AdmParameterDetail->find('first', array(
//				'conditions'=>array(
//					'AdmParameter.name'=>'Moneda',
//					'AdmParameterDetail.par_char1'=>'Dolares'
//				)
//			)); 
//		$currencyId = $currency['AdmParameterDetail']['id'];
//		
//		$this->loadModel('AdmExchangeRate');
//		$rateDirty = $this->AdmExchangeRate->find('first', array(
//				'fields'=>array('AdmExchangeRate.value'),
//				'order' => array('AdmExchangeRate.date' => 'desc'),
//				'conditions'=>array(
//					'AdmExchangeRate.currency'=>$currencyId,
//					'AdmExchangeRate.date <='=>$date
//				),
//				'recursive'=>-1
//			)); 		
//		if($rateDirty == array() || $rateDirty['AdmExchangeRate']['value'] == null){
//			$exRate = '';
//		}else{		
//			$exRate = $rateDirty['AdmExchangeRate']['value'];	
//		}
		////////////////////////////to find the previous last currency value
		////////////////////////////to find the previous last currency value
        $this->loadModel('AdmExchangeRate');
        $rateDirty = $this->AdmExchangeRate->find('first', array(
            'fields' => array('AdmExchangeRate.value'),
            'order' => array('AdmExchangeRate.date' => 'desc'),
            'conditions' => array(
                'AdmExchangeRate.currency' => $currencyId,
                'AdmExchangeRate.date <=' => $date
            ),
            'recursive' => -1
        ));
        if ($rateDirty == array() || $rateDirty['AdmExchangeRate']['value'] == null) {
            $exRate = ''; //ESTO TIENE Q SER ''
        } else {
            $exRate = $rateDirty['AdmExchangeRate']['value'];
        }
        ////////////////////////////to find the previous last currency value
		
		if($id <> null){
			$date = date("d/m/Y", strtotime($this->request->data['PurPurchase']['date']));//$this->request->data['InvMovement']['date'];
			$purDetails = $this->_get_movements_details($id);
			$purPrices = $this->_get_costs_details($id);
			$purPayments = $this->_get_pays_details($id);
			$documentState =$this->request->data['PurPurchase']['lc_state'];
			$genericCode = $this->request->data['PurPurchase']['code'];
                        $cloneStatus = $this->request->data['PurPurchase']['clone'];
			//find the origin doc code
//			$originDocCode = $this->PurPurchase->find('first', array(
//				'fields'=>array('PurPurchase.doc_code'),
//				'conditions'=>array(
//					'PurPurchase.code'=>$genericCode,
//					'PurPurchase.lc_state LIKE'=> '%ORDER%'
//					)
//			));
//			$originCode = $originDocCode['PurPurchase']['doc_code'];
			$exRate = $this->request->data['PurPurchase']['ex_rate'];
			$discountTypeName = $this->request->data['PurPurchase']['discount_type'];
			if($discountTypeName == 'NONE'){$discountType=1;}else if($discountTypeName == 'PERCENT'){$discountType=2;}else/*if($discountTypeName == 'BOB')*/{$discountType=3;} 
            $discount = $this->request->data['PurPurchase']['discount'];
			$paid = $this->request->data['PurPurchase']['paid'];
			////////////////////////////
//			$movementId = $this->_get_doc_id(null, $genericCode, 1/*, 2*/);//EL ALTO 2 MANUALMENTE VER COMO ELEGIR ESTO
//			$movementState = $this->InvMovement->find('list', array(
//						'fields'=>array(
//							'InvMovement.lc_state'
//							),
//						'conditions'=>array(
//								'InvMovement.id'=>$movementId
//							)
//				));
			////////////////////////////
		}
		
			
		$this->set(compact('id', 'invWarehouses', 'date', 'purDetails', 'purPrices', 'purPayments', 'documentState', 'genericCode', /*'originCode',*/ 'exRate', 'discountType', 'discount', 'paid'/*, 'movementState'*/, 'cloneStatus'));
	}
	
	//////////////////////////////////////////// END - SAVE /////////////////////////////////////////////////
	
	//////////////////////////////////////////// START - AJAX ///////////////////////////////////////////////
	
	public function ajax_initiate_modal_add_item_in(){
		if($this->RequestHandler->isAjax()){
						
			/*$itemsAlreadySaved = $this->request->data['itemsAlreadySaved'];
			$supplierItemsAlreadySaved = $this->request->data['supplierItemsAlreadySaved'];*/
			$itemsSuppliersAlreadySaved = $this->request->data['itemsSuppliersAlreadySaved'];
//			$date = $this->request->data['date'];
			$lastSupplier = $this->request->data['lastSupplier'];
			
//			$this->loadModel('InvSupplier');
//			$suppliersCount = $this->InvSupplier->find('count');
			$this->loadModel('InvItems');
//			$itemsCount = $this->InvItems->find('count', array(
////				'conditions'=>array(
////					'NOT'=>array('InvSupplier.id'=>$suppliersWithAllItemsTaken)
////				)
//			));
			if($lastSupplier != 0){
				foreach ($itemsSuppliersAlreadySaved as $a) {
					list($itemsAlreadySaved[],$suppliersAlreadySaved[]) = explode("s", $a);
				}
				
				$itemsAlreadyTakenFromSupplier = array();
				for ($i = 0; $i < count($itemsAlreadySaved); $i++) {
					if ($suppliersAlreadySaved[$i] == $lastSupplier) {
						$itemsAlreadyTakenFromSupplier[] = $itemsAlreadySaved[$i];
					}
				}
				
				$itemsBySupplier = $this->PurPurchase->PurDetail->InvItem->InvItemsSupplier->find('list', array(
					'fields'=>array('InvItemsSupplier.inv_item_id'),
					'conditions'=>array(
						'InvItemsSupplier.inv_supplier_id'=>$lastSupplier
					),
					'recursive'=>-1
				)); 
				
				$items = $this->PurPurchase->PurDetail->InvItem->find('list', array(
					'conditions'=>array(
						'NOT'=>array('InvItem.id'=>$itemsAlreadyTakenFromSupplier)
						,'InvItem.id'=>$itemsBySupplier
					),
					'recursive'=>-1,
					'order'=>array('InvItem.code')
				));
				
			}
			


//			$suppliersWithAllItemsTaken = array();
//            $suppliersCount = array_count_values($suppliersAlreadySaved);
//            foreach ($suppliersCount as $key => $value) {
//                if ($value == $itemsCount) {
//                    $suppliersWithAllItemsTaken[] = $key;
//                }
//            }
			
			/*$suppliers = $this->InvSupplier->find('list');
			$supplier = key($suppliers);
			$itemsBySupplier = $this->PurPurchase->PurDetail->InvItem->InvItemsSupplier->find('list', array(
				'fields'=>array('InvItemsSupplier.inv_item_id'),
				'conditions'=>array(
					'InvItemsSupplier.inv_supplier_id'=>$supplier
				),
				'recursive'=>-1
			)); 	
			
			$itemsAlreadyTakenFromSupplier = array();
			for($i=0; $i<count($itemsAlreadySaved); $i++){
				if($supplierItemsAlreadySaved[$i] == $supplier){
					$itemsAlreadyTakenFromSupplier[] = $itemsAlreadySaved[$i];
				}	
			}	*/	
			$suppliers = $this->PurPurchase->PurDetail->InvSupplier->find('list', array(
//				'conditions'=>array(
//					'NOT'=>array('InvSupplier.id'=>$suppliersWithAllItemsTaken)
////					,'InvItem.id'=>$itemsBySupplier
//				),
//				'recursive'=>-1,
//				'order'=>array('InvSupplier.code')
			));
			$suppliers = array(0 => '-- Seleccione un Proveedor --') + $suppliers;
		////////////////////////////to find the previous last item fob ex_price 
//		$firstItemListed = key($items);
//		$priceDirty = $this->PurPurchase->PurDetail->InvItem->InvPrice->find('first', array(
//			'fields'=>array('InvPrice.ex_price'),
//			'order' => array('InvPrice.date' => 'desc'),
//			'conditions'=>array(
//				'InvPrice.inv_item_id'=>$firstItemListed
//				,'InvPrice.inv_price_type_id'=>1
//				,'InvPrice.date <='=>$date
//				)
//		));
//		if($priceDirty == array() || $priceDirty['InvPrice']['ex_price'] == null){
//			$price = '';
//		}else{
//			$price = $priceDirty['InvPrice']['ex_price'];
//		}
		////////////////////////////to find the previous last item fob ex_price 
			$this->set(compact('items', /*'price',*/ 'suppliers'));
		}
	}
	
	public function ajax_update_items_modal(){
		if($this->RequestHandler->isAjax()){
//			$itemsAlreadySaved = $this->request->data['itemsAlreadySaved'];
//			$supplierItemsAlreadySaved = $this->request->data['supplierItemsAlreadySaved'];
			$itemsSuppliersAlreadySaved = $this->request->data['itemsSuppliersAlreadySaved'];
			$supplier = $this->request->data['supplier'];
//			$date = $this->request->data['date'];
			
			foreach ($itemsSuppliersAlreadySaved as $a) {
				list($itemsAlreadySaved[],$suppliersAlreadySaved[]) = explode("s", $a);
			}

			$itemsAlreadyTakenFromSupplier = array();
			for ($i = 0; $i < count($itemsAlreadySaved); $i++) {
				if ($suppliersAlreadySaved[$i] == $supplier) {
					$itemsAlreadyTakenFromSupplier[] = $itemsAlreadySaved[$i];
				}
			}
						
			$itemsBySupplier = $this->PurPurchase->PurDetail->InvItem->InvItemsSupplier->find('list', array(
				'fields'=>array('InvItemsSupplier.inv_item_id'),
				'conditions'=>array(
					'InvItemsSupplier.inv_supplier_id'=>$supplier
				),
				'recursive'=>-1
			)); 	
			
//            $itemsAlreadyTakenFromSupplier = array();
//			for($i=0; $i<count($itemsAlreadySaved); $i++){
//				if($supplierItemsAlreadySaved[$i] == $supplier){
//                    $itemsAlreadyTakenFromSupplier[] = $itemsAlreadySaved[$i];
//                }
//            }
			
			$items = $this->PurPurchase->PurDetail->InvItem->find('list', array(
				'conditions'=>array(
					'NOT'=>array('InvItem.id'=>$itemsAlreadyTakenFromSupplier)
					,'InvItem.id'=>$itemsBySupplier
				),
				'recursive'=>-1,
				'order'=>array('InvItem.code')
			));
//			$item = key($items);
//			////////////////////////////to find the previous last item fob ex_price 
//			$priceDirty = $this->PurPurchase->PurDetail->InvItem->InvPrice->find('first', array(
//			'fields'=>array('InvPrice.ex_price'),
//			'order' => array('InvPrice.date' => 'desc'),
//			'conditions'=>array(
//				'InvPrice.inv_item_id'=>$item
//				,'InvPrice.inv_price_type_id'=>1
//				,'InvPrice.date <='=>$date
//				)
//			));
//			if($priceDirty == array() || $priceDirty['InvPrice']['ex_price'] == null){
//				$price = '';
//			}else{
//				$price = $priceDirty['InvPrice']['ex_price'];
//			}
			////////////////////////////to find the previous last item fob ex_price 
			$this->set(compact('items'/*, 'price'*/));
		}
	}
	
	public function ajax_update_price_modal(){
		if($this->RequestHandler->isAjax()){
			$item = $this->request->data['item'];
			$date = $this->request->data['date'];
			////////////////////////////to find the previous last item fob ex_price 
			$priceDirty = $this->PurPurchase->PurDetail->InvItem->InvPrice->find('first', array(
			'fields'=>array('InvPrice.ex_price'),
			'order' => array('InvPrice.date' => 'desc'),
			'conditions'=>array(
				'InvPrice.inv_item_id'=>$item
				,'InvPrice.inv_price_type_id'=>1
				,'InvPrice.date <='=>$date
				)
			));
			if($priceDirty == array() || $priceDirty['InvPrice']['ex_price'] == null){
				$price = '';
			}else{
				$price = $priceDirty['InvPrice']['ex_price'];
			}
			////////////////////////////to find the previous last item fob ex_price 
			$this->set(compact('price'));
		}
	}
	
	public function ajax_initiate_modal_add_cost(){
		if($this->RequestHandler->isAjax()){
		//				$cost = $this->request->data['cost'];
			$costsAlreadySaved = $this->request->data['costsAlreadySaved'];
//			$warehouse = $this->request->data['warehouse'];
		//	$supplier = $this->request->data['supplier'];
//	//		$itemsBySupplier = $this->PurPurchase->InvSupplier->InvItemsSupplier->find('list', array(
//				'fields'=>array('InvItemsSupplier.inv_item_id'),
//				'conditions'=>array(
//					'InvItemsSupplier.inv_supplier_id'=>$supplier
//				),
//				'recursive'=>-1
//			)); 
//debug($itemsBySupplier);			
			$costs = $this->PurPurchase->PurPrice->InvPriceType->find('list', array(
					'fields'=>array('InvPriceType.name'),
					'conditions'=>array(
						'NOT'=>array('InvPriceType.id'=>$costsAlreadySaved,'InvPriceType.name'=>array('VENTA','FOB','CIF'))
				),
				
				'recursive'=>-1
				//'fields'=>array('InvItem.id', 'CONCAT(InvItem.code, '-', InvItem.name)')
			));
//debug($costs);			
//debug($items);
//debug($this->request->data);
		// gets the first price in the list of the item prices
//		$firstItemListed = key($items);
//		$priceDirty = $this->PurPurchase->PurDetail->InvItem->InvPrice->find('first', array(
//			'fields'=>array('InvPrice.price'),
//			'order' => array('InvPrice.date_created' => 'desc'),
//			'conditions'=>array(
//				'InvPrice.inv_item_id'=>$firstItemListed
//				)
//		));
////debug($priceDirty);
//		if($priceDirty==array()){
//			$price = 0;
//		}  else {
//			
//			$price = $priceDirty['InvPrice']['price'];
//		}
//			$amountDirty = $this->PurPurchase->PurPrice->find('first', array(
//			'fields'=>array('PurPrice.amount'),
//	//		'order' => array('rice.date_created' => 'desc'),
//			'conditions'=>array(
//				'PurPrice.inv_price_type_id'=>$costsAlreadySaved
//				)
//			));
//			if($amountDirty==array()){
//			$amount = 0;
//		}  else {
//			
//			$amount = $amountDirty['PurPrice']['amount'];
//		}
				
			$this->set(compact('costs'/*, 'amount'*/));
		}
	}
	
	//Hace un find en la BD de los elementos que se mostraran en el combobox 
	public function ajax_initiate_modal_add_pay(){
		if($this->RequestHandler->isAjax()){
			$docCode = $this->request->data['docCode'];
			$datePay = $this->request->data['date']; //temporal date that shows in the payment modal
//			$paysAlreadySaved = $this->request->data['paysAlreadySaved'];
//			$payDebt = $this->request->data['payDebt'];
//			debug($payDebt);
//			$datePay=date('d/m/Y');
//			$debt = $this->SalSale->SalPayment->find('list', array(
//					'fields'=>array('SalPayment.amount'),
//					'conditions'=>array(
//						'SalPayment.date'=>$paysAlreadySaved
//				),
//				'recursive'=>-1
//			));
//	debug($debt);
//			$warehouse = $this->request->data['warehouse'];
		//	$supplier = $this->request->data['supplier'];
//	//		$itemsBySupplier = $this->PurPurchase->InvSupplier->InvItemsSupplier->find('list', array(
//				'fields'=>array('InvItemsSupplier.inv_item_id'),
//				'conditions'=>array(
//					'InvItemsSupplier.inv_supplier_id'=>$supplier
//				),
//				'recursive'=>-1
//			)); 
//debug($itemsBySupplier);			
//			$pays = $this->PurPurchase->PurPayment->PurPaymentType->find('list', array(
//					'fields'=>array('PurPaymentType.name'),
//					'conditions'=>array(
////						'NOT'=>array('InvPriceType.id'=>$paysAlreadySaved) /*aca se hace la discriminacion de items seleccionados*/
//				),
//				
//				'recursive'=>-1
//				//'fields'=>array('InvItem.id', 'CONCAT(InvItem.code, '-', InvItem.name)')
//			));
//debug($supplier);			
//debug($items);
//debug($this->request->data);
		// gets the first price in the list of the item prices
//		$firstItemListed = key($items);
//		$priceDirty = $this->PurPurchase->PurDetail->InvItem->InvPrice->find('first', array(
//			'fields'=>array('InvPrice.price'),
//			'order' => array('InvPrice.date_created' => 'desc'),
//			'conditions'=>array(
//				'InvPrice.inv_item_id'=>$firstItemListed
//				)
//		));
////debug($priceDirty);
//		if($priceDirty==array()){
//			$price = 0;
//		}  else {
//			
//			$price = $priceDirty['InvPrice']['price'];
//		}
//			$amountDirty = $this->PurPurchase->PurPrice->find('first', array(
//			'fields'=>array('PurPrice.amount'),
//	//		'order' => array('rice.date_created' => 'desc'),
//			'conditions'=>array(
//				'PurPrice.inv_price_type_id'=>$costsAlreadySaved
//				)
//			));
//			if($amountDirty==array()){
//			$amount = 0;
//		}  else {
//			
//			$amount = $amountDirty['PurPrice']['amount'];
//		}
			
			$discountType = current($this->PurPurchase->find('list', array(
				'fields' => array('PurPurchase.discount_type'),
				'conditions' => array('PurPurchase.doc_code' => $docCode)
			)));
			if($discountType == 'PERCENT'){
				$discountString = '- (SUM("PurDetail"."ex_subtotal") * "PurPurchase"."discount"/100)';
			}else if($discountType == 'USD'){
				$discountString = '- "PurPurchase"."discount"';
			}else if($discountType == 'NONE'){
				$discountString = '';
			}
			$total = $this->PurPurchase->PurDetail->find('first', array(
//              'fields' => array('(ROUND(SUM("PurDetail"."ex_subtotal") - (SUM("PurDetail"."ex_subtotal") * "PurPurchase"."discount"/100),2)) AS total'),
				'fields' => array('(ROUND(SUM("PurDetail"."ex_subtotal")' .$discountString. ',2)) AS total'),
                'conditions' => array('PurPurchase.doc_code' => $docCode),
                'group' => array('PurPurchase.discount')
            ));
            $paid = $this->PurPurchase->PurPayment->find('first', array(
                'fields' => array('(ROUND(SUM("PurPayment"."amount"),2)) AS paid'),
                'conditions' => array('PurPurchase.doc_code' => $docCode)
            ));
//			$payDebt = $total[0]['total'] - $paid[0]['paid'];
			$payDebt = number_format((float)$total[0]['total'] - $paid[0]['paid'], 2, '.', '');
			if($payDebt < 0){$payDebt = '0.00';}
			$this->set(compact(/*'pays',*/ 'datePay', 'payDebt'/*, 'amount'*/));
		}
	}
	
	
	
	//no se utiliza pq no tiene que mostrar ningun valos en otro campo a partir del elemento elegido en el combobox
	public function ajax_update_amount(){
		if($this->RequestHandler->isAjax()){
			$cost = $this->request->data['cost'];
//			$warehouse = $this->request->data['warehouse']; //if it's warehouse_transfer is OUT
//			$warehouse2 = $this->request->data['warehouse2'];//if it's warehouse_transfer is IN
//			$transfer = $this->request->data['transfer'];
			
//			$stock = $this->_find_stock($item, $warehouse);//if it's warehouse_transfer is OUT
//			$stock2 ='';
//			if($transfer == 'warehouses_transfer'){
//				$stock2 = $this->_find_stock($item, $warehouse2);//if it's warehouse_transfer is IN	
//			}
			$amountDirty = $this->PurPurchase->PurPrice->find('first', array(
			'fields'=>array('PurPrice.amount'),
	//		'order' => array('rice.date_created' => 'desc'),
			'conditions'=>array(
				'PurPrice.inv_price_type_id'=>$cost
				)
			));
			if($amountDirty==array()){
			$amount = 0;
		}  else {
			
			$amount = $amountDirty['PurPrice']['amount'];
		}
			$this->set(compact('amount'));
		}
	}
	
	 public function ajax_initiate_modal_edit_pay() {
        if ($this->RequestHandler->isAjax()) {
            $docCode = $this->request->data['docCode'];
//			$datePay = $this->request->data['date']; //temporal date that shows in the payment modal

			$discountType = current($this->PurPurchase->find('list', array(
				'fields' => array('PurPurchase.discount_type'),
				'conditions' => array('PurPurchase.doc_code' => $docCode)
			)));
			if($discountType == 'PERCENT'){
				$discountString = '- (SUM("PurDetail"."ex_subtotal") * "PurPurchase"."discount"/100)';
			}else if($discountType == 'USD'){
				$discountString = '- "PurPurchase"."discount"';
			}else if($discountType == 'NONE'){
				$discountString = '';
			}
			$total = $this->PurPurchase->PurDetail->find('first', array(
//              'fields' => array('(ROUND(SUM("PurDetail"."ex_subtotal") - (SUM("PurDetail"."ex_subtotal") * "PurPurchase"."discount"/100),2)) AS total'),
				'fields' => array('(ROUND(SUM("PurDetail"."ex_subtotal")' .$discountString. ',2)) AS total'),
                'conditions' => array('PurPurchase.doc_code' => $docCode),
                'group' => array('PurPurchase.discount')
            ));

            $paid = $this->PurPurchase->PurPayment->find('first', array(
                'fields' => array('(ROUND(SUM("PurPayment"."amount"),2)) AS paid'),
                'conditions' => array('PurPurchase.doc_code' => $docCode),
//			'group'=>array('SalSale.discount')
            ));

//            $payDebt = $total[0]['total'] - $paid[0]['paid'];
			$payDebt = number_format((float)$total[0]['total'] - $paid[0]['paid'], 2, '.', '');

            $this->set(compact(/* 'pays', 'datePay', */ 'payDebt'/* , 'amount' */));
        }
    }
	
	public function ajax_update_ex_rate(){
		if($this->RequestHandler->isAjax()){
			$date = $this->request->data['date']; 
			
			$this->loadModel('AdmParameter');
			$currency = $this->AdmParameter->AdmParameterDetail->find('first', array(
					'conditions'=>array(
						'AdmParameter.name'=>'Moneda',
						'AdmParameterDetail.par_char1'=>'Dolares'
					)
				)); 
			$currencyId = $currency['AdmParameterDetail']['id'];
			////////////////////////////to find the previous last currency value
			$this->loadModel('AdmExchangeRate');
			$rateDirty = $this->AdmExchangeRate->find('first', array(
					'fields'=>array('AdmExchangeRate.value'),
					'order' => array('AdmExchangeRate.date' => 'desc'),
					'conditions'=>array(
						'AdmExchangeRate.currency'=>$currencyId,
						'AdmExchangeRate.date <='=>$date
					),
					'recursive'=>-1
				)); 		
			if($rateDirty == array() || $rateDirty['AdmExchangeRate']['value'] == null){
				$exRate = '';
			}else{		
				$exRate = $rateDirty['AdmExchangeRate']['value'];	
			}
			//////////////////////////to find the previous last currency value
			$this->set(compact('exRate'));			
		}else{
			$this->redirect($this->Auth->logout());
		}
	}
	
	public function ajax_check_code_duplicity(){
		if($this->RequestHandler->isAjax()){
			$noteCode = $this->request->data['noteCode']; //23456
			$genericCode = $this->request->data['genericCode']; //COM
			if ($genericCode == ''){
				$result = $this->PurPurchase->find('count', array(
					'fields'=>array('PurPurchase.note_code'),
					'conditions'=>array(
						'PurPurchase.note_code'=>trim($noteCode),
						'NOT'=>array('PurPurchase.lc_state'=>array('ORDER_LOGIC_DELETED','PINVOICE_LOGIC_DELETED','ORDER_CANCELLED','PINVOICE_CANCELLED','DRAFT'))
                                             ,'PurPurchase.clone'=>'NO'
					)
				));
			}else{
				$result = $this->PurPurchase->find('count', array(
					'fields'=>array('PurPurchase.note_code'),
					'conditions'=>array(
						'PurPurchase.code !='=>$genericCode,
						'PurPurchase.note_code'=>trim($noteCode),
						'NOT'=>array('PurPurchase.lc_state'=>array('ORDER_LOGIC_DELETED','PINVOICE_LOGIC_DELETED','ORDER_CANCELLED','PINVOICE_CANCELLED','DRAFT'))
                                             ,'PurPurchase.clone'=>'NO'
					)
				));
			}
			echo $result;
		}
	}
	
	public function ajax_check_code_duplicity_pays_coherency(){	
		if($this->RequestHandler->isAjax()){
			$noteCode = $this->request->data['noteCode']; //23456
			$genericCode = $this->request->data['genericCode']; //COM
			if ($genericCode == ''){
				$result = $this->PurPurchase->find('count', array(
					'fields'=>array('PurPurchase.note_code'),
					'conditions'=>array(
						'PurPurchase.note_code'=>trim($noteCode),
						'NOT'=>array('PurPurchase.lc_state'=>array('ORDER_LOGIC_DELETED','PINVOICE_LOGIC_DELETED','ORDER_CANCELLED','PINVOICE_CANCELLED','DRAFT'))
                                               ,'PurPurchase.clone'=>'NO'
					)
				));
			}else{
				$result = $this->PurPurchase->find('count', array(
					'fields'=>array('PurPurchase.note_code'),
					'conditions'=>array(
						'PurPurchase.code !='=>$genericCode,
						'PurPurchase.note_code'=>trim($noteCode),
						'NOT'=>array('PurPurchase.lc_state'=>array('ORDER_LOGIC_DELETED','PINVOICE_LOGIC_DELETED','ORDER_CANCELLED','PINVOICE_CANCELLED','DRAFT'))
                                                ,'PurPurchase.clone'=>'NO'
					)
				));
			}
                        print_r($result);
                        die();
			////////////////////////////////////////////////////////////
			$docCode = $this->request->data['docCode'];
			$discountType = current($this->PurPurchase->find('list', array(
				'fields' => array('PurPurchase.discount_type'),
				'conditions' => array('PurPurchase.doc_code' => $docCode)
			)));
			if($discountType == 'PERCENT'){
				$discountString = '- (SUM("PurDetail"."ex_subtotal") * "PurPurchase"."discount"/100)';
			}else if($discountType == 'USD'){
				$discountString = '- "PurPurchase"."discount"';
			}else if($discountType == 'NONE'){
				$discountString = '';
			}
			$total = $this->PurPurchase->PurDetail->find('first', array(
//              'fields' => array('(ROUND(SUM("PurDetail"."ex_subtotal") - (SUM("PurDetail"."ex_subtotal") * "PurPurchase"."discount"/100),2)) AS total'),
				'fields' => array('(ROUND(SUM("PurDetail"."ex_subtotal")' .$discountString. ',2)) AS total'),
                'conditions' => array('PurPurchase.doc_code' => $docCode),
                'group' => array('PurPurchase.discount')
            ));
            $paid = $this->PurPurchase->PurPayment->find('first', array(
                'fields' => array('(ROUND(SUM("PurPayment"."amount"),2)) AS paid'),
                'conditions' => array('PurPurchase.doc_code' => $docCode)
            ));
			$payDebt = number_format((float)$total[0]['total'] - $paid[0]['paid'], 2, '.', '');
			
			echo $result.'|'.$payDebt;
		}
	}
	
	//aca tendria que poder calcular el pago adeudado en base a los pagos guardados
//	public function ajax_update_pay(){
//		if($this->RequestHandler->isAjax()){
//			$pay = $this->request->data['pay'];
//			$amountDirty = $this->PurPurchase->PurPrice->find('first', array(
//			'fields'=>array('PurPrice.amount'),
//			'conditions'=>array(
//				'PurPrice.inv_price_type_id'=>$cost
//				)
//			));
//			if($amountDirty==array()){
//			$amount = 0;
//		}  else {
//			$amount = $amountDirty['PurPrice']['amount'];
//		}
//			$this->set(compact('amount'));
//		}
//	}
	
	public function ajax_save_movement(){
		if($this->RequestHandler->isAjax()){
//                    print_r($this->request->data['cloneStatus']);
//                    die();
//			$arrayPurchaseOrder = array();
			$arrayPurchaseInvoice = array();
			$arrayMovement = array();
			////////////////////////////////////////////START - RECIEVE AJAX////////////////////////////////////////////////////////
			//For making algorithm
			$ACTION = $this->request->data['ACTION'];////////save_order/////////save_invoice//////////
			$OPERATION= $this->request->data['OPERATION'];////////////DEFAULT//////////ADD/////////EDIT///////////DELETE//////////
			$STATE = $this->request->data['STATE'];//also for Movement////////ORDER_PENDANT//////////ORDER_APPROVED////////ORDER_CANCELLED////////PINVOICE_PENDANT//////////PINVOICE_APPROVED///////PINVOICE_CANCELLED///////PINVOICE_PAY
			$cloneStatus = $this->request->data['cloneStatus'];
//			$OPERATION3 = $OPERATION;
//                        if(strpos($cloneStatus,'COM') === false){    
                            //For validate before approve OUT or cancelled IN
                            $arrayForValidate = array();
                            if (isset($this->request->data['arrayForValidate'])) {
                                $arrayForValidate = $this->request->data['arrayForValidate'];
//                							print_r($arrayForValidate);
//                			die();
                            }
//                        }
			//Purchase Header
			$purchaseId = $this->request->data['purchaseId'];
//			$purchaseOrderDocCode = $this->request->data['movementDocCode'];/// ORD-
			$purchaseInvoiceDocCode = $this->request->data['movementDocCode'];/// ORD-
			$purchaseCode = $this->request->data['movementCode'];/// COM-
			$noteCode = $this->request->data['noteCode'];
                        
			$date = $this->request->data['date'];
			$warehouseId = $this->request->data['warehouseId'];
//                        print_r($warehouseId);
			$dataMovement = array();
			$this->loadModel('InvMovement');
                        $arrayForValidateOrig = array();
//			if (strpos($noteCode,'CLON') !== false && $ACTION == 'save_invoice' && $STATE == 'PINVOICE_APPROVED') {
			if (strpos($cloneStatus,'COM') !== false && $ACTION == 'save_invoice' && $STATE == 'PINVOICE_APPROVED') {
//                            print_r($cloneStatus);
				$idOriginalPurchase = $this->PurPurchase->find('list', array(
					'fields'=>array(
						'PurPurchase.id','PurPurchase.code'),
					'conditions'=>array(
//						'PurPurchase.note_code'=>$noteCode
						'PurPurchase.lc_state'=>"PINVOICE_APPROVED"
                                                ,'PurPurchase.code'=>$cloneStatus
						)						
				));
//                                print_r($idOriginalPurchase);
//                                $arrayForValidateOrig = $this->PurPurchase->PurDetail->find('all', array(
//					'fields'=>array(
//						'PurPurchase.inv_warehouse_id','PurDetail.inv_item_id','PurDetail.quantity'),
//					'conditions'=>array(
//						'PurDetail.pur_purchase_id'=>key($idOriginalPurchase))						
//				));
                                $warehouseIdOrig = $this->PurPurchase->find('list', array(
					'fields'=>array(
						'PurPurchase.inv_warehouse_id'),
					'conditions'=>array(
						'PurPurchase.id'=>key($idOriginalPurchase))						
				));
                                //if it is different to the actual warehouse it doesn't need to send or do V
//                                print_r('('.$warehouseId.' = '.current($warehouseIdOrig).')');
                                
                                if($warehouseId == current($warehouseIdOrig)){//Same Warehouse (arrayForValidate != array(), arrayForValidateOrig != array())
                                    $arrayForValidateOrig = $this->PurPurchase->PurDetail->find('list', array(
                                            'fields'=>array(
                                                    'PurDetail.inv_item_id','PurDetail.quantity'),
                                            'conditions'=>array(
                                                    'PurDetail.pur_purchase_id'=>key($idOriginalPurchase))						
                                    ));
                                    
                                }else{//When warehouse is changed (arrayForValidate == array(), arrayForValidateOrig != array())
                                    $array = $this->PurPurchase->PurDetail->find('all', array(
                                            'fields'=>array(
                                                    'PurDetail.inv_item_id','PurDetail.quantity'),
                                            'conditions'=>array(
                                                    'PurDetail.pur_purchase_id'=>key($idOriginalPurchase))						
                                    ));
                                    $arrayForValidate = array();
                                    foreach ($array as $value) {
                                        $arrayForValidateOrig[] = array('inv_item_id'=>$value['PurDetail']['inv_item_id'],'quantity'=>$value['PurDetail']['quantity']); 
                                    }
//                                    print_r('ddd');
                                    $arrayPurchaseOrigInvoice['inv_warehouse_id']=current($warehouseIdOrig);
//                                    print_r($warehouseId.current($warehouseIdOrig));
//                                    die();
                                }
//				print_r($arrayForValidateOrig);
				$idOriginalMovement = $this->InvMovement->find('list', array(
					'fields'=>array(
						'InvMovement.id'),
					'conditions'=>array(
						'InvMovement.document_code'=>current($idOriginalPurchase)
						)						
				));
//				$noteCode = current(explode(" (CLON)",$noteCode));
				
				$arrayPurchaseOrigInvoice['id']=key($idOriginalPurchase);
				$arrayPurchaseOrigInvoice['lc_state']='PINVOICE_CANCELLED';
                                if($STATE != 'PINVOICE_PENDANT'){
                                    $arrayPurchaseOrigInvoice['clone']='NO';
                                }
                                $arrayPurchaseOrigInvoice['code_for_prices']=current($idOriginalPurchase);

				$dataPurchase[1] = array('PurPurchase'=>$arrayPurchaseOrigInvoice);

				$arrayOriginalMovement['id']=current($idOriginalMovement);
				$arrayOriginalMovement['lc_state']='CANCELLED';

				$dataMovement[1] = array('InvMovement'=>$arrayOriginalMovement);
			}
			
//			print_r($noteCode);
//			print_r($idOriginalPurchase);
//			print_r(key($idOriginalPurchase));
//			print_r(current($idOriginalPurchase));
//				print_r($idOriginalMovement);
//						die();
			
			$this->loadModel('InvWarehouse');
			$location = $this->InvWarehouse->find('list', array(
			'fields'=>array(
				'InvWarehouse.location'),
			'conditions'=>array(
				'InvWarehouse.id'=>$warehouseId
				)	
			));
			
			$description = $this->request->data['description'];
			$exRate = $this->request->data['exRate'];
			$discountType = $this->request->data['discountType'];
			if($discountType==1){$discountTypeName='NONE';}else if($discountType == 2){$discountTypeName='PERCENT';}else/*if($discountTypeName == 'BOB')*/{$discountTypeName='USD';}
            $discount = $this->request->data['discount'];
			//Purchase Details
			$supplierId = $this->request->data['supplierId'];
			$itemId = $this->request->data['itemId'];
			$quantity = $this->request->data['quantity'];
			$exSubtotal = $this->request->data['exSubtotal'];
			if($quantity > 0){
				$exFobPrice = $exSubtotal / $quantity;
			}else{
				$exFobPrice = 0;
			}
			$fobPrice = $exFobPrice * $exRate;
			if ($ACTION == 'save_invoice' && $STATE == 'PINVOICE_APPROVED'){
				//variables used to calculate apportionment assigned when Invoice is APPROVED
				$arrayItemsDetails = $this->request->data['arrayItemsDetails'];	
//				$total = $this->request->data['total'];
//				$totalCost = $this->request->data['totalCost'];
				
				$total = $this->PurPurchase->PurDetail->find('first', array(
					'fields' => array('(SUM("PurDetail"."ex_subtotal")) AS total'),
					'conditions' => array('PurPurchase.doc_code' => $purchaseInvoiceDocCode),
//					'group' => array('PurPurchase.discount')
				));
				$total = $total[0]['total'];
				
				$totalCost = $this->PurPurchase->PurPrice->find('first', array(
					'fields' => array('(SUM("PurPrice"."ex_amount")) AS total_cost'),
					'conditions' => array('PurPurchase.doc_code' => $purchaseInvoiceDocCode),
//					'group' => array('PurPurchase.discount')
				));
				$totalCost = $totalCost[0]['total_cost'];
			}
			if (($ACTION == 'save_invoice' && $OPERATION == 'ADD_PAY') || ($ACTION == 'save_invoice' && $OPERATION == 'EDIT_PAY') || ($ACTION == 'save_invoice' && $OPERATION == 'DELETE_PAY')) {
				//variables used to save Pays assigned on Invoice
				$payDate = $this->request->data['payDate'];
				$payAmount = $this->request->data['payAmount'];
				$payDescription = $this->request->data['payDescription'];
			}
			if (($ACTION == 'save_invoice' && $OPERATION == 'ADD_COST') || ($ACTION == 'save_invoice' && $OPERATION == 'EDIT_COST') || ($ACTION == 'save_invoice' && $OPERATION == 'DELETE_COST')) {
				//variables used to save Costs assigned on Invoice
				$costId = $this->request->data['costId'];
				$costExAmount = $this->request->data['costExAmount'];
			}
			//Internal variables
			$error=0;
//			$purchaseInvoiceDocCode = '';
			////////////////////////////////////////////END - RECIEVE AJAX////////////////////////////////////////////////////////
			
			////////////////////////////////////////////////START - SET DATA/////////////////////////////////////////////////////
			//header for ORDER // or header for INVOICE when save_invoice
//			$arrayPurchaseOrder['note_code']=$noteCode;
//			$arrayPurchaseOrder['date']=$date;
//			$arrayPurchaseOrder['inv_warehouse_id']=$warehouseId;
//			$arrayPurchaseOrder['description']=$description;
//			$arrayPurchaseOrder['ex_rate']=$exRate;
//			$arrayPurchaseOrder['discount']=$discount;
//			$arrayPurchaseOrder['lc_state']=$STATE;
			
			$arrayPurchaseInvoice['note_code']=$noteCode;
			$arrayPurchaseInvoice['date']=$date;
			$arrayPurchaseInvoice['inv_warehouse_id']=$warehouseId;
			$arrayPurchaseInvoice['location']=current($location);
			$arrayPurchaseInvoice['description']=$description;
			$arrayPurchaseInvoice['ex_rate']=$exRate;
			$arrayPurchaseInvoice['discount_type']=$discountTypeName;
			$arrayPurchaseInvoice['discount']=$discount;
                        if($STATE != 'PINVOICE_PENDANT'){
                            $arrayPurchaseInvoice['clone']='NO';
                        }    
			if($STATE != 'PINVOICE_PAY'){
				$arrayPurchaseInvoice['lc_state']=$STATE;
			}
//			if ($ACTION == 'save_order'){
			
		
			
//			$arrayPurchaseInvoice['paid'] = false;
			if(($ACTION == 'save_invoice' && $OPERATION == 'ADD_PAY') || ($ACTION == 'save_invoice' && $OPERATION == 'EDIT_PAY') || ($ACTION == 'save_invoice' && $OPERATION == 'DELETE_PAY')){			
				
				$currentState = $this->PurPurchase->find('list', array(
				'fields'=>array('PurPurchase.lc_state'),
				'conditions'=>array(
					'PurPurchase.id'=>$purchaseId,
					'PurPurchase.doc_code'=>$purchaseInvoiceDocCode
					)
				));
//				debug(reset($currentState));
//				debug(current($currentState));
//				die();
				if(current($currentState) == 'PINVOICE_APPROVED'){
					///////////////////****************//////////////////********************////////////////////
					$discountType = current($this->PurPurchase->find('list', array(
						'fields' => array('PurPurchase.discount_type'),
						'conditions' => array('PurPurchase.doc_code' => $purchaseInvoiceDocCode)
					)));
					if($discountType == 'PERCENT'){
						$discountString = '- (SUM("PurDetail"."ex_subtotal") * "PurPurchase"."discount"/100)';
					}else if($discountType == 'USD'){
						$discountString = '- "PurPurchase"."discount"';
					}else if($discountType == 'NONE'){
						$discountString = '';
					}
					$total = $this->PurPurchase->PurDetail->find('first', array(
		//              'fields' => array('(ROUND(SUM("PurDetail"."ex_subtotal") - (SUM("PurDetail"."ex_subtotal") * "PurPurchase"."discount"/100),2)) AS total'),
						'fields' => array('(ROUND(SUM("PurDetail"."ex_subtotal")' .$discountString. ',2)) AS total'),
						'conditions' => array('PurPurchase.doc_code' => $purchaseInvoiceDocCode),
						'group' => array('PurPurchase.discount')
					));	
					$paid = $this->PurPurchase->PurPayment->find('first', array(
						'fields' => array('(ROUND(SUM("PurPayment"."amount"),2)) AS paid'),
						'conditions' => array('NOT'=>array('PurPayment.date'=>$payDate),
											'PurPurchase.doc_code' => $purchaseInvoiceDocCode
							)
					));				
	//				$payDebt = $total[0]['total'] - $paid[0]['paid'];
					$payDebt = number_format((float)$total[0]['total'] - $paid[0]['paid'], 2, '.', '');
					if($payAmount == $payDebt){
						$arrayPurchaseInvoice['paid'] = true;
					}else{
						$arrayPurchaseInvoice['paid'] = false;
					}
					///////////////////****************//////////////////********************////////////////////
				}
//				else{
//					$arrayPurchaseInvoice['paid'] = false;
//				}

//				debug($payDate);
//				
//				debug($payDebt);
//				debug($payAmount);
//				debug($arrayPurchaseInvoice['paid']);
//				die();
				//pay details for INVOICE
				$arrayPayDetails = array('pur_payment_type_id'=>1,//Efectivo(Contado?) 
										'date'=>$payDate,
										'description'=>$payDescription,
										'amount'=>$payAmount, 
										'ex_amount'=>($payAmount / $exRate)
										);
			}elseif(($ACTION == 'save_invoice' && $OPERATION == 'ADD_COST') || ($ACTION == 'save_invoice' && $OPERATION == 'EDIT_COST') || ($ACTION == 'save_invoice' && $OPERATION == 'DELETE_COST')){
				//cost details for INVOICE
				$arrayCostDetails = array('inv_price_type_id'=>$costId,
										'ex_amount'=>$costExAmount, 
										'amount'=>($costExAmount * $exRate),
										'date'=>($date)
										);
			}elseif ($ACTION == 'save_invoice'){
//				//header for INVOICE
//				$arrayPurchaseInvoice['note_code']=$noteCode;
//				$arrayPurchaseInvoice['date']=$date;
//				$arrayPurchaseInvoice['inv_warehouse_id']=$warehouseId;
//				$arrayPurchaseInvoice['description']=$description;
//				$arrayPurchaseInvoice['ex_rate']=$exRate;
//				$arrayPurchaseInvoice['discount']=$discount;
				
//				if ($STATE == 'ORDER_APPROVED') {//CUANDO LA ORDEN SE APRUEBA
				if ($STATE == 'PINVOICE_APPROVED') {
					///////////////////****************//////////////////********************////////////////////
					$discountType = current($this->PurPurchase->find('list', array(
						'fields' => array('PurPurchase.discount_type'),
						'conditions' => array('PurPurchase.doc_code' => $purchaseInvoiceDocCode)
					)));
					if($discountType == 'PERCENT'){
						$discountString = '- (SUM("PurDetail"."ex_subtotal") * "PurPurchase"."discount"/100)';
					}else if($discountType == 'USD'){
						$discountString = '- "PurPurchase"."discount"';
					}else if($discountType == 'NONE'){
						$discountString = '';
					}
					$totalDebt = $this->PurPurchase->PurDetail->find('first', array(
						'fields' => array('(ROUND(SUM("PurDetail"."ex_subtotal")' .$discountString. ',2)) AS total'),
						'conditions' => array('PurPurchase.doc_code' => $purchaseInvoiceDocCode),
						'group' => array('PurPurchase.discount')
					));	
					$paid = $this->PurPurchase->PurPayment->find('first', array(
						'fields' => array('(ROUND(SUM("PurPayment"."amount"),2)) AS paid'),
						'conditions' => array('PurPurchase.doc_code' => $purchaseInvoiceDocCode)
					));	
//					$payDebt = number_format((float)$total[0]['total'] - $paid[0]['paid'], 2, '.', '');
//					if($payAmount == $payDebt){
					if($paid[0]['paid'] == $totalDebt[0]['total']){
						$arrayPurchaseInvoice['paid'] = true;
					}else{
						$arrayPurchaseInvoice['paid'] = false;
					}
					///////////////////****************//////////////////********************////////////////////
//					$arrayPurchaseInvoice['lc_state']='PINVOICE_PENDANT';
//					$arrayMovement['lc_state'] EL PENDANT SE PONE EN BATCH POR SI EXISTEN VARIAS CABECERAS DE MOVEMENT
//				}elseif ($STATE == 'ORDER_PENDANT') {
				}else if ($STATE == 'PINVOICE_PENDANT') {
//					$arrayPurchaseInvoice['lc_state']='DRAFT';
					$arrayMovement['lc_state']='DRAFT';
				}
			}
//			elseif($ACTION == 'save_invoice'){
//				if ($STATE == 'PINVOICE_PENDANT') {
//					$arrayMovement['lc_state']='PENDANT';//ESTO ESTA SOBREESCRITO POR LO Q DIGA $arrayMovementHeadsUpd
//				}
//				if ($STATE == 'PINVOICE_CANCELLED') {
//					$arrayMovement['lc_state']='CANCELLED';//ESTO ESTA SOBREESCRITO POR LO Q DIGA $arrayMovementHeadsUpd
//				}
//			}
			
			//header for MOVEMENT
			$arrayMovement['date']=$date;
			$arrayMovement['inv_warehouse_id']=$warehouseId;
			$arrayMovement['inv_movement_type_id']=1; //Reynaldo Rojas Compra = 1
			$arrayMovement['description']=$description;
			$arrayMovement['type']=1;//NON BACKORDER
			//item details for MOVEMENT
			$arrayMovementDetails = array('inv_item_id'=>$itemId, 'quantity'=>$quantity);
			//item details for ORDER & INVOICE
			$arrayPurchaseDetails = array('inv_supplier_id'=>$supplierId,  
										'inv_item_id'=>$itemId,
										'ex_fob_price'=>$exFobPrice, 'fob_price'=>$fobPrice,
										'quantity'=>$quantity,
										'ex_subtotal'=>$exSubtotal);

			/////////////////////////////////////////////////INSERT OR UPDATE
			if($purchaseId == ''){//INSERT
//				if($ACTION == 'save_order'){
					//ORDER
					$purchaseCode = $this->_generate_code('COM');
//					$purchaseOrderDocCode = $this->_generate_doc_code('ORD');
					$purchaseInvoiceDocCode = $this->_generate_doc_code('CFA');
//					$arrayPurchaseOrder['code'] = $purchaseCode;
//					$arrayPurchaseOrder['doc_code'] = $purchaseOrderDocCode;
					$arrayPurchaseInvoice['code'] = $purchaseCode;
					$arrayPurchaseInvoice['doc_code'] = $purchaseInvoiceDocCode;
					
					$arrayPurchaseInvoice['paid'] = false;
					//INVOICE
//					$purchaseInvoiceDocCode = 'NO';
//					$arrayPurchaseInvoice['code'] = $purchaseCode;
					$arrayPurchaseInvoice['clone'] = 'NO';
					//MOVEMENT type 1(hay stock)
					$arrayMovement['document_code'] = $purchaseCode;
					$arrayMovement['code'] = 'NO';
//				}	
//			if($purchaseOrderDocCode == 'error'){$error++;}	
			if($purchaseInvoiceDocCode == 'error'){$error++;}		
			}else{//UPDATE
				//ORDER id
//				$arrayPurchaseOrder['id'] = $purchaseId;
				$arrayPurchaseInvoice['id'] = $purchaseId;
//				if ($ACTION == 'save_order'){
				if ($ACTION == 'save_invoice'){
					//gets INVOICE id
//					$arrayPurchaseInvoice['id'] = $this->_get_doc_id($purchaseId, $purchaseCode, null);
					//gets MOVEMENT id type 1(hay stock)
					$arrayMovement['id'] = $this->_get_doc_id(null, $purchaseCode, 1);
//					if ($STATE == 'ORDER_APPROVED') {
					if ($STATE == 'PINVOICE_APPROVED') {	
						//FOR INVOICE
//						$purchaseInvoiceDocCode = $this->_generate_doc_code('CFA');
//						$arrayPurchaseInvoice['doc_code'] = $purchaseInvoiceDocCode;
//						$arrayMovement['code'] EL code SE PONE EN BATCH POR SI EXISTEN VARIAS CABECERAS DE MOVEMENT
					}
				}
//				elseif ($ACTION == 'save_invoice' && $OPERATION != 'ADD_PAY' && $OPERATION != 'EDIT_PAY' && $OPERATION != 'DELETE_PAY' && $OPERATION != 'ADD_COST' && $OPERATION != 'EDIT_COST' && $OPERATION != 'DELETE_COST') {
//					//movement id type 1(hay stock)
//					$arrayMovement['id'] = $this->_get_doc_id(null, $purchaseCode, 1);
//				}
//				if($purchaseInvoiceDocCode == 'error'){$error++;}	
			}
			if($purchaseCode == 'error'){$error++;}		
			/////////////////////////////////////////////////INSERT OR UPDATE			
			
			/////////////////////////////////////////////////FOR DELETING THE HEAD (inv_movements) WHEN IT LAST ITEM DETAIL IS DELETED ON save_order (when MOVEMENT HEAD is DRAFT)
//			$arrayMovement6 = array();
////			if(($ACTION == 'save_order' && $OPERATION3 == 'DELETE')){
//			if(($ACTION == 'save_invoice' && $OPERATION3 == 'DELETE')){
//				if (($arrayMovement['id'] != array())||($arrayMovementDetails['inv_item_id'] != array())){
//					$rest3 = $this->InvMovement->InvMovementDetail->find('count', array(
//						'conditions'=>array(
//							'NOT'=>array(
//								'AND'=>array(
//									'InvMovementDetail.inv_movement_id'=>$arrayMovement['id']
//									,'InvMovementDetail.inv_item_id'=>$arrayMovementDetails['inv_item_id']
//									)
//								)
//							,'InvMovementDetail.inv_movement_id'=>$arrayMovement['id']
//							),
//						'recursive'=>0
//					));
//				}
//					if(($rest3 == 0) && ($arrayMovement['id'] != array())){
//					$arrayMovement6 = array(
//						array('InvMovement.id' => $arrayMovement['id'])
//					);
//				}
//			}
			/////////////////////////////////////////////////FOR DELETING HEAD ON MOVEMENTS RELATED ON save_order
			
			/////////////////////////////////////////////////RETURNS THE ID OF THE MOVEMENT HEAD FOR UPDATING () THE HEAD (inv_movements) WHEN IT LAST ITEM DETAIL IS DELETED ON save_invoice
//			$draftId3 = array();
//			if(($ACTION == 'save_invoice' && $OPERATION3 == 'DELETE')){
//				if (($arrayMovement['id'] != array())||($arrayMovementDetails['inv_item_id'] != array())){
//					$rest3 = $this->InvMovement->InvMovementDetail->find('count', array(
//						'conditions'=>array(
//							'NOT'=>array(
//								'AND'=>array(
//									'InvMovementDetail.inv_movement_id'=>$arrayMovement['id']
//									,'InvMovementDetail.inv_item_id'=>$arrayMovementDetails['inv_item_id']
//									)
//								)
//							,'InvMovementDetail.inv_movement_id'=>$arrayMovement['id']
//							),
//						'recursive'=>0
//					));
//				}
//					if(($rest3 == 0) && ($arrayMovement['id'] != array())){
//					$draftId3 = $arrayMovement['id'];
//				}
//			}
			/////////////////////////////////////////////////FOR UPDATING HEAD ON DELETED MOVEMENTS ON save_invoice
						
			/////////////////////////////////////////////////
//				$this->loadModel('InvMovement');
//				if($STATE == 'ORDER_APPROVED'){
				if($STATE == 'PINVOICE_APPROVED'){
					$arrayMovementHeadsUpd = $this->InvMovement->find('all', array(
						'fields'=>array(
							'InvMovement.id'
							),
						'conditions'=>array(
							'InvMovement.document_code'=>$purchaseCode
//							,'InvMovement.lc_state !='=>'CANCELLED'
//							,'InvMovement.lc_state !='=>'LOGIC_DELETED'
							,'NOT'=>array('InvMovement.lc_state'=>array('CANCELLED','LOGIC_DELETED'))
							)
						,'order' => array('InvMovement.id' => 'ASC')
						,'recursive'=>0
					));
				}else{
					$arrayMovementHeadsUpd = $this->InvMovement->find('all', array(
						'fields'=>array(
							'InvMovement.id'
							),
						'conditions'=>array(
							'InvMovement.document_code'=>$purchaseCode
//							,'InvMovement.lc_state !='=>'CANCELLED'
//							,'InvMovement.lc_state !='=>'LOGIC_DELETED'//por que al grabar de la factura al movimiento no usa el guardado en batch
							,'NOT'=>array('InvMovement.lc_state'=>array('CANCELLED','LOGIC_DELETED'))
							)
						,'order' => array('InvMovement.id' => 'ASC')
						,'recursive'=>0
					));
				}
				//FOR UPDATING INVOICE AND MOVEMENT HEADS WHEN THE ORDER IS CANCELLED
//				if(($arrayMovementHeadsUpd <> array())&&($STATE == 'ORDER_CANCELLED')){
//					for($i=0;$i<count($arrayMovementHeadsUpd);$i++){
//						$arrayMovementHeadsUpd[$i]['InvMovement']['lc_state'] = 'LOGIC_DELETED';////////////////////////////////////////////////////////Needs Restrictions
//					}
//					$arrayPurchaseInvoice['lc_state']='PINVOICE_LOGIC_DELETED';////////////////////////////////////////////////////////Needs Restrictions
//				//FOR UPDATING MOVEMENT HEAD WHEN THE ORDER IS APPROVED				
//				}else
				if(($arrayMovementHeadsUpd <> array())&&($STATE == 'PINVOICE_APPROVED')) {	
					for($i=0;$i<count($arrayMovementHeadsUpd);$i++){
						$movementDocCode5 = $this->_generate_movement_code('ENT','inc');
						$arrayMovementHeadsUpd[$i]['InvMovement']['lc_state']='APPROVED';
						$arrayMovementHeadsUpd[$i]['InvMovement']['code'] = $movementDocCode5;
						$arrayMovementHeadsUpd[$i]['InvMovement']['date'] = $date;
						$arrayMovementHeadsUpd[$i]['InvMovement']['description'] = $description;
					}
				//FOR UPDATING MOVEMENT HEAD WHEN ORDER (NOT INVOICE) HEAD IS MODIFIED 
				}
//				else
					if($arrayMovementHeadsUpd <> array()){
					for($i=0;$i<count($arrayMovementHeadsUpd);$i++){
						$arrayMovementHeadsUpd[$i]['InvMovement']['date'] = $date;
						$arrayMovementHeadsUpd[$i]['InvMovement']['description'] = $description;
						if($STATE == 'PINVOICE_CANCELLED'){
							$arrayMovementHeadsUpd[$i]['InvMovement']['lc_state'] = 'CANCELLED';
						}	
						/////////////////////////////////////////////////////////////////FOR UPDATING MOVEMENTS WHEN IT LAST ITEM DETAIL IS DELETED ON save_invoice
//						if(($ACTION == 'save_invoice' && $OPERATION3 == 'DELETE')){		
//							if($arrayMovementHeadsUpd[$i]['InvMovement']['id'] === $draftId3){
//								$arrayMovementHeadsUpd[$i]['InvMovement']['lc_state']='LOGIC_DELETED';
//							}
//						}	
						/////////////////////////////////////////////////////////////////
					}
				}
			/////////////////////////////////////////////////
						
//			$dataMovement = array();
			$dataMovementDetail = array();
			$dataMovementHeadsUpd = array();
			//for ORDER	when save_order / INVOICE when save_invoice
//			$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseOrder);
			$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseInvoice);
//			if ($ACTION == 'save_order' && $STATE == 'ORDER_CANCELLED'){
//				$dataPayDetail = array();
//				$dataCostDetail = array();
//			}else
//				if ($ACTION == 'save_order'){
			
			if (($ACTION == 'save_invoice' && $OPERATION == 'ADD_PAY') || ($ACTION == 'save_invoice' && $OPERATION == 'EDIT_PAY') || ($ACTION == 'save_invoice' && $OPERATION == 'DELETE_PAY')) {
				$dataPayDetail = array('PurPayment'=> $arrayPayDetails);
				if($arrayMovementHeadsUpd <> array()){
					$dataMovementHeadsUpd = $arrayMovementHeadsUpd;
				}	
				$dataCostDetail = array();
			}elseif (($ACTION == 'save_invoice' && $OPERATION == 'ADD_COST') || ($ACTION == 'save_invoice' && $OPERATION == 'EDIT_COST') || ($ACTION == 'save_invoice' && $OPERATION == 'DELETE_COST')) {
				$dataCostDetail = array('PurPrice'=> $arrayCostDetails);
				if($arrayMovementHeadsUpd <> array()){	
					$dataMovementHeadsUpd = $arrayMovementHeadsUpd;
				}	
				$dataPayDetail = array();
			}elseif ($ACTION == 'save_invoice'){
				$this->loadModel('InvMovement');
				//for INVOICE
//				$dataPurchase[1] = array('PurPurchase'=>$arrayPurchaseInvoice);
				//for MOVEMENT
				$dataMovement[0] = array('InvMovement'=>$arrayMovement);
				//for MOVEMENT Details
				$dataMovementDetail = array('InvMovementDetail'=> $arrayMovementDetails);
				if($arrayMovementHeadsUpd <> array()){
					$dataMovementHeadsUpd = $arrayMovementHeadsUpd;
				}	
//				if($ACTION == 'save_order' && $OPERATION3 == 'DELETE' && $arrayMovement6 <> array() ){	
//				if($ACTION == 'save_invoice' && $OPERATION3 == 'DELETE' && $arrayMovement6 <> array() ){
//					$dataMovement6 = $arrayMovement6;
//				}	
				$dataPayDetail = array();
				$dataCostDetail = array();
			}
//			
//			elseif ($ACTION == 'save_invoice') {
//				$this->loadModel('InvMovement');
//				$dataMovement = array('InvMovement'=>$arrayMovement);
//				$dataMovementDetail = array('InvMovementDetail'=> $arrayMovementDetails);
//				if($arrayMovementHeadsUpd <> array()){	
//					$dataMovementHeadsUpd = $arrayMovementHeadsUpd;
//				}	
//				if($ACTION == 'save_order' && $OPERATION3 == 'DELETE' && $arrayMovement6 <> array() ){	
//					$dataMovement6 = $arrayMovement6;
//				}	
//				$dataPayDetail = array();
//				$dataCostDetail = array();
//			}
			$dataPurchaseDetail[0] = array('PurDetail'=> $arrayPurchaseDetails);
//			if ($ACTION == 'save_order'){
//				$dataPurchaseDetail[1] = array('PurDetail'=> $arrayPurchaseDetails);
//			}
			////////////////////////////////////////////////END - SET DATA//////////////////////////////////////////////////////
			
			////////////////////////////////////////////////START - HISTORY PRICES//////////////////////////////////////////////////////
				$arrayFobPrices = array();
				$arrayCifPrices = array();
				if ($ACTION == 'save_invoice' && $STATE == 'PINVOICE_APPROVED'){					
					for($i=0;$i<count($arrayItemsDetails);$i++){
						$arrayItemsDetailsIds[$i] = $arrayItemsDetails[$i]['inv_item_id']; 
					}
					
					$this->loadModel('InvPrice');
					$prices = $this->InvPrice->find('all', array(
					'fields'=>array(
						'InvPrice.inv_item_id'
						,'InvPrice.inv_price_type_id'
						,'InvPrice.ex_price'
						,'InvPrice.date'
						),
					'order' => 'InvPrice.date ASC',
					'conditions'=>array(
						'InvPrice.date <='=>$date
						,'InvPrice.inv_item_id'=>$arrayItemsDetailsIds	
						)
					));
					
					$date2Compare = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $date)));
					$arrayLastFobPrices = array();
					$arrayLastCifPrices = array();
					if($prices != array()){
						$lastFobPrices = array();
						$lastCifPrices = array();
						for($i=0;$i<count($arrayItemsDetailsIds);$i++){
							for($j=0;$j<count($prices);$j++){
								if($arrayItemsDetailsIds[$i] == $prices[$j]['InvPrice']['inv_item_id'] && $prices[$j]['InvPrice']['inv_price_type_id'] == 1){
									$lastFobPrices[$i] = $prices[$j];
								}
								if($arrayItemsDetailsIds[$i] == $prices[$j]['InvPrice']['inv_item_id'] && $prices[$j]['InvPrice']['inv_price_type_id'] == 8){
									$lastCifPrices[$i] = $prices[$j];
								}
							}
						}
//						foreach($lastFobPrices as $val){
//							if($date2Compare != $val['InvPrice']['date']){
//								$arrayLastFobPrices[] = $val;
//							}
//						}
//						foreach($lastCifPrices as $val){
//							if($date2Compare != $val['InvPrice']['date']){
//								$arrayLastCifPrices[] = $val;
//							}
//						}
//						$arrayLastFobPrices = array_values($lastFobPrices);
//						$arrayLastCifPrices = array_values($lastCifPrices);
					}
					
					$arrayFobPrices = array();
					$arrayCifPrices = array();
					$perc = $totalCost/$total;
					$year = $this->Session->read('Period.name');
					for($i=0;$i<count($arrayItemsDetails);$i++){
						$sameFobPriceOrDate = 'no';
						$firstOneFob = 'yes';
						if($arrayLastFobPrices != array()){
							for($j=0;$j<count($arrayLastFobPrices);$j++){
//								if($arrayItemsDetails[$i]['inv_item_id'] == $arrayLastFobPrices[$j]['InvPrice']['inv_item_id'] && $arrayLastFobPrices[$j]['InvPrice']['ex_price'] == $arrayItemsDetails[$i]['ex_fob_price'] ){
//									$contFob = 1;
//								}
								if($arrayItemsDetails[$i]['inv_item_id'] == $arrayLastFobPrices[$j]['InvPrice']['inv_item_id']){
									$firstOneFob = 'no';
									if($arrayLastFobPrices[$j]['InvPrice']['ex_price'] == $arrayItemsDetails[$i]['ex_fob_price'] OR $date2Compare == $arrayLastFobPrices[$j]['InvPrice']['date']){
										$sameFobPriceOrDate = 'yes';
									}
								}
							}
						}
						
						if($sameFobPriceOrDate == 'no'){
							$arrayFobPrices[$i]['inv_item_id'] = $arrayItemsDetails[$i]['inv_item_id'];
							$arrayFobPrices[$i]['inv_price_type_id'] = 1;//or better relate by name FOB
							$arrayFobPrices[$i]['ex_rate'] = $exRate;
							$arrayFobPrices[$i]['ex_price'] = $arrayItemsDetails[$i]['ex_fob_price'];
							$arrayFobPrices[$i]['price'] = $arrayItemsDetails[$i]['fob_price'];
							$arrayFobPrices[$i]['location'] = current($location);
							$arrayFobPrices[$i]['description'] = "Precio FOB de la compra ".$noteCode." del ".$date; 
							$arrayFobPrices[$i]['code'] = $purchaseCode; 
							if($firstOneFob == 'yes'){
								$arrayFobPrices[$i]['date'] =  $year.'-01-01 00:00:00'; //Cambiar por algo que me de el (año sacado de $date) + 01-01 00:00:00
							}else{
								$arrayFobPrices[$i]['date'] = $date;
							}	
						}
						
						$cif = round(($arrayItemsDetails[$i]['ex_fob_price'] + ($arrayItemsDetails[$i]['ex_fob_price'] * $perc)),2);
//						debug($cif);
						$sameCifPriceOrDate = 'no';
						$firstOneCif = 'yes';
						if($arrayLastCifPrices != array()){
							for($k=0;$k<count($arrayLastCifPrices);$k++){
//								if($arrayItemsDetails[$i]['inv_item_id'] == $arrayLastCifPrices[$k]['InvPrice']['inv_item_id'] && $arrayLastCifPrices[$k]['InvPrice']['ex_price'] == $cif){
//									$contCif = 1;
//								}
								if($arrayItemsDetails[$i]['inv_item_id'] == $arrayLastCifPrices[$k]['InvPrice']['inv_item_id']){
									$firstOneCif = 'no';
									if($arrayLastCifPrices[$k]['InvPrice']['ex_price'] == $cif OR $date2Compare == $arrayLastCifPrices[$j]['InvPrice']['date']){
										$sameCifPriceOrDate = 'yes';
									}
								}
							}	
						}
						
						if($sameCifPriceOrDate == 'no'){
							$arrayCifPrices[$i]['inv_item_id'] = $arrayItemsDetails[$i]['inv_item_id'];
							$arrayCifPrices[$i]['inv_price_type_id'] = 8;//or better relate by name CIF
							$arrayCifPrices[$i]['ex_rate'] = $exRate;
							$arrayCifPrices[$i]['ex_price'] = $cif;
							$arrayCifPrices[$i]['price'] = $cif * $exRate;
							$arrayCifPrices[$i]['location'] = current($location);
							$arrayCifPrices[$i]['description'] = "Precio CIF prorrateado de la compra ".$noteCode." del ".$date; 
							$arrayCifPrices[$i]['code'] = $purchaseCode;
							if($firstOneCif == 'yes'){
								$arrayCifPrices[$i]['date'] = $year.'-01-01 00:00:00'; //Cambiar por algo que me de el (año sacado de $date) + 01-01 00:00:00
							}else{
								$arrayCifPrices[$i]['date'] = $date;
							}	
						}
					}
				}
			////////////////////////////////////////////////END - HISTORY PRICES//////////////////////////////////////////////////////
//			print_r($dataPurchase);
//			print_r($dataPurchaseDetail);
//			print_r($dataMovement);
//			print_r($dataMovementDetail);
//			print_r($dataMovementHeadsUpd);
////			print_r($arrayFobPrices);
////			print_r($arrayCifPrices);
////			print_r($STATE);
////			print_r($dataPurchase[1]);	
//			die();
			////////////////////////////////////////////START- CORE SAVE////////////////////////////////////////////////////////
			if($error == 0){
				/////////////////////START - SAVE/////////////////////////////			
				$res = $this->PurPurchase->saveMovement($dataPurchase, $dataPurchaseDetail, $dataMovement, $dataMovementDetail, $dataMovementHeadsUpd, $OPERATION, $ACTION, $STATE, $purchaseInvoiceDocCode, $purchaseCode, $dataPayDetail, $dataCostDetail, $arrayFobPrices, $arrayCifPrices, $arrayForValidate, $arrayForValidateOrig);

							
				switch ($res[0]) {
					case 'SUCCESS':
//						echo $res[1].'|'.$purchaseOrderDocCode.'|'.$purchaseCode;
						echo $res[1]; //.'|'.$purchaseInvoiceDocCode.'|'.$purchaseCode;
						break;
					case 'VALIDATION':
                        echo 'VALIDATION|' . $res[1];
                        break;
					case 'ERROR':
						echo 'ERROR|onSaving';
						break;
				}	
				/////////////////////END - SAVE////////////////////////////////	
			}else{
				echo 'ERROR|onGeneratingParameters';
			}
			////////////////////////////////////////////END-CORE SAVE////////////////////////////////////////////////////////
		}
	}
	
//	public function ajax_logic_delete(){
//		if ($this->RequestHandler->isAjax()){
//			$purchaseId = $this->request->data['purchaseId'];
//			$type = $this->request->data['type'];	
//			$genCode = $this->request->data['genCode'];
//			$dataMovement = array();
//			if($type === 'ORDER_LOGIC_DELETED'){
//				$arrayPurchase['id']=$purchaseId;
//				$arrayPurchase['lc_state']=$type;
//				$dataPurchase[0] = $arrayPurchase;
//				$dataPurchase[1] = array();
//				$res = $this->PurPurchase->updateMovement($dataPurchase, $dataMovement);
//			}elseif($type === 'PINVOICE_LOGIC_DELETED'){
//				$this->loadModel('InvMovement');
//				$state = $this->InvMovement->find('first', array(
//					'fields'=>array(
//						'InvMovement.lc_state'
//						),
//					'conditions'=>array(
//						'InvMovement.document_code'=>$genCode
//						)
//					,'recursive'=>-1
//					,'order' => 'InvMovement.date_created DESC'
//				));
//				if($state['InvMovement']['lc_state'] === 'PENDANT'){
//					$arrayPurchase['id']=$purchaseId;
//					$arrayPurchase['lc_state']=$type;
//					$dataPurchase[0] = $arrayPurchase;
//					$dataPurchase[1] = array();
//					$arrayMovement['id'] = $this->_get_doc_id(null, $genCode, 1);				
//					$arrayMovement['lc_state']='LOGIC_DELETED';
//					$dataMovement = $arrayMovement;
//					$res = $this->PurPurchase->updateMovement($dataPurchase, $dataMovement);
//				}elseif($state['InvMovement']['lc_state'] === 'LOGIC_DELETED' || $state['InvMovement']['lc_state'] === 'CANCELLED'){	
//					$arrayPurchase['id']=$purchaseId;
//					$arrayPurchase['lc_state']=$type;
//					$dataPurchase[0] = $arrayPurchase;
//					$dataPurchase[1] = array();
//					$res = $this->PurPurchase->updateMovement($dataPurchase, $dataMovement);
//				}else{
//					$res[0] = 'EXCEPTION';
//				}	
//			}	
//			
//			switch ($res[0]) {
//				case 'SUCCESS':
//					echo 'success';
//					break;
//				case 'EXCEPTION':
//					echo 'exception';
//					break;
//				case 'ERROR':
//					echo 'ERROR|onSaving';
//					break;
//			}
//		}
//	}
	
	    public function ajax_logic_delete() {
        if ($this->RequestHandler->isAjax()) {
            $purchaseId = $this->request->data['purchaseId'];
            $type = $this->request->data['type'];
            $genCode = $this->request->data['genCode'];
            $cloneStatus = $this->request->data['cloneStatus'];
            $noteCode = $this->request->data['noteCode'];
            $dataMovement = array();
//            $reservedState = $this->SalSale->find('list', array(
//                'fields' => array('SalSale.reserve'),
//                'conditions' => array(
//                    'SalSale.id' => $purchaseId
//                )
//            ));
//            if ($type === 'NOTE_LOGIC_DELETED') {
////				debug($reservedState);
//                if (current($reservedState) == true) {
//                    echo 'BLOCK';
//                    exit();
//                }
////				$invId = $this->_get_doc_id($purchaseId, $genCode, null, null);
//                $arraySale1['id'] = $purchaseId;
//                $arraySale1['lc_state'] = $type;
//                $arraySale2['id'] = $this->_get_doc_id($purchaseId, $genCode, null, null);
//                $lcState = $this->SalSale->find('list', array(
//                    'fields' => array('SalSale.lc_state'),
//                    'conditions' => array(
//                        'SalSale.id' => $arraySale2['id']
//                    )
//                ));
//                if (current($lcState) == 'DRAFT') {
//                    $arraySale2['lc_state'] = 'DRAFT_DELETED';
//                } elseif (current($lcState) == 'SINVOICE_PENDANT') {
//                    $arraySale2['lc_state'] = 'SINVOICE_LOGIC_DELETED';
//                }
//                $dataSale[0] = $arraySale1;
//                $dataSale[1] = $arraySale2;
////				$dataSale[1] = array();
////				print_r($dataSale);
////				die();
//                $res = $this->SalSale->updateMovement($dataSale, $dataMovement);
//            } else
			if ($type === 'PINVOICE_LOGIC_DELETED') {
//                if (current($reservedState) == false) {
//                    echo 'BLOCK';
//                    exit();
//                }
//                $arraySale1['id'] = $this->_get_doc_id($purchaseId, $genCode, null, null);
				
				$arrayPurchase1['id'] = $purchaseId;
				$arrayPurchase1['lc_state'] = $type;
                               
				
               
                if(strpos($cloneStatus,'COM') !== false){
                     $arrayPurchase1['clone'] = 'NO';
                     $idOriginalPurchase = $this->PurPurchase->find('list', array(
                            'fields'=>array(
                                    'PurPurchase.id','PurPurchase.code'),
                            'conditions'=>array(
                                    'PurPurchase.note_code'=>$noteCode
                                    ,'PurPurchase.lc_state'=>"PINVOICE_APPROVED"
//                                                ,'PurPurchase.clone'=>"YES"
                                    )						
                    ));
                    $arrayPurchase2['id'] = key($idOriginalPurchase);
                    $arrayPurchase2['clone'] = 'NO'; 
                    $dataPurchase[1] = $arrayPurchase2;
                }else{
                     $dataPurchase[1] = array();
                }
                $dataPurchase[0] = $arrayPurchase1;
                
//                if ($arraySale1['id'] == null) {
//                    $arraySale2['id'] = $purchaseId;
//                    $arraySale2['lc_state'] = $type;
//                    $dataSale[0] = $arraySale2;
//                    $dataSale[1] = array();
//                } 
//				else {
//                    $arraySale2['id'] = $purchaseId;
//                    $arraySale2['lc_state'] = $type;
////					$arraySale1['id']=$arraySale1['id'];
//                    $arraySale1['lc_state'] = 'NOTE_LOGIC_DELETED';
//                    $dataSale[0] = $arraySale2;
//                    $dataSale[1] = $arraySale1;
//                }
				
                $res = $this->PurPurchase->updateMovement($dataPurchase, $dataMovement);
            }

            switch ($res[0]) {
                case 'SUCCESS':
                    echo 'success';
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
	
	public function ajax_set_to_pendant(){
		if($this->RequestHandler->isAjax()){
		
			////////////////////////////////////////////INICIO-CAPTURAR AJAX////////////////////////////////////////////////////////
			$arrayItemsDetails = $this->request->data['arrayItemsDetails'];		
			$purchaseId = $this->request->data['purchaseId'];
			$purchaseCode = $this->request->data['purchaseCode'];

			$noteCode = $this->request->data['noteCode'];
			$date = $this->request->data['date'];
			$warehouseId = $this->request->data['warehouseId'];
			$description = $this->request->data['description'];
			$discount = $this->request->data['discount'];
			$exRate = $this->request->data['exRate'];
			////////////////////////////////////////////FIN-CAPTURAR AJAX////////////////////////////////////////////////////////
//			print_r($arrayItemsDetails);
//			die();
			////////////////////////////////////////////INICIO-CREAR PARAMETROS////////////////////////////////////////////////////////
			$arrayPurchaseOrder['id']=$purchaseId;
			$arrayPurchaseOrder['lc_state']='ORDER_PENDANT';
			//header for INVOICE
			$arrayPurchaseInvoice['note_code']=$noteCode;
			$arrayPurchaseInvoice['date']=$date;
			$arrayPurchaseInvoice['inv_warehouse_id']=$warehouseId;
			$arrayPurchaseInvoice['description']=$description;
			$arrayPurchaseInvoice['ex_rate']=$exRate;
			$arrayPurchaseInvoice['discount']=$discount;
			$arrayPurchaseInvoice['code']=$purchaseCode;
			$arrayPurchaseInvoice['doc_code']='NO';
			$arrayPurchaseInvoice['lc_state']='DRAFT';
			//header for MOVEMENT
			$arrayMovement['date']=$date;
			$arrayMovement['inv_warehouse_id']=$warehouseId;
			$arrayMovement['inv_movement_type_id']=1; //Reynaldo Rojas Compra = 1
			$arrayMovement['description']=$description;
			$arrayMovement['type']=1;//NON BACKORDER
			$arrayMovement['document_code']=$purchaseCode;
			$arrayMovement['code']='NO';
			$arrayMovement['lc_state']='DRAFT';
			//item details for MOVEMENT
			$arrayMovementDetails = $arrayItemsDetails;//array('inv_item_id'=>$itemId, 'quantity'=>$quantity);
			//item details for INVOICE
			$arrayPurchaseDetails = $arrayItemsDetails;/*array('inv_supplier_id'=>$supplierId,  
										'inv_item_id'=>$itemId,
										'ex_fob_price'=>$exFobPrice, 'fob_price'=>$fobPrice,
										'quantity'=>$quantity,
										'ex_subtotal'=>$exSubtotal);*/
			
//			$OPERATION = 'ADD';
//			$ACTION = 'save_invoice';
//			$STATE = 'PINVOICE_PENDANT';
//			$dataMovementHeadsUpd = array();
//			$dataPayDetail = array();
//			$dataCostDetail = array();
//			$arrayFobPrices = array();
//			$arrayCifPrices = array();
//			$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseInvoice);
//			$dataPurchaseDetail[0] = array('PurDetail'=> $arrayPurchaseDetails);
//			$dataMovement = array('InvMovement'=>$arrayMovement);
//			$dataMovementDetail = array('InvMovementDetail'=> $arrayMovementDetails);
			
			$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseOrder);
			$dataPurchase[1] = array('PurPurchase'=>$arrayPurchaseInvoice, 'PurDetail'=> $arrayPurchaseDetails);
			$dataMovement = array('InvMovement'=>$arrayMovement, 'InvMovementDetail'=> $arrayMovementDetails);
			////////////////////////////////////////////FIN-CREAR PARAMETROS////////////////////////////////////////////////////////
//			print_r($dataPurchase);
//			print_r($dataMovement);
//			die();
			////////////////////////////////////////////INICIO-SAVE////////////////////////////////////////////////////////
			$res = $this->PurPurchase->updateMovement($dataPurchase, $dataMovement);
//			switch ($res[0]) {
//				case 'SUCCESS':
//					echo 'success|'.$res[1];
//					break;
//				case 'ERROR':
//					echo 'ERROR|onSaving';
//					break;
//			}			
			switch ($res[0]) {
				case 'SUCCESS':
					echo 'success';
					break;
				case 'ERROR':
					echo 'ERROR|onSaving';
					break;
			}	
			////////////////////////////////////////////FIN-SAVE////////////////////////////////////////////////////////
		
		}
	}
	
	public function ajax_generate_movements(){
		if($this->RequestHandler->isAjax()){
		
			////////////////////////////////////////////INICIO-CAPTURAR AJAX////////////////////////////////////////////////////////
			$arrayItemsDetails = $this->request->data['arrayItemsDetails'];		
//			$purchaseId = $this->request->data['purchaseId'];
			$purchaseCode = $this->request->data['purchaseCode'];

//			$noteCode = $this->request->data['noteCode'];
			$date = $this->request->data['date'];
			$warehouseId = $this->request->data['warehouseId'];
			$description = $this->request->data['description'];
//			$discount = $this->request->data['discount'];
//			$exRate = $this->request->data['exRate'];
			////////////////////////////////////////////FIN-CAPTURAR AJAX////////////////////////////////////////////////////////
//			print_r($arrayItemsDetails);
//			die();
			////////////////////////////////////////////INICIO-CREAR PARAMETROS////////////////////////////////////////////////////////
//			$arrayPurchaseOrder['id']=$purchaseId;
//			$arrayPurchaseOrder['lc_state']='ORDER_PENDANT';
//			//header for INVOICE
//			$arrayPurchaseInvoice['note_code']=$noteCode;
//			$arrayPurchaseInvoice['date']=$date;
//			$arrayPurchaseInvoice['inv_warehouse_id']=$warehouseId;
//			$arrayPurchaseInvoice['description']=$description;
//			$arrayPurchaseInvoice['ex_rate']=$exRate;
//			$arrayPurchaseInvoice['discount']=$discount;
//			$arrayPurchaseInvoice['code']=$purchaseCode;
//			$arrayPurchaseInvoice['doc_code']='NO';
//			$arrayPurchaseInvoice['lc_state']='DRAFT';
			//header for MOVEMENT
			$arrayMovement['date']=$date;
			$arrayMovement['inv_warehouse_id']=$warehouseId;
			$arrayMovement['inv_movement_type_id']=1; //Reynaldo Rojas Compra = 1
			$arrayMovement['description']=$description;
			$arrayMovement['type']=1;//NON BACKORDER
			$arrayMovement['document_code']=$purchaseCode;
			$arrayMovement['code']=  $this->_generate_movement_code('ENT',null);//'NO';
			$arrayMovement['lc_state']='PENDANT';
			//item details for MOVEMENT
			$arrayMovementDetails = $arrayItemsDetails;//array('inv_item_id'=>$itemId, 'quantity'=>$quantity);
			//item details for INVOICE
//			$arrayPurchaseDetails = $arrayItemsDetails;/*array('inv_supplier_id'=>$supplierId,  
//										'inv_item_id'=>$itemId,
//										'ex_fob_price'=>$exFobPrice, 'fob_price'=>$fobPrice,
//										'quantity'=>$quantity,
//										'ex_subtotal'=>$exSubtotal);*/
			
//			$OPERATION = 'ADD';
//			$ACTION = 'save_invoice';
//			$STATE = 'PINVOICE_PENDANT';
//			$dataMovementHeadsUpd = array();
//			$dataPayDetail = array();
//			$dataCostDetail = array();
//			$arrayFobPrices = array();
//			$arrayCifPrices = array();
//			$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseInvoice);
//			$dataPurchaseDetail[0] = array('PurDetail'=> $arrayPurchaseDetails);
//			$dataMovement = array('InvMovement'=>$arrayMovement);
//			$dataMovementDetail = array('InvMovementDetail'=> $arrayMovementDetails);
			
//			$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseOrder);
//			$dataPurchase[1] = array('PurPurchase'=>$arrayPurchaseInvoice, 'PurDetail'=> $arrayPurchaseDetails);
			$dataMovement = array('InvMovement'=>$arrayMovement, 'InvMovementDetail'=> $arrayMovementDetails);
			$dataPurchase[0] = array();
			$dataPurchase[1] = array();
			////////////////////////////////////////////FIN-CREAR PARAMETROS////////////////////////////////////////////////////////
//			print_r($dataPurchase[0]);
//			print_r($dataPurchase[1]);
//			print_r($dataMovement);
//			die();
			////////////////////////////////////////////INICIO-SAVE////////////////////////////////////////////////////////
			$res = $this->PurPurchase->updateMovement($dataPurchase, $dataMovement);
//			switch ($res[0]) {
//				case 'SUCCESS':
//					echo 'success|'.$res[1];
//					break;
//				case 'ERROR':
//					echo 'ERROR|onSaving';
//					break;
//			}		
			
			switch ($res[0]) {
				case 'SUCCESS':
					echo 'success';
					break;
				case 'ERROR':
					echo 'ERROR|onSaving';
					break;
			}	
			////////////////////////////////////////////FIN-SAVE////////////////////////////////////////////////////////
		
		}
	}
	
	public function ajax_generate_clone(){
		if($this->RequestHandler->isAjax()){

			////////////////////////////////////////////INICIO-CAPTURAR AJAX////////////////////////////////////////////////////////
			$arrayPurchaseInvoice = array();
			$arrayMovement = array();
			$purchaseId = $this->request->data['purchaseId'];
                        $code = $this->request->data['code'];
			$noteCode = $this->request->data['noteCode'];
			$date = $this->request->data['date'];
			$warehouseId = $this->request->data['warehouseId'];
			$description = $this->request->data['description'];
			$discountType = $this->request->data['discountType'];
			$discount = $this->request->data['discount'];
			$exRate = $this->request->data['exRate'];
			$arrayItemsDetails = $this->request->data['arrayItemsDetails'];
			$arrayCostsDetails = $this->request->data['arrayCostsDetails'];
//			$arrayPaysDetails = $this->request->data['arrayPaysDetails'];
//			$totalDisc = $this->request->data['totalDisc'];
//			$total = $this->request->data['total'];
//			$total2 = $this->request->data['total2'];
			//Internal variables
			$error=0;
			////////////////////////////////////////////FIN-CAPTURAR AJAX////////////////////////////////////////////////////////
			////////////////////////////////////////////INICIO-CREAR PARAMETROS////////////////////////////////////////////////////////
			
			$purchaseCode = $this->_generate_code('COM');
			$purchaseInvoiceDocCode = $this->_generate_doc_code('CFA');
			if($purchaseCode == 'error'){$error++;}		
			if($purchaseInvoiceDocCode == 'error'){$error++;}
//			if(){}//query for the paid field
			//header for INVOICE
			$arrayPurchaseInvoice['code'] = $purchaseCode;
			$arrayPurchaseInvoice['doc_code'] = $purchaseInvoiceDocCode;
			$arrayPurchaseInvoice['note_code']=$noteCode;
			$arrayPurchaseInvoice['date']=$date;
			$arrayPurchaseInvoice['inv_warehouse_id']=$warehouseId;
			$arrayPurchaseInvoice['description']=$description;
			$arrayPurchaseInvoice['ex_rate']=$exRate;
			$arrayPurchaseInvoice['discount']=$discount;
//			$arrayPurchaseInvoice['discount_type']=$discountType;
			if($discountType==1){$arrayPurchaseInvoice['discount_type']='NONE';}else if($discountType == 2){$arrayPurchaseInvoice['discount_type']='PERCENT';}else/*if($discountTypeName == 'BOB')*/{$arrayPurchaseInvoice['discount_type']='USD';}
			$arrayPurchaseInvoice['location']='La Paz';
			$arrayPurchaseInvoice['lc_state']='PINVOICE_PENDANT';
			$arrayPurchaseInvoice['paid']=false;
                        $arrayPurchaseInvoice['clone']=$code;
                        //header for old INVOICE
                        $arrayPurchaseOrigInvoice['id']=$purchaseId;
                        $arrayPurchaseOrigInvoice['clone']='YES';
			//header for MOVEMENT
			$arrayMovement['date']=$date;
			$arrayMovement['inv_warehouse_id']=$warehouseId;
			$arrayMovement['inv_movement_type_id']=1; //Reynaldo Rojas Compra = 1
			$arrayMovement['description']=$description;
			$arrayMovement['type']=1;//NON BACKORDER
			$arrayMovement['document_code']=$arrayPurchaseInvoice['code'];//$purchaseCode;
			$arrayMovement['code']=  'NO';//$this->_generate_movement_code('ENT',null);//'NO';
			$arrayMovement['lc_state']='DRAFT';
			//item details for INVOICE
			$arrayPurchaseDetails = $arrayItemsDetails;
			//item details for MOVEMENT
			$arrayMovementDetails = $arrayItemsDetails;
			//calculate if paid or not
			//falta o no? cambiar los totals a float con floatval()
//			if($arrayPaysDetails != array(0)){	
//				$total = 0;
//				if($discountType == 1){//NONE
//					foreach($arrayItemsDetails as $item){
//						$total += $item['ex_subtotal'];
//					}
//					$deuda = $total - $total2;
//				}else if($discountType == 2){//PERCENT
//					$deuda = $totalDisc - $total2;
//				}else{//USD
//					$deuda = $totalDisc - $total2;
//				}
//				if($deuda <= 0){
//					$arrayPurchaseInvoice['paid']=true;
//				}
//			}
			//details for COSTS
			foreach($arrayCostsDetails as &$cost){
				$cost['amount'] = $cost['ex_amount'] * $exRate;
			}
			$dataCostDetail = $arrayCostsDetails;
			//details for PAYS puede estar vacio
//			if($arrayPaysDetails != array(0)){	
//				foreach($arrayPaysDetails as &$pay){
//					$pay['ex_amount'] = $pay['amount'] / $exRate;
//					$pay['pur_payment_type_id'] = 1;
//				}
//			$dataPayDetail = $arrayPaysDetails;
//			}
//			if($arrayPaysDetails != array(0)){
//				$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseInvoice, 'PurDetail'=> $arrayPurchaseDetails, 'PurPrice'=> $dataCostDetail, 'PurPayment'=> $dataPayDetail);
//			}else{
				$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseInvoice, 'PurDetail'=> $arrayPurchaseDetails, 'PurPrice'=> $dataCostDetail);
//			}
			$dataMovement = array('InvMovement'=>$arrayMovement, 'InvMovementDetail'=> $arrayMovementDetails);
			$dataPurchase[1] = array('PurPurchase'=>$arrayPurchaseOrigInvoice);
//			$dataPurchase[1] = array();
			////////////////////////////////////////////FIN-CREAR PARAMETROS////////////////////////////////////////////////////////
//			print_r($dataPurchase[0]);
//			print_r($dataPurchase[1]);
//			print_r($dataMovement);
////			print_r($dataCostDetail);
////			print_r($dataPayDetail);
//			die();
			////////////////////////////////////////////INICIO-SAVE////////////////////////////////////////////////////////
			if($error == 0){
				/////////////////////START - SAVE/////////////////////////////			
//				$res = $this->PurPurchase->saveMovement($dataPurchase, $dataPurchaseDetail, $dataMovement, $dataMovementDetail, $dataMovementHeadsUpd, $OPERATION, $ACTION, $STATE, $purchaseInvoiceDocCode, $purchaseCode, $dataPayDetail, $dataCostDetail, $arrayFobPrices, $arrayCifPrices, $arrayForValidate);
				$res = $this->PurPurchase->updateMovement($dataPurchase, $dataMovement/*, $dataPayDetail, $dataCostDetail*/);
							
				switch ($res[0]) {
					case 'SUCCESS':
						echo 'success|'.$res[1];
//						 echo 'VALIDATION|' . $res[1];
						break;
					case 'ERROR':
						echo 'ERROR|onSaving';
						break;
				}
				/////////////////////END - SAVE////////////////////////////////	
			}else{
				echo 'ERROR|onGeneratingParameters';
			}
		}
	}
	
	public function ajax_generate_purchase_movements(){
		if($this->RequestHandler->isAjax()){
		
			////////////////////////////////////////////INICIO-CAPTURAR AJAX////////////////////////////////////////////////////////
			$arrayItemsDetails = $this->request->data['arrayItemsDetails'];		
//			$purchaseId = $this->request->data['purchaseId'];
//			$purchaseCode = $this->request->data['purchaseCode'];

			$noteCode = $this->request->data['noteCode'];
			$date = $this->request->data['date'];
			$warehouseId = $this->request->data['warehouseId'];
			$description = $this->request->data['description'];
			$discount = $this->request->data['discount'];
			$discountType = $this->request->data['discountType'];
			$exRate = $this->request->data['exRate'];
			////////////////////////////////////////////FIN-CAPTURAR AJAX////////////////////////////////////////////////////////
//			print_r($arrayItemsDetails);
//			die();
			////////////////////////////////////////////INICIO-CREAR PARAMETROS////////////////////////////////////////////////////////
//			$arrayPurchaseOrder['id']=$purchaseId;
//			$arrayPurchaseOrder['lc_state']='ORDER_PENDANT';
//			//header for INVOICE
			$arrayPurchaseInvoice['note_code']=$noteCode;
			$arrayPurchaseInvoice['date']=$date;
			$arrayPurchaseInvoice['inv_warehouse_id']=$warehouseId;
			$arrayPurchaseInvoice['description']=$description;
			$arrayPurchaseInvoice['ex_rate']=$exRate;
			$arrayPurchaseInvoice['discount']=$discount;
			$arrayPurchaseInvoice['discount_type']=$discountType;
			$arrayPurchaseInvoice['location']='La Paz';
			$arrayPurchaseInvoice['code']=$this->_generate_code('COM');//$purchaseCode;
			$arrayPurchaseInvoice['doc_code']=$this->_generate_doc_code('CFA');//'NO';//coger codigo
			$arrayPurchaseInvoice['lc_state']='PINVOICE_PENDANT';
			//header for MOVEMENT
			$arrayMovement['date']=$date;
			$arrayMovement['inv_warehouse_id']=$warehouseId;
			$arrayMovement['inv_movement_type_id']=1; //Reynaldo Rojas Compra = 1
			$arrayMovement['description']=$description;
			$arrayMovement['type']=1;//NON BACKORDER
			$arrayMovement['document_code']=$arrayPurchaseInvoice['code'];//$purchaseCode;
			$arrayMovement['code']=  'NO';//$this->_generate_movement_code('ENT',null);//'NO';
			$arrayMovement['lc_state']='DRAFT';
			//item details for MOVEMENT
			$arrayMovementDetails = $arrayItemsDetails;//array('inv_item_id'=>$itemId, 'quantity'=>$quantity);
			//item details for INVOICE
			$arrayPurchaseDetails = $arrayItemsDetails;/*array('inv_supplier_id'=>$supplierId,  
										'inv_item_id'=>$itemId,
										'ex_fob_price'=>$exFobPrice, 'fob_price'=>$fobPrice,
										'quantity'=>$quantity,
										'ex_subtotal'=>$exSubtotal);*/
			
//			$OPERATION = 'ADD';
//			$ACTION = 'save_invoice';
//			$STATE = 'PINVOICE_PENDANT';
//			$dataMovementHeadsUpd = array();
//			$dataPayDetail = array();
//			$dataCostDetail = array();
//			$arrayFobPrices = array();
//			$arrayCifPrices = array();
//			$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseInvoice);
//			$dataPurchaseDetail[0] = array('PurDetail'=> $arrayPurchaseDetails);
//			$dataMovement = array('InvMovement'=>$arrayMovement);
//			$dataMovementDetail = array('InvMovementDetail'=> $arrayMovementDetails);
			
//			$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseOrder);
			$dataPurchase[0] = array('PurPurchase'=>$arrayPurchaseInvoice, 'PurDetail'=> $arrayPurchaseDetails);
			$dataMovement = array('InvMovement'=>$arrayMovement, 'InvMovementDetail'=> $arrayMovementDetails);
//			$dataPurchase[0] = array();
			$dataPurchase[1] = array();
			////////////////////////////////////////////FIN-CREAR PARAMETROS////////////////////////////////////////////////////////
//			print_r($dataPurchase[0]);
//			print_r($dataPurchase[1]);
//			print_r($dataMovement);
//			die();
			////////////////////////////////////////////INICIO-SAVE////////////////////////////////////////////////////////
			$res = $this->PurPurchase->updateMovement($dataPurchase, $dataMovement);
//			switch ($res[0]) {
//				case 'SUCCESS':
//					echo 'success|'.$res[1];
//					break;
//				case 'ERROR':
//					echo 'ERROR|onSaving';
//					break;
//			}		
			
			switch ($res[0]) {
				case 'SUCCESS':
					echo 'success';
					break;
				case 'ERROR':
					echo 'ERROR|onSaving';
					break;
			}	
			////////////////////////////////////////////FIN-SAVE////////////////////////////////////////////////////////
		
		}
	}
	
	public function ajax_go_invoice(){
		if($this->RequestHandler->isAjax()){	
			$purchaseCode = $this->request->data['genericCode'];
			$purchaseId = $this->request->data['purchaseId'];
			$invoiceId = $this->_get_doc_id($purchaseId, $purchaseCode, null);
			echo $invoiceId;
		}
	}
	
	public function ajax_enable_movements(){//creo q lo voy a usar
		if($this->RequestHandler->isAjax()){	
			$genCode = $this->request->data['genericCode'];
//			$purchaseId = $this->request->data['purchaseId'];
//			$invoiceId = $this->_get_doc_id($purchaseId, $purchaseCode, null);
			$arrayMovement['id'] = $this->_get_doc_id(null, $genCode, 1);				
			$arrayMovement['lc_state']='PENDANT';
			$dataMovement = $arrayMovement;
			$dataPurchase[0] = array();
			$dataPurchase[1] = array();
			$res = $this->PurPurchase->updateMovement($dataPurchase, $dataMovement);
			switch ($res[0]) {
				case 'SUCCESS':
					echo 'success';
					break;
				case 'ERROR':
					echo 'ERROR|onSaving';
					break;
			}
		}
	}
	
	
//	public function ajax_check_document_state(){
//		if($this->RequestHandler->isAjax()){
//			$purchaseId = $this->request->data['purchaseId'];
//			$purchaseCode = $this->request->data['genericCode'];
//			$action = $this->request->data['action'];
//			if ($action !== 'save_invoice') {
//				//gets INVOICE id
//				$invoiceId = $this->_get_doc_id($purchaseId, $purchaseCode, null/*, null*/);
//				
//				$invoiceState = $this->PurPurchase->find('list', array(
//							'fields'=>array(
//								'PurPurchase.lc_state'
//								),
//							'conditions'=>array(
//									'PurPurchase.id'=>$invoiceId
//								)
//					));
//			}
//			//gets MOVEMENT id type 1(hay stock)
//			$movementId = $this->_get_doc_id(null, $purchaseCode, 1/*, 2*/);//EL ALTO 2 MANUALMENTE VER COMO ELEGIR ESTO
//			
//			$this->loadModel('InvMovement');
//			$movementState = $this->InvMovement->find('list', array(
//						'fields'=>array(
//							'InvMovement.lc_state'
//							),
//						'conditions'=>array(
//								'InvMovement.id'=>$movementId
//							)
//				));
//			if($action !== 'save_invoice'){
//				if ((current($invoiceState) != 'PINVOICE_APPROVED' && current($invoiceState) != 'PINVOICE_PENDANT') && (current($movementState) != 'APPROVED' && current($movementState) != 'PENDANT')){
//					echo "proceed";
//				}
//			}else{
//				if (current($movementState) != 'APPROVED'){
//					echo "proceed";
//				} elseif(current($movementState) == 'APPROVED'){
//					echo "approve";
//				}
//			}
//		}
//	}
	
		public function ajax_check_movements_state(){
		if($this->RequestHandler->isAjax()){
//			$purchaseId = $this->request->data['purchaseId'];
			$purchaseCode = $this->request->data['genericCode'];
			//gets MOVEMENT id type 1(hay stock)
			$movementId = $this->_get_doc_id(null, $purchaseCode, 1/*, 2*/);//EL ALTO 2 MANUALMENTE VER COMO ELEGIR ESTO
			
			$this->loadModel('InvMovement');
			$movementState = $this->InvMovement->find('list', array(
						'fields'=>array(
							'InvMovement.lc_state'
							),
						'conditions'=>array(
								'InvMovement.id'=>$movementId
							)
				));
//			if($action !== 'save_invoice'){
//				if ((current($invoiceState) != 'PINVOICE_APPROVED' && current($invoiceState) != 'PINVOICE_PENDANT') && (current($movementState) != 'APPROVED' && current($movementState) != 'PENDANT')){
//					echo "proceed";
//				}
//			}else{
				if (current($movementState) != 'APPROVED'){
					echo "proceed";
				} elseif(current($movementState) == 'APPROVED'){
					echo "approve";
				}
//			}
		}
	}
	
	//////////////////////////////////////////// END - AJAX /////////////////////////////////////////////////
	
	//////////////////////////////////////////// START - PRIVATE ///////////////////////////////////////////////
	
	private function _get_doc_id($purchaseId, $movementCode, $type/*, $warehouseId*/){
		if ($purchaseId <> null) {
			$invoiceId = $this->PurPurchase->find('list', array(
				'fields'=>array('PurPurchase.id'),
				'conditions'=>array(
					'PurPurchase.code'=>$movementCode,
					"PurPurchase.id !="=>$purchaseId
//					,'PurPurchase.lc_state !='=>'PINVOICE_LOGIC_DELETED'
					)
				,'order' => 'PurPurchase.date_created DESC'
			));
			$docId = key($invoiceId);
		}else{
			$this->loadModel('InvMovement');
			$movementId = $this->InvMovement->find('list', array(
				'fields'=>array('InvMovement.id'),
				'conditions'=>array(
					'InvMovement.document_code'=>$movementCode,
					'InvMovement.type'=>$type
//					,'InvMovement.lc_state !='=>'LOGIC_DELETED'
//					'InvMovement.inv_warehouse_id'=>$warehouseId,
					)
				,'order' => 'InvMovement.date_created DESC'
			));
			$docId = key($movementId);
		}
		return $docId;
	}
	
	private function _get_stocks($items, $warehouse, $limitDate = '', $dateOperator = '<='){
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
		
		$movements = $this->InvMovement->InvMovementDetail->find('all', array(
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
	
	private function _generate_code($keyword){
		$period = $this->Session->read('Period.name');
		if($period <> ''){
			try{
				$movements = $this->PurPurchase->find('count', array(
//					'conditions'=>array('PurPurchase.lc_state'=>array('ORDER_PENDANT','ORDER_APPROVED','ORDER_CANCELLED','ORDER_LOGIC_DELETED'))
					'conditions'=>array('PurPurchase.lc_state'=>array('PINVOICE_PENDANT','PINVOICE_APPROVED','PINVOICE_CANCELLED','PINVOICE_LOGIC_DELETED'))
				));
			}catch(Exception $e){
				return 'error';
			}
		}else{
			return 'error';
		}
		
		$quantity = $movements + 1; 
		$code = $keyword.'-'.$period.'-'.$quantity;
		return $code;
	}
	
	private function _generate_doc_code($keyword){
		$period = $this->Session->read('Period.name');
		if($period <> ''){
			try{
				if ($keyword == 'ORD'){
					$movements = $this->PurPurchase->find('count', array(
						'conditions'=>array('PurPurchase.lc_state'=>array('ORDER_PENDANT','ORDER_APPROVED','ORDER_CANCELLED','ORDER_LOGIC_DELETED'))
					)); 
				}elseif ($keyword == 'CFA'){
					$movements = $this->PurPurchase->find('count', array(
						'conditions'=>array('PurPurchase.lc_state'=>array('PINVOICE_PENDANT','PINVOICE_APPROVED','PINVOICE_CANCELLED','PINVOICE_LOGIC_DELETED'))
					));
				}
			}catch(Exception $e){
				return 'error';
			}
		}else{
			return 'error';
		}
		
		$quantity = $movements + 1; 
		$docCode = $keyword.'-'.$period.'-'.$quantity;
		return $docCode;
	}
	
	private function _generate_movement_code($keyword, $type){
		$this->loadModel('InvMovement');
		$period = $this->Session->read('Period.name');
		$movementType = '';
		if($keyword == 'ENT'){$movementType = 'entrada';}
		if($keyword == 'SAL'){$movementType = 'salida';}
		if($period <> ''){
			try{
				$movements = $this->InvMovement->find('count', array(
					'conditions'=>array(
						'InvMovementType.status'=>$movementType
						,'InvMovement.code !='=>'NO'
					//	,'InvMovement.lc_state !='=>'DRAFT'
						)
				)); 
			}catch(Exception $e){
				return 'error';
			}
			
//			$movementss = $this->InvMovement->find('all', array(
//					'conditions'=>array('InvMovementType.status'=>$movementType)
//				)); 
//		echo '------------------------------------------------ <br>';		
//		echo '---movements count--- <br>';	
//		debug($movements);
//		echo '---movements --- <br>';	
//		debug($movementss);
//		echo '----movement type------- <br>';
//		debug($movementType);
//		echo '------------------------------------------------ <br>';
			
		}else{
			return 'error';
		}
		if($type == 'inc'){
			static $inc = 0;
			$quantity = $movements + 1 + $inc;
			$inc++;
		}else{
			$quantity = $movements + 1; 
		}
		$code = $keyword.'-'.$period.'-'.$quantity;
		return $code;
	}
	
	public function _get_movements_details($idMovement){
		$movementDetails = $this->PurPurchase->PurDetail->find('all', array(
			'conditions'=>array(
				'PurDetail.pur_purchase_id'=>$idMovement
				),
			'fields'=>array('InvItem.name', 'InvItem.code', 'PurDetail.ex_fob_price', 'PurDetail.quantity', 'PurDetail.ex_subtotal', 'PurDetail.inv_supplier_id', 'InvItem.id', 'InvSupplier.name','InvSupplier.id',)
			,'order' => array('PurDetail.date_created ASC')
			));
		
		$formatedMovementDetails = array();
		foreach ($movementDetails as $key => $value) {			
			$formatedMovementDetails[$key] = array(
				'itemId'=>$value['InvItem']['id'],
				'item'=>'[ '. $value['InvItem']['code'].' ] '.$value['InvItem']['name'],
				'exFobPrice'=>$value['PurDetail']['ex_fob_price'],//llamar precio
				'cantidad'=>$value['PurDetail']['quantity'],//llamar cantidad
				'exSubtotal'=>$value['PurDetail']['ex_subtotal'],//llamar subtotal
				'supplierId'=>$value['InvSupplier']['id'],
				'supplier'=>$value['InvSupplier']['name']//llamar almacen
				);
		}		
		return $formatedMovementDetails;
	}
	
	private function _get_movements_details_without_stock($idMovement){
		$movementDetails = $this->PurPurchase->PurDetail->find('all', array(
			'conditions'=>array(
				'PurDetail.pur_purchase_id'=>$idMovement
				),
			'fields'=>array('InvItem.name', 'InvItem.code', 'PurDetail.ex_fob_price', 'PurDetail.quantity','PurDetail.inv_supplier_id', 'InvItem.id', 'InvSupplier.name','InvSupplier.id',)
			));
		
		$formatedMovementDetails = array();
		foreach ($movementDetails as $key => $value) {
			// gets the first price in the list of the item prices
//			$priceDirty = $this->PurPurchase->PurDetail->InvItem->InvPrice->find('first', array(
//					'fields'=>array('InvPrice.price'),
//					'order' => array('InvPrice.date_created' => 'desc'),
//					'conditions'=>array(
//						'InvPrice.inv_item_id'=>$value['InvItem']['id']
//						)
//				));
				//$price = $priceDirty['InvPrice']['price'];
			
			$formatedMovementDetails[$key] = array(
				'itemId'=>$value['InvItem']['id'],
				'item'=>'[ '. $value['InvItem']['code'].' ] '.$value['InvItem']['name'],
				'exFobPrice'=>$value['PurDetail']['ex_fob_price'],//llamar precio
				'cantidad'=>$value['PurDetail']['quantity'],//llamar cantidad
				'supplierId'=>$value['InvSupplier']['id'],
				'supplier'=>$value['InvSupplier']['name'],//llamar almacen
				);
		}
//debug($formatedMovementDetails);		
		return $formatedMovementDetails;
	}
	
	public function _get_costs_details($idMovement){
		$movementDetails = $this->PurPurchase->PurPrice->find('all', array(
			'conditions'=>array(
				'PurPrice.pur_purchase_id'=>$idMovement
				),
			'fields'=>array('InvPriceType.name', 'PurPrice.ex_amount', 'InvPriceType.id'/*, 'PurPurchase.inv_supplier_id','InvPrice.price'*/)
			));
		
		$formatedMovementDetails = array();
		foreach ($movementDetails as $key => $value) {
			// gets the first price in the list of the item prices
//			$priceDirty = $this->PurPurchase->PurDetail->InvItem->InvPrice->find('first', array(
//					'fields'=>array('InvPrice.price'),
//					'order' => array('InvPrice.date_created' => 'desc'),
//					'conditions'=>array(
//						'InvPrice.inv_item_id'=>$value['InvItem']['id']
//						)
//				));
				//$price = $priceDirty['InvPrice']['price'];
			
			$formatedMovementDetails[$key] = array(
				'costId'=>$value['InvPriceType']['id'],
				'costCodeName'=>$value['InvPriceType']['name'],
				'costExAmount'=>$value['PurPrice']['ex_amount']//llamar precio
				);
		}
//debug($formatedMovementDetails);		
		return $formatedMovementDetails;
	}
	
	public function _get_pays_details($idMovement){
		$paymentDetails = $this->PurPurchase->PurPayment->find('all', array(
			'conditions'=>array(
				'PurPayment.pur_purchase_id'=>$idMovement
				),
			'fields'=>array('PurPayment.date', 'PurPayment.amount', 'PurPayment.description')
			));
		$formatedPaymentDetails = array();
		foreach ($paymentDetails as $key => $value) {
			$formatedPaymentDetails[$key] = array(
				'dateId'=>$value['PurPayment']['date'],//llamar precio
				//'payDate'=>strftime("%A, %d de %B de %Y", strtotime($value['SalPayment']['date'])),
				'payDate'=>strftime("%d/%m/%Y", strtotime($value['PurPayment']['date'])),
				'payAmount'=>$value['PurPayment']['amount'],//llamar cantidad
				'payDescription'=>$value['PurPayment']['description']
				);
		}
//debug($formatedPaymentDetails);	//	strftime("%A, %d de %B de %Y", $value['SalPayment']['date'])
		return $formatedPaymentDetails;
	}

	//////////////////////////////////////////// END - PRIVATE /////////////////////////////////////////////////
	
	//*******************************************************************************************************//
	/////////////////////////////////////////// END - CLASS ///////////////////////////////////////////////
	//*******************************************************************************************************//
}
