<form id="login-form" action="<?=$action_url?>" method="post">
	<input type="hidden" name="mode" value="<?=$action_mode?>">
	<label for="user-id">아이디: </label><br>
	<input type="text" id="user-id" name="id" value=""><br>
	<label for="user-password">패스워드: </label><br>
	<input type="password" id="user-password" name="password" value=""><br>
	<input type="submit" value="로그인">
</form>