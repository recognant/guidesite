
function load(tag) {
	webi.poll("http://localhost/guidesite/node/index.php?tag=" + tag, function(data) {
		load(data);
	});	
}