<?php
////////////////////////////////////////////////////////////////////////////
// Main file for controll docs
////////////////////////////////////////////////////////////////////////////
date_default_timezone_set('Europe/Kiev'); 
function __autoload($className) {
  $className = str_replace("..", "", $className);
  require_once("classes/$className.class.php");
}

$db = new MyDB('zak.sqlite');
if(!$db){
	echo $db->lastErrorMsg();
} else {
   // echo "Opened database successfully\n";
}

if(isset($_REQUEST['exit'])) {
	//var_dump($_REQUEST['exit']);
	setcookie("id", "", time()-60*60*24*30); 
	setcookie("hash", "", time()-60*60*24*30); 
	header('Location: /'); exit();
		//$register_usr=FALSE;
} else {
	if (isset($_COOKIE['id']) and isset($_COOKIE['hash'])) {    

		$userdata = $db->query("SELECT * FROM users WHERE users_id = '".intval($_COOKIE['id'])."' LIMIT 1");
		$userdata = $userdata->fetchArray(SQLITE3_ASSOC);


		if( ( $userdata['users_hash'] !== $_COOKIE['hash'] ) or ( $userdata['users_id'] !== intval($_COOKIE['id']) ) ) 
		{ 
			setcookie('id', '', time() - 60*24*30*12, '/'); 
			setcookie('hash', '', time() - 60*24*30*12, '/');
			//setcookie('errors', '1', time() + 60*24*30*12, '/');

			$register_usr=FALSE;

			//header('Location: login.php'); exit();
		} else {
			$username = $userdata['users_login'];
			$register_usr=TRUE;
		}
	} 
	else 
	{ 
	//  setcookie('errors', '2', time() + 60*24*30*12, '/');
		$register_usr=FALSE;
	//header('Location: login.php'); exit();
	}
}

$maxvers = $db->querySingle("SELECT MAX(vers) FROM docs_attributes");
$maxdate = $db->querySingle("SELECT MAX(updtdate) FROM docs_attributes");
$md = explode("-", $maxdate);
$maxdate = $md[2].".".$md[1].".".$md[0];

?>

<!DOCTYPE html>

<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
	<meta charset="utf-8" />
	<title>Контроль редакцій</title>
	<link rel="stylesheet" href="lib/css/bootstrap.css">
	<link rel="stylesheet" href="lib/css/zak.css">

	<script src="lib/js/jquery-2.2.0.min.js"></script>
	<script src="lib/js/knockout-3.4.0.js"></script>
	<script src="lib/js/knockout-fast-foreach.min.js"></script>
	<script src="lib/js/bootstrap.min.js"></script>


