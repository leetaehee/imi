<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 메인화면 
	 */

	// 환경설정
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../configs/config.php';
	// 메세지
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../messages/message.php';
	// 공용함수
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/function.php';
	// 현재 세션체크
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../includes/session_check.php';

	// 템플릿에서 <title>에 보여줄 메세지 설정
	$title = TITLE_SITE_MAIN . ' | ' . TITLE_SITE_NAME;

	ob_Start();
	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/mypage_home.html.php'; // 템플릿
	$output = ob_get_clean();

	include_once $_SERVER['DOCUMENT_ROOT'] . '/../templates/layout.html.php'; // 전체 레이아웃