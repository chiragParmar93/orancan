
$(function(){
	es.file_ext = function(filename) {
		return filename.substr(filename.lastIndexOf('.') + 1);
	}
});