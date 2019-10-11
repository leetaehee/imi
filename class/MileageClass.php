<?php
    /**
	 * 마일리지 클래스 
	 */
	Class MileageClass 
	{
		/** @var string|null $db 는 데이터베이션 커넥션 객체를 할당하기 전에 초기화 함*/
		private $db = null;
        
        /**
		 * 객체 체크 
		 *
		 * @return bool
		 */
		private function checkConnection()
		{
			if(!is_object($this->db)) {
				return false;
			}
			return true;
		}
		
		/**
         * 마일리지 타입별 합계 테이블의 컬럼명 리턴
         *
         * @param string $mileageTypeCode
         *
         * @return string
         */
		private function getMileageTypeColumn($mileageTypeCode){
			switch($mileageTypeCode){
				case 1:
					$colName = 'card_sum';
					break;
				case 2:
					$colName = 'phone_sum';
					break;
				case 3:
					$colName = 'culcture_voucher_sum';
					break;
				case 5:
					$colName = 'virtual_account_sum';
					break;
				case 7:
					$colName = 'dealings_sum';
					break;
				case 8:
					$colName = 'event_sum';
					break;
			}
			return $colName;
		}
		
        /**
		 * 데이터베이스 커넥션을 생성하는 함수 
		 *
		 * @param object $db 데이터베이스 커넥션 
		 * 
		 * @return void
		 */
		public function __construct($db) 
        {
			$this->db = $db;
		}
        
        /**
         * 마일리지 충전 폼 유효성 검증
         *
         * @param array $postData
         *
         * @return array
         */
		public function checkChargeFormValidate($postData)
		{	
			if (isset($postData['account_bank']) && empty($postData['account_bank'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금은행을 입력하세요'];
			}

			if (isset($postData['account_no']) && empty($postData['account_no'])) {
				return ['isValid'=>false, 'errorMessage'=>'계좌번호를 입력하세요'];
			}

			if (isset($postData['charge_cost']) && empty($postData['charge_cost'])) {
				return ['isValid'=>false, 'errorMessage'=>'금액을 입력하세요'];
			}

			if (isset($postData['charge_name']) && empty($postData['charge_name'])) {
				return ['isValid'=>false, 'errorMessage'=>'입금자를 입력하세요.'];
			}

			return ['isValid'=>true, 'errorMessage'=>''];
		}
        
        /**
         * 마일리지 만료 주기 가져오기
         *
         * @param int $mileageType
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getExpirationDay($mileageType, $isUseForUpdate = false)
		{
			$query = 'SELECT `expiration_day`,`period` FROM `imi_mileage` WHERE `idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $mileageType);

			if ($result == false) {
				return false;
			}

			$mileageData = [
					'period'=>$result->fields['period'],
					'day'=>$result->fields['expiration_day']
				];

			return $mileageData;
		}
        
        /**
         * 마일리지 충전
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function insertMileageCharge($param)
		{
			$query = 'INSERT INTO `imi_mileage_charge` SET
						`member_idx` = ?,
						`charge_infomation` = ?,
						`charge_account_no` = ?,
						`charge_cost` = ?,
						`spare_cost` = ?,
						`charge_name` = ?,
						`mileage_idx` = ?,
						`charge_date` = ?,
						`charge_status` = ?';
			
			if(isset($param['expirationDate'])){
				$query .= ',`expiration_date` = ?';
			}

			$result = $this->db->execute($query, $param);
			$insertId = $this->db->insert_id(); // 추가

			if ($insertId < 1) {
				return false;
			}

			return $insertId;
		}
        
        /**
         * 충전을 제외한 출금,취소 등 데이터 삽입
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function insertMileageChange($param)
		{
			$count = count($param);

			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$query = 'INSERT INTO `imi_mileage_change` SET
								`member_idx` = ?,
								`mileage_idx` = ?,
								`charge_account_no` = ?,
								`charge_infomation` = ?,
								`charge_name` = ?,
								`charge_status` = ?,
								`process_date` = ?,
								`charge_idx` = ?,
								`charge_cost` = ?
							';

					$result = $this->db->execute($query, $param[$i]);
					$insertId = $this->db->insert_id(); // 추가

					if ($insertId < 1) {
						return false;
					}
				}
			} else {
				return false;
			}

			return $insertId;
		}
        
        /**
         * 거래 마일리지 변동내역 추가 
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function insertDealingsMileageChange($param)
		{
			$count = count($param);

			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$query = 'INSERT INTO `imi_dealings_mileage_change` SET
								`dealings_idx` = ?,
								`dealings_writer_idx` = ?,
								`dealings_member_idx` = ?,
								`charge_idx` = ?,
								`dealings_status_code` = ?,
								`dealings_date` = CURDATE(),
								`dealings_money` = ?';

					$result = $this->db->execute($query, $param[$i]);
					$insertId = $this->db->insert_id(); // 추가

					if ($insertId < 1) {
						return false;
					}
				}
			} else {
				return false;
			}

			return $insertId;
		}
        
        /**
         * 가상계좌를 제외한 마일리지 충전 데이터 중 유효기간 만려된 데이터 받아오기
		 *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getMileageExcessValidDateList($isUseForUpdate = false)
		{
			$today = date('Y-m-d');
			$param = [$today];

			$query = 'SELECT `member_idx`,
							 `mileage_idx`,
							 `charge_account_no`,
							 `charge_infomation`,
							 `charge_name`,
							 `charge_status`,
							 `idx`,
							 `spare_cost`,
							 `expiration_date`
					  FROM `imi_mileage_charge`
					  WHERE `mileage_idx` <> 5
					  AND `charge_status` = 3
					  AND `expiration_date` < ?
					  ORDER BY `member_idx` ASC, `expiration_date` ASC';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}
			
			return $result;
		}
        
        /**
         * 가상 계좌로 충전된 마일리지 리스트 
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getVirutalAccountWithdrawalPossibleList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx`,
							 `charge_cost`,
							 `spare_cost`
					  FROM `imi_mileage_charge`
					  WHERE `mileage_idx` = ?
					  AND `member_idx` = ?
					  AND `charge_status` = ?
					  AND `spare_cost` > 0
					  ORDER BY `charge_date` ASC';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}
        
        /**
         * 충전된 마일리지 리스트
         *
         * @param array $param
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         * 
         * @return array/bool
         */
		public function getMileageWithdrawalPossibleList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `imc`.`idx`,
							 `imc`.`charge_cost`,
							 `imc`.`spare_cost`,
							 `imc`.`mileage_idx`
					  FROM `imi_mileage_charge` `imc`
						INNER JOIN `imi_mileage` `im`
							ON `imc`.`mileage_idx` = `im`.`idx`
					  WHERE `imc`.`member_idx` = ?
					  AND `imc`.`charge_status` = ?
					  AND `imc`.`spare_cost` > 0
					  ORDER BY `im`.`priority` ,`imc`.`expiration_date` ASC, `imc`.`charge_date` ASC';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}
        
        /**
         * 출금 시 마일리지 충전내역에서 차감해야할 정보를 배열로 리턴
         *
         * @param array $list, int $chageCost
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getMildateChargeInfomationData($list,$chargeCost)
		{
			$count = $list->recordCount();
			$remainCost = $chargeCost;

			if ($count > 0) {
				foreach ($list as $key => $value){
					$useCost = 0;
					$tmpCost = $value['spare_cost'] - $remainCost;

					if ($tmpCost < 0) {
						$useCost = $value['spare_cost'];
						$remainCost -= $value['spare_cost'];
					}else {
						$useCost += $remainCost;
						$remainCost -= $remainCost;
					}
					
					// charge_status 변경 시 호출하는 영역에서 수정할것
					$updateData[] = [
						'spare_cost'=>$useCost,
						'use_cost'=>$useCost,
						'idx'=>$value['idx']
					];

					$mileageTypeData[] = [
						'use_cost'=>$useCost,
						'idx'=>$value['idx'],
						'mileage_idx'=>$value['mileage_idx']
					];

					if ($useCost==0) {
						array_pop($updateData);
						array_pop($mileageTypeData);
					}

					if ($tmpCost > 0) {
						break;
					}
				}
			} else {
				return false;
			}
	
			return ['update_data'=>$updateData, 'mileage_type_data'=>$mileageTypeData];
		}
        
        /**
         * 두 개의 배열 내용 합치기 (출금데이터))
         *
         * @param array $chageData, array $mileageChangeParam
         *
         * @return array/bool
         */
		public function updateMileageArray($chargeData, $mileageChangeParam)
		{
			$count = count($chargeData);

			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$changeData[] = [
							'member_idx'=>$mileageChangeParam['memberIdx'],
							'mileage_idx'=>$mileageChangeParam['mileageIdx'],
							'charge_account_no'=>$mileageChangeParam['accountNo'],
							'charge_infomation'=>$mileageChangeParam['accountBank'],
							'charge_name'=>$mileageChangeParam['chargeName'],
							'charge_status'=>$mileageChangeParam['chargeStatus'],
							'process_date'=>$mileageChangeParam['process_date'],
							'charge_idx'=>$chargeData[$i]['idx'],
							'charge_cost'=>$chargeData[$i]['spare_cost'],
						];
				}
			} else {
				return false;
			}
			return $changeData;
		}

		/**
         * 두 개의 배열 내용 합치기 (출금데이터))
         *
         * @param array $chageData, array $mileageChangeParam
         *
         * @return array/bool
         */
		public function updateDealingsMileageArray($chargeData, $mileageChangeParam)
		{
			$count = count($chargeData);

			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$changeData[] = [
						'dealings_idx'=>$mileageChangeParam['dealings_idx'],
						'dealings_writer_idx'=>$mileageChangeParam['dealings_writer_idx'],
						'dealings_member_idx'=>$mileageChangeParam['dealings_member_idx'],
						'charge_idx'=>$chargeData[$i]['idx'],
						'dealings_status_code'=>$mileageChangeParam['dealings_status_code'],
						'charge_idx'=>$chargeData[$i]['idx'],
						'charge_cost'=>$chargeData[$i]['spare_cost'],
					];
				}
			} else {
				return false;
			}
			return $changeData;
		}
        
        /**
         * 출금 시 충전정보 업데이트 
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateMileageCharge($param)
		{
			$count = count($param);
			
			if($count > 0){
				for ($i = 0; $i < $count; $i++){
					$query = 'UPDATE `imi_mileage_charge` SET
							   `spare_cost` = `spare_cost` - ?,
							   `use_cost` = `use_cost` + ?
							   WHERE `idx` = ?  
							';
					
					$result = $this->db->execute($query, $param[$i]);
                    $affected_row = $this->db->affected_rows();

					if ($affected_row < 1) {
						return false;
					}
				}
			} else {
				return false;
			}
			return $affected_row;
		}
        
        /**
         * 충전내역 출력 (충전, 충전취소만)
         *
         * @param int $idx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getMileageCharge($idx, $isUseForUpdate = false)
		{
			$param = [
				'memberIdx'=> $idx,
				'is_expiration'=> 'N'
			];
		
			$query = 'SELECT `imc`.`idx`,
							 `imc`.`charge_cost`,
							 `imc`.`use_cost`,
							 `imc`.`charge_date`,
							 `imc`.`charge_infomation`,
							 `imc`.`charge_account_no`,
							 `imc`.`charge_name`,
							 `imc`.`charge_status`,
							 `code`.`mileage_name`,
							 `im`.`charge_taget_name`
					  FROM `imi_mileage_charge` `imc`
						INNER JOIN `imi_mileage_code` `code`
							ON `imc`.`charge_status` = `code`.`mileage_code`
						INNER JOIN `imi_mileage` `im`
							ON `imc`.`mileage_idx` = `im`.`idx`
					  WHERE `imc`.`member_idx` = ?
					  AND `imc`.`charge_status` IN (1,3)
					  AND `imc`.`is_expiration` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}
			
			return $result;
		}
        
        /**
         * 출금 내역 출력
         *
         * @param int $idx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getMileageWithdrawal($idx, $isUseForUpdate = false)
		{
			$param = [
                'memberIdx'=>$idx
            ];

			$query = 'SELECT `imc`.`idx`,
							 `imc`.`charge_cost`,
							 `imc`.`process_date`,
							 `imc`.`charge_account_no`,
							 `imc`.`charge_infomation`,
							 `imc`.`charge_name`,
							 `imc`.`charge_status`,
							 `code`.`mileage_name`,
							 `im`.`charge_taget_name`
					  FROM `imi_mileage_change` `imc`
						INNER JOIN `imi_mileage_code` `code`
							ON `imc`.`charge_status` = `code`.`mileage_code`
						INNER JOIN `imi_mileage` `im`
							ON `imc`.`mileage_idx` = `im`.`idx`
					  WHERE `imc`.`member_idx` = ?
					  AND `imc`.`charge_status` IN (2,4,5)';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}
			
			return $result;
		}
        
        /**
         * 마일리지 타입별 합계 테이블에 회원 데이터 있는지 조회
         *
         * @param int $idx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */ 
		public function getMemberMileageTypeIdx($idx, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx` FROM `imi_mileage_type_sum` WHERE `member_idx` = ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $idx);

			if ($result == false) {
				return false;
			}
			
			return $result->fields['idx'];
		}
        
        /**
         * 마일리지 타입별 합계 테이블에 회원, 컬럼 찾아서 마일리지 삽입 
         *
         * @param int $mileageType, array $mileageTypeParam
         *
         * @return int/bool
         */
		public function mileageTypeInsert($mileageType, $mileageTypeParam)
		{
			// 마일리지 타입에 대해서 컬럼 지정
			$colName = $this->getMileageTypeColumn($mileageType);

			$query = "INSERT INTO `imi_mileage_type_sum` SET
						`member_idx` = ?,
						`{$colName}` = `{$colName}` + ?
					";

			$result = $this->db->execute($query, $mileageTypeParam);
			$insertId = $this->db->insert_id();

			if ($insertId < 1) {
				return false;
			}

			return $insertId;
		}

		/**
         * 마일리지 타입별 합계 테이블에 회원, 컬럼 찾아서 마일리지 수정 (누적) 
         *
         * @param int $mileageType, array $mileageTypeParam
         *
         * @return int/bool
         */
		public function mileageTypeChargeUpdate($mileageType, $mileageTypeParam)
		{
			// 마일리지 타입에 대해서 컬럼 지정
			$colName = $this->getMileageTypeColumn($mileageType);

			$query = "UPDATE `imi_mileage_type_sum` SET
						`{$colName}` = `{$colName}` + ?
						WHERE `member_idx` = ?
					";

			$result = $this->db->execute($query, $mileageTypeParam);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
		
		/**
         * 마일리지 타입별 합계 테이블에 회원, 컬럼 찾아서 마일리지 수정 (감소) 
         *
         * @param int $mileageType, array $mileageTypeParam
         *
         * @return int/bool
         */
		public function mileageTypeWithdrawalUpdate($mileageType, $mileageTypeParam)
		{
			// 마일리지 타입에 대해서 컬럼 지정
			$colName = $this->getMileageTypeColumn($mileageType);
	
			$query = "UPDATE `imi_mileage_type_sum` SET
						`{$colName}` = `{$colName}` - ?
						WHERE `member_idx` = ?
					";

			$result = $this->db->execute($query, $mileageTypeParam);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}
			return $affected_row;
		}
        
        /**
         * 전체 마일리지를 가지고 계산할 경우 타입별로 감소
         *
         * @param int $purchaser_idx, array $chageData
         *
         * @return int/bool
         */
		public function mileageAllTypeWithdrawalUpdate($purchaser_idx, $chargeData)
		{
			// 차감데이터 만큼 마일리지 타입에 대해서 컬럼 지정
			$count = count($chargeData);

			if ($count > 0) {
				for ($i = 0; $i < $count; $i++) {
					$param = [
						'use_cost'=>$chargeData[$i]['use_cost'],
						'member_idx'=>$purchaser_idx
					];
	
					$colName = $this->getMileageTypeColumn($chargeData[$i]['mileage_idx']);

					$query = "UPDATE `imi_mileage_type_sum` SET
								`{$colName}` = `{$colName}` - ?
								WHERE `member_idx` = ?
							";

					$result = $this->db->execute($query, $param);
					$affected_row = $this->db->affected_rows();

					if ($affected_row < 1) {
						return false;
					}
				}
			} else {
				return false;
			}
			return $affected_row;
		}
        
        /**
         * 출금 가능한 마일리지 가져오기
         *
         * @param array $mileageTypeParam
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getAvailableMileage($mileageTypeParam, $isUseForUpdate = false)
		{
			$colName = $this->getMileageTypeColumn($mileageTypeParam['mileageType']);
			$memberIdx = $mileageTypeParam['idx'];

			$query = "SELECT `{$colName}` `{$colName}`
					  FROM `imi_mileage_type_sum`
					  WHERE `member_idx` = ?";
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $memberIdx);

			if ($result == false) {
				return false;
			}
			
			return $result->fields[$colName];
		}
        
        /**
         * 충전내역 보여주기
		 *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getChargeList($isUseForUpdate = false)
		{
			$param = ['N',1];

			$query = 'SELECT `imc`.`idx`,
							 `imc`.`member_idx`,
							 `imc`.`charge_date`,
							 `imc`.`charge_cost`,
							 `imc`.`spare_cost`,
							 `imc`.`use_cost`,
							 `imc`.`charge_infomation`,
							 `imc`.`charge_account_no`,
							 `im`.`name`,
							 `im`.`phone`,
							 `im`.`id`,
							 `imcd`.`idx` `charge_target_idx`,
							 `imcd`.`charge_taget_name`
					  FROM `imi_mileage_charge` `imc`
						INNER JOIN `imi_members` `im`
							ON `imc`.`member_idx` = `im`.`idx`
						INNER JOIN `imi_mileage` `imcd`
							ON `imc`.`mileage_idx` = `imcd`.`idx`
					  WHERE `imc`.`is_expiration` = ?
					  AND `imc`.`charge_status` <> ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}
        
        /**
         * 유효기간 만료된 충전내역 배열로 받기
         *
         * @param array $list
         *
         * @return array/bool
         */
		public function getExpirationArrayData($list)
		{
			$count = $list->recordCount();
			
			if ($count > 0) {
				foreach($list as $key => $value){
					$expirationData[] = [
							'member_idx'=>$value['member_idx'],
							'mileage_idx'=>$value['mileage_idx'],
							'charge_account_no'=>$value['charge_account_no'],
							'charge_infomation'=>$value['charge_infomation'],
							'charge_name'=>$value['charge_name'],
							'charge_status'=>4,
							'process_date'=>$value['expiration_date'],
							'charge_idx'=>$value['idx'],
							'spare_cost'=>$value['spare_cost'],
						];
				}
			} else {
				return false;
			}
			return $expirationData;
		}
        
        /**
         * 충전내역 유효기간 만료여부 수정 
         *
         * @param array $chageData
         *
         * @return int/bool
         */
		public function updateExpirationDate($chargeData)
		{
			$count = count($chargeData);

			if ($count > 0) {
				for($i = 0; $i<$count; $i++){
					
					$param[] = [
							$chargeData[$i]['spare_cost'],
							$chargeData[$i]['spare_cost'],
							$chargeData[$i]['charge_idx']
						];

					// `expiration_date` = ?, 안해도됨 
					//`is_expiration` = ?, 마지막에..
					//`charge_status` = 4 마지막에

					$query = 'UPDATE `imi_mileage_charge` SET
							   `spare_cost` = `spare_cost` - ?,
							   `use_cost` = `use_cost` + ?
							  WHERE `idx` = ?';

					$result = $this->db->execute($query, $param[$i]);
					$affected_row = $this->db->affected_rows();

					if ($affected_row < 1) {
						return false;
					}
				}
			} else {
				return false; 
			}

			return $affected_row;
		}
        
        /**
         * 현재 일자보다 유효기간이 작은 경우 충전상태, 만료여부 변경
         *
         * @param array $param
         *
         * @return int/bool
         */
		public function updateStatusByExpirationDate($param)
		{
			$query = 'UPDATE `imi_mileage_charge` SET
						`is_expiration` = ?,
						`charge_status` = ?
					  WHERE `expiration_date` < ?
					  AND `charge_status` in (3,6)';

			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}
        
        /**
         * 충전 상태 변경 
         * 
         * @param array $param
         *
         * @return int
         */
		public function updateChargeStatus($param)
		{
			$query = 'UPDATE `imi_mileage_charge` SET
					   `charge_status` = ?,
					   `spare_cost` = `spare_cost` - ?,
					   `use_cost` = `use_cost` + ?
					   WHERE `idx` = ?';
			
			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return -1;
			}
			return $affected_row;
		}
        
        /**
         * 충전내역의 사용금액이 0원인 항목에 대해서 카운트
		 *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return int/bool
         */
		public function getCountChargeSpareCountZero($isUseForUpdate = false)
		{
			$query = 'SELECT COUNT(`spare_cost`) spare_cost
					  FROM `imi_mileage_charge`
					  WHERE `spare_cost` < 1
					  AND `charge_status` <> 6';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query);

			if ($result == false) {
				return false;
			}
			
			return $result->fields['spare_cost'];
		}
        
        /**
         * 충전내역의 사용금액이 0원인 항목에 대해 일괄 처리 
         *
         * @return int/bool
         */
		public function updateChargeZeroStatus()
		{
			$query = 'UPDATE `imi_mileage_charge` SET
					   `charge_status` = 6
					   WHERE `spare_cost` = 0';
			
			$result = $this->db->execute($query);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}
			return $affected_row;
		}
        
        /**
         * imi_mileage_charge에 들어갈 내용 추출
         *
         * @param int $idx
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getChargeInsertData($idx, $isUseForUpdate = false)
		{
			$query = 'SELECT `member_idx`,
							 `mileage_idx`,
							 `charge_account_no` ,
							 `charge_infomation`,
							 `charge_name`,
							 `charge_status`,
							 `idx`,
							 `charge_cost`
					  FROM `imi_mileage_charge`
					  WHERE `idx` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $idx);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}
        
        /**
         * 모든 회원 마일리지 합계 가져오기
		 *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
         *
         * @return array/bool
         */
		public function getAllMemberMileageTotal($isUseForUpdate = false)
		{
			$today = date('Y-m-d');

			$param = [$today];

			$query = 'SELECT `member_idx`,
							  ifnull(sum(`charge_cost`),0) charge_cost
					  FROM `imi_mileage_charge`
					  WHERE `mileage_idx` <> 5
					  AND `charge_status` = 3
					  AND `expiration_date` < ?
					  GROUP BY `member_idx`
					  ORDER BY `member_idx`';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);

			if ($result == false) {
				return false;
			}
			
			return $result;
		}
        
        /**
         * 모든 회원 마일리지 유형별 합계 가져오기
		 *
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부 
         *
         * @return array/bool
         */
		public function getAllMemberPartMileageTotal($isUseForUpdate = false)
		{
			$today = date('Y-m-d');

			$param = [$today];

			$query = 'SELECT `member_idx`,
							 `mileage_idx`,
							  ifnull(sum(`charge_cost`),0) charge_cost
					  FROM `imi_mileage_charge`
					  WHERE `mileage_idx` <> 5
					  AND `expiration_date` < ?
					  AND `charge_status` = 3
                      GROUP BY `member_idx`, `mileage_idx`';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);

			if ($result == false) {
				return false;
			}
			
			return $result;
        }
	}
