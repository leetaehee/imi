<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원탈퇴 화면
	 */
	
	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공통함수
	 include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// 현재 세션체크
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';

    // 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_MYPAGE_DEL_MENU . ' | ' . TITLE_SITE_NAME;

    $actionUrl = MEMBER_PROCESS_ACTION . '/member_process.php'; // form action url
	$actionMode = 'del'; // 회원탈퇴
    $JsTemplateUrl = JS_URL . '/member_delete.js'; 

    $idx = $_GET['idx'];

    ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/member_delete.html.php'; // 템플릿
	$output = ob_get_clean();

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout_main.html.php'; // 전체 레이아웃