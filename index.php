<?php
require 'vendor/autoload.php';

/**
 * Create a fake identity
 */
$faker = Faker\Factory::create();

$user = [
    'username' => $faker->name /* Give the user a random name */
];
$user['id'] = md5($user['username']);
?>

<!DOCTYPE html>
<html>
	<head>
		<title>Chat Room</title>
		<meta charset='utf-8' />		
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
		<script src="http://code.jquery.com/jquery-2.1.0-rc1.min.js"></script>
		<script src="index.js"></script>
		<link rel="stylesheet" type="text/css" href="index.css">
		<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
		
		<style type="text/css" media="screen">
			li span {
			  color: #66ccff;
			}
			.success {
			  color: green;
			}
			.error {
			  color: red;
			}
			#myMessage{
				color: lightskyblue;
				font-style: italic;
				font-weight: bold;
			}
			#othersMessage{
				color: rebeccapurple;
				font-style: italic;
				font-weight: bold;
			}
			#body {
				position: relative;
				margin-left: 50px;
				float: left;
				padding:0px;
			}
			#chat{
				width:800px;
				height: 400px;
				border:5px solid #CCC;
				background-color: white;
				overflow-y: scroll;
			}
			.btn{
				position:relative;
			}
			.user_list {
				width: 20%;
				height: 500px !important;
				border: 1px solid black;
				text-align: left;
				padding: 5px;
				float: left;
			}
		</style>
	
	</head>
	<body>
		<i class="fa fa-wechat" style="font-size:48px;color:red"></i>
		<ul>
		  <li><a class="active" href="login.html">Login</a></li>
		  <li><a class="active" href="signUp.html">Sign Up</a></li>
		</ul>
		
		
		<i><h4 id="message"></h4></i><!--System Message-->
		
	<div id="body">
		<div id="chat"></div><br />
		<div id="inputs">
			<div class="input-group col-xs-4">
				<span class="input-group-addon"><i class="glyphicon glyphicon-user"></i></span>
				<input class="form-control input-sm " type="text" readonly id="username" name="message" value="<?php print $user['username']; ?>">
			</div>
			<form>
				<div class="input-group col-xs-4" id='message_input'>
					<span class="input-group-addon">Text</span>
					<textarea rows='3' cols='3' wrap='hard' maxlength='150' class="form-control input-lg " type="text" id="input" name="message" value="" placeholder="Enter a Message"></textarea>
				</div>
				
				<br>
					<input type="submit" class="btn btn-info" name="Submit" value="Submit">
					<input type="reset" class="btn btn-warning" name="Clear" value="Clear">
			</form>
		</div>
	</div>	
		
		<div id="privateChat">
		<div class="chat-box">
			<div class="chat-header">Online Users</div>
			<div class="chat-body"></div>
		</div>
		<div class='msg-box' style='right:290px'>
			<div class='msg-header'>
				<div class='close'>x</div>
			</div>
			<div class='msg-wrap'>
				<div id='msg-body'>
					<!--<div class='msg-a'>This is from user A</div>
					<div class='msg-b'>This is from current user</div>-->
					<div class='msg-insert'></div>
				</div>
				
				<div class='msg-footer'>
				<form>
				<textarea id='msg-input' rows='4' value='' placeholder='Type a message..'></textarea>
				<input type='submit' name='submit' value='submit'>
				</form>
				</div>
			</div>
		</div>
		</div>
		

		
		
