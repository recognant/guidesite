<?php
	session_start();
?>

<!DOCS>

<html>
	<head>
		<link href="css/normalize.css" rel="stylesheet" type="text/css">
		<link href="css/bootstrap.min.css" rel="stylesheet" type="text/css">
		<link href="css/bootstrap-theme.min.css" rel="stylesheet" type="text/css">
		<link href="css/font-awesome.min.css" rel="stylesheet" type="text/css">
		<link href="css/styles.css" rel="stylesheet" type="text/css">
		
		<script>
			var wowhead_tooltips = { "colorlinks": true, "iconizelinks": true, "renamelinks": true };
		</script>
		<script type="text/javascript" src="http://static.wowhead.com/widgets/power.js"></script>
		
	</head>
	
	<body>
	
		<div class="container">
			
			<h1>Item Preview</h1>
			<div id="preview" class="container-fluid" style="padding: 25px; border:1px solid #ccc;"></div>
			
			<h1>Editor <button class="btn btn-success pull-right" onclick="save();"><i class="fa fa-file-text"></i>Preview</button></h1>
			<div id="editor" align="center">
			
				<div id="toolbox" style="padding: 10px;">
				
					<div class="btn-group">
						
						<button class="btn btn-default" id="tool-title" title="Title"><i class="fa fa-header"></i></button>
						<button class="btn btn-default" id="tool-icon" title="Icons"><i class="fa fa-smile-o"></i></button>
						<button class="btn btn-default" id="tool-list" title="Unordered List"><i class="fa fa-list"></i></button>
						<button class="btn btn-default" id="tool-list-ordered" title="Ordered List"><i class="fa fa-list-ol"></i></button>
					
					</div>
					
					<div class="btn-group">
					
						<button class="btn btn-default" id="tool-size" title="Text Size"><i class="fa fa-text-height"></i></button>
						<button class="btn btn-default" id="tool-bold" title="Bold"><i class="fa fa-bold"></i></button>
						<button class="btn btn-default" id="tool-italic" title="Italic"><i class="fa fa-italic"></i></button>
						<button class="btn btn-default" id="tool-underline" title="Underline"><i class="fa fa-underline"></i></button>
					
					</div>
					
					<div class="btn-group">
					
						<button class="btn btn-default" id="tool-code" title="Code"><i class="fa fa-code"></i></button>
						<button class="btn btn-default" id="tool-block" title="Block"><i class="fa fa-square-o"></i></button>
						<button class="btn btn-default" id="tool-img" title="Image"><i class="fa fa-picture-o"></i></button>
						<button class="btn btn-default" id="tool-video" title="Video"><i class="fa fa-youtube"></i></button>
						<button class="btn btn-default" id="tool-url" title="Link"><i class="fa fa-link"></i></button>
						<button class="btn btn-default" id="tool-quote" title="Quote"><i class="fa fa-quote-left"></i></button>
						<button class="btn btn-default" id="tool-alert" title="Alert"><i class="fa fa-exclamation-triangle"></i></button>
						<button class="btn btn-default" id="tool-table" title="Table"><i class="fa fa-table"></i></button>
						<button class="btn btn-default" id="tool-panel" title="Panel"><i class="fa fa-list-alt"></i></button>
						<button class="btn btn-default" id="tool-columns" title="Column Layout"><i class="fa fa-columns"></i></button>
						<button class="btn btn-default" id="tool-rows" title="Row Layout"><i class="fa fa-bars"></i></button>
						<button class="btn btn-default" id="tool-tabs" title="Tabs"><i class="fa fa-th-large"></i></button>
					
					</div>
				
				</div>
			
				<textarea id="content" class="form-control" style="resize:none; min-height:500px;"></textarea>
			
			</div>
		
		</div>

	</body>	
	<script src="js/jquery-2.1.4.min.js"></script>
	<script src="js/bootstrap.min.js"></script>
	<script>
		var id = "";
		
		function load(file) {
			$("#content").load("./node/temp/" + encodeURIComponent(file), function(response, status, xhr) {
				if( status !== "error" ) {
					id = file.replace(/\.gfl$/i, "");
				}
			});
		}
		
		function save() {
			console.log($("#content").val());
			$.post("./node/index.php", { 
				data: $("#content").val(),
				id: id
			}).done(function(data) {
				var content = $($.parseHTML(data));
				id = content.filter("title").text();
				$("#preview").html(content);
				$WowheadPower.refreshLinks();
			});
		}
		
		$("#toolbox").on("click", "button", function() { 
			switch($(this).attr("id")) {
			case "tool-title":
				insert("[title]", "[/title]");
				break;
			case "tool-icon":
				insert("[icon]", "[/icon]");
				break;
			case "tool-list":
				insert("[list]\n[*]", "\n[/list]");
				break;
			case "tool-list-ordered":
				insert("[list,type=numeric]\n[*]", "\n[/list]");
				break;
			case "tool-size":
				insert("[size,type=]", "[/size]");
				break;
			case "tool-bold":
				insert("[b]", "[/b]");
				break;
			case "tool-italic":
				insert("[i]", "[/i]");
				break;
			case "tool-underline":
				insert("[u]", "[/u]");
				break;
			case "tool-code":
				insert("[code]", "[/code]");
				break;
			case "tool-block":
				insert("[important]", "[/important]");
				break;
			case "tool-img":
				insert("[img,uri=]", "[/img]");
				break;
			case "tool-video":
				insert("[video,uri=]", "[/video]");
				break;
			case "tool-link":
				insert("[url,uri=]", "[/url]");
				break;
			case "tool-quote":
				insert("[quote,src=]", "[/quote]");
				break;
			case "tool-alert":
				insert("[alert,type=]", "[/alert]");
				break;
			case "tool-table":
				insert("[list,type=table,title=;]\n[*]", "\n[/list]");
				break;
			case "tool-panel":
				insert("[panel,title=]", "[/panel]");
				break;
			case "tool-columns":
				insert("[list,type=column]", "[/list]");
				break;
			case "tool-rows":
				insert("[list,type=row]\n[*]", "\n[/list]");
				break;
			case "tool-tabs":
				insert("[list,type=tab]\n[*,title=]", "\n[/list]");
				break;
			default:
			}
		});
		
		function insert(aTag, eTag) {
		
			var input = document.getElementById('content');
			input.focus();
			
			/* für Internet Explorer */
			if(typeof document.selection != 'undefined') {
				/* Einfügen des Formatierungscodes */
				var range = document.selection.createRange();
				var insText = range.text;
				range.text = aTag + insText + eTag;
				/* Anpassen der Cursorposition */
				range = document.selection.createRange();
				if (insText.length == 0) {
				  range.move('character', -eTag.length);
				} else {
				  range.moveStart('character', aTag.length + insText.length + eTag.length);      
				}
				range.select();
			}
		 
			/* für neuere auf Gecko basierende Browser */
			else if(typeof input.selectionStart != 'undefined') {
				/* Einfügen des Formatierungscodes */
				var start = input.selectionStart;
				var end = input.selectionEnd;
				var insText = input.value.substring(start, end);
				input.value = input.value.substr(0, start) + aTag + insText + eTag + input.value.substr(end);
				/* Anpassen der Cursorposition */
				var pos;
				if (insText.length == 0) {
				  pos = start + aTag.length;
				} else {
				  pos = start + aTag.length + insText.length + eTag.length;
				}
				input.selectionStart = pos;
				input.selectionEnd = pos;
			}
			
			/* für die übrigen Browser */
			else {
				/* Abfrage der Einfügeposition */
				var pos;
				var re = new RegExp('^[0-9]{0,3}$');
				while(!re.test(pos)) {
				  pos = prompt("Einfügen an Position (0.." + input.value.length + "):", "0");
				}
				if(pos > input.value.length) {
				  pos = input.value.length;
				}
				/* Einfügen des Formatierungscodes */
				var insText = prompt("Bitte geben Sie den zu formatierenden Text ein:");
				input.value = input.value.substr(0, pos) + aTag + insText + eTag + input.value.substr(pos);
			}
		}
	</script>

	<script>
		$(document).ready(function() {
		<?php
			$file = isset($_GET['file']) ? $_GET['file'] : "";
			if( !empty($file) ) {
				echo "load('" . basename($file) . "');";
			}
		?>
		});
	</script>
	
</html>