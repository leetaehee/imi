<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원수정 화면
	 */
	
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php'; // 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php'; // 메세지
    include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php'; // 공통함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php'; // 현재 세션체크

    include_once $_SERVER['DOCUMENT_ROOT'] . '/../adodb/adodb.inc.php'; // adodb
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/adodbConnection.php'; // adodb

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../class/MemberClass.php'; // Class 파일

    // 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_MODIFY_MENU . ' | ' . TITLE_SITE_NAME;
    $returnUrl = SITE_DOMAIN.'/mypage.php'; // 리턴되는 화면 URL 초기화.

    $actionUrl = MEMBER_PROCESS_ACTION . '/member_process.php'; // form action url
    $ajaxUrl = MEMBER_PROCESS_ACTION . '/member_ajax_process.php'; // ajax url
	$actionMode = 'modi'; // 회원수정
    $JsTemplateUrl = JS_URL . '/join.js'; 

    $memberClass = new MemberClass($db);
    $idx = $_SESSION['idx'];

    $myInfomation = $memberClass->getMyInfomation($idx);

    if ($myInfomation==false) {
        alertMsg($returnUrl,1,'회원정보를 찾을 수 없습니다.');
    }

    $userId = $myInfomation->fields['id'];
    $userName = setDecrypt($myInfomation->fields['name']);
    $userEmail = setDecrypt($myInfomation->fields['email']);
    $userPhone = setDecrypt($myInfomation->fields['phone']);
    $userBirth = setDecrypt($myInfomation->fields['birth']);
    $userSex = $myInfomation->fields['sex'];

    $userSexMChecked = 'checked';
    $userSexWChecked = '';
    if ($userSex=='M') {
        $userSexMChecked = 'checked';
    } else {
        $userSexWChecked = 'checked';
    }

    ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/join.html.php'; // 템플릿
	$output = ob_get_clean();

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