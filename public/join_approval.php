<?php
	/*
	 *  @author: LeeTaeHee
	 *	@brief: 회원가입 메일 승인 화면 
	 */

	include __DIR__.'/../configs/config.php'; // 환경설정
	include __DIR__.'/../messages/message.php'; // 메세지

	try {
        include __DIR__.'/../includes/databaseConnection.php'; // PDO 객체 생성
		$title = TITLE_JOIN_APPROVAL.' | '.TITLE_SITE_NAME; // 템플릿에서 <title>에 보여줄 메세지 설정
		
		if(isset($_GET['idx'])){
			// 한번 승인 한 경우 로그인 페이지로 이동.
			$stmt = $pdo->prepare('
						SELECT `join_approval_date` 
						FROM `imi_members` 
						WHERE `idx` = :idx
					');
			$stmt->bindValue(':idx',$_GET['idx']);
			$stmt->execute();
			$idResult = $stmt->fetch();	

			if($idResult['join_approval_date'] != null){
				// 메인승인한 경우 로그인페이지로 이동
				header('location: ./login.php');
			}

			// 승인일자에 현재일자로 업데이트 하여 정상적인 활동이 가능하도록 한다.
			$stmt = $pdo->prepare('
							update `imi_members`
							 set `join_approval_date` = CURDATE()
							 where `idx` = :idx
						');
			$stmt->bindValue(':idx',$_GET['idx']);
			$stmt->execute();
		}
		
		ob_Start();
		include __DIR__.'/../templates/join_approval.html.php'; // 템플릿
		$output = ob_get_clean();
	}catch(Exception $e) {
		$output = DB_CONNECTION_ERROR_MESSAGE.$e->getMessage().', 위치: '.$e->getFile().':'.$e->getLine();
	}

	include __DIR__ .'/../templates/layout.html.php'; // 전체 레이아웃
