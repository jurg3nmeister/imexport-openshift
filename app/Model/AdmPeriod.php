<?php
App::uses('AppModel', 'Model');
/**
 * AdmPeriod Model
 *
 */
class AdmPeriod extends AppModel {

/**
 * Display field
 *
 * @var string
 */
	public $displayField = 'name';

/**
 * Validation rules
 *
 * @var array
 */
	public $validate = array(
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
	);
	
	
	
	public function saveNewPeriod($lastPeriod, $newPeriod, $creator){
		$dataSource = $this->getDataSource();
		$dataSource->begin();
		////////////////////////////////////////////////
//		$error = 0;
		if($this->save(array('name'=>$newPeriod, 'creator'=> $creator))){
			ClassRegistry::init('AdmArea');
			$AdmArea = new AdmArea();

            $dataAreaUserRestriction = $AdmArea->find('all', array(
                'conditions'=>array('AdmArea.period'=>$lastPeriod),
                'fields'=>array('AdmArea.name', 'AdmArea.parent_area', 'AdmArea.period', 'AdmArea.id'
                ),
                'recursive'=>-1
            ));

			for($i=0; $i < count($dataAreaUserRestriction); $i++){

                $dataAreaUserRestriction[$i]['AdmArea']['creator']=$creator;
                $dataAreaUserRestriction[$i]['AdmArea']['period']=$newPeriod;
                ClassRegistry::init('AdmUserRestriction');
                $AdmUserRestriction = new AdmUserRestriction();
                $dataAreaUserRestriction[$i]['AdmUserRestriction'] = $AdmUserRestriction->find('all', array(
                    'conditions'=>array('AdmUserRestriction.lc_state !='=>'LOGIC_DELETED', 'AdmUserRestriction.adm_area_id'=>$dataAreaUserRestriction[$i]['AdmArea']['id']),
                    'recursive'=>-1,
                    'fields'=>array('AdmUserRestriction.adm_user_id', 'AdmUserRestriction.adm_role_id', 'AdmUserRestriction.selected', 'AdmUserRestriction.active', 'AdmUserRestriction.active_date', 'AdmUserRestriction.creator', 'AdmUserRestriction.period')
                ));

                for($j=0; $j < count($dataAreaUserRestriction[$i]['AdmUserRestriction']); $j++){
                    $dataAreaUserRestriction[$i]['AdmUserRestriction'][$j] = $dataAreaUserRestriction[$i]['AdmUserRestriction'][$j]['AdmUserRestriction']; //format
                    $dataAreaUserRestriction[$i]['AdmUserRestriction'][$j]['creator'] = $creator;
                    $dataAreaUserRestriction[$i]['AdmUserRestriction'][$j]['selected'] = 0;
                    $dataAreaUserRestriction[$i]['AdmUserRestriction'][$j]['active_date'] = ($newPeriod+1).'-01-01 00:00:00';
                    $dataAreaUserRestriction[$i]['AdmUserRestriction'][$j]['period'] = $newPeriod;
                }
                unset($dataAreaUserRestriction[$i]['AdmArea']['id']);
            ///////////////////////SAVE
				try{
					$AdmArea->saveAssociated($dataAreaUserRestriction[$i], array('deep' => true, 'atomic'=>false));
				}catch(Exception $e){
//					debug($e);
					$dataSource->rollback();
					return false;
//					$error++;
				}
			}
			
			$dataSource->commit();
			return true;
		}
		///////////////////////////////////////////////
		
	}
	
	
	
//END MODEL	
}