<script>
	
	var chat_user  = JSON.parse('<?php print addslashes(json_encode($user)); ?>');
	
	

    document.addEventListener('DOMContentLoaded', function() {
      var conn = new WebSocket('ws://localhost:8080');
      var mess = document.getElementById('message');
	  var body = document.getElementById('body');
      var chat = document.getElementById('chat');
	  var privateChat = document.getElementById('msg-body');
	  var chatInput = document.getElementById('input');
	  var messageInputDiv = document.getElementById('message_input');
	  var msgInput = document.getElementById('msg-input');
      var connected = false;
	  var username = document.getElementById('username');
	  var user_list = $('.chat-body').get(0);
		  
      var m = function(string, cname) {
        mess.className = cname;
        mess.innerHTML = string;
      }
     
	 
      conn.onopen = function(e) {
        m("Connection established!", 'success');
        connected = true;
		register_client();
		request_userlist();
      };
      conn.onclose = function(e) {
        m("Connection closed!", 'error');
        connected = false;
      };
      
      conn.onmessage = function(e) {
        var data = JSON.parse(e.data);
		if(data.type == 'message'){
			if(data.to_user==null){
				newChat(data);
			}else{
				newPrivateChat(data);
			}		
		}else if(data.type == 'userlist'){
			users_output(data.users);
		}
      };
	
	
	function clear_userlist(){
	
		while (user_list.firstChild){
			user_list.removeChild(user_list.firstChild);
		}
	}
	
	function register_client(){
		var package = {
			'user': chat_user,
			'type': 'registration',
		};
		package = JSON.stringify(package);
		conn.send(package);
	
	}
	
	function request_userlist(){
		var package = {
			'user': chat_user,
			'type': 'userlist',
		};
		package = JSON.stringify(package);
		conn.send(package);
	}
	
	function users_output(users){
		clear_userlist();
		for(var connid in users){
			if(users.hasOwnProperty(connid)){
				var user = users[connid];
				
				elm = document.createElement('DIV');
				elm.id = user.id;
				elm.className = 'user';
				
				
				if(user.id == chat_user.id){
					elm.disabled = true;
					elm.appendChild(document.createTextNode('You'));
				}else{
					
					elm.appendChild(document.createTextNode(user.username));
				}
				
				user_list.appendChild(elm);
				/*document.getElementById(user.id).addEventListener('click',function(event){
					event.preventDefault();
					chatOpen(user.username);
				});*/
			}
		}
	
	}
	
	function chatOpen(username){
		
		document.getElementById('privateChat').innerHTML += "<div class='msg-box' style='right:290px'>"+
									"<div class='msg-header'>"+username+
									"<div class='close'>x</div>"+
									"</div>"+
									"<div class='msg-wrap'>"+
										"<div class='msg-body'>"+
										"<div class='msg-a'>This is from user A</div>"+
										"<div class='msg-b'>This is from current user</div>"+
										"<div class='msg-insert'></div>"+
										"</div>"+	
										"<div class='msg-footer'><textarea class='msg-input' rows='4' placeholder='Type a message..'></textarea></div>"+
									"</div>"+
									"</div>";
									
	}
	  // Global Message
      document.forms[0].addEventListener('submit', function(event) {
        event.preventDefault();
        if (username.value == '' || chatInput.value === '') {
          //alert('All Fields must be filled');
		 messageInputDiv.setAttribute("class","input-group col-xs-4 has-error");
          return;
        }else if(!connected) {
          //alert('connection is closed');
          return false;
        }
		messageInputDiv.setAttribute("class","input-group col-xs-4");
		var timeSent = new Date().timeNow();

        var data = {
			'user': username.value,
			'message': chatInput.value,
			'to_user': null,
			'type': 'message',
			'time': timeSent
			};
        newChat(data);
        conn.send(JSON.stringify(data));
        chatInput.value = '';
        chatInput.focus();
		
        return false;
      });
	  
	  //Private message
	  document.forms[1].addEventListener('submit', function(event) {
        event.preventDefault();
        if (username.value == '' || msgInput.value == '') {
          alert('All Fields must be filled');
          return;
        }else if(!connected) {
          alert('connection is closed');
          return false;
        }
		
		var to_user = document.getElementsByClassName('user')[0];
		var sendTo = to_user.id;
		
		var timeSent = new Date().timeNow();

        var data = {
			'user': username.value,
			'message': msgInput.value,
			'to_user': sendTo,
			'type': 'message',
			'time': timeSent
			};
        newPrivateChat(data);
        conn.send(JSON.stringify(data));
        msgInput.value = '';
        msgInput.focus();
		
        return false;
      });

	  	  
      function newChat(data) {
		if(data.user!=chat_user.username){
			var template = "<span id='othersMessage'>"+data.user+" : </span><i>"+data.message+"</i>";
		}else{
			var template = "<span id='myMessage'>"+data.user+" : </span><i>"+data.message+"</i>";
		}
        chat.innerHTML += template;
		var template = "<b> {"+data.time+"}</b><br>";
		chat.innerHTML += template;
		chat.scrollTop = chat.scrollHeight;
      }
	  
	  function newPrivateChat(data) {
		if(data.user!=chat_user.username){
			$('<div class="msg-a">'+data.message+'</div>').insertBefore('.msg-insert');
			
			//var template = "<div style='width:150px;word-wrap:break-word;display:block;'><b>"+data.user+"</b><span class='msg-a'>"+data.message+"</span></div>";
		}else{
			$('<div class="msg-b">'+data.message+'</div>').insertBefore('.msg-insert');
			//var template = "<div style='width:150px;word-wrap:break-word;display:block;'><span class='msg-b' >"+data.message+"</span><b>Me</b><div>";
		}
        //privateChat.innerHTML += template;
		//var template = "<i>"+data.time+"</i>"
		privateChat.scrollTop = privateChat.scrollHeight;
      }
	  
	  
		  	  
	  Date.prototype.timeNow = function () {
			return ((this.getHours() < 10)?"0":"") + this.getHours() +":"+ ((this.getMinutes() < 10)?"0":"") + this.getMinutes() +":"+ ((this.getSeconds() < 10)?"0":"") + this.getSeconds();
		}  
	  
    });
	
	
	
  </script>
		
	</body>
<html>	