<?php
	/**
	 * 이벤트 클래스
	 */
	Class EventClass 
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
		 * 이벤트 히스토리 중복 체크 
		 *
		 * @param array 이벤트 키, 회원 키, 이벤트 타입 정보
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return int/bool
		 */
		public function getIsExistEventHistoryIdx($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `idx`
					  FROM `imi_event_history`
					  WHERE `member_idx` = ?
					  AND `event_type` = ?';
			
			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}

			$result = $this->db->execute($query, $param);
			if ($result === false) {
				return false;
			}
			
			return $result->fields['idx'];
		}

		/**
		 * 이벤트 히스토리 추가
		 *
		 * @param array 이벤트 히스토리 입력 데이터
		 *
		 * @return int/bool
		 */
		public function insertEventHistory($param)
		{
			$query = 'INSERT INTO `imi_event_history` SET 
						`member_idx` = ?,
						`participate_date` = CURDATE(),
						`participate_datetime` = NOW(),
						`event_cost` = ?,
						`event_type` = ?,
						`participate_count` = ?';

			$result = $this->db->execute($query, $param);
			$inserId = $this->db->insert_id();

			if ($inserId < 1) {
				return false;
			}

			return $inserId;
		}

		/**
		 * 이벤트 히스토리 수정 
		 *
		 * @param 이벤트 키, 이벤트 금액, 참여횟수 정보
		 *
		 * @return int/bool
		 */
		public function updateEventHistory($param)
		{
			$query = 'UPDATE `imi_event_history` SET 
						`event_cost` = `event_cost` + ?,
						`participate_count` = `participate_count` + ?
						WHERE `idx` = ?';
			
			$result = $this->db->execute($query, $param);
			$affected_row = $this->db->affected_rows();

			if ($affected_row < 1) {
				return false;
			}

			return $affected_row;
		}

		/**
		 * 이벤트 히스토리 LIMIT를 통해  원하는 만큼 출력
		 *
		 * @param array 이벤트 키, 출력 수 정보
		 * @param bool $isUseForUpdate 트랜잭션 FOR UPDATE 사용여부
		 *
		 * @return array/bool
		 */
		public function getEventHistoryList($param, $isUseForUpdate = false)
		{
			$query = 'SELECT `im`.`name`,
							 `ieh`.`participate_count`,
							 `ieh`.`event_cost`,
							 `ieh`.`event_type`,
                             `ieh`.`member_idx`
					  FROM `imi_event_history` `ieh`
						INNER JOIN `imi_members` `im`
							ON `ieh`.`member_idx` = `im`.`idx`
					  WHERE `ieh`.`event_type` = ?
					  ORDER BY `ieh`.`event_cost` DESC, `ieh`.`participate_count` DESC
					  LIMIT ?';

			if ($isUseForUpdate === true) {
				$query .= ' FOR UPDATE';
			}
			
			$result = $this->db->execute($query, $param);

			if ($result === false) {
				return false;
			}
			
			return $result;
		}
	}
		