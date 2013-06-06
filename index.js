$j=jQuery.noConflict();
$j(function(){
  function Citation(citation){
    that = this;
    this.ready = function(cb){
      if (!isNaN(citation.charAt(0))) {
	citation = 'science_health ' + citation	
      }
      else {
	citation = 'bible ' + citation	
      }
      $j.getJSON(
        that.url = "http://cskit-server.herokuapp.com/v1/text.json?"
        + "callback=?&citations=" + citation
      )
      .success(function(data){
        citation = data[0];
        that.volume = citation.volume;
        that.citation = citation.citation;
        that.text = citation.text;
        cb();
      })
      .error(function(jqxhr, status, error){ //Why isn't this working!!!! -> Apparently by design, there's a bug filed for it :(      
        that.volume = status + " : " + error; // This will support error handling https://github.com/jaubourg/jquery-jsonp
      });
    };
  }

  function current_line(){
    return $j('#citations').val().substr(0, $j('#citations')[0].selectionStart).split("\n").length - 1;
  }
  function format_citation(ct) {    
      var re = new RegExp(/_.[^_]+_/g);
      
      ct = ct.replace(/\n/g," <br>");
      
      while(m=re.exec(ct)) {
        k = m[0].replace(/_/g,"");
	k = "<i>" + k + "</i>";
	ct = ct.replace(m[0],k);	
      }
    return ct
  }
  
  function scroll_preview(){    
    $j('#sortable').scrollTop($j('#sortable').scrollTop() + ($j('#x' + current_line()).position().top - $j('#sortable').position().top) - ($j('#sortable').height()/2) + ($j('#x' + current_line()).height()/2) );
  }
  
  function update_preview(){
    //$j('#sortable > dl').each(function () {this.style.cssText = 'color: black;'});
    
    input_lines = $j('#citations').val().split('\n');
    for (var i=0;i<input_lines.length;i++){ 
      var xc = '#xc' + i;
      
      //console.log($j(xc).val());
      //console.log($j('#citations').val().split('\n')[i]);
      var new_input = $j('#citations').val().split('\n')[i];
      var cn = i + 1;
      if($j('#sortable > dl > input')[i]){
        xcvalue = $j('#sortable > dl > input')[i].value;
      } else {
	xcvalue = 'na';	
      }    
      
      if (xcvalue != input_lines[i]) {
	if (input_lines[i].length == 0) {
	  if($j('#sortable > dl')[i]){
	    el = $j('#sortable > dl > dt')[i];
	    //$j(el).text('Citation #'+cn);
	    $j(el).text('');
	    $j(el).append('<hr />');
	    el = $j('#sortable > dl > dd')[i];
	    //$j(el).text('Please type eg 4:4 or Gen 1:1...');
	    $j(el).text('');
	  }
	}
	console.log('xcvalue: ' + xcvalue);
	console.log('xcvaluelength: ' + xcvalue.length);
	if (xcvalue == 'na') {	  
	  $j('#sortable').append('<dl id="x'+i+'"><input type="hidden" id="xc'+i+'" class="xc" value="' + input_lines[i] + '"><dt id="citation_citation_'+i+'"><hr /></dt><dd id="citation_text_'+i+'"></dd> </dl>');      
	}
	//console.log("unequal");
	
	$j(xc).val(input_lines[i]);
	reload_citation(i);
      }            
    }        
    var i=1;
    $j('#sortable > dl').each(function () {
      //console.log('i' + i);
      //console.log('max lines' + input_lines.length);
      //console.log($j(this).val());
      if (i>input_lines.length) {
	$j(this).remove();
      }
      i++;
      
    });
    //$j('#x' + current_line()).attr('style','color: black;');
    //alert($j('#sortable').html());
	$j('#rendered_citations').val($j('#sortable').html());
    scroll_preview();
  }
  
  function reload_citation(cl){
    $j('#tooltip').text("Looking up citations...");    
    //console.log(current_line());        
    cit = $j('#citations').val().split('\n')[cl];
      if (!isNaN(cit.charAt(0))) {
	$j('#detected_book').text('  Book: S&H');    
      }
      else {
	$j('#detected_book').text('  Book: KJV');    
      }	            
    cit = $j('#citations').val().split('\n')[cl];
    //console.log($j('#citations').val().split('\n')[0]);
    citation = new Citation(cit);
    citation.ready(function(){  
      if (!isNaN(citation.citation.charAt(0))) {
	citation.citation = 'S&H ' + citation.citation
      }
      $j('#citation_citation_'+cl).text(citation.citation);
      formatted_citation = format_citation(citation.text);
      $j('#citation_text_'+cl).html(formatted_citation);        
      $j('#tooltip').text("Done, hit enter for the next one...");
      scroll_preview();
    });
    
  }

  $j('#citations').change(update_preview);
  $j('#citations').keyup(update_preview);    
  
  
  $j("#name").click(function(){
    if ($j("#name").val() == title_placeholder ) {$j(this).select();}
  });
  $j("#description").click(function(){
    if ($j('#description').val() == description_placeholder) {$j(this).select();}
  });
  
  
  
  update_preview();
  //console.log('just loaded citations');
  //$j('#masthead').remove();
  //$j('header').remove();
  //$j('#page').html($j('#takeover').html());
  
  
$j( document ).ready(function() {
  $j('#citations').focus();
  $j('#citations').val("Gen. 12:1-4 the (to :)");
  if ($j('#name').val().length == 0) {
    $j('#name').val(title_placeholder);
  }
  if ($j('#description').val().length == 0) {
    $j('#description').val(description_placeholder);
  }
  update_preview();
});

});

