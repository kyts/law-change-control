<?php
function __autoload( $className ) {
  $className = str_replace( "..", "", $className );
  require_once( "classes/$className.class.php" );
}
$db = new MyDB('zak.sqlite');
if(!$db){
   echo $db->lastErrorMsg();
} else {
   // echo "Opened database successfully\n";
  if(isset($_REQUEST['exit'])) {
	//var_dump($_REQUEST['exit']);
	setcookie("id", "", time()-60*60*24*30); 
        setcookie("hash", "", time()-60*60*24*30); 
        header('Location: /zak/'); exit();
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
    } else { 
  	//setcookie('errors', '2', time() + 60*24*30*12, '/');
  	$register_usr=FALSE;
  	//header('Location: login.php'); exit();
	}
  }
  $info = $db->query("SELECT MAX(vers) AS maxvers FROM docs_attributes");
  if($info){
    $info_maxvers = $info->fetchArray(SQLITE3_ASSOC);
    $maxvers = $info_maxvers['maxvers'];
  }
?>
<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta charset="utf-8" />
    <title>Контроль редакцій</title>
    <link rel="stylesheet" href="css/bootstrap.css">
    <link rel="stylesheet" href="css/zak.css">

    <script src="js/jquery-2.2.0.min.js"></script>
    <script src="js/knockout-3.4.0.js"></script>
    <script src="js/bootstrap.min.js"></script>
</head>

