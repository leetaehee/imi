<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 환경설정(DB,Session,Cookies, 에러메세지 처리) 
	 */

	// 세션 활성화 
	session_start();

	/**
	 * 상수
	 */

	// DB 커넥션정보
	define('DB_HOST', 'localhost'); 
	define('DB_USER', 'imi');
	define('DB_NAME', 'imi');
	define('DB_PASSWORD', 'imith190819@');

	// 메세지 상수(DB)
	define('DB_CONNECTION_ERROR_MESSAGE', '데이터베이스 서버에 접속 할 수 없습니다: ');

	// 사이트 도메인 
	define('SITE_DOMAIN', 'http://imi.th-study.co.kr');
	define('SITE_ADMIN_DOMAIN', 'http://imi.th-study.co.kr/admin');

	// front-end(회원)
	define('COMMON_JS_URL', SITE_DOMAIN . '/js/common.js'); // 공통 자바스크립트
	define('JS_URL', SITE_DOMAIN . '/js'); // 자바스크립트 파일 위치
	define('CSS_URL', SITE_DOMAIN . '/css'); // CSS 파일 위치
	define('NOMALIZE_CSS_URL', SITE_DOMAIN . '/css/nomalize.css'); // nomalize.css

	// front-end(관리자)
	define('COMMON_JS_ADMIN_URL', SITE_DOMAIN . '/js/admin/common.js'); // 공통 자바스크립트
	define('JS_ADMIN_URL', SITE_DOMAIN . '/js/admin'); // 자바스크립트 파일 위치
	define('CSS_ADMIN_URL', SITE_DOMAIN . '/css/admin'); // CSS 파일 위치
	define('NOMALIZE_CSS_ADMIN_URL', SITE_DOMAIN . '/css/adminnomalize.css'); // nomalize.css
	
    // process(회원)
	define('MEMBER_PROCESS_ACTION', SITE_DOMAIN . '/process/member'); // 로그인
	define('MILEAGE_PROCESS_ACTION', SITE_DOMAIN . '/process/mileage'); // 마일리지
	define('LOGIN_PROCESS_ACTION', SITE_DOMAIN . '/process/login'); // 세션 
	define('VIRTUAL_ACCOUNT_PROCESS_ACTION', SITE_DOMAIN . '/process/virtual'); // 마일리지
	define('DEALINGS_PROCESS_ACCTION', SITE_DOMAIN . '/process/dealings'); // 거래

	// process (관리자)
	define('ADMIN_PROCESS_ACTION', SITE_DOMAIN . '/process/admin'); // 로그인
	define('COUPON_PROCEE_ACTION', SITE_DOMAIN . '/process/coupon'); // 쿠폰

	// 암호화 및 복호화 상수 
	define('ENCRYPT_TYPE', 'aes-256-cbc');
	define('ENCRYPT_KEY', 'imi_key');

	/**
	 * 전역변수
	 */ 
	$ajaxUrl = '';
	$JsTemplateUrl = '';
	$templateFileName = '404.html.php';

	/**
	 * 배열
	 */

	// 은행 종류
	$CONFIG_BANK_ARRAY = [
		'기업은행','국민은행','신한은행','외환은행','우리은행','부산은행','광주은행','우체국','카카오뱅크'
	];

	// 카드사 종류
	$CONFIG_CARD_ARRAY = [
		'삼성','BC','현대','KB국민','외환','신한','롯데','하나','NH카드'
	];
	
	// 결제 가능한 마일리지
	$CONFIG_MILEAGE_ARRAY = [
		1000,5000,10000,50000,100000
	];

	// 쿠폰 등록시 상품권 리스트 
	$CONFIG_COUPON_VOUCHER_ARRAY = [
		'해피머니상품권','도서문화상품권','문화상품권','스마트문화상품권','모든상품권'
	];

	// 쿠폰 발행 타입 
	$CONFIG_COUPON_ISSUE_TYPE = ['구매','판매'];

	// 상품권 금액
	$CONFIG_VOUCHER_MONEY_ARRAY = [
		1000,5000,10000,50000,100000
	];


	// 페이지 접근금지
	$CONFIG_PROHIBIT_ACCESS = 1;