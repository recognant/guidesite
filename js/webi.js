var webi = {};

webi.settings = {};
webi.settings.type = "GET";
webi.settings.timeout = 30000;

webi.get = function(url, query) {
	if(!($.type(url) === "string")) {
		throw "TypeError: URL has to be a String";
	}
	if($.isPlainObject(query)) {
		if($.type(query) === "undefined" || query == null) {
			query = {};			
		}
	} else {
		throw "TypeError: query has to be an Object.";
	}

	var done = query.done;
	var fail = query.fail;
	var always = query.always;
	
	var options = {
		type: webi.settings.type,
		url: url,
		timeout: webi.settings.timeout,
	};
	
	if(query.data && !($.type(query.data) === "undefined" || query.data == null)) {
		$.extend(options, { data: query.data });
	}
	if(query.type && $.type(query.type) === "string" && !($.type(query.type) === "undefined" || query.type == null)) {
		$.extend(options, { type: query.type });
	}
	if($.type(query.timeout) === "number" && !($.type(query.timeout) === "undefined" || query.timeout == null)) {
		$.extend(options, { timeout: query.timeout });
	}
	if(query.dataType && $.type(query.dataType) === "string" && !($.type(query.dataType) === "undefined" || query.dataType == null)) {
		$.extend(options, { dataType: query.dataType });
	}
	if(query.contentType && $.type(query.contentType) === "string" && !($.type(query.contentType) === "undefined" || query.contentType == null)) {
		$.extend(options, { contentType: query.contentType });
	}
	if($.type(query.processData) === "boolean" && !($.type(query.processData) === "undefined" || query.processData == null)) {
		$.extend(options, { processData: query.processData });
	}
	
	$.ajax(options).done(function(data, textStatus, xhr) {
		if(!($.type(done) === "undefined" || done == null))
			done(data);
	}).fail(function(xhr, textStatus, error) {
		if(!($.type(fail) === "undefined" || fail == null))
			fail(error); 
	}).always(function() {
		if(!($.type(always) === "undefined" || always == null))
			always(url, done, fail);
	});
};

webi.poll = function(url, done, fail, always, options) {
	if(!($.type(url) === "string")) {
		throw "TypeError: URL has to be a String.";
	}
	
	var query = {};
	if($.isPlainObject(done)) {
		if(!($.type(fail) === "undefined" && $.type(always) === "undefined" && $.type(options) === "undefined"))
			throw "SyntaxError: Arguments were not undefined.";
		
		if(!($.type(done) === "undefined" || done == null))
			$.extend(query, done);
		
	} else if($.type(done) === "function") {
		if(!($.type(done) === "undefined" || done == null))
			$.extend(query, { done: done });
	}
//	console.log(query);
	
	if($.isPlainObject(fail)) {
		if(!($.type(always) === "undefined" && $.type(options) === "undefined"))
			throw "SyntaxError: Arguments were not undefined.";
		
		if(!($.type(fail) === "undefined" || fail == null))
			$.extend(query, fail);
		
	} else if($.type(fail) === "function") {
		if(!($.type(fail) === "undefined" || fail == null))
			$.extend(query, { fail: fail });
	}
//	console.log(query);
	
	if($.isPlainObject(always)) {
		if(!($.type(options) === "undefined"))
			throw "SyntaxError: Arguments were not undefined.";
		
		if(!($.type(always) === "undefined" || always == null))
			$.extend(query, always);
		
	} else if($.type(always) === "function") {
		if(!($.type(always) === "undefined" || always == null))
			$.extend(query, { always: always });
	}
//	console.log(query);
	
	if($.isPlainObject(options)) {
		if(!($.type(options) === "undefined" || options == null))
			$.extend(query, options);
	}
//	console.log(query);
	
	webi.get(url, query);
};

