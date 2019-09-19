<form id="vitual-withdrawal-form" method="post" action="<?=$actionUrl?>">
	<input type="hidden" name="mode" value="<?=$actionMode?>">
	<input type="hidden" id="mileageType" name="mileage_type" value="<?=$mileageType?>">
	<input type="hidden" id="maxMileage" value="<?=$maxMileage?>">

	<input type="hidden" name="account_bank" value="<?=$accountBank?>">
	<input type="hidden" name="account_no" value="<?=$accountNo?>">
	<input type="hidden" name="charge_name" value="<?=$_SESSION['name']?>">
    
    <p><h3>[가상계좌 출금]</h3></p>
	<br>

	<p>
		<h3>아래계좌로 출금됩니다.</h3>
		<h4>[<?=$accountBank?>] <?=setDecrypt($accountNo)?></h4>
	</p>
	<br>

	<p>
		<h3>현재 출금 가능한 마일리지는 <?=number_format($maxMileage)?>원입니다.</h3>
		<br>
	</p>

	<p>
		<label for="chargeCost">출금금액: </label>
		<?php if(count($CONFIG_MILEAGE_ARRAY) > 0):?>
			<select id="chargeCost" name="charge_cost">
				<option value="">선택하세요.</option>
				<?php for($i=0; $i<count($CONFIG_MILEAGE_ARRAY); $i++): ?>
					<option value="<?=$CONFIG_MILEAGE_ARRAY[$i]?>">
						<?=$CONFIG_MILEAGE_ARRAY[$i];?>
					</option>
				<?php endfor; ?> 
			</select>원
		<?php else: ?>
			<p>관리자에게 문의하세요!</p>
		<?php endif; ?>
	</p>
	<br>
	
	<p>
		<input type="button" id="withdrawal-btn" value="출금하기">
	</p>
</form>