</head>
<body style="margin:40px; margin-top: 10px; background-color:#eee;">




	<?php 
	if ($register_usr) {print('
<div class="modal bs-example-modal-sm" id="params" tabindex="-1" role="dialog" aria-labelledby="ModalLabel">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="exampleModalLabel"><span class="glyphicon glyphicon-cog"></span>&nbsp;&nbsp;&nbsp;&nbsp;Параметри</h4>
			</div>
			<div class="modal-body">
				<div id="loading_params" style="background-color:#FBF0DB; width: 100%; height: 100%"><span>L o a d i n g ...</span></div>  

				<div  data-bind="foreach: params"> 
					<form class="form-horizontal" id="params_form">
						<h4>Допустима затримка (днів):</h4>
						<div class="form-group">
							<div class="col-sm-4">
								<input type="number" class="form-control" name="days_i" placeholder="days_i" data-bind="value: days_i" min="0" max="15">
							</div>
							<label for="days_i" class="col-sm-8 control-label" style="text-align: left;">для важливих змін (!)</label>
						</div>
						<div class="form-group">
							<div class="col-sm-4">
								<input type="number" class="form-control" name="days_z" placeholder="days_z" data-bind="value: days_z" min="0" max="15">
							</div>
							<label for="days_z" class="col-sm-8 control-label" style="text-align: left;">для звичайних змін (z)</label>
						</div>
						<h4>Шукати:</h4>
						<div class="form-group">
							<div class="col-sm-offset-1 col-sm-11">
								<div class="checkbox">
									<label>
										<input name="in_redactions" type="checkbox" data-bind="checked: in_redactions"> по редакціям
									</label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-1 col-sm-11">
								<div class="checkbox">
									<label>
										<input name="in_history" type="checkbox" data-bind="checked: in_history"> по історії
									</label>
								</div>
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-offset-1 col-sm-11">
								<div class="checkbox">
									<label>
										<input name="in_hrefs" type="checkbox" data-bind="checked: in_hrefs"> по посиланням
									</label>
								</div>
							</div>
						</div>

					</form>
				</div>  
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Відмінити</button>
				<button type="button" class="btn btn-primary" data-bind="click: $root.SaveParams">Зберегти зміни</button>
			</div>
		</div>
	</div>
</div>
		');
} else { print ('
<!-- Modal Start here-->        
<div class="modal bs-example-modal-sm" id="loginform" tabindex="-1" role="dialog" aria-labelledby="ModalLabel">
	<div class="modal-dialog modal-sm" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				<h4 class="modal-title" id="exampleModalLabel"><span class="glyphicon glyphicon-cog"></span>&nbsp;&nbsp;&nbsp;&nbsp;Вхід</h4>
			</div>
			<div class="modal-body">
				<div>
					<div class="alert alert-danger" id="wrong_pass" style="display: none;"></div>
					<form class="form-horizontal" id="login_form">
						<div class="form-group">
							<label for="login" class="col-sm-4 control-label">Логін</label>
							<div class="col-sm-8">
								<input type="text" class="form-control" name="login" placeholder="login">
							</div>

						</div>
						<div class="form-group">
							<label for="password" class="col-sm-4 control-label">Пароль</label>
							<div class="col-sm-8">
								<input type="password" class="form-control" name="password" placeholder="password">
							</div>               
						</div>
					</form>
				</div>  
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-default" data-dismiss="modal">Відмінити</button>
				<button type="button" class="btn btn-primary" data-bind="click: $root.Login">Вхід</button>
			</div>
		</div>
	</div>
</div>

	');
}
?>

<div class="modal bs-example-modal-sm" id="myPleaseWait" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static">
	<div class="modal-dialog modal-sm">
		<div class="modal-content">
			<div class="modal-header">
				<h4 class="modal-title">
					<span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;&nbsp;Завантажую дані
				</h4>
			</div>
			<div class="modal-body">
				<div class="progress">
					<div class="progress-bar progress-bar-striped active" style="width: 100%"></div>
				</div>
			</div>
		</div>
	</div>
</div>
<!-- Modal ends Here -->

<h2 class="container text-center"><span class="glyphicon glyphicon-screenshot"></span>&nbsp;&nbsp;Контроль редакцій&nbsp;&nbsp;&nbsp;</h2>

<div class="row">
	<div class="col-md-12 text-center">
		<b>Поновлення <span style="color: red; font-size: 20px;"><?php echo $maxvers; ?></span> від <?php echo $maxdate; ?></b>
	</div>
</div>
<hr style="margin-bottom: 10px;">
<div class="row">
	<div class="col-md-9">

		<form class="form-inline">
			<div class="form-group">
				<select class="form-control" data-bind=" options: availableFil, 
							optionsText: 'text',
							optionsValue: 'value',
							value: selectedFil, 
							style: {'border-color': $root.border_color}
							"></select>
			</div>
			<div class="form-group">
				<div class="input-group">
					<input class="form-control" type="text" name="complteList" list="complteList" placeholder="Видавник... " data-bind='value: filter, valueUpdate: "input"'>
					<datalist id="complteList" data-bind="fastForEach:filters">
						<option data-bind="value: $data" />
					</datalist>
					<div class="input-group-addon btn" data-bind="click: clickFilter">X</div>
				</div>
			</div>
			<button class="btn btn-default" data-bind="click: loadDocsBtnClick">Завантажити&nbsp;</button>

			<div class="form-group">
				<span style="margin-left: 20px; font-size: 20px">Знайдено&nbsp;<b><span data-bind="text: doctmpcount" style="color: blue;"></span></b></span>
			</div>

		</form>
	</div>




<?php 
	if ($register_usr) {
		echo ("
		<div class='col-md-1 text-right'>
			<h4><span class='glyphicon glyphicon-user'></span>&nbsp;&nbsp;".$username."&nbsp;&nbsp;</h4>
		</div>
		<div class='col-md-1'>
			<form method='post'><button class='btn btn-link' type='submit' name='exit'><span class='glyphicon glyphicon-log-out'></span>&nbsp;&nbsp;Вихід</button></form>
		</div>
		<div class='col-md-1'>
			<button type='button' class='btn btn-default' data-toggle='modal' data-target='#params' data-bind='click: \$root.GetParams'><span class='glyphicon glyphicon-cog'></span></button>
		</div>");
	} else {
		echo ('
		<div class="col-md-3 text-right">
			<button type="button" class="btn btn-default" data-toggle="modal" data-target="#loginform"><span class="glyphicon glyphicon-log-in"></span>&nbsp;&nbsp;Вхід</button>
		</div>
			');}
?>       
</div>
<hr style="margin-top: 10px; margin-bottom: 10px;">

			<div id="nodocs">
				<div class="row">
					<div class="col-md-4 col-md-offset-4">
						<div class="panel panel-default" role="alert">
							<h4 class="text-center">Документів не знайдено</h4>
						</div>           
					</div>
				</div>
			</div>

			<div data-bind="if: docs">

				<!-- Template for document -->
				<div  data-bind="template: { foreach: filteredDocs }">

					<div class="panel panel-default" style="border-left-width: .25rem;" data-bind="attr: { id: 'doc'+hand_zminy.code+zcode(hand_zminy.zcode) }, style: { 'border-left-color': $root.border_color }">
						<div class="panel-body">

							<div class="row">

								<div data-bind="ifnot: document">
									<div class="col-sm-4" style="background-color:white;">
										<div class="well">
											<h4>Документ не знайдено!</h4>
											<div data-bind="if: hand_zminy">
												<p>Код: <b data-bind="text: hand_zminy.code"></b></p>
											
												<a class="btn btn-default btn-xs" href="#" data-bind="attr: {href: 'iplex://ukr/doc?code='+hand_zminy.code}">iplex</a>
												<a target="_blank" class="btn btn-default btn-xs" href="#" data-bind="attr: {href: 'http://zakon2.rada.gov.ua/laws/find/a?textl=1&bool=and&text='+hand_zminy.code}">рада</a>
											</div>
										</div>
										
									</div>
								</div>

<!--////////////////////-->
								<div data-bind="if: document">
									<div class="col-sm-4" style="background-color:white;">

										<div class="well">
											<h4 style="text-align: center;">Документ</h4>
											<p style="text-align: center;"><small>Поновлення <span class="text-primary"><strong data-bind="text: document.vers"></strong></span> від <span data-bind="text: document.updtdate"></span></small></p>

											<h5 style="text-align: center;"><strong data-bind="text: document.title"></strong></h5>
											<p>
												<dl class="dl-horizontal" style="font-size: small;">
													<dt>Видавник</dt>
													<dd><span data-bind="text: document.publish"></span></dd>
													<dt>Вид</dt>
													<dd><span data-bind="text: document.vidy"></span></dd>
													<dt>Номер</dt>
													<dd><span data-bind="text: document.numbers"></span></dd>
													<dt>Дата</dt>
													<dd><span data-bind="text: document.doc_date"></span></dd>
													<dt>Статус</dt>
													<dd><span data-bind="text: document.status"></span></dd>
													
													<!-- ko if: document.regnum -->
														<dt>Реєстр. №</dt>
														<dd><span data-bind="text: document.regnum"></span></dd>
													<!-- /ko -->
													<!-- ko if: document.regdate -->
														<dt>Реєстр. дата</dt>
														<dd><span data-bind="text: document.regdate"></span></dd>
													<!-- /ko -->
													<dt>Категорія</dt>
													<dd><span data-bind="text: document.npa"></span></dd>
													<dt>Гіперкод</dt>
													<dd><span data-bind="text: document.code"></span></dd>
													<!-- ko if: document.codeLG -->
														<dt>codeLG</dt>
														<dd><span data-bind="text: document.codeLG"></span></dd>
													<!-- /ko -->
													<dt>Uid</dt>
													<dd><span data-bind="text: document.uid"></span></dd>           
													<!-- ko if: document.gosnum -->
														<dt>Gosnum</dt>
														<dd><span data-bind="text: document.gosnum"></span></dd>
													<!-- /ko -->
													<dt>Modify date</dt>
													<dd><span data-bind="text: document.modify_date"></span></dd>
													<!-- ko if: document.public -->
													<dt>Публікація</dt>
													<dd><span data-bind="text: document.public"></span></dd>  
													<!-- /ko -->
												</dl>
											</p>
											<div>
												<a class="btn btn-default btn-xs" href="#" data-bind="attr: {href: 'iplex://ukr/doc?code='+document.code}">iplex</a>
												<a target="_blank" class="btn btn-default btn-xs" href="#" data-bind="attr: {href: 'http://zakon2.rada.gov.ua/laws/find/a?textl=1&bool=and&text='+document.code}">рада</a>
											</div>
										</div>  
									</div>
								</div>
								<div>
									<div class="col-sm-8" style="background-color:white;">
										<ul class="nav nav-tabs">
											<li class="active"><a data-toggle="tab" data-which="hand_zminy" data-bind="uniquehrefFor: $data"><h4><span class="glyphicon glyphicon-alert text-danger" ></span>  Не внесені зміни</h4></a></li>
											
											<!--<li><a data-toggle="tab" data-which="redactions" data-bind="uniquehrefFor: $data"><h4><span class="glyphicon glyphicon-th-list"></span>  Редакції</h4></a></li>
											<li><a data-toggle="tab" data-which="history" data-bind="uniquehrefFor: $data"><h4><span class="glyphicon glyphicon-book"></span>  Історія</h4></a></li>
											<li><a data-toggle="tab" data-which="hrefs" data-bind="uniquehrefFor: $data"><h4><span class="glyphicon glyphicon-link"></span>  Посилання</h4></a></li>-->
										</ul>

										<div class="tab-content" data-bind="if: hand_zminy">
																
											<div data-which="hand_zminy" data-bind="uniqueId: $data, checked: hand_zminy" class="tab-pane fade in active">
												<div>    
													<table class="table table-hover table-striped">
														<thead>
															<tr>
																<th>Змінюючий документ</th>
																<th>Позначка</th>
																<th>Додано</th>
															</tr>
														</thead>
														<tbody>
															<tr>
																<td><div data-bind="if: hand_zminy.zcode"><span data-bind="text: hand_zminy.zcode.title"></span></div>
																	<div data-bind="ifnot: hand_zminy.zcode.code">Документ відсутній в базі: <span data-bind="text: hand_zminy.zcode"></span></div>
																</td>
																<td><span data-bind="text: hand_zminy.mark"></span></td>
																<!--<td><span data-bind="text: checked"></span></td>-->
																<td><span data-bind="text: hand_zminy.updtdate"></span></td>
															</tr>
														</tbody>
													</table>
													
													<div data-bind="if: hand_zminy.zcode.code">
														<div class="row well" style="margin-right: 0px;">
															<div class="col-md-6">
																<dl class="dl-horizontal" style="font-size: small;">
																	<dt>Видавник</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.publish"></span></dd>
																	<dt>Вид</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.vidy"></span></dd>
																	<dt>Номер</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.numbers"></span></dd>
																	<dt>Дата</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.doc_date"></span></dd>
																	<dt>Статус</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.status"></span></dd>
																	<!-- ko if: hand_zminy.zcode.regnum -->
																	<dt>Реєстр. №</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.regnum"></span></dd>
																	<!-- /ko -->
																	<!-- ko if: hand_zminy.zcode.regdate -->
																	<dt>Реєстр. дата</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.regdate"></span></dd>
																	<!-- /ko -->
																	<!-- ko if: hand_zminy.zcode.npa -->
																	<dt>Категорія</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.npa"></span></dd>
																	<!-- /ko -->																	
																</dl>
															</div>
															<div class="col-md-6">
																<dl class="dl-horizontal" style="font-size: small;">
																	<dt>Гіперкод</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.code"></span></dd>
																	<!-- ko if: hand_zminy.zcode.codeLG -->
																	<dt>codeLG</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.codeLG"></span></dd>
																	<!-- /ko -->
																	<dt>Uid</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.uid"></span></dd>           
																	<!-- ko if: hand_zminy.zcode.gosnum -->
																	<dt>Gosnum</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.gosnum"></span></dd>  
																	<!-- /ko -->         
																	<dt>Modify date</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.modify_date"></span></dd>
																	<!-- ko if: hand_zminy.zcode.public -->
																	<dt>Публікація</dt>
																	<dd><span data-bind="text: hand_zminy.zcode.public"></span></dd>  
																	<!-- /ko -->
																</dl>
															</div>
														</div>						
													</div>
													<div  class="col-md-10 text-left" data-bind="attr: { id: hand_zminy.code+hand_zminy.zcode.code}">
														<?php if ($register_usr) { ?>

														<!-- ko ifnot: hand_zminy.checked=='+' -->
															<button class="btn btn-danger" data-bind="click: function(data) { $root.setBtnClick.call(this, 'delete', hand_zminy.code, hand_zminy.zcode.code) }">Видалити</button>
														<!-- /ko -->
																			
														<!-- ko ifnot: hand_zminy.checked=='p' -->
															<button class="btn btn-warning" data-bind="click: function(data) { $root.setBtnClick.call(this, 'postpone', hand_zminy.code, hand_zminy.zcode) }">Відкласти</button>
														<!-- /ko -->

														<!-- ko ifnot: hand_zminy.checked=='f' -->
															<button class="btn btn-primary" data-bind="click: function(data) { $root.setBtnClick.call(this, 'future', hand_zminy.code, hand_zminy.zcode) }">На майбутнє</button>
														<!-- /ko -->

														<!-- ko ifnot: hand_zminy.checked=='' -->
															<button class="btn btn-success" data-bind="click: function(data) { $root.setBtnClick.call(this, 'uncheck', hand_zminy.code, hand_zminy.zcode) }">В поточні</button>
														<!-- /ko -->

														<!-- ko ifnot: hand_zminy.checked=='np' -->
															<button class="btn btn-info" data-bind="click: function(data) { $root.setBtnClick.call(this, 'nopublik', hand_zminy.code, hand_zminy.zcode) }">В не опубліковані</button>
														<!-- /ko -->

														<?php } ?>

																		<?php 
																		/*
																		if ($register_usr) {
																			print ('
																				<div data-bind="if: hand_zminy.zcode.code">
																					<div data-bind="attr: { id: hand_zminy.code+hand_zminy.zcode.code}">  
																						<div data-bind="ifnot: hand_zminy.checked==\'+\'">
																							<button class="btn btn-primary" data-bind="click: function(data) { $root.toggleChecked.call(this, hand_zminy.code, hand_zminy.zcode.code) }">Так</button>
																							<span class="hidden"></span>
																						</div>
																					</div>
																					<div data-bind="if: hand_zminy.checked==\'+\'">
																						<button class="btn btn-warning" data-bind="click: function(data) { $root.toggleChecked.call(this, hand_zminy.code, hand_zminy.zcode.code) }">Не так</button>
																						<span class="hidden"></span>
																					</div>
																				</div>
																				<div data-bind="ifnot: hand_zminy.zcode.code">
																					<div data-bind="attr: { id: hand_zminy.code+hand_zminy.zcode.code}">
																						<div data-bind="ifnot: hand_zminy.zcode.code">
																							<div data-bind="ifnot: hand_zminy.checked==\'+\'">
																								<button class="btn btn-primary" data-bind="click: function(data) { $root.toggleChecked.call(this, hand_zminy.code, hand_zminy.zcode) }">Так</button>
																								<span class="hidden"></span>
																							</div>
																						</div>
																					</div>
																				</div>
																				
																				');
																		}
																		*/
																		?>                                 
													</div>
													<div class="col-md-2 text-right">
														<div data-bind="if: hand_zminy.zcode.code">													
															<a class="btn btn-default btn-xs" href="#" data-bind="attr: {href: 'iplex://ukr/doc?code='+hand_zminy.zcode.code}">iplex</a>
															<a target="_blank" class="btn btn-default btn-xs" href="#" data-bind="attr: {href: 'http://zakon2.rada.gov.ua/laws/find/a?textl=1&bool=and&text='+hand_zminy.zcode.code}">рада</a>
														</div>
														<div data-bind="ifnot: hand_zminy.zcode.code">				
															<a class="btn btn-default btn-xs" href="#" data-bind="attr: {href: 'iplex://ukr/doc?code='+hand_zminy.zcode}">iplex</a>
															<a target="_blank" class="btn btn-default btn-xs" href="#" data-bind="attr: {href: 'http://zakon2.rada.gov.ua/laws/find/a?textl=1&bool=and&text='+hand_zminy.zcode}">рада</a>
														</div>
													</div>

												</div>
											</div>
<!--
											<div data-which="redactions" data-bind="uniqueId: $data, checked: redactions" class="tab-pane fade">
												<table class="table table-hover table-striped">
													<thead>
														<tr>
															<th>uid</th>
															<th>Дата</th>
															<th>Дія</th>
															<th>Змінюючий документ</th>
															<th>Дата набрання чинності</th>
														</tr>
													</thead>
													<tbody data-bind="foreach: redactions">
														<tr>
															<td><span data-bind="text: uid"></span></td>
															<td><span data-bind="text: zdate"></span></td>
															<td><span data-bind="text: title"></span></td>
															<td><span data-bind="text: zcode"></span></td>
															<td><span data-bind="text: zfrom"></span></td>
														</tr>
													</tbody>
												</table>
											</div>

											<div data-which="history" data-bind="uniqueId: $data, checked: history" class="tab-pane fade">
												<table class="table table-hover table-striped">
													<thead>
														<tr>
															<th>Дата</th>
															<th>Дія</th>
															<th>Змінюючий документ</th>
														</tr>
													</thead>
													<tbody data-bind="foreach: history">
														<tr>
															<td><div data-bind="ifnot: his_date" ><p class="text-danger">не визначено</p></div><span data-bind="text: his_date"></span></td>
															<td><span data-bind="text: his_title"></span></td>
															<td><span data-bind="text: his_code"></span></td>
														</tr>
													</tbody>
												</table>
											</div>

											<div data-which="hrefs" data-bind="uniqueId: $data, checked: hrefs" class="tab-pane fade">
												<table class="table table-hover table-striped">
													<thead>
														<tr>
															<th>Документ</th>
														</tr>
													</thead>
													<tbody data-bind="foreach: hrefs">
														<tr>
															<td><span data-bind="text: hrefs_code"></span></td>
														</tr>
													</tbody>
												</table>
											</div>
-->

										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
	<!-- END template for document -->
			</div>

		<a href="#" id="toTop"  class="btn btn-default btn-lg" role="button"><span class="glyphicon glyphicon-chevron-up"></span></a>


<script type="text/javascript">
	//$(document).ready(function() {
		
		ko.bindingHandlers.uniqueId = {
			 /*
			 data-bind="uniqueId: $data" to stick a new id on $data and
			use it as the html id of the element. 

			data-which="foo" (optional) adds foo to the id, to separate
			 it from other ids made from this same $data.
			 */
			 counter: 0,
			 _ensureId: function (value, element) {

				if (value.id === undefined) {
					value.id = "elem" + (++ko.bindingHandlers.uniqueId.counter);
				}

				var id = value.id, which = element.getAttribute("data-which");
				if (which) {
					id += "-" + which;
				}
				return id;
			 },
			 init: function(element, valueAccessor) {
				var value = valueAccessor();
				element.id = ko.bindingHandlers.uniqueId._ensureId(value, element);
			 },
			};

			ko.bindingHandlers.uniquehrefFor = {
			/*
			data-bind="uniqueFor: $data" works like uniqueId above, and
			adds a for="the-new-id" attr to this element.

			data-which="foo" (optional) works like it does with uniqueId.
			*/
			init: function(element, valueAccessor) {
				element.setAttribute("href", "#"+ko.bindingHandlers.uniqueId._ensureId(valueAccessor(), element));
			} 
		};

		ko.bindingHandlers.uniquedatatargetFor = {
			init: function(element, valueAccessor) {
				element.setAttribute(
					"data-target", "#"+ko.bindingHandlers.uniqueId._ensureId(valueAccessor(), element));
			} 
		};


		function Fil(text, value){
			this.text = text;
			this.value = value;
		}

		function zcode(zcode){
			if ( $.isPlainObject(zcode) )
				zcode = zcode.code;
			return zcode;
		}
		

		function dataModel() {
			var self = this;

			self.params = ko.observableArray([]);
			self.loading = ko.observable(true);
			self.docs = ko.observableArray();
			self.maxvers = ko.observable();
			self.doctmpcount = ko.observable();
			self.filter = ko.observable('');
			self.filter2 = ko.observable('');	
			//self.filters = ko.observableArray(["Всі", "Верховна Рада України", "Кабінет Міністрів", "Президент України", "Зареєстровано в МЮ", "інші", "пусті"]);	
			self.border_color = ko.observable("#ddd");

			self.completeList = ko.observableArray(["всі", "пусті", "інші", "Зареєстровано в МЮ"]);
			
			self.filters = ko.computed(function() {
				return ko.utils.arrayGetDistinctValues(self.completeList()).sort();
			});
			
			self.availableFil = ko.observableArray([
				new Fil('Поточні',''),
				new Fil('Відкладені','p'), 
				new Fil('Майбутні','f'),
				new Fil('Не опубліковані','np'),
				//new Fil('Видалені','d')
				]);
			self.selectedFil = ko.observableArray();
			
			self.loadDocsBtnClick = function(){
		      self.GetDocs(self.selectedFil()); 
		    }
			
			self.clickFilter = function(){
				self.filter("");
			}
			
			self.zcode = function(zcode){
				if ( $.isPlainObject(zcode) ){
					zcode = zcode.code;
				}
				return zcode;
			};
			
			// Filtered docs
			self.filteredDocs = ko.computed(function() {
				var filter = self.filter();
				var filter2 = self.filter2();
				var tt;
				
				if (!filter || filter == "всі") {
					self.doctmpcount(self.docs().length);
					return self.docs();
				} else if (filter == "пусті") {
					tt = ko.utils.arrayFilter(self.docs(), function(item) { 
						return ( item.document == null );
					});
				} else if (filter == "інші"){
					tt = ko.utils.arrayFilter(self.docs(), function(item) {
						var res = true;
						if (item.document == null || item.document.regnum != null) {
							res = false;
						} else {
							for (var i = 0, j = self.filters().length; i < j; i++){
								if  (item.hand_zminy.zcode.publish == null || item.hand_zminy.zcode.publish.indexOf(self.filters()[i]) > -1)
									 {									
									res = false;
									break;
								}
							}
						}						
						return res;
					});
				} else if (filter == "Зареєстровано в МЮ") {
					tt = ko.utils.arrayFilter(self.docs(), function(item) { 
						if (item.hand_zminy != null && item.hand_zminy.zcode.regnum != null) {
							return true;
						}
						return false;
					});
				} else {
					var tt = ko.utils.arrayFilter(self.docs(), function(item) { 
						if (item.hand_zminy != null && item.hand_zminy.zcode.publish != null) {
							return (item.hand_zminy.zcode.publish.toLowerCase().indexOf(filter.toLowerCase()) > -1);
						}
					});
					
				}
				self.doctmpcount(tt.length);
				return tt;
			});
			
			// Load docs from server
			self.GetDocs = function(fil) {
				switch(fil){
					case '+':
						border = "#d43f3a";
						break;
					case 'p':
						border = "#eea236";
						break;
					case 'f':
						border = "#2e6da4";
						break;
					case '':
						border = "#4cae4c";
						break;
					case 'np':
						border = "#46b8da";
						break;

					default:
					border = "#ddd";
				}
				self.border_color(border);

				$.ajax({
					beforeSend:function(){

						$('#nodocs').hide();
						$('#myPleaseWait').modal('show');
						
						self.docs.removeAll();
						//alert( filters_default );
						//self.completeList.removeAll();
						self.completeList(["всі", "пусті", "інші", "Зареєстровано в МЮ"]);

					},
					complete:function(){
						$('#myPleaseWait').modal('hide');
						
					},
					data: 'fil='+fil,
					dataType: "json",
					url: "checkzminy.php",
					type: "GET",
					success: function (data) {					
						self.docs(data.docs);
						self.maxvers(data.maxvers);
						var n = data.docs.length; 
						//$('#count_docs').text(n);
						for (var j = 0; j < n+0; j++) {
							if (data.docs[j].hand_zminy != null && data.docs[j].hand_zminy.zcode.publish != null){
								self.completeList.push(data.docs[j].hand_zminy.zcode.publish.toLowerCase());
							}
						}
					},
					error: function () {
						$('#myPleaseWait').modal('hide');
						$('#nodocs').show();
					}
				});
			}
			self.GetDocs('');
						
			// Get parameters from server
			self.GetParams = function() {
				$.ajax({            
					beforeSend:function(){
						$("#loading_params").show();
					},
					complete:function(){
						$("#loading_params").hide();
					},
					dataType: "json",
					url: "paramsget.php",
					type: "GET",

					success: function (par) {
						self.params(par.params);                  
					}
				});
			}

			// Save parameters to server
			self.SaveParams = function() {
				$.ajax({            
					url: "paramsset.php",
					type: "POST",
					data: $('#params_form').serialize(),
					success: function (par) {
						if (par=='ok') {
							//alert('Параметри збережено');
						}   
						$('#params').modal('hide'); 
						window.location.reload(true);           
					}
				});            
			}

			// Login user 
			self.Login = function() {
				$.ajax({
					url: "login.php",
					type:"POST",
					data: $('#login_form').serialize(),
					success: function (par) {
						if (par === "OK") {
							$('#login_form').modal('hide');
							window.location.reload(true);
						} else {
							$('#wrong_pass').html(par).show();
							//alert(par); 
						}                                           
					}
				});
			}

			// Buttons actions
			self.setBtnClick = function(mode, code, zcode) {
				zcode = self.zcode(zcode);
				switch(mode){
					case 'delete':
						msg = "Видалити зі списку та помітити зробленим?";
						break;
					case 'postpone':
						msg = "Відкласти?";
						break;
					case 'future':
						msg = "На майбутнє?";
						break;
					case 'uncheck':
						msg = "Повернути в поточні?";
						break;
					case 'nopublik':
						msg = "Додати в не опубліковані?";
						break;

					default:
					msg = "?";
				}
				var r = confirm(msg);
				if (r == true) {
					//alert(mode);
					//alert(code);
					//alert(zcode);
					$.ajax({
						type: "POST",
						url: "dochecked.php",
						data: 'mode='+mode+'&code='+code+'&zcode='+zcode,
						success : function(text){
							//var idteg = "#"+code+zcode;
							//$( idteg+" > div > span").text( text );
							//$( idteg+" > div > button,"+idteg+" > div > span").toggleClass( "hidden" );
							self.docs.remove( function (item) { return (item.hand_zminy.code===code && self.zcode(item.hand_zminy.zcode)===zcode); } );
						}
					});
				} 
			}
		}

		ko.applyBindings(new dataModel()); 

		
		// scroll to top
		$(function(){
			$.fn.scrollToTop=function(){
				$(this).hide().removeAttr("href");
				if($(window).scrollTop()!="0"){
					$(this).fadeIn("slow")
				}
				var scrollDiv=$(this);
				$(window).scroll(function(){
					if($(window).scrollTop()=="0"){
						$(scrollDiv).fadeOut("slow")
					}else{
						$(scrollDiv).fadeIn("slow")
					}
				});
				$(this).click(function(){
					$("html, body").animate({scrollTop:0},"fast")
				})
			}
		});
		$(function() {
			$("#toTop").scrollToTop();
		});

	
	// });
</script>


</body>
</html>



