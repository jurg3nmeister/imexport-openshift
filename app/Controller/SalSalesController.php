<?php
App::uses('AppController', 'Controller');

/**
 * SalSales Controller
 *
 * @property SalSale $SalSale
 */
class SalSalesController extends AppController {

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
    //public $components = array('Session')
    //*******************************************************************************************************//
    ///////////////////////////////////////// START - FUNCTIONS ///////////////////////////////////////////////
    //*******************************************************************************************************//
    //////////////////////////////////////////// START - PDF ///////////////////////////////////////////////
    public function view_document_movement_pdf($id = null) {

        $this->InvMovement->id = $id;

        if (!$this->InvMovement->exists()) {
            throw new NotFoundException(__('Invalid post'));
        }
        // increase memory limit in PHP 
        ini_set('memory_limit', '512M');
        $movement = $this->InvMovement->read(null, $id);

        if ($movement['InvMovement']['inv_movement_type_id'] == 4) {
            $this->redirect(array('action' => 'index_warehouses_transfer'));
        }

        if ($movement['InvMovement']['inv_movement_type_id'] == 3) {

            $movementIdOut = $this->InvMovement->find('all', array(
                'conditions' => array(
                    'InvMovement.document_code' => $movement['InvMovement']['document_code'],
                    'InvMovement.inv_movement_type_id =' => 4
            ))); //Out Origin
            $movement['Transfer']['code'] = $movementIdOut[0]['InvMovement']['code'];
            $movement['Transfer']['warehouseName'] = $movementIdOut[0]['InvWarehouse']['name'];
        }


        $details = $this->_get_movements_details_without_stock($id);
        $this->set('movement', $movement);
        $this->set('details', $details);
    }

    //////////////////////////////////////////// END - PDF /////////////////////////////////////////////////
    //////////////////////////////////////////// START - REPORT ////////////////////////////////////////////////
    public function vreport_generator() {
        $this->loadModel('AdmUser');
        $salesmanClean = $this->AdmUser->AdmProfile->find('list', array(
            'order' => array('first_name'),
            'fields' => array('adm_user_id', 'full_name')
        ));
        $salesman = $salesmanClean;
        $salesman[0] = "TODOS";
        $salesmanWO0 = $salesmanClean;

        $customerClean = $this->SalSale->SalEmployee->SalCustomer->find('list');
        $customer = $customerClean;
        $customer[0] = "TODOS";
        $customerWO0 = $customerClean;

        $this->loadModel("InvWarehouse");
        $warehouseClean = $this->InvWarehouse->find('list');
        $warehouse = $warehouseClean;
        $warehouse[0] = "TODOS";
        $item = $this->_find_items();
        $this->set(compact("warehouse", "item", "salesman", "customer", "customerWO0", "salesmanWO0"));
    }

    private function _find_items($type = 'none', $selected = array(), $items = "") {
        $conditions = array();
        $order = array('InvItem.code');
        $conditionsTypes = array();

        switch ($type) {
            case 'category':
                $conditionsTypes = array('InvItem.inv_category_id' => $selected);
                //$order = array('InvCategory.name');
                break;
            case 'brand':
                $conditionsTypes = array('InvItem.inv_brand_id' => $selected);
                //$order = array('InvBrand.name');
                break;
        }

        if ($items <> "") {
            $conditions = array_merge($conditionsTypes, array("InvItem.id" => $items));
        } else {
            $conditions = $conditionsTypes;
        }

        $this->loadModel("InvItem");
        $this->InvItem->unbindModel(array('hasMany' => array('InvPrice', 'InvCategory', 'InvMovementDetail', 'InvItemsSupplier')));
        return $this->InvItem->find("all", array(
                    "fields" => array('InvItem.code', 'InvItem.name', 'InvCategory.name', 'InvBrand.name', 'InvItem.id'),
                    "conditions" => $conditions,
                    "order" => $order
        ));
    }

    public function ajax_get_group_items_and_filters() {
        if ($this->RequestHandler->isAjax()) {
            $type = $this->request->data['type'];
            $group = array();
            switch ($type) {
                case 'category':
                    $this->loadModel("InvCategory");
                    $group = $this->InvCategory->find("list", array("order" => array("InvCategory.name")));
                    $this->set('group', $group);
                    break;
                case 'brand':
                    $this->loadModel("InvBrand");
                    $group = $this->InvBrand->find("list", array("order" => array("InvBrand.name")));
                    $this->set('group', $group);
                    break;
            }
//			$item = $this->_find_items($type, array_keys($group));
            $item = $this->_find_items($type, array_keys(array()));
            $this->set(compact("item"));
        }
    }

    public function ajax_get_group_items() {
        if ($this->RequestHandler->isAjax()) {
            $type = $this->request->data['type'];
            if (isset($this->request->data['selected'])) {
                $selected = $this->request->data['selected'];
            } else {
                $selected = array();
            }
            $item = $this->_find_items($type, $selected);
            $this->set(compact("item"));
        }
    }

    public function ajax_generate_report() {
        if ($this->RequestHandler->isAjax()) {
            //SETTING DATA
            $this->Session->write('ReportMovement.startDate', $this->request->data['startDate']);
            $this->Session->write('ReportMovement.finishDate', $this->request->data['finishDate']);
            $this->Session->write('ReportMovement.showByType', $this->request->data['showByType']);
            $this->Session->write('ReportMovement.showByTypeName', $this->request->data['showByTypeName']);
            $this->Session->write('ReportMovement.warehouse', $this->request->data['warehouse']);
            $this->Session->write('ReportMovement.warehouseName', $this->request->data['warehouseName']);
            $this->Session->write('ReportMovement.customer', $this->request->data['customer']);
            $this->Session->write('ReportMovement.customerName', $this->request->data['customerName']);
            $this->Session->write('ReportMovement.customerWO0', $this->request->data['customerWO0']);
            $this->Session->write('ReportMovement.customerNameWO0', $this->request->data['customerNameWO0']);
            $this->Session->write('ReportMovement.salesman', $this->request->data['salesman']);
            $this->Session->write('ReportMovement.salesmanName', $this->request->data['salesmanName']);
            $this->Session->write('ReportMovement.salesmanWO0', $this->request->data['salesmanWO0']);
            $this->Session->write('ReportMovement.salesmanNameWO0', $this->request->data['salesmanNameWO0']);
            $this->Session->write('ReportMovement.currency', $this->request->data['currency']);
            $this->Session->write('ReportMovement.detail', $this->request->data['detail']);
            //for transfer
//			$this->Session->write('ReportMovement.warehouse2', $this->request->data['warehouse2']);
//			$this->Session->write('ReportMovement.warehouseName2', $this->request->data['warehouseName2']);
            //array items
            $this->Session->write('ReportMovement.items', $this->request->data['items']);

            //to send data response to ajax success so it can choose the report view
            echo $this->request->data['showByType'];
            ///END AJAX
        }
    }

    public function vreport_ins_or_outs() {
        $this->_generate_report();
    }

    public function vreport_ins_and_outs() {
        $this->_generate_report();
    }

    public function vreport_transfers() {
        $this->_generate_report();
    }

    private function _generate_report() {
        //special ctp template for printing due DOMPdf colapses generating too many pages
        $this->layout = 'print';

        //Check if session variables are set otherwise redirect
        if (!$this->Session->check('ReportMovement')) {
            $this->redirect(array('action' => 'vreport_generator'));
        }

        //put session data sent data into variables
        $initialData = $this->Session->read('ReportMovement');

//		debug($initialData);

        $settings = $this->_generate_report_settings($initialData);

//		debug($settings);

        $movements = $this->_generate_report_movements($settings['values'], $settings['conditions'], $settings['fields']);
//		debug($movements);

        $currencyFieldPrefix = '';
        $currencyAbbreviation = '(BS)';
        if (trim($initialData['currency']) == 'DOLARES') {
            $currencyFieldPrefix = 'ex_';
            $currencyAbbreviation = '($US)';
        }



        if ($initialData['showByType'] == 1000) {
            $clientsComplete = $this->_generate_report_clients_complete($initialData['customerWO0']);
//			debug($clientsComplete);
        } elseif ($initialData['showByType'] == 998) {
            $salesmenComplete = $this->_generate_report_salesmen_complete($initialData['salesmanWO0']);
//			debug($salesmenComplete);
        } else {
            $itemsComplete = $this->_generate_report_items_complete($initialData['items']);
//		debug($itemsComplete);
        }

        if ($initialData['showByType'] == 1000) {
            $clientsMovements = $this->_generate_report_clients_movements($clientsComplete, $movements, $currencyFieldPrefix);
//			debug($clientsMovements);
        } elseif ($initialData['showByType'] == 998) {
            $salesmenMovements = $this->_generate_report_salesmen_movements($salesmenComplete, $movements, $currencyFieldPrefix);
//			debug($salesmenMovements);
        } else {
            $itemsMovements = $this->_generate_report_items_movements($itemsComplete, $movements, $currencyFieldPrefix);
//		debug($itemsMovements);
        }


        $initialData['currencyAbbreviation'] = $currencyAbbreviation; //setting currency abbreviation before send
        $initialData['items'] = ''; //cleaning items ids 'cause won't be needed begore send
        //debug($initialData);
        $this->set('initialData', $initialData);

        if ($initialData['showByType'] == 1000) {
            $this->set('clientsMovements', $clientsMovements);
        } elseif ($initialData['showByType'] == 998) {
            $this->set('salesmenMovements', $salesmenMovements);
        } else {
            $this->set('itemsMovements', $itemsMovements);
        }
        //debug($settings['initialStocks']);
        $this->set('initialStocks', $settings['initialStocks']);
        $this->Session->delete('ReportMovement');
        //END FUNCTION	
    }

    private function _generate_report_items_movements($itemsComplete, $movements, $currencyFieldPrefix) {
        //I'll not calculate totals 'cause will be easier in the view and specially cleaner due the variation of calculation in every report
        $auxArray = array();
        foreach ($itemsComplete as $item) {
            $fobQuantityTotal = 0;
            $cifQuantityTotal = 0;
            $saleQuantityTotal = 0;
            $saleDiscountTotal = 0;
            $counter = 0;

            $forPricesSubQuery = 0; //before 'InvMovementDetail'
//			debug($item);
            //movements
            foreach ($movements as $movement) {
                if ($item['InvItem']['id'] == $movement['SalDetail']['inv_item_id']) {
                    $fobQuantity = $movement['SalDetail']['quantity'] * $movement[$forPricesSubQuery][$currencyFieldPrefix . 'fob_price'];
                    $cifQuantity = $movement['SalDetail']['quantity'] * $movement[$forPricesSubQuery][$currencyFieldPrefix . 'cif_price'];
                    $saleQuantity = $movement['SalDetail']['quantity'] * $movement['SalDetail'][$currencyFieldPrefix . 'sale_price']/* [$forPricesSubQuery][$currencyFieldPrefix.'sale_price'] */;
                    $saleDiscount = $this->fnPhpLoopDiscount($saleQuantity, $movement['SalSale']['discount'], $movement['SalSale']['discount_type'], $currencyFieldPrefix, $movement['SalSale']['ex_rate']);
                    $fobQuantityTotal = $fobQuantityTotal + $fobQuantity;
                    $cifQuantityTotal = $cifQuantityTotal + $cifQuantity;
                    $saleQuantityTotal = $saleQuantityTotal + $saleQuantity;
                    $saleDiscountTotal = $saleDiscountTotal + $saleDiscount;
                    $auxArray[$item['InvItem']['id']]['Movements'][$counter] = array(
                        'code' => $movement['SalSale']['code'],
                        'doc_code' => $movement['SalSale']['doc_code'],
                        'note_code' => $movement/* [$forPricesSubQuery] */['SalSale']['note_code'],
                        'customer' => $movement[$forPricesSubQuery]['customer'],
                        'salesman' => $movement[$forPricesSubQuery]['salesman'],
                        'quantity' => $movement['SalDetail']['quantity'],
                        'date' => date("d/m/Y", strtotime($movement['SalSale']['date'])),
                        'fob' => $movement[$forPricesSubQuery][$currencyFieldPrefix . 'fob_price'],
                        'cif' => $movement[$forPricesSubQuery][$currencyFieldPrefix . 'cif_price'],
                        'sale' => $movement['SalDetail'][$currencyFieldPrefix . 'sale_price']/* [$forPricesSubQuery][$currencyFieldPrefix.'sale_price'] */,
                        'fobQuantity' => $fobQuantity,
                        'cifQuantity' => $cifQuantity,
                        'saleQuantity' => $saleQuantity,
                        'saleDiscount' => $saleDiscount,
                        'warehouse' => $movement['SalDetail']['inv_warehouse_id']
                    );
////					if(isset($movement['InvMovementType']['status'])){
////						$auxArray[$item['InvItem']['id']]['Movements'][$counter]['status']=$movement['InvMovementType']['status'];
////					}
                    $counter++;
                }
            }
            //Items
            $auxArray[$item['InvItem']['id']]['Item']['codeName'] = '[ ' . $item['InvItem']['code'] . ' ] ' . $item['InvItem']['name'];
            $auxArray[$item['InvItem']['id']]['Item']['brand'] = $item['InvBrand']['name'];
            $auxArray[$item['InvItem']['id']]['Item']['category'] = $item['InvCategory']['name'];
            $auxArray[$item['InvItem']['id']]['Item']['id'] = $item['InvItem']['id'];
            //Totals
            $auxArray[$item['InvItem']['id']]['TotalMovements']['fobQuantityTotal'] = $fobQuantityTotal;
            $auxArray[$item['InvItem']['id']]['TotalMovements']['cifQuantityTotal'] = $cifQuantityTotal;
            $auxArray[$item['InvItem']['id']]['TotalMovements']['saleQuantityTotal'] = $saleQuantityTotal;
            $auxArray[$item['InvItem']['id']]['TotalMovements']['saleDiscountTotal'] = $saleDiscountTotal;
            ////I don't calculate total quantity here 'cause could vary in every report, it will be done in the report views
        }
        return $auxArray;
    }

    private function fnPhpLoopDiscount($amount, $discount, $discountType, $currencyFieldPrefix, $exchangeRate){
        if($discountType != 'NONE'){
            if($discountType == 'PERCENT'){
                return $amount - ($amount * ($discount / 100));
            }else{ //BOB and implicit USD too
                if($currencyFieldPrefix == ''){ //BOB
                    return $amount - $discount;
                }else{ //USD
                    return $amount - ($discount / $exchangeRate );
                }
            }
        }
        return $amount;
    }

    private function _generate_report_clients_movements($clientsComplete, $movements, $currencyFieldPrefix) {
//		debug($clientsComplete);
        //I'll not calculate totals 'cause will be easier in the view and specially cleaner due the variation of calculation in every report
        $auxArray = array();
        foreach ($clientsComplete as $client) {
            $fobQuantityTotal = 0;
            $cifQuantityTotal = 0;
            $saleQuantityTotal = 0;
            $saleDiscountTotal = 0;
            $counter = 0;

            $forPricesSubQuery = 0; //before 'InvMovementDetail'
//			debug($movements);
            //movements
            foreach ($movements as $movement) {
                if ($client['SalCustomer']['id'] == $movement[$forPricesSubQuery]['customerid']) {
                    $fobQuantity = $movement['SalDetail']['quantity'] * $movement[$forPricesSubQuery][$currencyFieldPrefix . 'fob_price'];
                    $cifQuantity = $movement['SalDetail']['quantity'] * $movement[$forPricesSubQuery][$currencyFieldPrefix . 'cif_price'];
                    $saleQuantity = $movement['SalDetail']['quantity'] * $movement['SalDetail'][$currencyFieldPrefix . 'sale_price']/* [$forPricesSubQuery][$currencyFieldPrefix.'sale_price'] */;
                    $saleDiscount = $this->fnPhpLoopDiscount($saleQuantity, $movement['SalSale']['discount'], $movement['SalSale']['discount_type'], $currencyFieldPrefix, $movement['SalSale']['ex_rate']);
                    $fobQuantityTotal = $fobQuantityTotal + $fobQuantity;
                    $cifQuantityTotal = $cifQuantityTotal + $cifQuantity;
                    $saleQuantityTotal = $saleQuantityTotal + $saleQuantity;
                    $saleDiscountTotal = $saleDiscountTotal + $saleDiscount;
                    $auxArray[$client['SalCustomer']['id']]['Movements'][$counter] = array(
                        'code' => $movement['SalSale']['code'],
                        'doc_code' => $movement['SalSale']['doc_code'],
                        'note_code' => $movement/* [$forPricesSubQuery] */['SalSale']['note_code'],
                        'item' => $movement[$forPricesSubQuery]['itemcode'],
//						'customer'=>$movement[$forPricesSubQuery]['customer'],
                        'salesman' => $movement[$forPricesSubQuery]['salesman'],
                        'quantity' => $movement['SalDetail']['quantity'],
                        'date' => date("d/m/Y", strtotime($movement['SalSale']['date'])),
                        'fob' => $movement[$forPricesSubQuery][$currencyFieldPrefix . 'fob_price'],
                        'cif' => $movement[$forPricesSubQuery][$currencyFieldPrefix . 'cif_price'],
                        'sale' => $movement['SalDetail'][$currencyFieldPrefix . 'sale_price']/* [$forPricesSubQuery][$currencyFieldPrefix.'sale_price'] */,
                        'fobQuantity' => $fobQuantity,
                        'cifQuantity' => $cifQuantity,
                        'saleQuantity' => $saleQuantity,
                        'saleDiscount' => $saleDiscount,
                        'warehouse' => $movement['SalDetail']['inv_warehouse_id']
                    );
////					if(isset($movement['InvMovementType']['status'])){
////						$auxArray[$item['InvItem']['id']]['Movements'][$counter]['status']=$movement['InvMovementType']['status'];
////					}
                    $counter++;
                }
            }
//			if($movements == array()){
            //Items
            $auxArray[$client['SalCustomer']['id']]['SalCustomer']['name'] = $client['SalCustomer']['name'];
            //			$auxArray[ $item['InvItem']['id'] ]['Item']['brand']=$item['InvBrand']['name'];
            //			$auxArray[ $item['InvItem']['id'] ]['Item']['category']=$item['InvCategory']['name'];
            $auxArray[$client['SalCustomer']['id']]['SalCustomer']['id'] = $client['SalCustomer']['id'];
//			} else{
//				//Items
//				$auxArray[ $client['SalCustomer']['id'] ]['SalCustomer']['name']=$movement[$forPricesSubQuery]['customer'];
//	//			$auxArray[ $item['InvItem']['id'] ]['Item']['brand']=$item['InvBrand']['name'];
//	//			$auxArray[ $item['InvItem']['id'] ]['Item']['category']=$item['InvCategory']['name'];
//				$auxArray[ $client['SalCustomer']['id'] ]['SalCustomer']['id']=$movement[$forPricesSubQuery]['customerid'];
//			}	
            //Totals
            $auxArray[$client['SalCustomer']['id']]['TotalMovements']['fobQuantityTotal'] = $fobQuantityTotal;
            $auxArray[$client['SalCustomer']['id']]['TotalMovements']['cifQuantityTotal'] = $cifQuantityTotal;
            $auxArray[$client['SalCustomer']['id']]['TotalMovements']['saleQuantityTotal'] = $saleQuantityTotal;
            $auxArray[$client['SalCustomer']['id']]['TotalMovements']['saleDiscountTotal'] = $saleDiscountTotal;
            ////I don't calculate total quantity here 'cause could vary in every report, it will be done in the report views
        }
        return $auxArray;
    }

    private function _generate_report_salesmen_movements($salesmenComplete, $movements, $currencyFieldPrefix) {
//		debug($salesmenComplete);
        //I'll not calculate totals 'cause will be easier in the view and specially cleaner due the variation of calculation in every report
        $auxArray = array();
        foreach ($salesmenComplete as $salesman) {
            $fobQuantityTotal = 0;
            $cifQuantityTotal = 0;
            $saleQuantityTotal = 0;
            $saleDiscountTotal = 0;
            $counter = 0;

            $forPricesSubQuery = 0; //before 'InvMovementDetail'
//			debug($salesman);
            //movements
            foreach ($movements as $movement) {
                if ($salesman['AdmProfile']['adm_user_id'] == $movement[$forPricesSubQuery]['salesmanid']) {
                    $fobQuantity = $movement['SalDetail']['quantity'] * $movement[$forPricesSubQuery][$currencyFieldPrefix . 'fob_price'];
                    $cifQuantity = $movement['SalDetail']['quantity'] * $movement[$forPricesSubQuery][$currencyFieldPrefix . 'cif_price'];
                    $saleQuantity = $movement['SalDetail']['quantity'] * $movement['SalDetail'][$currencyFieldPrefix . 'sale_price']/* [$forPricesSubQuery][$currencyFieldPrefix.'sale_price'] */;
                    $saleDiscount = $this->fnPhpLoopDiscount($saleQuantity, $movement['SalSale']['discount'], $movement['SalSale']['discount_type'], $currencyFieldPrefix, $movement['SalSale']['ex_rate']);
                    $fobQuantityTotal = $fobQuantityTotal + $fobQuantity;
                    $cifQuantityTotal = $cifQuantityTotal + $cifQuantity;
                    $saleQuantityTotal = $saleQuantityTotal + $saleQuantity;
                    $saleDiscountTotal = $saleDiscountTotal + $saleDiscount;
                    $auxArray[$salesman['AdmProfile']['adm_user_id']]['Movements'][$counter] = array(
                        'code' => $movement['SalSale']['code'],
                        'doc_code' => $movement['SalSale']['doc_code'],
                        'note_code' => $movement/* [$forPricesSubQuery] */['SalSale']['note_code'],
                        'item' => $movement[$forPricesSubQuery]['itemcode'],
                        'customer' => $movement[$forPricesSubQuery]['customer'],
                        'quantity' => $movement['SalDetail']['quantity'],
                        'date' => date("d/m/Y", strtotime($movement['SalSale']['date'])),
                        'fob' => $movement[$forPricesSubQuery][$currencyFieldPrefix . 'fob_price'],
                        'cif' => $movement[$forPricesSubQuery][$currencyFieldPrefix . 'cif_price'],
                        'sale' => $movement['SalDetail'][$currencyFieldPrefix . 'sale_price']/* [$forPricesSubQuery][$currencyFieldPrefix.'sale_price'] */,
                        'fobQuantity' => $fobQuantity,
                        'cifQuantity' => $cifQuantity,
                        'saleQuantity' => $saleQuantity,
                        'saleDiscount' => $saleDiscount,
                        'warehouse' => $movement['SalDetail']['inv_warehouse_id']
                    );
////					if(isset($movement['InvMovementType']['status'])){
////						$auxArray[$item['InvItem']['id']]['Movements'][$counter]['status']=$movement['InvMovementType']['status'];
////					}
                    $counter++;
                }
            }
//			if($movements == array()){
            //Items
            $auxArray[$salesman['AdmProfile']['adm_user_id']]['AdmProfile']['full_name'] = $salesman['AdmProfile']['full_name'];
            //			$auxArray[ $item['InvItem']['id'] ]['Item']['brand']=$item['InvBrand']['name'];
            //			$auxArray[ $item['InvItem']['id'] ]['Item']['category']=$item['InvCategory']['name'];
            $auxArray[$salesman['AdmProfile']['adm_user_id']]['AdmProfile']['adm_user_id'] = $salesman['AdmProfile']['adm_user_id'];
//			} else{
//				//Items
//				$auxArray[ $client['SalCustomer']['id'] ]['SalCustomer']['name']=$movement[$forPricesSubQuery]['customer'];
//	//			$auxArray[ $item['InvItem']['id'] ]['Item']['brand']=$item['InvBrand']['name'];
//	//			$auxArray[ $item['InvItem']['id'] ]['Item']['category']=$item['InvCategory']['name'];
//				$auxArray[ $client['SalCustomer']['id'] ]['SalCustomer']['id']=$movement[$forPricesSubQuery]['customerid'];
//			}	
            //Totals
            $auxArray[$salesman['AdmProfile']['adm_user_id']]['TotalMovements']['fobQuantityTotal'] = $fobQuantityTotal;
            $auxArray[$salesman['AdmProfile']['adm_user_id']]['TotalMovements']['cifQuantityTotal'] = $cifQuantityTotal;
            $auxArray[$salesman['AdmProfile']['adm_user_id']]['TotalMovements']['saleQuantityTotal'] = $saleQuantityTotal;
            $auxArray[$salesman['AdmProfile']['adm_user_id']]['TotalMovements']['saleDiscountTotal'] = $saleDiscountTotal;
            ////I don't calculate total quantity here 'cause could vary in every report, it will be done in the report views
        }
        return $auxArray;
        debug($auxArray);
    }

    private function _generate_report_settings($initialData) {
        ///////////////////VALUES, FIELDS, CONDITIONS////////////////////////
        $values = array();
        $conditions = array();
        $fields = array();
        $initialStocks = array();

        $values['startDate'] = $initialData['startDate'];
        $values['finishDate'] = $initialData['finishDate'];
        $warehouses = array(0 => $initialData['warehouse']);
        if ($initialData['showByType'] == 1000) {
            $customers = array(0 => $initialData['customerWO0']);
            $salesmen = array(0 => $initialData['salesman']);
        } elseif ($initialData['showByType'] == 998) {
            $salesmen = array(0 => $initialData['salesmanWO0']);
            $customers = array(0 => $initialData['customer']);
        } else {
            $customers = array(0 => $initialData['customer']);
            $salesmen = array(0 => $initialData['salesman']);
        }



        $employees = $this->SalSale->SalEmployee->find("list", array(
            "fields" => array('SalEmployee.id'),
            "conditions" => array('SalEmployee.sal_customer_id' => $customers)
        ));
//		debug($employees);
//		switch ($initialData['movementType']) {
//			case 998://TODAS LAS ENTRADAS
//				$conditions['InvMovement.inv_movement_type_id']=array(1,4,5,6);
//				break;
//			case 999://TODAS LAS SALIDAS
//				$conditions['InvMovement.inv_movement_type_id']=array(2,3,7);
//				break;
//			case 1000://ENTRADAS Y SALIDAS
//				$values['bindMovementType'] = 1;
//				$initialStocks = $this->_get_real_stocks($initialData['items'], $initialData['warehouse'], $initialData['startDate'], '<');//before starDate, 'cause it will be added or substracted with movements quantities
//				break;
//			case 1001://TRASPASOS ENTRE ALMACENES
//				$values['bindMovementType'] = 1;
//				$conditions['InvMovement.inv_movement_type_id']=array(3,4);
//				$warehouses[1]=$initialData['warehouse2'];
//				break;
//			default:
//				$conditions['InvMovement.inv_movement_type_id']=$initialData['movementType'];
//				break;
//		}
        if ($warehouses[0] > 0) {
            $conditions['SalDetail.inv_warehouse_id'] = $warehouses; //necessary to be here
        }
        if ($customers[0] > 0) {
            $conditions['SalSale.sal_employee_id'] = $employees; //necessary to be here
        }
        if ($salesmen[0] > 0) {
            $conditions['SalSale.salesman_id'] = $salesmen; //necessary to be here
        }
        $values['items'] = $initialData['items']; //just for order
        switch ($initialData['currency']) {
            case 'BOLIVIANOS':
                //$fields = array('InvMovementDetail.fob_price', 'InvMovementDetail.cif_price', 'InvMovementDetail.sale_price');
                $fields[] = '(SELECT price FROM inv_prices where inv_item_id = "SalDetail"."inv_item_id" AND date <= "SalSale"."date" AND inv_price_type_id=1 order by date DESC, date_created DESC LIMIT 1) AS "fob_price"';
                $fields[] = '(SELECT price FROM inv_prices where inv_item_id = "SalDetail"."inv_item_id" AND date <= "SalSale"."date" AND inv_price_type_id=8 order by date DESC, date_created DESC LIMIT 1) AS "cif_price"';
                $fields[] = '(SELECT price FROM inv_prices where inv_item_id = "SalDetail"."inv_item_id" AND date <= "SalSale"."date" AND inv_price_type_id=9 order by date DESC, date_created DESC LIMIT 1) AS "sale_price"';
                break;
            case 'DOLARES':
                //$fields = array('InvMovementDetail.ex_fob_price', 'InvMovementDetail.ex_cif_price', 'InvMovementDetail.ex_sale_price');
                $fields[] = '(SELECT ex_price FROM inv_prices where inv_item_id = "SalDetail"."inv_item_id" AND date <= "SalSale"."date" AND inv_price_type_id=1 order by date DESC, date_created DESC LIMIT 1) AS "ex_fob_price"';
                $fields[] = '(SELECT ex_price FROM inv_prices where inv_item_id = "SalDetail"."inv_item_id" AND date <= "SalSale"."date" AND inv_price_type_id=8 order by date DESC, date_created DESC LIMIT 1) AS "ex_cif_price"';
                $fields[] = '(SELECT ex_price FROM inv_prices where inv_item_id = "SalDetail"."inv_item_id" AND date <= "SalSale"."date" AND inv_price_type_id=9 order by date DESC, date_created DESC LIMIT 1) AS "ex_sale_price"';
                break;
        }

        return array('values' => $values, 'conditions' => $conditions, 'fields' => $fields, 'initialStocks' => $initialStocks);
    }

    private function _generate_report_movements($values, $conditions, $fields) {
        $staticFields = array(
            'SalSale.id',
            'SalSale.code',
            'SalSale.discount',
            'SalSale.discount_type',
            'SalSale.ex_rate',
            'SalSale.doc_code',
            'SalSale.note_code',
            'SalSale.date',
            'SalDetail.inv_warehouse_id',
            'SalDetail.inv_item_id',
            'SalDetail.quantity',
            'SalDetail.sale_price',
            'SalDetail.ex_sale_price'
        );


//		Field to get note_code from Sales and Purchases
        $staticFields[] = '(SELECT  inv_items.code FROM inv_items WHERE inv_items.id = "SalDetail"."inv_item_id") AS "itemcode"';
        /* $fieldNoteCode */ $staticFields[] = '(SELECT  sal_customers.id FROM sal_customers LEFT JOIN sal_employees ON sal_customers.id = sal_employees.sal_customer_id WHERE sal_employees.id = "SalSale"."sal_employee_id") AS "customerid"';
        $staticFields[] = '(SELECT  sal_customers.name FROM sal_customers LEFT JOIN sal_employees ON sal_customers.id = sal_employees.sal_customer_id WHERE sal_employees.id = "SalSale"."sal_employee_id") AS "customer"';
        //$fieldNoteCode = '(SELECT adm_profiles.first_name FROM adm_profiles  JOIN adm_users ON adm_users.id = adm_profiles.adm_user_id WHERE adm_profiles.id = "SalSale"."salesman_id") AS "salesman"';
        /* $fieldNoteCode1 */ $staticFields[] = '(SELECT adm_profiles.adm_user_id FROM adm_profiles WHERE adm_profiles.adm_user_id = "SalSale"."salesman_id") AS "salesmanid"';
        $staticFields[] = '(SELECT adm_profiles.first_name FROM adm_profiles WHERE adm_profiles.adm_user_id = "SalSale"."salesman_id") AS "salesman"';

        //$staticFields[] = $fieldNoteCode;
        //	$staticFields[] = $fieldNoteCode1;
//		debug($fieldNoteCode);
//		if(isset($values['bindMovementType']) AND $values['bindMovementType'] == 1){
//			$this->InvMovement->InvMovementDetail->bindModel(array(
//				'hasOne'=>array(
//					'InvMovementType'=>array(
//						'foreignKey'=>false,
//						'conditions'=> array('InvMovement.inv_movement_type_id = InvMovementType.id')
//					)
//				)
//			));
//			$fields[] = 'InvMovementType.status'; 
//		}
        $this->SalSale->SalDetail->unbindModel(array('belongsTo' => array('InvItem')));
        return $this->SalSale->SalDetail->find('all', array(
                    'conditions' => array(
                        'SalDetail.inv_item_id' => $values['items'],
                        'SalSale.lc_state' => 'SINVOICE_APPROVED',
                        'SalSale.date BETWEEN ? AND ?' => array($values['startDate'], $values['finishDate']),
                        $conditions
                    ),
                    'fields' => array_merge($staticFields, $fields),
                    'order' => array('SalSale.date', 'SalDetail.id')
        ));
    }

    private function _generate_report_items_complete($items) {
        $this->loadModel('InvItem');
        $this->InvItem->unbindModel(array('hasMany' => array('InvMovementDetail', 'PurDetail', 'SalDetail', 'InvItemsSupplier', 'InvPrice')));
        return $this->InvItem->find('all', array(
                    'fields' => array('InvItem.id', 'InvItem.code', 'InvItem.name', 'InvBrand.name', 'InvCategory.name'),
                    'conditions' => array('InvItem.id' => $items),
                    'order' => array('InvItem.code')
        ));
    }

    private function _generate_report_clients_complete($clients) {
//		debug($clientsDirty);
        $this->loadModel('SalCustomer');
        $this->SalCustomer->unbindModel(array('hasMany' => array('SalTaxNumber', 'SalEmployee')));
//		if($clientsDirty == 0){
//			$clients = $this->SalCustomer->find('list', array(
//				'fields'=>array('SalCustomer.id'),
//				//'conditions'=>array('SalCustomer.id'=>$clients),
//				//'order'=>array('SalCustomer.name')
//			));
////			debug($clients);
//			return $this->SalCustomer->find('all', array(
//				'fields'=>array('SalCustomer.id', 'SalCustomer.name'),
//				'conditions'=>array('SalCustomer.id'=>$clients),
//				'order'=>array('SalCustomer.name')
//			));
//		}else{
        return $this->SalCustomer->find('all', array(
                    'fields' => array('SalCustomer.id', 'SalCustomer.name'),
                    'conditions' => array('SalCustomer.id' => $clients),
                    'order' => array('SalCustomer.name')
        ));
//		}
//		$this->loadModel('SalCustomer');
//		$this->SalCustomer->unbindModel(array('hasMany' => array('SalTaxNumber', 'SalEmployee')));
    }

    private function _generate_report_salesmen_complete($salesmen) {
//		debug($salesmen);
        $this->loadModel('AdmProfile');
//		$this->AdmProfile->unbindModel(array('hasMany' => array('SalTaxNumber', 'SalEmployee')));
//		if($clientsDirty == 0){
//			$clients = $this->SalCustomer->find('list', array(
//				'fields'=>array('SalCustomer.id'),
//				//'conditions'=>array('SalCustomer.id'=>$clients),
//				//'order'=>array('SalCustomer.name')
//			));
////			debug($clients);
//			return $this->SalCustomer->find('all', array(
//				'fields'=>array('SalCustomer.id', 'SalCustomer.name'),
//				'conditions'=>array('SalCustomer.id'=>$clients),
//				'order'=>array('SalCustomer.name')
//			));
//		}else{

        /*$conditions = null;
        if($salesmen != 1){ */
         $conditions = array('AdmProfile.adm_user_id' => $salesmen);
        /*}*/

        return $this->AdmProfile->find('all', array(
                    'fields' => array('AdmProfile.adm_user_id', 'AdmProfile.full_name'), //full_name
                    'conditions' => $conditions,
                    'order' => array('AdmProfile.first_name')
        ));
//		}
//		$this->loadModel('SalCustomer');
//		$this->SalCustomer->unbindModel(array('hasMany' => array('SalTaxNumber', 'SalEmployee')));
    }

    //////////////////////////////////////////////////////////////////////////////////////////////////////////
    //////////////////////////////////////////////////////////////////////////////////////////////////////////

    public function vreport_generator_customers_debts() {
        $customers[0] = "TODOS";
        $this->loadModel("SalCustomer");
        $customersClean = $this->SalCustomer->find("list");
        foreach ($customersClean as $key => $value) {
            $customers[$key] = $value;
        }

        $this->set(compact("customers"));
    }

