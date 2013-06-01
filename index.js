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
    return $j('#citations').val().substr(0, $j('#citations')[0].selectionStart).split("\n").length;
  }
  function scroll_preview(){
    $j('#sortable').scrollTop($j('#sortable').scrollTop() + ($j('#x' + current_line()).position().top - $j('#sortable').position().top) - ($j('#sortable').height()/2) + ($j('#x' + current_line()).height()/2) );
  }
  
  function update_preview(){
    if ($j('#x' + current_line()).length == 0) {
      	$j('#sortable').append('<dl id="x'+current_line()+'">      	  <dt id="citation_citation_'+current_line()+'">Citation #'+current_line()+'</dt>	  <dd id="citation_text_'+current_line()+'">Please type eg 4:4 or Gen 1:1...</dd> </dl>');      
    }
    $j('#sortable > dl').each(function () {this.style.cssText = 'color: black;'});
    $j('#x' + current_line()).attr('style','color: blue;');
    scroll_preview();
    reload_citation();
  }
  
  function reload_citation(){
    $j('#tooltip').text("Busy, keep typing..");    
    console.log(current_line());    
    cl = current_line();
    cit = $j('#citations').val().split('\n')[cl-1];
      if (!isNaN(cit.charAt(0))) {
	$j('#detected_book').text('  Book: S&H');    
      }
      else {
	$j('#detected_book').text('  Book: KJV');    
      }	        
    cl = current_line();
    cit = $j('#citations').val().split('\n')[cl-1];
    console.log($j('#citations').val().split('\n')[0]);
    citation = new Citation(cit);
    citation.ready(function(){  
      if (!isNaN(citation.citation.charAt(0))) {
	citation.citation = 'S&H ' + citation.citation
      }
      $j('#citation_citation_'+cl).text(citation.citation);
      $j('#citation_text_'+cl).text(citation.text);  
      $j('#tooltip').text("Done, hit enter for the next one..");
      scroll_preview();
    });
    
  }

  $j('#citations').change(update_preview);
  $j('#citations').keyup(update_preview);    
  
  
  $j("#name").click(function(){
    if ($j("#name").val() == 'New Inspiration' ) {$j(this).select();}
  });
  $j("#description").click(function(){
    if ($j('#description').val() == 'This is a little introduction on my citations..') {$j(this).select();}
  });
  
  
  
  update_preview();
  console.log('just loaded citations');
  $j('#masthead').remove();
  $j('header').remove();
  //$j('#page').html($j('#takeover').html());
  
  
$j( document ).ready(function() {
  $j('#citations').focus();
  $j('#citations').val("Gen. 12:1-4 the (to :)");
  if ($j('#name').val().length == 0) {
    $j('#name').val('New Inspiration');
  }
  if ($j('#description').val().length == 0) {
    $j('#description').val('This is a little introduction on my citations..');
  }
  update_preview();
});

});

