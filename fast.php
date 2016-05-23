<?php 
	session_start();
	if(!(isset($_SESSION["username"]) && isset($_SESSION["password"])))
	{
		?>
		<script type="text/javascript">
		location.replace("login.php");
		</script>
		<noscript>
		<meta http-equiv="refresh" content="0; url=login.php">
		</noscript>
		<?
		exit();
	}
	include('header.php');
	$query = mysql_query("SELECT * FROM `board_history` WHERE DATE(`date`) = CURDATE() ORDER BY `sum_bets` DESC LIMIT 10");
	for($tableBet = array(); $row = mysql_fetch_assoc($query); $tableBet[] = $row);
	$winnerBoard = mysql_query("SELECT * FROM `winner_board` ORDER BY `date` DESC LIMIT 10");
	for($tableWin = array(); $row = mysql_fetch_assoc($winnerBoard); $tableWin[] = $row);
	
	$query = mysql_query("SELECT `firstBet` FROM `board_history` WHERE DATE(`date`) = CURDATE() ORDER BY `date` DESC LIMIT 1");
	$result = mysql_fetch_assoc($query);
	if(!empty($result)){
		$bet = $result['firstBet'];
	} else {
		$bet = 1;
	}
	if(isset($_POST["code"])){
		if(strtolower($_POST['code'])!= strtolower($_SESSION['texto']) OR !isset($_SESSION["texto"])){
			echo "<div class='error'>Неверно введен код с картинки... </div>";
		} else {
			if(!empty($_POST['bet'])) {
				if($usmem["money_rekl"] > $_POST['bet']){
					if($_POST['bet'] > $bet){
						$bets = (int)strip_tags(trim($_POST['bet']));
						$comment = strip_tags(trim($_POST['comment']));
						$countBet = mysql_query("SELECT * FROM `board_history` WHERE `id_user` = '".$usmem["id"]."' AND DATE(`date`) = CURDATE()");
						$res = mysql_fetch_assoc($countBet);
						if(!empty($res)){
							mysql_query("UPDATE `board_history` SET `firstBet`='".$bets."', `sum_bets` = `sum_bets` + '".$bets."', date=NOW() WHERE `id_user`='".$usmem["id"]."' AND DATE(`date`) = CURDATE()") or die (mysql_error());
						} else {
							mysql_query("INSERT INTO `board_history` (`id_user`, `name`, `firstBet`, `sum_bets`, `comment`, `date`) VALUES ('".$usmem["id"]."', '".$usmem["username"]."', '".$bets."', '".$bets."', '".$comment."', NOW())") or die (mysql_error());
						}
						mysql_query("UPDATE `tb_users` SET `money_rekl` = `money_rekl` - '".$bets."' WHERE `id` = '".$usmem["id"]."'");
						if(!empty($comment)) mysql_query("UPDATE `tb_users` SET `money_rekl` = `money_rekl` - 1 WHERE `id` = '".$usmem["id"]."'");
						echo refresh('/board.php','Вы добавлены на доску почёта');
						echo "<div class='succes'>Вы добавлены на доску почёта</div>";
					} else {
						echo "<div class='error'>Ваша ставка не перебивает ставку лидера</div>";
					}
				} else {
					echo "<div class='error'>На Вашем рекламном счету недостаточно средств</div>";
				}
			}
		}
		unset($_SESSION["texto"]);
	}
	?>
	<div class="align_c desc-board">
		<h2 class="h2 align_c">Размещение на доске почета</h2>
		<p style="text-align: left;'">
			Ваш аватар будет виден на всех страницах сайта, это отличная возможность заявить о себе или привлечь свободных (Всего: 12627) рефералов! Кликнув по аватару пользователи попадают на Вашу стену где узнают о вас больше и возможно присоединятся к вам. Ваш аватар будет размещен до тех пор пока его не сменит другой пользователь, а значит он может провисеть несколько минут, а может и несколько дней.
			<br><br>Сделав высокую ставку вас не смогут сместить пока не сделают равную ставку или ставку выше, но ставка каждые 5 минут понижается на одну единицу, пока не достигнет нуля
			<br><br>Стоимость размещения зависит от размера ставки (Оплата с вашего Рекламного счета.)
		</p>
		<p class="warning">
			Ежедневный конкурс
			Время проведения конкурса с 00.00 до 00.00 каждый день.
			Условия простые:
			Кто в течении суток наберёт больше всех в сумме ставок - тот и победитель.
			Денежный приз: 40% от суммы ВСЕХ ставок ВСЕХ участников в течении суток!
			Зачисление призов производится в 00.00 (Окончание конкурса)
			- По Московскому времени -
			В случае одинакового счёта (суммы ставок), побеждает тот кто сделал последнюю ставку
			(приз зачисляется на основной счёт т.е. можно вывести)
			!!! Допускается командная игра в конкурсе !!!
	   </p>
	   <p class="warning">  
			!!! ВНИМАНИЕ !!!
			Администрация не несёт никакой ответственности в потери вами средств и времени, а так же возможны изменения процента выигрыша в любой момент, если у вас есть сомнения или вы не поняли правил работы конкурса, откажитесь от участия! 
			НЕ РЕКОМЕНДУЕТСЯ ДЕЛАТЬ СТАВКИ НА ПОСЛЕДНИХ СЕКУНДАХ ...
	   </p>
		<form method="POST" action="">
		<table border="1">
			<tr>
				<td>
					<a style='cursor: pointer;' title="Обновить код" onClick="document.getElementById('captcha').src='zas/sec.php?<?=rand(0,1000);?>' + Math.random();"><img src="zas/sec.php?<?=rand(0,1000);?>" id="captcha"></a>
				</td>
				<td>
					<input type='text' maxlength='3' name='code' autocomplete='off' class='securitycode' value='' tabindex="5" />
				</td>
			</tr>
			<tr>
				<td>Укажите сумму ставки (Только целые числа, минимум 1 руб.)</td>
				<td>
					<input type='text' name='bet' placeholder='Введите сумму ставки'>
				</td>
			</tr>
			<tr>
				<td>Комментарий (Цена 1 руб)</td>
				<td><input type='text' name='comment'  maxlength='32' placeholder='Ваш комментарий'></td>
			</tr>
		</table>
		<input type="submit" value="Разместить">
		</form>

		<table border="1">
			<caption style="border: 1px solid black;">Лидеры на сегодня</caption>
			<tr>
				<th>№</th><th>Имя</th><th>Общая сумма ставок</th><th>Дата последней ставки</th>
			</tr>
			<?php if(!empty($tableBet)) {
				$c = 0;
				foreach($tableBet as $tableBets){
				$c++;
					echo	"<tr>
								<td>".$c."</td><td>".$tableBets['name']."</td><td>".$tableBets['sum_bets']."</td><td>".$tableBets['date']."</td>
						    </tr>";
				}
			} ?>
		</table>
		<table border="1">
			<caption style="border: 1px solid black;">Победители конкурсов</caption>
			<tr>
				<th>Дата конкурса</th><th>Победитель</th><th>Сумма приза</th><th>Статус</th>
			</tr>
			<?php if(!empty($tableWin)) {
				foreach($tableWin as $tableWins){
					echo	"<tr>
								<td>".$tableWins['date']."</td><td>".$tableWins['name']."</td><td>".$tableWins['sum_bets']."</td><td>".$tableWins['status']."</td>
						    </tr>";
				}
			} ?>
		</table>
	</div>
	<?php include('footer.php'); ?> 