    public function ajax_generate_report_customers_debts() {
        if ($this->RequestHandler->isAjax()) {
            //SETTING DATA
            $this->Session->write('ReportCustomersDebts.customer', $this->request->data['customer']);
            $this->Session->write('ReportCustomersDebts.customerName', $this->request->data['customerName']);
            $this->Session->write('ReportCustomersDebts.showType', $this->request->data['showType']);
            $this->Session->write('ReportCustomersDebts.currency', $this->request->data['currency']);
            ///END AJAX
        }
    }

    public function vreport_customers_debts() {
        //special ctp template for printing due DOMPdf colapses generating too many pages
        $this->layout = 'print';

        //Check if session variables are set otherwise redirect
        if (!$this->Session->check('ReportCustomersDebts')) {
            $this->redirect(array('action' => 'vreport_generator_customers_debts'));
        }

        //put session data sent data into variables
//		$initialData = $this->Session->read('ReportCustomersDebts');
//		
//		debug($initialData);
//		
//		
//		/////////////////////
//		$conditionCustomer = null;
//		if($initialData['customer'] > 0){
//			$conditionCustomer = array("SalCustomer.id"=>$initialData['customer']);
//		}
//		/////////////////////
//		$this->SalSale->SalDetail->bindModel(array(
//			'hasOne'=>array(
//				'SalEmployee'=>array(
//					'foreignKey'=>false,
//					'conditions'=> array('SalSale.sal_employee_id = SalEmployee.id')
//				),
//				'SalCustomer'=>array(
//					'foreignKey'=>false,
//					'conditions'=> array('SalEmployee.sal_customer_id = SalCustomer.id')
//				)
//			)
//		));
//		
//		$currencyField = "";
//		if(strtoupper($initialData["currency"]) == 'DOLARES'){ $currencyField = "ex_";}
//		$this->SalSale->SalDetail->unbindModel(array('belongsTo' => array('InvWarehouse')));
        $data = $this->SalSale->find("all", array(
            "fields" => array(
//				'SUM("SalDetail"."quantity" * "SalDetail"."'.$currencyField.'sale_price") AS money',
//				'SUM("SalDetail"."quantity") AS quantity',
//				'SalSale.date',
                'SalSale.note_code'
            ),
//			'group'=>array("SalCustomer.name", "SalCustomer.id"),
            "conditions" => array(
//				"to_char(SalSale.date,'YYYY')"=>$initialData['year'],
//				"SalDetail.inv_item_id"=>$initialData['items'],
//				$conditionMonth
                "SalSale.id <" => 50
            ),
//			"order"=>array("SalCustomer.name")
        ));
        debug($data);
        die();
//		$this->loadModel("SalCustomer");
//		$customers = $this->SalCustomer->find("list", array("order"=>array("SalCustomer.name")));
//		//debug($data);
//		
//		//debug($customers);
//		$details = array();
//		
//		if($initialData["zero"] == "yes"){
//			$counter = 0;
//			foreach ($customers as $key => $customer) {
//				$details[$counter]['SalCustomer']['name'] = $customer;
//				$details[$counter][0]['money'] = 0;
//				$details[$counter][0]['quantity'] = 0;
//				foreach ($data as $key2 => $value) {
//					
//					if($key == $value['SalCustomer']['id']){
//						//debug($value[0]['money']);
//						$details[$counter][0]['money'] = $value[0]['money'];
//						$details[$counter][0]['quantity'] = $value[0]['quantity'];
//					}
//				}
//				$counter++;
//			}
//		}else{
//			$details = $data;
//		}
//		
//		//debug($details);
//		
//		//debug($details);
//		
//		//debug($details);
//		
//		//Now list items selected in order to get a reference
//		$group = array();
//			switch ($initialData['groupBy']) {
//				case 'category':
//					$this->loadModel("InvCategory");
//					$group = $this->InvCategory->find("list", array("order"=>array("InvCategory.name")));
//					$this->set('group', $group);
//					break;
//				case 'brand':
//					$this->loadModel("InvBrand");
//					$group = $this->InvBrand->find("list", array("order"=>array("InvBrand.name")));
//					$this->set('group', $group);
//					break;
//			}
//			$items = $this->_find_items($initialData['groupBy'], array_keys($group), $initialData['items']);
//		
//		
//			
//		$this->set(compact("details", "items"));
//		//debug($items);
//		$this->Session->delete('ReportPurchasesCustomers');
    }

	public function ajax_generate_debts_report() {
        if ($this->RequestHandler->isAjax()) {
//            $this->Session->write('ReportHistoricalPrices.startDate', $this->request->data['startDate']);
//            $this->Session->write('ReportHistoricalPrices.finishDate', $this->request->data['finishDate']);
//            $this->Session->write('ReportHistoricalPrices.brand', $this->request->data['brand']);
//			$this->Session->write('ReportHistoricalPrices.brandName', $this->request->data['brandName']);
//			$this->Session->write('ReportHistoricalPrices.priceType', $this->request->data['priceType']);
//			$this->Session->write('ReportHistoricalPrices.priceTypeName', $this->request->data['priceTypeName']);
//			$this->Session->write('ReportHistoricalPrices.currency', $this->request->data['currency']);
        }
    }
	
