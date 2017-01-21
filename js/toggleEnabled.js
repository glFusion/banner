/*  $Id: toggleEnabled.js 96 2014-04-02 20:19:25Z root $
 */
var BANR_xmlHttp;

function BANR_toggleEnabled(cbox, id, type)
{
  BANR_xmlHttp = BANR_GetXmlHttpObject();
  if (BANR_xmlHttp==null) {
    alert ("Browser does not support HTTP Request")
    return
  }
  var oldval = cbox.checked == true ? 0 : 1;
  var url=site_admin_url + "/plugins/banner/ajax.php?action=toggleEnabled";
  url=url+"&id="+id;
  url=url+"&type="+type;
  url=url+"&oldval="+oldval;
  url=url+"&sid="+Math.random();
  BANR_xmlHttp.onreadystatechange=BANR_sc_BannerEnabled;
  BANR_xmlHttp.open("GET",url,true);
  BANR_xmlHttp.send(null);
}

function BANR_sc_BannerEnabled()
{
  var newstate;

  if (BANR_xmlHttp.readyState==4 || BANR_xmlHttp.readyState=="complete") {
    jsonObj = JSON.parse(BANR_xmlHttp.responseText)

    // Set the span ID of the updated checkbox
    if (jsonObj.type == "cat_cb") {
        spanid = "togcatcb" + jsonObj.id
    } else {
        spanid = "togena" + jsonObj.id
    }

    if (jsonObj.newval == 1) {
        document.getElementById(spanid).checked = true;
    } else {
        document.getElementById(spanid).checked = false;
    }
  }
}

function BANR_GetXmlHttpObject()
{
  var objXMLHttp=null
  if (window.XMLHttpRequest)
  {
    objXMLHttp=new XMLHttpRequest()
  }
  else if (window.ActiveXObject)
  {
    objXMLHttp=new ActiveXObject("Microsoft.XMLHTTP")
  }
  return objXMLHttp
}

