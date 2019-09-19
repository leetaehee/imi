<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 마이페이지
	 */
	
	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공통함수
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// 현재 세션체크
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';
    
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php'; // Class 파일
    
	try {
		// 템플릿에서 <title>에 보여줄 메세지 설정
		$title = TITLE_ADMIN_MEMBER_STATUS . ' | ' . TITLE_ADMIN_SITE_NAME;

		// 리턴되는 화면 URL 초기화.
		$returnUrl = SITE_ADMIN_DOMAIN.'/admin_page.php';
		$alertMessage = '';
		$idx = $_SESSION['mIdx'];
	
		$memberClass = new MemberClass($db); 
		$memberList = $memberClass->getMemberList();

		if ($memberList === false) {
			throw new Exception('회원 리스트 가져오다가 오류 발생! 관리자에게 문의하세요');
		} else {
			$templateFileName =  $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/member_status.html.php';
		}

		$rocordCount = $memberList->recordCount();
	} catch (Exception $e) {
		$alertMessage = $e->getMessage();
	} finally {
		if ($connection==true) {
			$db->close();
		}

		if (!empty($alertMessage)) {
			alertMsg($returnUrl,1,$alertMessage);
		}
	}

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout_main.html.php'; // 전체 레이아웃