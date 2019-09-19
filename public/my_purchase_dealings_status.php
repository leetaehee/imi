<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 나의 구매 등록현황 상세 화면 
	 */
	
	// 공통
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';
	
	// adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php';

	// Class 파일
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/DealingsClass.php';
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php';

	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_VOUCHER_PURCHASE_ENROLL_STATUS . ' | ' . TITLE_SITE_NAME;
		$returnUrl = SITE_DOMAIN.'/voucher_purchase_list.php'; // 리턴되는 화면 URL 초기화
		$alertMessage = '';

		$actionUrl = DEALINGS_PROCESS_ACCTION . '/dealings_process.php';
		$actionMode = 'payMileage';
		$JsTemplateUrl = JS_URL . '/my_purchase_dealings_status.js';
		$dealingsType = '판매';
		$btnName = '결제하기';

		// xss, injection 방지
		$_GET['idx'] = htmlspecialchars($_GET['idx']);
		$_GET['type'] = htmlspecialchars($_GET['type']);
		$getData = $_GET;

		$memberClass = new MemberClass($db);
		$dealingsClass = new DealingsClass($db);

		$dealingsData = $dealingsClass->getDealingsData($getData['idx']);
		if ($dealingsData === false) {
			throw new Exception('회원 구매 거래정보를 가져 올 수 없습니다! 관리자에게 문의하세요.');
		}

		// 구매자 정보 갖고오기
		$dealingsMemberIdx = $dealingsData->fields['dealings_member_idx'];
		$purchaserData = $memberClass->getMyInfomation($dealingsMemberIdx);
		if ($purchaserData === false) {
			throw new Exception('구매자 정보를 가져 올 수 없습니다.! 관리자에게 문의하세요.');
		} else {
			$purchaserDataCount = $purchaserData->recordCount();
		}

		// 이용가능한 마일맂 조회 
		$memberIdx = $_SESSION['idx'];
		$totalMileage = $memberClass->getTotalMileage($memberIdx);

		// 거래상태 변경
		$DealingsStatusChangehref = $actionUrl . '?mode=change_status&dealings_idx ='.$getData['type'];

		$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/my_purchase_dealings_status.html.php';
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	} 

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_voucher.html.php'; // 전체 레이아웃