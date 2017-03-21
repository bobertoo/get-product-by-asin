$(document).ready(function(){
    $(".toDB").hide()

    $.getJSON( "api.php?action=getAllProducts", function( data ) {
      var items = [];
      for(var i = data.length - 1; i >= 0; i--){
        items.push("<tr>")
        items.push("<td>" + data[i].asin + "</td>")
        items.push("<td>" + data[i].title + "</td>")
        items.push("<td>" + data[i].mpn + "</td>")
        items.push("<td>" + data[i].price + "</td>")
        items.push("</tr>")
      }
      $( "tbody").append( items.join( '' ) )
    });

    $( "#search-amz" ).submit(function( event ) {
      event.preventDefault();
      getAmazonJSON(event.target.children.asin.value)
    });

    function getAmazonJSON(asin){
      $.getJSON( "api.php?action=getAmazonUrl&asin=" + asin, function( data ) {
        $( "span#asin_target" ).text( data.Items.Item.ASIN );
        $( "span#title_target" ).text( data.Items.Item.ItemAttributes.Title );
        $( "span#mpn_target" ).text( data.Items.Item.ItemAttributes.MPN );
        $( "span#price_target" ).text( data.Items.Item.ItemAttributes.ListPrice.FormattedPrice );
        $(".toDB").show()
      });
    }

    $( ".toDB" ).click(function() {
      if ($("span#price_target")[0].innerHTML){
        var asin = $("span#asin_target")[0].innerHTML
        var title = $("span#title_target")[0].innerHTML
        var mpn = $("span#mpn_target")[0].innerHTML
        var price = $("span#price_target")[0].innerHTML
        $.post( "api.php", { asin: asin, title: title, mpn: mpn, price: price} )
        var items = [];
        items.push("<tr>")
        items.push("<td>" + asin + "</td>")
        items.push("<td>" + title + "</td>")
        items.push("<td>" + mpn + "</td>")
        items.push("<td>" + price + "</td>")
        items.push("</tr>")
        $( "tbody tr:first" ).after( items.join( '' ) );
        $(".toDB").hide()
      }
    });
});