	public function vreport_debts() {
        $this->layout = 'print';

        //Check if session variables are set otherwise redirect
//        if (!$this->Session->check('ReportHistoricalPrices')) {
//            $this->redirect(array('action' => 'index_invoice'));
//        }

        //put session data sent data into variables
//        $initialData = $this->Session->read('ReportHistoricalPrices');
		
//		$conditionBrand = null;
//        if ($initialData["brand"] > 0) {
//            $conditionBrand = array("InvItem.inv_brand_id" => $initialData["brand"]);
//        }
//		
//		$conditionPriceType = null;
//        if ($initialData["priceType"] > 0) {
//            $conditionPriceType = array("InvPrice.inv_price_type_id" => $initialData["priceType"]);
//        }
//		
//		$currencyAbbr = "";
//        if ($initialData["currency"] == "DOLARES") {
//            $currencyAbbr = "ex_";
//        }
		
		$this->loadModel("SalCustomer"); 
		 
//		$this->SalCustomer->unbindModel(array('hasMany' => array('SalTaxNumber'/*, 'SalEmployee'*/)));
		
		$this->SalSale->SalDetail->bindModel(array(
            'hasOne' => array(
                'SalEmployee' => array(
                    'foreignKey' => false,
                    'conditions' => array('SalSale.sal_employee_id = SalEmployee.id')
                ),
                'SalCustomer' => array(
                    'foreignKey' => false,
                    'conditions' => array('SalEmployee.sal_customer_id = SalCustomer.id')
                )
            )
        ));
		
		$debts = $this->SalCustomer->SalEmployee->SalSale->SalDetail->find("all", array(
            "conditions" => array(
				"SalSale.paid" => false 
				,"SalSale.lc_state" => "SINVOICE_APPROVED"
            ), 
			"fields" => array(
//				"SalCustomer.id",
				"SalCustomer.name"
				,"SalSale.note_code"
//				, "SalSale.discount_type" 
//				, "SalSale.discount" 
//				, "SalSale.ex_rate" 
				,"(ROUND(COALESCE(
					CASE WHEN \"SalSale\".\"discount_type\" = 'PERCENT' THEN 
						SUM(\"SalDetail\".\"quantity\" * \"SalDetail\".\"sale_price\") - SUM(\"SalDetail\".\"quantity\" * \"SalDetail\".\"sale_price\") * \"SalSale\".\"discount\" / 100   
					WHEN \"SalSale\".\"discount_type\" = 'BOB' THEN 
						SUM(\"SalDetail\".\"quantity\" * \"SalDetail\".\"sale_price\") - \"SalSale\".\"discount\" 	
					WHEN \"SalSale\".\"discount_type\" = 'USD' THEN 
						SUM(\"SalDetail\".\"quantity\" * \"SalDetail\".\"sale_price\") - \"SalSale\".\"discount\" / \"SalSale\".\"ex_rate\" 	
					ELSE 
						SUM(\"SalDetail\".\"quantity\" * \"SalDetail\".\"sale_price\")  
					END
				,0),2)) AS total"				
//				,'(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price"),2)) AS total'
//				,'(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price") - (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount" / 100) ,2)) AS total'
//				,'(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price") - "SalSale"."discount",2)) AS total'
//				,'(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price") - "SalSale"."discount" / "SalSale"."ex_rate",2)) AS total'
				
            ),
			"recursive" => 0,
			"group" => array(
//				"SalCustomer.id",
				"SalCustomer.name"
				,"SalSale.note_code"
				, "SalSale.discount_type" 
				, "SalSale.discount" 
				, "SalSale.ex_rate" 
            )
            , "order" => array('SalSale.note_code')//fecha para el caso
////			, "order" => array( 'InvItem.id', 'InvPrice.date' )
        ));
//		debug($debts);
//		$this->SalSale->SalPayment->bindModel(array(
//            'hasOne' => array(
//                'SalEmployee' => array(
//                    'foreignKey' => false,
//                    'conditions' => array('SalSale.sal_employee_id = SalEmployee.id')
//                ),
//                'SalCustomer' => array(
//                    'foreignKey' => false,
//                    'conditions' => array('SalEmployee.sal_customer_id = SalCustomer.id')
//                )
//            )
//        ));
		
		$payments = $this->SalCustomer->SalEmployee->SalSale->SalPayment->find('all', array(
			'fields' => array(
//				"SalCustomer.name",
				"SalSale.note_code"
				,'(ROUND(COALESCE(SUM("SalPayment"."amount"),0),2)) AS paid'
			),
			"conditions" => array(
				"SalSale.paid" => false 
				,"SalSale.lc_state" => "SINVOICE_APPROVED"
			),
			"recursive" => 0,
			"group" => array(
//				"SalCustomer.name",
				"SalSale.note_code"
			)
			, "order" => array('SalSale.note_code')//fecha para el caso
////			, "order" => array( 'InvItem.id', 'InvPrice.date' )
		));		
		
		for($i = 0; $i < count($debts); $i++) {
			$count = 0;
			foreach($payments as $payment){
				if($debts[$i]['SalSale']['note_code'] == $payment['SalSale']['note_code']){
					$debts[$i]['0']['paid'] = $payment[0]['paid'];
					$count = 1;
				}
			}
			if($count != 1){
				$debts[$i]['0']['paid'] = 0;
			}
		}
		
		$customers = array_values(array_unique(array_map(function($x){ return $x['SalCustomer']['name']; }, $debts)));
		
		$debtsByCustomer = null;
		for($i = 0; $i < count($customers); $i++) {
			$debtsByCustomer[$i]/*['SalCustomer']*/['total'] = 0;
			foreach ($debts as $debt) {
				if($debt['SalCustomer']['name'] == $customers[$i]){
					$debtsByCustomer[$i]/*['SalCustomer']*/['name'] = $customers[$i];
					$debtsByCustomer[$i]/*['SalCustomer']*/['total'] += $debt[0]['total'] - $debt[0]['paid'];
					$debtsByCustomer[$i][] = array($debt['SalSale']['note_code'],$debt[0]['total'],$debt[0]['paid']);
				}
			}
		}
		
//		debug($debtsByCustomer);
//		die();

//------------------------------------------------------------------------------------------		
		
//		debug($prices);
//		die();
//		foreach ($prices as $price) {
//			$datesUnsorted[] = $price['InvPrice']['date'];
//		}
//		asort($datesUnsorted);
//		$dates = array_unique($datesUnsorted);
////		die();
//		$pricesByItem = null;
//		foreach ($prices as $price) {
//			if($pricesByItem != null){
//				$size = count($pricesByItem);
////				$size = count($dates);
//				$count = 0;
//				for ($i = 0; $i < $size; $i++) {
//					$count = $count + 1;
//					if($price['InvItem']['id'] == $pricesByItem[$i]['InvItem']['id']){//edit
//						$lastPriceData = end($pricesByItem[$i]['prices']);
//						$lastPrice = $lastPriceData['price'];
//						$increment = number_format((( $price['InvPrice'][$currencyAbbr.'price'] / $lastPrice ) - 1) * 100, 2, '.', '');
////						$dates[] = $price['InvPrice']['date'];//NO SE PUEDE USAR EN VEZ DE $dateUnsorted, PQ FALTA PONER UNO EN first PERO SI SE PONE Y HAY DOS FECHAS SE REPITE
//						$pricex = ['date'=>$price['InvPrice']['date'],'price'=>$price['InvPrice'][$currencyAbbr.'price'],'increment'=>$increment];
//						$pricesByItem[$i]['prices'][] = $pricex;
//						break;
//					}					
//					if($count == $size){//new
////						$dates[] = $price['InvPrice']['date'];//NO SE PUEDE USAR EN VEZ DE $dateUnsorted, PQ FALTA PONER UNO EN first PERO SI SE PONE Y HAY DOS FECHAS SE REPITE
//						$price['prices'][] = ['date'=>$price['InvPrice']['date'],'price'=>$price['InvPrice'][$currencyAbbr.'price'],'increment'=>0];
//						unset($price['InvPrice']);
//						$pricesByItem[] = $price;					
//					}
//				}
//			}else{//first
//				$price['prices'][] = ['date'=>$price['InvPrice']['date'],'price'=>$price['InvPrice'][$currencyAbbr.'price'],'increment'=>0];
//				unset($price['InvPrice']);
//				$pricesByItem[] = $price;
//			}	
//		}	
//		debug('----------------------------------------------');
//		debug($pricesByItem);
//		debug($dates);
//		die();
//-----------------------------------------------------------------------------------------------------------------------------------------------		
//		$pricesByItem = null;
//		foreach ($prices as $price) {
//			if($pricesByItem != null){
//				$size = count($pricesByItem);
//				$count = 0;
//				for ($i = 0; $i < $size; $i++) {
//					$count = $count + 1;
//					if($price['InvItem']['id'] == $pricesByItem[$i]['InvItem']['id']){//edit
//						$lastPriceData = end($pricesByItem[$i]);
//						$lastPrice = $lastPriceData['price'];
//						$increment = number_format((( $price['InvPrice'][$currencyAbbr.'price'] / $lastPrice ) - 1) * 100, 2, '.', '');
//						$dates[] = $price['InvPrice']['date'];
//						$pricex = ['date'=>$price['InvPrice']['date'],'price'=>$price['InvPrice'][$currencyAbbr.'price'],'increment'=>$increment];
//						$pricesByItem[$i][] = $pricex;
//						break;
//					}					
//					if($count == $size){//new
//						$dates[] = $price['InvPrice']['date'];
//						$price[] = ['date'=>$price['InvPrice']['date'],'price'=>$price['InvPrice'][$currencyAbbr.'price'],'increment'=>0];
//						unset($price['InvPrice']);
//						$pricesByItem[] = $price;					
//					}
//				}
//			}else{//first
//				$dates[] = $price['InvPrice']['date'];
//				$price[] = ['date'=>$price['InvPrice']['date'],'price'=>$price['InvPrice'][$currencyAbbr.'price'],'increment'=>0];
//				unset($price['InvPrice']);
//				$pricesByItem[] = $price;
//			}	
//		}	
//---------------------------------------------------------------------------------------		
//		$pricesByItem = null;
//		foreach ($prices as $price) {	
//			if($pricesByItem != null){	
//				$size = count($pricesByItem);
//				$count = 0;
//				for ($i = 0; $i < $size; $i++) {
//					$count = $count + 1;
//					if($price['InvItem']['id'] == $pricesByItem[$i]['InvItem']['id']){
//						$pricex = $price['InvPrice']['price'];
//						$pricesByItem[$i]['prices'][$pricex] = $price['InvPrice']['date'];
//						break;
//					}					
//					if($count == $size){
//						$pricex = $price['InvPrice']['price'];
//						$price['prices'][$pricex] = $price['InvPrice']['date'];
//						unset($price['InvPrice']);
//						$pricesByItem[] = $price;					
//					}
//				}
//			}else{//new
//				$pricex = $price['InvPrice']['price'];
//				$price['prices'][$pricex] = $price['InvPrice']['date'];
//				unset($price['InvPrice']);
//				$pricesByItem[] = $price;
//				debug($pricesByItem);
//			}	
//        }
		
//        $this->set("data", $initialData);
//		$this->set("dates", array_values($dates));
//		$this->set("pricesByItem", $pricesByItem);
		$this->set("debtsByCustomer", $debtsByCustomer);
//        $this->set("dataDetails", $dataDetail);
//        $this->set("arrTotal", $arrTotal);
//        $this->Session->delete('ReportHistoricalPrices');
    }
	
    //////////////////////////////////////////// END - REPORT /////////////////////////////////////////////////
    //////////////////////////////////////////START-GRAPHICS//////////////////////////////////////////
    /*
      public function vgraphics(){
      $this->loadModel("AdmPeriod");
      $years = $this->AdmPeriod->find("list", array(
      "order"=>array("name"=>"desc"),
      "fields"=>array("name", "name")
      )
      );

      $this->loadModel("InvItem");

      $itemsClean = $this->InvItem->find("list", array('order'=>array('InvItem.code')));
      $items[0]="TODOS";
      foreach ($itemsClean as $key => $value) {
      $items[$key] = $value;
      }

      $this->set(compact("years", "items"));
      //debug($this->_get_bars_sales_and_time("2013", "0"));
      }
     */

    public function vreport_generator_purchases_customers() {
        $this->loadModel("AdmPeriod");
        $years = $this->AdmPeriod->find("list", array(
            "order" => array("name" => "desc"),
            "fields" => array("name", "name")
                )
        );
        $months = array(0 => "TODOS", 1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio", 7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre");
        $item = $this->_find_items();
        $customers[0] = "TODOS";
        $this->loadModel("SalCustomer");
        $customersClean = $this->SalCustomer->find("list");
        foreach ($customersClean as $key => $value) {
            $customers[$key] = $value;
        }
        $this->set(compact("years", "months", "item", "customers"));
    }

    public function vreport_purchases_customers() {
        //special ctp template for printing due DOMPdf colapses generating too many pages
        $this->layout = 'print';

        //Check if session variables are set otherwise redirect
        if (!$this->Session->check('ReportPurchasesCustomers')) {
            $this->redirect(array('action' => 'vreport_generator_purchases_customers'));
        }

        //put session data sent data into variables
        $initialData = $this->Session->read('ReportPurchasesCustomers');

        //debug($initialData);
        $this->set("initialData", $initialData);
        $conditionMonth = null;
        if ($initialData['month'] > 0) {
            if (count($initialData['month']) == 1) {
                $conditionMonth = array("to_char(SalSale.date,'mm')" => "0" . $initialData['month']);
            } else {
                $conditionMonth = array("to_char(SalSale.date,'mm')" => $initialData['month']);
            }
        }
        /////////////////////
        $conditionCustomer = null;
        if ($initialData['customer'] > 0) {
            $conditionCustomer = array("SalCustomer.id" => $initialData['customer']);
        }
        /////////////////////
        $this->SalSale->SalDetail->bindModel(array(
            'hasOne' => array(
                'SalEmployee' => array(
                    'foreignKey' => false,
                    'conditions' => array('SalSale.sal_employee_id = SalEmployee.id')
                ),
                'SalCustomer' => array(
                    'foreignKey' => false,
                    'conditions' => array('SalEmployee.sal_customer_id = SalCustomer.id')
                )
            )
        ));

        $currencyField = "";
        $currencyDiscount = "BOB";
        if (strtoupper($initialData["currency"]) == 'DOLARES') {
            $currencyField = "ex_";
            $currencyDiscount = "USD";
        }

        $this->SalSale->SalDetail->unbindModel(array('belongsTo' => array('InvWarehouse')));

        $sumField = '"SalDetail"."' . $currencyField . 'sale_price" * "SalDetail"."quantity"';
        $sumField = $this->_createSubQueryDiscounts($sumField, $currencyDiscount);

        $data = $this->SalSale->SalDetail->find("all", array(
            "fields" => array(
               // 'SUM("SalDetail"."quantity" * "SalDetail"."' . $currencyField . 'sale_price") AS money',
                'SUM('.$sumField.') AS money',
                'SUM("SalDetail"."quantity") AS quantity',
                //'SalDetail.quantity',
                //'SalDetail.sale_price',
                //'SalDetail.id',
                //'SalSale.id',
                'SalCustomer.name',
                'SalCustomer.id',
            ),
            'group' => array("SalCustomer.name", "SalCustomer.id"),
            "conditions" => array(
                //"SalCustomer.id"=>array(11,77,367),
                'SalSale.lc_state' => 'SINVOICE_APPROVED',
                "to_char(SalSale.date,'YYYY')" => $initialData['year'],
                "SalDetail.inv_item_id" => $initialData['items'],
                $conditionMonth,
                $conditionCustomer
            ),
            "order" => array('"money"' => "DESC", "SalCustomer.name")
        ));
        $this->loadModel("SalCustomer");
        $customers = $this->SalCustomer->find("list", array("order" => array("SalCustomer.name")));
//		debug($data);
        //debug($customers);
        //$details = array();

        $details = $data;
        //debug($details);



        if ($initialData["zero"] == "yes") {
            if ($initialData["customer"] == 0) {
                $counter = 0;
                foreach ($data as $key2 => $value) {
                    foreach ($customers as $key => $customer) {
                        if ($key == $value['SalCustomer']['id']) {
                            //debug($key);
                            $details[$counter]['SalCustomer']['name'] = $customer;
                            $details[$counter][0]['money'] = $value[0]['money'];
                            $details[$counter][0]['quantity'] = $value[0]['quantity'];
                            unset($customers[$key]);
                        }
                    }
                    $counter++;
                }
                foreach ($customers as $key => $customer) {
                    $details[$counter]['SalCustomer']['name'] = $customer;
                    $details[$counter][0]['money'] = 0;
                    $details[$counter][0]['quantity'] = 0;
                    $counter++;
                }
            } else { // in case for just one customer
                if (count($details) == 0) {
                    $details[0]['SalCustomer']['name'] = $initialData['customerName'];
                    $details[0][0]['money'] = 0;
                    $details[0][0]['quantity'] = 0;
                }
            }
        }



        //debug($details);
        //debug($details);
        //debug($details);
        //Now list items selected in order to get a reference
        $group = array();
        switch ($initialData['groupBy']) {
            case 'category':
                $this->loadModel("InvCategory");
                $group = $this->InvCategory->find("list", array("order" => array("InvCategory.name")));
                $this->set('group', $group);
                break;
            case 'brand':
                $this->loadModel("InvBrand");
                $group = $this->InvBrand->find("list", array("order" => array("InvBrand.name")));
                $this->set('group', $group);
                break;
        }
        $items = $this->_find_items($initialData['groupBy'], array_keys($group), $initialData['items']);


        $this->set(compact("details", "items"));
        //debug($items);
        $this->Session->delete('ReportPurchasesCustomers');
    }

    public function ajax_generate_report_purchases_customers() {
        if ($this->RequestHandler->isAjax()) {
            //SETTING DATA
            $this->Session->write('ReportPurchasesCustomers.year', $this->request->data['year']);
            $this->Session->write('ReportPurchasesCustomers.month', $this->request->data['month']);
            $this->Session->write('ReportPurchasesCustomers.monthName', $this->request->data['monthName']);
            $this->Session->write('ReportPurchasesCustomers.currency', $this->request->data['currency']);
            $this->Session->write('ReportPurchasesCustomers.zero', $this->request->data['zero']);
            $this->Session->write('ReportPurchasesCustomers.groupBy', $this->request->data['groupBy']);
            $this->Session->write('ReportPurchasesCustomers.customer', $this->request->data['customer']);
            $this->Session->write('ReportPurchasesCustomers.customerName', $this->request->data['customerName']);

            //array items
            $this->Session->write('ReportPurchasesCustomers.items', $this->request->data['items']);

            ///END AJAX
        }
    }

    /////////////////////////////////////////////////////
    public function ajax_generate_report_items_utilities() {
        if ($this->RequestHandler->isAjax()) {
            $this->Session->write('ReportItemsUtilities.startDate', $this->request->data['startDate']);
            $this->Session->write('ReportItemsUtilities.finishDate', $this->request->data['finishDate']);
            $this->Session->write('ReportItemsUtilities.currency', $this->request->data['currency']);
            $this->Session->write('ReportItemsUtilities.items', $this->request->data['items']);

            $this->Session->write('ReportItemsUtilities.customer', $this->request->data['customer']);
            $this->Session->write('ReportItemsUtilities.customerName', $this->request->data['customerName']);

            $this->Session->write('ReportItemsUtilities.salesman', $this->request->data['salesman']);
            $this->Session->write('ReportItemsUtilities.salesmanName', $this->request->data['salesmanName']);
        }
    }

    public function vreport_items_utilities() {
        $this->layout = 'print';

        //Check if session variables are set otherwise redirect
        if (!$this->Session->check('ReportItemsUtilities')) {
            $this->redirect(array('action' => 'vreport_items_utilities_generator'));
        }

        //put session data sent data into variables
        $initialData = $this->Session->read('ReportItemsUtilities');

        $conditionCustomer = null;
        if ($initialData["customer"] > 0) {
            $conditionCustomer = array("SalEmployee.sal_customer_id" => $initialData["customer"]);
        }
        $conditionSalesman = null;
        if ($initialData["salesman"] > 0) {
            $conditionSalesman = array("SalSale.salesman_id" => $initialData["salesman"]);
        }

        $currencyAbbr = "";
        $currencyDiscount = "BOB";
        if ($initialData["currency"] == "DOLARES") {
            $currencyAbbr = "ex_";
            $currencyDiscount = "USD";
        }

        //debug($initialData);
        $this->SalSale->SalDetail->bindModel(array(
            'hasOne' => array(
                'SalEmployee' => array(
                    'foreignKey' => false,
                    'conditions' => array('SalSale.sal_employee_id = SalEmployee.id')
                )
            )
        ));
        $this->SalSale->SalDetail->unbindModel(array('belongsTo' => array('InvWarehouse')));

//        $sumField = '"SalDetail"."quantity" * "SalDetail"."' . $priceType . '_price"';
        $sumField = '"SalDetail"."' . $currencyAbbr . 'sale_price" * "SalDetail"."quantity"';
        $sumField = $this->_createSubQueryDiscounts($sumField, $currencyDiscount);
        $prices = $this->SalSale->SalDetail->find("all", array(
            "conditions" => array(
                "InvItem.id" => $initialData["items"]
                , 'SalSale.lc_state' => 'SINVOICE_APPROVED'
                , 'SalSale.date BETWEEN ? AND ?' => array($initialData['startDate'], $initialData['finishDate'])
                , $conditionCustomer
                , $conditionSalesman
            )
            , "fields" => array(
                "InvItem.id"
                //,"SalSale.salesman_id"
                //,"SalEmployee.sal_customer_id"
                , "InvItem.code"
                , "InvItem.name"
                , 'SUM(SalDetail.quantity) AS quantity'
                , 'SUM('.$sumField.') AS sale'
                , 'SUM((SELECT ' . $currencyAbbr . 'price FROM inv_prices where inv_item_id = "SalDetail"."inv_item_id" AND date <= "SalSale"."date" AND inv_price_type_id=8 order by date DESC, date_created DESC LIMIT 1) * "SalDetail"."quantity") AS "cif"'
            )
            , "group" => array(
                "InvItem.id"
                , "InvItem.code"
                , "InvItem.name"
            //,"SalSale.salesman_id"
            //,"SalEmployee.sal_customer_id"
            )
            , "order" => array('"quantity"' => 'DESC', 'InvItem.code')
        ));


        $dataDetail = array();
        //debug($prices);
        $this->loadModel("InvItem");
        $this->InvItem->unbindModel(array('belongsTo' => array('InvPrice', 'InvMovementDetail', 'InvItemsSupplier')));


        $items = array();

        foreach ($initialData["items"] as $value) {
            $items[$value] = $value;
        }
        $arrTotal = array("quantity"=>0, "sale"=>0, "cif"=>0, "utility"=>0);
        foreach ($prices as $value) {
            $index = $value['InvItem']['id'];
            $dataDetail[$index]["code"] = $value['InvItem']['code'];
            $dataDetail[$index]["name"] = $value['InvItem']['name'];
            $dataDetail[$index]["quantity"] = $value[0]['quantity'];
            $dataDetail[$index]["sale"] = $value[0]['sale'];
            $dataDetail[$index]["cif"] = $value[0]['cif'];

            $utility = $value[0]['sale'] - $value[0]['cif'];
            $dataDetail[$index]["utility"] = $utility;
            $dataDetail[$index]["margin"] = ($utility * 100) / $value[0]['sale'];
            
            //Total
            $arrTotal["quantity"] = $arrTotal["quantity"] + $dataDetail[$index]["quantity"];
            $arrTotal["sale"] = $arrTotal["sale"] + $dataDetail[$index]["sale"];
            $arrTotal["cif"] = $arrTotal["cif"] + $dataDetail[$index]["cif"];
            $arrTotal["utility"] = $arrTotal["utility"] + $dataDetail[$index]["utility"];
            
            unset($items[$index]);
        }
//        debug($arrTotal);
        $pricesZero = $this->InvItem->find("all", array(
            "conditions" => array("InvItem.id" => $items),
            "fields" => array("InvItem.id", "InvItem.code", "InvItem.name"),
            "order" => array("InvItem.code")
        ));

        foreach ($pricesZero as $keyItem => $item) {
            $index = $item['InvItem']['id'];
            $dataDetail[$index]["code"] = $item['InvItem']['code'];
            $dataDetail[$index]["name"] = $item['InvItem']['name'];
            $dataDetail[$index]["quantity"] = 0;
            $dataDetail[$index]["sale"] = 0;
            $dataDetail[$index]["cif"] = 0;
            $dataDetail[$index]["utility"] = 0;
            $dataDetail[$index]["margin"] = 0;
        }

        $this->set("data", $initialData);
        $this->set("dataDetails", $dataDetail);
        $this->set("arrTotal", $arrTotal);
        $this->Session->delete('ReportItemsUtilities');
    }

    public function vreport_items_utilities_generator() {
        $this->loadModel("AdmPeriod");
        $years = $this->AdmPeriod->find("list", array(
            "order" => array("name" => "desc"),
            "fields" => array("name", "name")
                )
        );
        $item = $this->_find_items();

        $this->loadModel('AdmUser');
        $salesmanClean = $this->AdmUser->AdmProfile->find('list', array(
            "order" => array("first_name"),
            "fields" => array("adm_user_id", "full_name")
                )
        );
        $salesmen[0] = "TODOS";
        foreach ($salesmanClean as $key => $value) {
            $salesmen[$key] = $value;
        }

//		debug($salesmen);


        $customersClean = $this->SalSale->SalEmployee->SalCustomer->find('list', array("order" => array("name")));
        $customers[0] = "TODOS";
        //debug($customer);
        foreach ($customersClean as $key => $value) {
            $customers[$key] = $value;
        }

        $this->set(compact("years", "item", "customers", "salesmen"));
    }

    ////////////////////////////////////////////////////
    
    
////////////////////////////////////////////NEW GRAPHICS - START//////////////////////////////////////////
    public function graphics_sales_products() {
        ////////////////////////////////////// new feature 2015 ///////////////////
        if(count($this->passedArgs) > 0){
            $this->request->data = array('SalSale'=>$this->passedArgs);
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

        $departamentsClean = $this->SalSale->find("list", array(
            "fields" => array("SalSale.location", "SalSale.location"),
            "group" => array("SalSale.location"),
        ));
        $departaments = array_merge(array("TODOS" => "TODOS"), $departamentsClean);
        ////////////////////////////
        $currencies = array("BOB" => "BOLIVIANOS", "USD" => "DOLARES");
 //       $priceTypes = array("sale" => "VENTA", "cif" => "CIF");
        $showBy = array("money" => "DINERO", "quantity" => "CANTIDAD");
        ///////////////////////////
        $groups = array();
        if (!isset($this->passedArgs['groupId'])) {
            $groups = array('brand' => 'Marca', 'category' => 'Categoria');
        }
        $this->set(compact("years", "months", "departaments", "currencies"/*, "priceTypes"*/, "showBy", "groups"));
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

    public function ajax_get_graphics_sales_products() {
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

            if ($groupId > 0) { //For Items
                $model = "InvItem";
                if ($group == "category") {
                    $productsCondition = array($model . ".inv_category_id" => $groupId);
                } else {
                    $productsCondition = array($model . ".inv_brand_id" => $groupId);
                }
            }else{//For Brands and Categories
                //FIND COLORS only for brands and categories
                $this->loadModel($model);
                $json['colors']=$this->$model->find('list', array('fields'=>array($model.'.id', $model.'.color')));
            }
            ////////////////////////////////////////EXEC - START///////////////////////////////


            //PIE (CORE) and also included data
            $pieDataCompleteIncluded = $this->_get_pie_sales_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $selectedIds, "include", $productsCondition, $groupId);
            $pieDataFormatedIncluded = $this->_formatDataPieToJson($pieDataCompleteIncluded, $model, $groupId); //Here divides in two = [json, selectedIds]
			$json["Pie"] = $pieDataFormatedIncluded["json"]; //$pieDataDivided["selectedIds"]

            $listIncludedSums = $pieDataFormatedIncluded["listDatatableIdsSums"];
            $listIncludedSelectedIds = $pieDataFormatedIncluded["selectedIds"]; //always will work because capture selected checkboxes ids or limit 5 order DESC (never nulls)

            if ($selectedIds[0] > 0) {//selected checkedbox value from datatable
                $listIncludedSelectedIds = $selectedIds;
            }

            //LINES BARS
            $linesBarsDataComplete = $this->_get_bars_lines_sales_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $pieDataFormatedIncluded["selectedIds"], $productsCondition, $groupId);
            //$json['debug'] = $pieDataFormatedIncluded["selectedIds"];
            $linesBarsDataFormated = $this->_formatDataLinesBarsToJson($linesBarsDataComplete, $model, $pieDataFormatedIncluded["selectedIds"], $groupId);

            $json["LinesBars"] = $linesBarsDataFormated;

            //excluded data
            $pieDataCompleteExcluded = $this->_get_pie_sales_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $selectedIds, "exclude", $productsCondition, $groupId);
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
    private function _get_pie_sales_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $selectedIds, $rule, $productsCondition, $groupId) {
        if ($location == "TODOS") {
            $location = null;
        } else {
            $location = array("SalSale.location" => $location);
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
        $this->SalSale->SalDetail->bindModel(array(
            "hasOne" => array_merge($genericBind, $exceptionBind)
        ));

        ///////////// Sales rules
        $sumField = '"SalDetail"."quantity"';
        $round = 0;
        if ($showBy == "money") {
            $round = 2;
            $sumField = '"SalDetail"."quantity" * "SalDetail"."' . $priceType . '_price"';
            if ($currency == "USD") {
                $sumField = '"SalDetail"."quantity" * "SalDetail"."ex_' . $priceType . '_price"';
            }
            if ($priceType == "sale") {//only sale has discounts
                $sumField = $this->_createSubQueryDiscounts($sumField, $currency);
            }
        }
        /////////
        $fields = array($fieldId, $fieldName, $model.".color", "ROUND(COALESCE(SUM(" . $sumField . "),0)," . $round . ") as sum");
        $group = array($fieldId, $fieldName, $model.".color");
        if($groupId > 0){ //for items without color field
            ///New 2015
            $fields = array($fieldId, $fieldName, $model.".code", "ROUND(COALESCE(SUM(" . $sumField . "),0)," . $round . ") as sum");
            $group = array($fieldId, $fieldName, $model.".code");
        }
        //Query
        $data = $this->SalSale->SalDetail->find('all', array(
            "fields" => $fields,
            "group" => $group,
            "conditions" => array(
                "SalSale.lc_state" => "SINVOICE_APPROVED",
                "to_char(SalSale.date,'YYYY')" => $year,
                "to_char(SalSale.date,'mm')" => $month,
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
            if($groupId == 0){ //brand or category
                $json[$i]["color"] = $data[$i][$model]["color"];
            }else{ //items New 2015
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

    private function _get_bars_lines_sales_by_groups($year, $month, $location, $currency, $priceType, $showBy, $model, $selectedIds, $productsCondition, $groupId) {
        if ($location == "TODOS") {
            $location = null;
        } else {
            $location = array("SalSale.location" => $location);
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
        $this->SalSale->SalDetail->bindModel(array(
            "hasOne" => array_merge($genericBind, $exceptionBind)
        ));

        ///////////// Sales rules
        $sumField = '"SalDetail"."quantity"';
        $round = 0;
        if ($showBy == "money") {
            $round = 2;
            $sumField = '"SalDetail"."quantity" * "SalDetail"."' . $priceType . '_price"';
            if ($currency == "USD") {
                $sumField = '"SalDetail"."quantity" * "SalDetail"."ex_' . $priceType . '_price"';
            }
            if ($priceType == "sale") {//only sale has discounts
                $sumField = $this->_createSubQueryDiscounts($sumField, $currency);
            }
        }
        /////Color exception
//        $fields = array($fieldId, $fieldName, "to_char(\"SalSale\".\"date\",'mm') AS month", "ROUND(COALESCE(SUM(" . $sumField . "),0)," . $round . ") as sum");
//        $group = array($fieldId, $fieldName, "month");

        $fields = array($fieldId, $fieldName, $model.".color", "to_char(\"SalSale\".\"date\",'mm') AS month", "ROUND(COALESCE(SUM(" . $sumField . "),0)," . $round . ") as sum");
        $group = array($fieldId, $fieldName, $model.".color", "month");
        if($groupId > 0){ //for items without color field
            //New 2015
            $fields = array($fieldId, $fieldName, $model.".code", "to_char(\"SalSale\".\"date\",'mm') AS month", "ROUND(COALESCE(SUM(" . $sumField . "),0)," . $round . ") as sum");
            $group = array($fieldId, $fieldName, $model.".code", "month");
        }

        //Query
        $data = $this->SalSale->SalDetail->find('all', array(
            "fields" => $fields,
            "group" => $group,
            "conditions" => array(
                "SalSale.lc_state" => "SINVOICE_APPROVED",
                "to_char(SalSale.date,'YYYY')" => $year,
                "to_char(SalSale.date,'mm')" => $month,
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
            $label = $data[$i][$model]["name"];
            $id = $data[$i][$model]["id"]; //
            $month = (int) $data[$i][0]["month"];
            $quantity = (float) $data[$i][0]["sum"];
            ///////////////////////////////
            if($groupId == 0){ //category or brand
                $color = $data[$i][$model]["color"];
                $dataGrouped[$id . "%-&" . $label. "%-&" . $color][$month] = array($month, $quantity); //Ej: 'Accesorios' => array('01'=>array(1,888), '02'=>array(2,543)) | 'Aceites' => array('01'=>array(1,78))
            }else{//items without color
                //New 2015
                $label = '[ '.$data[$i][$model]["code"].' ] ' . $data[$i][$model]["name"];
                $dataGrouped[$id . "%-&" . $label][$month] = array($month, $quantity); //Ej: 'Accesorios' => array('01'=>array(1,888), '02'=>array(2,543)) | 'Aceites' => array('01'=>array(1,78))
                // $dataGrouped[$label][$month] = array($month,$quantity); //Ej: 'Accesorios' => array('01'=>array(1,888), '02'=>array(2,543)) | 'Aceites' => array('01'=>array(1,78))
            }
            ////////////////////////////////
        }  //END FOR


        foreach ($selectedIds as $valueSelectedIds) {//order elements as pie chart DESC values
            foreach ($dataGrouped as $keyDataGrouped => $valueDataGrouped) {
                ////////////////////////////////////
                if($groupId == 0){ //category or brand
                    list($id, $label, $color) = split("%-&", $keyDataGrouped);
                }else{//Item without color
                    list($id, $label) = split("%-&", $keyDataGrouped);
                }
                ////////////////////////////////////

                if ($valueSelectedIds == $id) {
                    $json[$counter]["label"] = $label;
                    $json[$counter]["data"] = array_values($valueDataGrouped); //use array_values to reset keys. Ej: "04" to 0, "08" to 1 in sequencial order. For fit plotchart format
                    if($groupId == 0){ //brand or category
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
        $discountPercent = '(' . $sumField . ')-((' . $sumField . ')*("SalSale"."discount")/100)';

        $money = '';
        if($currency == 'USD'){
            $money = '(' . $sumField . ') - ("SalSale"."discount" / "SalSale"."ex_rate") ';
        }elseif($currency == 'BOB'){
            $money = '(' . $sumField . ') - ("SalSale"."discount") ';
        }

        $discountOperators = ' CASE ';
        $discountOperators .= ' WHEN "SalSale"."discount_type" = \'BOB\' THEN ( ' . $money . ' ) ';// its always BOB, should be money, or amount, etc. For Version 2.0
        $discountOperators .= ' WHEN "SalSale"."discount_type" = \'PERCENT\' THEN ( ' . $discountPercent . ' )';
        $discountOperators .= ' WHEN "SalSale"."discount_type" = \'NONE\' THEN ( ' . $sumField . ' ) ';
        $discountOperators .= ' END';
        return $discountOperators;
    }

////////////////////////////////////////////NEW GRAPHICS - END//////////////////////////////////////////
    
    public function vgraphics_items_customers() {
        $clientsClean = $this->SalSale->SalEmployee->SalCustomer->find('list');
        $clients[0] = "TODOS";
        foreach ($clientsClean as $key => $value) {
            $clients[$key] = $value;
        }
        $this->loadModel("AdmPeriod");
        $years = $this->AdmPeriod->find("list", array(
            "order" => array("name" => "desc"),
            "fields" => array("name", "name")
                )
        );
        $months = array(0 => "Todos", 1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio", 7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre");
        $item = $this->_find_items();

        $this->set(compact("clients", "years", "months", "item"));

        //////////////////////////////////////////////////////////////
        /*
          $this->SalSale->SalDetail->bindModel(array(
          'hasOne'=>array(
          'SalEmployee'=>array(
          'foreignKey'=>false,
          'conditions'=> array('SalSale.sal_employee_id = SalEmployee.id')
          )
          )
          ));
          $this->SalSale->SalDetail->unbindModel(array('belongsTo' => array('InvWarehouse')));
          $currencyType = "price";
          $data = $this->SalSale->SalDetail->find('all', array(
          "fields"=>array(
          //"InvItem.id",
          "InvItem.code",
          "InvItem.name",
          'SUM("SalDetail"."quantity" * (SELECT '.$currencyType.'  FROM inv_prices where inv_item_id = "SalDetail"."inv_item_id" AND date <= "SalSale"."date" AND inv_price_type_id=9 order by date DESC, date_created DESC LIMIT 1)) AS money',
          'SUM("SalDetail"."quantity") AS quantity'
          ),
          'group'=>array(
          //"InvItem.id",
          "InvItem.code",
          "InvItem.name"
          ),
          "conditions"=>array(
          "to_char(SalSale.date,'YYYY')"=>"2013",
          "SalSale.lc_state"=>"SINVOICE_APPROVED",
          //"SalDetail.inv_item_id" => $items,
          //$conditionPerson,
          //$conditionMonth
          ),
          "order"=>array('"money"'=> 'desc')
          ));

          debug($data);
         */
    }

    public function vgraphics_items_salesmen() {
        $this->loadModel("AdmProfile");
        $salesmenClean = $this->AdmProfile->find('list', array("fields" => array("AdmProfile.adm_user_id", "AdmProfile.full_name")));
        $salesmen[0] = "TODOS";
        foreach ($salesmenClean as $key => $value) {
            $salesmen[$key] = $value;
        }
        $this->loadModel("AdmPeriod");
        $years = $this->AdmPeriod->find("list", array(
            "order" => array("name" => "desc"),
            "fields" => array("name", "name")
                )
        );
        $months = array(0 => "Todos", 1 => "Enero", 2 => "Febrero", 3 => "Marzo", 4 => "Abril", 5 => "Mayo", 6 => "Junio", 7 => "Julio", 8 => "Agosto", 9 => "Septiembre", 10 => "Octubre", 11 => "Noviembre", 12 => "Diciembre");
        $item = $this->_find_items();

        $this->set(compact("salesmen", "years", "months", "item"));

        //////////////////////////////////////////////////////////////
    }

    /////////////
    /*
      public function ajax_get_graphics_data(){
      if($this->RequestHandler->isAjax()){
      $year = $this->request->data['year'];
      $currency = $this->request->data['currency'];
      $item = $this->request->data['item'];
      $string = $this->_get_bars_sales_and_time($year, $item, $currency);
      echo $string;
      }
      //		$string .= '30|54|12|114|64|100|98|80|10|50|169|222';
      }
     */

    public function ajax_get_graphics_items_customers() {
        if ($this->RequestHandler->isAjax()) {
            $year = $this->request->data['year'];
            $month = $this->request->data['month'];
            $currency = $this->request->data['currency'];
            $items = $this->request->data['items'];
            $groupBy = $this->request->data['groupBy'];
            $customer = $this->request->data['customer'];

            $showMode = $this->request->data['showMode'];
            //$string = $this->_get_bars_sales_and_time($year, $items, $currency, $client);
            //$string = $this->_get_pie_items_quantity_and_type("entrada", $year, $warehouse, $item).",";
            //$string .= $this->_get_pie_items_quantity_and_type("salida", $year, $warehouse, $item).",";

            $barsData = $this->_get_bars_sales_and_time($year, $items, $currency, $customer, "customer");
            $piesData = $this->_get_pies_sales_and_time($year, $items, $currency, $month, $customer, "customer", $showMode);
//			debug($piesData);
            $string = "";
            $string .= $barsData["quantity"] . ",";
            $string .= $barsData["money"] . ",";
            $string .= $piesData["quantity"] . ",";
            $string .= $piesData["money"] . ",";

            $string .= $piesData["topMoreQuantity"] . ",";
            $string .= $piesData["topMoreMoney"] . ",";
            $string .= $piesData["topLessQuantity"] . ",";
            $string .= $piesData["topLessMoney"] . ",";

            $string .= $this->ajax_find_colors_pie($showMode);


            echo $string;
        }
    }

    public function ajax_get_graphics_items_salesmen() {
        if ($this->RequestHandler->isAjax()) {
            $year = $this->request->data['year'];
            $month = $this->request->data['month'];
            $currency = $this->request->data['currency'];
            $items = $this->request->data['items'];
            $groupBy = $this->request->data['groupBy'];
            $salesman = $this->request->data['salesman'];

            $showMode = $this->request->data['showMode'];
            //$string = $this->_get_bars_sales_and_time($year, $items, $currency, $client);
            //$string = $this->_get_pie_items_quantity_and_type("entrada", $year, $warehouse, $item).",";
            //$string .= $this->_get_pie_items_quantity_and_type("salida", $year, $warehouse, $item).",";

            $barsData = $this->_get_bars_sales_and_time($year, $items, $currency, $salesman, "salesman");
            $piesData = $this->_get_pies_sales_and_time($year, $items, $currency, $month, $salesman, "salesman", $showMode);
            //debug($piesData);
            $string = "";
            $string .= $barsData["quantity"] . ",";
            $string .= $barsData["money"] . ",";
            $string .= $piesData["quantity"] . ",";
            $string .= $piesData["money"] . ",";

            $string .= $piesData["topMoreQuantity"] . ",";
            $string .= $piesData["topMoreMoney"] . ",";
            $string .= $piesData["topLessQuantity"] . ",";
            $string .= $piesData["topLessMoney"] . ",";

            $string .= $this->ajax_find_colors_pie($showMode);

            echo $string;
        }
    }

    private function _get_bars_sales_and_time($year, $items, $currency, $person, $personType) {
        $conditionPerson = null;
        $dataString = "";
        $dataString2 = "";
        /* 	
          if($item > 0){
          $conditionItem = array("SalDetail.inv_item_id" => $item);
          }
         */
        if ($person > 0) {
            if ($personType == "customer") {
                $conditionPerson = array("SalEmployee.sal_customer_id" => $person);
            } else {
                $conditionPerson = array("SalSale.salesman_id" => $person);
            }
        }
        $currencyType = "sale_price";
        $ex_rate = "";
        if (strtoupper($currency) == "DOLARES") {
            $currencyType = "ex_sale_price";
            $ex_rate = ' / "SalSale"."ex_rate"';
        }

        //*****************************************************************************//
        $this->SalSale->SalDetail->bindModel(array(
            'hasOne' => array(
                'SalEmployee' => array(
                    'foreignKey' => false,
                    'conditions' => array('SalSale.sal_employee_id = SalEmployee.id')
                )
            //Not using this relation because on SalEmployee already exists a customer ID.
            /* ,
              'SalCustomer'=>array(
              'foreignKey'=>false,
              'conditions'=> array('SalEmployee.sal_customer_id = SalCustomer.id')
              ) */
            )
        ));

        //$discount = '"SalSale"."discount"';

        $this->SalSale->SalDetail->unbindModel(array('belongsTo' => array('InvWarehouse')));
        $data = $this->SalSale->SalDetail->find('all', array(
            "fields" => array(
                "to_char(\"SalSale\".\"date\",'mm') AS month",
                //'SUM("SalDetail"."quantity" * (SELECT '.$currencyType.'  FROM inv_prices where inv_item_id = "SalDetail"."inv_item_id" AND date <= "SalSale"."date" AND inv_price_type_id=9 order by date DESC, date_created DESC LIMIT 1)) AS money',
                //'SUM("SalDetail"."quantity" * "SalDetail"."' . $currencyType . '") as money',
                'ROUND(  SUM("SalDetail"."quantity" * "SalDetail"."' . $currencyType . '" -  CASE WHEN "SalSale"."discount_type" = \'BOB\' THEN  "SalSale"."discount" '.$ex_rate.' WHEN "SalSale"."discount_type" = \'PERCENT\' THEN  ( "SalSale"."discount" * ("SalDetail"."quantity" * "SalDetail"."' . $currencyType . '") ) / 100    ELSE 0 END       ),2) as money',
                'SUM("SalDetail"."quantity") AS quantity'
            ),
            'group' => array("to_char(SalSale.date,'mm')"),
            "conditions" => array(
                "to_char(SalSale.date,'YYYY')" => $year,
                "SalSale.lc_state" => "SINVOICE_APPROVED",
                "SalDetail.inv_item_id" => $items,
                $conditionPerson
            )
        ));
        //*****************************************************************************//
        //format data on string to response ajax request
        $months = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12);

        foreach ($months as $month) {
            $exist = 0;
            foreach ($data as $value) {
                if ($month == (int) $value[0]['month']) {
                    $dataString .= $value[0]['money'] . "|";
                    $dataString2 .= $value[0]['quantity'] . "|";
                    //debug($dataString);
                    $exist++;
                }
            }
            if ($exist == 0) {
                $dataString .= "0|";
                $dataString2 .= "0|";
            }
        }

        return array("quantity" => substr($dataString2, 0, -1), "money" => substr($dataString, 0, -1));
    }

    private function _get_pies_sales_and_time($year, $items, $currency, $month, $person, $personType, $showMode) {
        $conditionPerson = null;
        $conditionMonth = null;
        $dataString = "";
        $dataString2 = "";

        if ($person > 0) {
            if ($personType == "customer") {
                $conditionPerson = array("SalEmployee.sal_customer_id" => $person);
            } else {
                $conditionPerson = array("SalSale.salesman_id" => $person);
            }
        }

        if ($month > 0) {
            if (count($month) == 1) {
                $conditionMonth = array("to_char(SalSale.date,'mm')" => "0" . $month);
            } else {
                $conditionMonth = array("to_char(SalSale.date,'mm')" => $month);
            }
        }
        $currencyType = "sale_price";
        $ex_rate = "";
        if (strtoupper($currency) == "DOLARES") {
            $currencyType = "ex_sale_price";
            $ex_rate = ' / "SalSale"."ex_rate"';
        }

        //********************************************* ********************************//

        $this->SalSale->SalDetail->bindModel(array(
            'hasOne' => array(
                'SalEmployee' => array(
                    'foreignKey' => false,
                    'conditions' => array('SalSale.sal_employee_id = SalEmployee.id')
                )
            )
        ));
        $this->SalSale->SalDetail->unbindModel(array('belongsTo' => array('InvWarehouse')));
        $data = $this->SalSale->SalDetail->find('all', array(
            "fields" => array(
                //"InvItem.id",
                "InvItem.code",
                "InvItem.name",
                //'SUM("SalDetail"."quantity" * (SELECT '.$currencyType.'  FROM inv_prices where inv_item_id = "SalDetail"."inv_item_id" AND date <= "SalSale"."date" AND inv_price_type_id=9 order by date DESC, date_created DESC LIMIT 1)) AS money',
                'ROUND(  SUM("SalDetail"."quantity" * "SalDetail"."' . $currencyType . '" -  CASE WHEN "SalSale"."discount_type" = \'BOB\' THEN  "SalSale"."discount" '.$ex_rate.' WHEN "SalSale"."discount_type" = \'PERCENT\' THEN  ( "SalSale"."discount" * ("SalDetail"."quantity" * "SalDetail"."' . $currencyType . '") ) / 100    ELSE 0 END       ),2) as money',
                'SUM("SalDetail"."quantity") AS quantity'
            ),
            'group' => array(
                //"InvItem.id",
                "InvItem.code",
                "InvItem.name"
            ),
            "conditions" => array(
                "to_char(SalSale.date,'YYYY')" => $year,
                "SalSale.lc_state" => "SINVOICE_APPROVED",
                "SalDetail.inv_item_id" => $items,
                $conditionPerson,
                $conditionMonth
            ),
            "order" => array('"money"' => 'desc')
        ));

        //*************************************JUST FOR TOP QUANTITIES************************************//
        $this->SalSale->SalDetail->bindModel(array(
            'hasOne' => array(
                'SalEmployee' => array(
                    'foreignKey' => false,
                    'conditions' => array('SalSale.sal_employee_id = SalEmployee.id')
                )
            )
        ));
        $this->SalSale->SalDetail->unbindModel(array('belongsTo' => array('InvWarehouse')));
        $topQuantity = $this->SalSale->SalDetail->find('all', array(
            "fields" => array(
                //"InvItem.id",
                "InvItem.code",
                "InvItem.name",
                'SUM("SalDetail"."quantity") AS quantity'
            ),
            'group' => array(
                "InvItem.code",
                "InvItem.name"
            ),
            "conditions" => array(
                "to_char(SalSale.date,'YYYY')" => $year,
                "SalSale.lc_state" => "SINVOICE_APPROVED",
                "SalDetail.inv_item_id" => $items,
                $conditionPerson,
                $conditionMonth
            ),
            "order" => array('"quantity"' => 'desc')
        ));

        //////////////////////////////////////////////////////////////////////////////////////////
        //debug($data);
        $limit = count($data);
        $dataString3 = "";
        $dataString4 = "";
        $dataString5 = "";
        $dataString6 = "";
        $counter = 1;
        //debug($limit);
        $limitbackwards = $limit - 10;
        $fullName = "";
        //debug($limitbackwards);
        $arrayForTopLessMoney = array();
        $arrayForTopLessQuantity = array();

        foreach ($data as $value) {
            $dataString .= $value['InvItem']['code'] . "==" . $value['0']['money'] . "|";
            $dataString2 .= $value['InvItem']['code'] . "==" . $value['0']['quantity'] . "|";
            $fullName = "[ " . $value['InvItem']['code'] . " ] " . $value['InvItem']['name'];
            if ($counter <= 10) {
                $dataString3 .= $fullName . "==" . $value['0']['money'] . "|";
                //debug($counter);
            }
            if ($counter >= $limitbackwards) {
                //$dataString5 .= $fullName ."==".$value['0']['money']."|";
                $arrayForTopLessMoney[] = $fullName . "==" . $value['0']['money'];
            }
            $counter++;
        }
        //////////////////////////////////START - Show mode - when option show by groups is selected/////////////////////////////
        if ($showMode <> "items") {
            $dataString = "";
            $dataString2 = "";

            //$varGroupId ="InvItem.inv_brand_id";
            $varGroupModel = "InvBrand";
            $varGroupField = "name";
            if ($showMode == "category") {
                //$varGroupId ="InvItem.inv_category_id";
                $varGroupModel = "InvCategory";
                $varGroupField = "name";
            }

            $this->SalSale->SalDetail->bindModel(array(
                'hasOne' => array(
                    'SalEmployee' => array(
                        'foreignKey' => false,
                        'conditions' => array('SalSale.sal_employee_id = SalEmployee.id')
                    ),
                    'InvBrand' => array(
                        'foreignKey' => false,
                        'conditions' => array('InvItem.inv_brand_id = InvBrand.id')
                    ),
                    'InvCategory' => array(
                        'foreignKey' => false,
                        'conditions' => array('InvItem.inv_category_id = InvCategory.id')
                    )
                )
            ));
            $this->SalSale->SalDetail->unbindModel(array('belongsTo' => array('InvWarehouse')));
            $dataGroup = $this->SalSale->SalDetail->find('all', array(
                "fields" => array(
                    //"InvItem.code",
                    //"InvItem.name",
                    $varGroupModel . "." . $varGroupField,
               //     $varGroupModel . ".color",
                    'SUM("SalDetail"."quantity" * "SalDetail"."' . $currencyType . '") as money',
                    'SUM("SalDetail"."quantity") AS quantity'
                ),
                'group' => array(
                    //"InvItem.code",
                    //"InvItem.name"
                    $varGroupModel . "." . $varGroupField,
                   // $varGroupModel . ".color",
                ),
                "conditions" => array(
                    "to_char(SalSale.date,'YYYY')" => $year,
                    "SalSale.lc_state" => "SINVOICE_APPROVED",
                    "SalDetail.inv_item_id" => $items,
                    $conditionPerson,
                    $conditionMonth
                ),
                "order" => array('"money"' => 'desc')
            ));
            //////////////////////////////////////////
            foreach ($dataGroup as $value) {
                $dataString .= $value[$varGroupModel][$varGroupField] . "==" . $value['0']['money'] . "|";
                $dataString2 .= $value[$varGroupModel][$varGroupField] . "==" . $value['0']['quantity'] . "|";
            }
        }
        //////////////////////////////////END - Show mode - when option show by groups is selected/////////////////////////////

        $counter = 1;
        foreach ($topQuantity as $value) {
            $fullName = "[ " . $value['InvItem']['code'] . " ] " . $value['InvItem']['name'];
            if ($counter <= 10) {
                $dataString4 .= $fullName . "==" . $value['0']['quantity'] . "|";
            }
            if ($counter >= $limitbackwards) {
                //$dataString6 .= $fullName ."==".$value['0']['quantity']."|";
                $arrayForTopLessQuantity[] = $fullName . "==" . $value['0']['quantity'];
            }
            $counter++;
        }

        //Now to revert order to get top less values
        $limitTopLessMoney = count($arrayForTopLessMoney);
        //debug($limitTopLessMoney);
        if ($limitTopLessMoney > 0) {
            do {
                $limitTopLessMoney = $limitTopLessMoney - 1;
                $dataString5 .= $arrayForTopLessMoney[$limitTopLessMoney] . "|";
            } while ($limitTopLessMoney > 1);
        }

        $limitTopLessQuantity = count($arrayForTopLessQuantity);
        //debug($limitTopLessQuantity);
        if ($limitTopLessQuantity > 0) {
            do {
                $limitTopLessQuantity = $limitTopLessQuantity - 1;
                $dataString6 .= $arrayForTopLessQuantity[$limitTopLessQuantity] . "|";
            } while ($limitTopLessQuantity > 1);
        }
        return array(
            "quantity" => substr($dataString2, 0, -1),
            "money" => substr($dataString, 0, -1),
            "topMoreQuantity" => substr($dataString4, 0, -1),
            "topMoreMoney" => substr($dataString3, 0, -1),
            "topLessQuantity" => substr($dataString6, 0, -1),
            "topLessMoney" => substr($dataString5, 0, -1),
        );
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
        if ($this->request->is("post")) {
            $url = array('action' => 'index_order');
            $parameters = array();
            $empty = 0;
            if (isset($this->request->data['SalSale']['doc_code']) && $this->request->data['SalSale']['doc_code']) {
                $parameters['doc_code'] = trim(strip_tags($this->request->data['SalSale']['doc_code']));
            } else {
                $empty++;
            }
            if (isset($this->request->data['SalSale']['note_code']) && $this->request->data['SalSale']['note_code']) {
                $parameters['note_code'] = trim(strip_tags($this->request->data['SalSale']['note_code']));
            } else {
                $empty++;
            }
            if (isset($this->request->data['SalSale']['searchDate']) && $this->request->data['SalSale']['searchDate']) {
                $parameters['searchDate'] = trim(strip_tags(str_replace("/", "", $this->request->data['SalSale']['searchDate'])));
            } else {
                $empty++;
            }
            if ($empty == 3) {
                $parameters['search'] = 'empty';
            } else {
                $parameters['search'] = 'yes';
            }
            $this->redirect(array_merge($url, $parameters));
        }
        ////////////////////////////END - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
        ////////////////////////////START - SETTING URL FILTERS//////////////////////////////////////
        if (isset($this->passedArgs['doc_code'])) {
            $filters['SalSale.doc_code LIKE'] = '%' . strtoupper($this->passedArgs['doc_code']) . '%';
            $doc_code = $this->passedArgs['doc_code'];
        }
        if (isset($this->passedArgs['note_code'])) {
            $filters['SalSale.note_code LIKE'] = '%' . strtoupper($this->passedArgs['note_code']) . '%';
            $note_code = $this->passedArgs['note_code'];
        }
        if (isset($this->passedArgs['searchDate'])) {
            $catchDate = $this->passedArgs['searchDate'];
            $finalDate = substr($catchDate, 0, 2) . "/" . substr($catchDate, 2, 2) . "/" . substr($catchDate, 4, 4);
            $filters['SalSale.date'] = $finalDate;
            $searchDate = $finalDate;
        }
        ////////////////////////////END - SETTING URL FILTERS//////////////////////////////////////
        ////////////////////////////START - SETTING PAGINATING VARIABLES//////////////////////////////////////
        $this->SalSale->bindModel(array('hasOne' => array('SalCustomer' => array('foreignKey' => false, 'conditions' => array('SalEmployee.sal_customer_id = SalCustomer.id')))));
        $this->SalSale->bindModel(array('hasOne' => array('AdmProfile' => array('foreignKey' => false, 'conditions' => array('SalSale.salesman_id = AdmProfile.adm_user_id')))));

//		$this->SalSale->bindModel(array('hasMany'=>array('SalDetail'=>array('foreignKey'=>false,'conditions'=> array('SalSale.id = SalDetail.sal_sale_id')))));

        $this->paginate = array(
            "conditions" => array(
                "SalSale.lc_state !=" => "NOTE_LOGIC_DELETED",
                'SalSale.lc_state LIKE' => '%NOTE%',
                "to_char(SalSale.date,'YYYY')" => $period,
                $filters
            ),
            "recursive" => 0,
//			"fields"=>array("SalSale.id", "SalSale.code", "SalSale.doc_code", "SalSale.date", "SalSale.note_code", "SalSale.sal_employee_id", "SalEmployee.name", "SalSale.lc_state", "SalCustomer.name"),
            "fields" => array("SalSale.id", "SalSale.code", "SalSale.doc_code", "SalSale.date", "SalSale.note_code", "SalSale.sal_employee_id", "SalEmployee.name", "SalSale.lc_state", "SalCustomer.name", "SalSale.salesman_id", "AdmProfile.first_name", "AdmProfile.last_name1", "SalSale.reserve", "SalSale.paid"
                /* , '(SELECT lc_state FROM sal_sales where code = "SalSale"."code" AND lc_state NOT LIKE  \'%NOTE%\') AS "inv_lc_state"' */
//                , "(SELECT ROUND(SUM(quantity * sale_price) - (SUM(quantity * sale_price) * \"SalSale\".\"discount\"/100),2) FROM sal_details WHERE sal_sale_id = \"SalSale\".\"id\") AS cost_sum"//YA NO SIRVE EL DISCOUNT PQ PUEDE VARIAR EL TIPO
			),
//			"order"=> array("SalSale.id"=>"desc"),
//            "order" => array("SalSale.date" => "desc"),
            "order" => array("SalSale.date" => "desc", "SalSale.note_code" => "desc"/*"SalSale.date_created" => "desc"*/),
            "limit" => 15,
        );
        ////////////////////////////END - SETTING PAGINATING VARIABLES//////////////////////////////////////
//		debug($this->paginate('SalSale'));
        ////////////////////////START - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
        $this->set('salSales', $this->paginate('SalSale'));
        $this->set('doc_code', $doc_code);
        $this->set('note_code', $note_code);
        $this->set('searchDate', $searchDate);
        ////////////////////////END - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
//		$this->paginate = array(
//			'conditions' => array(
//				'SalSale.lc_state !='=>'NOTE_LOGIC_DELETED'
//				,'SalSale.lc_state LIKE'=> '%ORDER%'
//			),
//			'order' => array('SalSale.id' => 'desc'),
//			'limit' => 15
//		);
//		$this->SalSale->recursive = 0;
//		$this->set('salSales', $this->paginate());
    }

    ////////////////////////color to pie vgraphics_items_customers
    public function ajax_find_colors_pie($show){
        $model = "InvCategory";
        if($show == "brand"){
            $model = "InvBrand";
        }
        $this->loadModel($model);
        $color = $this->$model->find('list', array('fields'=>array('name', 'color')));
        $string = "";
        foreach($color as $name => $color){
            $string .= $name .'%-%'.$color.'&&&';
        }
        return $string;
    }


    public function index_invoice() {
        ///////////////////////////////////////START - CREATING VARIABLES//////////////////////////////////////
        $filters = array();
        $doc_code = '';
        $note_code = '';
        $searchDate = '';
        $period = $this->Session->read('Period.name');
        ///////////////////////////////////////END - CREATING VARIABLES////////////////////////////////////////
        ////////////////////////////START - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
        if ($this->request->is("post")) {
            $url = array('action' => 'index_invoice');
            $parameters = array();
            $empty = 0;
            if (isset($this->request->data['SalSale']['doc_code']) && $this->request->data['SalSale']['doc_code']) {
                $parameters['doc_code'] = trim(strip_tags($this->request->data['SalSale']['doc_code']));
            } else {
                $empty++;
            }
            if (isset($this->request->data['SalSale']['note_code']) && $this->request->data['SalSale']['note_code']) {
                $parameters['note_code'] = trim(strip_tags($this->request->data['SalSale']['note_code']));
            } else {
                $empty++;
            }
            if (isset($this->request->data['SalSale']['searchDate']) && $this->request->data['SalSale']['searchDate']) {
                $parameters['searchDate'] = trim(strip_tags(str_replace("/", "", $this->request->data['SalSale']['searchDate'])));
            } else {
                $empty++;
            }
            if ($empty == 3) {
                $parameters['search'] = 'empty';
            } else {
                $parameters['search'] = 'yes';
            }
            $this->redirect(array_merge($url, $parameters));
        }
        ////////////////////////////END - WHEN SEARCH IS SEND THROUGH POST//////////////////////////////////////
        ////////////////////////////START - SETTING URL FILTERS//////////////////////////////////////
        if (isset($this->passedArgs['doc_code'])) {
            $filters['SalSale.doc_code LIKE'] = '%' . strtoupper($this->passedArgs['doc_code']) . '%';
            $doc_code = $this->passedArgs['doc_code'];
        }
        if (isset($this->passedArgs['note_code'])) {
            $filters['SalSale.note_code LIKE'] = '%' . strtoupper($this->passedArgs['note_code']) . '%';
            $note_code = $this->passedArgs['note_code'];
        }
        if (isset($this->passedArgs['searchDate'])) {
            $catchDate = $this->passedArgs['searchDate'];
            $finalDate = substr($catchDate, 0, 2) . "/" . substr($catchDate, 2, 2) . "/" . substr($catchDate, 4, 4);
            $filters['SalSale.date'] = $finalDate;
            $searchDate = $finalDate;
        }
        ////////////////////////////END - SETTING URL FILTERS//////////////////////////////////////
        ////////////////////////////START - SETTING PAGINATING VARIABLES//////////////////////////////////////
        $this->SalSale->bindModel(array('hasOne' => array('SalCustomer' => array('foreignKey' => false, 'conditions' => array('SalEmployee.sal_customer_id = SalCustomer.id')))));
        $this->SalSale->bindModel(array('hasOne' => array('AdmProfile' => array('foreignKey' => false, 'conditions' => array('SalSale.salesman_id = AdmProfile.adm_user_id')))));

        $this->paginate = array(
            "conditions" => array(
                "SalSale.lc_state !=" => "SINVOICE_LOGIC_DELETED",
                'SalSale.lc_state LIKE' => '%SINVOICE%',
                "to_char(SalSale.date,'YYYY')" => $period,
                $filters
            ),
            "recursive" => 0,
//			"fields"=>array("SalSale.id", "SalSale.code", "SalSale.doc_code", "SalSale.date", "SalSale.note_code", "SalSale.sal_employee_id", "SalEmployee.name", "SalSale.lc_state", "SalCustomer.name"),
            "fields" => array("SalSale.id", "SalSale.code", "SalSale.doc_code", "SalSale.date", "SalSale.note_code", "SalSale.sal_employee_id", "SalEmployee.name", "SalSale.lc_state", "SalCustomer.name", "SalSale.salesman_id", "AdmProfile.first_name", "AdmProfile.last_name1", "SalSale.reserve", "SalSale.discount", "SalSale.paid"
//                , "(SELECT ROUND(SUM(quantity * sale_price) - (SUM(quantity * sale_price) * \"SalSale\".\"discount\"/100),2) FROM sal_details WHERE sal_sale_id = \"SalSale\".\"id\") AS cost_sum"//YA NO SIRVE EL DISCOUNT PQ PUEDE VARIAR EL TIPO
			),
//			Number(payTotal) - ((Number(payTotal) * Number(discount))/100);
//			"order"=> array("SalSale.id"=>"desc"),
//			"order"=> array("SalSale.date"=>"desc"),
            "order" => array("SalSale.date" => "desc", "SalSale.note_code" => "desc"/*"SalSale.date_created" => "desc"*/), //REVISAR SI ESTA BIEN Q PRIMERO ORDENE POR CREADO Y LUEGO POR DATE DE LA NOTA
            "limit" => 15,
        );
        ////////////////////////////END - SETTING PAGINATING VARIABLES//////////////////////////////////////
//		debug($this->paginate('SalSale'));
        ////////////////////////START - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
        $this->set('salSales', $this->paginate('SalSale'));
        $this->set('doc_code', $doc_code);
        $this->set('note_code', $note_code);
        $this->set('searchDate', $searchDate);
        ////////////////////////END - SETTING PAGINATE AND OTHER VARIABLES TO THE VIEW//////////////////
    }

    ///////////////////////////////////////////// END - INDEX ////////////////////////////////////////////////
    //////////////////////////////////////////// START - SAVE ///////////////////////////////////////////////

    public function save_order() {
        $id = '';
        if (isset($this->passedArgs['id'])) {
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
        $this->loadModel('AdmUser');
        $salAdmUsers = $this->AdmUser->AdmProfile->find('list', array(
            'conditions' => array('adm_user_id !=' => 1),
			'order' => array('first_name'),
            'fields' => array('adm_user_id', 'full_name')
        ));
        $salAdmUsers = array(0 => '-- Seleccione un Vendedor --') + $salAdmUsers;
        //array_unshift($salAdmUsers,"Sin Vendedor"); //REVISAR ESTO ARRUINA EL CODIGO Q BOTA EL DROPDOWN
        $salCustomers = $this->SalSale->SalEmployee->SalCustomer->find('list'/* , array('conditions'=>array('SalCustomer.location'=>'COCHABAMBA')) */);
        $salCustomers = array(0 => '-- Seleccione un Cliente --') + $salCustomers;
        $customer = key($salCustomers);
        $salEmployees = $this->SalSale->SalEmployee->find('list', array('conditions' => array('SalEmployee.sal_customer_id' => $customer)));
        $salTaxNumbers = $this->SalSale->SalTaxNumber->find('list', array('conditions' => array('SalTaxNumber.sal_customer_id' => $customer)));

        $this->SalSale->recursive = -1;
        $this->request->data = $this->SalSale->read(null, $id);

        $documentState = '';
        $genericCode = '';
        $date = date('d/m/Y');
        $customerId = '';
        $admUserId = '';
		$discountType = 1;
        $discount = 0;
        $invoiced = '';
        $invoiceName = '';
        $invoiceDescription = '';
        $salDetails = array();
        $salPayments = array();

//		$invoiceState = array();
        $reserved = '';
        $delivered = '';
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
        if ($id <> null) {
            $date = date("d/m/Y", strtotime($this->request->data['SalSale']['date']));
            $salDetails = $this->_get_movements_details($id);
            $salPayments = $this->_get_pays_details($id);
            $documentState = $this->request->data['SalSale']['lc_state'];
            $genericCode = $this->request->data['SalSale']['code'];

            $employeeId = $this->request->data['SalSale']['sal_employee_id'];
            $customerId = $this->SalSale->SalEmployee->find('list', array('fields' => array('SalEmployee.sal_customer_id'), 'conditions' => array('SalEmployee.id' => $employeeId)));
            $salEmployees = $this->SalSale->SalEmployee->find('list', array('conditions' => array('SalEmployee.sal_customer_id' => $customerId)));
            $salTaxNumbers = $this->SalSale->SalTaxNumber->find('list', array('conditions' => array('SalTaxNumber.sal_customer_id' => $customerId)));

            $admUserId = $this->request->data['SalSale']['salesman_id'];
            $exRate = $this->request->data['SalSale']['ex_rate'];
			$discountTypeName = $this->request->data['SalSale']['discount_type'];
			if($discountTypeName == 'NONE'){$discountType=1;}else if($discountTypeName == 'PERCENT'){$discountType=2;}else/*if($discountTypeName == 'BOB')*/{$discountType=3;} 
            $discount = $this->request->data['SalSale']['discount'];
            $invoiced = $this->request->data['SalSale']['invoice'];

            $reserved = $this->request->data['SalSale']['reserve'];
            $delivered = $this->request->data['SalSale']['deliver'];

            $this->loadModel('SalInvoice');
            $invoiceName = current($this->SalInvoice->find('list', array('fields' => array('SalInvoice.invoice_number'), 'conditions' => array('SalInvoice.sal_code' => $genericCode), 'limit' => 1)));
            $invoiceDescription = current($this->SalInvoice->find('list', array('fields' => array('SalInvoice.description'), 'conditions' => array('SalInvoice.sal_code' => $genericCode), 'limit' => 1)));
			$paid = $this->request->data['SalSale']['paid'];
			////////////////////////////
//			$salInvId = $this->_get_doc_id($id, $genericCode, null, null);
//			$invoiceState = $this->SalSale->find('list', array(
//						'fields'=>array(
//							'SalSale.lc_state'
//							),
//						'conditions'=>array(
//								'SalSale.id'=>$salInvId
//							)
//				));
//			$reserveState = $this->SalSale->find('list', array(
//						'fields'=>array(
//							'SalSale.reserve'
//							),
//						'conditions'=>array(
//								'SalSale.id'=>$id
//							)
//				));
            ////////////////////////////
//			debug($salDetails);
        }
        $this->set(compact('salCustomers', 'customerId', 'salTaxNumbers', 'salEmployees', 'employeeId', 'salAdmUsers', 'admUserId', 'id', 'date', 'salDetails', 'salPayments', 'documentState', 'genericCode', 'exRate', 'discountType', 'discount', 'invoiced', 'invoiceName', 'invoiceDescription', 'reserved', 'delivered', 'paid' /* , 'invoiceState' */));
    }

    public function save_invoice() {
        $id = '';
        if (isset($this->passedArgs['id'])) {
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
        $this->loadModel('AdmUser');
        $salAdmUsers = $this->AdmUser->AdmProfile->find('list', array(
			'conditions' => array('adm_user_id !=' => 1),
            'order' => array('first_name'),
            'fields' => array('adm_user_id', 'full_name')
        ));
        $salAdmUsers = array(0 => '-- Seleccione un Vendedor --') + $salAdmUsers;

        $salCustomers = $this->SalSale->SalEmployee->SalCustomer->find('list');
        $salCustomers = array(0 => '-- Seleccione un Cliente --') + $salCustomers;
        $customer = key($salCustomers);
        $salEmployees = $this->SalSale->SalEmployee->find('list', array('conditions' => array('SalEmployee.sal_customer_id' => $customer)));
        $salTaxNumbers = $this->SalSale->SalTaxNumber->find('list', array('conditions' => array('SalTaxNumber.sal_customer_id' => $customer)));


        $this->SalSale->recursive = -1;
        $this->request->data = $this->SalSale->read(null, $id);

        $documentState = '';
        $genericCode = '';
//		$originCode = '';
        $customerId = '';
        $admUserId = '';
        $date = date('d/m/Y');
		$discountType = 1;
        $discount = 0.00;
        $invoiced = '';
        $salDetails = array();
        $salPayments = array();
        $reserved = '';
        $delivered = '';
		$invoiceName = '';
		$invoiceDescription = '';
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

        if($id <> null) {
            $date = date("d/m/Y", strtotime($this->request->data['SalSale']['date']));
            $salDetails = $this->_get_movements_details($id);
            $salPayments = $this->_get_pays_details($id);
            $documentState = $this->request->data['SalSale']['lc_state'];
            $genericCode = $this->request->data['SalSale']['code'];
            //buscar el codigo del documento origen
//			$originDocCode = $this->SalSale->find('first', array(
//				'fields'=>array('SalSale.doc_code'),
//				'conditions'=>array(
//					'SalSale.code'=>$genericCode,
//					'SalSale.lc_state LIKE'=> '%NOTE%'
//					)
//			));
//			$originCode = $originDocCode['SalSale']['doc_code'];
            $employeeId = $this->request->data['SalSale']['sal_employee_id'];
            $customerId = $this->SalSale->SalEmployee->find('list', array('fields' => array('SalEmployee.sal_customer_id'), 'conditions' => array('SalEmployee.id' => $employeeId)));
            $salEmployees = $this->SalSale->SalEmployee->find('list', array('conditions' => array('SalEmployee.sal_customer_id' => $customerId)));
            $salTaxNumbers = $this->SalSale->SalTaxNumber->find('list', array('conditions' => array('SalTaxNumber.sal_customer_id' => $customerId)));
            $admUserId = $this->request->data['SalSale']['salesman_id'];
//			$admUserId = $this->AdmUser->AdmProfile->find('list', array('fields'=>array('AdmProfile.id'),'conditions'=>array('AdmProfile.adm_user_id'=>$admProfileId)));
            $exRate = $this->request->data['SalSale']['ex_rate'];
			$discountTypeName = $this->request->data['SalSale']['discount_type'];
			if($discountTypeName == 'NONE'){$discountType=1;}else if($discountTypeName == 'PERCENT'){$discountType=2;}else/*if($discountTypeName == 'BOB')*/{$discountType=3;} 
            $discount = $this->request->data['SalSale']['discount'];
            $invoiced = $this->request->data['SalSale']['invoice'];
            $reserved = $this->request->data['SalSale']['reserve'];
            $delivered = $this->request->data['SalSale']['deliver'];
			$paid = $this->request->data['SalSale']['paid'];
            ////////////////////////////
//			$movementId = $this->_get_doc_id(null, $genericCode, 1, 1);//EL ALTO 2 MANUALMENTE VER COMO ELEGIR ESTO
//			$this->loadModel('InvMovement');
//			$movementsExistance = $this->InvMovement->find('list', array(
//						'fields'=>array(
//							'InvMovement.lc_state'
//							),
//						'conditions'=>array(
//								'InvMovement.document_code'=>$genericCode
//							)
//				));
//				debug($movementsExistance);
//				$reserveState = $this->SalSale->find('list', array(
//						'fields'=>array(
//							'SalSale.reserve'
//							),
//						'conditions'=>array(
//								'SalSale.id'=>$id
//							)
//				));
            ////////////////////////////
            ////////////////////////////////////////////////////////////////////////// DELIVER LIST //////////////////////////////////////////////////////////////////////////////////////////////////
//			if($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED'){
//				$this->loadModel('InvMovement');
//				$deliverList = $this->InvMovement->InvMovementDetail->find('all', array(
//						'fields'=>array('InvMovementDetail.inv_item_id',
//									'InvMovement.inv_warehouse_id',
//									'InvMovementDetail.quantity'),	
//						'conditions'=>array(
//							'InvMovement.document_code'=>$genericCode
//						)
//					));
//				print_r($deliverList);
//				die();
//			}
            ////////////////////////////////////////////////////////////////////////// DELIVER LIST //////////////////////////////////////////////////////////////////////////////////////////////////
//			$notDelivered = $this->SalSale->SalDetail->find('first', array(
//                'fields' => array('SUM("SalDetail"."quantity") - SUM("SalDetail"."approved") AS not_delivered',
//					'SUM("SalDetail"."backorder") AS backorder'),
//                'conditions' => array('SalSale.id' => $id)
////                ,'group' => array('SalSale.discount')
//            ));
////			debug($notDelivered[0]['not_delivered']);
////			debug($notDelivered[0]['backorder']);
////			debug($notDelivered[0]['not_delivered'] - $notDelivered[0]['backorder']);
////			die();
//			if($notDelivered[0]['not_delivered'] > 0){
//				$appButton = false;
//				if($notDelivered[0]['not_delivered'] - $notDelivered[0]['backorder'] > 0){
//					$delivButton = true;
//				}else{
//					$delivButton = false;
//				} 
//			}else{
//				$delivButton = true;
//				$appButton = true;
//			}
//			debug($delivButton);
//				$sumApproved = $this->SalSale->SalDetail->find('first', array(
//               array(
////				   'conditions'=>array('SalSale.id'=>$id),
//						'fields' => array('sum(SalDetail.approved) AS SalDetail.sumApproved'))));
//			$this->SalSale->SalDetail->virtualFields = array('sumQuantity' => 'SUM(SalDetail.quantity)');
//			$sumQuantity = $this->SalSale->SalDetail->field('sumQuantity');
//			$this->SalSale->SalDetail->virtualFields = array('sumApproved' => 'SUM(SalDetail.approved)');
//			$sumApproved = $this->SalSale->SalDetail->field('sumApproved');
//			debug($sumQuantity);
//			debug($notDelivered[0]['not_delivered']);
			$this->loadModel('SalInvoice');
            $invoiceName = current($this->SalInvoice->find('list', array('fields' => array('SalInvoice.invoice_number'), 'conditions' => array('SalInvoice.sal_code' => $genericCode), 'limit' => 1)));
            $invoiceDescription = current($this->SalInvoice->find('list', array('fields' => array('SalInvoice.description'), 'conditions' => array('SalInvoice.sal_code' => $genericCode), 'limit' => 1)));
		}

        $this->set(compact('salCustomers', 'customerId', 'salTaxNumbers', 'salEmployees', 'employeeId', 'salAdmUsers', 'admUserId', 'id', 'date', 'salDetails', 'salPayments', 'documentState', 'genericCode', 'exRate', 'discountType', 'discount', 'invoiced', 'invoiceName', 'invoiceDescription', 'reserved', 'delivered', 'paid'/*, 'appButton', 'delivButton'*//* , 'deliverList' /*movementsExistance' */));
//debug($this->request->data);
    }

    //////////////////////////////////////////// END - SAVE /////////////////////////////////////////////////
    //////////////////////////////////////////// START - AJAX ///////////////////////////////////////////////

    public function ajax_initiate_modal_add_item_in_order() {
        if ($this->RequestHandler->isAjax()) {

            $itemsWarehousesAlreadySaved = $this->request->data['itemsWarehousesAlreadySaved'];
			$action = $this->request->data['action'];
//			$warehouseItemsAlreadySaved = $this->request->data['warehouseItemsAlreadySaved'];
//			debug($itemsWarehousesAlreadySaved);
//			$date = $this->request->data['date'];

            $this->loadModel('InvWarehouse');
            $warehousesCount = $this->InvWarehouse->find('count');

//			$this->loadModel('InvWarehouse');
//			$invWarehouses = $this->InvWarehouse->find('list');
//			$warehouse = key($invWarehouses);
            ////////explode an array of delimited strings into two arrays
            foreach ($itemsWarehousesAlreadySaved as $a) {
                list($itemsAlreadySaved[]/* , $warehouseItemsAlreadySaved[] */) = explode("w", $a);
            }
//			debug($itemsAlreadySaved);
//			debug($warehouseItemsAlreadySaved);
            ////////explode an array of delimited strings into two arrays 
            $itemsAlreadySavedInWarehouse = array();
            $itemsCount = array_count_values($itemsAlreadySaved);
            foreach ($itemsCount as $key => $value) {
                if ($value == $warehousesCount) {
                    $itemsAlreadySavedInWarehouse[] = $key;
                }
            }

//				for($i=0; $i<count($itemsWarehousesAlreadySaved); $i++){
//					if($warehouseItemsAlreadySaved[$i] == $warehouse){
//						$itemsAlreadySavedInWarehouse[] = $itemsAlreadySaved[$i];
//					}	
//				}
            $items = $this->SalSale->SalDetail->InvItem->find('list', array(
                'conditions' => array(
                    'NOT' => array('InvItem.id' => $itemsAlreadySavedInWarehouse)
                ),
//				'recursive'=>-1,
                'order' => array('InvItem.code')
            ));
            $items = array(0 => '-- Seleccione un Item --') + $items;
//			$firstItemListed = key($items);
//			$stocks = $this->_get_real_stocks($firstItemListed, $warehouse);
//			$stock = $this->_find_real_item_stock($stocks, $firstItemListed);
//			//to get the last sale(9) price of item before the date
//			$this->loadModel('InvPrice');
//			$priceDirty = $this->InvPrice->find('list', array(
//				'fields'=>array('InvPrice.price'),
//				'order' => array('InvPrice.date' => 'desc'),
//				'conditions'=>array(
//					'InvPrice.inv_item_id'=>$firstItemListed
//					,'InvPrice.inv_price_type_id'=>9
//					,'InvPrice.date <='=>$date
//					),
//				'limit' => 1
//			));
//			if($priceDirty == array() || $priceDirty == null){
//				$price ='';
//			} else {
//				$price = $priceDirty;
//			}
            //to get the last sale(9) price of item before the date
//			debug($items);
//			debug($invWarehouses);
//			die();
            $this->set(compact('items'/* , 'price' */, 'invWarehouses'/* , 'stock', 'warehouse' */, 'action'));
        }
    }

    public function ajax_initiate_modal_add_item_in() {
        if ($this->RequestHandler->isAjax()) {

            $itemsWarehousesAlreadySaved = $this->request->data['itemsWarehousesAlreadySaved'];
//			$warehouseItemsAlreadySaved = $this->request->data['warehouseItemsAlreadySaved'];
//			debug($itemsWarehousesAlreadySaved);
//			$date = $this->request->data['date'];

            $this->loadModel('InvWarehouse');
            $warehousesCount = $this->InvWarehouse->find('count');

//			$this->loadModel('InvWarehouse');
//			$invWarehouses = $this->InvWarehouse->find('list');
//			$warehouse = key($invWarehouses);
            ////////explode an array of delimited strings into two arrays
            foreach ($itemsWarehousesAlreadySaved as $a) {
                list($itemsAlreadySaved[]/* , $warehouseItemsAlreadySaved[] */) = explode("w", $a);
            }
//			debug($itemsAlreadySaved);
//			debug($warehouseItemsAlreadySaved);
            ////////explode an array of delimited strings into two arrays 
            $itemsAlreadySavedInWarehouse = array();
            $itemsCount = array_count_values($itemsAlreadySaved);
            foreach ($itemsCount as $key => $value) {
                if ($value == $warehousesCount) {
                    $itemsAlreadySavedInWarehouse[] = $key;
                }
            }

//				for($i=0; $i<count($itemsWarehousesAlreadySaved); $i++){
//					if($warehouseItemsAlreadySaved[$i] == $warehouse){
//						$itemsAlreadySavedInWarehouse[] = $itemsAlreadySaved[$i];
//					}	
//				}
            $items = $this->SalSale->SalDetail->InvItem->find('list', array(
                'conditions' => array(
                    'NOT' => array('InvItem.id' => $itemsAlreadySavedInWarehouse)
                ),
//				'recursive'=>-1,
                'order' => array('InvItem.code')
            ));
            $items = array(0 => '-- Seleccione un Item --') + $items;
//			$firstItemListed = key($items);
//			$stocks = $this->_get_real_stocks($firstItemListed, $warehouse);
//			$stock = $this->_find_real_item_stock($stocks, $firstItemListed);
//			//to get the last sale(9) price of item before the date
//			$this->loadModel('InvPrice');
//			$priceDirty = $this->InvPrice->find('list', array(
//				'fields'=>array('InvPrice.price'),
//				'order' => array('InvPrice.date' => 'desc'),
//				'conditions'=>array(
//					'InvPrice.inv_item_id'=>$firstItemListed
//					,'InvPrice.inv_price_type_id'=>9
//					,'InvPrice.date <='=>$date
//					),
//				'limit' => 1
//			));
//			if($priceDirty == array() || $priceDirty == null){
//				$price ='';
//			} else {
//				$price = $priceDirty;
//			}
            //to get the last sale(9) price of item before the date
            $this->set(compact('items'/* , 'price' */, 'invWarehouses'/* , 'stock', 'warehouse' */));
        }
    }

    public function ajax_initiate_modal_edit_item() {
        if ($this->RequestHandler->isAjax()) {

            $itemsWarehousesAlreadySaved = $this->request->data['itemsWarehousesAlreadySaved'];
            $itemIdForEdit = $this->request->data['itemIdForEdit'];
            $warehouseIdForEdit = $this->request->data['warehouseIdForEdit'];
			$action = $this->request->data['action'];
			$genCode = $this->request->data['genCode'];
//			$date = $this->request->data['date'];
//			debug($warehouseIdForEdit);
//			$ff = $itemIdForEdit.'w'.$warehouseIdForEdit;
//			debug($ff);
            $itemsWarehousesAlreadySaved = array_diff($itemsWarehousesAlreadySaved, array($itemIdForEdit . 'w' . $warehouseIdForEdit));
            ////////explode an array of delimited strings into two arrays
            foreach ($itemsWarehousesAlreadySaved as $a) {
                list($itemsAlreadySaved[], $warehouseItemsAlreadySaved[]) = explode("w", $a);
            }
            ////////explode an array of delimited strings into two arrays
            $warehousesAlreadySavedByItems = array();
            for ($i = 0; $i < count($itemsWarehousesAlreadySaved); $i++) {
                if ($itemsAlreadySaved[$i] == $itemIdForEdit) {
                    $warehousesAlreadySavedByItems[] = $warehouseItemsAlreadySaved[$i];
                }
            }

            $firstWarehouseListed[] = $warehouseIdForEdit;
            //++++++++ all warehouses in the selected region that have not been taken +++++++++++++			
            $this->loadModel('InvWarehouse');
            $invWarehouses = $this->InvWarehouse->find('list', array(
                'conditions' => array(
                    'NOT' => array('InvWarehouse.id' => $warehousesAlreadySavedByItems)
//                    , 'InvWarehouse.region' => 1//THIS HAS TO BE OBTAINED FROM USER LOGED OR SOMETHING
//					,'InvWarehouse.location' => 'La Paz'
                )
//				,'recursive'=>-1
//				,'order'=>array('InvWarehouse.code')
            ));
            //++++++++ all warehouses in the selected region that have not been taken +++++++++++++
//			debug($warehouseIdForEdit);
//			$firstWarehouseListed[] = key($invWarehouses);
//			$stocks = $this->_get_real_stocks($item, $firstWarehouseListed);
//			$stock = $this->_find_real_item_stock($stocks, $item);
            //------------------------------------------------ all warehouses in the seleted region ---------------------------------------------
            $invAllWarehouses = $this->InvWarehouse->find('list', array(
                'conditions' => array(
//                    'InvWarehouse.region' => 1//THIS HAS TO BE OBTAINED FROM USER LOGED OR SOMETHING
//					'InvWarehouse.location' => 'La Paz'
                )
            ));
            $warehousesListed = array_keys($invAllWarehouses);
            //------------------------------------------------ all warehouses in the seleted region ---------------------------------------------
//			$stocksTotal = $this->_get_real_stocks_by_warehouse($itemIdForEdit, $warehousesListed);
//			$stock = $this->_find_real_item_stock_by_warehouse($stocksTotal, $itemIdForEdit, $firstWarehouseListed);
//			$stockTotal = $this->_find_real_item_stock_by_warehouse($stocksTotal, $itemIdForEdit, $warehousesListed);
//			
//			$stocksReservedTotal = $this->_get_reserved_stocks_by_warehouse($itemIdForEdit, $warehousesListed);
//			$stocksVirtualTotal = $this->_get_reserved_stocks_by_warehouse_minus_backorder($itemIdForEdit, $warehousesListed);
//			
//			$stockVirtual = $this->_find_reserved_item_stock_by_warehouse($stocksVirtualTotal, $itemIdForEdit, $firstWarehouseListed);
//			$stockReservedTotal = $this->_find_reserved_item_stock_by_warehouse($stocksReservedTotal, $itemIdForEdit, $warehousesListed);
//			
//			$stockReal = $stock;
//			$stock2 = $stockReal - $stockVirtual;
//			$stockTotal = $stockTotal - $stockReservedTotal;
            //realStock
            $realStocks = $this->_get_real_stocks_by_warehouse($itemIdForEdit, $warehousesListed);
			$realStock = $this->_find_real_item_stock_by_warehouse($realStocks, $itemIdForEdit, $firstWarehouseListed);//pq jala de interfaz
            $realStockTotal = $this->_find_real_item_stock_by_warehouse($realStocks, $itemIdForEdit, $warehousesListed);

            //reservedStock
            $reservedStocks = $this->_get_reserved_stocks_by_warehouse_minus_backorder($itemIdForEdit, $warehousesListed);
			$reservedStock = $this->_find_reserved_item_stock_by_warehouse($reservedStocks, $itemIdForEdit, $firstWarehouseListed);//pq jala de interfaz
            $reservedStockTotal = $this->_find_reserved_item_stock_by_warehouse($reservedStocks, $itemIdForEdit, $warehousesListed);

            //virtualStock
			$virtualStock = $realStock - $reservedStock;//pq jala de interfaz
            $virtualStockTotal = $realStockTotal - $reservedStockTotal;
			
			$quantities = $this->SalSale->SalDetail->find('first', array(
                'fields' => array('SalDetail.approved', 'SalDetail.quantity', 'SalDetail.backorder'),
				'conditions' => array('SalSale.code' => $genCode,
									'SalDetail.inv_item_id' => $itemIdForEdit,
									'SalDetail.inv_warehouse_id' => $warehouseIdForEdit)
            ));
			
			$approvedQuantity = $quantities['SalDetail']['approved'];

			$virtualStock = $virtualStock + ($quantities['SalDetail']['quantity'] - $quantities['SalDetail']['backorder']);
			$virtualStockTotal = $virtualStockTotal + ($quantities['SalDetail']['quantity'] - $quantities['SalDetail']['backorder']);
			
			if($virtualStock < 0){
				$virtualStock = 0;
			}
//			debug($invWarehouses);
//			die();
//			$this->loadModel('InvWarehouse');
//			$invWarehouses = $this->InvWarehouse->find('list');
//			$warehouse = key($invWarehouses);
//			////////explode an array of delimited strings into two arrays
//			foreach($itemsWarehousesAlreadySaved as $a){
//				list($itemsAlreadySaved[], $warehouseItemsAlreadySaved[]) = explode("w", $a);
//			}
//			////////explode an array of delimited strings into two arrays 
//			$itemsAlreadySavedInWarehouse = array();
//			for($i=0; $i<count($itemsWarehousesAlreadySaved); $i++){
//				if($warehouseItemsAlreadySaved[$i] == $warehouse){
//					$itemsAlreadySavedInWarehouse[] = $itemsAlreadySaved[$i];
//				}	
//			}
//			$items = $this->SalSale->SalDetail->InvItem->find('list', array(
//				'conditions'=>array(
//					'NOT'=>array('InvItem.id'=>$itemsAlreadySavedInWarehouse)
//				),
////				'recursive'=>-1,
//				'order'=>array('InvItem.code')
//			));
//			$firstItemListed = key($items);
//			$stocks = $this->_get_real_stocks($firstItemListed, $warehouse);
//			$stock = $this->_find_real_item_stock($stocks, $firstItemListed);
            //to get the last sale(9) price of item before the date
//			$this->loadModel('InvPrice');
//			$priceDirty = $this->InvPrice->find('list', array(
//				'fields'=>array('InvPrice.price'),
//				'order' => array('InvPrice.date' => 'desc'),
//				'conditions'=>array(
//					'InvPrice.inv_item_id'=>$firstItemListed
//					,'InvPrice.inv_price_type_id'=>9
//					,'InvPrice.date <='=>$date
//					),
//				'limit' => 1
//			));
//			if($priceDirty == array() || $priceDirty == null){
//				$price ='';
//			} else {
//				$price = $priceDirty;
//			}
            //to get the last sale(9) price of item before the date	TEMP
            //pq jala de interfaz
            $this->set(compact(/* 'items', 'price', */'realStock', 'invWarehouses', 'virtualStock', 'warehouseIdForEdit', 'virtualStockTotal', 'action', 'approvedQuantity'));
        }
    }

    public function ajax_initiate_modal_distrib_item() {
        if ($this->RequestHandler->isAjax()) {

//			$itemsWarehousesAlreadySaved = $this->request->data['itemsWarehousesAlreadySaved'];
            $itemIdForDistrib = $this->request->data['itemIdForDistrib']; 
            $warehouseIdOrigForDistrib = $this->request->data['warehouseIdOrigForDistrib']; 
            $saleDocCode = $this->request->data['saleDocCode'];
//			$date = $this->request->data['date'];
//			debug($warehouseIdForEdit);
//			die();
//			$ff = $itemIdForEdit.'w'.$warehouseIdForEdit;
//			debug($ff);
//			$itemsWarehousesAlreadySaved = array_diff($itemsWarehousesAlreadySaved, array($itemIdForEdit.'w'.$warehouseIdForEdit));
            ////////explode an array of delimited strings into two arrays
//			foreach($itemsWarehousesAlreadySaved as $a){
//				list($itemsAlreadySaved[], $warehouseItemsAlreadySaved[]) = explode("w", $a);
//			}
            ////////explode an array of delimited strings into two arrays
//			$warehousesAlreadySavedByItems = array();
//			for($i=0; $i<count($itemsWarehousesAlreadySaved); $i++){
//				if($itemsAlreadySaved[$i] == $itemIdForEdit){
//					$warehousesAlreadySavedByItems[] = $warehouseItemsAlreadySaved[$i];
//				}	
//			}
//			debug($warehouseIdForEdit);
            //++++++++ all warehouses in the selected region that are not the origin warehouse +++++++++++++
            $this->loadModel('InvWarehouse');
            $invWarehousesDest = $this->InvWarehouse->find('list', array(
                'conditions' => array(
                    'NOT' => array('InvWarehouse.id' => $warehouseIdOrigForDistrib)
                )
//				,'recursive'=>-1
//				,'order'=>array('InvWarehouse.code')
            ));
            $warehouseDest = key($invWarehousesDest);
            //++++++++ all warehouses in the selected region that have not been taken +++++++++++++
//			debug($warehouse);
//			die();
//			$this->loadModel('InvWarehouse');
//			$invWarehouses = $this->InvWarehouse->find('list');
//			debug($warehouse);
            $firstWarehouseDestListed[] = $warehouseDest;
//			debug($warehouse);
//			////////explode an array of delimited strings into two arrays
//			foreach($itemsWarehousesAlreadySaved as $a){
//				list($itemsAlreadySaved[], $warehouseItemsAlreadySaved[]) = explode("w", $a);
//			}
//			////////explode an array of delimited strings into two arrays 
//			$itemsAlreadySavedInWarehouse = array();
//			for($i=0; $i<count($itemsWarehousesAlreadySaved); $i++){
//				if($warehouseItemsAlreadySaved[$i] == $warehouse){
//					$itemsAlreadySavedInWarehouse[] = $itemsAlreadySaved[$i];
//				}	
//			}
//			$items = $this->SalSale->SalDetail->InvItem->find('list', array(
//				'conditions'=>array(
//					'NOT'=>array('InvItem.id'=>$itemsAlreadySavedInWarehouse)
//				),
////				'recursive'=>-1,
//				'order'=>array('InvItem.code')
//			));
//			$firstItemListed = key($items);
            //------------------------------------------------ all warehouses in the seleted region ---------------------------------------------	
            $invAllWarehouses = $this->InvWarehouse->find('list', array(
                'conditions' => array(
//                    'InvWarehouse.region' => 1//THIS HAS TO BE OBTAINED FROM USER LOGED OR SOMETHING
//					'InvWarehouse.location' => 'La Paz'
                )
            ));
            $warehousesListed = array_keys($invAllWarehouses);
            //------------------------------------------------ all warehouses in the seleted region ---------------------------------------------
//			$stocks = $this->_get_real_stocks($itemIdForEdit, $warehouse);
//			$stock = $this->_find_real_item_stock($stocks, $itemIdForEdit);
//			
////			debug($itemIdForEdit);debug($warehousesListed);
//			$stocksVirtualTotal = $this->_get_reserved_stocks_by_warehouse_minus_backorder($itemIdForEdit, $warehousesListed);
////			debug($stocksVirtualTotal);debug($itemIdForEdit);debug($firstWarehouseListed);
//			$stockVirtual = $this->_find_reserved_item_stock_by_warehouse($stocksVirtualTotal, $itemIdForEdit, $firstWarehouseListed);
//			
//			$stocksReservedTotal = $this->_get_reserved_stocks_by_warehouse($itemIdForEdit, $warehouse);
//			$stockReserved = $this->_find_reserved_item_stock_by_warehouse($stocksReservedTotal, $itemIdForEdit, array($warehouse));
//			
//			$stockReal = $stock;
//			
//			$stock = $stock - $stockReserved;
////			debug($stockReal);
////			debug($stockVirtual);
//			$stock2 = $stockReal - $stockVirtual;//minus_backorder
            ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            //realStock
            $realStocksDest = $this->_get_real_stocks($itemIdForDistrib, $warehouseDest);
            $realStockDest = $this->_find_real_item_stock($realStocksDest, $itemIdForDistrib);
//			$realStockTotal = $this->_find_real_item_stock_by_warehouse($realStocks, $itemIdForEdit, $warehousesListed);
            //reservedStock
            $reservedStocksDest = $this->_get_reserved_stocks_by_warehouse_minus_backorder($itemIdForDistrib, $warehousesListed);
            $reservedStockDest = $this->_find_reserved_item_stock_by_warehouse($reservedStocksDest, $itemIdForDistrib, $firstWarehouseDestListed);
//			$reservedStockTotal = $this->_find_reserved_item_stock_by_warehouse($reservedStocks, $itemIdForEdit, $warehousesListed);
            //virtualStock
            $virtualStockDest = $realStockDest - $reservedStockDest;
//			$stockTotal = $realStockTotal - $reservedStockTotal;

			
            $backorderDest = $this->SalSale->SalDetail->find('first', array( 
                'fields' => array('SalDetail.backorder'),
                'conditions' => array(
                    'SalSale.doc_code' => $saleDocCode,
					'SalDetail.inv_item_id' => $itemIdForDistrib,
                    'SalDetail.inv_warehouse_id' => $warehouseDest
                )
//				,'recursive'=>-1
//				,'order'=>array('InvWarehouse.code')
            ));
//			debug($bo);
            if ($backorderDest == array()) {
                $backorderDest = '';
            } else {
                $backorderDest = $backorderDest['SalDetail']['backorder'];
            }
//			debug($bo);
//			
//			$stocksO = $this->_get_real_stocks($itemIdForEdit, $warehouseIdForEdit);
//			$stockO = $this->_find_real_item_stock($stocksO, $itemIdForEdit);
//			
//			$stocksVirtualTotalO = $this->_get_reserved_stocks_by_warehouse_minus_backorder($itemIdForEdit, $warehousesListed);
//			$stockVirtualO = $this->_find_reserved_item_stock_by_warehouse($stocksVirtualTotalO, $itemIdForEdit, array($warehouseIdForEdit));
//			
//			$stock3 = $stockO - $stockVirtualO;


            $realStocksOrig = $this->_get_real_stocks($itemIdForDistrib, $warehouseIdOrigForDistrib);
            $realStockOrig = $this->_find_real_item_stock($realStocksOrig, $itemIdForDistrib);

            $reservedStocksOrig = $this->_get_reserved_stocks_by_warehouse_minus_backorder($itemIdForDistrib, $warehousesListed);
            $reservedStockOrig = $this->_find_reserved_item_stock_by_warehouse($reservedStocksOrig, $itemIdForDistrib, array($warehouseIdOrigForDistrib));

            $virtualStockOrig = $realStockOrig - $reservedStockOrig;

//			debug($stock2);
            //to get the last sale(9) price of item before the date
//			$this->loadModel('InvPrice');
//			$priceDirty = $this->InvPrice->find('list', array(
//				'fields'=>array('InvPrice.price'),
//				'order' => array('InvPrice.date' => 'desc'),
//				'conditions'=>array(
//					'InvPrice.inv_item_id'=>$firstItemListed
//					,'InvPrice.inv_price_type_id'=>9
//					,'InvPrice.date <='=>$date
//					),
//				'limit' => 1
//			));
//			if($priceDirty == array() || $priceDirty == null){
//				$price ='';
//			} else {
//				$price = $priceDirty;
//			}
            //to get the last sale(9) price of item before the date
            $this->set(compact(/* 'items', 'price', */ 'invWarehousesDest', 'virtualStockDest', 'realStockDest', 'stock2', 'backorderDest', 'virtualStockOrig', 'realStockOrig' /* , 'warehouseIdForEdit' */));
        }
    }

    public function ajax_update_warehouse_price_stock_modal_order() {
        if ($this->RequestHandler->isAjax()) {
            $itemsWarehousesAlreadySaved = $this->request->data['itemsWarehousesAlreadySaved'];
            $item = $this->request->data['item'];
            $date = $this->request->data['date'];
			$action = $this->request->data['action'];
//			debug($action);
//			$warehouse = $this->request->data['warehouse'];
            ////////explode an array of delimited strings into two arrays
            foreach ($itemsWarehousesAlreadySaved as $a) {
                list($itemsAlreadySaved[], $warehouseItemsAlreadySaved[]) = explode("w", $a);
            }
            ////////explode an array of delimited strings into two arrays
            $warehousesAlreadySavedByItems = array();
            for ($i = 0; $i < count($warehouseItemsAlreadySaved); $i++) {
                if ($itemsAlreadySaved[$i] == $item) {
                    $warehousesAlreadySavedByItems[] = $warehouseItemsAlreadySaved[$i];
                }
            }
            //********************************** warehouse that shows first **************************************
            $this->loadModel('InvWarehouse');
            $invWarehouses = $this->InvWarehouse->find('list', array(//CUIDADO CON ESTO YA QUE CUANDO EXISTA UN ALMACEN QUE NO CONTENGA ESTE ITEM IGUAL SE MOSTRARA
                'conditions' => array(
                    'NOT' => array('InvWarehouse.id' => $warehousesAlreadySavedByItems)
//                    , 'InvWarehouse.region' => 1//THIS HAS TO BE OBTAINED FROM USER LOGED OR SOMETHING
//					 ,'InvWarehouse.location' => 'La Paz'
				)
//				,'recursive'=>-1
//				,'order'=>array('InvWarehouse.code')
            ));
            $firstWarehouseListed[] = key($invWarehouses);
            //********************************** warehouse that shows first **************************************
//			$stocks = $this->_get_real_stocks($item, $firstWarehouseListed);
//			$stock = $this->_find_real_item_stock($stocks, $item);
            //------------------------------------------------ all warehouses in the seleted region ---------------------------------------------
            $invAllWarehouses = $this->InvWarehouse->find('list', array(
                'conditions' => array(
//                    'InvWarehouse.region' => 1//THIS HAS TO BE OBTAINED FROM USER LOGED OR SOMETHING
//					'InvWarehouse.location' => 'La Paz'
                )
            ));
            $warehousesListed = array_keys($invAllWarehouses);
            //------------------------------------------------ all warehouses in the seleted region ---------------------------------------------
//			$stocksTotal = $this->_get_real_stocks_by_warehouse($item, $warehousesListed);
//			$stock = $this->_find_real_item_stock_by_warehouse($stocksTotal, $item, $firstWarehouseListed);
//			$stockTotal = $this->_find_real_item_stock_by_warehouse($stocksTotal, $item, $warehousesListed);
//			$stocksReservedTotal = $this->_get_reserved_stocks_by_warehouse($item, $warehousesListed);
//			$stockReserved = $this->_find_reserved_item_stock_by_warehouse($stocksReservedTotal, $item, $firstWarehouseListed);
//			$stockReservedTotal = $this->_find_reserved_item_stock_by_warehouse($stocksReservedTotal, $item, $warehousesListed);
//			$stockReal = $stock;
//			
//			$stock = $stock - $stockReserved;
//			$stockTotal = $stockTotal - $stockReservedTotal;
            //realStock
            $realStocks = $this->_get_real_stocks_by_warehouse($item, $warehousesListed);
            $realStock = $this->_find_real_item_stock_by_warehouse($realStocks, $item, $firstWarehouseListed);
            $realStockTotal = $this->_find_real_item_stock_by_warehouse($realStocks, $item, $warehousesListed);

            //reservedStock
            $reservedStocks = $this->_get_reserved_stocks_by_warehouse_minus_backorder($item, $warehousesListed);
            $reservedStock = $this->_find_reserved_item_stock_by_warehouse($reservedStocks, $item, $firstWarehouseListed);
            $reservedStockTotal = $this->_find_reserved_item_stock_by_warehouse($reservedStocks, $item, $warehousesListed);

            //virtualStock
            $virtualStock = $realStock - $reservedStock;
            $virtualStockTotal = $realStockTotal - $reservedStockTotal;

			if($virtualStockTotal < 0){
				$virtualStockTotal = 0;
			}
			if($virtualStock < 0){
				$virtualStock = 0;
			}
            //to get the last sale(9) price of item before the date
            $this->loadModel('InvPrice');
            $priceDirty = $this->InvPrice->find('list', array(
                'fields' => array('InvPrice.price'),
                'order' => array('InvPrice.date' => 'desc'),
                'conditions' => array(
                    'InvPrice.inv_item_id' => $item
                    , 'InvPrice.inv_price_type_id' => 9
                    , 'InvPrice.date <=' => $date
                ),
                'limit' => 1
            ));
            if ($priceDirty == array() || $priceDirty == null) {
                $price = '';
            } else {
                $price = $priceDirty;
            }
            //to get the last sale(9) price of item before the date
            $this->set(compact('price', 'virtualStock', 'virtualStockTotal', 'realStock', 'invWarehouses', 'action'));
        }
    }

    public function ajax_update_warehouse_price_stock_modal() {
        if ($this->RequestHandler->isAjax()) {
            $itemsWarehousesAlreadySaved = $this->request->data['itemsWarehousesAlreadySaved'];
            $item = $this->request->data['item'];
            $date = $this->request->data['date'];
//			$warehouse = $this->request->data['warehouse'];
            ////////explode an array of delimited strings into two arrays
            foreach ($itemsWarehousesAlreadySaved as $a) {
                list($itemsAlreadySaved[], $warehouseItemsAlreadySaved[]) = explode("w", $a);
            }
            ////////explode an array of delimited strings into two arrays
            $warehousesAlreadySavedByItems = array();
            for ($i = 0; $i < count($warehouseItemsAlreadySaved); $i++) {
                if ($itemsAlreadySaved[$i] == $item) {
                    $warehousesAlreadySavedByItems[] = $warehouseItemsAlreadySaved[$i];
                }
            }
            $this->loadModel('InvWarehouse');
            $invWarehouses = $this->InvWarehouse->find('list', array(
                'conditions' => array(
                    'NOT' => array('InvWarehouse.id' => $warehousesAlreadySavedByItems)
                )
//				,'recursive'=>-1
//				,'order'=>array('InvWarehouse.code')
            ));
            $firstWarehouseListed = key($invWarehouses);
//			debug($invWarehouses);
//			debug($firstWarehouseListed);
            $realStocks = $this->_get_real_stocks($item, $firstWarehouseListed); //$firstWarehouseListed);//get all the stocks for all the $items by $warehouse
//			debug($stocks);
            $realStock = $this->_find_real_item_stock($realStocks, $item);
//			debug($stock);
            //to get the last sale(9) price of item before the date
            $this->loadModel('InvPrice');
            $priceDirty = $this->InvPrice->find('list', array(
                'fields' => array('InvPrice.price'),
                'order' => array('InvPrice.date' => 'desc'),
                'conditions' => array(
                    'InvPrice.inv_item_id' => $item
                    , 'InvPrice.inv_price_type_id' => 9
                    , 'InvPrice.date <=' => $date
                ),
                'limit' => 1
            ));
            if ($priceDirty == array() || $priceDirty == null) {
                $price = '';
            } else {
                $price = $priceDirty;
            }
            //to get the last sale(9) price of item before the date

            $this->set(compact('price', 'realStock', 'invWarehouses'));
        }
    }

//	public function ajax_update_stock_modal_1(){
//		if($this->RequestHandler->isAjax()){
//			$item = $this->request->data['item'];
//			
//			
//			/////////////////for new stock method 
//			$stocks = $this->_get_real_stocks($item, $warehouse);//get all the stocks
//			///////////////////
//			$stock = $this->_find_real_item_stock($stocks, $item);
////			$stock = $this->_find_stock($item, $warehouse);			
//			
//			$this->set(compact('stock'));
//		}
//	}

    public function ajax_list_controllers_inside() {
        if ($this->RequestHandler->isAjax()) {
            $customer = $this->request->data['customer']; //???????????????????
            //	print_r( $customer);
            //	$admControllers = $this->AdmMenu->AdmAction->AdmController->find('list', array('conditions'=>array('AdmController.adm_module_id'=>$module)));
            $salEmployees = $this->SalSale->SalEmployee->find('list', array('conditions' => array('SalEmployee.sal_customer_id' => $customer)));
            $salTaxNumbers = $this->SalSale->SalTaxNumber->find('list', array('conditions' => array('SalTaxNumber.sal_customer_id' => $customer)));

            //	print_r( $salEmployees);
            //	$controller = key($admControllers);
            //	$employee = key($salEmployees);
            //$admActions = $this->AdmMenu->AdmAction->find('list', array('conditions'=>array('AdmAction.adm_controller_id'=>$controller)));
            //	$admActions = $this->_list_action_inside($controller);
            $this->set(compact('salEmployees', 'salTaxNumbers'/* 'admControllers','admActions' */));
        } else {
            $this->redirect($this->Auth->logout());
        }
    }

    public function ajax_update_price_stock_modal() {
        if ($this->RequestHandler->isAjax()) {
//			$itemsWarehousesAlreadySaved = $this->request->data['itemsWarehousesAlreadySaved'];
//			$itemsAlreadySaved = $this->request->data['itemsAlreadySaved'];
//			$warehouseItemsAlreadySaved = $this->request->data['warehouseItemsAlreadySaved'];
            $item = $this->request->data['item'];

            $date = $this->request->data['date'];
            $warehouse = $this->request->data['warehouse'];
			 $action = $this->request->data['action'];
//				////////explode an array of delimited strings into two arrays
//				foreach($itemsWarehousesAlreadySaved as $a){
//					list($itemsAlreadySaved[], $warehouseItemsAlreadySaved[]) = explode("w", $a);
//				}
//				////////explode an array of delimited strings into two arrays
//				$itemsAlreadySavedInWarehouse = array();
//				for($i=0; $i<count($itemsAlreadySaved); $i++){
//					if($warehouseItemsAlreadySaved[$i] == $warehouse){
//						$itemsAlreadySavedInWarehouse[] = $itemsAlreadySaved[$i];
//					}	
//				}
//			
//			$items = $this->SalSale->SalDetail->InvItem->find('list', array(
//				'conditions'=>array(
//					'NOT'=>array('InvItem.id'=>$itemsAlreadySavedInWarehouse)
//				),
////				'recursive'=>-1,
//				'order'=>array('InvItem.code')
//			));
//			
//			$item = key($items);	
            $firstWarehouseListed[] = $warehouse;
            //------------------------------------------------ all warehouses in the seleted region ---------------------------------------------
            $this->loadModel('InvWarehouse');
            $invAllWarehouses = $this->InvWarehouse->find('list', array(
                'conditions' => array(
//					'NOT'=>array('InvWarehouse.id'=>$warehousesAlreadySavedByItems)
//                    'InvWarehouse.region' => 1//THIS HAS TO BE OBTAINED FROM USER LOGED OR SOMETHING
//					'InvWarehouse.location' => 'La Paz'
				)
//				,'recursive'=>-1
//				,'order'=>array('InvWarehouse.code')
            ));
            $warehousesListed = array_keys($invAllWarehouses);
            //------------------------------------------------ all warehouses in the seleted region ---------------------------------------------
//			$stocksTotal = $this->_get_real_stocks_by_warehouse($item, $warehousesListed);
//			$stock = $this->_find_real_item_stock_by_warehouse($stocksTotal, $item, $firstWarehouseListed);
//			$stockTotal = $this->_find_real_item_stock_by_warehouse($stocksTotal, $item, $warehousesListed);
//			$stocksTotal = $this->_get_real_stocks_by_warehouse($item, $warehousesListed);
//			$stock = $this->_find_real_item_stock_by_warehouse($stocksTotal, $item, $firstWarehouseListed);
//			$stockTotal = $this->_find_real_item_stock_by_warehouse($stocksTotal, $item, $warehousesListed);
//			
//			
//			$stocksReservedTotal = $this->_get_reserved_stocks_by_warehouse($item, $warehousesListed);
//			$stockReserved = $this->_find_reserved_item_stock_by_warehouse($stocksReservedTotal, $item, $firstWarehouseListed);
//			$stockReservedTotal = $this->_find_reserved_item_stock_by_warehouse($stocksReservedTotal, $item, $warehousesListed);
//			
//			$stockReal = $stock;
//			
//			$stock = $stock - $stockReserved;
//			$stockTotal = $stockTotal - $stockReservedTotal;			
            //realStock
            $realStocks = $this->_get_real_stocks_by_warehouse($item, $warehousesListed);
            $realStock = $this->_find_real_item_stock_by_warehouse($realStocks, $item, $firstWarehouseListed);
            $realStockTotal = $this->_find_real_item_stock_by_warehouse($realStocks, $item, $warehousesListed);

            //reservedStock
            $reservedStocks = $this->_get_reserved_stocks_by_warehouse_minus_backorder($item, $warehousesListed);
            $reservedStock = $this->_find_reserved_item_stock_by_warehouse($reservedStocks, $item, $firstWarehouseListed);
            $reservedStockTotal = $this->_find_reserved_item_stock_by_warehouse($reservedStocks, $item, $warehousesListed);

            //virtualStock
            $virtualStock = $realStock - $reservedStock;
            $virtualStockTotal = $realStockTotal - $reservedStockTotal;

			if($virtualStock < 0){
				$virtualStock = 0;
			}

//			$stocks = $this->_get_real_stocks($item, $warehouse);
//			$stock = $this->_find_real_item_stock($stocks, $item);
            //to get the last sale(9) price of item before the date
            $this->loadModel('InvPrice');
            $priceDirty = $this->InvPrice->find('list', array(
                'fields' => array('InvPrice.price'),
                'order' => array('InvPrice.date' => 'desc'),
                'conditions' => array(
                    'InvPrice.inv_item_id' => $item
                    , 'InvPrice.inv_price_type_id' => 9
                    , 'InvPrice.date <=' => $date
                ),
                'limit' => 1
            ));
            if ($priceDirty == array() || $priceDirty == null) {
                $price = '';
            } else {
                $price = $priceDirty;
            }
            //to get the last sale(9) price of item before the date

            $this->set(compact(/* 'items', */ 'price', 'virtualStock', 'virtualStockTotal', 'realStock', 'action'));
        }
    }

    public function ajax_update_stock_modal() {
        if ($this->RequestHandler->isAjax()) {
			$genCode = $this->request->data['genCode'];
            $item = $this->request->data['item'];
            $warehouse = $this->request->data['warehouse'];
			$lastWarehouse = $this->request->data['lastWarehouse'];
			$action = $this->request->data['action'];
//			$this->loadModel('InvWarehouse');
//			$invWarehouses = $this->InvWarehouse->find('list', array(
//				'conditions'=>array(
//					'InvWarehouse.region'=>1//THIS HAS TO BE OBTAINED FROM USER LOGED OR SOMETHING
//				)
//			));
//				
//			$warehousesListed = array_keys($invWarehouses);
//			
//			$stocksTotal = $this->_get_real_stocks_by_warehouse($item, $warehousesListed);
//			$stocks = $this->_get_real_stocks($item, $warehouse);
//			$stock = $this->_find_real_item_stock($stocks, $item);
//			
//			$stocksReservedTotal = $this->_get_reserved_stocks_by_warehouse($item, $warehousesListed);
//			$stockReserved = $this->_find_reserved_item_stock_by_warehouse($stocksReservedTotal, $item, array($warehouse));
//			$stockReservedTotal = $this->_find_reserved_item_stock_by_warehouse($stocksReservedTotal, $item, $warehousesListed);
//			
//			$stock = $stock - $stockReserved;
//			$stockTotal = $stockTotal - $stockReservedTotal;	

            $firstWarehouseListed[] = $warehouse;
            //------------------------------------------------ all warehouses in the seleted region ---------------------------------------------
            $this->loadModel('InvWarehouse');
            $invAllWarehouses = $this->InvWarehouse->find('list', array(
                'conditions' => array(
//					'NOT'=>array('InvWarehouse.id'=>$warehousesAlreadySavedByItems)
//                    'InvWarehouse.region' => 1//THIS HAS TO BE OBTAINED FROM USER LOGED OR SOMETHING
//					'InvWarehouse.location' => 'La Paz'
				)
//				,'recursive'=>-1
//				,'order'=>array('InvWarehouse.code')
            ));
            $warehousesListed = array_keys($invAllWarehouses);
            //------------------------------------------------ all warehouses in the seleted region ---------------------------------------------
//			$stocksTotal = $this->_get_real_stocks_by_warehouse($item, $warehousesListed);
//			$stock = $this->_find_real_item_stock_by_warehouse($stocksTotal, $item, $firstWarehouseListed);
//			$stockTotal = $this->_find_real_item_stock_by_warehouse($stocksTotal, $item, $warehousesListed);
//			$stocksTotal = $this->_get_real_stocks_by_warehouse($item, $warehousesListed);
//			$stock = $this->_find_real_item_stock_by_warehouse($stocksTotal, $item, $firstWarehouseListed);
//			$stockTotal = $this->_find_real_item_stock_by_warehouse($stocksTotal, $item, $warehousesListed);
//			
//			
//			$stocksReservedTotal = $this->_get_reserved_stocks_by_warehouse($item, $warehousesListed);
//			$stockReserved = $this->_find_reserved_item_stock_by_warehouse($stocksReservedTotal, $item, $firstWarehouseListed);
//			$stocksVirtualTotal = $this->_get_reserved_stocks_by_warehouse_minus_backorder($item, $warehousesListed);
//			$stockVirtual = $this->_find_reserved_item_stock_by_warehouse($stocksVirtualTotal, $item, $firstWarehouseListed);
//			$stockReservedTotal = $this->_find_reserved_item_stock_by_warehouse($stocksReservedTotal, $item, $warehousesListed);
//			$stockReal = $stock;
//			
//			$stock = $stock - $stockReserved;
//			$stock2 = $stockReal - $stockVirtual;//minus_backorder
//			$stockTotal = $stockTotal - $stockReservedTotal;	
            //realStock
            $realStocks = $this->_get_real_stocks_by_warehouse($item, $warehousesListed);
            $realStock = $this->_find_real_item_stock_by_warehouse($realStocks, $item, $firstWarehouseListed); //pq jala de interfaz
            $realStockTotal = $this->_find_real_item_stock_by_warehouse($realStocks, $item, $warehousesListed);

            //reservedStock
            $reservedStocks = $this->_get_reserved_stocks_by_warehouse_minus_backorder($item, $warehousesListed);
            $reservedStock = $this->_find_reserved_item_stock_by_warehouse($reservedStocks, $item, $firstWarehouseListed); //pq jala de interfaz
            $reservedStockTotal = $this->_find_reserved_item_stock_by_warehouse($reservedStocks, $item, $warehousesListed);

            //virtualStock
            $virtualStock = $realStock - $reservedStock;
            $virtualStockTotal = $realStockTotal - $reservedStockTotal;

			$quantities = $this->SalSale->SalDetail->find('first', array(
					'fields' => array('SalDetail.approved', 'SalDetail.quantity', 'SalDetail.backorder'),
					'conditions' => array('SalSale.code' => $genCode,
										'SalDetail.inv_item_id' => $item,
										'SalDetail.inv_warehouse_id' => $lastWarehouse)
				));
			
			if($warehouse == $lastWarehouse){
				$virtualStock = $virtualStock + ($quantities['SalDetail']['quantity'] - $quantities['SalDetail']['backorder']);
			}
			
			$virtualStockTotal = $virtualStockTotal + ($quantities['SalDetail']['quantity'] - $quantities['SalDetail']['backorder']);

			if($virtualStock < 0){
				$virtualStock = 0;
			}
			
            $this->set(compact(/* 'items','price', */ 'virtualStock', 'virtualStockTotal', 'realStock'/* , 'stock2' */, 'action'));
        }
    }

     public function ajax_save_movement() {
        if ($this->RequestHandler->isAjax()) {
            ////////////////////////////////////////////START - RECIEVE AJAX////////////////////////////////////////////////////////
//			print_r($this->request->data);
            //For making algorithm
            $ACTION = $this->request->data['ACTION'];
            $OPERATION = $this->request->data['OPERATION'];
            $STATE = $this->request->data['STATE']; //also for Movement
//			debug($ACTION);
//			debug($OPERATION);
//			debug($STATE);
//			die();
//			//For validate before approve OUT or cancelled IN
            $arrayForValidate = array();
            if (isset($this->request->data['arrayForValidate'])) {
                $arrayForValidate = $this->request->data['arrayForValidate'];
//							print_r($arrayForValidate);
//			die();
            }
            //Sale
            $saleId = $this->request->data['purchaseId'];
            $saleNoteDocCode = $this->request->data['movementDocCode']; /// NOT-
            $saleCode = $this->request->data['movementCode']; /// VEN-
//			debug($STATE);
//			debug($saleId);
            ////////////////////////////////////////////////////////////////////////
//			if(($ACTION == 'save_invoice' && $saleId != '') && ($ACTION == 'save_invoice' && $OPERATION != 'ADD_PAY') && ($ACTION == 'save_invoice' && $OPERATION != 'EDIT_PAY') && ($ACTION == 'save_invoice' && $OPERATION != 'DELETE_PAY')){
			if (($saleId != '') && ($OPERATION != 'ADD_PAY') && ($OPERATION != 'EDIT_PAY') && ($OPERATION != 'DELETE_PAY')) {
                $reservedState = $this->SalSale->find('list', array(
                    'fields' => array('SalSale.reserve'),
                    'conditions' => array(
                        'SalSale.id' => $saleId
                    )
                ));
//				print_r($reservedState);
				//AUMENTAR LA CONDICION DE SI ESTA EN ESTADO SINVOICE_LOGIC_DELETED O SINVOICE_CANCELLED TB SALE
                if ($ACTION == 'save_invoice' && current($reservedState) == false) {
                    echo 'BLOCK|onGeneratingParameters';
                    exit();
				//AUMENTAR LA CONDICION DE SI ESTA EN ESTADO NOTE_LOGIC_DELETED O NOTE_CANCELLED TB SALE	
                } elseif ($ACTION == 'save_order' && current($reservedState) == true) {
//					print_r($reservedState);
                    echo 'BLOCK|onGeneratingParameters';
                    exit();
                }
            }
            if ($STATE == 'NOTE_RESERVED') {
                $saleId2 = $this->_get_doc_id($saleId, $saleCode, null, null);
                $lcState = $this->SalSale->find('list', array(
                    'fields' => array('SalSale.lc_state'),
                    'conditions' => array(
                        'SalSale.id' => $saleId2
                    )
                ));
//				debug($lcState);
            }
            ///////////////////////////////////////////////////////////////////////
            $noteCode = $this->request->data['noteCode'];
            $date = $this->request->data['date'];
            $employee = $this->request->data['employee'];
            $taxNumber = $this->request->data['taxNumber'];
            $salesman = $this->request->data['salesman'];
            $description = $this->request->data['description'];
            $exRate = $this->request->data['exRate'];
			$discountType = $this->request->data['discountType'];
			if($discountType==1){$discountTypeName='NONE';}else if($discountType == 2){$discountTypeName='PERCENT';}else/*if($discountTypeName == 'BOB')*/{$discountTypeName='BOB';}
            $discount = $this->request->data['discount'];
            $invoiced = $this->request->data['invoiced'];
//			$invoicePercent = $this->request->data['invoiced'];
            //Invoice
            $invoiceNumber = $this->request->data['invoiceNumber'];
            $invoiceDescription = $this->request->data['invoiceDescription'];
            //Sale Details
            $warehouseId = $this->request->data['warehouseId'];
//			debug($warehouseId);
			if($OPERATION == 'ADD' || $OPERATION == 'EDIT'/* || $OPERATION == 'DELETE'*/){
				$this->loadModel('InvWarehouse');
				$location = $this->InvWarehouse->find('list', array(
				'fields'=>array(
					'InvWarehouse.location'),
				'conditions'=>array(
					'InvWarehouse.id'=>$warehouseId
					)	
				));
			}
            $itemId = $this->request->data['itemId'];
            $salePrice = $this->request->data['salePrice'];
            $warehouseLastOrigId = $this->request->data['warehouseLastOrigId'];
            $quantity = $this->request->data['quantity'];
            $backorder = $this->request->data['backorder'];
            $exSalePrice = $salePrice / $exRate;
            if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED') {
                //variables used to assign Sale Price when Invoice is APPROVED
                $arrayItemsDetails = $this->request->data['arrayItemsDetails'];
            }
            if ((/* $ACTION == 'save_invoice' && */ $OPERATION == 'ADD_PAY') || (/* $ACTION == 'save_invoice' && */ $OPERATION == 'EDIT_PAY') || (/* $ACTION == 'save_invoice' && */ $OPERATION == 'DELETE_PAY')) {
                //variables used to save Pays assigned on Invoice
                $payDate = $this->request->data['payDate'];
                $payAmount = $this->request->data['payAmount'];
                $payDescription = $this->request->data['payDescription'];
            }
            if ($OPERATION == 'DISTRIB') {
                $backorderLastOrig = $this->request->data['backorderLastOrig'];
                $quantityLastToDistrib = $this->request->data['quantityLastToDistrib'];
                $warehouseLastOrigId = $this->request->data['warehouseLastOrigId'];
            }
//			debug($OPERATION);
//			debug($warehouseLastOrigId);
//			debug($quantity);
//			debug($warehouseId);
//			debug($quantityLastToDistrib);
//			die();
            //Internal variables
            $error = 0;
            $saleInvoiceDocCode = '';
            ////////////////////////////////////////////END - RECIEVE AJAX////////////////////////////////////////////////////////
            $otherId = null;
            if ($saleId != '') {
                /* $arraySaleInvoice['id'] */$otherId = $this->_get_doc_id($saleId, $saleCode, null, null);
            }
            ////////////////////////////////////////////////START - SET DATA/////////////////////////////////////////////////////
            //header for NOTE // or header for INVOICE when save_invoice
            $arraySaleNote['note_code'] = $noteCode;
            $arraySaleNote['date'] = $date;
            $arraySaleNote['sal_employee_id'] = $employee;
            $arraySaleNote['sal_tax_number_id'] = $taxNumber;
            $arraySaleNote['salesman_id'] = $salesman;
			if($OPERATION == 'ADD' || $OPERATION == 'a'/* || $OPERATION == 'DELETE'*/){
				$arraySaleNote['location']=current($location);
			}			
            $arraySaleNote['description'] = $description;
            $arraySaleNote['ex_rate'] = $exRate;
			$arraySaleNote['discount_type'] = $discountTypeName;
            $arraySaleNote['discount'] = $discount;
            $arraySaleNote['invoice'] = $invoiced;
            if ($STATE != 'NOTE_RESERVED' AND $STATE != 'SINVOICE_APPROVED' AND $STATE != 'NOTE_PAY' AND $STATE != 'SINVOICE_PAY') {
                $arraySaleNote['lc_state'] = $STATE;
            }
//			print_r($arraySaleNote);
//			debug($arraySaleNote['deliver']);
//			debug($arraySaleInvoice['deliver']);
            if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED') {			
                $arraySaleNote['deliver'] = true;
            }
//			debug($ACTION);
//			debug($saleId);
//			debug($otherId);
//			die();
            if (($ACTION == 'save_order') || ($ACTION == 'save_invoice' && $saleId != '' && $otherId != null)) {
                //header for INVOICE
                $arraySaleInvoice['note_code'] = $noteCode;
                $arraySaleInvoice['date'] = $date;
                $arraySaleInvoice['sal_employee_id'] = $employee;
                $arraySaleInvoice['sal_tax_number_id'] = $taxNumber;
                $arraySaleInvoice['salesman_id'] = $salesman;
                $arraySaleInvoice['description'] = $description;
				if($OPERATION == 'ADD' || $OPERATION == 'EDIT'/* || $OPERATION == 'DELETE'*/){
					$arraySaleInvoice['location']=current($location);
				}
                $arraySaleInvoice['ex_rate'] = $exRate;
				$arraySaleInvoice['discount_type'] = $discountTypeName;
                $arraySaleInvoice['discount'] = $discount;
                $arraySaleInvoice['invoice'] = $invoiced;
                if ($STATE == 'NOTE_RESERVED') {// 'NOTE_APPROVED') {
                    if (current($lcState) == 'DRAFT') {
                        $arraySaleInvoice['lc_state'] = 'SINVOICE_PENDANT';
                    }
                    $arraySaleNote['reserve'] = true;
                    $arraySaleInvoice['reserve'] = true;
                } else if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED') {
                    $arraySaleInvoice['deliver'] = true;
//                    $arraySaleInvoice['lc_state'] = 'NOTE_APPROVED';
                }
            }
//			print_r($arraySaleInvoice);
//			debug($arraySaleNote['deliver']);
//			debug($arraySaleInvoice['deliver']);
//			die();
//			else
//			$arraySaleNote['paid'] = false;
            if ((/* $ACTION == 'save_invoice' && */ $OPERATION == 'ADD_PAY') || (/* $ACTION == 'save_invoice' && */ $OPERATION == 'EDIT_PAY') || (/* $ACTION == 'save_invoice' && */ $OPERATION == 'DELETE_PAY')) {
               
				$currentState = $this->SalSale->find('list', array(
				'fields'=>array('SalSale.lc_state'),
				'conditions'=>array(
					'SalSale.id'=>$saleId,
					'SalSale.doc_code'=>$saleNoteDocCode
					)
				));
				
				if(current($currentState) == 'NOTE_APPROVED' OR current($currentState) == 'SINVOICE_APPROVED'){
					///////////////////****************//////////////////********************////////////////////
					$discountType = current($this->SalSale->find('list', array(
						'fields' => array('SalSale.discount_type'),
						'conditions' => array('SalSale.doc_code' => $saleNoteDocCode)
					)));
					if($discountType == 'PERCENT'){
						$discountString = '- (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100)';
					}else if($discountType == 'BOB'){
						$discountString = '- "SalSale"."discount"';
					}else if($discountType == 'NONE'){
						$discountString = '';
					}
					$total = $this->SalSale->SalDetail->find('first', array(
//						'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price") - (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100),2)) AS total'),//YA NO SIRVE EL DISCOUNT PQ PUEDE VARIAR EL TIPO
						'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price")' .$discountString. ',2)) AS total'),
						'conditions' => array('SalSale.doc_code' => $saleNoteDocCode),
						'group' => array('SalSale.discount')
					));				
					$paid = $this->SalSale->SalPayment->find('first', array(
						'fields' => array('(ROUND(SUM("SalPayment"."amount"),2)) AS paid'),
						'conditions' => array('NOT'=>array('SalPayment.date'=>$payDate),
											'SalSale.doc_code' => $saleNoteDocCode
							)
					));				
	//				$payDebt = $total[0]['total'] - $paid[0]['paid'];
					$payDebt = number_format((float)$total[0]['total'] - $paid[0]['paid'], 2, '.', '');
//					debug($payAmount.'|'.$payDebt);
					if($payAmount == $payDebt){
						$arraySaleNote['paid'] = true;
						if (($ACTION == 'save_invoice' && $saleId != '' && $otherId != null)) {
							$arraySaleInvoice['paid'] = true;
						}
					}else{
						$arraySaleNote['paid'] = false;
					}
					///////////////////****************//////////////////********************////////////////////
				}		
				//pay details for INVOICE
                $arrayPayDetails = array('sal_payment_type_id' => 1,
										'date' => $payDate,
										'description' => $payDescription,
										'amount' => $payAmount, 'ex_amount' => ($payAmount / $exRate)
                );
			}else if($STATE == 'SINVOICE_APPROVED'){
				///////////////////****************//////////////////********************////////////////////
				$discountType = current($this->SalSale->find('list', array(
					'fields' => array('SalSale.discount_type'),
					'conditions' => array('SalSale.doc_code' => $saleNoteDocCode)
				)));
				if($discountType == 'PERCENT'){
					$discountString = '- (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100)';
				}else if($discountType == 'BOB'){
					$discountString = '- "SalSale"."discount"';
				}else if($discountType == 'NONE'){
					$discountString = '';
				}
				$totalDebt = $this->SalSale->SalDetail->find('first', array(
//						'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price") - (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100),2)) AS total'),//YA NO SIRVE EL DISCOUNT PQ PUEDE VARIAR EL TIPO
					'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price")' .$discountString. ',2)) AS total'),
					'conditions' => array('SalSale.doc_code' => $saleNoteDocCode),
					'group' => array('SalSale.discount')
				));				
				$paid = $this->SalSale->SalPayment->find('first', array(
					'fields' => array('(ROUND(SUM("SalPayment"."amount"),2)) AS paid'),
					'conditions' => array('SalSale.doc_code' => $saleNoteDocCode)
				));				
//				$payDebt = number_format((float)$total[0]['total'] - $paid[0]['paid'], 2, '.', '');
//				if($payAmount == $payDebt){
				if($paid[0]['paid'] == $totalDebt[0]['total']){
					$arraySaleNote['paid'] = true;
					if (($ACTION == 'save_invoice' && $saleId != '' && $otherId != null)) {
						$arraySaleInvoice['paid'] = true;
					}
				}else{
					$arraySaleNote['paid'] = false;
				}
				///////////////////****************//////////////////********************////////////////////
			}
			
			if ($OPERATION != 'DEFAULT') {
				if ($OPERATION == 'DISTRIB') {
					$arraySaleDetails = array('inv_warehouse_id' => $warehouseLastOrigId,
						'inv_item_id' => $itemId,
						'quantity' => $quantity - $quantityLastToDistrib
						, 'backorder' => $backorderLastOrig);

					$arraySaleDetailsDistrib = array('inv_warehouse_id' => $warehouseId,
						'inv_item_id' => $itemId,
						'sale_price' => $salePrice, 'ex_sale_price' => $exSalePrice,
						'quantity' => $quantityLastToDistrib
						, 'backorder' => $backorder
							,'approved' => 0);
				} else {
					//item details for NOTE & INVOICE
					$arraySaleDetails = array('inv_warehouse_id' => $warehouseId,
						'inv_item_id' => $itemId,
						'sale_price' => $salePrice, 'ex_sale_price' => $exSalePrice,
						'last_warehouse' => $warehouseLastOrigId,
						'quantity' => $quantity
						, 'backorder' => $backorder);
					if ($OPERATION == 'ADD') {
						$arraySaleDetails['approved'] = 0;
					}
				}
			}


//			if($invoiced == 'true'){
//				//Invoice Details
			$arrayInvoiceDetails = array('sal_code' => $saleCode,
				'invoice_number' => $invoiceNumber,
				'description' => $invoiceDescription);
////										debug($arrayInvoiceDetails);
//			}
			
            /////////////////////////////////////////////////INSERT OR UPDATE
            if ($saleId == '') {//INSERT
//				switch ($ACTION) {
//					case 'save_order':
                //SALES NOTE
                $saleCode = $this->_generate_code('VEN'/*, $ACTION*/);
                if ($ACTION == 'save_invoice') {
                    $saleNoteDocCode = $this->_generate_doc_code('VFA');
                    $arraySaleNote['reserve'] = true;

                    $arraySaleNote['deliver'] = false;
					$arraySaleNote['paid'] = false;
                } else {
                    $saleNoteDocCode = $this->_generate_doc_code('NOT');
                    $arraySaleNote['reserve'] = false;
                    $arraySaleInvoice['reserve'] = false;

                    $arraySaleNote['deliver'] = false;
                    $arraySaleInvoice['deliver'] = false;
					
					$arraySaleNote['paid'] = false;
					$arraySaleInvoice['paid'] = false;
                }

                $arraySaleNote['code'] = $saleCode;
                $arraySaleNote['doc_code'] = $saleNoteDocCode;
                //SALES INVOICE

                $saleInvoiceDocCode = 'NO';
                $arraySaleInvoice['code'] = $saleCode;
                $arraySaleInvoice['doc_code'] = $saleInvoiceDocCode;
                //MOVEMENT type 1(hay stock)
//						$arrayMovement3['document_code'] = $saleCode;
//						$arrayMovement3['code'] = $saleInvoiceDocCode;
//						//MOVEMENT type 2(NO hay stock)
//						$arrayMovement4['document_code'] = $saleCode;
//						$arrayMovement4['code'] = $saleInvoiceDocCode;
//						break;
//				}	

                if ($saleNoteDocCode == 'error') {
                    $error++;
                }

                if ($invoiced == 'true') {
                    //Invoice Details
                    $dataInvoiceDetail = array('new', 'SalInvoice' => $arrayInvoiceDetails);
                } else {
//					$dataInvoiceDetail = array('empty','SalInvoice'=>$arrayInvoiceDetails);
                    $dataInvoiceDetail[0] = 'empty';
                }
                $arraySaleInvoice['lc_state'] = 'DRAFT'; // solo la primera vez q haga draft
            } else {//UPDATE
                //sale note id
                $arraySaleNote['id'] = $saleId;
                if (($ACTION == 'save_order') || ($ACTION == 'save_invoice' && $saleId != '' && $otherId != null)) {
//				if ($ACTION == 'save_order'){
                    //sale invoice id
                    $arraySaleInvoice['id'] = $otherId; //$this->_get_doc_id($saleId, $saleCode, null, null);
                    //movement id type 1(hay stock)
//					$arrayMovement3['id'] = $this->_get_doc_id(null, $saleCode, 1, $warehouseId);
//					if($arrayMovement3['id'] === null){
//						$arrayMovement3['document_code'] = $saleCode;
//						$arrayMovement3['code'] = 'NO';
//					}
                    //movement id type 2(NO hay stock)
//					$arrayMovement4['id'] = $this->_get_doc_id(null, $saleCode, 2, $warehouseId);
//					if(($arrayMovement4['id'] === null) && ($quantity > $stock)){
//						$arrayMovement4['document_code'] = $saleCode;
//						$arrayMovement4['code'] = 'NO';
//					}
//					if($quantity > $stock){//CHEKAR BIEN ESTO, CREO Q YA NO VA!!!
//						$arrayMovement4['document_code'] = $saleCode;
//						$arrayMovement4['code'] = 'NO';
//					}
//					Para eliminar el detalle que ocupaba la HEAD type 2 					
//					if(($arrayMovement4['id'] <> null) && ($quantity <= $stock)){
//						$OPERATION4 = 'DELETE';
//					}
                    if ($STATE == 'NOTE_RESERVED') {// == 'NOTE_APPROVED') {
                        //FOR INVOICE
                        $saleInvoiceDocCode = $this->_generate_doc_code('VFA');
                        $arraySaleInvoice['doc_code'] = $saleInvoiceDocCode;
                    }
                }
//				debug($arraySaleNote['id']);
//				debug($arraySaleInvoice['id']);
				
//				}else
                if (/* $ACTION == 'save_invoice' && */ $OPERATION != 'ADD_PAY' && $OPERATION != 'EDIT_PAY' && $OPERATION != 'DELETE_PAY') {
                    //movement id type 1(hay stock)
//					$arrayMovement3['id'] = $this->_get_doc_id(null, $saleCode, 1, $warehouseId);
//					if($arrayMovement3['id'] === null){//SI NO HAY EL DOCUMENTO (CON STOCK) SE CREA
//						$arrayMovement3['document_code'] = $saleCode;
//						$movementDocCode3 = $this->_generate_movement_code('SAL',null);
//						$arrayMovement3['code'] = $movementDocCode3;//'NO';
//					}
                    //movement id type 2(NO hay stock)
//					$arrayMovement4['id'] = $this->_get_doc_id(null, $saleCode, 2, $warehouseId);
//					if(($arrayMovement4['id'] === null) && ($quantity > $stock)){//SI NO HAY EL DOCUMENTO (SIN STOCK), Y LA CANTIDAD SOBREPASA EL STOCK SE CREA
//						$arrayMovement4['document_code'] = $saleCode;
//						$movementDocCode4 = $this->_generate_movement_code('SAL',null);
//						$arrayMovement4['code'] = $movementDocCode4;//'NO';
//					}
//					if($quantity > $stock){
//						$arrayMovement4['document_code'] = $saleCode;
//						$movementDocCode4 = $this->_generate_movement_code('SAL',null);
//						$arrayMovement4['code'] = $movementDocCode4;//'NO';
//					}
//					Para eliminar el detalle que ocupaba la HEAD type 2
//					if(($arrayMovement4['id'] <> null) && ($quantity <= $stock)){
//						$OPERATION4 = 'DELETE';
//					}
                }
                if ($saleInvoiceDocCode == 'error') {
                    $error++;
                }
//				if($movementDocCode3 == 'error'){$error++;}
//				if($movementDocCode4 == 'error'){$error++;}
                if ($invoiced == 'true') {
                    //Invoice Details
                    $dataInvoiceDetail = array('edit', 'SalInvoice' => $arrayInvoiceDetails);
                } else {
                    $dataInvoiceDetail = array('delete', 'SalInvoice' => $arrayInvoiceDetails);
                }
            }

//			debug($dataInvoiceDetail['SalInvoice']);
//			print_r($arraySaleNote);
//			print_r($arraySaleInvoice);
//										die();
            if ($saleCode == 'error') {
                $error++;
            }
            ////////////////////////////////////////////////////////////////////////  //////////////////////////////////////////////////////////////////////////////////////////////////
//            print_r($arrayItemsDetails);
//            die();
            ////////////////////////////////////////////////////////////////////////  //////////////////////////////////////////////////////////////////////////////////////////////////
            //////////////////////////////////////////////////////////////////////// DELIVER LIST //////////////////////////////////////////////////////////////////////////////////////////////////
            $notDeliveredSum = null;
			$backorderSum = null;
			if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED') {
//                //this lists the items and their approved quantities
				$deliveredList = $this->SalSale->SalDetail->find('all', array(
                    'fields' => array('SalDetail.inv_item_id',
                        'SalDetail.inv_warehouse_id',
                        'SalDetail.approved'),
                    'conditions' => array(
                        'SalSale.id' => $saleId
                    )
                ));
//                print_r($deliveredList);
//				die();
           
            //////////////////////////////////////////////////////////////////////// DELIVER LIST //////////////////////////////////////////////////////////////////////////////////////////////////
				//sorting $arrayItemsDetails by inv_item_id order
//				usort($arrayItemsDetails, function($a, $b) {
//					return $a['inv_item_id'] - $b['inv_item_id'];
//				});
				
				//sorting $arrayItemsDetails by inv_item_id and inv_warehouse_id order
				$sort = array();
				foreach($arrayItemsDetails as $k=>$v) {
					$sort['inv_item_id'][$k] = $v['inv_item_id'];
					$sort['inv_warehouse_id'][$k] = $v['inv_warehouse_id'];
				}
				array_multisort($sort['inv_item_id'], SORT_ASC, $sort['inv_warehouse_id'], SORT_ASC,$arrayItemsDetails);
				
				$notDeliveredSum = 0;
				$backorderSum = 0;
//				 print_r($arrayItemsDetails);
//				die();
				$arrayApprovedItemsDetails = null;
				$arrayApprovedSaleDetailsBatch = null;
				$arrayEmptySaleDetailsBatch = null;
				
				//this gets the list to update in Sales and the list to create on Movements
				foreach ($arrayItemsDetails as $item1) {
					foreach ($deliveredList as $item2) {
						if ($item1['inv_item_id'] == $item2['SalDetail']['inv_item_id'] AND $item1['inv_warehouse_id'] == $item2['SalDetail']['inv_warehouse_id']) {
	//						debug($item1['quantity']-$item1['backorder']."--".$item2['SalDetail']['approved']);
							if($item1['quantity']-$item1['backorder'] > $item2['SalDetail']['approved']){
								//FOR MOVEMENTS
								$arrayApprovedItemsDetails[] = array('approved' => $item1['quantity']-$item1['backorder'] - $item2['SalDetail']['approved'], 'inv_item_id' => $item1['inv_item_id'], 'inv_warehouse_id' => $item1['inv_warehouse_id']);
//								$arraySaleDetailsBatch[] = array('approved' => $item1['quantity']-$item1['backorder'], 'inv_item_id' => $item1['inv_item_id'], 'inv_warehouse_id' => $item1['inv_warehouse_id']);
								//FOR SALE DETAILS
								$arrayApprovedSaleDetailsBatch['inv_item_id'][] = $item1['inv_item_id'];
								$arrayApprovedSaleDetailsBatch['inv_warehouse_id'][] = $item1['inv_warehouse_id'];
								$arrayApprovedSaleDetailsBatch['approved'][] = $item1['quantity']-$item1['backorder'];
								$arrayApprovedSaleDetailsBatch['quantity'][] = $item1['quantity'];
	//							$remaining = $remaining + ($item1['quantity'] - ($item2['SalDetail']['approved'] + ($item1['quantity']-$item1['backorder'])));
	//							$remaining = $remaining + ($item1['quantity'] - $item2['SalDetail']['approved']);
//								$remaining = $remaining + $item1['backorder'];
//								$remaining = $remaining + ($item1['quantity'] - $item2['SalDetail']['approved']);
	//							$arrayApprovedItemsDetails[]['inv_item_id'] = $item1['inv_item_id'];
	//							$arrayApprovedItemsDetails[]['inv_warehouse_id'] = $item1['inv_warehouse_id'];
							}else if($item1['quantity']-$item1['backorder'] == 0){
								$arrayEmptySaleDetailsBatch['inv_item_id'][] = $item1['inv_item_id'];
								$arrayEmptySaleDetailsBatch['inv_warehouse_id'][] = $item1['inv_warehouse_id'];
								$arrayEmptySaleDetailsBatch['approved'][] = $item1['quantity']-$item1['backorder'];
								$arrayEmptySaleDetailsBatch['quantity'][] = $item1['quantity'];
							}
							$notDeliveredSum = $notDeliveredSum + ($item1['quantity'] - $item2['SalDetail']['approved']);
							$backorderSum = $backorderSum + $item1['backorder'];
							break;
						}
					}
				}
				//PONER MENSAJE 'NO TIENE NADA QUE ENTREGAR'
				if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED') {			
					if($arrayApprovedSaleDetailsBatch == null && $arrayEmptySaleDetailsBatch != null){
//						$arraySaleNote['deliver'] = false;
						unset($arraySaleNote['deliver']);					}
				}

				 if (($ACTION == 'save_order') || ($ACTION == 'save_invoice' && $saleId != '' && $otherId != null)) {
					if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED') {
						if($arrayApprovedSaleDetailsBatch == null && $arrayEmptySaleDetailsBatch != null){
//							$arraySaleInvoice['deliver'] = false;
							unset($arraySaleInvoice['deliver']);
						}
					}
				}
				
//				if($arrayApprovedSaleDetailsBatch == null){
//					print_r('no tiene approved');
//				}else{
//					print_r('tiene approved');
//					
//				}
//				if($arrayEmptySaleDetailsBatch == null){
//					print_r('no tiene empty');
//				}else{
//					print_r('tiene empty');					
//				}
//				die();
//				$remaining = 0;
//				foreach ($arrayItemsDetails as $item1) {
//					$arrayApprovedItemsDetails[] = array('approved' => $item1['quantity']-$item1['backorder'], 'inv_item_id' => $item1['inv_item_id'], 'inv_warehouse_id' => $item1['inv_warehouse_id']);
//					$remaining = $remaining + $item1['backorder'];
//				}
				
				
//				debug($arrayApprovedItemsDetails);
//				print_r($arrayApprovedSaleDetailsBatch);
//				print_r('++++++++++++++++++++++++++++');
//				print_r($arrayEmptySaleDetailsBatch);
////				debug($notDelivered);
//				die();				
	//				 if ($STATE != 'NOTE_RESERVED') {//??????????????????????????????
//					if($notDelivered > 0){ 	
//						$arraySaleNote['lc_state'] = 'SINVOICE_PENDANT';
//						$arraySaleInvoice['lc_state'] = 'NOTE_PENDANT';
//					} else if ($notDelivered == 0){
//						$arraySaleNote['lc_state'] = $STATE;
//						$arraySaleInvoice['lc_state'] = 'NOTE_APPROVED';
//					}
					if($notDeliveredSum > 0){
						if($backorderSum > 0){
//							$STATE = 'SINVOICE_PENDANT';
							$arraySaleNote['lc_state'] = 'SINVOICE_PENDANT';
							$arraySaleInvoice['lc_state'] = 'NOTE_PENDANT';
						}else{
//							$STATE = 'SINVOICE_APPROVED';
							$arraySaleNote['lc_state'] = $STATE;
							$arraySaleInvoice['lc_state'] = 'NOTE_APPROVED';
						}
					}else{
//						$STATE = 'SINVOICE_PENDANT';
//						$arraySaleNote['lc_state'] = 'SINVOICE_PENDANT';
//						$arraySaleInvoice['lc_state'] = 'NOTE_PENDANT';
						$arraySaleNote['lc_state'] = $STATE;
						$arraySaleInvoice['lc_state'] = 'NOTE_APPROVED';
					}
										
	//				}
	//			 } else {
	////                $dataMovement = null;
	//				$arraySaleDetailsBatch = null;
	//            }
//				 debug($arraySaleNote);
//				debug($arraySaleInvoice);
	//			print_r($arrayApprovedItemsDetails);
	//			print_r($arraySaleDetailsBatch);
//				die();
				////////////////////////////////////////////////////////////////////////// DELIVER LIST //////////////////////////////////////////////////////////////////////////////////////////////////
	//			if($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED'){
	//				$this->loadModel('InvMovement');
	//				$deliverList = $this->InvMovement->InvMovementDetail->find('all', array(
	//						'fields'=>array('InvMovementDetail.inv_item_id',
	//									'InvMovement.inv_warehouse_id',
	//									'InvMovementDetail.quantity'),	
	//						'conditions'=>array(
	//							'InvMovement.document_code'=>$saleCode
	//						)
	//					));
	//				print_r($deliverList);
	//				die();
	//			}
				////////////////////////////////////////////////////////////////////////// DELIVER LIST //////////////////////////////////////////////////////////////////////////////////////////////////
				if($arrayApprovedItemsDetails != null AND $arrayApprovedSaleDetailsBatch != null){
				//////////////////////////////////////////////////////////////////////// GENEREATE MOVEMENTS //////////////////////////////////////////////////////////////////////////////////////////////////
	//            if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED') {
					$arrayWarehouses = null;
					foreach($arrayApprovedItemsDetails as $val) {
						$arrayWarehouses[] = $val['inv_warehouse_id'];
					}
					$arrayWarehousesList = array_values(array_unique($arrayWarehouses));

					for ($i = 0; $i < count($arrayWarehousesList); $i++) {
						$arrayMovement = array();
						$arrayMovementDetails = array();
						$movementDocCode = '';
						for($j=0;$j<count($arrayApprovedItemsDetails);$j++){
							if($arrayApprovedItemsDetails[$j]['inv_warehouse_id'] == $arrayWarehousesList[$i]){
								$itemId = $arrayApprovedItemsDetails[$j]['inv_item_id'];
								$approved = $arrayApprovedItemsDetails[$j]['approved'];
	//							$backorder = $arrayApprovedItemsDetails[$j]['backorder'];
	//							if($backorder > 0){
	//								if(($quantity - $backorder) > 0){
	//									$arrayMovementDetails[] = array('inv_item_id'=>$itemId, 'quantity'=>($quantity - $backorder));
	//								}	
	//							}else{
	//								$arrayMovementDetails[] = array('inv_item_id'=>$itemId, 'quantity'=>$quantity);
	//							}
								if($approved > 0){
									$arrayMovementDetails[] = array('inv_item_id'=>$itemId, 'quantity'=>$approved);
								}					
	//							$arraySaleDetailsBatch[] = array('inv_item_id'=>$itemId, 'inv_warehouse_id'=>$arrayWarehousesList[$i], 'approved'=>$approved);

	//							$arraySaleDetailsBatch1['inv_item_id'][] = $itemId;
	//							$arraySaleDetailsBatch1['inv_warehouse_id'][] = $arrayWarehousesList[$i];
	//							$arraySaleDetailsBatch1['approved'][] = $approved;

	//							$arraySaleDetailsBatch['inv_item_id']=$itemId;
	//							$arraySaleDetailsBatch['inv_warehouse_id']=$arrayWarehousesList[$i];
	//							$arraySaleDetailsBatch['approved']=$approved;
	//							if((/*$ACTION == 'save_invoice' && */$saleId != '' && $otherId != null)){
	//								$arrayInvoiceDetails['approved']=$approved;
	//							}
							}
						}
						if ($arrayMovementDetails != array()) {
							$movementDocCode = $this->_generate_movement_code('SAL', 'inc'/* , $idsOrdered */);
							$arrayMovement = array('type' => 1, 'date' => $date, 'inv_warehouse_id' => $arrayWarehousesList[$i], 'inv_movement_type_id' => 2, 'description' => $description, 'code' => $movementDocCode, 'document_code' => $saleCode, 'lc_state' => 'APPROVED');
							$dataMovement[] = array('InvMovement' => $arrayMovement, 'InvMovementDetail' => $arrayMovementDetails);
						} else {
							$movementDocCode = $this->_generate_movement_code('SAL', 'inc'/* , $idsOrdered */);
							$arrayMovement = array('type' => 1, 'date' => $date, 'inv_warehouse_id' => $arrayWarehousesList[$i], 'inv_movement_type_id' => 2, 'description' => $description, 'code' => $movementDocCode, 'document_code' => $saleCode, 'lc_state' => 'APPROVED');
							$dataMovement[] = array('InvMovement' => $arrayMovement, 'InvMovementDetail' => $arrayMovementDetails);
						}
						if ($movementDocCode == 'error') {
							$error++;
						}
					}
				} else {
					$dataMovement = null;
					$arrayApprovedSaleDetailsBatch = null;
				}
            } else {
                $dataMovement = null;
				$arrayApprovedSaleDetailsBatch = null;
            }	
//			print_r($notDeliveredSum);
//			print_r('...');
//			print_r($backorderSum);
//			die();
			if($notDeliveredSum == 0 && $backorderSum == 0){
				//ENCONTRAR OTRA FORMA DE QUE CUANDO LOS DOS SEAN 0(CERO) NO ENTRE A BLOCK
			}else if($dataMovement == null && $STATE == 'SINVOICE_APPROVED'){
//				print_r('entra');
                    echo 'BLOCK|onGeneratingParameters';
                    exit();
			}
//			print_r($dataMovement);
//			print_r($arraySaleDetailsBatch0);
//			print_r($remaining);
//			print_r($arraySaleDetailsBatch);
//			print_r($arraySaleNote['lc_state']);
//			print_r($arraySaleInvoice['lc_state']);
//			print_r($arrayApprovedSaleDetailsBatch);
//			print_r($arrayApprovedSaleDetailsBatch);
//			die();
			//////////////////////////////////////////////////////////////////////// GENEREATE MOVEMENTS //////////////////////////////////////////////////////////////////////////////////////////////////
			
			//////////////////////////////////////////////////////////////////////// GENEREATE MOVEMENTS //////////////////////////////////////////////////////////////////////////////////////////////////
//			if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED'){
//				foreach($arrayItemsDetails as $val) {
//					$arrayWarehouses[] = $val['inv_warehouse_id'];
//				}
//				$arrayWarehousesList = array_values(array_unique($arrayWarehouses));
//
//				for($i=0;$i<count($arrayWarehousesList);$i++){
//					$arrayMovement = array();
//					$arrayMovementDetails = array();
//					$movementDocCode = '';
//					for($j=0;$j<count($arrayItemsDetails);$j++){
//						if($arrayItemsDetails[$j]['inv_warehouse_id'] == $arrayWarehousesList[$i]){
//							$itemId = $arrayItemsDetails[$j]['inv_item_id'];
//							$quantity = $arrayItemsDetails[$j]['quantity'];
//							$backorder = $arrayItemsDetails[$j]['backorder'];
//							if($backorder > 0){
//								if(($quantity - $backorder) > 0){
//									$arrayMovementDetails[] = array('inv_item_id'=>$itemId, 'quantity'=>($quantity - $backorder));
//								}	
//							}else{
//								$arrayMovementDetails[] = array('inv_item_id'=>$itemId, 'quantity'=>$quantity);
//							}
//						}
//					}
//					if ($arrayMovementDetails != array()){
//						$movementDocCode = $this->_generate_movement_code('SAL','inc'/*, $idsOrdered*/);
//						$arrayMovement = array('type'=>1, 'date'=>$date, 'inv_warehouse_id'=>$arrayWarehousesList[$i], 'inv_movement_type_id'=>2, 'description'=>$description, 'code'=>$movementDocCode, 'document_code'=>$saleCode, 'lc_state'=>'PENDANT');
//						$dataMovement[] = array('InvMovement'=>$arrayMovement, 'InvMovementDetail'=>$arrayMovementDetails);
//					} else {
//						$movementDocCode = $this->_generate_movement_code('SAL','inc'/*, $idsOrdered*/);
//						$arrayMovement = array('type'=>1, 'date'=>$date, 'inv_warehouse_id'=>$arrayWarehousesList[$i], 'inv_movement_type_id'=>2, 'description'=>$description, 'code'=>$movementDocCode, 'document_code'=>$saleCode, 'lc_state'=>'PENDANT');
//						$dataMovement[] = array('InvMovement'=>$arrayMovement, 'InvMovementDetail'=>$arrayMovementDetails);
//					}
//					if($movementDocCode == 'error'){$error++;}
//				}				
//			}else{
//				$dataMovement = null;
//			}					
//			print_r($dataMovement);
//			die();
            //////////////////////////////////////////////////////////////////////// GENEREATE MOVEMENTS //////////////////////////////////////////////////////////////////////////////////////////////////
			
            /////////////////////////////////////////////////INSERT OR UPDATE	
            //-------------------------FOR DELETING HEAD ON MOVEMENTS RELATED ON save_order--------------------------------
//			if(($ACTION == 'save_order' && $OPERATION3 == 'DELETE') || ($ACTION == 'save_order' && $OPERATION4 == 'DELETE')){	
//			$arrayMovement6 = null;	
//			$rest3 = null;
//			$rest4 = null;																		//VER SI ESTA V RESTRICCION NO INCLUYE OTRAS OPERACIONES MAS ??????????					
//			if(($ACTION == 'save_order' && $OPERATION3 == 'DELETE' && $OPERATION4 == 'DELETE')||($ACTION == 'save_order' && $OPERATION3 == 'EDIT' && $OPERATION4 == 'DELETE')){//TOMANDO EN CUENTA QUE SIEMPRE QUE $OPERATION3 == 'DELETE' TAMBIEN $OPERATION4 == 'DELETE' Y VICEVERSA
//				if (($arrayMovement3['id'] !== null && $arrayMovementDetails3['inv_item_id'] !== null && $OPERATION3 == 'DELETE') ){
//					$rest3 = $this->InvMovement->InvMovementDetail->find('count', array(
//						'conditions'=>array(
//							'NOT'=>array(
//								'AND'=>array(
//									'InvMovementDetail.inv_movement_id'=>$arrayMovement3['id']
//									,'InvMovementDetail.inv_item_id'=>$arrayMovementDetails3['inv_item_id']
//									)
//								)
//							,'InvMovementDetail.inv_movement_id'=>$arrayMovement3['id']
//							),
//						'recursive'=>0
//					));
//				}
//				if (($arrayMovement4['id'] !== null && $arrayMovementDetails4['inv_item_id'] !== null && $OPERATION4 == 'DELETE')){
//					$rest4 = $this->InvMovement->InvMovementDetail->find('count', array(
//						'conditions'=>array(
//							'NOT'=>array(
//								'AND'=>array(
//									'InvMovementDetail.inv_movement_id'=>$arrayMovement4['id']
//									,'InvMovementDetail.inv_item_id'=>$arrayMovementDetails4['inv_item_id']
//									)
//								)
//							,'InvMovementDetail.inv_movement_id'=>$arrayMovement4['id']
//							),
//						'recursive'=>0
//					));
//				}	
//				if(($rest3 === 0) && ($rest4 === 0) && ($arrayMovement3['id'] !== null) && ($arrayMovement4['id'] !== null)){
//					$arrayMovement6 = array(
//						array('InvMovement.id' => array($arrayMovement3['id'],$arrayMovement4['id']))
//					);
//				}elseif(($rest3 === 0) && ($arrayMovement3['id'] !== null)){
//					$arrayMovement6 = array(
//						array('InvMovement.id' => $arrayMovement3['id'])
//					);
//				}elseif(($rest4 === 0) && ($arrayMovement4['id'] !== null)){
//					$arrayMovement6 = array(
//						 array('InvMovement.id' => $arrayMovement4['id'])
//					);
//				}
////				else{
////					$arrayMovement6 = null;
////				}
//			}
            //---------------------------FOR DELETING HEAD ON MOVEMENTS RELATED ON save_order------------------------------
//			-------------------------FOR UPDATING HEAD ON DELETED MOVEMENTS ON save_invoice--------------------------------
////			if(($ACTION == 'save_invoice' && $OPERATION3 == 'DELETE') || ($ACTION == 'save_invoice' && $OPERATION4 == 'DELETE')){	
//			$draftId3 = null;
//			$draftId4 = null;																		//VER SI ESTA V RESTRICCION NO INCLUYE OTRAS OPERACIONES MAS ??????????			
//			if(($ACTION == 'save_invoice' && $OPERATION3 == 'DELETE' && $OPERATION4 == 'DELETE')||($ACTION == 'save_invoice' && $OPERATION3 == 'EDIT' && $OPERATION4 == 'DELETE')){//TOMANDO EN CUENTA QUE SIEMPRE QUE $OPERATION3 == 'DELETE' TAMBIEN $OPERATION4 == 'DELETE' Y VICEVERSA
//				if (($arrayMovement3['id'] !== null && $arrayMovementDetails3['inv_item_id'] !== null  && $OPERATION3 == 'DELETE')){
//					$rest3 = $this->InvMovement->InvMovementDetail->find('count', array(
//						'conditions'=>array(
//							'NOT'=>array(
//								'AND'=>array(
//									'InvMovementDetail.inv_movement_id'=>$arrayMovement3['id']
//									,'InvMovementDetail.inv_item_id'=>$arrayMovementDetails3['inv_item_id']
//									)
//								)
//							,'InvMovementDetail.inv_movement_id'=>$arrayMovement3['id']
//							),
//						'recursive'=>0
//					));
//				}
//				if (($arrayMovement4['id'] !== null && $arrayMovementDetails4['inv_item_id'] !== null && $OPERATION4 == 'DELETE')){
//					$rest4 = $this->InvMovement->InvMovementDetail->find('count', array(
//						'conditions'=>array(
//							'NOT'=>array(
//								'AND'=>array(
//									'InvMovementDetail.inv_movement_id'=>$arrayMovement4['id']
//									,'InvMovementDetail.inv_item_id'=>$arrayMovementDetails4['inv_item_id']
//									)
//								)
//							,'InvMovementDetail.inv_movement_id'=>$arrayMovement4['id']
//							),
//						'recursive'=>0
//					));
//				}	
//				if(($rest3 === 0) && ($rest4 === 0) && ($arrayMovement3['id'] !== null) && ($arrayMovement4['id'] !== null)){
//					$draftId3 = $arrayMovement3['id'];
//					$draftId4 = $arrayMovement4['id'];
////					echo "<br>1<br>";
////					debug($draftId3);
////					debug($draftId4);
//				}elseif(($rest3 === 0) && ($arrayMovement3['id'] !== null)){
//					$draftId3 = $arrayMovement3['id'];
////					$draftId4 = null;
////					echo "<br>2<br>";
////					debug($draftId3);
//				}elseif(($rest4 === 0) && ($arrayMovement4['id'] !== null)){
//					$draftId4 = $arrayMovement4['id'];
////					$draftId3 = null;
////					echo "<br>3<br>";
////					debug($draftId4);
//				}
////				else{
////					$draftId3 = null;
////					$draftId4 = null;
////				}
//			}
//			---------------------------FOR UPDATING HEAD ON DELETED MOVEMENTS ON save_invoice------------------------------
            //*********************************************************MAKE AN IF WHEN $STATE == DEFAULT
//			$this->loadModel('InvMovement');
//			$arrayMovement5 = $this->InvMovement->find('all', array(
//				'fields'=>array(
//					'InvMovement.id'
////					,'InvMovement.date'
////					,'InvMovement.description'
////					,'InvMovement.lc_state'
////					,'InvMovement.inv_warehouse_id'
//					),
//				'conditions'=>array(
//						'InvMovement.document_code'=>$saleCode
//					)
//				,'order' => array('InvMovement.id' => 'ASC')
//				,'recursive'=>0
//			));
//			if(($arrayMovement5 <> null)&&($STATE == 'NOTE_CANCELLED')){
//				for($i=0;$i<count($arrayMovement5);$i++){
//					$arrayMovement5[$i]['InvMovement']['lc_state'] = 'DRAFT';
//				}
//			}elseif(($arrayMovement5 <> null)&&($STATE == 'NOTE_APPROVED')) {
//				for($i=0;$i<count($arrayMovement5);$i++){
//					$movementDocCode5 = $this->_generate_movement_code('SAL','inc');
//					$arrayMovement5[$i]['InvMovement']['lc_state']='PENDANT';
//					$arrayMovement5[$i]['InvMovement']['code'] = $movementDocCode5;
//					$arrayMovement5[$i]['InvMovement']['date'] = $date;
//					$arrayMovement5[$i]['InvMovement']['description'] = $description;
//				}
//			}elseif($arrayMovement5 <> null){
//				for($i=0;$i<count($arrayMovement5);$i++){
//					$arrayMovement5[$i]['InvMovement']['date'] = $date;
//					$arrayMovement5[$i]['InvMovement']['description'] = $description;
//					/////////////////////////////////////////////////////////////////
//					if(($ACTION == 'save_invoice' && $OPERATION3 == 'DELETE') || ($ACTION == 'save_invoice' && $OPERATION4 == 'DELETE')){		
//						if($arrayMovement5[$i]['InvMovement']['id'] === $draftId3){
//							$arrayMovement5[$i]['InvMovement']['lc_state']='DRAFT';
//						}
//						if($arrayMovement5[$i]['InvMovement']['id'] === $draftId4){
//							$arrayMovement5[$i]['InvMovement']['lc_state']='DRAFT';
//						}
//					}	
//					/////////////////////////////////////////////////////////////////
//				}
//			}
            //*********************************************************
            //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
//			if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_PENDANT'){
//				if($draftId3 == null){
//					$arrayMovement3['lc_state']='PENDANT';
//				}
//				if($draftId4 == null){
//					$arrayMovement4['lc_state']='PENDANT';
//				}
//			}
            //++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
            //for NOTE		when save_order / INVOICE when save_invoice
            $dataSale[0] = array('SalSale' => $arraySaleNote);
            if (($ACTION == 'save_order') || ($ACTION == 'save_invoice' && $saleId != '' && $otherId != null)) {
//			if ($ACTION == 'save_order'){
//				$this->loadModel('InvMovement');
                //for invoice
                $dataSale[1] = array('SalSale' => $arraySaleInvoice);
                //for movement
//				$dataMovement3 = array('InvMovement'=>$arrayMovement3);
//				$dataMovementDetail3 = array('InvMovementDetail'=> $arrayMovementDetails3);
//				$dataMovement4 = array('InvMovement'=>$arrayMovement4);
//				$dataMovementDetail4 = array('InvMovementDetail'=> $arrayMovementDetails4);
//				if($arrayMovement5 <> null){
//					$dataMovement5 = $arrayMovement5;
//				}	
//				if((($ACTION == 'save_order' && $OPERATION3 == 'DELETE' && $arrayMovement6 <> null) || ($ACTION == 'save_order' && $OPERATION4 == 'DELETE' && $arrayMovement6 <> null)) ){
//					$dataMovement6 = $arrayMovement6;
//				}	
                $dataPayDetail = null;
            } elseif ($ACTION == 'save_invoice') {
//				$this->loadModel('InvMovement');
//				//for movement
//				$dataMovement3 = array('InvMovement'=>$arrayMovement3);
//				$dataMovementDetail3 = array('InvMovementDetail'=> $arrayMovementDetails3);
//				$dataMovement4 = array('InvMovement'=>$arrayMovement4);
//				$dataMovementDetail4 = array('InvMovementDetail'=> $arrayMovementDetails4);
//				if($arrayMovement5 <> null){
//					$dataMovement5 = $arrayMovement5;
//				}	
//				if((($ACTION == 'save_order' && $OPERATION3 == 'DELETE' && $arrayMovement6 <> null) || ($ACTION == 'save_order' && $OPERATION4 == 'DELETE' && $arrayMovement6 <> null)) ){
//					$dataMovement6 = $arrayMovement6;
//				}	
                $dataPayDetail = null;
            }

            if ((/* $ACTION == 'save_invoice' && */ $OPERATION == 'ADD_PAY') || (/* $ACTION == 'save_invoice' && */ $OPERATION == 'EDIT_PAY') || (/* $ACTION == 'save_invoice' && */ $OPERATION == 'DELETE_PAY')) {
                $dataPayDetail = array('SalPayment' => $arrayPayDetails);
//				if($arrayMovement5 <> null){
//					$dataMovement5 = $arrayMovement5;
//				}	
            }
//			print_r($arraySaleDetails);
//			die();
			if($OPERATION != 'DEFAULT'){
				$dataSaleDetail[0] = array('SalDetail' => $arraySaleDetails);
				if (($ACTION == 'save_order') || ($ACTION == 'save_invoice' && $saleId != '' && $otherId != null)) {
	//			if ($ACTION == 'save_order'){
					$dataSaleDetail[1] = array('SalDetail' => $arraySaleDetails);
				}
				if ($OPERATION == 'DISTRIB') {
					$dataSaleDetail[2] = array('SalDetail' => $arraySaleDetailsDistrib);
					$dataSaleDetail[3] = array('SalDetail' => $arraySaleDetailsDistrib);
				}
				$dataSaleDetail1 = null;
			}else if($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED' && $arrayApprovedItemsDetails != null AND $arrayApprovedSaleDetailsBatch != null){	
				$dataSaleDetail[0] = array('SalDetail' => $arrayApprovedSaleDetailsBatch);
				$dataSaleDetail[1] = array('SalDetail' => $arrayApprovedSaleDetailsBatch);
				if($arrayEmptySaleDetailsBatch != null){
					$dataSaleDetail1[0] = array('SalDetail' => $arrayEmptySaleDetailsBatch);
					$dataSaleDetail1[1] = array('SalDetail' => $arrayEmptySaleDetailsBatch);
				}else{
					$dataSaleDetail1 = null;
				}
			}else{
				$dataSaleDetail = null;
				$dataSaleDetail1 = null;
			}	
//			if($invoiced == 'true'){
//				//Invoice Details
//				$dataInvoiceDetail =  array('SalInvoice'=>$arrayInvoiceDetails);
//			}else{
//				$dataInvoiceDetail = 'delete';
//			}
            if ($STATE == 'NOTE_RESERVED') {
                $STATE = 'NOTE_PENDANT';
            }
			
						
			if($STATE == 'SINVOICE_CANCELLED'){
				$dataSale[0]['SalSale']['code_for_prices']=$saleCode;
				if($ACTION == 'save_invoice' && $saleId != '' && $otherId != null){
					$dataSale[1]['SalSale']['lc_state']='NOTE_CANCELLED';
				}
				$this->loadModel('InvMovement');
				$movementIds = $this->InvMovement->find('list', array(
					'fields' => array('InvMovement.id'),
					'conditions' => array(
						'InvMovement.document_code' => $saleCode
					)
				));
				foreach ($movementIds as $movementId) {
					$dataMovement[] = array('id' => $movementId, 'lc_state' => 'CANCELLED');
				}		
				
			}
//			
            ////////////////////////////////////////////////END - SET DATA//////////////////////////////////////////////////////
//			print_r($dataSaleDetail);
//			print_r($dataSaleDetail1);	
//			debug($STATE);	
//			debug($notDeliveredSum);
//			debug($backorderSum);
//			die();
            ////////////////////////////////////////////////START - HISTORY PRICES//////////////////////////////////////////////////////
            $arraySalePrices = array();
            if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED' && $backorderSum == 0) {
                for ($i = 0; $i < count($arrayItemsDetails); $i++) {
                    $arrayItemsDetailsIds[$i] = $arrayItemsDetails[$i]['inv_item_id'];
                }

                $this->loadModel('InvPrice');
                $prices = $this->InvPrice->find('all', array(
                    'fields' => array(
                        'InvPrice.inv_item_id'
                        , 'InvPrice.inv_price_type_id'
                        , 'InvPrice.price'
                        , 'InvPrice.date'
                    ),
                    'order' => 'InvPrice.date ASC',
                    'conditions' => array(
                        'InvPrice.date <=' => $date
                        , 'InvPrice.inv_item_id' => $arrayItemsDetailsIds
						,'InvPrice.inv_price_type_id' => 9
                    )
                ));
				
				$date2Compare = date("Y-m-d H:i:s", strtotime(str_replace('/', '-', $date)));
                $arrayLastSalePrices = array();
                if ($prices != array()) {
                    $lastSalePrices = array();
                    for ($i = 0; $i < count($arrayItemsDetailsIds); $i++) {
                        for ($j = 0; $j < count($prices); $j++) {
                            if ($arrayItemsDetailsIds[$i] == $prices[$j]['InvPrice']['inv_item_id']/* && $prices[$j]['InvPrice']['inv_price_type_id'] == 9*//* VENTA */) {
								$lastSalePrices[$i] = $prices[$j];	
                            }
                        }
                    }
//					foreach($lastSalePrices as $val){
//						if($date2Compare != $val['InvPrice']['date']){
//							$arrayLastSalePrices[] = $val;
//						}
//					}
                    $arrayLastSalePrices = array_values($lastSalePrices);
                }		
//				print_r($date2Compare);
//				print_r($arrayItemsDetails);
//				print_r($arrayLastSalePrices);
//				die();
                $arraySalePrices = array();
                for ($i = 0; $i < count($arrayItemsDetails); $i++) {
                    $samePriceOrDate = 'no';
                    $firstOne = 'yes';
                    if ($arrayLastSalePrices != array()) {
                        for ($j = 0; $j < count($arrayLastSalePrices); $j++) {
//							if($arrayItemsDetails[$i]['inv_item_id'] == $arrayLastSalePrices[$j]['InvPrice']['inv_item_id'] && $arrayLastSalePrices[$j]['InvPrice']['price'] == $arrayItemsDetails[$i]['sale_price'] ){
//								$contSale = 1;
//							}else if($arrayItemsDetails[$i]['inv_item_id'] == $arrayLastSalePrices[$j]['InvPrice']['inv_item_id']) {
//								$contNew = 1;
//							}
                            if ($arrayItemsDetails[$i]['inv_item_id'] == $arrayLastSalePrices[$j]['InvPrice']['inv_item_id']) {
                                $firstOne = 'no';
                                if ($arrayLastSalePrices[$j]['InvPrice']['price'] == $arrayItemsDetails[$i]['sale_price'] OR $date2Compare == $arrayLastSalePrices[$j]['InvPrice']['date']) {
                                    $samePriceOrDate = 'yes';
                                }
                            }
                        }
					}
					
                    if ($samePriceOrDate == 'no') {
//						$this->loadModel('InvWarehouse');
						$location = $this->SalSale->find('list', array(
						'fields'=>array(
							'SalSale.location'),
						'conditions'=>array(
							'SalSale.id'=>$saleId
							)	
						));
						
                        $arraySalePrices[$i]['inv_item_id'] = $arrayItemsDetails[$i]['inv_item_id'];
                        $arraySalePrices[$i]['inv_price_type_id'] = 9; //or better relate by name VENTA
						$arraySalePrices[$i]['ex_rate'] = $exRate;						
                        $arraySalePrices[$i]['price'] = $arrayItemsDetails[$i]['sale_price'];
                        $arraySalePrices[$i]['ex_price'] = $arrayItemsDetails[$i]['ex_sale_price'];
						$arraySalePrices[$i]['location'] = current($location);
                        $arraySalePrices[$i]['description'] = "Precio de Venta de la Nota " . $noteCode . " del " . $date;
						$arraySalePrices[$i]['code'] = $saleCode;
                        if ($firstOne == 'yes') {
                            $year = $this->Session->read('Period.name');
                            $arraySalePrices[$i]['date'] = $year . '-01-01 00:00:00'; //Cambiar por algo que me de el (año sacado de $date) + 01-01 00:00:00
                        } else {
                            $arraySalePrices[$i]['date'] = $date;
                        }
//                        $arraySalePrices[$i]['region'] = 1;
                    }
                }
            }
			
            ////////////////////////////////////////////////END - HISTORY PRICES//////////////////////////////////////////////////////
//			$validation['error'] = 0;
//			$strItemsStock = '';
//			print_r($STATE);
//			print_r('<br>');
//			print_r($dataSaleDetail);
//			print_r($dataPayDetail);
//			print_r($arraySalePrices);
//			print_r($dataInvoiceDetail);
//			die();
//			print_r($dataSale);
//			print_r('//////////////////////');
//			print_r($dataSaleDetail);
//			print_r('//////////////////////');
//			print_r($OPERATION);
//			print_r('//////////////////////');
//			print_r($ACTION);
//			print_r('//////////////////////');
//			print_r($STATE);
//			print_r('//////////////////////');
//			print_r($dataMovement);
//			print_r('//////////////////////');
//			print_r($dataSaleDetail1);
//			die();
            ////////////////////////////////////////////START- CORE SAVE////////////////////////////////////////////////////////		
            if ($error === 0) {
                /////////////////////START - SAVE/////////////////////////////							
                $res = $this->SalSale->saveSale($dataSale, $dataSaleDetail, $OPERATION, $ACTION, $STATE, $dataPayDetail, $arraySalePrices, $dataInvoiceDetail, $dataMovement/*, $dataSaleDetailsBatch*/, $saleNoteDocCode, $saleCode, $notDeliveredSum, $backorderSum, $dataSaleDetail1, $arrayForValidate);
//				if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED'){
//					$res = $this->SalSale->saveGeneratedMovements(/*$idsToDelete,*/ $data);			
//				}	
//							if ($ACTION == 'save_invoice' && $STATE == 'SINVOICE_APPROVED'){
//									$this->loadModel('InvPrice');
//									$this->InvPrice->saveAll($arraySalePrices);
//							}
//							if ($ACTION == 'save_order'){
//								$res2 = $this->SalSale->saveMovement($dataMovement2, $dataMovementDetail, $OPERATION, $ACTION, $movementDocCode, null);
//								if(($stock != 0)||(($OPERATION3 == 'DELETE')&&($arrayMovement3['id']!==null))){
//									//used to insert/update type 1 detail movements 
//									//used to delete movement details type 1
////									echo "ini3";
//									$res3 = $this->InvMovement->saveMovement($dataMovement3, $dataMovementDetail3, $OPERATION3, 'save_in', null, $movementDocCode3);
////									echo "fin3";
//								}
//								if(($quantity > $stock)||(($OPERATION4 == 'DELETE')&&($arrayMovement4['id']!==null))){	//($quantity > $stock) doesn't work when stock changes
//									//used to insert/update type 2 detail movements									
//									//used to delete movement details type 2
////									echo "ini4";
//									$res4 = $this->InvMovement->saveMovement($dataMovement4, $dataMovementDetail4, $OPERATION4, 'save_in', null, $movementDocCode4);
////									echo "fin4";
//								}	
//								if($arrayMovement5 <> null){
//									//used to update movements head
////									echo "ini5";
//									$res5 = $this->InvMovement->saveMovement($dataMovement5, null, 'UPDATEHEAD', null, null, null);
////									echo "fin5";
//								}
//								if((($ACTION == 'save_order' && $OPERATION3 == 'DELETE' && $arrayMovement6 <> null) || ($ACTION == 'save_order' && $OPERATION4 == 'DELETE' && $arrayMovement6 <> null)) ){
////									echo "ini6";
//									$res6 = $this->InvMovement->saveMovement($dataMovement6, null, 'DELETEHEAD', null, null, null);
////									echo "fin6";
//								}
//								
//							}elseif ($ACTION == 'save_invoice' && $OPERATION != 'ADD_PAY' && $OPERATION != 'EDIT_PAY' && $OPERATION != 'DELETE_PAY') {
//								if(($stock != 0)||(($OPERATION3 == 'DELETE')&&($arrayMovement3['id']!==null))){
//									//used to insert/update type 1 detail movements 
//									//used to delete movement details type 1
////									echo "ini3";
//									$res3 = $this->InvMovement->saveMovement($dataMovement3, $dataMovementDetail3, $OPERATION3, 'save_in', null, $movementDocCode3);
////									echo "fin3";
//								}							//VER SI v ESTA CONDICION DEJA ENTRAR LO NECESARIO										//VER SI v ESTA OTRA CONDICION DEJA ENTRAR LO NECESARIO 
//								if(($quantity > $stock)||(($OPERATION3 == 'EDIT')&&($OPERATION4 == 'DELETE')&&($arrayMovement4['id']!==null))||(($OPERATION3 == 'DELETE')&&($OPERATION4 == 'DELETE')&&($arrayMovement4['id']!==null))){	//($quantity > $stock) doesn't work when stock changes
////								if(($quantity > $stock)||(($OPERATION4 == 'DELETE')&&($arrayMovement4['id']!==null))){
//									//used to insert/update type 2 detail movements									
//									//used to delete movement details type 2
////									echo "ini4";
//									$res4 = $this->InvMovement->saveMovement($dataMovement4, $dataMovementDetail4, $OPERATION4, 'save_in', null, $movementDocCode4);
////									echo "fin4";
//								}	
//								if($arrayMovement5 <> null){
//									//used to update movements head
//									//LO QUE ENTRE AQUI SOBREESCRIBE LA CABECERA DE $dataMovement3 y $dataMovement4
////									echo "ini5";
//									$res5 = $this->InvMovement->saveMovement($dataMovement5, null, 'UPDATEHEAD', null, null, null);
////									echo "fin5";
//								}
////								if((($OPERATION3 == 'DELETE' || $OPERATION4 == 'DELETE') && $arrayMovement6 <> null)){
////									$res6 = $this->InvMovement->saveMovement($dataMovement6, null, 'DELETEHEAD', null);
////								}
//							}elseif(($ACTION == 'save_invoice' && $OPERATION == 'ADD_PAY') || ($ACTION == 'save_invoice' && $OPERATION == 'EDIT_PAY') || ($ACTION == 'save_invoice' && $OPERATION == 'DELETE_PAY')){
//								if($arrayMovement5 <> null){
//									//used to update movements head
//									$res5 = $this->InvMovement->saveMovement($dataMovement5, null, 'UPDATEHEAD', null, null, null);
//								}
//							}
//				debug($res[1]);
//				debug($saleNoteDocCode);
//				debug($saleCode);
//				die();
                switch ($res[0]) {
                    case 'SUCCESS':
						echo $res[1];
//                        echo $res[1] . '|' . $saleNoteDocCode . '|' . $saleCode;
//						echo 'NOTE_PENDANT|4477|NOT-2014-10|VEN-2014-10';
                        break;
					case 'VALIDATION':
                        echo 'VALIDATION|' . $res[1];
                        break;
                    case 'ERROR':
                        echo 'ERROR|onSaving';
                        break;
                }
                /////////////////////END - SAVE////////////////////////////////	
            } else {
                echo 'ERROR|onGeneratingParameters';
            }
            ////////////////////////////////////////////END-CORE SAVE////////////////////////////////////////////////////////
        }
    }

    public function ajax_logic_delete() {
        if ($this->RequestHandler->isAjax()) {
//			print_r($this->request->data);
//			die();
            $purchaseId = $this->request->data['purchaseId'];
            $type = $this->request->data['type'];
            $genCode = $this->request->data['genCode'];
			$delivered = $this->request->data['delivered'];
            $dataMovement = array();
			
//			SI YA ESTA ETREGADO ALGO YA NO SE PUEDE HACER LOGIC_DELETE MEDIANTE SAVE_ORDER SOLO MEDIANTE SAVE_INVOICE
			if($delivered == 1){
				$this->loadModel('InvMovement');
				$movementIds = $this->InvMovement->find('list', array(
					'fields' => array('InvMovement.id'),
					'conditions' => array(
						'InvMovement.document_code' => $genCode
					)
				));
				foreach ($movementIds as $movementId) {
					$dataMovement[] = array('id' => $movementId, 'lc_state' => 'CANCELLED');
				}					
			}
			
            $reservedState = $this->SalSale->find('list', array(
                'fields' => array('SalSale.reserve'),
                'conditions' => array(
                    'SalSale.id' => $purchaseId
                )
            ));
            if ($type === 'NOTE_LOGIC_DELETED') {
//				debug($reservedState);
                if (current($reservedState) == true) {
                    echo 'BLOCK';
                    exit();
                }
//				$invId = $this->_get_doc_id($purchaseId, $genCode, null, null);
                $arraySale1['id'] = $purchaseId;
				if($delivered == 1){
					$arraySale1['lc_state'] = 'NOTE_CANCELLED';
				}else{
					$arraySale1['lc_state'] = $type;
				}	
                $arraySale2['id'] = $this->_get_doc_id($purchaseId, $genCode, null, null);
                $lcState = $this->SalSale->find('list', array(
                    'fields' => array('SalSale.lc_state'),
                    'conditions' => array(
                        'SalSale.id' => $arraySale2['id']
                    )
                ));
                if (current($lcState) == 'DRAFT') {
                    $arraySale2['lc_state'] = 'DRAFT_DELETED';
                } elseif (current($lcState) == 'SINVOICE_PENDANT') {
					if($delivered == 1){
						$arraySale2['lc_state'] = 'SINVOICE_CANCELLED';
					}else{
						$arraySale2['lc_state'] = 'SINVOICE_LOGIC_DELETED';
					}
                }
                $dataSale[0] = $arraySale1;
                $dataSale[1] = $arraySale2;
                $dataSale[2] = array();
//				$dataSale[1] = array();
//				print_r($dataSale);
//				die();
                $res = $this->SalSale->updateMovement($dataSale, $dataMovement, $pricesCode='');
            } elseif ($type === 'SINVOICE_LOGIC_DELETED') {
                if (current($reservedState) == false) {
                    echo 'BLOCK';
                    exit();
                }
                $arraySale1['id'] = $this->_get_doc_id($purchaseId, $genCode, null, null);

//				debug($arraySale1['id']);
//				die();
                if ($arraySale1['id'] == null) {
                    $arraySale2['id'] = $purchaseId;
					if($delivered == 1){
						$arraySale2['lc_state'] = 'SINVOICE_CANCELLED';
					}else{
						$arraySale2['lc_state'] = $type;
					}
                    $dataSale[0] = $arraySale2;
                    $dataSale[1] = array();
                    $dataSale[2] = array();
                } else {
                    $arraySale2['id'] = $purchaseId;
					if($delivered == 1){
						$arraySale2['lc_state'] = 'SINVOICE_CANCELLED';
					}else{
						 $arraySale2['lc_state'] = $type;
					}
//					$arraySale1['id']=$arraySale1['id'];
					if($delivered == 1){
						$arraySale1['lc_state'] = 'NOTE_CANCELLED';
					}else{
						$arraySale1['lc_state'] = 'NOTE_LOGIC_DELETED';
					}
                    $dataSale[0] = $arraySale2;
                    $dataSale[1] = $arraySale1;
                    $dataSale[2] = array();
                }
				
				
				
//				print_r($dataSale);
//				die();
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
//					$arraySale['id']=$purchaseId;
//					$arraySale['lc_state']=$type;
//					$dataSale[0] = $arraySale;
//					$dataSale[1] = array();
//					$arrayMovement['id'] = $this->_get_doc_id(null, $genCode, 1);				
//					$arrayMovement['lc_state']='LOGIC_DELETED';
//					$dataMovement = $arrayMovement;
//					$res = $this->PurPurchase->updateMovement($dataSale, $dataMovement);
//				}elseif($state['InvMovement']['lc_state'] === 'LOGIC_DELETED' || $state['InvMovement']['lc_state'] === 'CANCELLED'){	
//					$arraySale['id']=$purchaseId;
//					$arraySale['lc_state']=$type;
//					$dataSale[0] = $arraySale;
//					$dataSale[1] = array();

                $res = $this->SalSale->updateMovement($dataSale, $dataMovement, $pricesCode='');
//				}else{
//					$res[0] = 'EXCEPTION';
//				}	
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

    public function ajax_change_reserved() {
        if ($this->RequestHandler->isAjax()) {
            $saleId1 = $this->request->data['saleId'];
            $reserve = $this->request->data['reserve'];
            $genCode = $this->request->data['genCode'];
            $action = $this->request->data['action'];
            $reservedState = $this->SalSale->find('list', array(
                'fields' => array('SalSale.reserve'),
                'conditions' => array(
                    'SalSale.id' => $saleId1
                )
            ));
//				debug($action);
//				debug(current($reservedState));
            if ($action == 'save_invoice') {
                if (current($reservedState) == true) {
                    echo 'BLOCK';
                    exit();
                }
            }
//			
            $dataMovement = array();
//			if($type === 'NOTE_LOGIC_DELETED'){
            $saleId2 = $this->_get_doc_id($saleId1, $genCode, null, null);
//				debug($saleId2);
//				die();
            $arraySale1['id'] = $saleId1;
            $arraySale1['reserve'] = $reserve;

            $arraySale2['id'] = $saleId2;
            $arraySale2['reserve'] = $reserve;

            $dataSale[0] = $arraySale1;
            $dataSale[1] = $arraySale2;
            $dataSale[2] = array();
            $res = $this->SalSale->updateMovement($dataSale, $dataMovement, $pricesCode='');
//			}elseif($type === 'SINVOICE_LOGIC_DELETED'){
////				
//					$arraySale['id']=$purchaseId;
//					$arraySale['lc_state']=$type;
//					$dataSale[0] = $arraySale;
//					$dataSale[1] = array();
//					$res = $this->SalSale->updateMovement($dataSale, $dataMovement);
//
//			}	

            switch ($res[0]) {
                case 'SUCCESS':
                    echo 'success' . '|' . $saleId1;
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

//	public function ajax_logic_delete(){
//		if($this->RequestHandler->isAjax()){
//			$purchaseId = $this->request->data['purchaseId'];
//			$type = $this->request->data['type'];	
//			$genCode = $this->request->data['genCode'];
//				if($this->SalSale->updateAll(array('SalSale.lc_state'=>"'$type'", 'SalSale.lc_transaction'=>"'MODIFY'"), array('SalSale.id'=>$purchaseId)) 
//						){
//					echo 'success';
//				}
//				if($type === 'SINVOICE_LOGIC_DELETED'){
//					$this->loadModel('InvMovement');
//					$arrayMovement5 = $this->InvMovement->find('all', array(
//						'fields'=>array(
//							'InvMovement.id',
////							,'InvMovement.date'
////							,'InvMovement.description'
//							'InvMovement.inv_warehouse_id'
//							),
//						'conditions'=>array(
//								'InvMovement.document_code'=>$genCode
//							)
//						,'order' => array('InvMovement.id' => 'ASC')
//						,'recursive'=>0
//					));
//					if($arrayMovement5 <> null){
//						for($i=0;$i<count($arrayMovement5);$i++){
//							$arrayMovement5[$i]['InvMovement']['lc_state'] = 'DRAFT';
////							$arrayMovement5[$i]['InvMovement']['code'] = 'NO'; //not sure to put this
//						}
//					}
//					if($arrayMovement5 <> null){
//						$dataMovement5 = $arrayMovement5;
//					}
//					if($arrayMovement5 <> null){
//						$res5 = $this->InvMovement->saveMovement($dataMovement5, null, 'UPDATEHEAD', null, null, null);
//					}
//				}
//		}
//	}

    public function ajax_initiate_modal_add_pay() {
        if ($this->RequestHandler->isAjax()) {
//			$paysAlreadySaved = $this->request->data['paysAlreadySaved'];
            $docCode = $this->request->data['docCode'];
//			debug($paysAlreadySaved);
//			debug($docCode);
//			$payDebt = $this->request->data['payDebt'];
//			debug($payDebt);
            $datePay = $this->request->data['date']; //temporal date that shows in the payment modal
            //$datePay=date('d/m/Y');
//			$discount = $this->request->data['discount'];
//			debug($discount);
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
//			if ($discount != 0){
//				$payDebtVar = $payDebt-($payDebt*($discount/100));
//				$payDebt = number_format($payDebtVar, 2, '.', '');
//			}
//			debug($payDebt);
//			$pays = $this->SalSale->SalPayment->SalPaymentType->find('list', array(
//					'fields'=>array('SalPaymentType.name'),
//					'conditions'=>array(
////						'NOT'=>array('InvPriceType.id'=>$paysAlreadySaved) /*aca se hace la discriminacion de items seleccionados*/
//				),
//				
//				'recursive'=>-1
//				//'fields'=>array('InvItem.id', 'CONCAT(InvItem.code, '-', InvItem.name)')
//			));
//debug($payDebt);		
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
            //,"(SELECT ROUND(SUM(quantity * sale_price) - (SUM(quantity * sale_price) * \"SalSale\".\"discount\"/100),2) FROM sal_details WHERE sal_sale_id = \"SalSale\".\"id\") AS cost_sum"),
//			'SUM("SalDetail"."quantity" * "SalDetail"."'.$currencyField.'sale_price") AS money',
            
			$discountType = current($this->SalSale->find('list', array(
				'fields' => array('SalSale.discount_type'),
				'conditions' => array('SalSale.doc_code' => $docCode)
			)));
			if($discountType == 'PERCENT'){
				$discountString = '- (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100)';
			}else if($discountType == 'BOB'){
				$discountString = '- "SalSale"."discount"';
			}else if($discountType == 'NONE'){
				$discountString = '';
			}
			$total = $this->SalSale->SalDetail->find('first', array(
//						'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price") - (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100),2)) AS total'),//YA NO SIRVE EL DISCOUNT PQ PUEDE VARIAR EL TIPO
				'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price")' .$discountString. ',2)) AS total'),
				'conditions' => array('SalSale.doc_code' => $docCode),
				'group' => array('SalSale.discount')
			));				

            $paid = $this->SalSale->SalPayment->find('first', array(
                'fields' => array('(ROUND(SUM("SalPayment"."amount"),2)) AS paid'),
                'conditions' => array('SalSale.doc_code' => $docCode),
//			'group'=>array('SalSale.discount')
            ));
//		
//		$paid = $this->SalSale->SalPayment->find('all', array(
//			'fields'=>array(
//				"SalDetail.inv_item_id", 
//				"SalDetail.inv_warehouse_id",
////				"(SUM(CASE WHEN \"SalSale\".\"lc_state\" IN ('DRAFT','SINVOICE_PENDANT') THEN \"SalDetail\".\"quantity\" ELSE 0 END)) AS stock_reserved"
//				"(SUM(CASE WHEN \"SalSale\".\"lc_state\" IN ('DRAFT','SINVOICE_PENDANT') THEN (\"SalDetail\".\"quantity\" - \"SalDetail\".\"backorder\") ELSE 0 END)) AS stock_reserved"
//				),
//			'conditions'=>array(
//				'SalDetail.inv_warehouse_id'=>$warehouse,
//				'SalDetail.inv_item_id'=>$items,
//				$dateRanges
//				),
//			'group'=>array('SalDetail.inv_item_id', 'SalDetail.inv_warehouse_id'), 
//			'order'=>array('SalDetail.inv_item_id', 'SalDetail.inv_warehouse_id') 
//		));
//            $payDebt = $total[0]['total'] - $paid[0]['paid'];
            $payDebt = number_format((float)$total[0]['total'] - $paid[0]['paid'], 2, '.', '');
			if($payDebt < 0){$payDebt = '0.00';}
//			debug($total[0]['total']);
//			debug($paid[0]['paid']);
//			debug($debt);
            $this->set(compact(/* 'pays', */ 'datePay', 'payDebt'/* , 'amount' */));
        }
    }

    public function ajax_initiate_modal_edit_pay() {
        if ($this->RequestHandler->isAjax()) {
            $docCode = $this->request->data['docCode'];
//			$datePay = $this->request->data['date']; //temporal date that shows in the payment modal

            $discountType = current($this->SalSale->find('list', array(
				'fields' => array('SalSale.discount_type'),
				'conditions' => array('SalSale.doc_code' => $docCode)
			)));
			if($discountType == 'PERCENT'){
				$discountString = '- (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100)';
			}else if($discountType == 'BOB'){
				$discountString = '- "SalSale"."discount"';
			}else if($discountType == 'NONE'){
				$discountString = '';
			}
			$total = $this->SalSale->SalDetail->find('first', array(
//						'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price") - (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100),2)) AS total'),//YA NO SIRVE EL DISCOUNT PQ PUEDE VARIAR EL TIPO
				'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price")' .$discountString. ',2)) AS total'),
				'conditions' => array('SalSale.doc_code' => $docCode),
				'group' => array('SalSale.discount')
			));		

            $paid = $this->SalSale->SalPayment->find('first', array(
                'fields' => array('(ROUND(SUM("SalPayment"."amount"),2)) AS paid'),
                'conditions' => array('SalSale.doc_code' => $docCode),
//			'group'=>array('SalSale.discount')
            ));

//            $payDebt = $total[0]['total'] - $paid[0]['paid'];
			$payDebt = number_format((float)$total[0]['total'] - $paid[0]['paid'], 2, '.', '');

            $this->set(compact(/* 'pays', 'datePay', */ 'payDebt'/* , 'amount' */));
        }
    }

    public function ajax_update_ex_rate() {
        if ($this->RequestHandler->isAjax()) {
            $date = $this->request->data['date'];

            $this->loadModel('AdmParameter');
            $currency = $this->AdmParameter->AdmParameterDetail->find('first', array(
                'conditions' => array(
                    'AdmParameter.name' => 'Moneda',
                    'AdmParameterDetail.par_char1' => 'Dolares'
                )
            ));
            $currencyId = $currency['AdmParameterDetail']['id'];
            $this->loadModel('AdmExchangeRate');
            $xxxRate = $this->AdmExchangeRate->find('first', array(
                'fields' => array('AdmExchangeRate.value'),
                'order' => array('AdmExchangeRate.date' => 'desc'),
                'conditions' => array(
                    'AdmExchangeRate.currency' => $currencyId,
                    'AdmExchangeRate.date <=' => $date
                ),
                'recursive' => -1
            ));
            if ($xxxRate == array() || $xxxRate['AdmExchangeRate']['value'] == null) {
                $exRate = ''; //ESTO TIENE Q SER ''
            } else {
                $exRate = $xxxRate['AdmExchangeRate']['value'];
            }

            $this->set(compact('exRate'));
        } else {
            $this->redirect($this->Auth->logout());
        }
    }

    public function ajax_check_code_duplicity() {
        if ($this->RequestHandler->isAjax()) {
            $noteCode = $this->request->data['noteCode'];
            $genericCode = $this->request->data['genericCode'];
            if ($genericCode == '') {
                $result = $this->SalSale->find('count', array(
                    'fields' => array('SalSale.note_code'),
                    'conditions' => array(
                        'SalSale.note_code ' => trim($noteCode),
                        'NOT' => array('SalSale.lc_state' => array('NOTE_LOGIC_DELETED', 'SINVOICE_LOGIC_DELETED', 'NOTE_CANCELLED', 'SINVOICE_CANCELLED', 'DRAFT', 'DRAFT_DELETED'))//ver si esta bien poner DRAFT_DELETED
                    )
                ));
            } else {
                $result = $this->SalSale->find('count', array(
                    'fields' => array('SalSale.note_code'),
                    'conditions' => array(
                        'SalSale.code !=' => $genericCode,
                        'SalSale.note_code ' => trim($noteCode),
                        'NOT' => array('SalSale.lc_state' => array('NOTE_LOGIC_DELETED', 'SINVOICE_LOGIC_DELETED', 'NOTE_CANCELLED', 'SINVOICE_CANCELLED', 'DRAFT', 'DRAFT_DELETED'))//ver si esta bien poner DRAFT_DELETED
                    )
                ));
            }
			echo $result;
        }
    }

	public function ajax_check_code_duplicity_pays_coherency(){	
		if($this->RequestHandler->isAjax()){
			$noteCode = $this->request->data['noteCode'];
            $genericCode = $this->request->data['genericCode'];
            if ($genericCode == '') {
                $result = $this->SalSale->find('count', array(
                    'fields' => array('SalSale.note_code'),
                    'conditions' => array(
                        'SalSale.note_code ' => trim($noteCode),
                        'NOT' => array('SalSale.lc_state' => array('NOTE_LOGIC_DELETED', 'SINVOICE_LOGIC_DELETED', 'NOTE_CANCELLED', 'SINVOICE_CANCELLED', 'DRAFT', 'DRAFT_DELETED'))//ver si esta bien poner DRAFT_DELETED
                    )
                ));
            } else {
                $result = $this->SalSale->find('count', array(
                    'fields' => array('SalSale.note_code'),
                    'conditions' => array(
                        'SalSale.code !=' => $genericCode,
                        'SalSale.note_code ' => trim($noteCode),
                        'NOT' => array('SalSale.lc_state' => array('NOTE_LOGIC_DELETED', 'SINVOICE_LOGIC_DELETED', 'NOTE_CANCELLED', 'SINVOICE_CANCELLED', 'DRAFT', 'DRAFT_DELETED'))//ver si esta bien poner DRAFT_DELETED
                    )
                ));
            }
			////////////////////////////////////////////////////////////
			$docCode = $this->request->data['docCode'];
			$discountType = current($this->SalSale->find('list', array(
				'fields' => array('SalSale.discount_type'),
				'conditions' => array('SalSale.doc_code' => $docCode)
			)));
			if($discountType == 'PERCENT'){
				$discountString = '- (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100)';
			}else if($discountType == 'BOB'){
				$discountString = '- "SalSale"."discount"';
			}else if($discountType == 'NONE'){
				$discountString = '';
			}
			$total = $this->SalSale->SalDetail->find('first', array(
//				'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price") - (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100),2)) AS total'),//YA NO SIRVE EL DISCOUNT PQ PUEDE VARIAR EL TIPO
				'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price")' .$discountString. ',2)) AS total'),
				'conditions' => array('SalSale.doc_code' => $docCode),
				'group' => array('SalSale.discount')
			));		

            $paid = $this->SalSale->SalPayment->find('first', array(
                'fields' => array('(ROUND(SUM("SalPayment"."amount"),2)) AS paid'),
                'conditions' => array('SalSale.doc_code' => $docCode),
            ));
			$payDebt = number_format((float)$total[0]['total'] - $paid[0]['paid'], 2, '.', '');
			////////////////////////////////////////////////////////////
			$backorderSum = $this->SalSale->SalDetail->find('first', array(
//				'fields' => array('(ROUND(SUM("SalDetail"."quantity" * "SalDetail"."sale_price") - (SUM("SalDetail"."quantity" * "SalDetail"."sale_price") * "SalSale"."discount"/100),2)) AS total'),//YA NO SIRVE EL DISCOUNT PQ PUEDE VARIAR EL TIPO
				'fields' => array('SUM("SalDetail"."backorder") AS backorder_sum'),
				'conditions' => array('SalSale.doc_code' => $docCode),
				'group' => array('SalSale.discount')
			));		
//			debug($payDebt);
//			debug($backorderSum[0]['backorder_sum']);
//			die();
			echo $result.'|'.$payDebt.'|'.$backorderSum[0]['backorder_sum'];
		}
	}
	
    public function ajax_check_document_state() {
        if ($this->RequestHandler->isAjax()) {
            $purchaseId = $this->request->data['purchaseId'];
            $purchaseCode = $this->request->data['genericCode'];
            $action = $this->request->data['action'];
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
            ////////////////////////////
//			$movementId = $this->_get_doc_id(null, $genericCode, 1, 1);//EL ALTO 2 MANUALMENTE VER COMO ELEGIR ESTO
            $this->loadModel('InvMovement');
            $movementsExistance = $this->InvMovement->find('list', array(
                'fields' => array(
                    'InvMovement.lc_state'
                ),
                'conditions' => array(
                    'InvMovement.document_code' => $purchaseCode
                )
            ));
            $movementsExistanceClear = array_values($movementsExistance);
            ////////////////////////////
            $cont = 0;
            for ($i = 0; $i < count($movementsExistanceClear); $i++) {
                if ($movementsExistanceClear[$i] == 'APPROVED') {
                    $cont = $cont + 1;
                }
            }
//			debug(count($movementsExistanceClear));
//			debug($cont);
//			die();
            if (count($movementsExistanceClear) == $cont) {
                echo "approve";
            }
        }
    }

    public function ajax_check_document_state1() {
        if ($this->RequestHandler->isAjax()) {
            $purchaseId = $this->request->data['purchaseId'];
            $purchaseCode = $this->request->data['genericCode'];
            $action = $this->request->data['action'];
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
            ////////////////////////////
//			$movementId = $this->_get_doc_id(null, $genericCode, 1, 1);//EL ALTO 2 MANUALMENTE VER COMO ELEGIR ESTO
            $this->loadModel('InvMovement');
            $movementsExistance = $this->InvMovement->find('list', array(
                'fields' => array(
                    'InvMovement.lc_state'
                ),
                'conditions' => array(
                    'InvMovement.document_code' => $purchaseCode
                )
            ));
            $movementsExistanceClear = array_values($movementsExistance);
            ////////////////////////////
            $cont = 0;
            for ($i = 0; $i < count($movementsExistanceClear); $i++) {
                if ($movementsExistanceClear[$i] == 'CANCELLED' OR $movementsExistanceClear[$i] == 'LOGIC_DELETED') {
                    $cont = $cont + 1;
                }
            }
//			debug(count($movementsExistanceClear));
//			debug($cont);
//			die();
            if (count($movementsExistanceClear) == $cont) {
                echo "cancell";
            }
        }
    }
	
	public function ajax_check_movements_state(){
		if($this->RequestHandler->isAjax()){
//			$purchaseId = $this->request->data['purchaseId'];
			$saleCode = $this->request->data['genericCode'];
			//gets MOVEMENT id type 1(hay stock)
			$movementId = $this->_get_doc_id(null, $saleCode, 1/*, 2*/);//EL ALTO 2 MANUALMENTE VER COMO ELEGIR ESTO
			
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

    public function ajax_generate_clone(){
        if($this->RequestHandler->isAjax()){
            ////////////////////////////////////////////INICIO-CAPTURAR AJAX////////////////////////////////////////////////////////
            $arraySaleInvoice = array();
            $dataMovement = array();
            $saleId = $this->request->data['saleId'];
            $noteCode = $this->request->data['noteCode'];
            $code = $this->request->data['code'];
            $date = $this->request->data['date'];
            $employeeId = $this->request->data['employeeId'];
            $taxNumberId = $this->request->data['taxNumberId'];
            $salesmanId = $this->request->data['salesmanId'];
            $description = $this->request->data['description'];
            $discountType = $this->request->data['discountType'];
            $invoice = $this->request->data['invoice'];
            $discount = $this->request->data['discount'];
            $exRate = $this->request->data['exRate'];
            $arrayItemsDetails = $this->request->data['arrayItemsDetails'];
            for ($i = 0; $i < count($arrayItemsDetails); $i++) {
                $arrayItemsDetails[$i]['approved'] = 0;
            }
            $error=0;
            ////////////////////////////////////////////FIN-CAPTURAR AJAX////////////////////////////////////////////////////////
            ////////////////////////////////////////////INICIO-CREAR PARAMETROS////////////////////////////////////////////////////////

            $saleCode = $this->_generate_code('VEN');
            $saleInvoiceDocCode = $this->_generate_doc_code('VFA');
            if($saleCode == 'error'){$error++;}
            if($saleInvoiceDocCode == 'error'){$error++;}
            //header for new INVOICE
            $arraySaleInvoice['code'] = $saleCode;
            $arraySaleInvoice['doc_code'] = $saleInvoiceDocCode;
            $arraySaleInvoice['note_code']=$noteCode;
            $arraySaleInvoice['date']=$date;
            $arraySaleInvoice['sal_employee_id'] = $employeeId;
            $arraySaleInvoice['sal_tax_number_id'] = $taxNumberId;
            $arraySaleInvoice['salesman_id'] = $salesmanId;
            $arraySaleInvoice['description']=$description;
            $arraySaleInvoice['ex_rate']=$exRate;
            $arraySaleInvoice['discount']=$discount;
            if($discountType==1){$arraySaleInvoice['discount_type']='NONE';}else if($discountType == 2){$arraySaleInvoice['discount_type']='PERCENT';}else/*if($discountTypeName == 'BOB')*/{$arraySaleInvoice['discount_type']='BOB';}
            $arraySaleInvoice['invoice']=$invoice;
            $arraySaleInvoice['location']='La Paz';//COMO SABER DE QUE DEPARTAMENTO ES???????????
            $arraySaleInvoice['lc_state']='SINVOICE_PENDANT';
            $arraySaleInvoice['paid']=false;
            $arraySaleInvoice['reserve']=true;
            $arraySaleInvoice['deliver']=false;
            //header for old INVOICE
            $arraySaleOrigInvoice['id']=$saleId;
            $arraySaleOrigInvoice['lc_state']='SINVOICE_CANCELLED';
//            $arraySaleOrigInvoice['code_for_prices']=$code;
            //header for old NOTE
            $idOriginalNote = $this->SalSale->find('list', array(
                    'fields'=>array(
                            'SalSale.id'),
                    'conditions'=>array(
                            'SalSale.code'=>$code
                            ,'SalSale.lc_state'=>'NOTE_APPROVED')
            ));
            //header for MOVEMENT
            $this->loadModel('InvMovement');
            $arrayMovement = $this->InvMovement->find('all', array(
                    'fields'=>array(
                            'InvMovement.id'
                            ),
                    'conditions'=>array(
                                    'InvMovement.document_code'=>$code
                            )
                    ,'order' => array('InvMovement.id' => 'ASC')
                    ,'recursive'=>0
            ));
            //item details for INVOICE
            $arraySaleDetails = $arrayItemsDetails;
            //item details for MOVEMENT
            $dataSale[0] = array('SalSale'=>$arraySaleInvoice, 'SalDetail'=> $arraySaleDetails);
            $dataSale[1] = array('SalSale'=>$arraySaleOrigInvoice);
            if($idOriginalNote != array()){
                $arraySaleOrigNote['id']=current($idOriginalNote);
                $arraySaleOrigNote['lc_state']='NOTE_CANCELLED';
                $dataSale[2] = array('SalSale'=>$arraySaleOrigNote);
            }else{
                $dataSale[2] = array();
            }
            for($i=0;$i<count($arrayMovement);$i++){
                $arrayMovement[$i]['InvMovement']['lc_state'] = 'CANCELLED';
            }
            $dataMovement = $arrayMovement;
            
            ////////////////////////////////////////////FIN-CREAR PARAMETROS////////////////////////////////////////////////////////
//			print_r($arraySaleInvoice);
//			print_r($arraySaleDetails);
//			print_r($dataMovement);
//			print_r($dataCostDetail);
//			print_r($dataPayDetail);
//			die();
            ////////////////////////////////////////////INICIO-SAVE////////////////////////////////////////////////////////
            if($error == 0){
                /////////////////////START - SAVE/////////////////////////////
//				$res = $this->PurPurchase->saveMovement($dataPurchase, $dataPurchaseDetail, $dataMovement, $dataMovementDetail, $dataMovementHeadsUpd, $OPERATION, $ACTION, $STATE, $purchaseInvoiceDocCode, $purchaseCode, $dataPayDetail, $dataCostDetail, $arrayFobPrices, $arrayCifPrices, $arrayForValidate);
                $res = $this->SalSale->updateMovement($dataSale, $dataMovement/*, $dataPayDetail, $dataCostDetail*/, $pricesCode = $code );

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

    //////////////////////////////////////////// END - AJAX /////////////////////////////////////////////////
    //////////////////////////////////////////// START - PRIVATE ///////////////////////////////////////////////

    public function _get_movements_details($idMovement) {
        $movementDetails = $this->SalSale->SalDetail->find('all', array(
            'conditions' => array('SalDetail.sal_sale_id' => $idMovement), /* REVISAR ESTO V */
            'fields' => array('InvItem.name', 'InvItem.code', 'SalDetail.sale_price', 'SalDetail.quantity', 'SalDetail.backorder', 'SalDetail.inv_warehouse_id', 'SalDetail.approved', 'InvItem.id', 'InvWarehouse.name', 'InvWarehouse.id', 'InvItem.id')
			,'order' => array('SalDetail.date_created ASC')
//						,'order' => array('InvItem.code ASC', 'SalDetail.inv_warehouse_id DESC')
//			,'order' => array('InvItem.code DESC', 'SalDetail.inv_warehouse_id ASC')
		));
//		debug($movementDetails);
        ///////////for new stock method
        $items = array();
        foreach ($movementDetails as $value) {//get a clean items arrays
            $items[/* $value['InvItem']['id'] */] = $value['InvItem']['id'];
            $warehouses[] = $value['InvWarehouse']['id'];
        }
        $realStocks = $this->_get_real_stocks_by_warehouse($items, $warehouses/* $movementDetails[0]['SalDetail']['inv_warehouse_id'] */); //get all the stocks
        $reservedStocks = $this->_get_reserved_stocks_by_warehouse_minus_backorder($items, $warehouses/* $movementDetails[0]['SalDetail']['inv_warehouse_id'] */); //get all the stocks
        ///////////////////
        $formatedMovementDetails = array();
        foreach ($movementDetails as $key => $value) {
            $formatedMovementDetails[$key] = array(
                'itemId' => $value['InvItem']['id'],
                'item' => '[ ' . $value['InvItem']['code'] . ' ] ' . $value['InvItem']['name'],
                'salePrice' => $value['SalDetail']['sale_price'], //llamar precio
                'cantidad' => $value['SalDetail']['quantity'], //llamar cantidad
                'backorder' => $value['SalDetail']['backorder'], //llamar cantidad
                'warehouseId' => $value['InvWarehouse']['id'],
                'warehouse' => $value['InvWarehouse']['name']//llamar almacen
                , 'stock' => $this->_find_real_item_stock_by_warehouse($realStocks, $value['InvItem']['id'], array($value['InvWarehouse']['id']))
                /* temp */, 'reservedStock' => $this->_find_reserved_item_stock_by_warehouse($reservedStocks, $value['InvItem']['id'], array($value['InvWarehouse']['id']))
				,'approved' => $value['SalDetail']['approved']//llamar despachados
            );
        }
        return $formatedMovementDetails;
    }

    public function _get_pays_details($idMovement) {
        $paymentDetails = $this->SalSale->SalPayment->find('all', array(
            'conditions' => array(
                'SalPayment.sal_sale_id' => $idMovement
            ),
            'fields' => array('SalPayment.date', 'SalPayment.amount', 'SalPayment.description')
        ));

        $formatedPaymentDetails = array();
        foreach ($paymentDetails as $key => $value) {
            $formatedPaymentDetails[$key] = array(
                'dateId' => $value['SalPayment']['date'], //llamar precio
                //'payDate'=>strftime("%A, %d de %B de %Y", strtotime($value['SalPayment']['date'])),
                'payDate' => strftime("%d/%m/%Y", strtotime($value['SalPayment']['date'])),
                'payAmount' => $value['SalPayment']['amount'], //llamar cantidad
                'payDescription' => $value['SalPayment']['description']
            );
        }
//debug($formatedPaymentDetails);	//	strftime("%A, %d de %B de %Y", $value['SalPayment']['date'])
        return $formatedPaymentDetails;
    }

    private function _get_price($itemId, $date, $type, $currType) {
        $this->loadModel('InvPrice');
        //To change UK date format to US date format
        $bits = explode('/', $date);
        $date = $bits[1] . '/' . $bits[0] . '/' . $bits[2];
        //To get id of the price type
        $typeId = $this->InvPrice->InvPriceType->find('list', array(
            'fields' => array(
                'InvPriceType.id'
            ),
            'conditions' => array(
                'InvPriceType.name' => $type
            )
        ));
        //To get the history of prices
        $prices = $this->InvPrice->find('list', array(
            'fields' => array(
                'InvPrice.id',
                'InvPrice.date'
            ),
            'conditions' => array(
                'InvPrice.inv_item_id' => $itemId,
                'InvPrice.inv_price_type_id' => $typeId//'InvPrice.inv_price_type_id'=>1
            )
        ));
        if ($prices <> null) {
            //To get the list of subtracted dates in unix time format
            foreach ($prices as $id => $day) {
                $interval[$id] = abs(strtotime($date) - strtotime($day));
            }
            asort($interval);
            $closest = key($interval);
            //To get the price
            if ($currType == 'dolar') {
                $priceField = $this->InvPrice->find('first', array(
                    'fields' => array(
                        'InvPrice.ex_price'
                    ),
                    'conditions' => array(
                        'InvPrice.id' => $closest
                    )
                ));
                $price = $priceField['InvPrice']['ex_price'];
            } else {
                $priceField = $this->InvPrice->find('first', array(
                    'fields' => array(
                        'InvPrice.price'
                    ),
                    'conditions' => array(
                        'InvPrice.id' => $closest
                    )
                ));
                $price = $priceField['InvPrice']['price'];
            }
            if ($price === null) {
                $price = 0;
            }
        } else {
            $price = 0;
        }
        //debug($price);
        return $price;
    }

    private function _get_doc_id($saleId, $movementCode, $type, $warehouseId) {
        if ($saleId <> null) {
            $invoiceId = $this->SalSale->find('list', array(
                'fields' => array('SalSale.id'),
                'conditions' => array(
                    'SalSale.code' => $movementCode,
                    "SalSale.id !=" => $saleId
                )
            ));
            $docId = key($invoiceId);
        } else {
            $this->loadModel('InvMovement');
            $movementId = $this->InvMovement->find('list', array(
                'fields' => array('InvMovement.id'),
                'conditions' => array(
                    'InvMovement.document_code' => $movementCode,
                    'InvMovement.type' => $type,
                    'InvMovement.inv_warehouse_id' => $warehouseId,
                )
            ));
            $docId = key($movementId);
        }
        return $docId;
    }

	//gets a list of (inv_item_id, inv_warehouse_id and its (quantity - approved - backorder = reserved quantity)) for the required items in the required warehouses (sal_details)
    private function _get_reserved_stocks_by_warehouse_minus_backorder($items, $warehouses, $limitDate = '', $dateOperator = '<=') {
        $dateRanges = array();
        if ($limitDate <> '') {
            $dateRanges = array('SalSale.date ' . $dateOperator => $limitDate);
        }

        $sales = $this->SalSale->SalDetail->find('all', array(
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
    }

	//gets a list of (inv_item_id, inv_warehouse_id and its (quantity = requested quantity)) for the required items in the required warehouses (sal_details)
    private function _get_reserved_stocks_by_warehouse($items, $warehouse, $limitDate = '', $dateOperator = '<=') {
        $dateRanges = array();
        if ($limitDate <> '') {
            $dateRanges = array('SalSale.date ' . $dateOperator => $limitDate);
        }

        $sales = $this->SalSale->SalDetail->find('all', array(
            'fields' => array(
                "SalDetail.inv_item_id",
                "SalDetail.inv_warehouse_id",
                "(SUM(CASE WHEN \"SalSale\".\"lc_state\" IN ('DRAFT','SINVOICE_PENDANT') THEN \"SalDetail\".\"quantity\" ELSE 0 END)) AS stock_reserved"
//				"(SUM(CASE WHEN \"SalSale\".\"lc_state\" IN ('DRAFT','SINVOICE_PENDANT') THEN (\"SalDetail\".\"quantity\" - \"SalDetail\".\"backorder\") ELSE 0 END)) AS stock_reserved"
            ),
            'conditions' => array(
                'SalDetail.inv_warehouse_id' => $warehouse,
                'SalDetail.inv_item_id' => $items,
                $dateRanges
            ),
            'group' => array('SalDetail.inv_item_id', 'SalDetail.inv_warehouse_id'),
            'order' => array('SalDetail.inv_item_id', 'SalDetail.inv_warehouse_id')
        ));

        return $sales;
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

	//gets a list of (inv_item_id and its stock) for the required items on one required warehouse (inv_movement_details)
    private function _get_real_stocks($items, $warehouse, $limitDate = '', $dateOperator = '<=') {
        $this->loadModel('InvMovement');
        $this->InvMovement->InvMovementDetail->unbindModel(array('belongsTo' => array('InvItem')));
        $this->InvMovement->InvMovementDetail->bindModel(array(
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

        $movements = $this->InvMovement->InvMovementDetail->find('all', array(
            'fields' => array(
                "InvMovementDetail.inv_item_id",
                "(SUM(CASE WHEN \"InvMovementType\".\"status\" = 'entrada' AND \"InvMovement\".\"lc_state\" = 'APPROVED' THEN \"InvMovementDetail\".\"quantity\" ELSE 0 END))-
				(SUM(CASE WHEN \"InvMovementType\".\"status\" = 'salida' AND \"InvMovement\".\"lc_state\" = 'APPROVED' THEN \"InvMovementDetail\".\"quantity\" ELSE 0 END)) AS stock"
            ),
            'conditions' => array(
                'InvMovement.inv_warehouse_id' => $warehouse,
                'InvMovementDetail.inv_item_id' => $items,
                $dateRanges
            ),
            'group' => array('InvMovementDetail.inv_item_id'),
            'order' => array('InvMovementDetail.inv_item_id')
        ));

        return $movements;
    }
	
	//gets the stock of one item from the list that gets _get_real_stocks (inv_movement_details)
    private function _find_real_item_stock($stocks, $item) {
        foreach ($stocks as $stock) {//find required stock inside stocks array 
            if ($item == $stock['InvMovementDetail']['inv_item_id']) {
                return $stock[0]['stock'];
            }
        }
        //this fixes in case there isn't any item inside movement_details yet with a determinated warehouse
        return 0;
    }

	//gets a list of (inv_item_id, inv_warehouse_id and its stock) for the required items in the required warehouses (inv_movement_details) (so we can get stock by warehouse)
    private function _get_real_stocks_by_warehouse($items, $warehouses, $limitDate = '', $dateOperator = '<=') {
        $this->loadModel('InvMovement');
        $this->InvMovement->InvMovementDetail->unbindModel(array('belongsTo' => array('InvItem')));
        $this->InvMovement->InvMovementDetail->bindModel(array(
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

        $movements = $this->InvMovement->InvMovementDetail->find('all', array(
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

    private function _generate_code($keyword) {
        $period = $this->Session->read('Period.name');
        if ($period <> '') {
            try {
//				if($action == 'save_invoice'){
                $movements = $this->SalSale->find('count', array(
                    'conditions' => array('SalSale.lc_state' => array('SINVOICE_PENDANT', 'SINVOICE_APPROVED', 'SINVOICE_CANCELLED', 'SINVOICE_LOGIC_DELETED', 'DRAFT', 'DRAFT_DELETED'))//ver si esta bien poner DRAFT_DELETED
                ));
//				}else{
//					$movements = $this->SalSale->find('count', array(
//						'conditions'=>array('SalSale.lc_state'=>array('NOTE_PENDANT','NOTE_APPROVED','NOTE_CANCELLED','NOTE_LOGIC_DELETED'))
//						));
//				}	
            } catch (Exception $e) {
                return 'error';
            }
        } else {
            return 'error';
        }

        $quantity = $movements + 1;
        $code = $keyword . '-' . $period . '-' . $quantity;
        return $code;
    }

    private function _generate_doc_code($keyword) {
        $period = $this->Session->read('Period.name');
        if ($period <> '') {
            try {
                if ($keyword == 'NOT') {
                    $movements = $this->SalSale->find('count', array(
                        'conditions' => array('SalSale.lc_state' => array('NOTE_PENDANT', 'NOTE_APPROVED', 'NOTE_CANCELLED', 'NOTE_LOGIC_DELETED'))
                    ));
                } elseif ($keyword == 'VFA') {
                    $movements = $this->SalSale->find('count', array(
                        'conditions' => array('SalSale.lc_state' => array('SINVOICE_PENDANT', 'SINVOICE_APPROVED', 'SINVOICE_CANCELLED', 'SINVOICE_LOGIC_DELETED'))
                    ));
                }
            } catch (Exception $e) {
                return 'error';
            }
        } else {
            return 'error';
        }

        $quantity = $movements + 1;
        $docCode = $keyword . '-' . $period . '-' . $quantity;
        return $docCode;
    }

    private function _generate_movement_code($keyword, $type) {
        $this->loadModel('InvMovement');
        $period = $this->Session->read('Period.name');
        $movementType = '';
        if ($keyword == 'ENT') {
            $movementType = 'entrada';
        }
        if ($keyword == 'SAL') {
            $movementType = 'salida';
        }
        if ($period <> '') {
            try {
                $movements = $this->InvMovement->find('count', array(
                    'conditions' => array(
                        'InvMovementType.status' => $movementType
                        , 'InvMovement.code !=' => 'NO'
                    //	,'InvMovement.lc_state !='=>'DRAFT'
                    )
                ));
            } catch (Exception $e) {
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
        } else {
            return 'error';
        }
        if ($type == 'inc') {
            static $inc = 0;
            $quantity = $movements + 1 + $inc;
            $inc++;
        } else {
            $quantity = $movements + 1;
        }
        $code = $keyword . '-' . $period . '-' . $quantity;
        return $code;
    }

    private function _generate_movement_code_id($keyword, $type/* , $idsToDelete */) {
        $this->loadModel('InvMovement');
        $period = $this->Session->read('Period.name');
        $movementType = '';
        if ($keyword == 'ENT') {
            $movementType = 'entrada';
        }
        if ($keyword == 'SAL') {
            $movementType = 'salida';
        }
        if ($period <> '') {
            try {
                $movements = $this->InvMovement->find('count', array(
                    'conditions' => array(
                        'InvMovementType.status' => $movementType
                        , 'InvMovement.code !=' => 'NO'
                        , 'InvMovement.id !=' => $idsToDelete
                    )
                ));
            } catch (Exception $e) {
                return 'error';
            }
        } else {
            return 'error';
        }
        if ($type == 'inc') {
            static $inc = 0;
            $quantity = $movements + 1 + $inc;
            $inc++;
        } else {
            $quantity = $movements + 1;
        }
        $code = $keyword . '-' . $period . '-' . $quantity;
        return $code;
    }

//	private function _find_stock($idItem, $idWarehouse){		
//		$movementsIn = $this->_get_quantity_movements_item($idItem, $idWarehouse, 'entrada');
//		$movementsOut = $this->_get_quantity_movements_item($idItem, $idWarehouse, 'salida');
//		$add = array_sum($movementsIn);
//		$sub = array_sum($movementsOut);
//		$stock = $add - $sub;
//		return $stock;
//	}

    private function _get_quantity_movements_item($idItem, $idWarehouse, $status) {
        //******************************************************************************//
        //unbind for perfomance InvItem 'cause it isn't needed
//		$this->InvMovement->InvMovementDetail->unbindModel(array(
//			'belongsTo' => array('InvItem')
//		));
//		//Add association for InvMovementType
        $this->SalSale->SalDetail->InvItem->InvMovementDetail->bindModel(array(
            'hasOne' => array(
                'InvMovementType' => array(
                    'foreignKey' => false,
                    'conditions' => array('InvMovement.inv_movement_type_id = InvMovementType.id')
                )
            )
        ));
        //******************************************************************************//
        //Movements
//		$movs = $this->SalSale->SalDetail->InvItem->InvMovementDetail->InvMovement->find('all', array(	
//			'fields'=>array('InvMovement.inv_warehouse_id', 'InvMovement.lc_state'),
//			'conditions'=>array(
//				'InvMovement.inv_warehouse_id'=>$idWarehouse,
//				'InvMovementDetail.inv_item_id'=>$idItem,
//				'InvMovementType.status'=>$status,
//				'InvMovement.lc_state'=>'APPROVED',
//				)
//		));
        //	$movements = $this->InvMovement->InvMovementDetail->find('all', array(
        $movements = $this->SalSale->SalDetail->InvItem->InvMovementDetail->find('all', array(
            'fields' => array('InvMovementDetail.inv_movement_id', 'InvMovementDetail.quantity'),
            'conditions' => array(
                'InvMovement.inv_warehouse_id' => $idWarehouse,
                'InvMovementDetail.inv_item_id' => $idItem,
                'InvMovementType.status' => $status,
                'InvMovement.lc_state' => 'APPROVED',
            )
        ));
        //Give format to nested array movements
        $movementsCleaned = $this->_clean_nested_arrays($movements);
        return $movementsCleaned;
    }

    private function _clean_nested_arrays($array) {
        $clean = array();
        foreach ($array as $key => $value) {
            $clean[$key] = $value['InvMovementDetail']['quantity'];
        }
        return $clean;
    }

    //////////////////////////////////////////// END - PRIVATE /////////////////////////////////////////////////
    //*******************************************************************************************************//
    /////////////////////////////////////////// END - CLASS ///////////////////////////////////////////////
    //*******************************************************************************************************//

    /*     * ****************************************************************************************************************** */
    /*     * ******************************************************************************************************************** */
    /*     * ****************************************************************************************************************** */
    /*     * ******************************************************************************************************************** */
    /*     * ****************************************************************************************************************** */
    /*     * ******************************************************************************************************************** */

//+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++


    public function ajax_cancell_all() {
        if ($this->RequestHandler->isAjax()) {
            $purchaseId = $this->request->data['purchaseId'];
            $type = $this->request->data['type'];
            $genCode = $this->request->data['genCode'];
            $purchaseId2 = $this->_get_doc_id($purchaseId, $genCode, null, null);

            if ($this->SalSale->updateAll(array('SalSale.lc_state' => "'$type'", 'SalSale.lc_transaction' => "'MODIFY'"), array('SalSale.id' => $purchaseId))
            ) {
                if ($this->SalSale->updateAll(array('SalSale.lc_state' => "'SINVOICE_CANCELLED'", 'SalSale.lc_transaction' => "'MODIFY'"), array('SalSale.id' => $purchaseId2))
                ) {
                    echo 'success';
                }
            }

            if ($type === 'NOTE_CANCELLED') {
                $this->loadModel('InvMovement');
                $arrayMovement5 = $this->InvMovement->find('all', array(
                    'fields' => array(
                        'InvMovement.id',
//							,'InvMovement.date'
//							,'InvMovement.description'
                        'InvMovement.inv_warehouse_id'
                    ),
                    'conditions' => array(
                        'InvMovement.document_code' => $genCode
                    )
                    , 'order' => array('InvMovement.id' => 'ASC')
                    , 'recursive' => 0
                ));
                if ($arrayMovement5 <> null) {
                    for ($i = 0; $i < count($arrayMovement5); $i++) {
                        $arrayMovement5[$i]['InvMovement']['lc_state'] = 'CANCELLED';
//							$arrayMovement5[$i]['InvMovement']['code'] = 'NO'; //not sure to put this
                    }
                }
                if ($arrayMovement5 <> null) {
                    $dataMovement5 = $arrayMovement5;
                }
                if ($arrayMovement5 <> null) {
                    $res5 = $this->InvMovement->saveSale($dataMovement5, null, 'UPDATEHEAD', null, null, null);
                }
            }
        }
    }

    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    public function ajax_generate_movements() {
        if ($this->RequestHandler->isAjax()) {
            ////////////////////////////////////////////INICIO-CAPTURAR AJAX/////////////////////////////////////////////////////
            $arrayItemsDetails = $this->request->data['arrayItemsDetails'];
            $date = $this->request->data['date'];
            $description = $this->request->data['description'];
//			$note_code = $this->request->data['note_code'];
            $genericCode = $this->request->data['genericCode'];
//			$originCode = $this->request->data['originCode'];
            $error = 0;
            $movementDocCode = '';
//print_r($arrayItemsDetails);
            $this->loadModel('InvMovement');
            $ids = $this->InvMovement->find('list', array(
                'fields' => array('InvMovement.id'),
                'conditions' => array('InvMovement.document_code' => $genericCode)
            ));
            $idsOrdered = array_values($ids); //gets the ids if the movements where already created (to delete them)


            foreach ($arrayItemsDetails as $val) {
                $arrayWarehouses[] = $val['inv_warehouse_id'];
            }
//			debug($arrayWarehouses);
            $arrayWarehousesList = array_values(array_unique($arrayWarehouses));
//			sort($arrayWarehousesList);
//			debug($arrayWarehousesList);

            for ($i = 0; $i < count($arrayWarehousesList); $i++) {
                $arrayMovement = array();
                $arrayMovementDetails = array();
//				$arrayBOMovement = array();
//				$arrayBOMovementDetails = array();
//				$cont = 0;
                $movementDocCode = '';
//				$movementBODocCode = '';
                for ($j = 0; $j < count($arrayItemsDetails); $j++) {
                    if ($arrayItemsDetails[$j]['inv_warehouse_id'] == $arrayWarehousesList[$i]) {
                        $itemId = $arrayItemsDetails[$j]['inv_item_id'];
                        $quantity = $arrayItemsDetails[$j]['quantity'];
                        $backorder = $arrayItemsDetails[$j]['backorder'];
//						$warehouseId = $arrayItemsDetails[$j]['inv_warehouse_id'];
//						$stocks = $this->_get_real_stocks($itemId, $warehouseId);
//						$stock = $this->_find_real_item_stock($stocks, $itemId);
//						debug($itemId.'+'.$warehouseId.'=>'.$quantity.'@'.$stock);
//						if ($quantity > $stock) { //backorder
//							$cont++;
//							if ($stock > 0){
//								$arrayMovementDetails[] = array('inv_item_id'=>$itemId, 'quantity'=>$stock);
//							}
//							if ($stock >= 0){
//								$arrayBOMovementDetails[] = array('inv_item_id'=>$itemId, 'quantity'=>($quantity-$stock));
//							}else{
//								$arrayBOMovementDetails[] = array('inv_item_id'=>$itemId, 'quantity'=>$quantity);
//							}
//						} else {	//sin backorder
//							$arrayMovementDetails[] = array('inv_item_id'=>$itemId, 'quantity'=>$quantity);
//						}
                        if ($backorder > 0) {
//							$cont++;
                            if (($quantity - $backorder) > 0) {
                                $arrayMovementDetails[] = array('inv_item_id' => $itemId, 'quantity' => ($quantity - $backorder));
                            }
//							$arrayBOMovementDetails[] = array('inv_item_id'=>$itemId, 'quantity'=>$backorder);
                        } else {
                            $arrayMovementDetails[] = array('inv_item_id' => $itemId, 'quantity' => $quantity);
                        }
                    }
                }
                if (/* ($cont > 0)&& */($arrayMovementDetails != array())) {
                    $movementDocCode = $this->_generate_movement_code_id('SAL', 'inc', $idsOrdered);
//					$movementBODocCode = $this->_generate_movement_code_id('SAL','inc', $idsOrdered);
                    $arrayMovement = array('type' => 1, 'date' => $date, 'inv_warehouse_id' => $arrayWarehousesList[$i], 'inv_movement_type_id' => 2, 'description' => $description, 'code' => $movementDocCode, 'document_code' => $genericCode, 'lc_state' => 'PENDANT');
//					$arrayBOMovement = array('type'=>2, 'date'=>$date, 'inv_warehouse_id'=>$arrayWarehousesList[$i], 'inv_movement_type_id'=>2, 'description'=>$description, 'code'=>$movementBODocCode, 'document_code'=>$genericCode, 'lc_state'=>'PENDANT');
                    $data[] = array('InvMovement' => $arrayMovement, 'InvMovementDetail' => $arrayMovementDetails);
//					$data[] = array('InvMovement'=>$arrayBOMovement, 'InvMovementDetail'=>$arrayBOMovementDetails);
//				} elseif (($cont > 0)&&($arrayMovementDetails == array())) {
//					$movementBODocCode = $this->_generate_movement_code_id('SAL','inc', $idsOrdered);
//					$arrayBOMovement = array('type'=>2, 'date'=>$date, 'inv_warehouse_id'=>$arrayWarehousesList[$i], 'inv_movement_type_id'=>2, 'description'=>$description, 'code'=>$movementBODocCode, 'document_code'=>$genericCode, 'lc_state'=>'PENDANT');
//					$data[] = array('InvMovement'=>$arrayBOMovement, 'InvMovementDetail'=>$arrayBOMovementDetails);
                } else {
                    $movementDocCode = $this->_generate_movement_code_id('SAL', 'inc', $idsOrdered);
                    $arrayMovement = array('type' => 1, 'date' => $date, 'inv_warehouse_id' => $arrayWarehousesList[$i], 'inv_movement_type_id' => 2, 'description' => $description, 'code' => $movementDocCode, 'document_code' => $genericCode, 'lc_state' => 'PENDANT');
                    $data[] = array('InvMovement' => $arrayMovement, 'InvMovementDetail' => $arrayMovementDetails);
                }
                if ($movementDocCode == 'error') {
                    $error++;
                }
//				if($movementBODocCode == 'error'){$error++;}
            }
//			print_r($data);
//			$idsToDelete = array('InvMovement.id'=>$idsOrdered);

            if ($error == 0) {
                $res = $this->SalSale->saveGeneratedMovements(/* $idsToDelete, */ $data);

                switch ($res[0]) {
                    case 'SUCCESS':
                        echo 'creado|' . $res[1];
                        break;
                    case 'ERROR':
                        echo 'ERROR|onSaving';
                        break;
                }
            } else {
                echo 'ERROR|onGeneratingParameters';
            }
        }
    }

    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

    public function ajax_change_state_approved_movement_in_full() {
        if ($this->RequestHandler->isAjax()) {
            ////////////////////////////////////////////INICIO-CAPTURAR AJAX/////////////////////////////////////////////////////
            $arrayItemsDetails = $this->request->data['arrayItemsDetails'];
            $purchaseId = $this->request->data['purchaseId'];

            $this->loadModel('AdmUser');

            $date = $this->request->data['date'];
            $employee = $this->request->data['employee'];
            $taxNumber = $this->request->data['taxNumber'];
            $salesman = $this->request->data['salesman'];
            $description = $this->request->data['description'];
            $exRate = $this->request->data['exRate'];
            $discount = $this->request->data['discount'];
            $note_code = $this->request->data['note_code'];

//			$admUserId = $this->AdmUser->AdmProfile->find('list', array(
//			'fields'=>array('AdmProfile.adm_user_id'),
//			'conditions'=>array('AdmProfile.id'=>$admProfileId)
//			));
//			
//			$salesman = key($this->AdmUser->find('list', array(
//			'conditions'=>array('AdmUser.id'=>$admUserId)
//			)));

            $generalCode = $this->request->data['genericCode'];
            ////////////////////////////////////////////FIN-CAPTURAR AJAX/////////////////////////////////////////////////////
            ////////////////////////////////////////////INICIO-CREAR PARAMETROS////////////////////////////////////////////////////////
//			$arrayMovement = array('date'=>$date, 'sal_employee_id'=>$employee,'sal_tax_number_id'=>$taxNumber,'salesman_id'=>$salesman, 'description'=>$description, 'note_code'=>$note_code, 'ex_rate'=>$exRate);
//			$arrayMovement['lc_state'] = 'SINVOICE_PENDANT';
//			$arrayMovement['id'] = $purchaseId;
            $arrayNote = array('id' => $purchaseId, 'lc_state' => 'NOTE_APPROVED');
            $arrayInvoice = array('date' => $date, 'sal_employee_id' => $employee, 'sal_tax_number_id' => $taxNumber, 'salesman_id' => $salesman, 'description' => $description, 'note_code' => $note_code, 'ex_rate' => $exRate, 'discount' => $discount);
            $movementDocCode = $this->_generate_doc_code('VFA');
            $arrayInvoice['lc_state'] = 'SINVOICE_PENDANT';
//			$arrayInvoice['lc_transaction'] = 'CREATE';
            $arrayInvoice['code'] = $generalCode;
            $arrayInvoice['doc_code'] = $movementDocCode;
//			$arrayInvoice['inv_supplier_id'] = $supplier;
            //*********************************************

            $cont1 = 0;
            $cont2 = 0;
            $arrayMovement1 = array();
            $arrayMovement2 = array();
            $arrayMovementDetails1 = array();
            $arrayMovementDetails2 = array();
            for ($i = 0; $i < count($arrayItemsDetails); $i++) {
                if ($arrayItemsDetails[$i]['inv_warehouse_id'] == 1) {
                    $arrayMovementDetails1[$i]['inv_item_id'] = $arrayItemsDetails[$i]['inv_item_id'];
                    $arrayMovementDetails1[$i]['quantity'] = $arrayItemsDetails[$i]['quantity'];

                    $cont1 += 1;
                } elseif ($arrayItemsDetails[$i]['inv_warehouse_id'] == 2) {
                    $arrayMovementDetails2[$i]['inv_item_id'] = $arrayItemsDetails[$i]['inv_item_id'];
                    $arrayMovementDetails2[$i]['quantity'] = $arrayItemsDetails[$i]['quantity'];

                    $cont2 += 1;
                }
            }

            $data1 = array();
            $data2 = array();
            if ($cont1 > 0 && $cont2 == 0) {
                $arrayMovement1['date'] = $date;
                $arrayMovement1['inv_warehouse_id'] = 1;
                $arrayMovement1['inv_movement_type_id'] = 2;
                $arrayMovement1['description'] = $description;
                $arrayMovement1['document_code'] = $generalCode;
                $arrayMovement1['type'] = 1;
                $arrayMovement1['lc_state'] = 'PENDANT';
                $arrayMovement1['code'] = $this->_generate_movement_code('SAL', null);

                $data1 = array('InvMovement' => $arrayMovement1, 'InvMovementDetail' => $arrayMovementDetails1);
            } elseif ($cont2 > 0 && $cont1 == 0) {
                $arrayMovement2['date'] = $date;
                $arrayMovement2['inv_warehouse_id'] = 2;
                $arrayMovement2['inv_movement_type_id'] = 2;
                $arrayMovement2['description'] = $description;
                $arrayMovement2['document_code'] = $generalCode;
                $arrayMovement2['type'] = 1;
                $arrayMovement2['lc_state'] = 'PENDANT';
                $arrayMovement2['code'] = $this->_generate_movement_code('SAL', null);

                $data2 = array('InvMovement' => $arrayMovement2, 'InvMovementDetail' => $arrayMovementDetails2);
            } elseif ($cont1 > 0 && $cont2 > 0) {
                $arrayMovement1['date'] = $date;
                $arrayMovement1['inv_warehouse_id'] = 1;
                $arrayMovement1['inv_movement_type_id'] = 2;
                $arrayMovement1['description'] = $description;
                $arrayMovement1['document_code'] = $generalCode;
                $arrayMovement1['type'] = 1;
                $arrayMovement1['lc_state'] = 'PENDANT';
                $arrayMovement1['code'] = $this->_generate_movement_code('SAL', 'inc');

                $arrayMovement2['date'] = $date;
                $arrayMovement2['inv_warehouse_id'] = 2;
                $arrayMovement2['inv_movement_type_id'] = 2;
                $arrayMovement2['description'] = $description;
                $arrayMovement2['document_code'] = $generalCode;
                $arrayMovement2['type'] = 1;
                $arrayMovement2['lc_state'] = 'PENDANT';
                $arrayMovement2['code'] = $this->_generate_movement_code('SAL', 'inc');

                $data1 = array('InvMovement' => $arrayMovement1, 'InvMovementDetail' => $arrayMovementDetails1);
                $data2 = array('InvMovement' => $arrayMovement2, 'InvMovementDetail' => $arrayMovementDetails2);
            }

            $dataNot = array('SalSale' => $arrayNote);
            $dataInv = array('SalSale' => $arrayInvoice, 'SalDetail' => $arrayItemsDetails);


//			print_r($dataInv);
//			print_r($data1);
//			print_r($data2);
            ////////////////////////////////////////////FIN-CREAR PARAMETROS////////////////////////////////////////////////////////
            ////////////////////////////////////////////INICIO-CREAR PARAMETROS////////////////////////////////////////////////////////
            ////////////////////////////////////////////FIN-CREAR PARAMETROS////////////////////////////////////////////////////////
//			if ($data2 == array()){
//				echo "DATA2 VACIO";
//			}
            //print_r($code);
//			print_r($data2);
//			print_r($dataInv);
            ////////////////////////////////////////////INICIO-SAVE////////////////////////////////////////////////////////
//			if($purchaseId <> ''){//update
//				if($this->SalSale->SalDetail->deleteAll(array('SalDetail.sal_sale_id'=>$purchaseId))){
            $this->loadModel('InvMovement');
            if ($data2 === array()) {
                if (($this->SalSale->saveAll($dataNot)) && ($this->SalSale->saveAssociated($dataInv)) && ($this->InvMovement->saveAssociated($data1))) {
                    echo 'aprobado|first';
                }
            } elseif ($data1 === array()) {
                if (($this->SalSale->saveAll($dataNot)) && ($this->SalSale->saveAssociated($dataInv)) && ($this->InvMovement->saveAssociated($data2))) {
                    echo 'aprobado|sec';
                }
            } else {

                if (($this->SalSale->saveAll($dataNot)) && ($this->SalSale->saveAssociated($dataInv)) && ($this->InvMovement->saveAssociated($data1)) && ($this->InvMovement->saveAssociated($data2))) {
                    echo 'aprobado|both';
                }
            }
//				}$this->saveAll($dataNot)
//			}
            ////////////////////////////////////////////FIN-SAVE////////////////////////////////////////////////////////
        }
    }

    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
    //+++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
}