<body style="margin:40px;background-color:#ddd;">
<?php 
if ($register_usr) {print('
<!-- Modal Start here-->        
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
<div class="modal bs-example-modal-sm" id="loginform" tabindex="-1" role="dialog" aria-labelledby="ModalLabel">
  <div class="modal-dialog modal-sm" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title" id="exampleModalLabel"><span class="glyphicon glyphicon-cog"></span>&nbsp;&nbsp;&nbsp;&nbsp;Вхід</h4>
      </div>
      <div class="modal-body">
      
      <div><div class="alert alert-danger" id="wrong_pass" style="display: none;"></div>
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
<div class="modal bs-example-modal-sm" id="myPleaseWait" tabindex="-1"
    role="dialog" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    <span class="glyphicon glyphicon-time"></span>&nbsp;&nbsp;&nbsp;Завантажую
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

<div class="row" style="display: flex; align-items: center;">
    <div class="col-md-5"><h4><span class="glyphicon glyphicon-tasks"></span>&nbsp;&nbsp;Контроль редакцій&nbsp;&nbsp;&nbsp;<span class="label label-primary"><span class="glyphicon glyphicon-refresh"></span>&nbsp;&nbsp;<?php print($maxvers); ?></span></h4></div><div class="col-md-2"><h4 class="text-center" id="count_docs"></h4></div>
<?php 
if ($register_usr) {
	echo ("<div class='col-md-3 text-right'><h4><span class='glyphicon glyphicon-user'></span>&nbsp;&nbsp;".$username."&nbsp;&nbsp;</h4></div>
        <div class='col-md-1'><form method='post'><button class='btn btn-link' type='submit' name='exit'><span class='glyphicon glyphicon-log-out'></span>&nbsp;&nbsp;Вихід</button></form>
		</div>
		<div class='col-md-1'>
    	<button type='button' class='btn btn-default' data-toggle='modal' data-target='#params' data-bind='click: \$root.GetParams'><span class='glyphicon glyphicon-cog'></span></button></div>");
} else {
	echo ('<div class="col-md-5 text-right">
  		<button type="button" class="btn btn-default" data-toggle="modal" data-target="#loginform"><span class="glyphicon glyphicon-log-in"></span>&nbsp;&nbsp;Вхід</button>
');}
?>       
    </div>
</div>

<form id="formx" data-bind="submit: $root.toggleChecked">
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
<!-- Template fo document -->
<div  data-bind="template: { foreach: docs }">
    <div class="panel panel-default">
    <div class="panel-body">
    <div class="row">
    <div class="col-sm-4" style="background-color:white;" data-bind="foreach: document">
    <div class="well">
        <h4 style="text-align: center;">Документ</h4>
        <p style="text-align: center;"><small>Поновлення <span class="text-primary"><strong data-bind="text: vers"></strong></span> від <span data-bind="text: updtdate"></span></small></p>
        <h5 style="text-align: center;"><strong data-bind="text: title"></strong></h5>
        <p>
	    <dl class="dl-horizontal" style="font-size: small;">
            <dt>Видавник</dt>
                <dd><span data-bind="text: publish"></span></dd>
            <dt>Вид</dt>
                <dd><span data-bind="text: vidy"></span></dd>
            <dt>Номер</dt>
                <dd><span data-bind="text: numbers"></span></dd>
            <dt>Дата</dt>
                <dd><span data-bind="text: doc_date"></span></dd>
            <dt>Статус</dt>
                <dd><span data-bind="text: status"></span></dd>
            <div data-bind="if: regnum">
            <dt>Реєстр. №</dt>
                <dd><span data-bind="text: regnum"></span></dd></div>
            <div data-bind="if: regdate">
            <dt>Реєстр. дата</dt>
                <dd><span data-bind="text: regdate"></span></dd></div>
            <dt>Категорія</dt>
                <dd><span data-bind="text: npa"></span></dd>
            <!--<p class="text-center"><button class="btn btn-link" data-toggle="collapse" data-which="morein" data-bind="uniquedatatargetFor: $data">більше...</button></p>
            <div data-which="more" data-bind="uniqueId: $data, checked: morein" class="collapse">
            </div>-->
            <dt>Гіперкод</dt>
                <dd><span data-bind="text: code"></span></dd>
            <div data-bind="if: codeLG">
            <dt>codeLG</dt>
                <dd><span data-bind="text: codeLG"></span></dd></div>
            <dt>Uid</dt>
                <dd><span data-bind="text: uid"></span></dd>           
            <div data-bind="if: gosnum">
            <dt>Gosnum</dt>
                <dd><span data-bind="text: gosnum"></span></dd> </div>           
            <dt>Modify date</dt>
                <dd><span data-bind="text: modify_date"></span></dd>
        </dl>
	</p>
        </div>  
    </div>

    <div class="col-sm-8" style="background-color:white;">
    <ul class="nav nav-tabs">
        <li><a data-toggle="tab" data-which="redactions" data-bind="uniquehrefFor: $data"><h4><span class="glyphicon glyphicon-th-list"></span>  Редакції</h4></a></li>
        <li><a data-toggle="tab" data-which="history" data-bind="uniquehrefFor: $data"><h4><span class="glyphicon glyphicon-book"></span>  Історія</h4></a></li>
        <li><a data-toggle="tab" data-which="hrefs" data-bind="uniquehrefFor: $data"><h4><span class="glyphicon glyphicon-link"></span>  Посилання</h4></a></li>
        <li class="active"><a data-toggle="tab" data-which="hand_zminy" data-bind="uniquehrefFor: $data"><h4><span class="glyphicon glyphicon-alert text-danger" ></span>  Не внесені зміни</h4></a></li>
    </ul>

    <div class="tab-content">
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
        <div data-which="hand_zminy" data-bind="uniqueId: $data, checked: hand_zminy" class="tab-pane fade in active">
        <div data-bind="foreach: hand_zminy">    
            <table class="table table-hover table-striped">
            <thead>
                    <tr>
                    <!--<th>Документ</th>
                    <th>hz_code</th>
                    <th>hz_zcode</th>-->
                    <th>Перевірено</th>
                    <th>Змінюючий документ</th>
                    <th>Позначка</th>
                    <!--<th>checked</th>-->
                    <th>Додано</th>
                    </tr>
            </thead>
            
            <tbody>
                <tr>
                    <!--<td><span data-bind="text: code"></span></td>
                    <td><span data-bind="text: hz_code"></span></td>
                    <td><span data-bind="text: hz_zcode"></span></td>-->
                    <td>
  			<div data-bind="attr: { id: code }">	  								
<?php 
if ($register_usr) {
	print ('
            <div data-bind="ifnot: checked==\'+\'">
				<button class="btn btn-primary" data-bind="click: $root.toggleChecked.bind(code)">Так</button>
                <span class="hidden">OK</span>
            </div>
            <div data-bind="if: checked==\'+\'">
                <button class="btn btn-warning" data-bind="click: $root.toggleChecked.bind(code)">Не так</button>
                <span class="hidden">OK</span>
            </div>
		');
}
?>                                 
  			</div>	
		    </td>
                    <td><div data-bind="if: zcode"><span data-bind="text: zcode.title"></span></div>
                        <div data-bind="ifnot: zcode">Документ відсутній в базі</div>
                    </td>
                    <td><span data-bind="text: mark"></span></td>
                    <!--<td><span data-bind="text: checked"></span></td>-->
                    <td><span data-bind="text: updtdate"></span></td>
                </tr>

            </tbody>
        	</table>

            <div data-bind="if: zcode">
            
            <div class="row well" style="margin-right: 0px;">
                
            <div class="col-md-6">
                    <dl class="dl-horizontal" style="font-size: small;">
                        <dt>Видавник</dt>
                        <dd><span data-bind="text: zcode.publish"></span></dd>
                        <dt>Вид</dt>
                        <dd><span data-bind="text: zcode.vidy"></span></dd>
                        <dt>Номер</dt>
                        <dd><span data-bind="text: zcode.numbers"></span></dd>
                        <dt>Дата</dt>
                        <dd><span data-bind="text: zcode.doc_date"></span></dd>
                        <dt>Статус</dt>
                        <dd><span data-bind="text: zcode.status"></span></dd>
                        <div data-bind="if: zcode.regnum">
                            <dt>Реєстр. №</dt>
                            <dd><span data-bind="text: zcode.regnum"></span></dd>
                        </div>
                        <div data-bind="if: zcode.regdate">
                            <dt>Реєстр. дата</dt>
                            <dd><span data-bind="text: zcode.regdate"></span></dd>
                        </div>
                        <div data-bind="if: zcode.npa">
                            <dt>Категорія</dt>
                            <dd><span data-bind="text: zcode.npa"></span></dd>
                        </div>
                    </dl>
                </div>
                <div class="col-md-6">
                    <dl class="dl-horizontal" style="font-size: small;">
                        <dt>Гіперкод</dt>
                        <dd><span data-bind="text: zcode.code"></span></dd>
                        <div data-bind="if: zcode.codeLG">
                            <dt>codeLG</dt>
                            <dd><span data-bind="text: zcode.codeLG"></span></dd>
                        </div>
                        <dt>Uid</dt>
                        <dd><span data-bind="text: zcode.uid"></span></dd>           
                        <div data-bind="if: zcode.gosnum">
                            <dt>Gosnum</dt>
                            <dd><span data-bind="text: zcode.gosnum"></span></dd>  
                        </div>          
                        <dt>Modify date</dt>
                        <dd><span data-bind="text: zcode.modify_date"></span></dd>
                    </dl>
                </div>
            </div>
            
            <!--<p class="text-center"><button class="btn btn-link" data-toggle="collapse" data-which="morein" data-bind="uniquedatatargetFor: $data">більше...</button></p>

            <div data-which="more" data-bind="uniqueId: $data, checked: morein" class="collapse">
            </div>-->
            
            </div>
          </div>
        </div>
    </div>
    </div>
    
    </div>

    </div>
    </div>
</div>
<!-- END template for document -->
</div>
</form>
<a href="#" id="toTop"  class="btn btn-default btn-lg" role="button"><span class="glyphicon glyphicon-chevron-up"></span></a>

<script type="text/javascript">
$(document).ready(function() {
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
            init: function(element, valueAccessor) {
                element.setAttribute(
                "href", "#"+ko.bindingHandlers.uniqueId._ensureId(valueAccessor(), element));
             } 
        };

        ko.bindingHandlers.uniquedatatargetFor = {
            init: function(element, valueAccessor) {
                element.setAttribute(
                "data-target", "#"+ko.bindingHandlers.uniqueId._ensureId(valueAccessor(), element));
             } 
        };

        function dataModel() {
            var self = this;
            self.docs = ko.observableArray([]);
            self.maxvers = ko.observable();
            self.toggleChecked = function(data) {
                $.ajax({
                    type: "POST",
                    url: "dochecked.php",
                    //data: JSON.stringify(data),
                    data: data,
                    success : function(text){
                        //alert(text);
                        var idteg = "#"+data['code'];
                        $( idteg+" > div > button,"+idteg+" > div > span").toggleClass( "hidden" );
                    }
                });
            }

            $.ajax({            
                beforeSend:function(){
                    $('#nodocs').hide();
                    $('#myPleaseWait').modal('show');
                },
                complete:function(){
                    $('#myPleaseWait').modal('hide');
                },
                dataType: "json",
                url: "checkzminy.php",
                type: "GET",
                success: function (data) {
                    self.docs(data.docs);
                    self.maxvers = data.maxvers;
                    var n = data.docs.length; 
                    $('#count_docs').text("Знайдено "+n+" ");                   
                },
                error: function () {
                    $('#myPleaseWait').modal('hide');
                    $('#nodocs').show();
                    //console.log('An error occurred');
                }
            });
            
            self.params = ko.observableArray([]);
            
            self.GetParams = function() {
            	$.ajax({            
                	beforeSend:function(){
                    	$("#loading_params").show();
                	},
               		complete:function(){
                    	$("#loading_params").hide();
                	},
               		dataType: "json",
                	url: "params.php",
                	type: "GET",

                	success: function (par) {
                    	self.params(par.params);                  
                	}
            	});
            }
            self.SaveParams = function() {
            	$.ajax({            
                	url: "paramsset.php",
                	type: "POST",
                	data: $('#params_form').serialize(),
                	success: function (par) {
                    		//if (par=='ok') {
                            		//alert('Параметри збережено');
                        	//}   
                    		$('#params').modal('hide'); 
                        	window.location.reload(true);           
                	}
            	    });            
        	}
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

        }

        ko.applyBindings(new dataModel()); 

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
});
</script>
</body>
</html>

<?php } ?>
