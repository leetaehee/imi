<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원가입 완료 후 보여주는 화면
	 */

	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공용함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// 현재 세션체크
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_admin_check.php';

	// 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_JOIN_COMPLETE_MENU . ' | ' . TITLE_ADMIN_SITE_NAME;
	
	ob_Start();
	// 템플릿
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/join_complete.html.php';
	$output = ob_get_clean();

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/admin/layout.html.php'; // 전체 레이아웃